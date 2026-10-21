<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReservationRequest extends FormRequest
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
            'equipment_id' => ['required', 'exists:equipment,id'],
            'user_id' => ['sometimes', 'exists:users,id'],
            'start_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            // Totals and lifecycle state are computed/controlled server-side.
            'total_amount' => ['prohibited'],
            'status' => [$this->user()?->isAdmin() ? 'sometimes' : 'prohibited', 'in:pending,confirmed,cancelled'],
        ];
    }
}
