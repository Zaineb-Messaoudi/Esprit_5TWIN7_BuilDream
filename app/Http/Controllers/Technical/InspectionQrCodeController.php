<?php

namespace App\Http\Controllers\Technical;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Services\Maintenance\InspectionQrCodeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InspectionQrCodeController extends Controller
{
    public function __invoke(Request $request, Equipment $equipment, InspectionQrCodeService $service): Response
    {
        abort_unless($request->user()->isAdmin() || (int) $equipment->owner_id === (int) $request->user()->getKey(), 403);

        return response($service->svg($equipment), 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => "inline; filename=inspection-equipment-{$equipment->getKey()}.svg",
        ]);
    }
}
