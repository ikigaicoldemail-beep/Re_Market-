<?php

namespace Database\Factories;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Store>
 */
class StoreFactory extends Factory
{
    protected $model = Store::class;

    public function definition(): array
    {
        $name = fake()->company();

        return [
            'user_id' => User::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 9999),
            'description' => fake()->paragraph(),
            'contact_email' => fake()->safeEmail(),
            'contact_phone' => fake()->numerify('##########'),
            'country_code' => 'US',
            'state' => fake()->state(),
            'city' => fake()->city(),
            'address_line' => fake()->streetAddress(),
            'status' => 'active',
            'is_verified' => false,
            'followers_count' => 0,
            'published_at' => now(),
        ];
    }
}
