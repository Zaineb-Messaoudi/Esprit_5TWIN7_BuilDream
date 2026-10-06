<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('energy_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('equipment_id')->unique()->constrained('equipment')->cascadeOnDelete();
            $table->unsignedInteger('power_watts')->nullable();
            $table->decimal('voltage', 8, 2)->nullable();
            $table->unsignedInteger('capacity_wh')->nullable();
            $table->decimal('efficiency', 5, 2)->nullable();
            $table->string('technology')->nullable();
            $table->unsignedInteger('max_output')->nullable();
            $table->decimal('operating_duration', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('energy_profiles');
    }
};
