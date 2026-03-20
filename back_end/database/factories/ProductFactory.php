<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->numberBetween(5000, 500000),
            'quantity' => $this->faker->numberBetween(0, 50),
            'image' => 'https://picsum.photos/seed/' . $this->faker->uuid() . '/600/400',
            'category_id' => Category::factory(),
        ];
    }
}
