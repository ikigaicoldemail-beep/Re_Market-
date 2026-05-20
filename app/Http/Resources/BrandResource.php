<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'logo_url' => $this->logo_url,
            'status' => $this->status,
            'sort_order' => $this->sort_order,
            'products_count' => $this->when(isset($this->products_count), fn () => (int) $this->products_count),
        ];
    }
}
