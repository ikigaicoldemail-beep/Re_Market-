<?php

namespace Tests\Feature\Api\V1;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class StoreApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_all_seller_stores(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create(['role' => 'seller', 'email' => 'seller-one@example.com']);
        $otherSeller = User::factory()->create(['role' => 'seller', 'email' => 'seller-two@example.com']);

        Store::factory()->for($seller)->create(['name' => 'Seller One Store']);
        Store::factory()->for($otherSeller)->create(['name' => 'Seller Two Store']);

        $token = Auth::guard('api')->login($admin);

        $response = $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/admin/stores');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'stores')
            ->assertJsonPath('meta.total', 2)
            ->assertJsonFragment(['email' => 'seller-one@example.com'])
            ->assertJsonFragment(['email' => 'seller-two@example.com']);
    }

    public function test_admin_can_filter_stores_by_seller(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create(['role' => 'seller']);
        $otherSeller = User::factory()->create(['role' => 'seller']);

        Store::factory()->for($seller)->create(['name' => 'Target Store']);
        Store::factory()->for($otherSeller)->create(['name' => 'Other Store']);

        $token = Auth::guard('api')->login($admin);

        $response = $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/admin/stores?seller_id='.$seller->id);

        $response
            ->assertOk()
            ->assertJsonCount(1, 'stores')
            ->assertJsonPath('stores.0.name', 'Target Store');
    }

    public function test_non_admin_cannot_list_admin_stores(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        Store::factory()->for($seller)->create();

        $token = Auth::guard('api')->login($seller);

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/admin/stores')
            ->assertForbidden();
    }
}
