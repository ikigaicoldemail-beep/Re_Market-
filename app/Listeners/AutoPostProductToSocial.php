<?php

namespace App\Listeners;

use App\Events\ProductCreated;
use App\Models\SocialPost;
use App\Services\SocialPostingService;
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

        $platform = $product->auto_post;

        $existing = SocialPost::query()
            ->where('product_id', $product->id)
            ->where('platform', $platform)
            ->whereIn('status', ['queued', 'processing', 'posted'])
            ->exists();

        if ($existing || ! $product->images()->exists()) {
            return;
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
            'social_account_id' => null,
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

        $this->socialPostingService->publish($post);
    }
}
