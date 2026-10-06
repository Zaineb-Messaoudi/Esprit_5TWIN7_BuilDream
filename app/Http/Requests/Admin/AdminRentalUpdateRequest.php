<?php

namespace App\Http\Requests\Admin;

use App\Enums\RentalStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation rules used when an admin EDITS a rental.
 * Same rules as the store request, except for the "unique" check on
 * reservation_id which must ignore the rental being edited.
 * (The reference is generated automatically and cannot be edited.)
 */
class AdminRentalUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],

            'equipment_id' => ['required', 'integer', 'exists:equipment,id'],

            // $this->route('rental') is the Rental being edited (route model binding).
            // ignore(...) lets the rental keep its own reservation_id without an error.
            'reservation_id' => [
                'nullable',
                'integer',
                'min:1',
                Rule::unique('rentals', 'reservation_id')->ignore($this->route('rental')),
            ],

            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],

            'total_amount' => ['required', 'numeric', 'min:0', 'max:99999999.99'],

            'status' => ['required', Rule::enum(RentalStatus::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'user_id'        => 'renter',
            'equipment_id'   => 'equipment',
            'reservation_id' => 'reservation',
            'start_date'     => 'start date',
            'end_date'       => 'end date',
            'total_amount'   => 'total amount',
        ];
    }
}
