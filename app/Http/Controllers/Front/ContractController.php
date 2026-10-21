<?php

namespace App\Http\Controllers\Front;

use App\Enums\ContractStatus;
use App\Enums\RentalStatus;
use App\Exceptions\GroqException;
use App\Http\Controllers\Controller;
use App\Models\Rental;
use App\Models\RentalContract;
use App\Services\ContractSummaryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Front office: the renter READS the contract first, then ACCEPTS it (signature) or DECLINES it.
 *
 * Rule given by the teacher: before signing, the client has the right to read the contract
 * to understand it, and signs only if he accepts. So signing is NOT one click anymore:
 *   1. show()    the contract page. Opening it records "the renter read the contract" in the session.
 *   2. accept()  needs the "I have read and accept" checkbox AND the page opened in the session.
 *   3. decline() the renter refuses: the contract is terminated and the rental is cancelled.
 *   4. summary() asks the AI for a plain-language summary (5 key points) to help understand it.
 *
 * Who can do what:
 *   - the renter (the user of the rental)        : read, summary, accept, decline, download the PDF
 *   - the owner of the rented equipment          : read, summary
 *   - an administrator                           : read, summary (an admin can never sign for a client)
 *
 * Routes (see routes/web.php):
 *   contracts.show    GET  /rental-contracts/{contract}
 *   contracts.accept  POST /rental-contracts/{contract}/accept
 *   contracts.decline POST /rental-contracts/{contract}/decline
 *   contracts.summary POST /rental-contracts/{contract}/summary
 */
class ContractController extends Controller
{
    // The summary service is injected by Laravel
    public function __construct(protected ContractSummaryService $summaries) {}

    /** The contract page: full contract, PDF link, AI summary and, for the renter of a draft, the sign form. */
    public function show(Request $request, RentalContract $contract): View
    {
        $role = $this->roleFor($request, $contract);
        $contract->loadMissing('rental.user', 'rental.equipment.owner');

        // Only the renter of a contract that still waits for a signature can sign it
        $canSign = $role === 'renter' && $contract->contract_status === ContractStatus::DRAFT;

        if ($canSign) {
            // Remember in the session that the renter opened (read) the contract.
            // accept() refuses to sign if this mark is missing.
            $request->session()->put($this->readKey($contract), now()->timestamp);
        }

        return view('pages.front.contract.show', [
            'contract' => $contract,
            'rental' => $contract->rental,
            'role' => $role,
            'canSign' => $canSign,
            'backUrl' => $this->backUrl($request, $contract, $role),
            'pdfUrl' => $this->pdfUrl($request, $contract, $role),
            // The summary generated earlier (null if none). This never calls the AI service.
            'summary' => $this->summaries->cached($contract, app()->getLocale()),
            'aiAvailable' => $this->summaries->isAvailable(),
            'title' => __('Rental Contract'),
        ]);
    }

    /**
     * Asks the AI for a plain-language summary of the contract, then goes back to the page,
     * where the summary is displayed (it is kept in the cache).
     * Any person who can read the contract may ask for it; the route is limited to 6 calls a minute.
     */
    public function summary(Request $request, RentalContract $contract): RedirectResponse
    {
        $this->roleFor($request, $contract); // 403 for anyone who cannot read this contract

        try {
            $this->summaries->summarize($contract, app()->getLocale());
        } catch (GroqException $e) {
            // The technical reason is already in the logs: the user only sees a friendly message
            return redirect()
                ->route('contracts.show', $contract)
                ->withErrors(['summary' => $e->userMessage()]);
        }

        return redirect()->route('contracts.show', $contract)->with('status', 'contract-summary-ready');
    }

