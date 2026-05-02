<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function checkout(User $user, Address $address, array $data): Order
    {
        $cart = $user->cart()->with(['items.product.condition', 'items.product.store', 'items.product.images'])->first();

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
            foreach ($cart->items as $item) {
                if ($item->product->status !== 'published') {
                    throw ValidationException::withMessages([
                        'cart' => ["Product {$item->product->title} is no longer available."],
                    ]);
                }

                if ($item->product->stock_quantity < $item->quantity) {
                    throw ValidationException::withMessages([
                        'cart' => ["Product {$item->product->title} does not have enough stock."],
                    ]);
                }
            }

            $storeIds = $cart->items->pluck('product.store_id')->filter()->unique()->values();

            $order = Order::create([
                'order_number' => 'ORD-'.strtoupper(Str::random(10)),
                'buyer_id' => $user->id,
                'store_id' => $storeIds->count() === 1 ? $storeIds->first() : null,
                'address_id' => $address->id,
                'status' => 'pending',
                'payment_status' => 'pending',
                'currency' => $cart->currency,
                'subtotal_amount' => $cart->subtotal_amount,
                'discount_amount' => $cart->discount_amount,
                'shipping_amount' => $cart->shipping_amount,
                'total_amount' => $cart->total_amount,
                'notes' => $data['notes'] ?? null,
                'placed_at' => now(),
            ]);

            foreach ($cart->items as $item) {
                $product = $item->product;

                $order->items()->create([
                    'product_id' => $product->id,
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

                $product->decrement('stock_quantity', $item->quantity);

                if ($product->fresh()->stock_quantity <= 0) {
                    $product->update(['status' => 'sold']);
                }
            }

            $payment = $order->payments()->create([
                'user_id' => $user->id,
                'provider' => $data['provider'] ?? 'manual',
                'provider_reference' => null,
                'payment_method' => $data['payment_method'] ?? 'cash_on_delivery',
                'status' => 'pending',
                'currency' => $order->currency,
                'amount' => $order->total_amount,
            ]);

            $cart->items()->delete();
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
