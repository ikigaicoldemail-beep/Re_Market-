<?php

namespace App\Integrations\Social;

use App\Contracts\SocialPlatformClientInterface;
use App\Models\SocialAccount;
use App\Models\SocialPost;
use RuntimeException;

class FacebookSocialClient implements SocialPlatformClientInterface
{
    public function platform(): string
    {
        return 'facebook';
    }

    public function publish(SocialAccount $account, SocialPost $post): array
    {
        if (app()->environment('production')) {
            throw new RuntimeException('Placeholder Facebook publisher cannot be used in production.');
        }

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
