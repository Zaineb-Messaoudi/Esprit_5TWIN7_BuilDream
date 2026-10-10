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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('billing_cycle', ['weekly', 'monthly', 'quarterly', 'yearly']);
            $table->decimal('price_per_cycle', 10, 2);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->integer('min_commitment_cycles')->default(1);
            $table->integer('max_subscriptions')->nullable();
            $table->boolean('auto_renew')->default(true);
            $table->boolean('allow_pause')->default(true);
            $table->integer('pause_max_days')->default(30);
            $table->json('included_services')->nullable(); // ['maintenance', 'insurance', 'priority_support', 'free_upgrades']
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('renter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('subscription_number')->unique();
            $table->timestamp('starts_at');
            $table->timestamp('next_billing_at');
            $table->timestamp('ends_at')->nullable();
            $table->enum('status', ['pending', 'active', 'paused', 'cancelled', 'expired'])->default('pending');
            $table->decimal('current_price_per_cycle', 10, 2);
            $table->integer('billing_cycles_completed')->default(0);
            $table->integer('pauses_remaining')->default(3);
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->json('paused_cycles')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['renter_id', 'status']);
            $table->index(['owner_id', 'status']);
            $table->index(['next_billing_at', 'status']);
        });

        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->string('payment_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('transaction_reference')->nullable();
            $table->timestamp('billing_period_start');
            $table->timestamp('billing_period_end');
            $table->timestamp('paid_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->index(['subscription_id', 'status']);
        });

        Schema::create('seasonal_pricing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // 'Summer Peak', 'Winter Off-season', 'Holiday Premium'
            $table->date('starts_at');
            $table->date('ends_at');
            $table->decimal('price_multiplier', 5, 2); // 1.0 = base price, 1.5 = 50% increase, 0.8 = 20% discount
            $table->json('applicable_days')->nullable(); // ['monday', 'tuesday', ...] or null for all
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0); // Higher priority wins on overlap
            $table->timestamps();

            $table->index(['equipment_id', 'starts_at', 'ends_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seasonal_pricing');
        Schema::dropIfExists('subscription_payments');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('subscription_plans');
    }
};
