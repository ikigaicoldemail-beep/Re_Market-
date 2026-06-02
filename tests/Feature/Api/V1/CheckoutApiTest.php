<?php

namespace Tests\Feature\Api\V1;

use App\Models\Address;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CheckoutApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_checkout_cart(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $buyer = User::factory()->create();
        $store = Store::factory()->for($seller)->create();
        $product = Product::factory()->forStore($store)->create([
            'user_id' => $seller->id,
            'stock_quantity' => 2,
            'price_amount' => 25000,
            'price' => 25000,
            'status' => 'published',
            'visibility' => 'public',
        ]);
        $address = Address::factory()->for($buyer)->create();

        $token = Auth::guard('api')->login($buyer);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/cart/items', [
                'product_id' => $product->id,
                'quantity' => 1,
            ])
            ->assertCreated();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/checkout', [
                'address_id' => $address->id,
                'provider' => 'manual',
                'payment_method' => 'cash_on_delivery',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('order.payment_status', 'pending');

        $this->assertDatabaseHas('orders', [
            'buyer_id' => $buyer->id,
            'payment_status' => 'pending',
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
    }

    public function test_user_cannot_add_own_product_to_cart(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $store = Store::factory()->for($seller)->create();
        $product = Product::factory()->forStore($store)->create([
            'user_id' => $seller->id,
            'status' => 'published',
            'visibility' => 'public',
        ]);

        $token = Auth::guard('api')->login($seller);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/cart/items', [
                'product_id' => $product->id,
                'quantity' => 1,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('product_id');
    }

    public function test_checkout_rejects_cart_when_product_price_changed(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $buyer = User::factory()->create();
        $store = Store::factory()->for($seller)->create();
        $product = Product::factory()->forStore($store)->create([
            'user_id' => $seller->id,
            'stock_quantity' => 2,
            'price_amount' => 25000,
            'price' => 25000,
            'status' => 'published',
            'visibility' => 'public',
        ]);
        $address = Address::factory()->for($buyer)->create();

        $token = Auth::guard('api')->login($buyer);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/cart/items', [
                'product_id' => $product->id,
                'quantity' => 1,
            ])
            ->assertCreated();

        $product->update([
            'price_amount' => 30000,
            'price' => 30000,
        ]);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/checkout', [
                'address_id' => $address->id,
                'provider' => 'manual',
                'payment_method' => 'cash_on_delivery',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('cart');
    }

    public function test_checkout_with_variant_does_not_sell_parent_product_when_other_variants_have_stock(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $buyer = User::factory()->create();
        $store = Store::factory()->for($seller)->create();
        $product = Product::factory()->forStore($store)->create([
            'user_id' => $seller->id,
            'stock_quantity' => 1,
            'price_amount' => 25000,
            'price' => 25000,
            'status' => 'published',
            'visibility' => 'public',
        ]);
        $purchasedVariant = ProductVariant::create([
            'product_id' => $product->id,
            'label' => 'Small',
            'price_amount' => 25000,
            'stock_quantity' => 1,
            'sort_order' => 0,
            'is_default' => true,
        ]);
        $remainingVariant = ProductVariant::create([
            'product_id' => $product->id,
            'label' => 'Large',
            'price_amount' => 25000,
            'stock_quantity' => 2,
            'sort_order' => 1,
            'is_default' => false,
        ]);
        $address = Address::factory()->for($buyer)->create();

        $token = Auth::guard('api')->login($buyer);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/cart/items', [
                'product_id' => $product->id,
                'variant_id' => $purchasedVariant->id,
                'quantity' => 1,
            ])
            ->assertCreated();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/checkout', [
                'address_id' => $address->id,
                'provider' => 'manual',
                'payment_method' => 'cash_on_delivery',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('product_variants', [
            'id' => $purchasedVariant->id,
            'stock_quantity' => 0,
        ]);
        $this->assertDatabaseHas('product_variants', [
            'id' => $remainingVariant->id,
            'stock_quantity' => 2,
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'published',
            'stock_quantity' => 1,
        ]);
    }

    public function test_checkout_rejects_unsupported_payment_provider(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $buyer = User::factory()->create();
        $store = Store::factory()->for($seller)->create();
        $product = Product::factory()->forStore($store)->create([
            'user_id' => $seller->id,
            'stock_quantity' => 2,
            'price_amount' => 25000,
            'price' => 25000,
            'status' => 'published',
            'visibility' => 'public',
        ]);
        $address = Address::factory()->for($buyer)->create();
        $token = Auth::guard('api')->login($buyer);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/cart/items', [
                'product_id' => $product->id,
                'quantity' => 1,
            ])
            ->assertCreated();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/checkout', [
                'address_id' => $address->id,
                'provider' => 'stripe',
                'payment_method' => 'cash_on_delivery',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('provider');
    }

    public function test_checkout_rejects_unsupported_payment_method(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $buyer = User::factory()->create();
        $store = Store::factory()->for($seller)->create();
        $product = Product::factory()->forStore($store)->create([
            'user_id' => $seller->id,
            'stock_quantity' => 2,
            'price_amount' => 25000,
            'price' => 25000,
            'status' => 'published',
            'visibility' => 'public',
        ]);
        $address = Address::factory()->for($buyer)->create();
        $token = Auth::guard('api')->login($buyer);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/cart/items', [
                'product_id' => $product->id,
                'quantity' => 1,
            ])
            ->assertCreated();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/checkout', [
                'address_id' => $address->id,
                'provider' => 'manual',
                'payment_method' => 'credit_card',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('payment_method');
    }
}
