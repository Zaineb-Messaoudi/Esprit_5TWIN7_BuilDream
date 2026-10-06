<?php

namespace App\Http\Controllers\Technical;

use App\Models\Maintenance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MaintenanceController extends TechnicalCrudController
{
    protected function modelClass(): string { return Maintenance::class; }
    protected function resource(): string { return 'maintenances'; }
    protected function title(): string { return 'Maintenances'; }
    protected function eagerLoads(): array { return ['equipment', 'report']; }

    protected function scope(Builder $query, Request $request): Builder
    {
        return $this->isAdmin($request) ? $query : $query->whereHas('equipment', fn (Builder $q) => $q->where('owner_id', $request->user()->id));
    }

    protected function fields(): array
    {
        return [
            'equipment_id' => ['label' => 'Equipment', 'type' => 'select', 'required' => true],
            'start_date' => ['label' => 'Start date', 'type' => 'date', 'required' => true],
            'end_date' => ['label' => 'End date', 'type' => 'date'],
            'reason' => ['label' => 'Reason', 'type' => 'text', 'required' => true],
            'cost' => ['label' => 'Cost (TND)', 'type' => 'number', 'required' => true, 'step' => '0.01'],
            'status' => ['label' => 'Status', 'type' => 'select', 'required' => true],
            'notes' => ['label' => 'Notes', 'type' => 'textarea'],
        ];
    }

    protected function formOptions(Request $request, ?Model $record = null): array
    {
        return [
            'equipment_id' => $this->equipmentOptions($request),
            'status' => ['planned' => __('Planned'), 'in_progress' => __('In progress'), 'completed' => __('Completed')],
        ];
    }

    protected function rules(Request $request, ?Model $record = null): array
    {
        return [
            'equipment_id' => ['required', 'integer', Rule::exists('equipment', 'id')->when(! $this->isAdmin($request), fn ($rule) => $rule->where('owner_id', $request->user()->id))],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:255'],
            'cost' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'status' => ['required', Rule::in(['planned', 'in_progress', 'completed'])],
            'notes' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
