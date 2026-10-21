<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservation_assistant_requests', function (Blueprint $table): void {
            $table->string('city', 100)->nullable()->after('address');
            $table->index(['service_type', 'city', 'urgency']);
        });
    }

    public function down(): void
    {
        Schema::table('reservation_assistant_requests', function (Blueprint $table): void {
            $table->dropIndex(['service_type', 'city', 'urgency']);
            $table->dropColumn('city');
        });
    }
};
