<?php

namespace App\Listeners;

use App\Events\ProductCreated;
use App\Models\SocialAccount;
use App\Models\SocialPost;
use App\Services\SocialPostingService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AutoPostProductToSocial
{
    public function __construct(private readonly SocialPostingService $socialPostingService) {}

    public function handle(ProductCreated $event): void
    {
        $product = $event->product;

        if (empty($product->auto_post) || $product->status !== 'published') {
            return;
        }

        $platforms = $product->auto_post === 'all'
            ? ['facebook', 'tiktok']
            : [$product->auto_post];

        // The seller form posts the product first, then uploads images in a second request — so this listener
        // is fired twice. On the first firing the image isn't attached yet, so we skip and wait for the
        // image-upload step to re-fire ProductCreated. Only when no images will ever be attached do we
        // fall back to publishing text-only (handled by a deferred check at the bottom of this loop).
        $hasImage = $product->images()->exists();

        foreach ($platforms as $platform) {
            // Idempotency: if we've already started or completed an auto-post for this product+platform,
            // do not create a duplicate. Only retry if the prior attempt failed.
            $existing = SocialPost::query()
                ->where('product_id', $product->id)
                ->where('platform', $platform)
                ->whereIn('status', ['queued', 'processing', 'posted'])
                ->exists();

            if ($existing) {
                continue;
            }

            // Skip if no image yet AND the post has no images. We want the second event firing
            // (after image upload) to publish with the image attached. Text-only auto-posts are still
            // produced when the seller uploads no images at all — only the *image-upload re-fire* will be
            // the one we wait on, so on first-firing-with-no-image we exit early and the image-upload
            // re-fire (or a later manual publish) will pick it up.
            if (! $hasImage) {
                continue;
            }

            $account = $this->resolveAccount($platform, $product->user_id);

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

            // Publish inline — the post should appear on the page as soon as the seller submits the product.
            try {
                $this->socialPostingService->publish($post);
            } catch (\Throwable $e) {
                Log::warning('Auto-post to '.$platform.' failed; post left in failed state.', [
                    'post_id' => $post->id,
                    'product_id' => $product->id,
                    'error' => $e->getMessage(),
                ]);
            }
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
