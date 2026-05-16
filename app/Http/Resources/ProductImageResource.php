<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'path' => $this->path,
            'url' => $this->buildUrl($request),
            'disk' => $this->disk,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'sort_order' => $this->sort_order,
            'is_primary' => $this->is_primary,
        ];
    }

    private function buildUrl(Request $request): string
    {
        $prefix = match ($this->disk) {
            'product-images' => '/storage/products/',
            'public' => '/storage/',
            default => '/storage/',
        };

        return $request->getSchemeAndHttpHost().$prefix.ltrim((string) $this->path, '/');
    }
}
