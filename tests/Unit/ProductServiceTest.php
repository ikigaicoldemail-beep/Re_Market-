<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCondition;
use App\Models\Store;
use App\Models\User;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_rejects_store_not_owned_by_user(): void
    {
        $service = app(ProductService::class);
        $owner = User::factory()->create(['role' => 'seller']);
        $otherUser = User::factory()->create(['role' => 'seller']);
        $store = Store::factory()->for($otherUser)->create();
        $category = Category::factory()->create();
        $condition = ProductCondition::factory()->create();

        $this->expectException(ValidationException::class);

        $service->create($owner, [
            'store_id' => $store->id,
            'category_id' => $category->id,
            'product_condition_id' => $condition->id,
            'title' => 'Unauthorized Product',
            'description' => 'Should fail',
            'price_amount' => 1000,
            'currency' => 'USD',
            'status' => 'draft',
        ]);
    }

    public function test_create_generates_unique_slug_for_duplicate_titles(): void
    {
        $service = app(ProductService::class);
        $user = User::factory()->create(['role' => 'seller']);
        $store = Store::factory()->for($user)->create();
        $category = Category::factory()->create();
        $condition = ProductCondition::factory()->create();

        Product::factory()->forStore($store)->create([
            'user_id' => $user->id,
            'title' => 'Vintage Watch',
            'slug' => 'vintage-watch',
        ]);

        $product = $service->create($user, [
            'store_id' => $store->id,
            'category_id' => $category->id,
            'product_condition_id' => $condition->id,
            'title' => 'Vintage Watch',
            'description' => 'Second item with same title',
            'price_amount' => 5000,
            'currency' => 'USD',
            'status' => 'draft',
        ]);

        $this->assertNotSame('vintage-watch', $product->slug);
        $this->assertStringStartsWith('vintage-watch', $product->slug);
    }
}
