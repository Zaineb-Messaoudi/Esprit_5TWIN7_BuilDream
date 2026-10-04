<?php

namespace App\Enums;

/**
 * Status of a rental extension request
 * (table "rental_extensions", column "status").
 *
 * Workflow: the renter asks for more time (PENDING),
 * then the owner/admin either APPROVES or REJECTS the request.
 */
enum ExtensionStatus: string
{
    case PENDING  = 'pending';   // requested, waiting for a decision
    case APPROVED = 'approved';  // accepted: the rental end_date and amount get updated
    case REJECTED = 'rejected';  // refused: the rental stays unchanged

    /** Human readable text shown in the views. */
    public function label(): string
    {
        return match ($this) {
            self::PENDING  => 'Pending',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
        };
    }

    /** Colour name used by the <x-ui.badge> component. */
    public function color(): string
    {
        return match ($this) {
            self::PENDING  => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'error',
        };
    }

    /** [value => label] pairs, used for <select> dropdowns and filters. */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }
        return $options;
    }
}