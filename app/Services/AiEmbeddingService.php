<?php

namespace App\Services;

use App\Contracts\AiImageEmbeddingClientInterface;
use App\Models\ProductImage;
use App\Models\ProductImageEmbedding;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AiEmbeddingService
{
    public function __construct(private readonly AiImageEmbeddingClientInterface $client) {}

    public function generateForProductImage(ProductImage $productImage): ProductImageEmbedding
    {
        $absolutePath = Storage::disk($productImage->disk)->path($productImage->path);
        $result = $this->client->embedFromPath($absolutePath);

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
