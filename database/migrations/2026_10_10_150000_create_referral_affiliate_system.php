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
        Schema::create('referral_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->enum('type', ['referrer', 'referee', 'both']);
            $table->decimal('referrer_reward', 10, 2)->default(0);
            $table->decimal('referee_discount', 5, 2)->default(0); // percentage
            $table->integer('max_uses')->nullable();
            $table->integer('uses_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });

        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_code_id')->constrained()->cascadeOnDelete();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referee_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['pending', 'completed', 'expired', 'cancelled'])->default('pending');
            $table->decimal('referrer_reward_earned', 10, 2)->default(0);
            $table->decimal('referee_discount_applied', 10, 2)->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['referral_code_id', 'referee_id']);
            $table->index(['referrer_id', 'status']);
            $table->index(['referee_id', 'status']);
        });

        Schema::create('referral_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['referrer_reward', 'referee_discount', 'bonus']);
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'available', 'claimed', 'expired'])->default('pending');
            $table->timestamp('available_at')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('affiliate_programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('base_commission_rate', 5, 2); // percentage
            $table->decimal('tier_1_commission', 5, 2)->nullable(); // for sub-affiliates
            $table->decimal('tier_2_commission', 5, 2)->nullable();
            $table->integer('cookie_duration_days')->default(30);
            $table->decimal('min_payout_threshold', 10, 2)->default(50);
            $table->boolean('is_active')->default(true);
            $table->json('terms')->nullable();
            $table->timestamps();
        });

        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('referral_code')->unique();
            $table->foreignId('parent_affiliate_id')->nullable()->constrained('affiliates')->nullOnDelete();
            $table->enum('tier', ['bronze', 'silver', 'gold', 'platinum'])->default('bronze');
            $table->decimal('commission_rate', 5, 2);
            $table->enum('status', ['pending', 'active', 'suspended', 'terminated'])->default('pending');
            $table->decimal('total_earnings', 12, 2)->default(0);
            $table->decimal('pending_earnings', 12, 2)->default(0);
            $table->decimal('paid_earnings', 12, 2)->default(0);
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['affiliate_program_id', 'status']);
            $table->index(['user_id', 'status']);
        });

        Schema::create('affiliate_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('referral_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('commission_number')->unique();
            $table->decimal('amount', 12, 2);
            $table->decimal('rate_applied', 5, 2);
            $table->enum('status', ['pending', 'approved', 'paid', 'cancelled'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['affiliate_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_commissions');
        Schema::dropIfExists('affiliates');
        Schema::dropIfExists('affiliate_programs');
        Schema::dropIfExists('referral_rewards');
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('referral_codes');
    }
};
