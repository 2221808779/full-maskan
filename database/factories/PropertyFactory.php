<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    protected $model = Property::class;

    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'location' => fake()->address(),
            'price' => fake()->randomFloat(2, 500, 5000),
            'property_type' => fake()->randomElement(['شقة', 'فيلا', 'منزل', 'مبنى', 'منتجع', 'استراحة']),
            'rooms_count' => fake()->numberBetween(1, 10),
            'bathrooms_count' => fake()->numberBetween(1, 5),
            'status' => 'available',
        ];
    }
}
