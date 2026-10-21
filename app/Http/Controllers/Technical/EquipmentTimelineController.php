<?php

namespace App\Http\Controllers\Technical;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Services\Maintenance\EquipmentTimelineService;
use App\Services\Maintenance\TotalCostOfOwnershipService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EquipmentTimelineController extends Controller
{
    public function __invoke(Request $request, Equipment $equipment, EquipmentTimelineService $service, TotalCostOfOwnershipService $tcoService): View
    {
        abort_unless($request->user()->isAdmin() || (int) $equipment->owner_id === (int) $request->user()->getKey(), 403);

        return view('pages.technical.equipment-history', [
            'title' => __('Equipment history'),
            'equipment' => $equipment,
            'timeline' => $service->forEquipment((int) $equipment->getKey()),
            'tco' => $tcoService->calculate((int) $equipment->getKey()),
            'layout' => $request->routeIs('admin.*') ? 'layouts.app' : 'layouts.front',
        ]);
    }
}
