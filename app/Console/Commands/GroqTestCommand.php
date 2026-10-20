<?php

namespace App\Console\Commands;

use App\Exceptions\GroqException;
use App\Services\GroqClient;
use Illuminate\Console\Command;

/**
 * Checks that the Groq key and model work, WITHOUT ever displaying the key.
 * Usage:  php artisan groq:test
 */
class GroqTestCommand extends Command
{
    protected $signature = 'groq:test';

    protected $description = 'Checks that the Groq API key and model in .env work (the key is never displayed)';

    public function handle(GroqClient $groq): int
    {
        $this->line('Model: ' . config('groq.model'));

        if (! $groq->isConfigured()) {
            $this->error('GROQ_API_KEY is empty. Add it to your .env file, then run: php artisan config:clear');

            return self::FAILURE;
        }

        try {
            $answer = $groq->chat(
                [['role' => 'user', 'content' => 'Reply with the single word: OK']],
                ['max_tokens' => 500]
            );
        } catch (GroqException $e) {
            $this->error("Groq call failed ({$e->reason}): " . $e->getMessage());

            return self::FAILURE;
        }

        $this->info('Groq answered: ' . $answer);

        return self::SUCCESS;
    }
}