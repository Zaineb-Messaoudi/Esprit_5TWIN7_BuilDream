<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head><meta charset="utf-8"><title>{{ __('Inspection report') }}</title></head>
<body>
    <h1>{{ __('Inspection report') }} #{{ $inspection->id }}</h1>
    <p><strong>{{ __('Equipment') }}:</strong> {{ $inspection->equipment?->name ?? '#'.$inspection->equipment_id }}</p>
    <p><strong>{{ __('Inspection date') }}:</strong> {{ $inspection->inspection_date?->format('Y-m-d') }}</p>
    <p><strong>{{ __('Condition before') }}:</strong> {{ $inspection->condition_before }}</p>
    <p><strong>{{ __('Condition after') }}:</strong> {{ $inspection->condition_after }}</p>
    <p><strong>{{ __('Damage detected') }}:</strong> {{ $inspection->damage_detected ? __('Yes') : __('No') }}</p>

    <h2>{{ __('Checklist results') }}</h2>
    <table width="100%" border="1" cellspacing="0" cellpadding="6">
        <thead><tr><th>{{ __('Control') }}</th><th>{{ __('Critical') }}</th><th>{{ __('Result') }}</th></tr></thead>
        <tbody>
        @forelse ($inspection->checklist_results ?? [] as $result)
            <tr><td>{{ $result['label'] }}</td><td>{{ $result['is_critical'] ? __('Yes') : __('No') }}</td><td>{{ $result['passed'] ? __('Validated') : __('Not validated') }}</td></tr>
        @empty
            <tr><td colspan="3">{{ __('No checklist results.') }}</td></tr>
        @endforelse
        </tbody>
    </table>

    <h2>{{ __('Damages') }}</h2>
    <ul>@forelse ($inspection->damages as $damage)<li>{{ $damage->severity }} — {{ $damage->description }}</li>@empty<li>{{ __('No damage') }}</li>@endforelse</ul>
    <p><strong>{{ __('Comments') }}:</strong> {{ $inspection->comments ?: '—' }}</p>
    <p><strong>{{ __('Inspector') }}:</strong> {{ $inspector->name }}</p>
    <p>{{ __('Signature') }}: ______________________________</p>
</body>
</html>
