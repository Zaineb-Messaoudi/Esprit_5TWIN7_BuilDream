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
        Schema::create('stripe_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('stripe_account_id')->unique();
            $table->enum('status', ['pending', 'active', 'restricted', 'rejected'])->default('pending');
            $table->boolean('charges_enabled')->default(false);
            $table->boolean('payouts_enabled')->default(false);
            $table->boolean('details_submitted')->default(false);
            $table->json('requirements')->nullable(); // currently_due, eventually_due, past_due, pending_verification
            $table->json('capabilities')->nullable(); // card_payments, transfers
            $table->string('business_type')->nullable(); // individual, company
            $table->string('country')->nullable();
            $table->string('default_currency')->default('tnd');
            $table->json('business_profile')->nullable();
            $table->json('settings')->nullable();
            $table->timestamp('onboarded_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('escrow_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('payee_id')->constrained('users')->cascadeOnDelete();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_transfer_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->decimal('platform_fee', 10, 2)->default(0);
            $table->decimal('stripe_fee', 10, 2)->default(0);
            $table->string('currency', 3)->default('tnd');
            $table->enum('status', ['pending', 'authorized', 'captured', 'held', 'released', 'refunded', 'disputed', 'cancelled'])->default('pending');
            $table->string('stripe_charge_id')->nullable();
            $table->string('stripe_refund_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('authorized_at')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['rental_id', 'status']);
            $table->index(['payer_id', 'status']);
            $table->index(['payee_id', 'status']);
        });

        Schema::create('stripe_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_event_id')->unique();
            $table->string('type'); // payment_intent.succeeded, transfer.created, etc.
            $table->json('payload');
            $table->enum('status', ['pending', 'processed', 'failed', 'ignored'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stripe_webhook_events');
        Schema::dropIfExists('escrow_accounts');
        Schema::dropIfExists('stripe_accounts');
    }
};
