<?php

namespace App\Services;

use App\Jobs\PublishScheduledPostJob;
use App\Models\ScheduledPost;
use App\Models\SocialPost;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ScheduledPostService
{
    public function list(User $user): LengthAwarePaginator
    {
        return ScheduledPost::query()
            ->whereHas('socialPost', fn ($query) => $query->where('user_id', $user->id))
            ->with(['socialPost.product.images', 'socialPost.socialAccount'])
            ->orderBy('scheduled_for')
            ->paginate(15);
    }

    public function schedule(User $user, SocialPost $socialPost, array $data): ScheduledPost
    {
        $this->assertOwnership($user, $socialPost);

        if (in_array($socialPost->status, ['posted', 'processing'], true)) {
            throw ValidationException::withMessages([
                'social_post_id' => ['This social post can no longer be scheduled.'],
            ]);
        }

        return DB::transaction(function () use ($socialPost, $data) {
            $socialPost->update(['status' => 'queued']);

            return ScheduledPost::updateOrCreate(
                ['social_post_id' => $socialPost->id],
                [
                    'scheduled_for' => $data['scheduled_for'],
                    'status' => 'scheduled',
                    'attempts' => 0,
                    'last_attempt_at' => null,
                    'processed_at' => null,
                    'cancelled_at' => null,
                    'failure_reason' => null,
                ]
            )->load(['socialPost.product.images', 'socialPost.socialAccount']);
        });
    }

    public function update(ScheduledPost $scheduledPost, array $data): ScheduledPost
    {
        if (! in_array($scheduledPost->status, ['scheduled', 'failed'], true)) {
            throw ValidationException::withMessages([
                'scheduled_post' => ['Only scheduled or failed posts can be edited.'],
            ]);
        }

        $scheduledPost->update([
            'scheduled_for' => $data['scheduled_for'],
            'status' => 'scheduled',
            'failure_reason' => null,
            'cancelled_at' => null,
        ]);

        $scheduledPost->socialPost()->update([
            'status' => 'queued',
        ]);

        return $scheduledPost->fresh(['socialPost.product.images', 'socialPost.socialAccount']);
    }

    public function cancel(ScheduledPost $scheduledPost): ScheduledPost
    {
        if (in_array($scheduledPost->status, ['posted', 'processing'], true)) {
            throw ValidationException::withMessages([
                'scheduled_post' => ['This scheduled post can no longer be cancelled.'],
            ]);
        }

        return DB::transaction(function () use ($scheduledPost) {
            $scheduledPost->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'failure_reason' => null,
            ]);

            $scheduledPost->socialPost()->update([
                'status' => 'cancelled',
            ]);

            return $scheduledPost->fresh(['socialPost.product.images', 'socialPost.socialAccount']);
        });
    }

    public function dispatchDue(): int
    {
        $duePosts = ScheduledPost::query()
            ->whereIn('status', ['scheduled', 'failed'])
            ->where('scheduled_for', '<=', now())
            ->get();

        foreach ($duePosts as $scheduledPost) {
            $scheduledPost->update(['status' => 'queued']);
            PublishScheduledPostJob::dispatch($scheduledPost->id);
        }

        return $duePosts->count();
    }

    public function process(ScheduledPost $scheduledPost): ScheduledPost
    {
        if ($scheduledPost->status === 'cancelled') {
            return $scheduledPost;
        }

        return DB::transaction(function () use ($scheduledPost) {
            $scheduledPost->increment('attempts');
            $scheduledPost->update([
                'status' => 'processing',
                'last_attempt_at' => now(),
            ]);

            try {
                app(SocialPostingService::class)->publish($scheduledPost->socialPost);

                $scheduledPost->update([
                    'status' => 'posted',
                    'processed_at' => now(),
                    'failure_reason' => null,
                ]);
            } catch (\Throwable $throwable) {
                $scheduledPost->update([
                    'status' => 'failed',
                    'failure_reason' => $throwable->getMessage(),
                ]);

                throw $throwable;
            }

            return $scheduledPost->fresh(['socialPost.product.images', 'socialPost.socialAccount']);
        });
    }

    private function assertOwnership(User $user, SocialPost $socialPost): void
    {
        if ($socialPost->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'social_post_id' => ['You can only schedule your own social posts.'],
            ]);
        }
    }
}
