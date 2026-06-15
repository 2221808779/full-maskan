<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['phone' => '0910000000'],
            [
                'full_name' => 'Admin',
                'password' => bcrypt('password'),
                'phone' => '0500000000',
                'user_type' => 'admin',
                'phone_verified_at' => now(),
            ]
        );
    }
}
