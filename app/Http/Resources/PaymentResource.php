<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'provider' => $this->provider,
            'provider_reference' => $this->provider_reference,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'currency' => $this->currency,
            'amount' => $this->amount,
            'failure_code' => $this->failure_code,
            'failure_message' => $this->failure_message,
            'paid_at' => $this->paid_at,
            'created_at' => $this->created_at,
        ];
    }
}
