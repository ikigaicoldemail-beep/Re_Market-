<?php

namespace App\Integrations\Social;

use App\Contracts\SocialPlatformClientInterface;
use App\Models\SocialAccount;
use App\Models\SocialPost;

class FacebookSocialClient implements SocialPlatformClientInterface
{
    public function platform(): string
    {
        return 'facebook';
    }

    public function publish(SocialAccount $account, SocialPost $post): array
    {
        return [
            'provider_post_id' => 'fb_'.uniqid(),
            'response' => [
                'platform' => 'facebook',
                'mode' => 'placeholder',
                'account' => $account->provider_account_name,
                'caption' => $post->caption,
            ],
        ];
    }
}
