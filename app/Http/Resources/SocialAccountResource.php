<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SocialAccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'platform' => $this->platform,
            'provider_user_id' => $this->provider_user_id,
            'provider_account_name' => $this->provider_account_name,
            'scopes' => $this->scopes,
            'status' => $this->status,
            'token_expires_at' => $this->token_expires_at,
            'last_synced_at' => $this->last_synced_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
