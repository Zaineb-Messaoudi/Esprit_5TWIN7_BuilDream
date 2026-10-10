<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('protection_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->enum('tier', ['basic', 'standard', 'premium', 'enterprise']);
            $table->decimal('price_per_day', 10, 2);
            $table->decimal('coverage_amount', 12, 2);
            $table->decimal('deductible', 10, 2);
            $table->json('covered_risks'); // ['theft', 'accidental_damage', 'weather', 'vandalism', 'electrical', 'mechanical']
            $table->json('exclusions')->nullable();
            $table->integer('max_claim_count')->default(3);
            $table->integer('waiting_period_days')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('terms_and_conditions')->nullable();
            $table->timestamps();
        });

        Schema::create('rental_protections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained()->cascadeOnDelete();
            $table->foreignId('protection_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchased_by')->constrained('users')->cascadeOnDelete();
            $table->decimal('premium_paid', 10, 2);
            $table->timestamp('starts_at');
            $table->timestamp('expires_at');
            $table->enum('status', ['active', 'expired', 'cancelled', 'claimed'])->default('active');
            $table->integer('claims_count')->default(0);
            $table->json('claim_history')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['rental_id', 'status']);
            $table->index(['purchased_by', 'status']);
        });

        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_protection_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reported_by')->constrained('users')->cascadeOnDelete();
            $table->string('claim_number')->unique();
            $table->enum('type', ['theft', 'accidental_damage', 'weather_damage', 'vandalism', 'electrical', 'mechanical', 'other']);
            $table->text('description');
            $table->decimal('estimated_cost', 12, 2);
            $table->decimal('approved_amount', 12, 2)->nullable();
            $table->enum('status', ['submitted', 'under_review', 'approved', 'rejected', 'paid', 'disputed'])->default('submitted');
            $table->json('evidence_photos')->nullable();
            $table->json('documents')->nullable();
            $table->text('adjuster_notes')->nullable();
            $table->foreignId('assessed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at');
            $table->timestamp('assessed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claims');
        Schema::dropIfExists('rental_protections');
        Schema::dropIfExists('protection_plans');
    }
};
