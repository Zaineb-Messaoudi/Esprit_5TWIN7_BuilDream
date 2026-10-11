<?php

namespace App\Http\Controllers;

use App\Http\Requests\Owner\EquipmentStoreRequest;
use App\Http\Requests\Owner\EquipmentUpdateRequest;
use App\Models\Category;
use App\Models\Equipment;
use App\Services\EquipmentCatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Owner-facing CRUD for the authenticated user's equipment listings. */
class OwnerEquipmentController extends Controller
{
    public function __construct(private readonly EquipmentCatalogService $catalog) {}

    public function index(Request $request): View
    {
        // Ownership is enforced by querying through the authenticated user's relation.
        return view('pages.front.owner-equipment.index', [
            'equipment' => $request->user()->equipment()->with(['category', 'energyProfile'])->latest()->paginate(12),
            'title' => __('My equipment'),
        ]);
    }

    public function create(): View
    {
        return view('pages.front.owner-equipment.create', [
            'categories' => Category::where('status', 'active')->orderBy('name')->get(),
            'title' => __('Publish equipment'),
        ]);
    }

    public function store(EquipmentStoreRequest $request): RedirectResponse
    {
        $attributes = $request->validated();
        $attributes['owner_id'] = $request->user()->id;
        $attributes['approval_status'] = 'pending_review';
        $this->catalog->createEquipment($attributes);

        return redirect()->route('front.my-equipment')->with('status', __('Equipment submitted for administrator review.'));
    }

    public function show(Request $request, Equipment $equipment): View
    {
        $this->ensureOwner($request, $equipment);

        return view('pages.front.owner-equipment.show', [
            'equipment' => $equipment->load(['category', 'energyProfile', 'maintenances.report']),
            'title' => $equipment->name,
        ]);
    }

    public function edit(Request $request, Equipment $equipment): View
    {
        $this->ensureOwner($request, $equipment);

        return view('pages.front.owner-equipment.edit', [
            'equipment' => $equipment->load('energyProfile'),
            'categories' => Category::where('status', 'active')->orderBy('name')->get(),
            'title' => __('Edit equipment'),
        ]);
    }

    public function update(EquipmentUpdateRequest $request, Equipment $equipment): RedirectResponse
    {
        $this->ensureOwner($request, $equipment);
        $attributes = $request->validated();
        $attributes['owner_id'] = $request->user()->id;
        $this->catalog->updateEquipment($equipment, $attributes);

        return redirect()->route('front.my-equipment')->with('status', __('Equipment updated successfully.'));
    }

    public function destroy(Request $request, Equipment $equipment): RedirectResponse
    {
        $this->ensureOwner($request, $equipment);
        $this->catalog->deleteEquipment($equipment);

        return redirect()->route('front.my-equipment')->with('status', __('Equipment deleted successfully.'));
    }

    private function ensureOwner(Request $request, Equipment $equipment): void
    {
        // Prevent an owner from reading or mutating another owner's listing.
        abort_unless((int) $equipment->owner_id === (int) $request->user()->id, 403);
    }
}
