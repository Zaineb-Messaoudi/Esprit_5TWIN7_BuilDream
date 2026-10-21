<?php

namespace App\Http\Controllers\Technical;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\Maintenance;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TechnicalPdfController extends Controller
{
    public function inspection(Request $request, Inspection $inspection): Response
    {
        $this->authorize('view', $inspection);
        $inspection->load(['equipment', 'checklist', 'damages']);

        return Pdf::loadView('pdf.inspection', [
            'inspection' => $inspection,
            'inspector' => $request->user(),
        ])->stream("inspection-{$inspection->getKey()}.pdf");
    }

    public function maintenance(Request $request, Maintenance $maintenance): Response
    {
        $this->authorize('view', $maintenance);
        $maintenance->load(['equipment', 'report', 'photos']);

        return Pdf::loadView('pdf.maintenance', [
            'maintenance' => $maintenance,
            'inspector' => $request->user(),
        ])->stream("maintenance-{$maintenance->getKey()}.pdf");
    }
}
