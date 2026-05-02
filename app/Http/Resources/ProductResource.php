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
            'store_id' => $this->store_id,
            'category_id' => $this->category_id,
            'product_condition_id' => $this->product_condition_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'description' => $this->description,
            'price_amount' => $this->price_amount,
            'currency' => $this->currency,
            'stock_quantity' => $this->stock_quantity,
            'location_country_code' => $this->location_country_code,
            'location_state' => $this->location_state,
            'location_city' => $this->location_city,
            'status' => $this->status,
            'moderation_status' => $this->moderation_status,
            'visibility' => $this->visibility,
            'allow_offers' => $this->allow_offers,
            'schedule_at' => $this->schedule_at,
            'published_at' => $this->published_at,
            'similarity_score' => $this->when(isset($this->similarity_score), $this->similarity_score),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'store' => new StoreResource($this->whenLoaded('store')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'condition' => new ProductConditionResource($this->whenLoaded('condition')),
            'seller' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
