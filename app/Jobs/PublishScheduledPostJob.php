<?php

namespace App\Jobs;

use App\Models\ScheduledPost;
use App\Services\ScheduledPostService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class PublishScheduledPostJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(private readonly int $scheduledPostId)
    {
        $this->onQueue('social');
    }

    public function handle(ScheduledPostService $scheduledPostService): void
    {
        $scheduledPost = ScheduledPost::with('socialPost')->find($this->scheduledPostId);

        if (! $scheduledPost) {
            return;
        }

        $scheduledPostService->process($scheduledPost);
    }

    public function failed(?Throwable $exception): void
    {
        $scheduledPost = ScheduledPost::find($this->scheduledPostId);

        if (! $scheduledPost) {
            return;
        }

        $scheduledPost->update([
            'status' => 'failed',
            'failure_reason' => $exception?->getMessage(),
        ]);
    }
}
