<?php

namespace App\Policies;

use App\Models\SocialPost;
use App\Models\User;

class SocialPostPolicy
{
    public function view(User $user, SocialPost $socialPost): bool
    {
        return $user->id === $socialPost->user_id;
    }

    public function update(User $user, SocialPost $socialPost): bool
    {
        return $user->id === $socialPost->user_id;
    }
}
