<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EquipmentStoreRequest;
use App\Http\Requests\Admin\EquipmentUpdateRequest;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\User;
use App\Services\EquipmentCatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Back Office CRUD for all equipment listings and their energy profiles. */
class EquipmentController extends Controller
{
    public function __construct(private readonly EquipmentCatalogService $catalog) {}

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

        return view('pages.admin.equipment.show', compact('equipment') + ['title' => $equipment->name]);
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
