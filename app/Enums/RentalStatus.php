<?php

namespace App\Enums;

/**
 * Lifecycle of a rental (table "rentals", column "status").
 *
 * Stored in the database as a plain string (the "value"),
 * but used in PHP code as an enum so we never mistype a status.
 */
enum RentalStatus: string
{
    case PENDING   = 'pending';    // created, rental period not started yet
    case ACTIVE    = 'active';     // equipment is currently with the renter
    case COMPLETED = 'completed';  // equipment returned, rental finished
    case CANCELLED = 'cancelled';  // rental cancelled before completion

    /** Human readable text shown in the views (same idea as UserRole::label()). */
    public function label(): string
    {
        return match ($this) {
            self::PENDING   => 'Pending',
            self::ACTIVE    => 'Active',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }

    /** Colour name used by the <x-ui.badge> component to display the status. */
    public function color(): string
    {
        return match ($this) {
            self::PENDING   => 'warning',
            self::ACTIVE    => 'success',
            self::COMPLETED => 'gray',
            self::CANCELLED => 'error',
        };
    }

    /**
     * List of [value => label] pairs.
     * Handy to fill a <select> dropdown or to validate a filter.
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }
        return $options;
    }
}