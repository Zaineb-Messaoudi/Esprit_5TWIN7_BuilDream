<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReviewDispute;
use App\Services\DeliveryService;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use Illuminate\View\View;

class OperationsController extends Controller
{
    public function __construct(
        private readonly ReviewService $reviews,
        private readonly DeliveryService $deliveries,
    ) {}

    public function index(Request $request): View
    {
        $queueSizes = collect(['ai:high', 'ai:default', 'ai:low', 'default'])
            ->mapWithKeys(fn (string $queue): array => [$queue => Queue::size($queue)])
            ->all();

        return view('pages.admin.operations', [
            'title' => __('Operations control centre'),
            'disputes' => ReviewDispute::with(['review.rental.equipment', 'initiator', 'resolvedBy'])->latest()->paginate(10, ['*'], 'disputes_page'),
            'deliveries' => $this->deliveries->getDeliveriesNeedingAttention(),
            'queueSizes' => $queueSizes,
            'queueSize' => array_sum($queueSizes),
        ]);
    }

    public function resolveDispute(Request $request, ReviewDispute $dispute): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:resolved,dismissed'],
            'resolution' => ['nullable', 'string', 'max:2000'],
        ]);
        $this->reviews->resolveDispute($dispute, $request->user(), $data['status'], $data['resolution'] ?? null);

        return back()->with('status', __('Review dispute updated.'));
    }

    public function autoCancelDeliveries(): RedirectResponse
    {
        $count = $this->deliveries->autoCancelStale();

        return back()->with('status', __(':count stale deliveries cancelled.', ['count' => $count]));
    }
}
