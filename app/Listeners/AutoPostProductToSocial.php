<?php

namespace App\Listeners;

use App\Events\ProductCreated;
use App\Jobs\PublishSocialPostJob;
use App\Models\SocialAccount;
use App\Models\SocialPost;
use App\Services\SocialPostingService;
use Illuminate\Support\Facades\Storage;

class AutoPostProductToSocial
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     */
    public function handle(ProductCreated $event): void
    {
        $product = $event->product;

        // Check if auto_post is set and product is published
        if (empty($product->auto_post) || $product->status !== 'published') {
            return;
        }

        // Determine which platforms to post to
        $platforms = $product->auto_post === 'all'
            ? ['facebook', 'tiktok']
            : [$product->auto_post];

        // Post to each platform
        foreach ($platforms as $platform) {
            $account = $this->resolveAccount($platform, $product->user_id);

            // Skip if no usable account for this platform
            if (! $account) {
                continue;
            }

            $primaryImage = $product->images()
                ->orderByDesc('is_primary')
                ->orderBy('sort_order')
                ->first();

            $imagePath = $primaryImage?->path ?? $product->image;
            $imageDisk = $primaryImage?->disk ?? ($imagePath ? 'product-images' : null);
            $imageUrl = ($imagePath && $imageDisk) ? Storage::disk($imageDisk)->url($imagePath) : null;

            $post = SocialPost::create([
                'user_id' => $product->user_id,
                'product_id' => $product->id,
                'social_account_id' => $account->id,
                'platform' => $platform,
                'caption' => SocialPostingService::defaultCaption($product),
                'media_payload' => [
                    'image' => $imageUrl,
                    'image_url' => $imageUrl,
                    'image_path' => $imagePath,
                    'image_disk' => $imageDisk,
                    'title' => $product->title,
                    'description' => $product->description,
                    'price_amount' => $product->price_amount,
                    'currency' => $product->currency,
                    'location_city' => $product->location_city,
                ],
                'status' => 'queued',
            ]);

            // Delay so the follow-up POST /products/{id}/images request has time
            // to attach images before the worker reads the product.
            PublishSocialPostJob::dispatch($post->id)->delay(now()->addSeconds(15));
        }
    }

    private function resolveAccount(string $platform, int $userId): ?SocialAccount
    {
        if ($platform === 'facebook') {
            $marketplaceId = config('services.facebook.marketplace_social_account_id');

            if ($marketplaceId) {
                return SocialAccount::query()
                    ->whereKey($marketplaceId)
                    ->where('platform', 'facebook')
                    ->where('status', 'active')
                    ->first();
            }
        }

        return SocialAccount::query()
            ->where('user_id', $userId)
            ->where('platform', $platform)
            ->where('status', 'active')
            ->first();
    }
}
