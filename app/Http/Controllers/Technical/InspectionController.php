<?php

namespace App\Http\Controllers\Technical;

use App\Http\Requests\Technical\StoreInspectionRequest;
use App\Models\Equipment;
use App\Models\Inspection;
use App\Models\InspectionChecklist;
use App\Models\Rental;
use App\Services\Maintenance\InspectionChecklistService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class InspectionController extends TechnicalCrudController
{
    protected function modelClass(): string
    {
        return Inspection::class;
    }

    protected function resource(): string
    {
        return 'inspections';
    }

    protected function title(): string
    {
        return 'Inspections';
    }

    protected function eagerLoads(): array
    {
        return $this->rentalsReady() ? ['equipment', 'rental', 'checklist', 'damages'] : ['equipment', 'checklist', 'damages'];
    }

    private function rentalsReady(): bool
    {
        return class_exists(Rental::class) && Schema::hasTable('rentals');
    }

    protected function fields(): array
    {
        return [
            'equipment_id' => ['label' => 'Equipment', 'type' => 'select', 'required' => true],
            'rental_id' => ['label' => 'Rental (optional)', 'type' => 'select'],
            'inspection_checklist_id' => ['label' => 'Inspection checklist', 'type' => 'select'],
            'inspection_date' => ['label' => 'Inspection date', 'type' => 'date', 'required' => true],
            'condition_before' => ['label' => 'Condition before', 'type' => 'text', 'required' => true],
            'condition_after' => ['label' => 'Condition after', 'type' => 'text', 'required' => true],
            'damage_detected' => ['label' => 'Damage detected', 'type' => 'checkbox'],
            'checklist_results' => ['label' => 'Checklist results', 'type' => 'checklist'],
            'comments' => ['label' => 'Comments', 'type' => 'textarea'],
        ];
    }

    protected function formOptions(Request $request, ?Model $record = null): array
    {
        $rentals = $this->rentalsReady()
            ? Rental::query()->when(! $this->isAdmin($request), fn (Builder $query) => $query->whereHas('equipment', fn (Builder $q) => $q->where('owner_id', $request->user()->id))
            )->orderBy('id')->get(['id', 'equipment_id'])
            : collect();

        return [
            'equipment_id' => $this->equipmentOptions($request),
            'equipment_categories' => Equipment::query()
                ->when(! $this->isAdmin($request), fn (Builder $query) => $query->where('owner_id', $request->user()->id))
                ->pluck('category_id', 'id')->all(),
            'rental_id' => $rentals->mapWithKeys(fn ($rental) => [$rental->id => '#'.$rental->id.' (equipment #'.$rental->equipment_id.')'])->all(),
            'inspection_checklist_id' => InspectionChecklist::query()->where('is_active', true)->orderBy('category_name')->pluck('name', 'id')->all(),
            'checklist_results' => InspectionChecklist::query()->where('is_active', true)->with('items')->get()->mapWithKeys(
                fn (InspectionChecklist $checklist) => [$checklist->id => $checklist->items->map(fn ($item) => [
                    'id' => $item->id,
                    'label' => $item->label,
                    'is_critical' => $item->is_critical,
                ])->all()],
            )->all(),
        ];
    }

    protected function requestClass(): string
    {
        return StoreInspectionRequest::class;
    }

    public function createForEquipment(Request $request, Equipment $equipment): View
    {
        abort_unless($request->user()->isAdmin() || (int) $equipment->owner_id === (int) $request->user()->getKey(), 403);
        $request->query->set('equipment_id', $equipment->getKey());

        return $this->create($request);
    }

    protected function createRecord(array $data): Model
    {
        return app(InspectionChecklistService::class)->create($data);
    }

    protected function updateRecord(Model $record, array $data): Model
    {
        return app(InspectionChecklistService::class)->update($record, $data);
    }
}
