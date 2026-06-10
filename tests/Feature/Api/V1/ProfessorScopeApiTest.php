<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Product;
use App\Models\SocialAccount;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ProfessorScopeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_payment_cart_order_and_address_routes_are_authenticated_ecommerce_scope(): void
    {
        $this->getJson('/api/v1/cart')->assertUnauthorized();
        $this->postJson('/api/v1/cart/items')->assertUnauthorized();
        $this->postJson('/api/v1/checkout')->assertUnauthorized();
        $this->getJson('/api/v1/orders')->assertUnauthorized();
        $this->getJson('/api/v1/orders/1/payment-status')->assertUnauthorized();
        $this->getJson('/api/v1/addresses')->assertUnauthorized();
    }

    public function test_seller_can_create_facebook_post_draft_with_connected_account(): void
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
        $account = SocialAccount::create([
            'user_id' => $seller->id,
            'platform' => 'facebook',
            'provider_user_id' => 'page-123',
            'provider_account_name' => 'Demo Page',
            'access_token' => encrypt('demo-token'),
            'status' => 'active',
        ]);

        $token = Auth::guard('api')->login($seller);

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/social/posts', [
                'platform' => 'facebook',
                'product_id' => $product->id,
                'social_account_id' => $account->id,
                'publish_now' => false,
            ])
            ->assertCreated()
            ->assertJsonPath('social_post.status', 'draft')
            ->assertJsonPath('social_post.social_account.id', $account->id);
    }
}
