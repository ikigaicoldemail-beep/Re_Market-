<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationParticipantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this->user_id,
            'joined_at' => $this->joined_at,
            'last_read_message_id' => $this->last_read_message_id,
            'last_read_at' => $this->last_read_at,
            'is_muted' => $this->is_muted,
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
