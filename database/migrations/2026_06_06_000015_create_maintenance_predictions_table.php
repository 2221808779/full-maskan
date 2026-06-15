<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('predicted_category', 100);
            $table->unsignedTinyInteger('predicted_category_id');
            $table->unsignedInteger('days_until_next');
            $table->date('predicted_date');
            $table->boolean('is_active')->default(true);
            $table->string('model_used', 50)->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->index('property_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_predictions');
    }
};
