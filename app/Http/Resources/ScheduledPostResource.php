<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduledPostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'social_post_id' => $this->social_post_id,
            'scheduled_for' => $this->scheduled_for,
            'status' => $this->status,
            'attempts' => $this->attempts,
            'last_attempt_at' => $this->last_attempt_at,
            'processed_at' => $this->processed_at,
            'cancelled_at' => $this->cancelled_at,
            'failure_reason' => $this->failure_reason,
            'social_post' => new SocialPostResource($this->whenLoaded('socialPost')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
