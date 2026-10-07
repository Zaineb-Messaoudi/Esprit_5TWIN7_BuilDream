<?php

namespace App\Http\Controllers\Front;

use App\Enums\ExtensionStatus;
use App\Enums\RentalStatus;
use App\Http\Controllers\Controller;
use App\Models\Rental;
use App\Models\RentalExtension;
use App\Events\ExtensionApproved;
use App\Events\ExtensionRejected;
use App\Events\ExtensionRequested;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RentalExtensionController extends Controller
{
    /**
     * Front-office: Show extension request form for the renter (buyer).
     */
    public function create(Request $request, Rental $rental): View
    {
        abort_unless($rental->user_id === $request->user()->id, 403);
        abort_unless(in_array($rental->status->value, ['active']), 409, 'Only active rentals can be extended.');

        // Check if there's already a pending extension
        $pendingExtension = $rental->extensions()->where('status', ExtensionStatus::PENDING->value)->first();
        if ($pendingExtension) {
            return redirect()->route('front.my-rental-detail', $rental->reference)
                ->with('status', 'extension-already-pending');
        }

        $owner = $request->user()->isOwner();

        return view('pages.front.rental-extension.create', [
            'rental' => $rental->load('equipment'),
            'title'  => __('Request Extension'),
            'owner'  => $owner,
        ]);
    }

    /**
     * Front-office: Store extension request from renter.
     */
    public function store(Request $request, Rental $rental): RedirectResponse
    {
        abort_unless($rental->user_id === $request->user()->id, 403);
        abort_unless(in_array($rental->status->value, ['active']), 409);

        $data = $request->validate([
            'new_end_date' => ['required', 'date', 'after:' . $rental->end_date->toDateString()],
            'reason'       => ['nullable', 'string', 'max:1000'],
        ]);

        $newEnd = Carbon::parse($data['new_end_date']);
        $additionalAmount = $this->suggestedAmount($rental, $rental->end_date, $newEnd);

        $extension = RentalExtension::create([
            'rental_id'         => $rental->id,
            'requested_date'    => now()->toDateString(),
            'old_end_date'      => $rental->end_date,
            'new_end_date'      => $newEnd,
            'additional_amount' => $additionalAmount,
            'reason'            => $data['reason'] ?? null,
            'status'            => ExtensionStatus::PENDING,
        ]);

        // Fire real-time notification for extension requested
        $extension->load('rental.equipment', 'rental.user');
        ExtensionRequested::dispatch($extension, $rental->equipment->owner);

        return redirect()->route(
            $request->user()->isBuyer() ? 'front.buyer-rental-detail' : 'front.my-rental-detail',
            $rental->reference
        )->with('status', 'extension-requested');
    }

    /**
     * Front-office: Approve extension request (owner only).
     */
    public function approve(Request $request, RentalExtension $rentalExtension): RedirectResponse
    {
        abort_unless($rentalExtension->rental->equipment->owner_id === $request->user()->id, 403);
        abort_unless($rentalExtension->status === ExtensionStatus::PENDING, 409);

        $rental = $rentalExtension->rental;
        $rental->update([
            'end_date'     => $rentalExtension->new_end_date,
            'total_amount' => round((float) $rental->total_amount + (float) $rentalExtension->additional_amount, 2),
        ]);

        $rentalExtension->update(['status' => ExtensionStatus::APPROVED]);

        // Fire real-time notification for extension approved
        $rentalExtension->load('rental.equipment', 'rental.user');
        ExtensionApproved::dispatch($rentalExtension, $rentalExtension->rental->user);

        return redirect()->route('front.my-rental-detail', $rentalExtension->rental->reference)
            ->with('status', 'extension-approved');
    }

    /**
     * Front-office: Reject extension request (owner only).
     */
    public function reject(Request $request, RentalExtension $rentalExtension): RedirectResponse
    {
        abort_unless($rentalExtension->rental->equipment->owner_id === $request->user()->id, 403);
        abort_unless($rentalExtension->status === ExtensionStatus::PENDING, 409);

        $rentalExtension->update(['status' => ExtensionStatus::REJECTED]);

        // Fire real-time notification for extension rejected
        $rentalExtension->load('rental.equipment', 'rental.user');
        ExtensionRejected::dispatch($rentalExtension, $rentalExtension->rental->user);

        return redirect()->route('front.my-rental-detail', $rentalExtension->rental->reference)
            ->with('status', 'extension-rejected');
    }

    /**
     * Price of the extra days, computed from the rental daily rate:
     *   daily rate = rental total / number of rental days
     *   amount     = daily rate x number of extra days
     */
    private function suggestedAmount(Rental $rental, Carbon $oldEnd, Carbon $newEnd): float
    {
        $rentalDays = max(1, (int) $rental->start_date->diffInDays($rental->end_date));
        $extraDays  = max(1, (int) $oldEnd->diffInDays($newEnd));
        $dailyRate  = (float) $rental->total_amount / $rentalDays;

        return round($dailyRate * $extraDays, 2);
    }
}