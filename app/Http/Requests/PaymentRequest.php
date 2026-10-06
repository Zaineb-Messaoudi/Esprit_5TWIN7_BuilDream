<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
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
        return [
            'reservation_id' => ['required', 'exists:reservations,id'],
        'amount'         => ['required', 'numeric', 'min:0.01'],
        'payment_date'   => ['required', 'date'],
        'status'         => ['required', 'in:pending,paid,failed'],
        'payment_method' => ['required', 'in:CARD,BANK_TRANSFER,CASH'],
        ];
    }


    public function messages(): array
{
    return [
        'amount.min'              => 'Le montant doit être supérieur à 0.',
        'payment_method.in'       => 'Mode de paiement invalide.',
        'reservation_id.required' => 'Veuillez choisir une réservation.',
    ];
}
}
