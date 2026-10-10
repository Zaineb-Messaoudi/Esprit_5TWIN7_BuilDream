<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MaintenanceReportStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() || $this->user()?->isOwner();
    }

    public function rules(): array
    {
        $isAdmin = $this->user()?->isAdmin();

        $exists = Rule::exists('maintenances', 'id');
        if (! $isAdmin) {
            $exists->whereIn('equipment_id', fn ($q) => $q->select('id')->from('equipment')->where('owner_id', $this->user()->id));
        }

        return [
            'maintenance_id' => ['required', 'integer', $exists, Rule::unique('maintenance_reports', 'maintenance_id')],
            'diagnosis' => ['required', 'string', 'max:10000'],
            'actions_taken' => ['required', 'string', 'max:10000'],
            'parts_replaced' => ['nullable', 'string', 'max:10000'],
            'technician_notes' => ['nullable', 'string', 'max:10000'],
            'report_date' => ['required', 'date'],
        ];
    }
}
