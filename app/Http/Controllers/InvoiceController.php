<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    public function index()
    {
        $query = Invoice::with('reservation.equipment')->latest();
        if (! request()->user()->isAdmin()) $query->whereHas('reservation', fn ($q) => $q->where('user_id', request()->user()->id));
        return view('invoices.index', ['invoices' => $query->paginate(10)]);
    }

    public function create()
    {
        $query = Reservation::doesntHave('invoice');
        if (! request()->user()->isAdmin()) $query->where('user_id', request()->user()->id);
        return view('invoices.create', ['reservations' => $query->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'reservation_id' => 'required|exists:reservations,id|unique:invoices,reservation_id',
            'issue_date'     => 'required|date',
            'subtotal'       => 'required|numeric|min:0',
            'status'         => 'required|in:unpaid,paid',
        ]);

        $data['tax']            = round($data['subtotal'] * 0.19, 2);
        $data['total']          = $data['subtotal'] + $data['tax'];
        $data['invoice_number'] = 'INV-' . random_int(100000, 999999);

        $reservation = Reservation::findOrFail($data['reservation_id']);
        abort_unless(request()->user()->isAdmin() || $reservation->user_id === request()->user()->id, 403);
        Invoice::create($data);

        return redirect()->route('rental.invoices.index')->with('success', __('Invoice created successfully.'));
    }

    public function show(Invoice $invoice)
    {
        abort_unless(request()->user()->isAdmin() || $invoice->reservation->user_id === request()->user()->id, 403);
        $invoice->load('reservation.equipment');
        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        abort_unless(request()->user()->isAdmin() || $invoice->reservation->user_id === request()->user()->id, 403);
        return view('invoices.edit', compact('invoice'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'issue_date' => 'required|date',
            'subtotal'   => 'required|numeric|min:0',
            'status'     => 'required|in:unpaid,paid',
        ]);

        $data['tax']   = round($data['subtotal'] * 0.19, 2);
        $data['total'] = $data['subtotal'] + $data['tax'];

        abort_unless(request()->user()->isAdmin() || $invoice->reservation->user_id === request()->user()->id, 403);
        $invoice->update($data);

        return redirect()->route('rental.invoices.index')->with('success', __('Invoice updated successfully.'));
    }

    public function destroy(Invoice $invoice)
    {
        abort_unless(request()->user()->isAdmin() || $invoice->reservation->user_id === request()->user()->id, 403);
        $invoice->delete();
        return redirect()->route('rental.invoices.index')->with('success', __('Invoice deleted successfully.'));
    }
}
