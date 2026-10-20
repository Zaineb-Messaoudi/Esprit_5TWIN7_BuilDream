<?php

namespace App\Http\Requests\Technical;

use App\Models\Rental;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreInspectionRequest extends TechnicalRecordRequest
{
    public function validationData(): array
    {
        return $this->all();
    }

    public function rules(): array
    {
        $rentalRule = ['nullable', 'integer'];
        if (class_exists(Rental::class) && Schema::hasTable('rentals')) {
            $rentalRule[] = Rule::exists('rentals', 'id')->where('equipment_id', $this->input('equipment_id'));
        } else {
            $rentalRule[] = Rule::prohibitedIf(true);
        }

        return [
            'equipment_id' => $this->ownerEquipmentRule(),
            'rental_id' => $rentalRule,
            'inspection_checklist_id' => ['nullable', 'integer', Rule::exists('inspection_checklists', 'id')->where('is_active', true)],
            'inspection_date' => ['required', 'date'],
            'condition_before' => ['required', 'string', 'max:255'],
            'condition_after' => ['required', 'string', 'max:255'],
            'damage_detected' => ['required', 'boolean'],
            'checklist_results' => ['nullable', 'required_with:inspection_checklist_id', 'array'],
            'checklist_results.*.item_id' => ['required', 'integer', 'distinct', Rule::exists('inspection_checklist_items', 'id')],
            'checklist_results.*.passed' => ['required', 'boolean'],
            'comments' => ['nullable', 'string', 'max:10000'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (! $this->input('inspection_checklist_id') || $validator->errors()->has('checklist_results')) {
                return;
            }

            $submittedIds = collect($this->input('checklist_results', []))->pluck('item_id')->map(fn ($id) => (int) $id)->sort()->values();
            $expectedIds = \App\Models\InspectionChecklistItem::query()
                ->where('inspection_checklist_id', $this->integer('inspection_checklist_id'))
                ->pluck('id')->map(fn ($id) => (int) $id)->sort()->values();

            if ($submittedIds->all() !== $expectedIds->all()) {
                $validator->errors()->add('checklist_results', __('Every checklist item must have a result.'));
            }
        }];
    }
}
