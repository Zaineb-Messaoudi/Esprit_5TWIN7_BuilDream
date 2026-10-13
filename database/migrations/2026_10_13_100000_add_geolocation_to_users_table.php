<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->decimal('address_latitude', 10, 8)->nullable()->after('address');
            $table->decimal('address_longitude', 11, 8)->nullable()->after('address_latitude');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['address_latitude', 'address_longitude']);
        });
    }
};
