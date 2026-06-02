<?php

namespace App\Services;

use App\Events\ProductCreated;
use App\Jobs\GenerateProductImageEmbeddingJob;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductService
{
    public function __construct(
        private readonly ImageVariantService $imageVariants = new ImageVariantService()
    ) {}

    public function listPublic(array $filters): LengthAwarePaginator
    {
        $query = Product::query()
            ->with(['images', 'store', 'category', 'brand', 'condition', 'user.profile'])
            ->withCount(['reviews' => fn ($q) => $q->where('status', 'published')])
            ->withAvg(['reviews as reviews_avg_rating' => fn ($q) => $q->where('status', 'published')], 'rating')
            ->where('status', 'published')
            ->where('visibility', 'public')
            ->where(function ($q) {
                $q->whereNull('schedule_at')->orWhere('schedule_at', '<=', now());
            });

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            if (mb_strlen($search) >= 3 && DB::connection()->getDriverName() === 'mysql') {
                // FULLTEXT BOOLEAN MODE with wildcard suffix on each token for prefix matching.
                $tokens = preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY);
                $expr = collect($tokens)
                    ->map(fn ($t) => '+'.preg_replace('/[+\-><()~*"@]/u', '', $t).'*')
                    ->filter(fn ($t) => $t !== '+*')
                    ->implode(' ');

                if ($expr !== '') {
                    $query->whereRaw('MATCH(title, description) AGAINST(? IN BOOLEAN MODE)', [$expr]);
                } else {
                    $this->applyLikeSearch($query, $search);
                }
            } else {
                $this->applyLikeSearch($query, $search);
            }
        }

        if (! empty($filters['store_id'])) {
            $query->where('store_id', $filters['store_id']);
        }

        if (! empty($filters['category_id'])) {
            $catId = (int) $filters['category_id'];
            $childIds = Category::where('parent_id', $catId)->pluck('id')->all();
            $query->whereIn('category_id', array_merge([$catId], $childIds));
        }

        if (! empty($filters['brand_id'])) {
            $query->where('brand_id', (int) $filters['brand_id']);
        }

        if (! empty($filters['featured'])) {
            $query->where('is_featured', true);
        }

        if (! empty($filters['on_sale'])) {
            $query->whereNotNull('original_price_amount')
                ->whereColumn('original_price_amount', '>', 'price_amount');
        }

        if (! empty($filters['product_condition_id'])) {
            $query->where('product_condition_id', $filters['product_condition_id']);
        }

        if (! empty($filters['min_price'])) {
            $query->where('price_amount', '>=', $filters['min_price']);
        }

        if (! empty($filters['max_price'])) {
            $query->where('price_amount', '<=', $filters['max_price']);
        }

        if (! empty($filters['location_city'])) {
            $query->where('location_city', $filters['location_city']);
        }

        match ($filters['sort'] ?? 'latest') {
            'oldest' => $query->oldest(),
            'price_asc' => $query->orderBy('price_amount'),
            'price_desc' => $query->orderByDesc('price_amount'),
            'featured' => $query->orderByDesc('is_featured')->orderByDesc('featured_at')->latest(),
            default => $query->latest(),
        };

        return $query->paginate($filters['per_page'] ?? 15)->withQueryString();
    }

    public function listForOwner(User $user): LengthAwarePaginator
    {
        return Product::query()
            ->with(['images', 'store', 'category', 'brand', 'condition'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(15);
    }

    public function listForStore(Store $store): LengthAwarePaginator
    {
        return Product::query()
            ->with(['images', 'category', 'brand', 'condition'])
            ->withCount(['reviews' => fn ($q) => $q->where('status', 'published')])
            ->withAvg(['reviews as reviews_avg_rating' => fn ($q) => $q->where('status', 'published')], 'rating')
            ->where('store_id', $store->id)
            ->where('status', 'published')
            ->where('visibility', 'public')
            ->latest()
            ->paginate(15);
    }

    public function create(User $user, array $data): Product
    {
        $store = Store::query()
            ->whereKey($data['store_id'])
            ->where('user_id', $user->id)
            ->first();

        if (! $store) {
            throw ValidationException::withMessages([
                'store_id' => ['You can only create products for your own store.'],
            ]);
        }

        return DB::transaction(function () use ($user, $data) {
            $requestedStatus = $data['status'] ?? 'draft';
            $scheduleAt = ! empty($data['schedule_at'])
                ? \Illuminate\Support\Carbon::parse($data['schedule_at'])->setTimezone(config('app.timezone'))
                : null;
            $isScheduled = $scheduleAt && $scheduleAt->isFuture();
            $effectiveStatus = $isScheduled ? 'scheduled' : $requestedStatus;

            $product = Product::create([
                'user_id' => $user->id,
                'store_id' => $data['store_id'],
                'category_id' => $data['category_id'] ?? null,
                'brand_id' => $data['brand_id'] ?? null,
                'product_condition_id' => $data['product_condition_id'] ?? null,
                'title' => $data['title'],
                'slug' => $this->resolveUniqueSlug($data['slug'] ?? $data['title']),
                'sku' => $data['sku'] ?? null,
                'description' => $data['description'],
                'specs' => $data['specs'] ?? null,
                'price' => $data['price_amount'],
                'price_amount' => $data['price_amount'],
                'original_price_amount' => $data['original_price_amount'] ?? null,
                'currency' => strtoupper((string) ($data['currency'] ?? 'USD')),
                'stock_quantity' => $data['stock_quantity'] ?? 1,
                'location' => $data['location_city'] ?? null,
                'location_country_code' => isset($data['location_country_code']) ? strtoupper((string) $data['location_country_code']) : null,
                'location_state' => $data['location_state'] ?? null,
                'location_city' => $data['location_city'] ?? null,
                'status' => $effectiveStatus,
                'moderation_status' => 'approved',
                'visibility' => $data['visibility'] ?? 'public',
                'allow_offers' => $data['allow_offers'] ?? true,
                'is_featured' => $data['is_featured'] ?? false,
                'featured_at' => ! empty($data['is_featured']) ? now() : null,
                'published_at' => $effectiveStatus === 'published' ? now() : null,
                'schedule_at' => $scheduleAt,
                'auto_post' => $data['auto_post'] ?? null,
            ]);

            // ProductCreated is fired by the controller AFTER images are attached so auto-post listeners see the primary image.
            // Scheduled products are picked up later by the dispatch command, which fires the event itself.

            if (! empty($data['variants']) && is_array($data['variants'])) {
                $this->syncVariants($product, $data['variants']);
            }

            return $product->load(['images', 'variants', 'store', 'category', 'brand', 'condition', 'user.profile']);
        });
    }

    public function syncVariants(Product $product, array $variants): void
    {
        $keepIds = [];
        foreach (array_values($variants) as $i => $v) {
            if (empty($v['label'])) {
                continue;
            }
            $row = [
                'label' => (string) $v['label'],
                'sku' => $v['sku'] ?? null,
                'attributes' => $v['attributes'] ?? null,
                'price_amount' => (int) ($v['price_amount'] ?? $product->price_amount),
                'original_price_amount' => isset($v['original_price_amount']) && $v['original_price_amount'] !== '' && $v['original_price_amount'] !== null
                    ? (int) $v['original_price_amount']
                    : null,
                'stock_quantity' => (int) ($v['stock_quantity'] ?? 0),
                'sort_order' => (int) ($v['sort_order'] ?? $i),
                'is_default' => (bool) ($v['is_default'] ?? ($i === 0)),
            ];
            if (! empty($v['id'])) {
                $existing = $product->variants()->whereKey($v['id'])->first();
                if ($existing) {
                    $existing->fill($row)->save();
                    $keepIds[] = $existing->id;
                    continue;
                }
            }
            $created = $product->variants()->create($row);
            $keepIds[] = $created->id;
        }

        if (! empty($keepIds)) {
            $product->variants()->whereNotIn('id', $keepIds)->delete();
        }
    }

    public function update(Product $product, User $user, array $data): Product
    {
        if (isset($data['store_id'])) {
            $store = Store::query()
                ->whereKey($data['store_id'])
                ->where('user_id', $user->id)
                ->first();

            if (! $store) {
                throw ValidationException::withMessages([
                    'store_id' => ['You can only assign products to your own store.'],
                ]);
            }
        }

        $requestedStatus = $data['status'] ?? $product->status;

        $incomingScheduleAt = array_key_exists('schedule_at', $data) ? $data['schedule_at'] : $product->schedule_at;
        $scheduleAt = $incomingScheduleAt
            ? \Illuminate\Support\Carbon::parse($incomingScheduleAt)->setTimezone(config('app.timezone'))
            : null;
        $isScheduled = $scheduleAt && $scheduleAt->isFuture();

        // If scheduling is requested, override status; otherwise, if leaving a previously-scheduled state, default to published.
        if ($isScheduled) {
            $effectiveStatus = 'scheduled';
        } elseif ($product->status === 'scheduled' && $requestedStatus === 'scheduled') {
            $effectiveStatus = 'published';
        } else {
            $effectiveStatus = $requestedStatus;
        }

        $product->fill([
            'store_id' => $data['store_id'] ?? $product->store_id,
            'category_id' => $data['category_id'] ?? $product->category_id,
            'brand_id' => array_key_exists('brand_id', $data) ? $data['brand_id'] : $product->brand_id,
            'product_condition_id' => $data['product_condition_id'] ?? $product->product_condition_id,
            'title' => $data['title'] ?? $product->title,
            'slug' => isset($data['slug']) ? $this->resolveUniqueSlug($data['slug'], $product->id) : $product->slug,
            'sku' => array_key_exists('sku', $data) ? $data['sku'] : $product->sku,
            'description' => $data['description'] ?? $product->description,
            'specs' => array_key_exists('specs', $data) ? $data['specs'] : $product->specs,
            'price' => $data['price_amount'] ?? $product->price,
            'price_amount' => $data['price_amount'] ?? $product->price_amount,
            'original_price_amount' => array_key_exists('original_price_amount', $data) ? $data['original_price_amount'] : $product->original_price_amount,
            'currency' => isset($data['currency']) ? strtoupper((string) $data['currency']) : $product->currency,
            'stock_quantity' => $data['stock_quantity'] ?? $product->stock_quantity,
            'location' => $data['location_city'] ?? $product->location,
            'location_country_code' => array_key_exists('location_country_code', $data) ? strtoupper((string) $data['location_country_code']) : $product->location_country_code,
            'location_state' => $data['location_state'] ?? $product->location_state,
            'location_city' => $data['location_city'] ?? $product->location_city,
            'status' => $effectiveStatus,
            'visibility' => $data['visibility'] ?? $product->visibility,
            'allow_offers' => $data['allow_offers'] ?? $product->allow_offers,
            'is_featured' => array_key_exists('is_featured', $data) ? (bool) $data['is_featured'] : $product->is_featured,
            'featured_at' => array_key_exists('is_featured', $data)
                ? ($data['is_featured'] ? ($product->featured_at ?? now()) : null)
                : $product->featured_at,
            'published_at' => $effectiveStatus === 'published'
                ? ($product->published_at ?? now())
                : ($effectiveStatus === 'draft' || $effectiveStatus === 'scheduled' ? null : $product->published_at),
            'schedule_at' => $isScheduled ? $scheduleAt : null,
            'auto_post' => array_key_exists('auto_post', $data) ? $data['auto_post'] : $product->auto_post,
        ]);
        $product->save();

        if (array_key_exists('variants', $data) && is_array($data['variants'])) {
            $this->syncVariants($product, $data['variants']);
        }

        return $product->load(['images', 'variants', 'store', 'category', 'brand', 'condition', 'user.profile']);
    }

    public function delete(Product $product): void
    {
        DB::transaction(function () use ($product) {
            foreach ($product->images as $image) {
                Storage::disk($image->disk)->delete($image->path);
                $this->imageVariants->delete($image->disk, $image->variants);
            }

            $product->delete();
        });
    }

    public function uploadImages(Product $product, array $images): Product
    {
        DB::transaction(function () use ($product, $images) {
            $startingOrder = (int) $product->images()->max('sort_order') + 1;
            $hasPrimary = $product->images()->where('is_primary', true)->exists();

            foreach ($images as $index => $image) {
                /** @var UploadedFile $image */
                if (! str_starts_with((string) $image->getMimeType(), 'image/')) {
                    throw ValidationException::withMessages([
                        'images' => ['Only valid image uploads are allowed.'],
                    ]);
                }

                $path = $image->store('', 'product-images');

                try {
                    $variants = $this->imageVariants->generateFromUpload($image, 'product-images', $path);
                } catch (\Throwable $e) {
                    report($e);
                    $variants = null;
                }

                $productImage = $product->images()->create([
                    'path' => $path,
                    'disk' => 'product-images',
                    'variants' => $variants,
                    'mime_type' => $image->getMimeType(),
                    'file_size' => $image->getSize(),
                    'sort_order' => $startingOrder + $index,
                    'is_primary' => ! $hasPrimary && $index === 0,
                    'ai_embedding_status' => 'pending',
                ]);

                if ($productImage->is_primary) {
                    $product->image = $productImage->path;
                    $product->save();
                }

                GenerateProductImageEmbeddingJob::dispatch($productImage->id);
            }
        });

        return $product->fresh(['images', 'store', 'category', 'brand', 'condition', 'user.profile']);
    }

    private function resolveUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'product';
        $slug = $base;
        $counter = 1;

        while (
            Product::query()
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function applyLikeSearch($query, string $search): void
    {
        $query->where(function ($builder) use ($search) {
            $builder
                ->where('title', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%');
        });
    }
}
