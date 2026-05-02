<?php

namespace App\Contracts;

use App\Models\SocialAccount;
use App\Models\SocialPost;

interface SocialPlatformClientInterface
{
    public function platform(): string;

    /**
     * @return array{provider_post_id: ?string, response: array<string, mixed>}
     */
    public function publish(SocialAccount $account, SocialPost $post): array;
}
