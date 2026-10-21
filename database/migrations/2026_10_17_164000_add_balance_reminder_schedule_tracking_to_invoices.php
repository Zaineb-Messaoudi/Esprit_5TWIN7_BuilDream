<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->timestamp('balance_reminder_three_days_sent_at')->nullable();
            $table->timestamp('balance_reminder_due_today_sent_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'balance_reminder_three_days_sent_at',
                'balance_reminder_due_today_sent_at',
            ]);
        });
    }
};
