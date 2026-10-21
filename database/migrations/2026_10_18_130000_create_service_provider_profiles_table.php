<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_provider_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('business_name');
            $table->text('description')->nullable();
            $table->json('specialties');
            $table->json('service_areas');
            $table->unsignedSmallInteger('experience_years')->default(0);
            $table->decimal('minimum_project_budget', 10, 2)->nullable();
            $table->date('available_from')->nullable();
            $table->boolean('accepting_requests')->default(false)->index();
            $table->string('contact_email')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_provider_profiles');
    }
};
