<?php

namespace App\Jobs;

use App\Models\SocialPost;
use App\Services\SocialPostingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class PublishSocialPostJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(private readonly int $socialPostId)
    {
        $this->onQueue('social');
    }

    public function handle(SocialPostingService $socialPostingService): void
    {
        $post = SocialPost::find($this->socialPostId);

        if (! $post) {
            return;
        }

        $socialPostingService->publish($post);
    }

    public function failed(?Throwable $exception): void
    {
        $post = SocialPost::find($this->socialPostId);

        if (! $post) {
            return;
        }

        $post->update([
            'status' => 'failed',
            'error_message' => $exception?->getMessage(),
        ]);
    }
}
