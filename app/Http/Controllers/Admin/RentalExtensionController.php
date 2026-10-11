<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ExtensionStatus;
use App\Enums\RentalStatus;
use App\Events\ExtensionApproved;
use App\Events\ExtensionRejected;
use App\Events\ExtensionRequested;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminRentalExtensionStoreRequest;
use App\Http\Requests\Admin\AdminRentalExtensionUpdateRequest;
use App\Models\Rental;
use App\Models\RentalExtension;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * CRUD + approve/reject workflow of rental extension requests (back office).
 *
 * Workflow: a request is created as PENDING. An admin then either
 *  - APPROVES it: the rental end_date and total_amount are updated, or
 *  - REJECTS it: the rental stays unchanged.
 * Only PENDING requests can be edited. APPROVED requests cannot be deleted
 * (they already changed the rental, we keep the history).
 *
 * Routes (resource + 2 custom actions), all under /admin and named admin.*:
 *  rental-extensions.index / create / store / show / edit / update / destroy
 *  rental-extensions.approve  (POST /admin/rental-extensions/{rental_extension}/approve)
 *  rental-extensions.reject   (POST /admin/rental-extensions/{rental_extension}/reject)
 */
class RentalExtensionController extends Controller
{
    /** List of requests, with search + status filter + pagination. */
    public function index(Request $request): View
    {
        // Load the rental and its renter with the list (avoids the N+1 problem)
        $query = RentalExtension::query()->with('rental.user');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by rental reference or renter name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('rental', function ($r) use ($search) {
                $r->where('reference', 'like', '%'.$search.'%')
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%'.$search.'%'));
            });
        }

        $extensions = $query->latest()->paginate(10)->withQueryString();

