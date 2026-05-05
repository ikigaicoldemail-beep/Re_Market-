<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Http\Resources\CartResource;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService) {}

    public function show(Request $request): JsonResponse
    {
        $cart = $this->cartService->getCart($request->user());

        return response()->json([
            'cart' => new CartResource($cart),
        ]);
    }

    public function store(AddToCartRequest $request): JsonResponse
    {
        $product = Product::findOrFail($request->validated('product_id'));
        $cart = $this->cartService->addItem(
            $request->user(),
            $product,
            $request->validated('quantity')
        );

        return response()->json([
            'message' => 'Product added to cart.',
            'cart' => new CartResource($cart),
        ], 201);
    }

    public function update(UpdateCartItemRequest $request, CartItem $cartItem): JsonResponse
    {
        abort_unless($cartItem->cart->user_id === $request->user()->id, 403);

        $cart = $this->cartService->updateItem($cartItem, $request->validated('quantity'));

        return response()->json([
            'message' => 'Cart item updated successfully.',
            'cart' => new CartResource($cart),
        ]);
    }

    public function destroy(Request $request, CartItem $cartItem): JsonResponse
    {
        abort_unless($cartItem->cart->user_id === $request->user()->id, 403);

        $cart = $this->cartService->removeItem($cartItem);

        return response()->json([
            'message' => 'Cart item removed successfully.',
            'cart' => new CartResource($cart),
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        $cart = $this->cartService->clear($this->cartService->getCart($request->user()));

        return response()->json([
            'message' => 'Cart cleared successfully.',
            'cart' => new CartResource($cart),
        ]);
    }
}
