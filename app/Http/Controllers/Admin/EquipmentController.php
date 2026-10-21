<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EquipmentStoreRequest;
use App\Http\Requests\Admin\EquipmentUpdateRequest;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\User;
use App\Services\EquipmentCatalogService;
use App\Services\Maintenance\EquipmentFailureRiskService;
use App\Services\Maintenance\EquipmentHealthScoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Back Office CRUD for all equipment listings and their energy profiles. */
class EquipmentController extends Controller
{
    public function __construct(
        private readonly EquipmentCatalogService $catalog,
        private readonly EquipmentHealthScoreService $healthScoreService,
        private readonly EquipmentFailureRiskService $failureRiskService,
    ) {}

    public function index(Request $request): View
    {
        $equipment = Equipment::with(['category', 'owner', 'energyProfile'])
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('approval_status'), fn ($query) => $query->where('approval_status', $request->string('approval_status')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.admin.equipment.index', compact('equipment') + ['title' => __('Equipment')]);
    }

    public function create(): View
    {
        return view('pages.admin.equipment.create', [
            'categories' => Category::where('status', 'active')->orderBy('name')->get(),
            'owners' => User::where('role', 'owner')->orderBy('name')->get(),
            'title' => __('Create equipment'),
        ]);
    }

    public function store(EquipmentStoreRequest $request): RedirectResponse
    {
        $this->catalog->createEquipment($request->validated());

        return redirect()->route('admin.equipment.index')->with('status', 'equipment-created');
    }

    public function show(Equipment $equipment): View
    {
        $equipment->load(['category', 'owner', 'energyProfile', 'maintenances.report']);

        return view('pages.admin.equipment.show', [
            'equipment' => $equipment,
            'healthScore' => $this->healthScoreService->score($equipment),
            'failureRisk' => $this->failureRiskService->forEquipment($equipment),
            'title' => $equipment->name,
        ]);
    }

    public function edit(Equipment $equipment): View
    {
        $equipment->load('energyProfile');

        return view('pages.admin.equipment.edit', [
            'equipment' => $equipment,
            'categories' => Category::where('status', 'active')->orderBy('name')->get(),
            'owners' => User::where('role', 'owner')->orderBy('name')->get(),
            'title' => __('Edit equipment'),
        ]);
    }

    public function update(EquipmentUpdateRequest $request, Equipment $equipment): RedirectResponse
    {
        $this->catalog->updateEquipment($equipment, $request->validated());

        return redirect()->route('admin.equipment.index')->with('status', 'equipment-updated');
    }

    public function destroy(Equipment $equipment): RedirectResponse
    {
        $this->catalog->deleteEquipment($equipment);

        return redirect()->route('admin.equipment.index')->with('status', 'equipment-deleted');
    }

    public function exportCsv(): StreamedResponse
    {
        $equipment = Equipment::with(['category', 'owner', 'energyProfile'])->orderBy('id')->get();
        $headers = ['id', 'name', 'category', 'owner_email', 'brand', 'model', 'description', 'price_per_day', 'condition', 'location', 'latitude', 'longitude', 'status', 'approval_status', 'power_watts', 'voltage', 'capacity_wh', 'efficiency', 'technology', 'max_output', 'operating_duration'];

        return response()->streamDownload(function () use ($equipment, $headers): void {
            $output = fopen('php://output', 'wb');
            fputcsv($output, $headers);
            foreach ($equipment as $item) {
                $profile = $item->energyProfile;
                fputcsv($output, [
                    $item->id, $item->name, $item->category?->name, $item->owner?->email, $item->brand, $item->model,
                    $item->description, $item->price_per_day, $item->condition, $item->location, $item->latitude, $item->longitude,
                    $item->status, $item->approval_status, $profile?->power_watts, $profile?->voltage, $profile?->capacity_wh,
                    $profile?->efficiency, $profile?->technology, $profile?->max_output, $profile?->operating_duration,
                ]);
            }
            fclose($output);
        }, 'solarshare-equipment-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function importCsv(Request $request): RedirectResponse
    {
        $request->validate(['csv' => ['required', 'file', 'mimes:csv,txt', 'max:10240']]);
        $handle = fopen($request->file('csv')->getRealPath(), 'rb');
        $headers = array_map(fn ($header) => Str::snake(trim((string) $header)), fgetcsv($handle) ?: []);
        $errors = [];
        $line = 1;
        $requiredHeaders = ['name', 'category', 'owner_email', 'price_per_day', 'condition', 'location'];
        foreach (array_diff($requiredHeaders, $headers) as $requiredHeader) {
            $errors['csv header'][] = __('Missing required column: :column', ['column' => $requiredHeader]);
        }

        while (($values = fgetcsv($handle)) !== false) {
            $line++;
            if (count(array_filter($values, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }
            if (count($values) !== count($headers)) {
                $errors["csv row {$line}"][] = __('The number of values does not match the CSV header.');

                continue;
            }
            $row = array_combine($headers, array_pad($values, count($headers), null));
            $owner = User::where('email', trim((string) ($row['owner_email'] ?? '')))->where('role', 'owner')->first();
            $category = Category::where('name', trim((string) ($row['category'] ?? '')))->first();
            $payload = [
                'category_id' => $category?->id, 'owner_id' => $owner?->id,
                'name' => trim((string) ($row['name'] ?? '')), 'description' => $row['description'] ?? null,
                'brand' => $row['brand'] ?? null, 'model' => $row['model'] ?? null,
                'price_per_day' => $row['price_per_day'] ?? null, 'condition' => strtolower((string) ($row['condition'] ?? '')),
                'location' => trim((string) ($row['location'] ?? '')), 'latitude' => $row['latitude'] ?? null,
                'longitude' => $row['longitude'] ?? null, 'status' => $row['status'] ?? 'available',
                'approval_status' => $row['approval_status'] ?? 'published',
                'energy' => [
                    'power_watts' => $row['power_watts'] ?? null, 'voltage' => $row['voltage'] ?? null,
                    'capacity_wh' => $row['capacity_wh'] ?? null, 'efficiency' => $row['efficiency'] ?? null,
                    'technology' => $row['technology'] ?? null, 'max_output' => $row['max_output'] ?? null,
                    'operating_duration' => $row['operating_duration'] ?? null,
                ],
            ];
            $validator = Validator::make($payload, EquipmentStoreRequest::equipmentRules());
            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $message) {
                    $errors["csv row {$line}"][] = $message;
                }

                continue;
            }
            $this->catalog->createEquipment($validator->validated());
        }
        fclose($handle);

        if ($errors) {
            return back()->withErrors($errors)->with('status', __('Some CSV rows were skipped.'));
        }

        return back()->with('status', __('Equipment CSV imported successfully.'));
    }

    public function approve(Equipment $equipment): RedirectResponse
    {
        $equipment->update([
            'approval_status' => 'published',
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.equipment.index')->with('status', 'equipment-approved');
    }

    public function reject(Request $request, Equipment $equipment): RedirectResponse
    {
        $request->validate(['rejection_reason' => ['required', 'string', 'max:500']]);

        $equipment->update([
            'approval_status' => 'rejected',
            'reviewed_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()->route('admin.equipment.index')->with('status', 'equipment-rejected');
    }
}
