<?php

namespace App\Http\Requests\Admin;

use App\Enums\ExtensionStatus;
use App\Enums\RentalStatus;
use App\Models\Rental;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation rules used when an admin CREATES an extension request.
 * Business rules checked here (with closures = small custom rules):
 *  - only a PENDING or ACTIVE rental can be extended
 *  - a rental can have only ONE pending request at a time
 *  - the new end date must be after the current end date of the rental
 */
class AdminRentalExtensionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rental_id' => [
                'required',
                'integer',
                'exists:rentals,id',
                // Closure: receives the field name, its value and a $fail callback
                function (string $attribute, mixed $value, \Closure $fail) {
                    $rental = Rental::find($value);
                    if (! $rental) {
                        return; // "exists" already reports this error
                    }

                    // Strict comparison with the enum cases of RentalStatus
                    if (! in_array($rental->status, [RentalStatus::PENDING, RentalStatus::ACTIVE], true)) {
                        $fail('Only a pending or active rental can be extended.');
                    }

                    // At most one pending request per rental
                    if ($rental->extensions()->where('status', ExtensionStatus::PENDING->value)->exists()) {
                        $fail('This rental already has a pending extension request.');
                    }
                },
            ],

            // "bail" stops at the first failing rule, so the closure never receives an invalid date
            'new_end_date' => [
                'bail',
                'required',
                'date',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $rental = Rental::find($this->input('rental_id'));
                    if ($rental && Carbon::parse($value)->lte($rental->end_date)) {
                        $fail('The new end date must be after the current end date of the rental ('
                            .$rental->end_date->format('d/m/Y').').');
                    }
                },
            ],

            // Optional: if empty, the controller computes it from the rental price per day
            'additional_amount' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],

            // Why the renter wants more time (optional)
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /** Nicer field names inside the error messages. */
    public function attributes(): array
    {
        return [
            'rental_id' => 'rental',
            'new_end_date' => 'new end date',
            'additional_amount' => 'additional amount',
        ];
    }
}
