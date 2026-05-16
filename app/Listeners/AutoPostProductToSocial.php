<?php

namespace App\Listeners;

use App\Events\ProductCreated;
use App\Jobs\PublishSocialPostJob;
use App\Models\SocialAccount;
use App\Models\SocialPost;
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
            // Find the user's first active connected account for this platform
            $account = SocialAccount::query()
                ->where('user_id', $product->user_id)
                ->where('platform', $platform)
                ->where('status', 'active')
                ->first();

            // Skip if no connected account for this platform
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
                'caption' => "{$product->title} - {$product->currency} {$product->price_amount}",
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

            // Dispatch the job to publish the post
            PublishSocialPostJob::dispatch($post->id);
        }
    }
}
