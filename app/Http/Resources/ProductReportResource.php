<?php

namespace App\Http\Resources;

use App\Models\ProductReport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'reporter_id' => $this->reporter_id,
            'reason' => $this->reason,
            'reason_label' => ProductReport::REASONS[$this->reason] ?? $this->reason,
            'details' => $this->details,
            'status' => $this->status,
            'resolved_by' => $this->resolved_by,
            'resolved_at' => $this->resolved_at,
            'resolution_note' => $this->resolution_note,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'product' => new ProductResource($this->whenLoaded('product')),
            'reporter' => new UserResource($this->whenLoaded('reporter')),
            'resolver' => new UserResource($this->whenLoaded('resolver')),
        ];
    }
}
