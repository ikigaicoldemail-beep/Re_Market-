<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Product;
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

    public function test_product_creation_rejects_zero_price(): void
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
                'title' => 'Free Camera',
                'description' => 'This should not be accepted as a paid marketplace listing.',
                'price_amount' => 0,
                'currency' => 'USD',
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('price_amount');
    }

    public function test_product_update_rejects_zero_price(): void
    {
        $user = User::factory()->create(['role' => 'seller']);
        $store = Store::factory()->for($user)->create();
        $product = Product::factory()->forStore($store)->create();

        $token = Auth::guard('api')->login($user);

        $response = $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/v1/products/{$product->id}", [
                'price_amount' => 0,
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('price_amount');
    }

    public function test_product_creation_rejects_zero_variant_price(): void
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
                'title' => 'Camera With Variant',
                'description' => 'Variant price must also be positive.',
                'price_amount' => 45000,
                'currency' => 'USD',
                'variants' => [
                    [
                        'label' => 'Body only',
                        'price_amount' => 0,
                        'stock_quantity' => 1,
                    ],
                ],
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('variants.0.price_amount');
    }

    public function test_product_creation_rejects_unsupported_auto_post_platform(): void
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
                'title' => 'Phone with unsupported auto post',
                'description' => 'Only Facebook auto-post is supported in this version.',
                'price_amount' => 45000,
                'currency' => 'USD',
                'auto_post' => 'tiktok',
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('auto_post');
    }

    public function test_product_update_rejects_unsupported_auto_post_platform(): void
    {
        $user = User::factory()->create(['role' => 'seller']);
        $store = Store::factory()->for($user)->create();
        $product = Product::factory()->forStore($store)->create();

        $token = Auth::guard('api')->login($user);

        $response = $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/v1/products/{$product->id}", [
                'auto_post' => 'all',
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('auto_post');
    }

    public function test_public_products_endpoint_returns_published_products_only(): void
    {
        $publishedStore = Store::factory()->create();
        $draftStore = Store::factory()->create();

        Product::factory()->forStore($publishedStore)->create([
            'title' => 'Published Item',
            'status' => 'published',
            'visibility' => 'public',
        ]);

        Product::factory()->forStore($draftStore)->create([
            'title' => 'Draft Item',
            'status' => 'draft',
            'visibility' => 'public',
        ]);

        $response = $this->getJson('/api/v1/products');

        $response->assertOk();
        $response->assertSee('Published Item');
        $response->assertDontSee('Draft Item');
    }

    public function test_public_products_search_works_with_three_or_more_character_terms(): void
    {
        $matchingStore = Store::factory()->create();
        $otherStore = Store::factory()->create();

        Product::factory()->forStore($matchingStore)->create([
            'title' => 'Vintage Phone',
            'description' => 'A working old mobile device.',
            'status' => 'published',
            'visibility' => 'public',
        ]);

        Product::factory()->forStore($otherStore)->create([
            'title' => 'Canvas Bag',
            'description' => 'A sturdy reusable tote.',
            'status' => 'published',
            'visibility' => 'public',
        ]);

        $response = $this->getJson('/api/v1/products?search=phone');

        $response->assertOk();
        $response->assertSee('Vintage Phone');
        $response->assertDontSee('Canvas Bag');
    }
}
