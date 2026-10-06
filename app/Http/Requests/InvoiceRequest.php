<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() || $this->user()?->isBuyer() || $this->user()?->isOwner();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        $invoice = $this->route('invoice');

        return [
            'reservation_id' => [
            $invoice ? 'sometimes' : 'required',
            'exists:reservations,id',
            Rule::unique('invoices', 'reservation_id')->ignore($invoice?->id),
        ],
        'issue_date' => ['required', 'date'],
        'subtotal'   => ['required', 'numeric', 'min:0'],
        'status'     => ['required', 'in:unpaid,paid'],
        ];
    }
}
