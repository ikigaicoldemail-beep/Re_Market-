<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'currency' => $this->currency,
            'subtotal_amount' => $this->subtotal_amount,
            'discount_amount' => $this->discount_amount,
            'shipping_amount' => $this->shipping_amount,
            'total_amount' => $this->total_amount,
            'notes' => $this->notes,
            'placed_at' => $this->placed_at,
            'paid_at' => $this->paid_at,
            'cancelled_at' => $this->cancelled_at,
            'completed_at' => $this->completed_at,
            'buyer' => new UserResource($this->whenLoaded('buyer')),
            'address' => new AddressResource($this->whenLoaded('address')),
            'store' => new StoreResource($this->whenLoaded('store')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'created_at' => $this->created_at,
        ];
    }
}
