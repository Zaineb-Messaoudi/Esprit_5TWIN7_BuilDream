<?php

namespace App\Services\Maintenance;

use App\Models\Damage;
use App\Models\Inspection;
use App\Models\InspectionChecklist;
use App\Models\InspectionChecklistItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InspectionChecklistService
{
    public function create(array $data): Inspection
    {
        return DB::transaction(function () use ($data): Inspection {
            $data = $this->validateAndNormalizeResults($data);
            $inspection = Inspection::query()->create(Arr::only($data, (new Inspection())->getFillable()));
            $this->synchronizeCriticalDamages($inspection);

            return $inspection;
        });
    }

    public function update(Model $record, array $data): Inspection
    {
        if (! $record instanceof Inspection) {
            throw new \InvalidArgumentException('InspectionChecklistService only updates inspections.');
        }

        return DB::transaction(function () use ($record, $data): Inspection {
            $data = $this->validateAndNormalizeResults($data);
            $record->update(Arr::only($data, $record->getFillable()));
            $record->damages()->whereNotNull('inspection_checklist_item_id')->delete();
            $this->synchronizeCriticalDamages($record);

            return $record->refresh();
        });
    }

    private function validateAndNormalizeResults(array $data): array
    {
        if (empty($data['inspection_checklist_id'])) {
            $data['checklist_results'] = null;

            return $data;
        }

        $checklist = InspectionChecklist::query()->with('items')->find($data['inspection_checklist_id']);
        if (! $checklist) {
            throw ValidationException::withMessages(['inspection_checklist_id' => __('The selected checklist is invalid.')]);
        }

        $submitted = collect($data['checklist_results'] ?? [])->keyBy(fn (array $result) => (int) $result['item_id']);
        $expectedIds = $checklist->items->pluck('id')->map(fn ($id) => (int) $id);
        if ($submitted->keys()->sort()->values()->all() !== $expectedIds->sort()->values()->all()) {
            throw ValidationException::withMessages(['checklist_results' => __('Every checklist item must have a result.')]);
        }

        $data['checklist_results'] = $checklist->items->map(fn (InspectionChecklistItem $item): array => [
            'item_id' => $item->getKey(),
            'label' => $item->label,
            'is_critical' => $item->is_critical,
            'passed' => (bool) $submitted->get($item->getKey())['passed'],
        ])->values()->all();

        return $data;
    }

    private function synchronizeCriticalDamages(Inspection $inspection): void
    {
        $failedCriticalResults = collect($inspection->checklist_results ?? [])->filter(
            fn (array $result): bool => ($result['is_critical'] ?? false) && ! ($result['passed'] ?? false),
        );

        foreach ($failedCriticalResults as $result) {
            Damage::query()->create([
                'inspection_id' => $inspection->getKey(),
                'equipment_id' => $inspection->equipment_id,
                'inspection_checklist_item_id' => $result['item_id'],
                'severity' => 'severe',
                'description' => $result['label'],
                'status' => 'reported',
            ]);
        }

        if ($failedCriticalResults->isNotEmpty() && ! $inspection->damage_detected) {
            $inspection->forceFill(['damage_detected' => true])->save();
        }
    }
}
