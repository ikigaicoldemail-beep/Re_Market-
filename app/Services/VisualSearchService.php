<?php

namespace App\Services;

use App\Models\ListingVisualIndex;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;

class VisualSearchService
{
    public function indexProduct(Product $product): void
    {
        $product->loadMissing('images');

        $rows = $product->images
            ->filter(fn (ProductImage $image) => Storage::disk($image->disk)->exists($image->path))
            ->values();

        if ($rows->isEmpty()) {
            $this->removeProduct($product);
            return;
        }

        $nextFaissId = ((int) ListingVisualIndex::max('faiss_id')) + 1;
        $manifest = $rows->map(function (ProductImage $image) use ($product, &$nextFaissId): array {
            return [
                'listing_id' => $product->id,
                'product_image_id' => $image->id,
                'faiss_id' => $nextFaissId++,
                'path' => Storage::disk($image->disk)->path($image->path),
            ];
        });

        $result = $this->runVisualSearchScript('add', $manifest);

        $now = now();
        $indexed = collect($result['items'] ?? [])->map(fn (array $item): array => [
            'listing_id' => (int) $item['listing_id'],
            'product_image_id' => $item['product_image_id'] ?? null,
            'faiss_id' => (int) $item['faiss_id'],
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        if ($indexed !== []) {
            ListingVisualIndex::where('listing_id', $product->id)->delete();
            ListingVisualIndex::insert($indexed);
        }
    }

    public function rebuildIndex(?int $limit = null): array
    {
        $query = ProductImage::query()
            ->with('product')
            ->whereHas('product')
            ->orderBy('id');

        if ($limit !== null && $limit > 0) {
            $query->limit($limit);
        }

        $nextFaissId = 1;
        $manifest = collect();

        $query->chunkById(500, function ($images) use (&$nextFaissId, $manifest): void {
            foreach ($images as $image) {
                if (! Storage::disk($image->disk)->exists($image->path)) {
                    continue;
                }

                $manifest->push([
                    'listing_id' => $image->product_id,
                    'product_image_id' => $image->id,
                    'faiss_id' => $nextFaissId++,
                    'path' => Storage::disk($image->disk)->path($image->path),
                ]);
            }
        });

        $result = $this->runVisualSearchScript('rebuild', $manifest);

        ListingVisualIndex::truncate();

        collect($result['items'] ?? [])
            ->chunk(1000)
            ->each(function (Collection $chunk): void {
                $now = now();
                ListingVisualIndex::insert($chunk->map(fn (array $item): array => [
                    'listing_id' => (int) $item['listing_id'],
                    'product_image_id' => $item['product_image_id'] ?? null,
                    'faiss_id' => (int) $item['faiss_id'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all());
            });

        return [
            'indexed' => count($result['items'] ?? []),
            'failed' => (int) ($result['failed'] ?? 0),
        ];
    }

    public function search(UploadedFile $image, int $limit = 24): Collection
    {
        $result = $this->runVisualSearchScript('search', collect(), [
            '--image', $image->getRealPath(),
            '--limit', (string) max($limit * 4, $limit),
        ]);

        $matches = collect($result['matches'] ?? [])
            ->filter(fn (array $match) => isset($match['faiss_id']))
            ->keyBy(fn (array $match) => (int) $match['faiss_id']);

        if ($matches->isEmpty()) {
            return collect();
        }

        $indexRows = ListingVisualIndex::query()
            ->whereIn('faiss_id', $matches->keys()->all())
            ->with(['listing.images', 'listing.store', 'listing.category', 'listing.brand', 'listing.condition', 'listing.user.profile'])
            ->get();

        return $indexRows
            ->map(function (ListingVisualIndex $row) use ($matches) {
                $product = $row->listing;
                if (! $product || $product->status !== 'published' || $product->visibility !== 'public') {
                    return null;
                }

                $product->setAttribute('visual_similarity', round((float) ($matches[$row->faiss_id]['score'] ?? 0), 4));

                return $product;
            })
            ->filter()
            ->unique('id')
            ->sortByDesc('visual_similarity')
            ->take($limit)
            ->values();
    }

    public function removeProduct(Product $product): void
    {
        ListingVisualIndex::where('listing_id', $product->id)->delete();
    }

    private function runVisualSearchScript(string $mode, Collection $manifest, array $extraArgs = []): array
    {
        $this->ensureDirectories();

        $manifestPath = null;
        if ($manifest->isNotEmpty() || in_array($mode, ['add', 'rebuild'], true)) {
            $manifestPath = storage_path('app/visual-search/tmp/'.Str::uuid().'.jsonl');
            file_put_contents(
                $manifestPath,
                $manifest->map(fn (array $row) => json_encode($row, JSON_THROW_ON_ERROR))->implode(PHP_EOL).($manifest->isNotEmpty() ? PHP_EOL : '')
            );
        }

        $command = array_values(array_filter(array_merge([
            config('services.visual_search.python', 'python3'),
            base_path('scripts/visual_search.py'),
            $mode,
            '--index', storage_path('app/visual-search/faiss.index'),
            '--model', config('services.visual_search.model', 'ViT-B-32'),
            '--pretrained', config('services.visual_search.pretrained', 'laion2b_s34b_b79k'),
            '--device', config('services.visual_search.device', 'cpu'),
            $manifestPath ? '--manifest' : null,
            $manifestPath,
        ], $extraArgs), fn ($value) => $value !== null && $value !== ''));

        $process = new Process($command, base_path());
        $process->setTimeout((int) config('services.visual_search.timeout', 900));
        $process->run();

        if ($manifestPath && file_exists($manifestPath)) {
            @unlink($manifestPath);
        }

        if (! $process->isSuccessful()) {
            throw ValidationException::withMessages([
                'image' => ['Visual search is unavailable: '.trim($process->getErrorOutput() ?: $process->getOutput())],
            ]);
        }

        try {
            return json_decode($process->getOutput(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'image' => ['Visual search returned an invalid response.'],
            ]);
        }
    }

    private function ensureDirectories(): void
    {
        foreach (['app/visual-search', 'app/visual-search/tmp'] as $path) {
            if (! is_dir(storage_path($path))) {
                mkdir(storage_path($path), 0775, true);
            }
        }
    }
}
