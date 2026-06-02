<?php

namespace Tests\Feature\Api\V1;

use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CartApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_to_cart_rejects_too_large_quantity(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $buyer = User::factory()->create();
        $store = Store::factory()->for($seller)->create();
        $product = Product::factory()->forStore($store)->create([
            'user_id' => $seller->id,
            'stock_quantity' => 1000,
            'status' => 'published',
            'visibility' => 'public',
        ]);
        $token = Auth::guard('api')->login($buyer);

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/cart/items', [
                'product_id' => $product->id,
                'quantity' => 1000,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('quantity');
    }

    public function test_update_cart_item_rejects_too_large_quantity(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $buyer = User::factory()->create();
        $store = Store::factory()->for($seller)->create();
        $product = Product::factory()->forStore($store)->create([
            'user_id' => $seller->id,
            'stock_quantity' => 1000,
            'status' => 'published',
            'visibility' => 'public',
        ]);
        $token = Auth::guard('api')->login($buyer);

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/cart/items', [
                'product_id' => $product->id,
                'quantity' => 1,
            ])
            ->assertCreated();

        $cartItemId = $buyer->cart()->firstOrFail()->items()->firstOrFail()->id;

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/v1/cart/items/'.$cartItemId, [
                'quantity' => 1000,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('quantity');
    }
}
