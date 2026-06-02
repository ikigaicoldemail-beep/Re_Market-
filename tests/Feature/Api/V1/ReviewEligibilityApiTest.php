<?php

namespace Tests\Feature\Api\V1;

use App\Models\Conversation;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ReviewEligibilityApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_chat_about_product_does_not_make_buyer_review_eligible_without_paid_order(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $buyer = User::factory()->create();
        $store = Store::factory()->for($seller)->create();
        $product = Product::factory()->forStore($store)->create([
            'user_id' => $seller->id,
            'status' => 'published',
            'visibility' => 'public',
        ]);
        $conversation = Conversation::create([
            'product_id' => $product->id,
            'created_by' => $buyer->id,
            'type' => 'private',
        ]);
        $conversation->participants()->createMany([
            ['user_id' => $buyer->id, 'joined_at' => now()],
            ['user_id' => $seller->id, 'joined_at' => now()],
        ]);

        $token = Auth::guard('api')->login($buyer);

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/products/'.$product->id.'/review-eligibility')
            ->assertOk()
            ->assertJsonPath('eligible', false)
            ->assertJsonPath('reason', 'Only buyers who have purchased this product can leave a review.');
    }

    public function test_paid_order_makes_buyer_review_eligible(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $buyer = User::factory()->create();
        $store = Store::factory()->for($seller)->create();
        $product = Product::factory()->forStore($store)->create([
            'user_id' => $seller->id,
            'status' => 'published',
            'visibility' => 'public',
        ]);
        $order = Order::create([
            'order_number' => 'ORD-TEST-PAID',
            'buyer_id' => $buyer->id,
            'store_id' => $store->id,
            'status' => 'completed',
            'payment_status' => 'paid',
            'currency' => 'USD',
            'subtotal_amount' => 10000,
            'discount_amount' => 0,
            'shipping_amount' => 0,
            'total_amount' => 10000,
            'placed_at' => now(),
            'paid_at' => now(),
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'seller_id' => $seller->id,
            'product_title' => $product->title,
            'product_slug' => $product->slug,
            'quantity' => 1,
            'unit_price_amount' => 10000,
            'line_total_amount' => 10000,
            'fulfillment_status' => 'completed',
        ]);

        $token = Auth::guard('api')->login($buyer);

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/products/'.$product->id.'/review-eligibility')
            ->assertOk()
            ->assertJsonPath('eligible', true)
            ->assertJsonPath('reason', '');
    }
}
