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
            'url' => $this->buildUrl($request, $this->path),
            'urls' => $this->variantUrls($request),
            'disk' => $this->disk,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'sort_order' => $this->sort_order,
            'is_primary' => $this->is_primary,
        ];
    }

    private function variantUrls(Request $request): array
    {
        $variants = $this->variants ?? [];

        $out = [
            'original' => $this->buildUrl($request, $this->path),
        ];

        foreach (['thumb', 'card', 'large'] as $size) {
            if (! empty($variants[$size])) {
                $out[$size] = $this->buildUrl($request, $variants[$size]);
            }
            if (! empty($variants[$size.'_webp'])) {
                $out[$size.'_webp'] = $this->buildUrl($request, $variants[$size.'_webp']);
            }
        }

        return $out;
    }

    private function buildUrl(Request $request, string $relativePath): string
    {
        if ($this->disk === 'external') {
            return $relativePath;
        }

        $prefix = match ($this->disk) {
            'product-images' => '/storage/products/',
            'public' => '/storage/',
            default => '/storage/',
        };

        return $request->getSchemeAndHttpHost().$prefix.ltrim($relativePath, '/');
    }
}
