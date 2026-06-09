<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\ProductCondition;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class MarketplaceEmailVerificationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_user_cannot_create_store(): void
    {
        $user = User::factory()->unverified()->create();
        $token = Auth::guard('api')->login($user);

        $response = $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/stores', [
                'name' => 'Unverified Store',
                'description' => 'This request should be blocked before store validation matters.',
            ]);

        $response
            ->assertForbidden()
            ->assertJsonPath('message', 'Verify your email before using marketplace actions.');
    }

    public function test_unverified_user_cannot_create_product(): void
    {
        $user = User::factory()->unverified()->create(['role' => 'seller']);
        $store = Store::factory()->for($user)->create();
        $category = Category::factory()->create();
        $condition = ProductCondition::factory()->create();
        $token = Auth::guard('api')->login($user);

        $response = $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/products', [
                'store_id' => $store->id,
                'category_id' => $category->id,
                'product_condition_id' => $condition->id,
                'title' => 'Blocked Product',
                'description' => 'Unverified users cannot publish listings.',
                'price_amount' => 1000,
            ]);

        $response->assertForbidden();
    }

    public function test_verified_user_can_create_store(): void
    {
        $user = User::factory()->create();
        $token = Auth::guard('api')->login($user);

        $response = $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/stores', [
                'name' => 'Verified Store',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('store.name', 'Verified Store');
    }
}
