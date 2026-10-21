<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Reservation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index()
    {
        $query = Invoice::with('reservation.equipment')->latest();
        if (! request()->user()->isAdmin()) {
            $query->whereHas('reservation', fn ($q) => $q->where('user_id', request()->user()->id));
        }

        return view('invoices.index', ['invoices' => $query->paginate(10)]);
    }

    public function create()
    {
        abort_unless(request()->user()->isAdmin(), 403);
        $query = Reservation::doesntHave('invoice');
        if (! request()->user()->isAdmin()) {
            $query->where('user_id', request()->user()->id);
        }

        return view('invoices.create', ['reservations' => $query->get()]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);
        $data = $request->validate([
            'reservation_id' => 'required|exists:reservations,id|unique:invoices,reservation_id',
            'issue_date' => 'required|date',
            'subtotal' => 'nullable|numeric|min:0',
        ]);

        $reservation = Reservation::findOrFail($data['reservation_id']);
        abort_unless(request()->user()->isAdmin() || $reservation->user_id === request()->user()->id, 403);

        $data['subtotal'] = request()->user()->isAdmin() && isset($data['subtotal'])
            ? $data['subtotal']
            : $reservation->total_amount;
        $data['tax'] = round($data['subtotal'] * (float) config('billing.tax_rate', 0.19), 2);
        $data['total'] = $data['subtotal'] + $data['tax'];
        $data['status'] = 'unpaid';
        $data['invoice_number'] = 'INV-'.random_int(100000, 999999);

        Invoice::create($data);

        return redirect()->route('rental.invoices.index')->with('success', __('Invoice created successfully.'));
    }

    public function show(Invoice $invoice)
    {
        abort_unless(request()->user()->isAdmin() || $invoice->reservation->user_id === request()->user()->id, 403);
        $invoice->load('reservation.equipment', 'reservation.payments');

        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        abort_unless(request()->user()->isAdmin(), 403);
        abort_unless($invoice->status === 'unpaid' && ! $invoice->reservation->payments()->exists(), 409, __('An invoice cannot be edited after payment activity begins.'));

        return view('invoices.edit', compact('invoice'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless($invoice->status === 'unpaid' && ! $invoice->reservation->payments()->exists(), 409, __('An invoice cannot be edited after payment activity begins.'));
        $data = $request->validate([
            'issue_date' => 'required|date',
            'subtotal' => 'nullable|numeric|min:0',
        ]);

        $data['subtotal'] = request()->user()->isAdmin() && isset($data['subtotal'])
            ? $data['subtotal']
            : $invoice->reservation->total_amount;
        $data['tax'] = round($data['subtotal'] * (float) config('billing.tax_rate', 0.19), 2);
        $data['total'] = $data['subtotal'] + $data['tax'];

        $invoice->update($data);

        return redirect()->route('rental.invoices.index')->with('success', __('Invoice updated successfully.'));
    }

    public function destroy(Invoice $invoice)
    {
        abort_unless(request()->user()->isAdmin(), 403);
        abort_unless($invoice->status === 'unpaid' && ! $invoice->reservation->payments()->exists(), 409, __('An invoice cannot be deleted after payment activity begins.'));
        $invoice->delete();

        return redirect()->route('rental.invoices.index')->with('success', __('Invoice deleted successfully.'));
    }

    /** Export invoice as PDF. */
    public function exportPdf(Invoice $invoice)
    {
        abort_unless(request()->user()->isAdmin() || $invoice->reservation->user_id === request()->user()->id, 403);
        $invoice->load('reservation.equipment.category', 'reservation.user', 'reservation.payments');
        $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $invoice]);

        return $pdf->download($invoice->invoice_number.'.pdf');
    }
}
