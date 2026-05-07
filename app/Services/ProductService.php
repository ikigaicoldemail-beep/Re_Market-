<?php

namespace App\Services;

use App\Events\ProductCreated;
use App\Jobs\GenerateProductImageEmbeddingJob;
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
    public function listPublic(array $filters): LengthAwarePaginator
    {
        $query = Product::query()
            ->with(['images', 'store', 'category', 'condition', 'user.profile'])
            ->where('status', 'published')
            ->where('visibility', 'public');

        if (! empty($filters['search'])) {
            $query->where(function ($builder) use ($filters) {
                $builder
                    ->where('title', 'like', '%'.$filters['search'].'%')
                    ->orWhere('description', 'like', '%'.$filters['search'].'%');
            });
        }

        if (! empty($filters['store_id'])) {
            $query->where('store_id', $filters['store_id']);
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
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
            default => $query->latest(),
        };

        return $query->paginate($filters['per_page'] ?? 15)->withQueryString();
    }

    public function listForOwner(User $user): LengthAwarePaginator
    {
        return Product::query()
            ->with(['images', 'store', 'category', 'condition'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(15);
    }

    public function listForStore(Store $store): LengthAwarePaginator
    {
        return Product::query()
            ->with(['images', 'category', 'condition'])
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
            $status = $data['status'] ?? 'draft';

            $product = Product::create([
                'user_id' => $user->id,
                'store_id' => $data['store_id'],
                'category_id' => $data['category_id'] ?? null,
                'product_condition_id' => $data['product_condition_id'] ?? null,
                'title' => $data['title'],
                'slug' => $this->resolveUniqueSlug($data['slug'] ?? $data['title']),
                'sku' => $data['sku'] ?? null,
                'description' => $data['description'],
                'price' => $data['price_amount'],
                'price_amount' => $data['price_amount'],
                'currency' => strtoupper((string) ($data['currency'] ?? 'USD')),
                'stock_quantity' => $data['stock_quantity'] ?? 1,
                'location' => $data['location_city'] ?? null,
                'location_country_code' => isset($data['location_country_code']) ? strtoupper((string) $data['location_country_code']) : null,
                'location_state' => $data['location_state'] ?? null,
                'location_city' => $data['location_city'] ?? null,
                'status' => $status,
                'moderation_status' => 'approved',
                'visibility' => $data['visibility'] ?? 'public',
                'allow_offers' => $data['allow_offers'] ?? true,
                'published_at' => $status === 'published' ? now() : null,
                'schedule_at' => $data['schedule_at'] ?? null,
                'auto_post' => $data['auto_post'] ?? null,
            ]);

            // Fire event to trigger auto-posting if enabled
            event(new ProductCreated($product));

            return $product->load(['images', 'store', 'category', 'condition', 'user.profile']);
        });
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

        $status = $data['status'] ?? $product->status;

        $product->fill([
            'store_id' => $data['store_id'] ?? $product->store_id,
            'category_id' => $data['category_id'] ?? $product->category_id,
            'product_condition_id' => $data['product_condition_id'] ?? $product->product_condition_id,
            'title' => $data['title'] ?? $product->title,
            'slug' => isset($data['slug']) ? $this->resolveUniqueSlug($data['slug'], $product->id) : $product->slug,
            'sku' => array_key_exists('sku', $data) ? $data['sku'] : $product->sku,
            'description' => $data['description'] ?? $product->description,
            'price' => $data['price_amount'] ?? $product->price,
            'price_amount' => $data['price_amount'] ?? $product->price_amount,
            'currency' => isset($data['currency']) ? strtoupper((string) $data['currency']) : $product->currency,
            'stock_quantity' => $data['stock_quantity'] ?? $product->stock_quantity,
            'location' => $data['location_city'] ?? $product->location,
            'location_country_code' => array_key_exists('location_country_code', $data) ? strtoupper((string) $data['location_country_code']) : $product->location_country_code,
            'location_state' => $data['location_state'] ?? $product->location_state,
            'location_city' => $data['location_city'] ?? $product->location_city,
            'status' => $status,
            'visibility' => $data['visibility'] ?? $product->visibility,
            'allow_offers' => $data['allow_offers'] ?? $product->allow_offers,
            'published_at' => $status === 'published'
                ? ($product->published_at ?? now())
                : ($status === 'draft' ? null : $product->published_at),
            'schedule_at' => array_key_exists('schedule_at', $data) ? $data['schedule_at'] : $product->schedule_at,
            'auto_post' => array_key_exists('auto_post', $data) ? $data['auto_post'] : $product->auto_post,
        ]);
        $product->save();

        return $product->load(['images', 'store', 'category', 'condition', 'user.profile']);
    }

    public function delete(Product $product): void
    {
        DB::transaction(function () use ($product) {
            foreach ($product->images as $image) {
                Storage::disk($image->disk)->delete($image->path);
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

                $productImage = $product->images()->create([
                    'path' => $path,
                    'disk' => 'product-images',
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

        return $product->fresh(['images', 'store', 'category', 'condition', 'user.profile']);
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
}
