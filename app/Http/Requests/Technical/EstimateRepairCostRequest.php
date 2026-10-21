<?php

namespace App\Http\Requests\Technical;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EstimateRepairCostRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('technical-access') ?? false; }

    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'integer', 'min:1'],
            'severity' => ['required', Rule::in(['minor', 'moderate', 'severe'])],
        ];
    }
}
