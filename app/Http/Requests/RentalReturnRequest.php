<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RentalReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() || $this->user()?->isBuyer() || $this->user()?->isOwner();
    }

    public function rules(): array
    {
        return [
            'condition_before' => ['required', 'string', 'max:255'],
            'condition_after' => ['required', 'string', 'max:255'],
            'damage_detected' => ['required', 'boolean'],
            'comments' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
