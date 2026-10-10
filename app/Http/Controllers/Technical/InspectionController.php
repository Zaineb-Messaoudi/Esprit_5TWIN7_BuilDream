<?php

namespace App\Http\Controllers\Technical;

use App\Models\Inspection;
use App\Models\Rental;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

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
        return $this->rentalsReady() ? ['equipment', 'rental'] : ['equipment'];
    }

    private function rentalsReady(): bool
    {
        return class_exists(Rental::class) && Schema::hasTable('rentals');
    }

    protected function scope(Builder $query, Request $request): Builder
    {
        return $this->isAdmin($request) ? $query : $query->whereHas('equipment', fn (Builder $q) => $q->where('owner_id', $request->user()->id));
    }

    protected function fields(): array
    {
        return [
            'equipment_id' => ['label' => 'Equipment', 'type' => 'select', 'required' => true],
            'rental_id' => ['label' => 'Rental (optional)', 'type' => 'select'],
            'inspection_date' => ['label' => 'Inspection date', 'type' => 'date', 'required' => true],
            'condition_before' => ['label' => 'Condition before', 'type' => 'text', 'required' => true],
            'condition_after' => ['label' => 'Condition after', 'type' => 'text', 'required' => true],
            'damage_detected' => ['label' => 'Damage detected', 'type' => 'checkbox'],
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
            'rental_id' => $rentals->mapWithKeys(fn ($rental) => [$rental->id => '#'.$rental->id.' (equipment #'.$rental->equipment_id.')'])->all(),
        ];
    }

    protected function rules(Request $request, ?Model $record = null): array
    {
        return [
            'equipment_id' => ['required', 'integer', Rule::exists('equipment', 'id')->when(! $this->isAdmin($request), fn ($rule) => $rule->where('owner_id', $request->user()->id))],
            'rental_id' => $this->rentalsReady()
                ? ['nullable', 'integer', Rule::exists('rentals', 'id')->where('equipment_id', $request->input('equipment_id'))]
                : ['nullable', Rule::prohibitedIf(true)],
            'inspection_date' => ['required', 'date'],
            'condition_before' => ['required', 'string', 'max:255'],
            'condition_after' => ['required', 'string', 'max:255'],
            'damage_detected' => ['required', 'boolean'],
            'comments' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
