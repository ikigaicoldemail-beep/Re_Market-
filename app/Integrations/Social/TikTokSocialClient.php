<?php

namespace App\Integrations\Social;

use App\Contracts\SocialPlatformClientInterface;
use App\Models\SocialAccount;
use App\Models\SocialPost;

class TikTokSocialClient implements SocialPlatformClientInterface
{
    public function platform(): string
    {
        return 'tiktok';
    }

    public function publish(SocialAccount $account, SocialPost $post): array
    {
        return [
            'provider_post_id' => 'tt_'.uniqid(),
            'response' => [
                'platform' => 'tiktok',
                'mode' => 'placeholder',
                'account' => $account->provider_account_name,
                'caption' => $post->caption,
            ],
        ];
    }
}
