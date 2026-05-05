<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function getCart(User $user): Cart
    {
        $cart = $user->cart()->firstOrCreate(
            [],
            [
                'status' => 'active',
                'currency' => 'USD',
            ]
        );

        if ($cart->status !== 'active') {
            $cart->update(['status' => 'active']);
        }

        return $this->refreshTotals($cart);
    }

    public function addItem(User $user, Product $product, int $quantity): Cart
    {
        if ($product->user_id === $user->id) {
            throw ValidationException::withMessages([
                'product_id' => ['You cannot add your own product to cart.'],
            ]);
        }

        if ($product->status !== 'published') {
            throw ValidationException::withMessages([
                'product_id' => ['Only published products can be added to cart.'],
            ]);
        }

        if ($product->stock_quantity < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => ['Requested quantity exceeds available stock.'],
            ]);
        }

        $cart = $this->getCart($user);

        DB::transaction(function () use ($cart, $product, $quantity) {
            $cart = Cart::query()
                ->whereKey($cart->id)
                ->lockForUpdate()
                ->firstOrFail();

            $product = Product::query()
                ->whereKey($product->id)
                ->lockForUpdate()
                ->firstOrFail();

            $item = $cart->items()->firstOrNew([
                'product_id' => $product->id,
            ]);

            $newQuantity = ($item->exists ? $item->quantity : 0) + $quantity;

            if ($product->stock_quantity < $newQuantity) {
                throw ValidationException::withMessages([
                    'quantity' => ['Requested quantity exceeds available stock.'],
                ]);
            }

            $item->fill([
                'quantity' => $newQuantity,
                'unit_price_amount' => $product->price_amount,
                'line_total_amount' => $newQuantity * $product->price_amount,
            ]);
            $item->save();
        });

        return $this->refreshTotals($cart->fresh());
    }

    public function updateItem(CartItem $item, int $quantity): Cart
    {
        DB::transaction(function () use ($item, $quantity) {
            $product = Product::query()
                ->whereKey($item->product_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($product->stock_quantity < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => ['Requested quantity exceeds available stock.'],
                ]);
            }

            $item->update([
                'quantity' => $quantity,
                'unit_price_amount' => $product->price_amount,
                'line_total_amount' => $quantity * $product->price_amount,
            ]);
        });

        return $this->refreshTotals($item->cart->fresh());
    }

    public function removeItem(CartItem $item): Cart
    {
        $cart = $item->cart;
        $item->delete();

        return $this->refreshTotals($cart->fresh());
    }

    public function clear(Cart $cart): Cart
    {
        $cart->items()->delete();

        return $this->refreshTotals($cart->fresh());
    }

    public function refreshTotals(Cart $cart): Cart
    {
        $cart->loadMissing(['items.product.images', 'items.product.store', 'items.product.category', 'items.product.condition']);

        $subtotal = (int) $cart->items->sum('line_total_amount');
        $cart->forceFill([
            'subtotal_amount' => $subtotal,
            'discount_amount' => 0,
            'shipping_amount' => 0,
            'total_amount' => $subtotal,
        ])->save();

        return $cart->fresh(['items.product.images', 'items.product.store', 'items.product.category', 'items.product.condition']);
    }
}
