<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_prompts', function (Blueprint $table) {
            $table->id();
            $table->string('endpoint'); // e.g., 'natural-language-search', 'chatbot'
            $table->string('prompt_hash', 64); // SHA-256 of the prompt
            $table->text('system_prompt');
            $table->text('user_prompt_template');
            $table->json('variables')->nullable(); // Variable names used in template
            $table->string('model')->nullable(); // Model used when this prompt was active
            $table->string('provider')->nullable(); // Provider when this prompt was active
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->unique(['endpoint', 'prompt_hash']);
            $table->index(['endpoint', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_prompts');
    }
};
