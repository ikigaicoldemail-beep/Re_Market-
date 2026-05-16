<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'seller_id' => $this->seller_id,
            'product_title' => $this->product_title,
            'product_slug' => $this->product_slug,
            'product_image_path' => $this->product_image_path,
            'product_image_url' => $this->product_image_path
                ? $request->getSchemeAndHttpHost().'/storage/products/'.ltrim((string) $this->product_image_path, '/')
                : null,
            'product_condition_label' => $this->product_condition_label,
            'quantity' => $this->quantity,
            'unit_price_amount' => $this->unit_price_amount,
            'line_total_amount' => $this->line_total_amount,
            'fulfillment_status' => $this->fulfillment_status,
        ];
    }
}
