<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head><meta charset="utf-8"><title>{{ __('Maintenance report') }}</title></head>
<body>
    <h1>{{ __('Maintenance report') }} #{{ $maintenance->id }}</h1>
    <p><strong>{{ __('Equipment') }}:</strong> {{ $maintenance->equipment?->name ?? '#'.$maintenance->equipment_id }}</p>
    <p><strong>{{ __('Start date') }}:</strong> {{ $maintenance->start_date?->format('Y-m-d') }}</p>
    <p><strong>{{ __('End date') }}:</strong> {{ $maintenance->end_date?->format('Y-m-d') ?: '—' }}</p>
    <p><strong>{{ __('Reason') }}:</strong> {{ $maintenance->reason }}</p>
    <p><strong>{{ __('Status') }}:</strong> {{ __($maintenance->status) }}</p>
    <p><strong>{{ __('Cost') }}:</strong> {{ number_format((float) $maintenance->cost, 2) }} TND</p>
    <p><strong>{{ __('Diagnosis') }}:</strong> {{ $maintenance->report?->diagnosis ?: '—' }}</p>
    <p><strong>{{ __('Actions taken') }}:</strong> {{ $maintenance->report?->actions_taken ?: '—' }}</p>
    <p><strong>{{ __('Parts replaced') }}:</strong> {{ $maintenance->report?->parts_replaced ?: '—' }}</p>
    <p><strong>{{ __('Technician notes') }}:</strong> {{ $maintenance->report?->technician_notes ?: $maintenance->notes ?: '—' }}</p>
    <p><strong>{{ __('Inspector') }}:</strong> {{ $inspector->name }}</p>
    <p>{{ __('Signature') }}: ______________________________</p>
</body>
</html>
