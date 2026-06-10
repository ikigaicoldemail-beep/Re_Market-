<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Electronics & Gadgets',    'slug' => 'electronics',           'sort_order' => 1],
            ['name' => 'Motorcycles & Vehicles',   'slug' => 'motorcycles-vehicles',  'sort_order' => 2],
            ['name' => 'Fashion & Clothing',       'slug' => 'fashion-clothing',      'sort_order' => 3],
            ['name' => 'Home & Living',            'slug' => 'home-living',           'sort_order' => 4],
            ['name' => 'Books & Education',        'slug' => 'books-education',       'sort_order' => 5],
            ['name' => 'Sports & Fitness',         'slug' => 'sports-fitness',        'sort_order' => 6],
            ['name' => 'Traditional Crafts',       'slug' => 'traditional-crafts',    'sort_order' => 7],
            ['name' => 'Agriculture & Tools',      'slug' => 'agriculture-tools',     'sort_order' => 8],
        ];

        foreach ($categories as $data) {
            Category::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name'       => $data['name'],
                    'status'     => 'active',
                    'sort_order' => $data['sort_order'],
                ]
            );
        }
    }
}
