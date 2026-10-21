<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_stage')->default('full')->after('amount');
            $table->date('due_date')->nullable()->after('payment_date');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->timestamp('balance_reminder_sent_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['payment_stage', 'due_date']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('balance_reminder_sent_at');
        });
    }
};
