<?php

namespace Database\Seeders;

use App\Models\ProductCondition;
use Illuminate\Database\Seeder;

class ProductConditionSeeder extends Seeder
{
    public function run(): void
    {
        $conditions = [
            ['name' => 'New',      'slug' => 'new',      'rank' => 1, 'quality_score' => 100, 'color' => 'green',  'description' => 'Brand new, never used, original packaging.'],
            ['name' => 'Like New', 'slug' => 'like-new', 'rank' => 2, 'quality_score' => 90,  'color' => 'blue',   'description' => 'Used once or twice, no visible wear.'],
            ['name' => 'Good',     'slug' => 'good',     'rank' => 3, 'quality_score' => 75,  'color' => 'yellow', 'description' => 'Light signs of use, fully functional.'],
            ['name' => 'Fair',     'slug' => 'fair',     'rank' => 4, 'quality_score' => 55,  'color' => 'orange', 'description' => 'Visible wear or minor damage, still works.'],
            ['name' => 'Poor',     'slug' => 'poor',     'rank' => 5, 'quality_score' => 30,  'color' => 'red',    'description' => 'Heavy wear, may need repair.'],
        ];

        foreach ($conditions as $condition) {
            ProductCondition::query()->updateOrCreate(
                ['slug' => $condition['slug']],
                $condition
            );
        }
    }
}
