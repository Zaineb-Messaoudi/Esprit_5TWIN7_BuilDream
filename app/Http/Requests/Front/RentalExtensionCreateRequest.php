<?php

namespace App\Http\Requests\Front;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RentalExtensionCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isBuyer();
    }

    public function rules(): array
    {
        $rental = $this->route('rental');
        
        return [
            'new_end_date' => ['required', 'date', 'after:' . ($rental?->end_date?->toDateString() ?? 'today')],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}