<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('problem_description');
            $table->string('ai_category', 255)->nullable();
            $table->decimal('ai_accuracy', 5, 2)->nullable();
            $table->string('category', 255)->nullable();
            $table->string('priority', 50)->nullable();
            $table->unsignedTinyInteger('category_id')->nullable()->index();
            $table->string('status')->default('pending');
            $table->text('technician_notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('property_id');
            $table->index('technician_id');
            $table->index('status');
            $table->index('ai_category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
    }
};
