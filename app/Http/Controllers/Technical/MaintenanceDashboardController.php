<?php

namespace App\Http\Controllers\Technical;

use App\Http\Controllers\Controller;
use App\Services\Maintenance\MaintenanceDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class MaintenanceDashboardController extends Controller
{
    public function index(): View
    {
        return view('pages.technical.dashboard', ['title' => __('Maintenance dashboard')]);
    }

    public function costs(MaintenanceDashboardService $service): JsonResponse
    {
        return response()->json($service->monthlyCosts());
    }

    public function failures(MaintenanceDashboardService $service): JsonResponse
    {
        return response()->json($service->failuresByCategory());
    }

    public function intervals(MaintenanceDashboardService $service): JsonResponse
    {
        return response()->json($service->averageFailureInterval());
    }
}
