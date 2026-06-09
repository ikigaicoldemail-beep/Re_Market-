<?php

namespace App\Services;

use App\Models\Product;
use App\Models\SharedProduct;
use App\Models\SocialAccount;
use App\Models\SocialPost;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SocialPostingService
{
    public function __construct(private readonly SocialPlatformManager $platformManager) {}

    public function createPost(User $user, Product $product, ?SocialAccount $account, array $data): SocialPost
    {
        $this->assertProductOwnership($user, $product);

        if ($account) {
            $this->assertAccountOwnership($user, $account);
        }

        $post = SocialPost::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'social_account_id' => $account?->id,
            'platform' => $account?->platform ?? $data['platform'] ?? 'facebook',
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
            $this->publish($post);
        }

        return $post->fresh(['product.images', 'socialAccount']);
    }

    public function publish(SocialPost $post): SocialPost
    {
        $post->loadMissing(['socialAccount', 'product.images']);

        if ($post->status === 'posted' && $post->provider_post_id) {
            return $post;
        }

        // Simulated provider publish for classroom scope. Re-resolve the primary image first
        // so the stored social post still reflects the final product listing.
        if ($post->product) {
            $payload = $post->media_payload ?? [];
            $hasImage = ! empty($payload['image_path']) && ! empty($payload['image_disk']);
            if (! $hasImage) {
                $refreshed = $this->resolveImagePayload($post->product);
                if (! empty($refreshed['image_path'])) {
                    $post->media_payload = array_merge($payload, $refreshed);
                    $post->save();
                }
            }
        }

        $post->update([
            'status' => 'posted',
            'provider_post_id' => 'simulated-facebook-'.$post->id,
            'provider_response' => [
                'simulated' => true,
                'platform' => $post->platform,
            ],
            'error_message' => null,
            'posted_at' => now(),
        ]);

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
            'social_account_id' => $account?->id,
            'platform' => $account?->platform ?? 'facebook',
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

    private function getTargetAccount(User $user, array $data): ?SocialAccount
    {
        if (($data['post_to'] ?? 'marketplace') === 'user_account' && ! empty($data['social_account_id'])) {
            $account = SocialAccount::findOrFail($data['social_account_id']);
            $this->assertAccountOwnership($user, $account);
            return $account;
        }

        return null;
    }

    public static function defaultCaption(Product $product): string
    {
        $lines = [$product->title];

        if (! empty($product->description)) {
            $lines[] = '';
            $lines[] = $product->description;
        }

        $lines[] = '';
        $lines[] = 'Price: '.$product->currency.' '.number_format((float) $product->price_amount, 2);

        $condition = $product->condition?->name;
        if ($condition) {
            $lines[] = 'Condition: '.$condition;
        }

        $location = collect([$product->location_city, $product->location_state, $product->location_country_code])
            ->filter()
            ->implode(', ');
        if ($location !== '') {
            $lines[] = 'Location: '.$location;
        }

        if ($product->allow_offers) {
            $lines[] = 'Open to offers';
        }

        $base = rtrim(config('app.frontend_url', config('app.url')), '/');
        if ($base !== '') {
            $lines[] = 'View: '.$base.'/products/'.$product->id;
        }

        return implode("\n", $lines);
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
