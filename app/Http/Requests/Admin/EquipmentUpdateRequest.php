<?php

namespace App\Http\Requests\Admin;

class EquipmentUpdateRequest extends EquipmentStoreRequest
{
    public function rules(): array
    {
        $equipment = $this->route('equipment');

        return self::equipmentRules($equipment?->id);
    }
}
