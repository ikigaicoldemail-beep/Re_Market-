<?php

namespace App\Http\Controllers;

use App\Services\SocialAccountService;
use Composer\CaBundle\CaBundle;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SocialOAuthController extends Controller
{
    private const SUPPORTED_PROVIDERS = ['facebook'];

    public function __construct(private readonly SocialAccountService $socialAccountService) {}

    /**
     * API endpoint (JWT-auth): returns a signed start URL the frontend opens in a popup.
     */
    public function buildAuthorizeUrl(Request $request, string $provider)
    {
        abort_unless(in_array($provider, self::SUPPORTED_PROVIDERS, true), 404);
        abort_unless($this->isConfigured($provider), 503, ucfirst($provider).' OAuth is not configured.');

        $state = Str::random(40);
        Cache::put('oauth_state:'.$state, [
            'user_id' => $request->user()->id,
            'provider' => $provider,
        ], now()->addMinutes(15));

        $startUrl = URL::temporarySignedRoute(
            'oauth.start',
            now()->addMinutes(10),
            ['provider' => $provider, 'state' => $state]
        );

        return response()->json([
            'authorize_url' => $startUrl,
            'state' => $state,
        ]);
    }

    /**
     * Browser entry: validates signed URL, builds provider authorize URL, redirects.
     */
    public function start(Request $request, string $provider): Response
    {
        abort_unless($request->hasValidSignature(), 403, 'Invalid or expired OAuth start link.');
        abort_unless(in_array($provider, self::SUPPORTED_PROVIDERS, true), 404);

        $state = (string) $request->query('state', '');
        $stash = Cache::get('oauth_state:'.$state);
        abort_unless($stash && ($stash['provider'] ?? null) === $provider, 403, 'OAuth state expired.');

        $url = $this->buildFacebookAuthorizeUrl($state);

        return redirect()->away($url);
    }

    /**
     * Provider callback: exchanges code, fetches profile, persists account, closes popup.
     */
    public function callback(Request $request, string $provider): Response
    {
        abort_unless(in_array($provider, self::SUPPORTED_PROVIDERS, true), 404);

        if ($error = $request->query('error_description') ?? $request->query('error')) {
            return $this->renderClosePopup(false, $error);
        }

        $state = (string) $request->query('state', '');
        $stash = Cache::pull('oauth_state:'.$state);
        if (! $stash || ($stash['provider'] ?? null) !== $provider) {
            return $this->renderClosePopup(false, 'OAuth state is invalid or already used.');
        }

        $code = (string) $request->query('code', '');
        if ($code === '') {
            return $this->renderClosePopup(false, 'Authorization code missing.');
        }

        try {
            $payloads = $this->handleFacebookCallback($code);

            $user = \App\Models\User::findOrFail($stash['user_id']);
            foreach ($payloads as $payload) {
                $this->socialAccountService->connect($user, array_merge($payload, [
                    'platform' => $provider,
                ]));
            }
        } catch (\Throwable $e) {
            Log::warning('OAuth callback failed', [
                'provider' => $provider,
                'message' => $e->getMessage(),
            ]);
            return $this->renderClosePopup(false, 'Could not connect account: '.$e->getMessage());
        }

        $msg = count($payloads) > 1
            ? count($payloads).' Pages linked to your account.'
            : null;
        return $this->renderClosePopup(true, $msg);
    }

    private function isConfigured(string $provider): bool
    {
        return ! empty(config("services.$provider.client_id"))
            && ! empty(config("services.$provider.client_secret"));
    }

    private function callbackUrl(string $provider): string
    {
        return config("services.$provider.redirect")
            ?: route('oauth.callback', ['provider' => $provider]);
    }

    private function buildFacebookAuthorizeUrl(string $state): string
    {
        $version = config('services.facebook.graph_version', 'v22.0');
        return 'https://www.facebook.com/'.$version.'/dialog/oauth?'.http_build_query([
            'client_id' => config('services.facebook.client_id'),
            'redirect_uri' => $this->callbackUrl('facebook'),
            'state' => $state,
            'scope' => 'public_profile,email,pages_manage_posts,pages_read_engagement',
            'response_type' => 'code',
        ]);
    }

    /**
     * Returns an array of SocialAccount payloads. If the user manages Pages,
     * one payload per Page (with the Page's own access token for posting).
     * Otherwise falls back to the user-level profile.
     */
    private function http(): PendingRequest
    {
        $verify = class_exists(CaBundle::class) ? CaBundle::getBundledCaBundlePath() : true;

        return Http::withOptions(['verify' => $verify]);
    }

    private function handleFacebookCallback(string $code): array
    {
        $version = config('services.facebook.graph_version', 'v22.0');

        $tokenResp = $this->http()->asJson()->get("https://graph.facebook.com/$version/oauth/access_token", [
            'client_id' => config('services.facebook.client_id'),
            'client_secret' => config('services.facebook.client_secret'),
            'redirect_uri' => $this->callbackUrl('facebook'),
            'code' => $code,
        ])->throw()->json();

        $shortUserToken = $tokenResp['access_token'] ?? null;
        if (! $shortUserToken) {
            throw new \RuntimeException('Facebook did not return an access token.');
        }

        // Exchange the short-lived user token (≈1 hour) for a long-lived one (~60 days).
        // Page Access Tokens derived from a long-lived user token are themselves long-lived,
        // so they keep working across re-logins instead of dying after an hour.
        $longUserToken = $shortUserToken;
        $expiresAt = isset($tokenResp['expires_in'])
            ? now()->addSeconds((int) $tokenResp['expires_in'])->toIso8601String()
            : null;

        try {
            $longResp = $this->http()->asJson()->get("https://graph.facebook.com/$version/oauth/access_token", [
                'grant_type' => 'fb_exchange_token',
                'client_id' => config('services.facebook.client_id'),
                'client_secret' => config('services.facebook.client_secret'),
                'fb_exchange_token' => $shortUserToken,
            ])->throw()->json();

            if (! empty($longResp['access_token'])) {
                $longUserToken = $longResp['access_token'];
                if (isset($longResp['expires_in'])) {
                    $expiresAt = now()->addSeconds((int) $longResp['expires_in'])->toIso8601String();
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Facebook long-lived token exchange failed; falling back to short-lived token.', [
                'error' => $e->getMessage(),
            ]);
        }

        // Fetch the Pages this user manages — Page Access Tokens here inherit the long-lived lifetime.
        $pagesResp = $this->http()->get("https://graph.facebook.com/$version/me/accounts", [
            'access_token' => $longUserToken,
            'fields' => 'id,name,access_token,tasks',
        ])->throw()->json();

        $pages = $pagesResp['data'] ?? [];
        $payloads = [];

        foreach ($pages as $page) {
            $pageToken = $page['access_token'] ?? null;
            $pageId = $page['id'] ?? null;
            if (! $pageToken || ! $pageId) {
                continue;
            }
            $payloads[] = [
                'provider_user_id' => (string) $pageId,
                'provider_account_name' => $page['name'] ?? null,
                'access_token' => $pageToken,
                'token_expires_at' => $expiresAt,
                'scopes' => $page['tasks'] ?? ['pages_manage_posts'],
            ];
        }

        if (! empty($payloads)) {
            return $payloads;
        }

        // Fallback: no Pages — save the personal profile (won't be able to post to a Page).
        $profile = $this->http()->get("https://graph.facebook.com/$version/me", [
            'access_token' => $longUserToken,
            'fields' => 'id,name,email',
        ])->throw()->json();

        return [[
            'provider_user_id' => (string) ($profile['id'] ?? Str::random(12)),
            'provider_account_name' => $profile['name'] ?? null,
            'access_token' => $longUserToken,
            'token_expires_at' => $expiresAt,
            'scopes' => ['public_profile', 'email'],
        ]];
    }

    private function renderClosePopup(bool $success, ?string $message = null): Response
    {
        $payload = json_encode([
            'type' => 'social-oauth',
            'success' => $success,
            'message' => $message,
        ]);
        $statusText = $success ? 'Connected!' : 'Could not connect';
        $bodyMsg = $success
            ? 'Your account is now linked. You can close this window.'
            : htmlspecialchars($message ?? 'Something went wrong.', ENT_QUOTES);

        $html = <<<HTML
<!DOCTYPE html>
<html><head><title>$statusText</title>
<style>
    body { font-family: system-ui, sans-serif; padding: 3rem; text-align: center; color: #1f2937; }
    .ok { color: #047857; } .err { color: #b91c1c; }
</style></head><body>
<h2 class="{$this->cssClass($success)}">$statusText</h2>
<p>$bodyMsg</p>
<script>
    try {
        const data = $payload;
        if (window.opener && !window.opener.closed) {
            window.opener.postMessage(data, window.location.origin);
        }
    } catch (e) {}
    setTimeout(() => window.close(), 1500);
</script>
</body></html>
HTML;

        return response($html);
    }

    private function cssClass(bool $success): string
    {
        return $success ? 'ok' : 'err';
    }
}
