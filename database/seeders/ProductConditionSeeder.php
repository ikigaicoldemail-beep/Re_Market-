<?php

namespace Database\Seeders;

use App\Models\ProductCondition;
use Illuminate\Database\Seeder;

class ProductConditionSeeder extends Seeder
{
    public function run(): void
    {
        $conditions = [
            ['name' => 'New', 'slug' => 'new', 'rank' => 1],
            ['name' => 'Like New', 'slug' => 'like-new', 'rank' => 2],
            ['name' => 'Good', 'slug' => 'good', 'rank' => 3],
            ['name' => 'Fair', 'slug' => 'fair', 'rank' => 4],
            ['name' => 'Poor', 'slug' => 'poor', 'rank' => 5],
        ];

        foreach ($conditions as $condition) {
            ProductCondition::query()->updateOrCreate(
                ['slug' => $condition['slug']],
                $condition
            );
        }
    }
}
