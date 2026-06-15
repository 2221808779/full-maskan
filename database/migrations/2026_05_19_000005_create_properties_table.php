<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('location', 500);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('price', 10, 2);
            $table->string('property_type');
            $table->integer('rooms_count')->nullable();
            $table->integer('bathrooms_count')->nullable();
            $table->integer('area')->nullable();
            $table->boolean('has_pool')->default(false);
            $table->boolean('has_parking')->default(false);
            $table->boolean('has_ac')->default(false);
            $table->boolean('has_furniture')->default(false);
            $table->string('status')->default('active');
            $table->json('unavailable_dates')->nullable();
            $table->decimal('rating', 3, 1)->default(0);
            $table->integer('review_count')->default(0);
            $table->timestamps();

            $table->index('status');
            $table->index('property_type');
            $table->index('price');
            $table->index('owner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
