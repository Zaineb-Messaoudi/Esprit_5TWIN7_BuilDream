<?php

namespace App\Http\Requests\Admin;

use App\Enums\RentalStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

/**
 * Validation rules used when an admin CREATES a rental.
 * Laravel runs these rules automatically BEFORE the controller method.
 * If a rule fails, the user goes back to the form with the error messages
 * (shown by @error) and the typed values (restored by old()).
 */
class AdminRentalStoreRequest extends FormRequest
{
    /** Who may send this form? The route group already limits it to admins. */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // The renter must be an existing user
            'user_id' => ['required', 'integer', 'exists:users,id'],

            // The "equipment" table belongs to Student 1. Until it is merged we only
            // check that it is a number; afterwards we also check that it exists.
            'equipment_id' => [
                'required',
                'integer',
                Schema::hasTable('equipment') ? Rule::exists('equipment', 'id') : 'min:1',
            ],

            // Optional. A reservation can become only ONE rental, hence "unique".
            // (We do not check "exists" yet: Student 4's table is not merged.)
            'reservation_id' => ['nullable', 'integer', 'min:1', 'unique:rentals,reservation_id'],

            'start_date' => ['required', 'date'],
            // The rental cannot end before it starts
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],

            'total_amount' => ['required', 'numeric', 'min:0', 'max:99999999.99'],

            // The status must be one of the RentalStatus enum values
            'status' => ['required', Rule::enum(RentalStatus::class)],
        ];
    }

    /** Nicer field names inside the error messages. */
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