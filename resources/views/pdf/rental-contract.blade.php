@extends('pdf.layout')

@section('documentTitle', 'Rental Contract')
@section('documentNumber', $contract->contract_number)

@section('content')
    <div class="section">
        <h2 class="section-title">Rental Information</h2>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Rental Reference</span>
                <span class="info-value">{{ $contract->rental->reference }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Renter</span>
                <span class="info-value">{{ $contract->rental->user->name }} ({{ $contract->rental->user->email }})</span>
            </div>
            <div class="info-item">
                <span class="info-label">Equipment</span>
                <span class="info-value">{{ $contract->rental->equipment->name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Category</span>
                <span class="info-value">{{ $contract->rental->equipment->category->name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Rental Period</span>
                <span class="info-value">{{ $contract->rental->start_date->format('d/m/Y') }} to {{ $contract->rental->end_date->format('d/m/Y') }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Status</span>
                <span class="info-value">
                    <span class="status-badge status-{{ $contract->contract_status->value }}">{{ $contract->contract_status->label() }}</span>
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">Signed At</span>
                <span class="info-value">{{ $contract->signed_at?->format('d/m/Y H:i') ?? 'Not signed yet' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Deposit Amount</span>
                <span class="info-value">{{ number_format($contract->deposit_amount, 2) }} TND</span>
            </div>
        </div>
    </div>

    <div class="section">
        <h2 class="section-title">Terms and Conditions</h2>
        <div class="terms-box">
            {!! nl2br(e($contract->terms)) !!}
        </div>
    </div>

    @if ($contract->signed_at)
    <div class="section">
        <h2 class="section-title">Signature</h2>
        <p class="text-sm color: #667085;">This contract was electronically signed on {{ $contract->signed_at->format('d/m/Y H:i') }}.</p>
    </div>
    @endif
@endsection