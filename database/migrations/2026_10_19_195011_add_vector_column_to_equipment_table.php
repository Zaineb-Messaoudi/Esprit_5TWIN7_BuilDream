<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only add vector column on PostgreSQL (pgvector extension)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS vector');

            Schema::table('equipment', function (Blueprint $table) {
                // Vector column for semantic search (768 dimensions for nomic-embed-text)
                $table->vector('embedding', 768)->nullable();
            });

            // Create index for vector similarity search
            DB::statement('CREATE INDEX IF NOT EXISTS equipment_embedding_idx ON equipment USING ivfflat (embedding vector_cosine_ops) WITH (lists = 100)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS equipment_embedding_idx');
            Schema::table('equipment', function (Blueprint $table) {
                $table->dropColumn('embedding');
            });
        }
    }
};
