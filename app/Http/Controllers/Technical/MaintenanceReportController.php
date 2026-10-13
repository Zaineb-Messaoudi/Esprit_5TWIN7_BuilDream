<?php

namespace App\Http\Controllers\Technical;

use App\Models\Maintenance;
use App\Models\MaintenanceReport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MaintenanceReportController extends TechnicalCrudController
{
    protected function modelClass(): string
    {
        return MaintenanceReport::class;
    }

    protected function resource(): string
    {
        return 'reports';
    }

    protected function title(): string
    {
        return 'Maintenance reports';
    }

    protected function eagerLoads(): array
    {
        return ['maintenance.equipment'];
    }

    protected function scope(Builder $query, Request $request): Builder
    {
        return $this->isAdmin($request) ? $query : $query->whereHas('maintenance.equipment', fn (Builder $q) => $q->where('owner_id', $request->user()->id));
    }

    protected function fields(): array
    {
        return [
            'maintenance_id' => ['label' => 'Maintenance', 'type' => 'select', 'required' => true],
            'diagnosis' => ['label' => 'Diagnosis', 'type' => 'textarea', 'required' => true],
            'actions_taken' => ['label' => 'Actions taken', 'type' => 'textarea', 'required' => true],
            'parts_replaced' => ['label' => 'Parts replaced', 'type' => 'textarea'],
            'technician_notes' => ['label' => 'Technician notes', 'type' => 'textarea'],
            'report_date' => ['label' => 'Report date', 'type' => 'date', 'required' => true],
        ];
    }

    protected function formOptions(Request $request, ?Model $record = null): array
    {
        $query = Maintenance::query()->with('equipment')->where(function (Builder $q) use ($record) {
            $q->whereDoesntHave('report');
            if ($record) {
                $q->orWhere('id', $record->maintenance_id);
            }
        });
        if (! $this->isAdmin($request)) {
            $query->whereHas('equipment', fn (Builder $q) => $q->where('owner_id', $request->user()->id));
        }

        return ['maintenance_id' => $query->orderBy('id')->get()->mapWithKeys(
            fn ($maintenance) => [$maintenance->id => '#'.$maintenance->id.' — '.$maintenance->equipment->name]
        )->all()];
    }

    protected function rules(Request $request, ?Model $record = null): array
    {
        $exists = Rule::exists('maintenances', 'id');
        if (! $this->isAdmin($request)) {
            $exists->whereIn('equipment_id', fn ($q) => $q->select('id')->from('equipment')->where('owner_id', $request->user()->id));
        }

        return [
            'maintenance_id' => ['required', 'integer', $exists, Rule::unique('maintenance_reports', 'maintenance_id')->ignore($record?->id)],
            'diagnosis' => ['required', 'string', 'max:10000'],
            'actions_taken' => ['required', 'string', 'max:10000'],
            'parts_replaced' => ['nullable', 'string', 'max:10000'],
            'technician_notes' => ['nullable', 'string', 'max:10000'],
            'report_date' => ['required', 'date'],
        ];
    }
}
