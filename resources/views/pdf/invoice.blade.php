@extends('pdf.layout')

@section('documentTitle', 'Invoice')
@section('documentNumber', $invoice->invoice_number)

@section('content')
    <div class="section">
        <h2 class="section-title">Invoice Details</h2>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Invoice Number</span>
                <span class="info-value">{{ $invoice->invoice_number }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Issue Date</span>
                <span class="info-value">{{ $invoice->issue_date->format('d/m/Y') }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Status</span>
                <span class="info-value">
                    <span class="status-badge status-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span>
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">Reservation</span>
                <span class="info-value">{{ $invoice->reservation->reference }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Renter</span>
                <span class="info-value">{{ $invoice->reservation->user->name }} ({{ $invoice->reservation->user->email }})</span>
            </div>
            <div class="info-item">
                <span class="info-label">Equipment</span>
                <span class="info-value">{{ $invoice->reservation->equipment->name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Rental Period</span>
                <span class="info-value">{{ $invoice->reservation->start_date->format('d/m/Y') }} to {{ $invoice->reservation->end_date->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>

    <div class="section">
        <h2 class="section-title">Amount Breakdown</h2>
        <div class="amount-box">
            <div class="amount-row">
                <span class="amount-label">Subtotal</span>
                <span class="amount-value">{{ number_format($invoice->subtotal, 2) }} TND</span>
            </div>
            <div class="amount-row">
                <span class="amount-label">VAT (19%)</span>
                <span class="amount-value">{{ number_format($invoice->tax, 2) }} TND</span>
            </div>
            <div class="amount-row">
                <span class="amount-label">Total</span>
                <span class="amount-value">{{ number_format($invoice->total, 2) }} TND</span>
            </div>
        </div>
    </div>

    @if ($invoice->status === 'paid')
    <div class="section">
        <p class="text-sm color: #027a48; font-weight: 600;">✓ This invoice has been paid.</p>
    </div>
    @endif
@endsection