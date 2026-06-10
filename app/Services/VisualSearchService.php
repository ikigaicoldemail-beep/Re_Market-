<?php

namespace App\Services;

use App\Models\ListingVisualIndex;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;

class VisualSearchService
{
    private const MIN_RAW_SCORE = 0.18;
    private const CONFIDENCE_FLOOR = 0.18;
    private const CONFIDENCE_SPAN = 0.45;
    private const MIN_VISIBLE_CONFIDENCE = 0.35;

    public function indexProduct(Product $product): void
    {
        $this->ensureDirectories();

        $product->loadMissing('images');

        $tempFiles = [];
        $nextFaissId = ((int) ListingVisualIndex::max('faiss_id')) + 1;
        $manifest = $product->images
            ->map(function (ProductImage $image) use ($product, &$nextFaissId, &$tempFiles): ?array {
                $path = $this->resolveImagePath($image, $tempFiles);

                if (! $path) {
                    return null;
                }

                return [
                    'listing_id' => $product->id,
                    'product_image_id' => $image->id,
                    'faiss_id' => $nextFaissId++,
                    'path' => $path,
                ];
            })
            ->filter()
            ->values();

        if ($manifest->isEmpty()) {
            $this->cleanupTempFiles($tempFiles);
            $this->removeProduct($product);
            return;
        }

        try {
            $result = $this->runVisualSearchScript('add', $manifest);
        } finally {
            $this->cleanupTempFiles($tempFiles);
        }

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
        $this->ensureDirectories();

        $query = ProductImage::query()
            ->with('product')
            ->whereHas('product')
            ->orderBy('id');

        if ($limit !== null && $limit > 0) {
            $query->limit($limit);
        }

        $nextFaissId = 1;
        $manifest = collect();
        $tempFiles = [];

        $query->chunkById(500, function ($images) use (&$nextFaissId, $manifest, &$tempFiles): void {
            foreach ($images as $image) {
                $path = $this->resolveImagePath($image, $tempFiles);

                if (! $path) {
                    continue;
                }

                $manifest->push([
                    'listing_id' => $image->product_id,
                    'product_image_id' => $image->id,
                    'faiss_id' => $nextFaissId++,
                    'path' => $path,
                ]);
            }
        });

        try {
            $result = $this->runVisualSearchScript('rebuild', $manifest);
        } finally {
            $this->cleanupTempFiles($tempFiles);
        }

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
            '--limit', (string) max($limit * 8, $limit),
        ]);

        $matches = collect($result['matches'] ?? [])
            ->filter(fn (array $match) => isset($match['faiss_id']))
            ->filter(fn (array $match) => (float) ($match['score'] ?? 0) >= self::MIN_RAW_SCORE)
            ->keyBy(fn (array $match) => (int) $match['faiss_id']);

        if ($matches->isEmpty()) {
            return collect();
        }

        $queryConcept = $this->queryConcept($result['query_labels'] ?? []);

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

                $rawScore = (float) ($matches[$row->faiss_id]['score'] ?? 0);
                $product->setAttribute('visual_similarity_raw', round($rawScore, 4));
                $product->setAttribute('visual_similarity', $this->calibratedConfidence($rawScore));

                return $product;
            })
            ->filter()
            ->filter(fn (Product $product) => $this->matchesQueryConcept($product, $queryConcept))
            ->filter(fn (Product $product) => (float) $product->getAttribute('visual_similarity') >= self::MIN_VISIBLE_CONFIDENCE)
            ->sortByDesc('visual_similarity')
            ->unique('id')
            ->take($limit)
            ->values();
    }

    private function queryConcept(array $labels): ?string
    {
        $top = collect($labels)->first();
        if (! is_array($top) || empty($top['label'])) {
            return null;
        }

        return (string) $top['label'];
    }

    private function matchesQueryConcept(Product $product, ?string $concept): bool
    {
        if ($concept === null) {
            return true;
        }

        $haystack = Str::of(implode(' ', array_filter([
            $product->title,
            $product->description,
            $product->brand?->name,
            $product->category?->name,
            $product->category?->slug,
        ])))->lower()->toString();

        $keywords = [
            'smartphone' => ['iphone', ' phone', 'smartphone', 'mobile phone', 'galaxy', 'redmi', 'note 12', 'oppo', 'huawei'],
            'laptop' => ['laptop', 'notebook', 'macbook', 'dell', 'computer'],
            'speaker' => ['speaker', 'jbl', 'bluetooth'],
            'power_bank' => ['power bank', 'powercore', 'charger', 'battery'],
            'television' => ['tv', 'television', 'tcl', 'screen'],
            'motorbike' => ['motorbike', 'motorcycle', 'scooter', 'honda', 'yamaha', 'wave', 'click', 'exciter'],
            'bicycle' => ['bicycle', 'bike', 'giant', 'talon'],
            'helmet' => ['helmet'],
            'shoes' => ['shoe', 'shoes', 'sneaker', 'ultraboost', 'boots'],
            'clothing' => ['shirt', 'dress', 'skirt', 'scarf', 'uniform', 'clothing', 'fashion', 'sampot', 'krama'],
            'book' => ['book', 'textbook', 'workbook', 'dictionary', 'education'],
            'rice_cooker' => ['rice cooker', 'cooker'],
            'fan' => ['fan'],
            'refrigerator' => ['refrigerator', 'fridge'],
            'washing_machine' => ['washing machine', 'washer'],
            'sofa' => ['sofa', 'furniture'],
            'dishes' => ['dish', 'dishes', 'ceramic', 'dinner'],
            'sports' => ['sport', 'fitness', 'badminton', 'racket', 'dumbbell', 'goggles', 'yoga'],
        ][$concept] ?? [];

        if ($keywords === []) {
            return true;
        }

        return collect($keywords)->contains(fn (string $keyword) => str_contains($haystack, $keyword));
    }

    private function calibratedConfidence(float $rawScore): float
    {
        $confidence = ($rawScore - self::CONFIDENCE_FLOOR) / self::CONFIDENCE_SPAN;

        return round(max(0.0, min(0.99, $confidence)), 4);
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

    private function resolveImagePath(ProductImage $image, array &$tempFiles): ?string
    {
        if ($image->disk === 'external') {
            return $this->downloadExternalImage($image->path, $tempFiles);
        }

        $disk = Storage::disk($image->disk);

        if (! $disk->exists($image->path)) {
            return null;
        }

        return $disk->path($image->path);
    }

    private function downloadExternalImage(string $url, array &$tempFiles): ?string
    {
        try {
            $response = Http::timeout(12)
                ->retry(1, 200)
                ->get($url);
        } catch (\Throwable) {
            return null;
        }

        if (! $response->successful() || $response->body() === '') {
            return null;
        }

        $path = storage_path('app/visual-search/tmp/'.Str::uuid().'.img');
        file_put_contents($path, $response->body());
        $tempFiles[] = $path;

        return $path;
    }

    private function cleanupTempFiles(array $paths): void
    {
        foreach ($paths as $path) {
            if (is_string($path) && file_exists($path)) {
                @unlink($path);
            }
        }
    }
}
