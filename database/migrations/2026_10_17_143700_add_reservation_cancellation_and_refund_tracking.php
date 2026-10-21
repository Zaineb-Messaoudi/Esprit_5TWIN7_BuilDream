<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('cancellation_status')->nullable()->after('status');
            $table->text('cancellation_reason')->nullable()->after('cancellation_status');
            $table->timestamp('cancellation_requested_at')->nullable()->after('cancellation_reason');
            $table->foreignId('cancellation_reviewed_by')->nullable()->after('cancellation_requested_at')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('cancellation_reviewed_at')->nullable()->after('cancellation_reviewed_by');
            $table->decimal('cancellation_refund_percentage', 5, 2)->nullable()->after('cancellation_reviewed_at');
            $table->decimal('cancellation_refund_total', 10, 2)->nullable()->after('cancellation_refund_percentage');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('refund_status')->default('none')->after('status');
            $table->unsignedSmallInteger('refund_attempts')->default(0)->after('refund_status');
            $table->decimal('refund_target_amount', 10, 2)->default(0)->after('refund_attempts');
            $table->decimal('refunded_amount', 10, 2)->default(0)->after('refund_target_amount');
            $table->string('stripe_payment_intent_id')->nullable()->index()->after('refund_status');
            $table->string('stripe_refund_id')->nullable()->unique()->after('stripe_payment_intent_id');
            $table->timestamp('refunded_at')->nullable()->after('stripe_refund_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['stripe_refund_id']);
            $table->dropIndex(['stripe_payment_intent_id']);
            $table->dropColumn([
                'refund_status',
                'refund_attempts',
                'refund_target_amount',
                'refunded_amount',
                'stripe_payment_intent_id',
                'stripe_refund_id',
                'refunded_at',
            ]);
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cancellation_reviewed_by');
            $table->dropColumn([
                'cancellation_status',
                'cancellation_reason',
                'cancellation_requested_at',
                'cancellation_reviewed_at',
                'cancellation_refund_percentage',
                'cancellation_refund_total',
            ]);
        });
    }
};
