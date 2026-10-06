<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Reservation;
use App\Models\Rental;

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
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            // Totals and lifecycle state are computed/controlled server-side.
            'total_amount' => ['prohibited'],
            'status' => [$this->user()?->isAdmin() ? 'sometimes' : 'prohibited', 'in:pending,confirmed,cancelled'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $reservationId = $this->route('reservation')?->id;
            $overlap = Reservation::query()
                ->where('equipment_id', $this->input('equipment_id'))
                ->where('status', '!=', 'cancelled')
                ->when($reservationId, fn ($query) => $query->where('id', '!=', $reservationId))
                ->where('start_date', '<=', $this->input('end_date'))
                ->where('end_date', '>=', $this->input('start_date'))
                ->exists();

            $rentalOverlap = Rental::query()
                ->where('equipment_id', $this->input('equipment_id'))
                ->whereNotIn('status', ['cancelled', 'completed'])
                ->when($reservationId, fn ($query) => $query->whereDoesntHave('reservation', fn ($reservation) => $reservation->whereKey($reservationId)))
                ->where('start_date', '<=', $this->input('end_date'))
                ->where('end_date', '>=', $this->input('start_date'))
                ->exists();

            if ($overlap || $rentalOverlap) {
                $validator->errors()->add('start_date', __('This equipment is already reserved for the selected dates.'));
            }
        });
    }
}
