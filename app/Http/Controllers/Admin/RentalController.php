<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminRentalStoreRequest;
use App\Http\Requests\Admin\AdminRentalUpdateRequest;
use App\Models\Rental;
use App\Models\User;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * CRUD of rentals for the back office.
 * Every route of this controller is protected by the "can:admin-only"
 * middleware declared in routes/web.php (same as the users management).
 *
 * Route name          | URL                        | Method
 * admin.rentals.index   | GET    /admin/rentals            | index()   list
 * admin.rentals.create  | GET    /admin/rentals/create     | create()  empty form
 * admin.rentals.store   | POST   /admin/rentals            | store()   save new
 * admin.rentals.show    | GET    /admin/rentals/{rental}   | show()    details
 * admin.rentals.edit    | GET    /admin/rentals/{rental}/edit | edit() filled form
 * admin.rentals.update  | PUT    /admin/rentals/{rental}   | update()  save changes
 * admin.rentals.destroy | DELETE /admin/rentals/{rental}   | destroy() delete
 */
class RentalController extends Controller
{
    /** List of rentals, with search + status filter + pagination. */
    public function index(Request $request): View
    {
        // with('user') loads the renters in ONE extra query (avoids the N+1 problem)
        $query = Rental::query()->with('user');

        // Load the catalogue listing so rental rows show the actual equipment name.
        $query->with('equipment.category');

        // Filter by status (dropdown)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by reference OR by the renter's name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', '%' . $search . '%')
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%' . $search . '%'))
                  ->orWhereHas('equipment', fn ($equipment) => $equipment
                      ->where('name', 'like', '%' . $search . '%')
                      ->orWhere('brand', 'like', '%' . $search . '%')
                      ->orWhere('model', 'like', '%' . $search . '%'));
            });
        }

        // 10 per page; withQueryString() keeps ?search=...&status=... on the page links
        $rentals = $query->latest()->paginate(10)->withQueryString();

        return view('pages.admin.rentals.index', [
            'rentals' => $rentals,
            'title'   => __('Rentals'),
        ]);
    }

    /** Empty creation form. */
    public function create(): View
    {
        return view('pages.admin.rentals.create', $this->formData() + [
            'title' => __('Create Rental'),
        ]);
    }

    /** Save a new rental (validation already done by AdminRentalStoreRequest). */
    public function store(AdminRentalStoreRequest $request): RedirectResponse
    {
        // The reference needs the new id (e.g. RNT-2026-0007), so we:
        // 1) insert with a temporary unique reference, 2) replace it with the final one.
        // The transaction makes both steps succeed or fail together.
        DB::transaction(function () use ($request) {
            $attributes = $this->reservationAttributes($request->validated());
            $rental = Rental::create($attributes + [
                'reference' => 'TMP-' . Str::uuid(),
            ]);

            $rental->update([
                'reference' => sprintf('RNT-%d-%04d', now()->year, $rental->id),
            ]);
        });

        return redirect()->route('admin.rentals.index')->with('status', 'rental-created');
    }

    /** Details of one rental (with its contract and extension requests). */
    public function show(Rental $rental): View
    {
        // Route model binding: Laravel already found the Rental from the {rental} id in the URL
        $rental->load(['user', 'equipment.category', 'contract', 'extensions']);

        return view('pages.admin.rentals.show', [
            'rental' => $rental,
            'title'  => __('Rental Details'),
        ]);
    }

    /** Edit form, pre-filled with the current values. */
    public function edit(Rental $rental): View
    {
        return view('pages.admin.rentals.edit', $this->formData() + [
            'rental' => $rental,
            'title'  => __('Edit Rental'),
        ]);
    }

    /** Save the changes (validation done by AdminRentalUpdateRequest). */
    public function update(AdminRentalUpdateRequest $request, Rental $rental): RedirectResponse
    {
        $rental->update($this->reservationAttributes($request->validated()));

        return redirect()->route('admin.rentals.index')->with('status', 'rental-updated');
    }

    /** Delete a rental (its contract and extensions are deleted too by the database). */
    public function destroy(Rental $rental): RedirectResponse
    {
        $rental->delete();

        return redirect()->route('admin.rentals.index')->with('status', 'rental-deleted');
    }

    /**
     * Data needed by the create and edit forms (the dropdowns).
     * - users: every user can be a renter
     * - equipments: empty until Student 1's Equipment model exists; the form then
     *   falls back to a simple number input.
     */
    private function formData(): array
    {
        return [
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
            'equipments' => \App\Models\Equipment::query()
                ->with('category')
                ->where('approval_status', 'published')
                ->orderBy('name')
                ->get(['id', 'category_id', 'name', 'brand', 'model', 'status']),
        ];
    }

    /** Keep a manually linked rental identical to its paid, owner-approved reservation. */
    private function reservationAttributes(array $attributes): array
    {
        if (empty($attributes['reservation_id'])) {
            return $attributes;
        }

        $reservation = Reservation::with('payments')->findOrFail($attributes['reservation_id']);
        $payment = $reservation->payments->firstWhere('status', 'paid');
        abort_unless($reservation->status === 'confirmed' && $payment, 422, __('A linked rental requires an approved reservation with verified payment.'));

        return array_merge($attributes, [
            'user_id' => $reservation->user_id,
            'equipment_id' => $reservation->equipment_id,
            'start_date' => $reservation->start_date,
            'end_date' => $reservation->end_date,
            'total_amount' => $payment->amount,
        ]);
    }
}
