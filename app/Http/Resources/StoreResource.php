<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'country_code' => $this->country_code,
            'state' => $this->state,
            'city' => $this->city,
            'address_line' => $this->address_line,
            'status' => $this->status,
            'is_verified' => $this->is_verified,
            'published_at' => $this->published_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'seller' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
