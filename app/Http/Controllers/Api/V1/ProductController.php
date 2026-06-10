<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ListProductsRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Events\ProductCreated;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Requests\Product\UploadProductImagesRequest;
use App\Http\Requests\Social\ScheduleProductPostRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ScheduledPostResource;
use App\Http\Resources\StoreResource;
use App\Models\Conversation;
use App\Models\Product;
use App\Models\Store;
use App\Services\ProductService;
use App\Services\SocialPostingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
        private readonly SocialPostingService $socialPostingService
    ) {}

    public function index(ListProductsRequest $request): JsonResponse
    {
        $products = $this->productService->listPublic($request->validated());

        return response()->json([
            'products' => ProductResource::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->create($request->user(), $request->validated());

        if ($request->hasFile('images')) {
            $product = $this->productService->uploadImages($product, $request->file('images'));
        }

        // Fire after images are attached so the auto-post listener publishes with the primary image.
        if ($product->status === 'published') {
            event(new ProductCreated($product));
        }

        return response()->json([
            'message' => 'Product created successfully.',
            'product' => new ProductResource($product),
        ], 201);
    }

    public function show(Request $request, Product $product): JsonResponse
    {
        if (
            $product->status !== 'published'
            && (! $request->user() || $request->user()->id !== $product->user_id)
        ) {
            abort(404);
        }

        $product->load(['images', 'variants', 'store', 'category', 'brand', 'condition', 'user.profile']);
        $product->loadCount(['reviews' => fn ($q) => $q->where('status', 'published')]);
        $product->loadAvg(['reviews as reviews_avg_rating' => fn ($q) => $q->where('status', 'published')], 'rating');

        return response()->json([
            'product' => new ProductResource($product),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $product = $this->productService->update($product, $request->user(), $request->validated());

        return response()->json([
            'message' => 'Product updated successfully.',
            'product' => new ProductResource($product),
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('delete', $product);

        $this->productService->delete($product->load('images'));

        return response()->json([
            'message' => 'Product deleted successfully.',
        ]);
    }

    public function uploadImages(UploadProductImagesRequest $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $product = $this->productService->uploadImages($product, $request->file('images'));

        // The seller form creates the product first, then POSTs images here. Re-fire ProductCreated so
        // the auto-post listener can publish with the image attached. The listener itself is idempotent —
        // it skips when a non-failed social post for this product+platform already exists.
        if ($product->status === 'published') {
            event(new ProductCreated($product));
        }

        return response()->json([
            'message' => 'Product images uploaded successfully.',
            'product' => new ProductResource($product),
        ]);
    }

    public function myProducts(Request $request): JsonResponse
    {
        $products = $this->productService->listForOwner($request->user());

        return response()->json([
            'products' => ProductResource::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function storePage(Store $store): JsonResponse
    {
        $products = $this->productService->listForStore($store);

        $store->loadMissing('user');

        return response()->json([
            'store' => new StoreResource($store),
            'products' => ProductResource::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function reviewEligibility(Request $request, Product $product): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['eligible' => false, 'reason' => 'Sign in to leave a review.']);
        }

        if ($user->id === $product->user_id) {
            return response()->json(['eligible' => false, 'reason' => '']);
        }

        $hasReview = $product->reviews()->where('user_id', $user->id)->exists();
        if ($hasReview) {
            return response()->json(['eligible' => true, 'reason' => '']);
        }

        $hasConversation = Conversation::where('product_id', $product->id)
            ->whereHas('participants', fn ($q) => $q->where('user_id', $user->id))
            ->exists();

        return response()->json([
            'eligible' => $hasConversation,
            'reason' => $hasConversation ? '' : 'Chat with the seller about this item first to leave a review.',
        ]);
    }

    public function share(Product $product): JsonResponse
    {
        $sharePath = '/products/'.$product->id;

        return response()->json([
            'share' => [
                'product_id' => $product->id,
                'title' => $product->title,
                'slug' => $product->slug,
                'share_url' => rtrim(config('app.frontend_url', config('app.url')), '/').$sharePath,
                'api_url' => url('/api/v1/products/'.$product->id),
            ],
        ]);
    }

    public function schedulePost(ScheduleProductPostRequest $request, ?Product $product = null): JsonResponse
    {
        $validated = $request->validated();

        if (! $product) {
            $product = Product::findOrFail($validated['product_id']);
        }

        $this->authorize('update', $product);

        $scheduledPost = $this->socialPostingService->scheduleProductPost(
            $request->user(),
            $product,
            $validated
        );

        return response()->json([
            'message' => 'Product post scheduled successfully.',
            'scheduled_post' => new ScheduledPostResource($scheduledPost),
        ], 201);
    }
}
