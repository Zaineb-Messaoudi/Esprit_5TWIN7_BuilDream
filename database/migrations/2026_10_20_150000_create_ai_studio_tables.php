<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_studio_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('text_model')->default('gemini-3.8-flash');
            $table->unsignedSmallInteger('max_output_tokens')->default(2048);
            $table->decimal('temperature', 2, 1)->default(0.4);
            $table->timestamps();
        });

        Schema::create('ai_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('tool', 20)->index();
            $table->text('prompt');
            $table->longText('result_text')->nullable();
            $table->string('media_path')->nullable();
            $table->string('media_mime_type', 100)->nullable();
            $table->string('model', 120);
            $table->string('status', 20)->default('succeeded')->index();
            $table->unsignedInteger('input_tokens')->nullable();
            $table->unsignedInteger('output_tokens')->nullable();
            $table->string('error_message')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_generations');
        Schema::dropIfExists('ai_studio_settings');
    }
};
