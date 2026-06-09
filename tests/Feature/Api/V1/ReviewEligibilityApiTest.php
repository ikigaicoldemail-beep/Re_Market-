<?php

namespace Tests\Feature\Api\V1;

use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ReviewEligibilityApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_in_user_can_review_product_without_checkout_dependency(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $buyer = User::factory()->create();
        $store = Store::factory()->for($seller)->create();
        $product = Product::factory()->forStore($store)->create([
            'user_id' => $seller->id,
            'status' => 'published',
            'visibility' => 'public',
        ]);

        $token = Auth::guard('api')->login($buyer);

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/products/'.$product->id.'/review-eligibility')
            ->assertOk()
            ->assertJsonPath('eligible', true)
            ->assertJsonPath('reason', '');
    }

    public function test_seller_cannot_review_own_product(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $store = Store::factory()->for($seller)->create();
        $product = Product::factory()->forStore($store)->create([
            'user_id' => $seller->id,
            'status' => 'published',
            'visibility' => 'public',
        ]);

        $token = Auth::guard('api')->login($seller);

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/products/'.$product->id.'/review-eligibility')
            ->assertOk()
            ->assertJsonPath('eligible', false);
    }

    public function test_existing_review_keeps_user_eligible_to_edit(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $buyer = User::factory()->create();
        $store = Store::factory()->for($seller)->create();
        $product = Product::factory()->forStore($store)->create([
            'user_id' => $seller->id,
            'status' => 'published',
            'visibility' => 'public',
        ]);

        ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $buyer->id,
            'rating' => 5,
            'status' => 'published',
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
