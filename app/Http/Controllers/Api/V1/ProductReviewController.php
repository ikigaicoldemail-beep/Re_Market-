<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductReviewResource;
use App\Models\Product;
use App\Models\ProductReview;
use App\Notifications\NewReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    /** Public listing of reviews for a product. */
    public function index(Request $request, Product $product): JsonResponse
    {
        $filters = $request->validate([
            'rating' => ['nullable', 'integer', 'between:1,5'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $query = ProductReview::query()
            ->with(['reviewer.profile'])
            ->where('product_id', $product->id)
            ->where('status', 'published')
            ->when($filters['rating'] ?? null, fn ($q, $r) => $q->where('rating', $r))
            ->latest();

        $reviews = $query->paginate($filters['per_page'] ?? 10)->withQueryString();

        $summary = ProductReview::query()
            ->where('product_id', $product->id)
            ->where('status', 'published')
            ->selectRaw('COUNT(*) as total, AVG(rating) as avg, '.
                'SUM(rating=5) as r5, SUM(rating=4) as r4, SUM(rating=3) as r3, SUM(rating=2) as r2, SUM(rating=1) as r1')
            ->first();

        return response()->json([
            'reviews' => ProductReviewResource::collection($reviews),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
            'summary' => [
                'total' => (int) ($summary->total ?? 0),
                'average' => round((float) ($summary->avg ?? 0), 2),
                'breakdown' => [
                    '5' => (int) ($summary->r5 ?? 0),
                    '4' => (int) ($summary->r4 ?? 0),
                    '3' => (int) ($summary->r3 ?? 0),
                    '2' => (int) ($summary->r2 ?? 0),
                    '1' => (int) ($summary->r1 ?? 0),
                ],
            ],
        ]);
    }

    /** User creates one review for a product. */
    public function store(Request $request, Product $product): JsonResponse
    {
        $user = $request->user();

        if ($product->user_id === $user->id) {
            abort(422, 'You cannot review your own product.');
        }

        if (ProductReview::where('product_id', $product->id)->where('user_id', $user->id)->exists()) {
            abort(409, 'You have already reviewed this product. Edit your existing review instead.');
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['nullable', 'string', 'max:120'],
            'body' => ['nullable', 'string', 'max:5000'],
        ]);

        $review = ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => $data['rating'],
            'title' => $data['title'] ?? null,
            'body' => $data['body'] ?? null,
            'status' => 'published',
        ]);

        // Notify the seller.
        try {
            $seller = $product->user;
            if ($seller) {
                $seller->notify(new NewReview($review->load('product')));
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'message' => 'Thanks for your review.',
            'review' => new ProductReviewResource($review->load('reviewer.profile')),
        ], 201);
    }

    /** Update own review. */
    public function update(Request $request, ProductReview $review): JsonResponse
    {
        if ($review->user_id !== $request->user()->id) {
            abort(403, 'You can only edit your own reviews.');
        }

        $data = $request->validate([
            'rating' => ['sometimes', 'integer', 'between:1,5'],
            'title' => ['nullable', 'string', 'max:120'],
            'body' => ['nullable', 'string', 'max:5000'],
        ]);

        $review->fill($data)->save();

        return response()->json([
            'message' => 'Review updated.',
            'review' => new ProductReviewResource($review->fresh('reviewer.profile')),
        ]);
    }

    /** Delete own review. */
    public function destroy(Request $request, ProductReview $review): JsonResponse
    {
        if ($review->user_id !== $request->user()->id) {
            abort(403, 'You can only delete your own reviews.');
        }

        $review->delete();

        return response()->json(['message' => 'Review deleted.']);
    }

    /** Seller reply (only the product owner). */
    public function reply(Request $request, ProductReview $review): JsonResponse
    {
        $review->loadMissing('product');

        if (! $review->product || $review->product->user_id !== $request->user()->id) {
            abort(403, 'Only the product owner can reply to reviews on this product.');
        }

        $data = $request->validate([
            'seller_reply' => ['required', 'string', 'max:2000'],
        ]);

        $review->fill([
            'seller_reply' => $data['seller_reply'],
            'seller_replied_at' => now(),
        ])->save();

        return response()->json([
            'message' => 'Reply posted.',
            'review' => new ProductReviewResource($review->fresh('reviewer.profile')),
        ]);
    }

    /** Reviews I have written. */
    public function myReviews(Request $request): JsonResponse
    {
        $reviews = ProductReview::query()
            ->with(['product.images'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return response()->json([
            'reviews' => ProductReviewResource::collection($reviews),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
        ]);
    }

    /** Reviews on products I sell (for the seller dashboard). */
    public function reviewsOnMyProducts(Request $request): JsonResponse
    {
        $reviews = ProductReview::query()
            ->with(['reviewer.profile', 'product.images'])
            ->whereHas('product', fn ($q) => $q->where('user_id', $request->user()->id))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return response()->json([
            'reviews' => ProductReviewResource::collection($reviews),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
        ]);
    }
}
