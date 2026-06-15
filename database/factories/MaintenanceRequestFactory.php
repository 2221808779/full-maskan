<?php

namespace Database\Factories;

use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaintenanceRequestFactory extends Factory
{
    protected $model = MaintenanceRequest::class;

    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'tenant_id' => User::factory(),
            'problem_description' => fake()->sentence(),
            'status' => 'pending',
        ];
    }
}
