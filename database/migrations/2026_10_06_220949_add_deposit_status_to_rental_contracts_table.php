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
        Schema::table('rental_contracts', function (Blueprint $table) {
            $table->string('deposit_status', 30)->default('pending')->after('deposit_amount');
            $table->timestamp('deposit_held_at')->nullable()->after('deposit_status');
            $table->timestamp('deposit_released_at')->nullable()->after('deposit_held_at');
            $table->text('deposit_notes')->nullable()->after('deposit_released_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rental_contracts', function (Blueprint $table) {
            $table->dropColumn(['deposit_status', 'deposit_held_at', 'deposit_released_at', 'deposit_notes']);
        });
    }
};
