<?php

namespace Database\Factories;

use App\Models\ProductCondition;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductCondition>
 */
class ProductConditionFactory extends Factory
{
    protected $model = ProductCondition::class;

    public function definition(): array
    {
        $name = Str::title(fake()->unique()->word());

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 9999),
            'description' => fake()->sentence(),
            'rank' => fake()->numberBetween(1, 5),
        ];
    }
}
