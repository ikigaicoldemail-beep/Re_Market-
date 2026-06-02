<?php

namespace Tests\Feature\Api\V1;

use App\Models\Product;
use App\Models\ProductReport;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AdminApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_user_role_and_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user', 'status' => 'active']);
        $token = Auth::guard('api')->login($admin);

        $response = $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson('/api/v1/admin/users/'.$user->id, [
                'role' => 'seller',
                'status' => 'suspended',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('user.role', 'seller')
            ->assertJsonPath('user.status', 'suspended');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'seller',
            'status' => 'suspended',
        ]);
    }

    public function test_admin_can_verify_store_and_moderate_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create(['role' => 'seller']);
        $store = Store::factory()->for($seller)->create([
            'is_verified' => false,
            'status' => 'draft',
            'published_at' => null,
        ]);
        $product = Product::factory()->forStore($store)->create([
            'status' => 'pending',
            'moderation_status' => 'pending',
            'visibility' => 'private',
        ]);
        $token = Auth::guard('api')->login($admin);

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson('/api/v1/admin/stores/'.$store->id, [
                'status' => 'active',
                'is_verified' => true,
            ])
            ->assertOk()
            ->assertJsonPath('store.status', 'active')
            ->assertJsonPath('store.is_verified', true);

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson('/api/v1/admin/products/'.$product->id, [
                'status' => 'published',
                'moderation_status' => 'approved',
                'visibility' => 'public',
            ])
            ->assertOk()
            ->assertJsonPath('product.status', 'published')
            ->assertJsonPath('product.moderation_status', 'approved');
    }

    public function test_non_admin_cannot_use_admin_user_controls(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $user = User::factory()->create();
        $token = Auth::guard('api')->login($seller);

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->patchJson('/api/v1/admin/users/'.$user->id, [
                'status' => 'suspended',
            ])
            ->assertForbidden();
    }

    public function test_admin_can_list_reports_with_open_reports_first(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $reporter = User::factory()->create();
        $seller = User::factory()->create(['role' => 'seller']);
        $store = Store::factory()->for($seller)->create();
        $openProduct = Product::factory()->forStore($store)->create();
        $resolvedProduct = Product::factory()->forStore($store)->create();

        ProductReport::create([
            'product_id' => $resolvedProduct->id,
            'reporter_id' => $reporter->id,
            'reason' => 'spam',
            'details' => 'Already handled.',
            'status' => 'resolved',
            'resolved_by' => $admin->id,
            'resolved_at' => now(),
        ]);
        ProductReport::create([
            'product_id' => $openProduct->id,
            'reporter_id' => $reporter->id,
            'reason' => 'scam',
            'details' => 'Needs review.',
            'status' => 'open',
        ]);

        $token = Auth::guard('api')->login($admin);

        $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/admin/reports')
            ->assertOk()
            ->assertJsonPath('reports.0.status', 'open')
            ->assertJsonPath('reports.1.status', 'resolved');
    }
}
