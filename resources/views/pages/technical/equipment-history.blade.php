@extends($layout)

@section('content')
<div class="mx-auto w-full max-w-4xl px-6 py-8 sm:px-8 lg:px-10">
    <h1 class="text-title-md font-semibold text-gray-900 dark:text-white">{{ __('Equipment history') }} — {{ $equipment->name }}</h1>
    <dl class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([__('Maintenance costs') => number_format($tco['maintenance_costs'], 2).' TND', __('Rental revenue') => number_format($tco['rental_revenue'], 2).' TND', __('Net margin') => number_format($tco['net_margin'], 2).' TND', __('Cost/revenue ratio') => $tco['cost_revenue_ratio'] === null ? '—' : number_format($tco['cost_revenue_ratio'] * 100, 2).'%'] as $label => $value)
            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <dt class="text-xs text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                <dd class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $value }}</dd>
            </div>
        @endforeach
    </dl>
    <ol class="mt-8 space-y-4">
        @forelse ($timeline as $event)
            <li class="relative border-s-2 border-brand-200 ps-6 dark:border-brand-800">
                <span class="absolute -start-[7px] top-2 size-3 rounded-full bg-brand-500" aria-hidden="true"></span>
                <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-400">{{ __($event['type']) }} · {{ $event['date']->format('Y-m-d') }}</p>
                    <h2 class="mt-2 font-semibold text-gray-900 dark:text-white">{{ $event['title'] }}</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __($event['status']) }}</p>
                </article>
            </li>
        @empty
            <li class="rounded-2xl border border-gray-200 bg-white p-8 text-center text-gray-500 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">{{ __('No technical history.') }}</li>
        @endforelse
    </ol>
</div>
@endsection
