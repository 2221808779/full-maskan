<?php

namespace Database\Seeders;

use App\Models\Specialty;
use Illuminate\Database\Seeder;

class SpecialtySeeder extends Seeder
{
    public function run(): void
    {
        $specialties = [
            ['name' => 'electricity'],
            ['name' => 'plumbing'],
            ['name' => 'air_conditioning'],
            ['name' => 'painting'],
            ['name' => 'carpentry'],
            ['name' => 'other'],
        ];

        foreach ($specialties as $spec) {
            Specialty::firstOrCreate($spec);
        }
    }
}