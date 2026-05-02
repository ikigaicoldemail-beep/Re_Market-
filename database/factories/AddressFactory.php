<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'label' => 'Home',
            'recipient_name' => fake()->name(),
            'phone' => fake()->numerify('##########'),
            'country_code' => 'US',
            'state' => fake()->state(),
            'city' => fake()->city(),
            'postal_code' => fake()->postcode(),
            'address_line_1' => fake()->streetAddress(),
            'address_line_2' => fake()->secondaryAddress(),
            'landmark' => fake()->streetName(),
            'type' => 'shipping',
            'is_default' => true,
        ];
    }
}
