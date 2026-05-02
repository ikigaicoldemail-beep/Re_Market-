<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'username' => $this->username,
            'avatar_path' => $this->avatar_path,
            'cover_path' => $this->cover_path,
            'bio' => $this->bio,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'country_code' => $this->country_code,
            'state' => $this->state,
            'city' => $this->city,
            'is_seller' => $this->is_seller,
            'profile_visibility' => $this->profile_visibility,
            'default_store_id' => $this->default_store_id,
            'default_store' => new StoreResource($this->whenLoaded('defaultStore')),
        ];
    }
}
