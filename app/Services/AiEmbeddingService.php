<?php

namespace App\Services;

use App\Contracts\AiImageEmbeddingClientInterface;
use App\Models\ProductImage;
use App\Models\ProductImageEmbedding;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class AiEmbeddingService
{
    public function __construct(private readonly AiImageEmbeddingClientInterface $client) {}

    public function generateForProductImage(ProductImage $productImage): ProductImageEmbedding
    {
        $tempFile = null;

        if ($productImage->disk === 'external') {
            // External URL (e.g. picsum.photos) — download to a temp file for embedding.
            $tempFile = tempnam(sys_get_temp_dir(), 'ai_embed_');
            $imageBytes = @file_get_contents($productImage->path);
            if ($imageBytes === false) {
                throw new RuntimeException("Failed to download external image: {$productImage->path}");
            }
            file_put_contents($tempFile, $imageBytes);
            $absolutePath = $tempFile;
        } else {
            $absolutePath = Storage::disk($productImage->disk)->path($productImage->path);
        }

        try {
            $result = $this->client->embedFromPath($absolutePath);
        } finally {
            if ($tempFile && file_exists($tempFile)) {
                unlink($tempFile);
            }
        }

        return DB::transaction(function () use ($productImage, $result) {
            $embedding = ProductImageEmbedding::updateOrCreate(
                ['product_image_id' => $productImage->id],
                [
                    'provider' => $this->client->provider(),
                    'embedding_model' => $result['model'] ?? null,
                    'embedding_vector' => $result['vector'],
                    'vector_hash' => hash('sha256', json_encode($result['vector'])),
                    'status' => 'completed',
                    'generated_at' => now(),
                ]
            );

            $productImage->update([
                'ai_embedding_status' => 'completed',
            ]);

            return $embedding;
        });
    }
}