        return view('pages.admin.rental-extensions.index', [
            'extensions' => $extensions,
            'title' => __('Rental Extensions'),
        ]);
    }

    /**
     * Empty creation form.
     * Only rentals that can be extended are offered: pending or active,
     * and without another pending request. ?rental_id=5 pre-selects a rental.
     */
    public function create(Request $request): View
    {
        $rentals = Rental::query()
            ->whereIn('status', [RentalStatus::PENDING->value, RentalStatus::ACTIVE->value])
            ->whereDoesntHave('extensions', fn ($q) => $q->where('status', ExtensionStatus::PENDING->value))
            ->with('user')
            ->latest()
            ->get();

        return view('pages.admin.rental-extensions.create', [
            'rentals' => $rentals,
            'selectedRentalId' => $request->query('rental_id'),
            'title' => __('Create Extension Request'),
        ]);
    }

    /** Save a new request (validation already done by the Store request). */
    public function store(AdminRentalExtensionStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $rental = Rental::findOrFail($data['rental_id']);
        $newEnd = Carbon::parse($data['new_end_date']);

        $extension = RentalExtension::create([
            'rental_id' => $rental->id,
            'requested_date' => now()->toDateString(),   // the request is made today
            'old_end_date' => $rental->end_date,       // snapshot of the current end date
            'new_end_date' => $newEnd,
            // If the admin left the amount empty, compute it from the rental daily rate
            'additional_amount' => $data['additional_amount'] ?? $this->suggestedAmount($rental, $rental->end_date, $newEnd),
            'reason' => $data['reason'] ?? null,
            'status' => ExtensionStatus::PENDING,
        ]);

        // Fire real-time notification for extension requested
        ExtensionRequested::dispatch($extension->load('rental.equipment', 'rental.user'), $rental->equipment->owner);

        return redirect()->route('admin.rental-extensions.index')->with('status', 'extension-created');
    }

    /** Details of one request, with the approve / reject buttons. */
    public function show(RentalExtension $rentalExtension): View
    {
        // Route model binding: {rental_extension} in the URL -> $rentalExtension
        $rentalExtension->load('rental.user');

        return view('pages.admin.rental-extensions.show', [
            'extension' => $rentalExtension,
            'title' => __('Extension Details'),
        ]);
    }

    /** Edit form (only for pending requests). */
    public function edit(RentalExtension $rentalExtension): View|RedirectResponse
    {
        if ($rentalExtension->status !== ExtensionStatus::PENDING) {
            return redirect()
                ->route('admin.rental-extensions.show', $rentalExtension)
                ->withErrors(['extension' => 'Only a pending request can be edited.']);
        }

        $rentalExtension->load('rental.user');

        return view('pages.admin.rental-extensions.edit', [
            'extension' => $rentalExtension,
            'title' => __('Edit Extension Request'),
        ]);
    }

    /** Save the changes (validation done by the Update request). */
    public function update(AdminRentalExtensionUpdateRequest $request, RentalExtension $rentalExtension): RedirectResponse
    {
        if ($rentalExtension->status !== ExtensionStatus::PENDING) {
            return redirect()
                ->route('admin.rental-extensions.show', $rentalExtension)
                ->withErrors(['extension' => 'Only a pending request can be edited.']);
        }

        $data = $request->validated();
        $newEnd = Carbon::parse($data['new_end_date']);

        $rentalExtension->update([
            'new_end_date' => $newEnd,
            'additional_amount' => $data['additional_amount']
                ?? $this->suggestedAmount($rentalExtension->rental, $rentalExtension->old_end_date, $newEnd),
            'reason' => $data['reason'] ?? null,
        ]);

        return redirect()->route('admin.rental-extensions.index')->with('status', 'extension-updated');
    }

    /** Delete a request (not allowed once approved: it already changed the rental). */
    public function destroy(RentalExtension $rentalExtension): RedirectResponse
    {
        if ($rentalExtension->status === ExtensionStatus::APPROVED) {
            return redirect()
                ->route('admin.rental-extensions.index')
                ->withErrors(['extension' => 'An approved request cannot be deleted because it already updated the rental.']);
        }

        $rentalExtension->delete();

        return redirect()->route('admin.rental-extensions.index')->with('status', 'extension-deleted');
    }

    /**
     * APPROVE a pending request:
     * the rental gets the new end date and the additional amount is added to its total.
     * Everything happens in ONE transaction with locked rows, so two admins clicking
     * at the same time cannot approve twice.
     */
    public function approve(RentalExtension $rentalExtension): RedirectResponse
    {
        $error = null;

        DB::transaction(function () use ($rentalExtension, &$error) {
            // lockForUpdate(): other transactions must wait until we finish
            $extension = RentalExtension::whereKey($rentalExtension->id)->lockForUpdate()->first();
            $rental = Rental::whereKey($extension->rental_id)->lockForUpdate()->first();

            if ($extension->status !== ExtensionStatus::PENDING) {
                $error = 'This request has already been processed.';

                return;
            }

            if (! in_array($rental->status, [RentalStatus::PENDING, RentalStatus::ACTIVE], true)) {
                $error = 'Only a pending or active rental can be extended.';

                return;
            }

            // The rental may have been edited since the request was made
            if ($extension->new_end_date->lte($rental->end_date)) {
                $error = 'The new end date is no longer after the rental end date. Edit the request first.';

                return;
            }

            // TODO (after Student 4's merge): refuse the extension if another reservation
            // of the same equipment overlaps the extra period (old end date -> new end date).

            $rental->update([
                'end_date' => $extension->new_end_date,
                'total_amount' => round((float) $rental->total_amount + (float) $extension->additional_amount, 2),
            ]);

            $extension->update(['status' => ExtensionStatus::APPROVED]);
        });

        if ($error) {
            return back()->withErrors(['extension' => $error]);
        }

        // Fire real-time notification for extension approved
        $rentalExtension->load('rental.equipment', 'rental.user');
        ExtensionApproved::dispatch($rentalExtension, $rentalExtension->rental->user);

        return back()->with('status', 'extension-approved');
    }

    /** REJECT a pending request: the rental stays unchanged. */
    public function reject(RentalExtension $rentalExtension): RedirectResponse
    {
        if ($rentalExtension->status !== ExtensionStatus::PENDING) {
            return back()->withErrors(['extension' => 'This request has already been processed.']);
        }

        $rentalExtension->update(['status' => ExtensionStatus::REJECTED]);

        // Fire real-time notification for extension rejected
        $rentalExtension->load('rental.equipment', 'rental.user');
        ExtensionRejected::dispatch($rentalExtension, $rentalExtension->rental->user);

        return back()->with('status', 'extension-rejected');
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
