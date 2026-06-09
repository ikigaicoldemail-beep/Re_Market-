<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'user_id' => $this->user_id,
            'rating' => $this->rating,
            'title' => $this->title,
            'body' => $this->body,
            'status' => $this->status,
            'seller_reply' => $this->seller_reply,
            'seller_replied_at' => $this->seller_replied_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'reviewer' => new UserResource($this->whenLoaded('reviewer')),
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
