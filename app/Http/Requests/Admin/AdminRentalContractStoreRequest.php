<?php

namespace App\Http\Requests\Admin;

use App\Enums\ContractStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation rules used when an admin CREATES a rental contract.
 * Laravel runs them automatically BEFORE the controller method.
 * On failure the user goes back to the form with the error messages
 * (@error) and the typed values (old()).
 */
class AdminRentalContractStoreRequest extends FormRequest
{
    /** The route group already limits these pages to admins. */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // The rental must exist AND must not have a contract yet
            // (a rental has exactly ONE contract, relation 1-1).
            'rental_id' => ['required', 'integer', 'exists:rentals,id', 'unique:rental_contracts,rental_id'],

            // Terms and conditions text
            'terms' => ['required', 'string', 'max:5000'],

            // Security deposit (0 = no deposit)
            'deposit_amount' => ['required', 'numeric', 'min:0', 'max:99999999.99'],

            // Must be one of the ContractStatus enum values (draft / signed / terminated)
            'contract_status' => ['required', Rule::enum(ContractStatus::class)],

            // Optional: if empty and the contract is signed, the controller uses "now"
            'signed_at' => ['nullable', 'date'],
        ];
    }

    /** Custom error message for the 1-1 rule. */
    public function messages(): array
    {
        return [
            'rental_id.unique' => 'This rental already has a contract.',
        ];
    }

    /** Nicer field names inside the error messages. */
    public function attributes(): array
    {
        return [
            'rental_id'       => 'rental',
            'deposit_amount'  => 'deposit amount',
            'contract_status' => 'contract status',
            'signed_at'       => 'signature date',
        ];
    }
}