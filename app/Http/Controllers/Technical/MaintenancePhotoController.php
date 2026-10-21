<?php

namespace App\Http\Controllers\Technical;

use App\Http\Controllers\Controller;
use App\Http\Requests\Technical\StoreMaintenancePhotoRequest;
use App\Models\Maintenance;
use App\Models\MaintenancePhoto;
use App\Services\Maintenance\MaintenancePhotoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MaintenancePhotoController extends Controller
{
    public function store(StoreMaintenancePhotoRequest $request, Maintenance $maintenance, MaintenancePhotoService $service): RedirectResponse
    {
        $service->store($maintenance, $request->file('photo'), $request->string('type')->toString());

        return back()->with('status', __('Photo uploaded.'));
    }

    public function destroy(Request $request, Maintenance $maintenance, MaintenancePhoto $photo, MaintenancePhotoService $service): RedirectResponse
    {
        abort_unless($photo->maintenance_id === $maintenance->getKey(), 404);
        $this->authorize('update', $maintenance);
        $service->delete($photo);

        return back()->with('status', __('Photo deleted.'));
    }
}
