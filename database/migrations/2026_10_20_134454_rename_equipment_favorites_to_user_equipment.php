<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('equipment_favorites', 'user_equipment');
    }

    public function down(): void
    {
        Schema::rename('user_equipment', 'equipment_favorites');
    }
};