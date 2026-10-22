<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Rental;
use App\Services\ChatService;
use App\Services\DeliveryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OperationsController extends Controller
{
    public function __construct(
        private readonly ChatService $chat,
        private readonly DeliveryService $deliveries,
    ) {}

    public function chat(Request $request, Rental $rental): View
    {
        $this->authorizeParticipant($request, $rental);
        $this->chat->getOrCreateParticipants($rental);

        return view('pages.front.chat', [
            'title' => __('Rental chat'),
            'rental' => $rental->load('equipment.owner', 'user'),
            'messages' => $this->chat->getMessages($rental, $request->user(), 50),
        ]);
    }

    public function sendMessage(Request $request, Rental $rental): RedirectResponse
    {
        $this->authorizeParticipant($request, $rental);
        $data = $request->validate(['message' => ['required', 'string', 'max:5000']]);
        $this->chat->getOrCreateParticipants($rental);
        $this->chat->sendMessage($rental, $request->user(), $data['message']);

        return back()->with('status', __('Message sent.'));
    }

    public function deliveries(Request $request): View
    {
        $deliveries = $this->deliveries->getUserDeliveries($request->user(), $request->string('status')->toString() ?: null);

        return view('pages.front.deliveries', [
            'title' => __('Deliveries'),
            'deliveries' => $deliveries,
            'stats' => $this->deliveries->getUserDeliveryStats($request->user()),
        ]);
    }

    public function updateDelivery(Request $request, Delivery $delivery): RedirectResponse
    {
        abort_unless(in_array($request->user()->id, [$delivery->owner_id, $delivery->renter_id], true), 403);
        $data = $request->validate([
            'status' => ['required', 'in:in_transit,arrived,completed,cancelled,issue'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $this->deliveries->updateStatusWithProof($delivery, $data['status'], $request->user(), $data);

        return back()->with('status', __('Delivery status updated.'));
    }

    private function authorizeParticipant(Request $request, Rental $rental): void
    {
        abort_unless(
            (int) $rental->user_id === (int) $request->user()->id
                || (int) $rental->equipment?->owner_id === (int) $request->user()->id,
            403
        );
    }
}
