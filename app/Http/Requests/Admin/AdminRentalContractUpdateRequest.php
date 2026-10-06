<?php

namespace App\Http\Requests\Admin;

use App\Enums\ContractStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation rules used when an admin EDITS a rental contract.
 * The rental cannot be changed once the contract exists, and the contract
 * number is generated automatically: so neither field is validated here.
 */
class AdminRentalContractUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'terms' => ['required', 'string', 'max:5000'],
            'deposit_amount' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'contract_status' => ['required', Rule::enum(ContractStatus::class)],
            'signed_at' => ['nullable', 'date'],
        ];
    }

    public function attributes(): array
    {
        return [
            'deposit_amount'  => 'deposit amount',
            'contract_status' => 'contract status',
            'signed_at'       => 'signature date',
        ];
    }
}