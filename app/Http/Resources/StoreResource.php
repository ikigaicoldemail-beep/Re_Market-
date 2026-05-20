<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isFollowing = null;
        if ($request->user()) {
            $isFollowing = \DB::table('store_followers')
                ->where('user_id', $request->user()->id)
                ->where('store_id', $this->id)
                ->exists();
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'logo_url' => $this->logo_url,
            'banner_url' => $this->banner_url,
            'description' => $this->description,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'telegram_url' => $this->telegram_url,
            'messenger_url' => $this->messenger_url,
            'country_code' => $this->country_code,
            'state' => $this->state,
            'city' => $this->city,
            'address_line' => $this->address_line,
            'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
            'status' => $this->status,
            'is_verified' => $this->is_verified,
            'followers_count' => (int) ($this->followers_count ?? 0),
            'products_count' => $this->when(isset($this->products_count), fn () => (int) $this->products_count),
            'is_following' => $isFollowing,
            'published_at' => $this->published_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'seller' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
