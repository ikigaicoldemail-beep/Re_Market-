<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCondition;
use App\Models\Store;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;

class DemoMarketplaceSeeder extends Seeder
{
    public function run(): void
    {
        $seller = User::factory()->create([
            'name' => 'Demo Seller',
            'email' => 'seller@example.com',
            'role' => 'seller',
        ]);

        UserProfile::firstOrCreate(
            ['user_id' => $seller->id],
            [
                'username' => 'demo-seller',
                'is_seller' => true,
                'profile_visibility' => 'public',
            ]
        );

        $store = Store::factory()->for($seller)->create([
            'name' => 'Demo Resale Store',
            'slug' => 'demo-resale-store',
        ]);

        $seller->profile()->update([
            'default_store_id' => $store->id,
        ]);

        $buyer = User::factory()->create([
            'name' => 'Demo Buyer',
            'email' => 'buyer@example.com',
        ]);

        UserProfile::firstOrCreate(
            ['user_id' => $buyer->id],
            [
                'username' => 'demo-buyer',
                'is_seller' => false,
                'profile_visibility' => 'public',
            ]
        );

        Address::factory()->for($buyer)->create([
            'recipient_name' => $buyer->name,
            'is_default' => true,
        ]);

        $category = Category::query()->first() ?? Category::factory()->create();
        $condition = ProductCondition::query()->first() ?? ProductCondition::factory()->create();

        Product::factory()->count(3)->forStore($store)->create([
            'category_id' => $category->id,
            'product_condition_id' => $condition->id,
            'status' => 'published',
            'visibility' => 'public',
        ]);
    }
}
