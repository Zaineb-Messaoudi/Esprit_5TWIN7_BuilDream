<?php

namespace App\Enums;

/**
 * Status of a rental contract (table "rental_contracts", column "contract_status").
 */
enum ContractStatus: string
{
    case DRAFT = 'draft';       // contract written but not signed yet
    case SIGNED = 'signed';      // contract signed by the renter
    case TERMINATED = 'terminated';  // contract ended (rental finished or cancelled)

    /** Human readable text shown in the views. */
    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SIGNED => 'Signed',
            self::TERMINATED => 'Terminated',
        };
    }

    /** Colour name used by the <x-ui.badge> component. */
    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'warning',
            self::SIGNED => 'success',
            self::TERMINATED => 'gray',
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
