<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SocialPostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'platform' => $this->platform,
            'product_id' => $this->product_id,
            'social_account_id' => $this->social_account_id,
            'caption' => $this->caption,
            'media_payload' => $this->media_payload,
            'status' => $this->status,
            'provider_post_id' => $this->provider_post_id,
            'provider_response' => $this->provider_response,
            'error_message' => $this->error_message,
            'posted_at' => $this->posted_at,
            'product' => new ProductResource($this->whenLoaded('product')),
            'social_account' => new SocialAccountResource($this->whenLoaded('socialAccount')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
