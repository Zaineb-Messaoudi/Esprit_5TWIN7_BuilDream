<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_usage_logs', function (Blueprint $table) {
            // Quality rating: 1 = thumbs up, -1 = thumbs down, 0 = no feedback
            $table->tinyInteger('quality_rating')->default(0)->after('prompt_hash');
            $table->text('quality_feedback')->nullable()->after('quality_rating');
        });
    }

    public function down(): void
    {
        Schema::table('ai_usage_logs', function (Blueprint $table) {
            $table->dropColumn(['quality_rating', 'quality_feedback']);
        });
    }
};
