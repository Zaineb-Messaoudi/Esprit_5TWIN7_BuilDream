<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation rules used when an admin EDITS a PENDING extension request.
 * The rental and the old end date cannot change; the status changes only through
 * the Approve / Reject buttons.
 */
class AdminRentalExtensionUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // $this->route('rental_extension') is the extension being edited (route model binding)
        $oldEndDate = $this->route('rental_extension')->old_end_date->format('Y-m-d');

        return [
            // The new end date must stay after the end date the rental had at request time
            'new_end_date' => ['required', 'date', 'after:'.$oldEndDate],
            'additional_amount' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'new_end_date' => 'new end date',
            'additional_amount' => 'additional amount',
        ];
    }
}
