<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'property_id' => Property::factory(),
            'stars' => fake()->numberBetween(1, 5),
            'comment' => fake()->sentence(),
        ];
    }
}
