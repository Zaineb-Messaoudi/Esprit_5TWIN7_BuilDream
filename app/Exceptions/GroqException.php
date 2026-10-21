<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when a call to Groq cannot give a usable answer.
 * $reason tells why (not_configured, network, rate_limited, http_error, empty_answer, invalid_format)
 * and userMessage() gives a short text that is safe to show on a page.
 */
class GroqException extends RuntimeException
{
    public function __construct(public readonly string $reason, string $message = '')
    {
        parent::__construct($message !== '' ? $message : $reason);
    }

    /** Friendly message for the user: no technical detail, never the key. */
    public function userMessage(): string
    {
        return match ($this->reason) {
            'not_configured' => 'The AI explanation is not available on this server.',
            'rate_limited' => 'The AI service is busy. Please try again in a minute.',
            'network' => 'The AI service cannot be reached right now. Please try again later.',
            default => 'The AI explanation is not available right now. Please read the contract text.',
        };
    }
}