    /** The renter accepts the contract: it becomes SIGNED. */
    public function accept(Request $request, RentalContract $contract): RedirectResponse
    {
        abort_unless($this->roleFor($request, $contract) === 'renter', 403);
        abort_unless(
            $contract->contract_status === ContractStatus::DRAFT,
            409,
            'This contract is no longer waiting for a signature.'
        );

        // 1) The checkbox must be ticked ("accepted" also accepts on, 1, yes, true)
        $request->validate(
            ['accept_terms' => ['accepted']],
            ['accept_terms.accepted' => __('You must confirm that you have read and accept the terms.')]
        );

        // 2) The contract page must have been opened in this session.
        //    This stops someone from sending the form directly without ever reading the contract.
        if (! $request->session()->has($this->readKey($contract))) {
            return redirect()
                ->route('contracts.show', $contract)
                ->withErrors(['accept_terms' => __('Please read the contract before signing it.')]);
        }

        // 3) Sign inside a transaction with a locked row: a double click cannot sign twice
        DB::transaction(function () use ($contract) {
            $locked = RentalContract::whereKey($contract->id)->lockForUpdate()->first();

            if ($locked->contract_status !== ContractStatus::DRAFT) {
                return; // already processed by a parallel request
            }

            $locked->update([
                'contract_status' => ContractStatus::SIGNED,
                'signed_at' => now(),
            ]);
        });

        $request->session()->forget($this->readKey($contract));

        return redirect()->route('contracts.show', $contract)->with('status', 'contract-signed');
    }

    /**
     * The renter refuses the contract after reading it:
     * the contract is TERMINATED and the rental is CANCELLED (so the equipment is free again).
     * NOTE: a refund is not handled here, the payment stays as it is.
     */
    public function decline(Request $request, RentalContract $contract): RedirectResponse
    {
        abort_unless($this->roleFor($request, $contract) === 'renter', 403);
        abort_unless(
            $contract->contract_status === ContractStatus::DRAFT,
            409,
            'This contract is no longer waiting for a signature.'
        );

        DB::transaction(function () use ($contract) {
            $locked = RentalContract::whereKey($contract->id)->lockForUpdate()->first();

            if ($locked->contract_status !== ContractStatus::DRAFT) {
                return; // already processed by a parallel request
            }

            $locked->update(['contract_status' => ContractStatus::TERMINATED]);

            // Cancel the rental, but only if it is still pending or active
            $rental = Rental::whereKey($locked->rental_id)->lockForUpdate()->first();
            if (in_array($rental->status, [RentalStatus::PENDING, RentalStatus::ACTIVE], true)) {
                $rental->update(['status' => RentalStatus::CANCELLED]);
            }
        });

        $request->session()->forget($this->readKey($contract));

        return redirect()->route('contracts.show', $contract)->with('status', 'contract-declined');
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /**
     * Who is the current user for this contract? 'renter', 'owner' or 'admin'.
     * Anyone else gets a 403 error: nobody can read a contract that is not theirs.
     */
    private function roleFor(Request $request, RentalContract $contract): string
    {
        $user = $request->user();
        $contract->loadMissing('rental.equipment');

        if ((int) $contract->rental->user_id === (int) $user->id) {
            return 'renter';
        }

        if ((int) $contract->rental->equipment?->owner_id === (int) $user->id) {
            return 'owner';
        }

        if ($user->isAdmin()) {
            return 'admin';
        }

        abort(403);
    }

    /** Session key that remembers "this user opened this contract". */
    private function readKey(RentalContract $contract): string
    {
        return 'contract_read.'.$contract->id;
    }

    /** Where the "Back" link of the contract page goes. */
    private function backUrl(Request $request, RentalContract $contract, string $role): string
    {
        // The front routes use the rental reference in the URL ({rental:reference}):
        // route() reads it from the model automatically.
        return match (true) {
            $role === 'admin' => route('admin.rental-contracts.show', $contract),
            $role === 'renter' && $request->user()->isBuyer() => route('front.buyer-rental-detail', $contract->rental),
            default => route('front.my-rental-detail', $contract->rental),
        };
    }

    /** Which PDF route to use for the "Download (PDF)" link. */
    private function pdfUrl(Request $request, RentalContract $contract, string $role): string
    {
        return match (true) {
            $role === 'admin' => route('admin.rental-contracts.export-pdf', $contract),
            $role === 'renter' && $request->user()->isBuyer() => route('front.rental-contract.export-pdf', $contract->rental),
            default => route('front.owner-rental-contract.export-pdf', $contract->rental),
        };
    }
}
