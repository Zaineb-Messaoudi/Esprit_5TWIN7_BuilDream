<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->string('image_url', 2048)->nullable()->after('description');
        });

        Schema::table('equipment', function (Blueprint $table): void {
            $table->string('approval_status', 30)->default('published')->after('status')->index();
            $table->timestamp('reviewed_at')->nullable()->after('approval_status');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table): void {
            $table->dropSoftDeletes();
            $table->dropColumn(['approval_status', 'reviewed_at']);
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropColumn('image_url');
        });
    }
};
