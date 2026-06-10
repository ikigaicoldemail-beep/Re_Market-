<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $attachmentUrl = $this->attachment_path
            ? '/storage/'.ltrim((string) $this->attachment_path, '/')
            : null;

        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'sender_id' => $this->sender_id,
            'type' => $this->type,
            'body' => $this->body,
            'attachment_path' => $this->attachment_path,
            'attachment_url' => $attachmentUrl,
            'sent_at' => $this->sent_at,
            'edited_at' => $this->edited_at,
            'sender' => new UserResource($this->whenLoaded('sender')),
        ];
    }
}
