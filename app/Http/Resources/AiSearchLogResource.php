<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiSearchLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,
            'product_image_id' => $this->product_image_id,
            'query_image_path' => $this->query_image_path,
            'provider' => $this->provider,
            'status' => $this->status,
            'embedding_version' => $this->embedding_version,
            'top_k' => $this->top_k,
            'result_count' => $this->result_count,
            'error_message' => $this->error_message,
            'searched_at' => $this->searched_at,
        ];
    }
}
