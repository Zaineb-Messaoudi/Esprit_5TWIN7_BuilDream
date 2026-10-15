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
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('aggregate_rating', 3, 1)->nullable()->after('profile_photo_path');
            $table->unsignedInteger('reviews_count')->default(0)->after('aggregate_rating');
            $table->json('detailed_ratings')->nullable()->after('reviews_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['aggregate_rating', 'reviews_count', 'detailed_ratings']);
        });
    }
};
