@props(['item', 'href' => null])

@php
    $available = $item->status === 'available';
    $profile = $item->energyProfile;
    $href ??= route('front.equipment.show', $item->id);
    $imageSrc = filter_var($item->image, FILTER_VALIDATE_URL) ? $item->image : asset($item->image);
@endphp

<article class="group flex flex-col overflow-hidden rounded-[1.75rem] border border-gray-200 bg-white shadow-theme-xs transition duration-500 hover:-translate-y-1 hover:border-brand-300 hover:shadow-theme-lg motion-reduce:transition-none motion-reduce:hover:translate-y-0 dark:border-gray-800 dark:bg-gray-900/70">
    <div class="relative aspect-5/4 overflow-hidden bg-linear-to-br from-brand-50 to-warning-50 dark:from-brand-950 dark:to-gray-800">
        <img src="{{ $imageSrc }}" alt="{{ __($item->name) }}" width="800" height="600" loading="lazy" class="size-full object-cover transition duration-500 motion-reduce:transition-none group-hover:scale-110" />
        <div class="pointer-events-none absolute inset-0 bg-linear-to-t from-gray-950/80 via-gray-950/10 to-transparent" aria-hidden="true"></div>
        <span title="{{ __($item->category->name) }}" class="absolute start-4 top-4 max-w-[68%] truncate rounded-full border border-white/30 bg-gray-950/55 px-3 py-1.5 text-theme-xs font-medium text-white shadow-theme-xs backdrop-blur">
            {{ __($item->category->name) }}
        </span>
        <span @class([
            'absolute bottom-4 end-4 rounded-full px-2.5 py-1 text-theme-xs font-semibold backdrop-blur',
            'bg-success-100/90 text-success-800 dark:bg-success-500/25 dark:text-success-300' => $available,
            'bg-warning-100/90 text-warning-800 dark:bg-warning-500/25 dark:text-warning-300' => ! $available,
        ])>
            {{ $available ? __('Available') : __(ucfirst($item->status)) }}
        </span>
        <div class="absolute bottom-4 start-4 max-w-[68%] text-white transition duration-300 group-hover:opacity-0">
            <p class="text-theme-xs font-medium text-white/75">{{ $item->location }} · {{ $item->brand }}</p>
        </div>
        <div class="pointer-events-none absolute inset-0 flex translate-y-3 flex-col justify-end bg-gray-950/88 p-5 opacity-0 backdrop-blur-sm transition duration-300 group-hover:translate-y-0 group-hover:opacity-100 motion-reduce:translate-y-0 motion-reduce:transition-none">
            <p class="text-theme-xs font-semibold uppercase tracking-[0.18em] text-warning-300">{{ __('Live equipment readout') }}</p>
            <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-3 text-theme-xs text-white">
                <div><dt class="text-gray-400">{{ __('Owner') }}</dt><dd class="mt-0.5 font-semibold">{{ $item->owner }}</dd></div>
                <div><dt class="text-gray-400">{{ __('Location') }}</dt><dd class="mt-0.5 font-semibold">{{ $item->location }}</dd></div>
                <div><dt class="text-gray-400">{{ __('Efficiency') }}</dt><dd class="mt-0.5 font-semibold text-warning-200">{{ $profile->efficiency ? number_format((float) $profile->efficiency, 0).'%' : '—' }}</dd></div>
                <div><dt class="text-gray-400">{{ __('Technology') }}</dt><dd class="mt-0.5 truncate font-semibold">{{ $profile->technology ? __($profile->technology) : '—' }}</dd></div>
            </dl>
            <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-white/15"><span class="block h-full w-11/12 rounded-full bg-linear-to-r from-brand-300 to-warning-300"></span></div>
        </div>
    </div>

    <div class="flex flex-1 flex-col p-5 sm:p-6">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="text-lg font-semibold tracking-tight text-gray-900 dark:text-white">{{ __($item->name) }}</h3>
                <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ $item->model }}</p>
            </div>
            <span class="shrink-0 text-theme-xs font-semibold uppercase tracking-wider text-brand-700 dark:text-brand-300">{{ __('Energy gear') }}</span>
        </div>

        <dl class="mt-4 grid grid-cols-2 gap-2 text-theme-sm sm:grid-cols-4">
            <div class="rounded-2xl border border-brand-100 bg-brand-50/80 px-3 py-2.5 dark:border-brand-500/20 dark:bg-brand-500/10">
                <dt class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Power') }}</dt>
                <dd class="font-medium text-gray-800 dark:text-white/90">{{ $profile->power_watts }} W</dd>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-gray-50 px-3 py-2.5 dark:border-gray-800 dark:bg-white/5">
                <dt class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Capacity') }}</dt>
                <dd class="font-medium text-gray-800 dark:text-white/90">{{ $profile->capacity_wh ? $profile->capacity_wh.' Wh' : '—' }}</dd>
            </div>
            <div class="rounded-2xl border border-warning-100 bg-warning-50/70 px-3 py-2.5 dark:border-warning-500/20 dark:bg-warning-500/10">
                <dt class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Efficiency') }}</dt>
                <dd class="font-medium text-gray-800 dark:text-white/90">{{ $profile->efficiency ? number_format((float) $profile->efficiency, 0).'%' : '—' }}</dd>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-gray-50 px-3 py-2.5 dark:border-gray-800 dark:bg-white/5">
                <dt class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Status') }}</dt>
                <dd class="truncate font-medium text-gray-800 dark:text-white/90">{{ $available ? __('Ready') : __(ucfirst($item->status)) }}</dd>
            </div>
        </dl>

        <div class="mt-5 flex items-center justify-between gap-3 border-t border-gray-200 pt-4 dark:border-gray-800">
            <p>
                <span class="text-xl font-semibold text-gray-900 dark:text-white">{{ number_format($item->price_per_day, 0) }} {{ __('TND') }}</span>
                <span class="text-theme-xs text-gray-500 dark:text-gray-400">/ {{ __('day') }}</span>
            </p>
            <a href="{{ $href }}" class="button-base border border-gray-300 px-3.5 py-2.5 text-theme-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                {{ __('View details') }}
                <svg class="size-4 rtl:rotate-180" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7.5 4.5 13 10l-5.5 5.5" /></svg>
            </a>
        </div>
    </div>
</article>
