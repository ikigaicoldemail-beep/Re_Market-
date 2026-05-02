<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wishlist\ToggleWishlistRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function __construct(private readonly WishlistService $wishlistService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $products = $this->wishlistService->list($request->user());

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

    public function store(ToggleWishlistRequest $request): JsonResponse
    {
        $product = Product::findOrFail($request->validated('product_id'));
        $this->wishlistService->add($request->user(), $product);

        return response()->json([
            'message' => 'Product added to wishlist.',
        ], 201);
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        $this->wishlistService->remove($request->user(), $product);

        return response()->json([
            'message' => 'Product removed from wishlist.',
        ]);
    }
}
