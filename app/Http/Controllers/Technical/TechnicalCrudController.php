<?php

namespace App\Http\Controllers\Technical;

use App\Events\MaintenanceCompleted;
use App\Events\MaintenanceCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Technical\TechnicalRecordRequest;
use App\Models\Equipment;
use App\Models\Maintenance;
use App\Services\Technical\TechnicalRecordService;
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

    abstract protected function requestClass(): string;

    protected function eagerLoads(): array
    {
        return [];
    }

    private function prefix(Request $request): string
    {
        return $request->routeIs('admin.technical.*') ? 'admin.technical.' : 'technical.';
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
        return app(TechnicalRecordService::class)->find($this->modelClass(), $id, $request, $this->isAdmin($request), $this->eagerLoads());
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', $this->modelClass());
        $records = app(TechnicalRecordService::class)->paginate($this->modelClass(), $request, $this->isAdmin($request), $this->eagerLoads());

        return view('pages.technical.index', $this->data($request) + compact('records'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', $this->modelClass());

        return view('pages.technical.create', $this->data($request));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', $this->modelClass());
        $data = $this->validatedData($request);
        $record = $this->createRecord($data);

        // Dispatch events for Maintenance model
        if ($this->modelClass() === Maintenance::class) {
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
        $this->authorize('view', $record);

        return view('pages.technical.show', $this->data($request, $record));
    }

    public function edit(Request $request, int $id): View
    {
        $record = $this->find($request, $id);
        $this->authorize('view', $record);

        return view('pages.technical.edit', $this->data($request, $record));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $record = $this->find($request, $id);
        $wasCompleted = $record instanceof Maintenance && $record->status === 'completed';
        $this->authorize('update', $record);
        $record = $this->updateRecord($record, $this->validatedData($request, $record));

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
        $this->authorize('delete', $record);
        app(TechnicalRecordService::class)->delete($record);

        return redirect()->route($this->prefix($request).$this->resource().'.index')
            ->with('status', __('Record deleted.'));
    }

    private function validatedData(Request $request, ?Model $record = null): array
    {
        /** @var TechnicalRecordRequest $formRequest */
        $formRequest = app($this->requestClass());
        $formRequest->initialize(
            $request->query->all(),
            $request->request->all(),
            $request->attributes->all(),
            $request->cookies->all(),
            $request->files->all(),
            $request->server->all(),
            $request->getContent(),
        );
        $formRequest->setActorAndRoute($request, $record);

        return $formRequest->validateRecord();
    }

    protected function createRecord(array $data): Model
    {
        return app(TechnicalRecordService::class)->create($this->modelClass(), $data);
    }

    protected function updateRecord(Model $record, array $data): Model
    {
        return app(TechnicalRecordService::class)->update($record, $data);
    }
}
