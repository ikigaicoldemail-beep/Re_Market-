<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
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

    public function addItem(User $user, Product $product, int $quantity, ?int $variantId = null): Cart
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

        $variant = null;
        if ($variantId) {
            $variant = ProductVariant::where('id', $variantId)
                ->where('product_id', $product->id)
                ->first();
            if (! $variant) {
                throw ValidationException::withMessages([
                    'variant_id' => ['The selected variant does not belong to this product.'],
                ]);
            }
        }

        $stockSource = $variant ?? $product;
        if ($stockSource->stock_quantity < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => ['Requested quantity exceeds available stock.'],
            ]);
        }

        $cart = $this->getCart($user);

        DB::transaction(function () use ($cart, $product, $quantity, $variant) {
            $cart = Cart::query()
                ->whereKey($cart->id)
                ->lockForUpdate()
                ->firstOrFail();

            $product = Product::query()
                ->whereKey($product->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedVariant = $variant
                ? ProductVariant::query()->whereKey($variant->id)->lockForUpdate()->firstOrFail()
                : null;

            $itemLookup = ['product_id' => $product->id, 'product_variant_id' => $lockedVariant?->id];
            $item = $cart->items()->firstOrNew($itemLookup);

            $newQuantity = ($item->exists ? $item->quantity : 0) + $quantity;

            $stockSource = $lockedVariant ?? $product;
            if ($stockSource->stock_quantity < $newQuantity) {
                throw ValidationException::withMessages([
                    'quantity' => ['Requested quantity exceeds available stock.'],
                ]);
            }

            $price = $lockedVariant?->price_amount ?? $product->price_amount;
            $item->fill([
                'product_variant_id' => $lockedVariant?->id,
                'quantity' => $newQuantity,
                'unit_price_amount' => $price,
                'line_total_amount' => $newQuantity * $price,
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
        $cart->loadMissing(['items.product.images', 'items.product.store', 'items.product.category', 'items.product.condition', 'items.variant']);

        $subtotal = (int) $cart->items->sum('line_total_amount');
        $cart->forceFill([
            'subtotal_amount' => $subtotal,
            'discount_amount' => 0,
            'shipping_amount' => 0,
            'total_amount' => $subtotal,
        ])->save();

        return $cart->fresh(['items.product.images', 'items.product.store', 'items.product.category', 'items.product.condition', 'items.variant']);
    }
}
