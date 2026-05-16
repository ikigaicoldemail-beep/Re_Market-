<?php

namespace App\Services;

use App\Jobs\PublishSocialPostJob;
use App\Models\Product;
use App\Models\SharedProduct;
use App\Models\SocialAccount;
use App\Models\SocialPost;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SocialPostingService
{
    public function __construct(private readonly SocialPlatformManager $platformManager) {}

    public function createPost(User $user, Product $product, SocialAccount $account, array $data): SocialPost
    {
        $this->assertAccountOwnership($user, $account);
        $this->assertProductOwnership($user, $product);

        $post = SocialPost::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'social_account_id' => $account->id,
            'platform' => $account->platform,
            'caption' => $data['caption'] ?? $this->defaultCaption($product),
            'media_payload' => array_merge(
                $this->resolveImagePayload($product),
                [
                    'title' => $product->title,
                    'price_amount' => $product->price_amount,
                    'currency' => $product->currency,
                ]
            ),
            'status' => ($data['publish_now'] ?? false) ? 'queued' : 'draft',
        ]);

        if (($data['publish_now'] ?? false) === true) {
            PublishSocialPostJob::dispatch($post->id);
        }

        return $post->fresh(['product.images', 'socialAccount']);
    }

    public function publish(SocialPost $post): SocialPost
    {
        $post->loadMissing(['socialAccount', 'product']);

        if (! $post->socialAccount || $post->socialAccount->status !== 'active') {
            throw ValidationException::withMessages([
                'social_account_id' => ['The social account is not active.'],
            ]);
        }

        $post->update(['status' => 'processing', 'error_message' => null]);

        try {
            $client = $this->platformManager->forPlatform($post->platform);
            $result = $client->publish($post->socialAccount, $post);

            $post->update([
                'status' => 'posted',
                'provider_post_id' => $result['provider_post_id'] ?? null,
                'provider_response' => $result['response'] ?? [],
                'posted_at' => now(),
            ]);
        } catch (\Throwable $throwable) {
            $post->update([
                'status' => 'failed',
                'error_message' => $throwable->getMessage(),
            ]);

            throw $throwable;
        }

        return $post->fresh(['product.images', 'socialAccount']);
    }

    public function shareProduct(User $user, Product $product, array $data): SharedProduct
    {
        return SharedProduct::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'platform' => $data['platform'] ?? null,
            'destination' => $data['destination'] ?? null,
            'status' => 'shared',
            'metadata' => [
                'share_url' => rtrim(config('app.frontend_url', config('app.url')), '/').'/products/'.$product->id,
            ],
            'shared_at' => now(),
        ])->load('product.images');
    }

    private function assertAccountOwnership(User $user, SocialAccount $account): void
    {
        if ($account->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'social_account_id' => ['You can only use your own connected social accounts.'],
            ]);
        }
    }

    private function assertProductOwnership(User $user, Product $product): void
    {
        if ($product->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'product_id' => ['You can only publish your own products to social platforms.'],
            ]);
        }
    }

    public function scheduleProductPost(User $user, Product $product, array $data): \App\Models\ScheduledPost
    {
        $this->assertProductOwnership($user, $product);

        $account = $this->getTargetAccount($user, $data);

        $post = SocialPost::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'social_account_id' => $account->id,
            'platform' => $account->platform,
            'caption' => $data['caption'] ?? $this->defaultCaption($product),
            'media_payload' => array_merge(
                $this->resolveImagePayload($product),
                [
                    'title' => $product->title,
                    'price_amount' => $product->price_amount,
                    'currency' => $product->currency,
                ]
            ),
            'status' => 'queued',
        ]);

        $scheduledDatetime = \Carbon\Carbon::parse($data['scheduled_date'].' '.$data['scheduled_time']);

        $scheduledPost = \App\Models\ScheduledPost::create([
            'social_post_id' => $post->id,
            'scheduled_for' => $scheduledDatetime,
            'status' => 'scheduled',
        ]);

        return $scheduledPost->load(['socialPost.product.images', 'socialPost.socialAccount']);
    }

    private function getTargetAccount(User $user, array $data): SocialAccount
    {
        if (($data['post_to'] ?? 'marketplace') === 'user_account') {
            $account = SocialAccount::findOrFail($data['social_account_id']);
            $this->assertAccountOwnership($user, $account);
            return $account;
        }

        $accountId = config('services.facebook.marketplace_social_account_id');

        if (! $accountId) {
            throw ValidationException::withMessages([
                'post_to' => ['Marketplace Facebook account is not configured.'],
            ]);
        }

        try {
            $account = SocialAccount::whereKey($accountId)
                ->where('platform', 'facebook')
                ->firstOrFail();
        } catch (ModelNotFoundException) {
            throw ValidationException::withMessages([
                'post_to' => ['Configured marketplace Facebook account was not found.'],
            ]);
        }

        if ($account->status !== 'active') {
            throw ValidationException::withMessages([
                'post_to' => ['Marketplace account is not connected or active.'],
            ]);
        }

        return $account;
    }

    private function defaultCaption(Product $product): string
    {
        return "{$product->title} - {$product->currency} {$product->price_amount}";
    }

    private function resolveImagePayload(Product $product): array
    {
        $primary = $product->images()
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->first();

        $path = $primary?->path ?? $product->image;
        $disk = $primary?->disk ?? ($path ? 'product-images' : null);

        if (! $path || ! $disk) {
            return [
                'image' => null,
                'image_url' => null,
                'image_path' => null,
                'image_disk' => null,
            ];
        }

        $url = Storage::disk($disk)->url($path);

        return [
            'image' => $url,
            'image_url' => $url,
            'image_path' => $path,
            'image_disk' => $disk,
        ];
    }
}
