<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SharedProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'platform' => $this->platform,
            'destination' => $this->destination,
            'status' => $this->status,
            'metadata' => $this->metadata,
            'shared_at' => $this->shared_at,
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
