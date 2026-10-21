<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ai_studio_settings')) {
            DB::table('ai_studio_settings')
                ->whereIn('text_model', ['gemini-2.5-flash', 'gemini-2.5-pro'])
                ->update(['text_model' => 'gemini-3.8-flash']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ai_studio_settings')) {
            DB::table('ai_studio_settings')
                ->where('text_model', 'gemini-3.8-flash')
                ->update(['text_model' => 'gemini-2.5-flash']);
        }
    }
};
