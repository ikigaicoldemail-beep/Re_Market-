<?php

namespace App\Services;

use App\Contracts\AiImageEmbeddingClientInterface;
use App\Models\AiSearchLog;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductImageEmbedding;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AiSimilaritySearchService
{
    public function __construct(private readonly AiImageEmbeddingClientInterface $client)
    {
    }

    public function search(User $user, array $data): array
    {
        $topK = $data['top_k'] ?? 10;

        [$queryVector, $sourceProduct, $sourceImage, $queryImagePath] = $this->resolveQueryVector($data);

        $log = AiSearchLog::create([
            'user_id' => $user->id,
            'product_id' => $sourceProduct?->id,
            'product_image_id' => $sourceImage?->id,
            'query_image_path' => $queryImagePath,
            'provider' => $this->client->provider(),
            'status' => 'processing',
            'top_k' => $topK,
            'request_payload' => [
                'product_id' => $sourceProduct?->id,
                'product_image_id' => $sourceImage?->id,
                'uploaded_image' => $queryImagePath !== null,
            ],
        ]);

        try {
            $results = $this->rankProducts($queryVector, $sourceProduct, $topK);

            $log->update([
                'status' => 'completed',
                'embedding_version' => 'v1',
                'result_count' => $results->count(),
                'response_payload' => [
                    'product_ids' => $results->pluck('id')->all(),
                    'scores' => $results->pluck('similarity_score')->all(),
                ],
                'searched_at' => now(),
            ]);

            return [$log->fresh(), $results];
        } catch (\Throwable $throwable) {
            $log->update([
                'status' => 'failed',
                'error_message' => $throwable->getMessage(),
                'searched_at' => now(),
            ]);

            throw $throwable;
        }
    }

    private function resolveQueryVector(array $data): array
    {
        if (! empty($data['product_image_id'])) {
            $productImage = ProductImage::with(['product', 'embedding'])->findOrFail($data['product_image_id']);

            if (! $productImage->embedding?->embedding_vector) {
                throw ValidationException::withMessages([
                    'product_image_id' => ['The selected product image is not indexed yet.'],
                ]);
            }

            return [$productImage->embedding->embedding_vector, $productImage->product, $productImage, null];
        }

        if (! empty($data['product_id'])) {
            $product = Product::with(['images.embedding'])->findOrFail($data['product_id']);
            $productImage = $product->images->firstWhere('is_primary', true) ?? $product->images->first();

            if (! $productImage?->embedding?->embedding_vector) {
                throw ValidationException::withMessages([
                    'product_id' => ['The selected product does not have an indexed image yet.'],
                ]);
            }

            return [$productImage->embedding->embedding_vector, $product, $productImage, null];
        }

        /** @var UploadedFile $uploadedImage */
        $uploadedImage = $data['image'];
        $path = $uploadedImage->store('search-queries', 'product-images');
        $absolutePath = Storage::disk('product-images')->path($path);
        $embedding = $this->client->embedFromPath($absolutePath);

        return [$embedding['vector'], null, null, $path];
    }

    private function rankProducts(array $queryVector, ?Product $sourceProduct, int $topK): Collection
    {
        $candidates = Product::query()
            ->with(['images.embedding', 'store', 'category', 'condition', 'user.profile'])
            ->where('status', 'published')
            ->where('visibility', 'public')
            ->when($sourceProduct, fn ($query) => $query->whereKeyNot($sourceProduct->id))
            ->get()
            ->map(function (Product $product) use ($queryVector, $sourceProduct) {
                $productVector = $product->images
                    ->map(fn ($image) => $image->embedding?->embedding_vector)
                    ->filter()
                    ->first();

                $vectorScore = $productVector
                    ? $this->cosineSimilarity($queryVector, $productVector)
                    : 0.0;

                $heuristicScore = 0.0;

                if ($sourceProduct) {
                    if ($sourceProduct->category_id && $product->category_id === $sourceProduct->category_id) {
                        $heuristicScore += 0.15;
                    }

                    if ($sourceProduct->product_condition_id && $product->product_condition_id === $sourceProduct->product_condition_id) {
                        $heuristicScore += 0.10;
                    }

                    if ($sourceProduct->price_amount > 0 && $product->price_amount > 0) {
                        $delta = abs($sourceProduct->price_amount - $product->price_amount) / max($sourceProduct->price_amount, 1);
                        $heuristicScore += max(0, 0.15 - min($delta, 1) * 0.15);
                    }
                }

                $product->similarity_score = round(($vectorScore * 0.85) + $heuristicScore, 4);

                return $product;
            })
            ->sortByDesc('similarity_score')
            ->take($topK)
            ->values();

        return $candidates;
    }

    private function cosineSimilarity(array $a, array $b): float
    {
        $length = min(count($a), count($b));

        if ($length === 0) {
            return 0.0;
        }

        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        for ($i = 0; $i < $length; $i++) {
            $dot += $a[$i] * $b[$i];
            $normA += $a[$i] ** 2;
            $normB += $b[$i] ** 2;
        }

        if ($normA == 0.0 || $normB == 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }
}
