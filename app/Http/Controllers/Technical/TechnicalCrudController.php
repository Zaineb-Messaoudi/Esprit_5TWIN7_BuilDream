<?php

namespace App\Http\Controllers\Technical;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\Maintenance;
use App\Events\MaintenanceCreated;
use App\Events\MaintenanceCompleted;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

abstract class TechnicalCrudController extends Controller
{
    abstract protected function modelClass(): string;

    abstract protected function resource(): string;

    abstract protected function title(): string;

    abstract protected function fields(): array;

    abstract protected function rules(Request $request, ?Model $record = null): array;

    abstract protected function scope(Builder $query, Request $request): Builder;

    protected function eagerLoads(): array
    {
        return [];
    }

    private function prefix(Request $request): string
    {
        return $request->routeIs('admin.technical.*') ? 'admin.technical.' : 'technical.';
    }

    protected function requireActor(Request $request): void
    {
        abort_unless($request->user()?->isAdmin() || $request->user()?->isOwner(), 403);
    }

    protected function isAdmin(Request $request): bool
    {
        return $request->user()->isAdmin();
    }

    protected function equipmentOptions(Request $request): array
    {
        return Equipment::query()
            ->when(! $this->isAdmin($request), fn (Builder $query) => $query->where('owner_id', $request->user()->id))
            ->orderBy('id')->get(['id', 'name'])
            ->mapWithKeys(fn ($equipment) => [$equipment->id => $equipment->name.' (#'.$equipment->id.')'])->all();
    }

    protected function formOptions(Request $request, ?Model $record = null): array
    {
        return [];
    }

    private function data(Request $request, ?Model $record = null): array
    {
        return [
            'title' => __($this->title()),
            'resource' => $this->resource(),
            'prefix' => $this->prefix($request),
            'layout' => $request->routeIs('admin.technical.*') ? 'layouts.app' : 'layouts.front',
            'fields' => $this->fields(),
            'options' => $this->formOptions($request, $record),
            'record' => $record,
        ];
    }

    private function find(Request $request, int $id): Model
    {
        $this->requireActor($request);
        $class = $this->modelClass();

        return $this->scope($class::query(), $request)->with($this->eagerLoads())->findOrFail($id);
    }

    public function index(Request $request): View
    {
        $this->requireActor($request);
        $class = $this->modelClass();
        $records = $this->scope($class::query(), $request)
            ->with($this->eagerLoads())->latest('id')->paginate(15);

        return view('pages.technical.index', $this->data($request) + compact('records'));
    }

    public function create(Request $request): View
    {
        $this->requireActor($request);

        return view('pages.technical.create', $this->data($request));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requireActor($request);
        $data = $request->validate($this->rules($request));
        $class = $this->modelClass();
        $record = $class::create($data);

        // Dispatch events for Maintenance model
        if ($class === Maintenance::class) {
            $record->load('equipment.owner');
            if ($record->equipment?->owner) {
                MaintenanceCreated::dispatch($record, $record->equipment->owner);
            }
        }

        return redirect()->route($this->prefix($request).$this->resource().'.show', $record)
            ->with('status', __('Record created.'));
    }

    public function show(Request $request, int $id): View
    {
        $record = $this->find($request, $id);

        return view('pages.technical.show', $this->data($request, $record));
    }

    public function edit(Request $request, int $id): View
    {
        $record = $this->find($request, $id);

        return view('pages.technical.edit', $this->data($request, $record));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $record = $this->find($request, $id);
        $wasCompleted = $record instanceof Maintenance && $record->status === 'completed';
        $record->update($request->validate($this->rules($request, $record)));
        
        // Dispatch MaintenanceCompleted event when maintenance is completed
        if ($record instanceof Maintenance && ! $wasCompleted && $record->status === 'completed') {
            $record->load('equipment.owner');
            if ($record->equipment?->owner) {
                MaintenanceCompleted::dispatch($record, $record->equipment->owner);
            }
        }

        return redirect()->route($this->prefix($request).$this->resource().'.show', $record)
            ->with('status', __('Record updated.'));
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $record = $this->find($request, $id);
        $record->delete();

        return redirect()->route($this->prefix($request).$this->resource().'.index')
            ->with('status', __('Record deleted.'));
    }
}
