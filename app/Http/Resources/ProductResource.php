<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'store_id' => $this->store_id,
            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id,
            'product_condition_id' => $this->product_condition_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'description' => $this->description,
            'specs' => $this->specs,
            'price_amount' => $this->price_amount,
            'original_price_amount' => $this->original_price_amount,
            'currency' => $this->currency,
            'stock_quantity' => $this->stock_quantity,
            'location_country_code' => $this->location_country_code,
            'location_state' => $this->location_state,
            'location_city' => $this->location_city,
            'status' => $this->status,
            'moderation_status' => $this->moderation_status,
            'visibility' => $this->visibility,
            'allow_offers' => $this->allow_offers,
            'is_featured' => (bool) $this->is_featured,
            'featured_at' => $this->featured_at,
            'schedule_at' => $this->schedule_at,
            'auto_post' => $this->auto_post,
            'published_at' => $this->published_at,
            'similarity_score' => $this->when(isset($this->similarity_score), $this->similarity_score),
            'reviews_count' => $this->when(isset($this->reviews_count), $this->reviews_count),
            'reviews_avg_rating' => $this->when(isset($this->reviews_avg_rating), round((float) $this->reviews_avg_rating, 2)),
            'similarity' => $this->when($this->getAttribute('visual_similarity') !== null, $this->getAttribute('visual_similarity')),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'store' => new StoreResource($this->whenLoaded('store')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'brand' => new BrandResource($this->whenLoaded('brand')),
            'condition' => new ProductConditionResource($this->whenLoaded('condition')),
            'seller' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
