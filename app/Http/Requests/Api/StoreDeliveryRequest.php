<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for creating a delivery.
 */
class StoreDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:pickup,dropoff,both'],
            'carrier' => ['nullable', 'string', 'max:50'],
            'pickup_address' => ['required', 'array'],
            'pickup_address.street' => ['required', 'string', 'max:255'],
            'pickup_address.city' => ['required', 'string', 'max:100'],
            'pickup_address.postal_code' => ['required', 'string', 'max:20'],
            'pickup_address.country' => ['required', 'string', 'max:100'],
            'pickup_address.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'pickup_address.longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'dropoff_address' => ['required', 'array'],
            'dropoff_address.street' => ['required', 'string', 'max:255'],
            'dropoff_address.city' => ['required', 'string', 'max:100'],
            'dropoff_address.postal_code' => ['required', 'string', 'max:20'],
            'dropoff_address.country' => ['required', 'string', 'max:100'],
            'dropoff_address.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'dropoff_address.longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'scheduled_pickup_at' => ['nullable', 'date', 'after:now'],
            'scheduled_dropoff_at' => ['nullable', 'date', 'after:now'],
            'dimensions' => ['nullable', 'array'],
            'dimensions.length' => ['nullable', 'numeric', 'min:0'],
            'dimensions.width' => ['nullable', 'numeric', 'min:0'],
            'dimensions.height' => ['nullable', 'numeric', 'min:0'],
            'dimensions.weight' => ['nullable', 'numeric', 'min:0'],
            'special_instructions' => ['nullable', 'string', 'max:1000'],
            'delivery_fee' => ['nullable', 'numeric', 'min:0'],
            'fee_paid_by' => ['nullable', 'in:owner,renter,split'],
        ];
    }
}
