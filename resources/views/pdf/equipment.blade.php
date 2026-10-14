@extends('pdf.layout')

@section('documentTitle', __('Equipment technical sheet'))
@section('documentNumber', 'SOLAR-'.$equipment->id)

@section('content')
<div class="section">
    <h2 class="section-title">{{ __('Equipment') }}</h2>
    <div class="info-grid">
        <div class="info-item"><span class="info-label">{{ __('Name') }}</span><span class="info-value">{{ __($equipment->name) }}</span></div>
        <div class="info-item"><span class="info-label">{{ __('Category') }}</span><span class="info-value">{{ __($equipment->category?->name) }}</span></div>
        <div class="info-item"><span class="info-label">{{ __('Brand / model') }}</span><span class="info-value">{{ $equipment->brand }} {{ $equipment->model }}</span></div>
        <div class="info-item"><span class="info-label">{{ __('Location') }}</span><span class="info-value">{{ $equipment->location }}</span></div>
        <div class="info-item"><span class="info-label">{{ __('Price per day') }}</span><span class="info-value">{{ number_format($equipment->price_per_day, 2) }} TND</span></div>
        <div class="info-item"><span class="info-label">{{ __('Condition') }}</span><span class="info-value">{{ __(ucfirst($equipment->condition)) }}</span></div>
    </div>
</div>
<div class="section">
    <h2 class="section-title">{{ __('Energy profile') }}</h2>
    <div class="info-grid">
        @foreach(['Power (W)' => $equipment->energyProfile?->power_watts, 'Capacity (Wh)' => $equipment->energyProfile?->capacity_wh, 'Voltage (V)' => $equipment->energyProfile?->voltage, 'Efficiency (%)' => $equipment->energyProfile?->efficiency, 'Technology' => $equipment->energyProfile?->technology, 'Max output (W)' => $equipment->energyProfile?->max_output] as $label => $value)
            <div class="info-item"><span class="info-label">{{ __($label) }}</span><span class="info-value">{{ $value ?: '—' }}</span></div>
        @endforeach
    </div>
</div>
<div style="text-align:center; margin-top:2rem; padding:1rem; border:1px solid #b5dec2; border-radius:8px;">
    <img src="{{ $qrUrl }}" width="140" height="140" alt="QR code">
    <p style="font-size:9pt; color:#667085;">{{ __('Scan to view this listing on SolarShare') }}</p>
    <p style="font-size:8pt; color:#667085;">{{ $publicUrl }}</p>
</div>
@endsection
