<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiStudioSetting extends Model
{
    protected $attributes = ['text_model' => 'openai/gpt-oss-20b', 'max_output_tokens' => 2048, 'temperature' => 0.4];

    protected $fillable = ['user_id', 'text_model', 'max_output_tokens', 'temperature'];

    protected function casts(): array
    {
        return ['max_output_tokens' => 'integer', 'temperature' => 'float'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
