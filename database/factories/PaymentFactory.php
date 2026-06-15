<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'user_id' => User::factory(),
            'amount' => fake()->randomFloat(2, 100, 5000),
            'payment_type' => 'bank_transfer',
            'status' => 'completed',
        ];
    }
}
