<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContractStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminRentalContractStoreRequest;
use App\Http\Requests\Admin\AdminRentalContractUpdateRequest;
use App\Models\Rental;
use App\Models\RentalContract;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * CRUD of rental contracts for the back office.
 * Protected by the admin-only middleware of the "admin." route group.
 *
 * Route name                     | URL                                   | Method
 * admin.rental-contracts.index   | GET    /admin/rental-contracts            | index()
 * admin.rental-contracts.create  | GET    /admin/rental-contracts/create     | create()
 * admin.rental-contracts.store   | POST   /admin/rental-contracts            | store()
 * admin.rental-contracts.show    | GET    /admin/rental-contracts/{rental_contract}      | show()
 * admin.rental-contracts.edit    | GET    /admin/rental-contracts/{rental_contract}/edit | edit()
 * admin.rental-contracts.update  | PUT    /admin/rental-contracts/{rental_contract}      | update()
 * admin.rental-contracts.destroy | DELETE /admin/rental-contracts/{rental_contract}      | destroy()
 */
class RentalContractController extends Controller
{
    /** List of contracts, with search + status filter + pagination. */
    public function index(Request $request): View
    {
        // Load the rental AND its renter in the same queries (avoids the N+1 problem)
        $query = RentalContract::query()->with('rental.user');

        // Filter by contract status (dropdown)
        if ($request->filled('status')) {
            $query->where('contract_status', $request->status);
        }

        // Search by contract number, rental reference or renter name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('contract_number', 'like', '%'.$search.'%')
                    ->orWhereHas('rental', function ($r) use ($search) {
                        $r->where('reference', 'like', '%'.$search.'%')
                            ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%'.$search.'%'));
                    });
            });
        }

        $contracts = $query->latest()->paginate(10)->withQueryString();

        return view('pages.admin.rental-contracts.index', [
            'contracts' => $contracts,
            'title' => __('Rental Contracts'),
        ]);
    }

    /**
     * Empty creation form.
     * Only rentals WITHOUT a contract are offered (1 rental = 1 contract).
     * The rentals page can link here with ?rental_id=5 to pre-select a rental.
     */
    public function create(Request $request): View
    {
        return view('pages.admin.rental-contracts.create', [
            'rentals' => Rental::doesntHave('contract')->with('user')->latest()->get(),
            'selectedRentalId' => $request->query('rental_id'),
            'title' => __('Create Contract'),
        ]);
    }

    /** Save a new contract (validation already done by the Store request). */
    public function store(AdminRentalContractStoreRequest $request): RedirectResponse
    {
        $data = $this->prepareData($request->validated());

        // Same trick as for rentals: the contract number needs the new id
        // (e.g. CTR-2026-0007), so we insert with a temporary number, then replace it.
        // The transaction makes both steps succeed or fail together.
        DB::transaction(function () use ($data) {
            $contract = RentalContract::create($data + [
                'contract_number' => 'TMP-'.Str::uuid(),
            ]);

            $contract->update([
                'contract_number' => sprintf('CTR-%d-%04d', now()->year, $contract->id),
            ]);
        });

        return redirect()->route('admin.rental-contracts.index')->with('status', 'contract-created');
    }

    /** Details of one contract. */
    public function show(RentalContract $rentalContract): View
    {
        // Route model binding: {rental_contract} in the URL -> $rentalContract
        $rentalContract->load('rental.user');

        return view('pages.admin.rental-contracts.show', [
            'contract' => $rentalContract,
            'title' => __('Contract Details'),
        ]);
    }

    /** Edit form, pre-filled with the current values. */
    public function edit(RentalContract $rentalContract): View
    {
        $rentalContract->load('rental.user');

        return view('pages.admin.rental-contracts.edit', [
            'contract' => $rentalContract,
            'title' => __('Edit Contract'),
        ]);
    }

    /** Save the changes (validation done by the Update request). */
    public function update(AdminRentalContractUpdateRequest $request, RentalContract $rentalContract): RedirectResponse
    {
        $rentalContract->update($this->prepareData($request->validated()));

        return redirect()->route('admin.rental-contracts.index')->with('status', 'contract-updated');
    }

    /** Delete a contract (the rental itself is kept). */
    public function destroy(RentalContract $rentalContract): RedirectResponse
    {
        $rentalContract->delete();

        return redirect()->route('admin.rental-contracts.index')->with('status', 'contract-deleted');
    }

    /** Export contract as PDF. */
    public function exportPdf(RentalContract $rentalContract)
    {
        $rentalContract->load('rental.user', 'rental.equipment.category');
        $pdf = Pdf::loadView('pdf.rental-contract', ['contract' => $rentalContract]);

        return $pdf->download($rentalContract->contract_number.'.pdf');
    }

    /**
     * Keep the signature date consistent with the status:
     * - draft      -> not signed, so signed_at is emptied
     * - signed or terminated -> signed_at is the typed date, or "now" if left empty
     */
    private function prepareData(array $data): array
    {
        if ($data['contract_status'] === ContractStatus::DRAFT->value) {
            $data['signed_at'] = null;
        } elseif (empty($data['signed_at'])) {
            $data['signed_at'] = now();
        }

        return $data;
    }

    /** Hold deposit (admin). */
    public function holdDeposit(RentalContract $rentalContract): RedirectResponse
    {
        if (! $rentalContract->canHoldDeposit()) {
            return back()->with('error', 'Deposit cannot be held in current state.');
        }
        $rentalContract->holdDeposit(request('notes'));

        return back()->with('status', 'deposit-held');
    }

    /** Release deposit (admin). */
    public function releaseDeposit(RentalContract $rentalContract): RedirectResponse
    {
        if (! $rentalContract->canReleaseDeposit()) {
            return back()->with('error', 'Deposit cannot be released in current state.');
        }
        $rentalContract->releaseDeposit(request('notes'));

        return back()->with('status', 'deposit-released');
    }

    /** Forfeit deposit (admin). */
    public function forfeitDeposit(RentalContract $rentalContract): RedirectResponse
    {
        if (! $rentalContract->canForfeitDeposit()) {
            return back()->with('error', 'Deposit cannot be forfeited in current state.');
        }
        $rentalContract->forfeitDeposit(request('notes'));

        return back()->with('status', 'deposit-forfeited');
    }
}
