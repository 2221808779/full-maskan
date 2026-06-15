<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technician_specializations', function (Blueprint $table) {
            $table->foreignId('profile_id')->constrained('technician_profiles')->cascadeOnDelete();
            $table->foreignId('specialization_id')->constrained('specialties')->cascadeOnDelete();
            $table->primary(['profile_id', 'specialization_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technician_specializations');
    }
};
