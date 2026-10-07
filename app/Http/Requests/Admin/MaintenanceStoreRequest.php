<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MaintenanceStoreRequest extends FormRequest
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
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:255'],
            'cost' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'status' => ['required', Rule::in(['planned', 'in_progress', 'completed'])],
            'notes' => ['nullable', 'string', 'max:10000'],
        ];
    }
}