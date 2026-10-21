<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop pgvector index if exists (PostgreSQL only)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS equipment_embedding_idx');
        }

        Schema::table('equipment', function (Blueprint $table) {
            // Drop vector column if exists (PostgreSQL)
            if (DB::getDriverName() === 'pgsql') {
                $table->dropColumn('embedding');
            }
            // Add JSON column for embeddings (works on MySQL/PostgreSQL)
            $table->json('embedding')->nullable()->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn('embedding');
        });

        // Recreate pgvector column on PostgreSQL
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS vector');
            Schema::table('equipment', function (Blueprint $table) {
                $table->vector('embedding', 768)->nullable();
            });
            DB::statement('CREATE INDEX IF NOT EXISTS equipment_embedding_idx ON equipment USING ivfflat (embedding vector_cosine_ops) WITH (lists = 100)');
        }
    }
};
