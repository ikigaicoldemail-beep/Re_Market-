<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $viewerId = $request->user()?->id;
        $viewerParticipant = $this->participants->firstWhere('user_id', $viewerId);

        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'type' => $this->type,
            'last_message_id' => $this->last_message_id,
            'last_message_at' => $this->last_message_at,
            'product' => new ProductResource($this->whenLoaded('product')),
            'participants' => ConversationParticipantResource::collection($this->whenLoaded('participants')),
            'last_message' => new ChatMessageResource($this->whenLoaded('lastMessage')),
            'unread_count' => $viewerParticipant?->unread_count ?? 0,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
