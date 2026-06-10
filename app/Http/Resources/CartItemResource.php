<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_variant_id' => $this->product_variant_id,
            'variant_label' => $this->variant?->label,
            'quantity' => $this->quantity,
            'unit_price_amount' => $this->unit_price_amount,
            'line_total_amount' => $this->line_total_amount,
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
