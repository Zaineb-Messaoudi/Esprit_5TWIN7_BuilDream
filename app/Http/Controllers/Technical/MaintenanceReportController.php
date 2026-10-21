<?php

namespace App\Http\Controllers\Technical;

use App\Http\Requests\Technical\StoreMaintenanceReportRequest;
use App\Models\Maintenance;
use App\Models\MaintenanceReport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

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
        $query = Maintenance::query()->with('equipment')->where(function ($q) use ($record) {
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

    protected function requestClass(): string
    {
        return StoreMaintenanceReportRequest::class;
    }
}
