<?php

namespace App\Http\Requests\Technical;

class StoreMaintenanceRequest extends TechnicalRecordRequest
{
    public function validationData(): array
    {
        return $this->all();
    }

    public function rules(): array
    {
        return [
            'equipment_id' => $this->ownerEquipmentRule(),
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:255'],
            'cost' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'status' => ['required', 'in:planned,in_progress,completed'],
            'notes' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
