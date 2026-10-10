<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

/**
 * Shared mailable used by every SolarShare notification.
 */
class NotificationMail extends Mailable
{
    public function __construct(
        public readonly string $mailSubject,
        public readonly string $mailBlade,
        public readonly array $mailViewData,
    ) {}

    public function build()
    {
        return $this->subject($this->mailSubject)
            ->view($this->mailBlade, $this->mailViewData);
    }
}
