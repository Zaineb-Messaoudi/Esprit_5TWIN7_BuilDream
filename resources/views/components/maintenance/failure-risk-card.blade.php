@props(['risk'])

@php
    [$label, $classes] = match ($risk['level']) {
        'high' => [__('High'), 'border-error-200 bg-error-50 text-error-700 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-300'],
        'medium' => [__('Medium'), 'border-warning-200 bg-warning-50 text-warning-700 dark:border-warning-500/20 dark:bg-warning-500/10 dark:text-warning-300'],
        default => [__('Low'), 'border-success-200 bg-success-50 text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-300'],
    };
@endphp

<section class="rounded-2xl border p-5 {{ $classes }}" aria-labelledby="failure-risk-title">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 id="failure-risk-title" class="font-semibold">{{ __('Failure risk prediction') }}</h2>
            <p class="mt-1 text-sm">{{ __('Rule-based prediction from technical history.') }}</p>
        </div>
        <span class="rounded-full bg-white/80 px-4 py-2 text-sm font-semibold ring-1 ring-current/20 dark:bg-gray-900/40">
            {{ $risk['score'] }}/100 &middot; {{ $label }}
        </span>
    </div>
    <dl class="mt-5 grid gap-3 text-sm sm:grid-cols-3">
        <div><dt class="opacity-75">{{ __('Rentals') }}</dt><dd class="mt-1 font-semibold">{{ $risk['rentals'] }}</dd></div>
        <div><dt class="opacity-75">{{ __('Past damages') }}</dt><dd class="mt-1 font-semibold">{{ $risk['damages'] }}</dd></div>
        <div><dt class="opacity-75">{{ __('Days since maintenance') }}</dt><dd class="mt-1 font-semibold">{{ $risk['days_since_maintenance'] ?? __('Never maintained') }}</dd></div>
    </dl>
</section>
