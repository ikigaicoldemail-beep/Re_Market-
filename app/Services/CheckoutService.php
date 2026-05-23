<?php

namespace App\Services;

use App\Models\Address;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function checkout(User $user, Address $address, array $data): Order
    {
        $cart = $user->cart()->with(['items.product.condition', 'items.product.store', 'items.product.images', 'items.variant'])->first();

        if (! $cart || $cart->items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => ['Your cart is empty.'],
            ]);
        }

        if ($address->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'address_id' => ['The selected address does not belong to the current user.'],
            ]);
        }

        return DB::transaction(function () use ($user, $address, $cart, $data) {
            $items = CartItem::query()
                ->where('cart_id', $cart->id)
                ->with(['product.condition', 'product.store', 'product.images', 'variant'])
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => ['Your cart is empty.'],
                ]);
            }

            foreach ($items as $item) {
                $product = Product::query()
                    ->whereKey($item->product_id)
                    ->with(['condition', 'store', 'images'])
                    ->lockForUpdate()
                    ->first();

                if (! $product || $product->status !== 'published') {
                    $title = $item->product?->title ?? 'item';

                    throw ValidationException::withMessages([
                        'cart' => ["Product {$title} is no longer available."],
                    ]);
                }

                if ($product->user_id === $user->id) {
                    throw ValidationException::withMessages([
                        'cart' => ["You cannot checkout your own product {$product->title}."],
                    ]);
                }

                $variant = $item->product_variant_id
                    ? ProductVariant::query()->whereKey($item->product_variant_id)->lockForUpdate()->first()
                    : null;

                $expectedPrice = $variant?->price_amount ?? $product->price_amount;
                if ((int) $item->unit_price_amount !== (int) $expectedPrice) {
                    throw ValidationException::withMessages([
                        'cart' => ["Product {$product->title} price has changed. Please refresh your cart before checkout."],
                    ]);
                }

                $stockSource = $variant ?? $product;
                if ($stockSource->stock_quantity < $item->quantity) {
                    throw ValidationException::withMessages([
                        'cart' => ["Product {$product->title} does not have enough stock."],
                    ]);
                }

                $item->setRelation('product', $product);
                $item->setRelation('variant', $variant);
            }

            $storeIds = $items->pluck('product.store_id')->filter()->unique()->values();
            $subtotal = (int) $items->sum('line_total_amount');
            $discount = 0;
            $shipping = 0;
            $total = $subtotal - $discount + $shipping;

            $order = Order::create([
                'order_number' => 'ORD-'.strtoupper(Str::random(10)),
                'buyer_id' => $user->id,
                'store_id' => $storeIds->count() === 1 ? $storeIds->first() : null,
                'address_id' => $address->id,
                'status' => 'pending',
                'payment_status' => 'pending',
                'currency' => $cart->currency,
                'subtotal_amount' => $subtotal,
                'discount_amount' => $discount,
                'shipping_amount' => $shipping,
                'total_amount' => $total,
                'notes' => $data['notes'] ?? null,
                'placed_at' => now(),
            ]);

            foreach ($items as $item) {
                $product = $item->product;
                $variant = $item->variant;

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_variant_id' => $variant?->id,
                    'variant_label' => $variant?->label,
                    'seller_id' => $product->user_id,
                    'product_title' => $product->title,
                    'product_slug' => $product->slug,
                    'product_image_path' => $product->image,
                    'product_condition_label' => $product->condition?->name,
                    'quantity' => $item->quantity,
                    'unit_price_amount' => $item->unit_price_amount,
                    'line_total_amount' => $item->line_total_amount,
                    'fulfillment_status' => 'pending',
                ]);

                if ($variant) {
                    $variant->stock_quantity -= $item->quantity;
                    $variant->save();
                }

                $product->stock_quantity -= $item->quantity;

                if ($product->stock_quantity <= 0) {
                    $product->status = 'sold';
                }

                $product->save();
            }

            $order->payments()->create([
                'user_id' => $user->id,
                'provider' => $data['provider'] ?? 'manual',
                'provider_reference' => null,
                'payment_method' => $data['payment_method'] ?? 'cash_on_delivery',
                'status' => 'pending',
                'currency' => $order->currency,
                'amount' => $total,
            ]);

            CartItem::query()
                ->where('cart_id', $cart->id)
                ->whereIn('id', $items->pluck('id'))
                ->delete();
            $cart->update([
                'status' => 'active',
                'checked_out_at' => now(),
                'currency' => $order->currency,
                'subtotal_amount' => 0,
                'discount_amount' => 0,
                'shipping_amount' => 0,
                'total_amount' => 0,
            ]);

            return $order->fresh(['address', 'store', 'items', 'payments']);
        });
    }

    public function orderHistory(User $user)
    {
        return $user->orders()
            ->with(['address', 'store', 'items', 'payments'])
            ->latest()
            ->paginate(15);
    }

    public function paymentForOrder(Order $order): ?Payment
    {
        return $order->payments()->latest()->first();
    }
}
