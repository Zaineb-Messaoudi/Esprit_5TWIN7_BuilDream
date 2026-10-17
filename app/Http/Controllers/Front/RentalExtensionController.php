<?php

namespace App\Http\Controllers\Front;

use App\Enums\ExtensionStatus;
use App\Events\ExtensionApproved;
use App\Events\ExtensionRejected;
use App\Events\ExtensionRequested;
use App\Http\Controllers\Controller;
use App\Models\Rental;
use App\Models\RentalExtension;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RentalExtensionController extends Controller
{
    /**
     * Front-office: Show extension request form for the renter (buyer).
     *
     * FIX 1: the return type was "View" but the method can also return a redirect
     * (when a request is already pending). PHP would throw a TypeError in that case,
     * so the type now accepts both.
     */
    public function create(Request $request, Rental $rental): View|RedirectResponse
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
            'title' => __('Request Extension'),
            'owner' => $owner,
        ]);
    }

    /**
     * Front-office: Store extension request from renter.
     */
    public function store(Request $request, Rental $rental): RedirectResponse
    {
        abort_unless($rental->user_id === $request->user()->id, 403);
        abort_unless(in_array($rental->status->value, ['active']), 409);

        // FIX 2: a rental can have only ONE pending request at a time.
        // create() already checked it, but a form can be sent directly without
        // opening that page, so the rule must also be enforced here.
        if ($rental->extensions()->where('status', ExtensionStatus::PENDING->value)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['new_end_date' => __('This rental already has a pending extension request.')]);
        }

        $data = $request->validate([
            'new_end_date' => ['required', 'date', 'after:'.$rental->end_date->toDateString()],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $newEnd = Carbon::parse($data['new_end_date']);
        $additionalAmount = $this->suggestedAmount($rental, $rental->end_date, $newEnd);

        $extension = RentalExtension::create([
            'rental_id' => $rental->id,
            'requested_date' => now()->toDateString(),
            'old_end_date' => $rental->end_date,
            'new_end_date' => $newEnd,
            'additional_amount' => $additionalAmount,
            'reason' => $data['reason'] ?? null,
            'status' => ExtensionStatus::PENDING,
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
     *
     * FIX 3: the rental update and the status change now run in ONE transaction with
     * locked rows (same as the admin version). Two clicks at the same time, or a rental
     * edited in between, can no longer produce a double approval or inconsistent data.
     */
    public function approve(Request $request, RentalExtension $rentalExtension): RedirectResponse
    {
        abort_unless((int) $rentalExtension->rental->equipment->owner_id === (int) $request->user()->id, 403);
        abort_unless($rentalExtension->status === ExtensionStatus::PENDING, 409);

        $error = null;

        DB::transaction(function () use ($rentalExtension, &$error) {
            // lockForUpdate(): other transactions must wait until we finish
            $extension = RentalExtension::whereKey($rentalExtension->id)->lockForUpdate()->first();
            $rental = Rental::whereKey($extension->rental_id)->lockForUpdate()->first();

            // Re-check with the locked data: someone may have processed it a moment ago
            if ($extension->status !== ExtensionStatus::PENDING) {
                $error = 'This request has already been processed.';

                return;
            }

            if ($extension->new_end_date->lte($rental->end_date)) {
                $error = 'The requested end date is no longer after the rental end date.';

                return;
            }

            $rental->update([
                'end_date' => $extension->new_end_date,
                'total_amount' => round((float) $rental->total_amount + (float) $extension->additional_amount, 2),
            ]);

            $extension->update(['status' => ExtensionStatus::APPROVED]);
        });

        if ($error) {
            return redirect()->route('front.my-rental-detail', $rentalExtension->rental->reference)
                ->withErrors(['extension' => $error]);
        }

        // Fire real-time notification for extension approved (after the transaction succeeded)
        $rentalExtension->refresh()->load('rental.equipment', 'rental.user');
        ExtensionApproved::dispatch($rentalExtension, $rentalExtension->rental->user);

        return redirect()->route('front.my-rental-detail', $rentalExtension->rental->reference)
            ->with('status', 'extension-approved');
    }

    /**
     * Front-office: Reject extension request (owner only).
     */
    public function reject(Request $request, RentalExtension $rentalExtension): RedirectResponse
    {
        abort_unless((int) $rentalExtension->rental->equipment->owner_id === (int) $request->user()->id, 403);
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
        $extraDays = max(1, (int) $oldEnd->diffInDays($newEnd));
        $dailyRate = (float) $rental->total_amount / $rentalDays;

        return round($dailyRate * $extraDays, 2);
    }
}