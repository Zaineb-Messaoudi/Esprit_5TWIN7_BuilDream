<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiPrompt extends Model
{
    protected $fillable = [
        'endpoint',
        'prompt_hash',
        'system_prompt',
        'user_prompt_template',
        'variables',
        'model',
        'provider',
        'is_active',
        'usage_count',
        'last_used_at',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
        'usage_count' => 'integer',
        'last_used_at' => 'datetime',
    ];

    public static function computeHash(string $systemPrompt, string $userPromptTemplate): string
    {
        return hash('sha256', $systemPrompt.'|'.$userPromptTemplate);
    }

    public static function register(
        string $endpoint,
        string $systemPrompt,
        string $userPromptTemplate,
        array $variables = [],
        ?string $model = null,
        ?string $provider = null
    ): self {
        $hash = self::computeHash($systemPrompt, $userPromptTemplate);

        return self::updateOrCreate(
            ['endpoint' => $endpoint, 'prompt_hash' => $hash],
            [
                'system_prompt' => $systemPrompt,
                'user_prompt_template' => $userPromptTemplate,
                'variables' => $variables,
                'model' => $model ?? config('ai.model'),
                'provider' => $provider ?? config('ai.provider'),
                'is_active' => true,
            ]
        );
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    public function getPromptWithVariables(array $variables): array
    {
        $userPrompt = $this->user_prompt_template;
        foreach ($variables as $key => $value) {
            $userPrompt = str_replace('{'.$key.'}', $value, $userPrompt);
        }

        return [
            'system' => $this->system_prompt,
            'user' => $userPrompt,
        ];
    }
}
