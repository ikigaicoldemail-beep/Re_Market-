<?php

namespace App\Policies;

use App\Models\ScheduledPost;
use App\Models\User;

class ScheduledPostPolicy
{
    public function view(User $user, ScheduledPost $scheduledPost): bool
    {
        return $scheduledPost->socialPost?->user_id === $user->id;
    }

    public function update(User $user, ScheduledPost $scheduledPost): bool
    {
        return $scheduledPost->socialPost?->user_id === $user->id;
    }
}
