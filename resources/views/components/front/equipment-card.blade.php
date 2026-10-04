@props(['item', 'href' => null])

@php
    $available = $item->status === 'available';
    $profile = $item->energyProfile;
    $href ??= route('front.equipment.show', $item->id);
@endphp

<article class="group flex flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-theme-xs transition duration-300 hover:-translate-y-1 hover:border-brand-300 hover:shadow-theme-lg motion-reduce:transition-none motion-reduce:hover:translate-y-0 dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="relative aspect-4/3 overflow-hidden bg-linear-to-br from-brand-50 to-warning-50 dark:from-brand-950 dark:to-gray-800">
        <img src="{{ asset($item->image) }}" alt="{{ $item->name }}" width="800" height="600" loading="lazy" class="size-full object-cover transition duration-300 motion-reduce:transition-none group-hover:scale-105" />
        <div class="pointer-events-none absolute inset-0 bg-linear-to-t from-gray-950/25 via-transparent to-transparent" aria-hidden="true"></div>
        <span title="{{ $item->category->name }}" class="absolute start-3 top-3 max-w-[48%] truncate rounded-full bg-white/90 px-2.5 py-1 text-theme-xs font-medium text-gray-700 shadow-theme-xs backdrop-blur dark:bg-gray-900/80 dark:text-gray-200">
            {{ $item->category->name }}
        </span>
        <span @class([
            'absolute end-3 top-3 max-w-[48%] truncate rounded-full px-2.5 py-1 text-theme-xs font-medium',
            'bg-success-50 text-success-700 dark:bg-success-500/20 dark:text-success-400' => $available,
            'bg-warning-50 text-warning-700 dark:bg-warning-500/20 dark:text-warning-400' => ! $available,
        ])>
            {{ $available ? __('Example listing') : __('Maintenance example') }}
        </span>
    </div>

    <div class="flex flex-1 flex-col p-5">
        <h3 class="text-lg font-semibold tracking-tight text-gray-900 dark:text-white">{{ $item->name }}</h3>
        <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">{{ $item->brand }} {{ $item->model }} &middot; {{ $item->location }}</p>

        <dl class="mt-4 grid grid-cols-2 gap-3 text-theme-sm">
            <div class="rounded-xl bg-brand-50/80 px-3 py-2.5 dark:bg-brand-500/10">
                <dt class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Power') }}</dt>
                <dd class="font-medium text-gray-800 dark:text-white/90">{{ $profile->power_watts }} W</dd>
            </div>
            <div class="rounded-xl bg-gray-50 px-3 py-2.5 dark:bg-white/5">
                <dt class="text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Capacity') }}</dt>
                <dd class="font-medium text-gray-800 dark:text-white/90">{{ $profile->capacity_wh ? $profile->capacity_wh.' Wh' : '—' }}</dd>
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
