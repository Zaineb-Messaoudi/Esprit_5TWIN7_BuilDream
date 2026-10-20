<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EnergyProfileStoreRequest;
use App\Http\Requests\Admin\EnergyProfileUpdateRequest;
use App\Models\Equipment;
use App\Models\EnergyProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** Back Office CRUD for the one-to-one technical profile of equipment. */
class EnergyProfileController extends Controller
{
    public function index(Equipment $equipment): View
    {
        $equipment->load('energyProfile');

        return view('pages.admin.energy-profiles.index', [
            'equipment' => $equipment,
            'profile' => $equipment->energyProfile,
            'title' => __('Energy profile').' · '.$equipment->name,
        ]);
    }

    public function create(Equipment $equipment): View|RedirectResponse
    {
        if ($equipment->energyProfile) {
            return redirect()->route('admin.equipment.energy-profile.edit', $equipment);
        }

        return view('pages.admin.energy-profiles.create', [
            'equipment' => $equipment,
            'title' => __('Create energy profile'),
        ]);
    }

    public function store(EnergyProfileStoreRequest $request, Equipment $equipment): RedirectResponse
    {
        $equipment->energyProfile()->updateOrCreate([], $request->validated());

        return redirect()->route('admin.equipment.energy-profile.index', $equipment)->with('status', 'energy-profile-created');
    }

    public function show(Equipment $equipment): View|RedirectResponse
    {
        return $this->index($equipment);
    }

    public function edit(Equipment $equipment): View|RedirectResponse
    {
        $profile = $equipment->energyProfile;
        if (! $profile) {
            return redirect()->route('admin.equipment.energy-profile.create', $equipment);
        }

        return view('pages.admin.energy-profiles.edit', [
            'equipment' => $equipment,
            'profile' => $profile,
            'title' => __('Edit energy profile'),
        ]);
    }

    public function update(EnergyProfileUpdateRequest $request, Equipment $equipment): RedirectResponse
    {
        $equipment->energyProfile()->updateOrCreate([], $request->validated());

        return redirect()->route('admin.equipment.energy-profile.index', $equipment)->with('status', 'energy-profile-updated');
    }

    public function destroy(Equipment $equipment): RedirectResponse
    {
        EnergyProfile::where('equipment_id', $equipment->id)->delete();

        return redirect()->route('admin.equipment.energy-profile.index', $equipment)->with('status', 'energy-profile-deleted');
    }
}
