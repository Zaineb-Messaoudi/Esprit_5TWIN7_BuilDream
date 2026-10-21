<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class EnergyProfileStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'power_watts' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'voltage' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'capacity_wh' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'efficiency' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'technology' => ['nullable', 'string', 'max:100'],
            'max_output' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'operating_duration' => ['nullable', 'numeric', 'min:0', 'max:100000'],
        ];
    }
}
