<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\ProductCondition;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_seller_can_create_product(): void
    {
        $user = User::factory()->create(['role' => 'seller']);
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
                'title' => 'Used Camera',
                'description' => 'Mirrorless camera in good condition',
                'price_amount' => 45000,
                'currency' => 'USD',
                'stock_quantity' => 1,
                'location_country_code' => 'US',
                'location_state' => 'California',
                'location_city' => 'Los Angeles',
                'status' => 'published',
                'visibility' => 'public',
                'allow_offers' => true,
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('product.title', 'Used Camera')
            ->assertJsonPath('product.status', 'published');

        $this->assertDatabaseHas('products', [
            'title' => 'Used Camera',
            'user_id' => $user->id,
            'store_id' => $store->id,
        ]);
    }

    public function test_public_products_endpoint_returns_published_products_only(): void
    {
        $publishedStore = Store::factory()->create();
        $draftStore = Store::factory()->create();

        \App\Models\Product::factory()->forStore($publishedStore)->create([
            'title' => 'Published Item',
            'status' => 'published',
            'visibility' => 'public',
        ]);

        \App\Models\Product::factory()->forStore($draftStore)->create([
            'title' => 'Draft Item',
            'status' => 'draft',
            'visibility' => 'public',
        ]);

        $response = $this->getJson('/api/v1/products');

        $response->assertOk();
        $response->assertSee('Published Item');
        $response->assertDontSee('Draft Item');
    }
}
