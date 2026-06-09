<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ProfessorScopeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_payment_cart_order_and_address_routes_are_not_active_scope(): void
    {
        $this->getJson('/api/v1/cart')->assertNotFound();
        $this->postJson('/api/v1/cart/items')->assertNotFound();
        $this->postJson('/api/v1/checkout')->assertNotFound();
        $this->getJson('/api/v1/orders')->assertNotFound();
        $this->getJson('/api/v1/orders/1/payment-status')->assertNotFound();
        $this->getJson('/api/v1/addresses')->assertNotFound();
    }

    public function test_seller_can_create_simulated_facebook_post_without_real_social_account(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $store = Store::factory()->for($seller)->create();
        $category = Category::factory()->create();
        $product = Product::factory()->forStore($store)->create([
            'user_id' => $seller->id,
            'category_id' => $category->id,
            'status' => 'published',
            'visibility' => 'public',
        ]);

        $token = Auth::guard('api')->login($seller);

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/social/posts', [
                'platform' => 'facebook',
                'product_id' => $product->id,
                'publish_now' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('social_post.status', 'posted')
            ->assertJsonPath('social_post.provider_response.simulated', true);
    }
}
