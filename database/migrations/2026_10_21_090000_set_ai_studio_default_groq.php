<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_studio_settings', function (Blueprint $table) {
            $table->string('text_model')->default('openai/gpt-oss-20b')->change();
        });

        DB::table('ai_studio_settings')
            ->where('text_model', '!=', 'openai/gpt-oss-20b')
            ->update(['text_model' => 'openai/gpt-oss-20b']);
    }

    public function down(): void
    {
        Schema::table('ai_studio_settings', function (Blueprint $table) {
            $table->string('text_model')->default('gemini-3.8-flash')->change();
        });

        DB::table('ai_studio_settings')
            ->where('text_model', 'openai/gpt-oss-20b')
            ->update(['text_model' => 'gemini-3.8-flash']);
    }
};
