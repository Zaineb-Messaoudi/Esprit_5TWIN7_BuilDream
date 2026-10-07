<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InspectionUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() || $this->user()?->isOwner();
    }

    public function rules(): array
    {
        $isAdmin = $this->user()?->isAdmin();
        
        return [
            'equipment_id' => ['required', 'integer', Rule::exists('equipment', 'id')->when(! $isAdmin, fn ($rule) => $rule->where('owner_id', $this->user()->id))],
            'rental_id' => ['nullable', 'integer', Rule::exists('rentals', 'id')->where('equipment_id', $this->input('equipment_id'))],
            'inspection_date' => ['required', 'date'],
            'condition_before' => ['required', 'string', 'max:255'],
            'condition_after' => ['required', 'string', 'max:255'],
            'damage_detected' => ['required', 'boolean'],
            'comments' => ['nullable', 'string', 'max:10000'],
        ];
    }
}