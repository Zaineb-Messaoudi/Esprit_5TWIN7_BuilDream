<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case OWNER = 'owner';
    case BUYER = 'buyer';
    /** @deprecated Legacy accounts are treated as buyers. */
    case USER = 'user';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => __('Administrator'),
            self::OWNER => __('Equipment Owner'),
            self::BUYER => __('Buyer'),
            self::USER => __('Buyer (legacy)'),
        };
    }
}
