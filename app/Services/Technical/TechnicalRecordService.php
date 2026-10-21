<?php

namespace App\Services\Technical;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TechnicalRecordService
{
    /** @param class-string<Model> $modelClass */
    public function paginate(string $modelClass, Request $request, bool $admin, array $relations = []): LengthAwarePaginator
    {
        $query = $this->scope($modelClass::query(), $request, $admin);

        return $query->with($relations)->latest('id')->paginate(15);
    }

    /** @param class-string<Model> $modelClass */
    public function find(string $modelClass, int $id, Request $request, bool $admin, array $relations = []): Model
    {
        return $this->scope($modelClass::query(), $request, $admin)->with($relations)->findOrFail($id);
    }

    /** @param class-string<Model> $modelClass */
    public function create(string $modelClass, array $data): Model
    {
        return $modelClass::query()->create(Arr::only($data, (new $modelClass())->getFillable()));
    }

    public function update(Model $record, array $data): Model
    {
        $record->update(Arr::only($data, $record->getFillable()));

        return $record;
    }

    public function delete(Model $record): void
    {
        $record->delete();
    }

    private function scope(Builder $query, Request $request, bool $admin): Builder
    {
        if ($admin) {
            return $query;
        }

        $relation = match ($query->getModel()::class) {
            \App\Models\Maintenance::class, \App\Models\Inspection::class => 'equipment',
            \App\Models\MaintenanceReport::class => 'maintenance.equipment',
            default => throw new \LogicException('Unsupported technical record model.'),
        };

        return $query->whereHas($relation, fn (Builder $equipment) => $equipment->where('owner_id', $request->user()->getKey()));
    }
}
