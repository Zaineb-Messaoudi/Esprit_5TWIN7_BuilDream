<?php

namespace App\Http\Requests\Technical;

use Illuminate\Validation\Rule;

class StoreMaintenanceReportRequest extends TechnicalRecordRequest
{
    public function validationData(): array
    {
        return $this->all();
    }

    public function rules(): array
    {
        $maintenance = Rule::exists('maintenances', 'id');
        if (! $this->isAdminActor()) {
            $maintenance->whereIn('equipment_id', fn ($query) => $query->select('id')->from('equipment')->where('owner_id', $this->user()->getKey()));
        }

        return [
            'maintenance_id' => ['required', 'integer', $maintenance, Rule::unique('maintenance_reports', 'maintenance_id')->ignore($this->route('report')?->getKey())],
            'diagnosis' => ['required', 'string', 'max:10000'],
            'actions_taken' => ['required', 'string', 'max:10000'],
            'parts_replaced' => ['nullable', 'string', 'max:10000'],
            'technician_notes' => ['nullable', 'string', 'max:10000'],
            'report_date' => ['required', 'date'],
        ];
    }
}
