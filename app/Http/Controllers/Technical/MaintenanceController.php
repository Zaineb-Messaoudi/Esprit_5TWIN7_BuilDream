<?php

namespace App\Http\Controllers\Technical;

use App\Models\Maintenance;
use App\Http\Requests\Technical\StoreMaintenanceRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Services\Maintenance\MaintenanceCostAnomalyService;

class MaintenanceController extends TechnicalCrudController
{
    protected function modelClass(): string
    {
        return Maintenance::class;
    }

    protected function resource(): string
    {
        return 'maintenances';
    }

    protected function title(): string
    {
        return 'Maintenances';
    }

    protected function eagerLoads(): array
    {
        return ['equipment', 'report', 'photos'];
    }

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

    protected function requestClass(): string { return StoreMaintenanceRequest::class; }

    protected function createRecord(array $data): Model
    {
        $maintenance = parent::createRecord($data);
        app(MaintenanceCostAnomalyService::class)->evaluateAndNotify($maintenance);

        return $maintenance;
    }
}
