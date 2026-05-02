<?php

namespace App\Jobs;

use App\Models\ProductImage;
use App\Services\AiEmbeddingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateProductImageEmbeddingJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly int $productImageId)
    {
        $this->onQueue('ai');
    }

    public function handle(AiEmbeddingService $embeddingService): void
    {
        $productImage = ProductImage::find($this->productImageId);

        if (! $productImage) {
            return;
        }

        $productImage->update(['ai_embedding_status' => 'processing']);

        $embeddingService->generateForProductImage($productImage);
    }
}
