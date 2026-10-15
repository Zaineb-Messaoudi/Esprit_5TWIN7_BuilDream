<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table): void {
            $table->foreign('equipment_id')
                ->references('id')->on('equipment')
                ->restrictOnDelete();
        });

        Schema::table('rentals', function (Blueprint $table): void {
            $table->foreign('reservation_id')
                ->references('id')->on('reservations')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table): void {
            $table->dropForeign(['reservation_id']);
        });

        Schema::table('reservations', function (Blueprint $table): void {
            $table->dropForeign(['equipment_id']);
        });
    }
};
