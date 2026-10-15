<?php

namespace App\Enums;

enum DepositStatus: string
{
    case PENDING = 'pending';
    case HELD = 'held';
    case RELEASED = 'released';
    case FORFEITED = 'forfeited';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::HELD => 'Held',
            self::RELEASED => 'Released',
            self::FORFEITED => 'Forfeited',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::HELD => 'brand',
            self::RELEASED => 'success',
            self::FORFEITED => 'error',
        };
    }
}
