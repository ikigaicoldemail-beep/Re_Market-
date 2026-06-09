<?php

namespace App\Services;

use App\Models\SocialAccount;
use App\Models\User;
use Composer\CaBundle\CaBundle;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SocialAccountService
{
    public function list(User $user): Collection
    {
        return $user->socialAccounts()->latest()->get();
    }

    public function connect(User $user, array $data): SocialAccount
    {
        if ($data['platform'] === 'facebook') {
            $data = $this->enrichFacebookPayload($data);
        }

        return DB::transaction(function () use ($user, $data) {
            return SocialAccount::updateOrCreate(
                [
                    'platform' => $data['platform'],
                    'provider_user_id' => $data['provider_user_id'],
                ],
                [
                    'user_id' => $user->id,
                    'provider_account_name' => $data['provider_account_name'] ?? null,
                    'access_token' => $data['access_token'],
                    'refresh_token' => $data['refresh_token'] ?? null,
                    'token_expires_at' => $data['token_expires_at'] ?? null,
                    'scopes' => $data['scopes'] ?? [],
                    'status' => 'active',
                    'last_synced_at' => now(),
                ]
            );
        });
    }

    public function disconnect(SocialAccount $account): SocialAccount
    {
        $account->update([
            'status' => 'disconnected',
            'access_token' => null,
            'refresh_token' => null,
        ]);

        return $account->fresh();
    }

    /**
     * Given a manual-link payload for Facebook, introspect the token and:
     *  - If it's a User token, exchange for a long-lived one and swap in the matching Page Access Token.
     *  - If it's already a Page token, verify it belongs to the page the user claims and record expiry.
     * Throws ValidationException with a friendly message if the token is invalid or doesn't match.
     */
    private function enrichFacebookPayload(array $data): array
    {
        $version = config('services.facebook.graph_version', 'v22.0');
        $clientId = config('services.facebook.client_id');
        $clientSecret = config('services.facebook.client_secret');
        $token = trim((string) $data['access_token']);
        $claimedPageId = (string) $data['provider_user_id'];

        // Introspection — needs an app access token but also accepts the input_token itself when allowed.
        $appToken = ($clientId && $clientSecret) ? "{$clientId}|{$clientSecret}" : $token;

        $debug = $this->http()->get("https://graph.facebook.com/$version/debug_token", [
            'input_token' => $token,
            'access_token' => $appToken,
        ]);

        if (! $debug->successful()) {
            throw ValidationException::withMessages([
                'access_token' => ['Could not validate the token with Facebook: '.$debug->body()],
            ]);
        }

        $info = $debug->json('data') ?? [];

        if (empty($info['is_valid'])) {
            throw ValidationException::withMessages([
                'access_token' => ['Facebook says this token is not valid (it may have expired). Regenerate it and try again.'],
            ]);
        }

        $type = strtoupper((string) ($info['type'] ?? ''));
        $scopes = $info['scopes'] ?? [];
        // Facebook returns expires_at=0 for non-expiring tokens; treat that as null.
        $expiresAt = (! empty($info['expires_at']) && (int) $info['expires_at'] > 0)
            ? Carbon::createFromTimestamp((int) $info['expires_at'])
            : null;

        if ($type === 'PAGE') {
            // Make sure the token actually belongs to the page id the user pasted.
            $profileId = (string) ($info['profile_id'] ?? '');
            if ($profileId !== '' && $profileId !== $claimedPageId) {
                throw ValidationException::withMessages([
                    'provider_user_id' => ["This Page token belongs to page id {$profileId}, not {$claimedPageId}."],
                ]);
            }

            return array_merge($data, [
                'provider_user_id' => $claimedPageId,
                'access_token' => $token,
                'token_expires_at' => $expiresAt,
                'scopes' => $scopes,
            ]);
        }

        if ($type !== 'USER') {
            throw ValidationException::withMessages([
                'access_token' => ['Unsupported Facebook token type: '.($info['type'] ?? 'unknown').'.'],
            ]);
        }

        // USER token — exchange to long-lived, then resolve the matching Page token.
        $longUserToken = $token;

        if ($clientId && $clientSecret) {
            try {
                $longResp = $this->http()->asJson()->get("https://graph.facebook.com/$version/oauth/access_token", [
                    'grant_type' => 'fb_exchange_token',
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'fb_exchange_token' => $token,
                ]);

                if ($longResp->successful() && $longResp->json('access_token')) {
                    $longUserToken = $longResp->json('access_token');
                    if ($longResp->json('expires_in')) {
                        $expiresAt = now()->addSeconds((int) $longResp->json('expires_in'));
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('FB long-lived token exchange failed; falling back to short-lived token.', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $pagesResp = $this->http()->get("https://graph.facebook.com/$version/me/accounts", [
            'access_token' => $longUserToken,
            'fields' => 'id,name,access_token,tasks',
        ]);

        if (! $pagesResp->successful()) {
            throw ValidationException::withMessages([
                'access_token' => ['Could not list your managed Pages: '.$pagesResp->body()],
            ]);
        }

        $pages = $pagesResp->json('data') ?? [];
        $page = collect($pages)->firstWhere('id', $claimedPageId);

        if (! $page || empty($page['access_token'])) {
            $visible = collect($pages)->map(fn ($p) => ($p['id'] ?? '?').' ('.($p['name'] ?? '?').')')->implode(', ') ?: 'none';
            throw ValidationException::withMessages([
                'provider_user_id' => [
                    "The page id {$claimedPageId} is not managed by this Facebook user. Pages visible to this token: {$visible}.",
                ],
            ]);
        }

        // Introspect the page token we just received so we know its true expiry (Page tokens from long-lived
        // user tokens are often non-expiring; FB returns expires_at=0 in that case).
        $pageDebug = $this->http()->get("https://graph.facebook.com/$version/debug_token", [
            'input_token' => $page['access_token'],
            'access_token' => $appToken,
        ]);
        if ($pageDebug->successful()) {
            $pe = (int) ($pageDebug->json('data.expires_at') ?? 0);
            $expiresAt = $pe > 0 ? Carbon::createFromTimestamp($pe) : null;
        }

        return array_merge($data, [
            'provider_user_id' => $claimedPageId,
            'provider_account_name' => $data['provider_account_name'] ?? $page['name'] ?? null,
            'access_token' => $page['access_token'],
            'token_expires_at' => $expiresAt,
            'scopes' => $page['tasks'] ?? $scopes,
        ]);
    }

    private function http(): PendingRequest
    {
        $verify = class_exists(CaBundle::class) ? CaBundle::getBundledCaBundlePath() : true;

        return Http::withOptions(['verify' => $verify, 'timeout' => 20]);
    }
}
