<?php

namespace App\Console\Commands;

use App\Models\SocialAccount;
use Composer\CaBundle\CaBundle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

/**
 * Refresh a Facebook social account by exchanging a short-lived user token into a long-lived one,
 * then fetching the matching Page Access Token (which inherits the ~60-day lifetime).
 *
 * Usage:
 *   php artisan fb:refresh-token {account_id} {short_user_token}
 *
 * The short user token can be obtained from Graph API Explorer (the "User Access Token" dropdown).
 * The account_id is the social_accounts.id of the existing Facebook account row to refresh.
 */
class RefreshFacebookTokenCommand extends Command
{
    protected $signature = 'fb:refresh-token {account_id : ID of the social_accounts row to update} {short_user_token : A short-lived USER access token from Graph API Explorer}';

    protected $description = 'Exchange a short-lived FB user token into a long-lived one and save the matching Page token';

    public function handle(): int
    {
        $accountId = (int) $this->argument('account_id');
        $shortUserToken = trim((string) $this->argument('short_user_token'));

        $account = SocialAccount::find($accountId);

        if (! $account || $account->platform !== 'facebook') {
            $this->error("Social account #{$accountId} not found or not a Facebook account.");
            return self::FAILURE;
        }

        $version = config('services.facebook.graph_version', 'v22.0');
        $clientId = config('services.facebook.client_id');
        $clientSecret = config('services.facebook.client_secret');

        if (! $clientId || ! $clientSecret) {
            $this->error('Facebook client_id/client_secret are not configured.');
            return self::FAILURE;
        }

        $this->line('Exchanging for long-lived user token...');

        $verify = class_exists(CaBundle::class) ? CaBundle::getBundledCaBundlePath() : true;

        $longResp = Http::asJson()
            ->withOptions(['verify' => $verify])
            ->get("https://graph.facebook.com/$version/oauth/access_token", [
                'grant_type' => 'fb_exchange_token',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'fb_exchange_token' => $shortUserToken,
            ]);

        if (! $longResp->successful()) {
            $this->error('Exchange failed: '.$longResp->body());
            return self::FAILURE;
        }

        $longUserToken = $longResp->json('access_token');
        $userExpires = $longResp->json('expires_in');

        if (! $longUserToken) {
            $this->error('No long-lived token returned.');
            return self::FAILURE;
        }

        $this->info('Long-lived user token acquired (expires in '.($userExpires ?? '∞').' s).');
        $this->line('Fetching matching Page Access Token for page id '.$account->provider_user_id.'...');

        $pagesResp = Http::withOptions(['verify' => $verify])
            ->get("https://graph.facebook.com/$version/me/accounts", [
                'access_token' => $longUserToken,
                'fields' => 'id,name,access_token,tasks',
            ]);

        if (! $pagesResp->successful()) {
            $this->error('Could not list pages: '.$pagesResp->body());
            return self::FAILURE;
        }

        $pages = $pagesResp->json('data') ?? [];
        $matchingPage = collect($pages)->firstWhere('id', (string) $account->provider_user_id);

        if (! $matchingPage || empty($matchingPage['access_token'])) {
            $this->error('No Page Access Token returned for page id '.$account->provider_user_id.'. Pages visible:');
            foreach ($pages as $p) {
                $this->line('  · '.$p['id'].' — '.($p['name'] ?? '?'));
            }
            return self::FAILURE;
        }

        $pageToken = $matchingPage['access_token'];

        // Save (encrypted).
        $account->access_token = Crypt::encryptString($pageToken);
        $account->status = 'active';
        $account->last_synced_at = now();
        // Long-lived page tokens from a long-lived user token typically last ~60 days; FB sometimes returns no expiry
        // for permanent ones. Fall back to 55 days as a conservative reminder.
        $account->token_expires_at = $userExpires
            ? now()->addSeconds((int) $userExpires)
            : now()->addDays(55);
        $account->save();

        $this->info('✓ Saved long-lived Page Access Token for account #'.$account->id.' ('.$matchingPage['name'].').');
        $this->info('  Will expire around: '.$account->token_expires_at->toDayDateTimeString());

        return self::SUCCESS;
    }
}
