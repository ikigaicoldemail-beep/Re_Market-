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

    public function test_public_store_search_matches_slug(): void
    {
        Store::factory()->create([
            'name' => 'Demo store 168',
            'slug' => 'demo-store-168',
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/v1/stores?search=demo-store-168&per_page=1');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'stores')
            ->assertJsonPath('stores.0.slug', 'demo-store-168');
    }

    public function test_stores_can_be_sorted_by_distance_from_a_point(): void
    {
        // Reference point: central Phnom Penh.
        $lat = 11.5564;
        $lng = 104.9282;

        Store::factory()->create(['name' => 'Far Shop', 'status' => 'active', 'latitude' => 13.3614, 'longitude' => 103.8597]); // Siem Reap ~230km
        Store::factory()->create(['name' => 'Near Shop', 'status' => 'active', 'latitude' => 11.5600, 'longitude' => 104.9300]); // ~0.5km
        Store::factory()->create(['name' => 'Mid Shop', 'status' => 'active', 'latitude' => 11.7000, 'longitude' => 105.0000]); // ~17km

        $response = $this->getJson("/api/v1/stores?sort=nearest&lat={$lat}&lng={$lng}");

        $response
            ->assertOk()
            ->assertJsonCount(3, 'stores')
            ->assertJsonPath('stores.0.name', 'Near Shop')
            ->assertJsonPath('stores.1.name', 'Mid Shop')
            ->assertJsonPath('stores.2.name', 'Far Shop');

        $distances = array_column($response->json('stores'), 'distance_km');
        $this->assertLessThan(2, $distances[0]);
        $this->assertEqualsWithDelta($distances, array_values(collect($distances)->sort()->all()), 0.0001);
    }

    public function test_stores_can_be_filtered_by_radius(): void
    {
        $lat = 11.5564;
        $lng = 104.9282;

        Store::factory()->create(['name' => 'Near Shop', 'status' => 'active', 'latitude' => 11.5600, 'longitude' => 104.9300]);
        Store::factory()->create(['name' => 'Far Shop', 'status' => 'active', 'latitude' => 13.3614, 'longitude' => 103.8597]);

        $response = $this->getJson("/api/v1/stores?lat={$lat}&lng={$lng}&radius_km=50&sort=nearest");

        $response
            ->assertOk()
            ->assertJsonCount(1, 'stores')
            ->assertJsonPath('stores.0.name', 'Near Shop');
    }

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
