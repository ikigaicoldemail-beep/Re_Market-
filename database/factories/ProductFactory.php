<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCondition;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function configure(): static
    {
        return $this->afterMaking(function (Product $product) {
            if ($product->store_id && ! $product->user_id) {
                $store = Store::find($product->store_id);
                $product->user_id = $store?->user_id;
            }
        })->afterCreating(function (Product $product) {
            if ($product->store && $product->user_id !== $product->store->user_id) {
                $product->forceFill([
                    'user_id' => $product->store->user_id,
                ])->save();
            }
        });
    }

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'user_id' => User::factory(),
            'store_id' => Store::factory(),
            'category_id' => Category::factory(),
            'product_condition_id' => ProductCondition::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 9999),
            'sku' => strtoupper(fake()->bothify('SKU-####??')),
            'description' => fake()->paragraph(),
            'price' => 10000,
            'price_amount' => 10000,
            'currency' => 'USD',
            'image' => null,
            'location' => fake()->city(),
            'location_country_code' => 'US',
            'location_state' => fake()->state(),
            'location_city' => fake()->city(),
            'stock_quantity' => fake()->numberBetween(1, 5),
            'status' => 'published',
            'moderation_status' => 'approved',
            'visibility' => 'public',
            'allow_offers' => true,
            'published_at' => now(),
            'schedule_at' => null,
            'auto_post' => null,
        ];
    }

    public function forStore(Store $store): static
    {
        return $this->state(fn () => [
            'user_id' => $store->user_id,
            'store_id' => $store->id,
        ]);
    }
}
