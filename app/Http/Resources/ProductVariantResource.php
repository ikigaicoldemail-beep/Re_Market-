<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'sku' => $this->sku,
            'attributes' => $this->attributes,
            'price_amount' => (int) $this->price_amount,
            'original_price_amount' => $this->original_price_amount !== null ? (int) $this->original_price_amount : null,
            'stock_quantity' => (int) $this->stock_quantity,
            'sort_order' => (int) $this->sort_order,
            'is_default' => (bool) $this->is_default,
        ];
    }
}
