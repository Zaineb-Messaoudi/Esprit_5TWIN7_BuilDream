@props(['equipment'])

@php
    $qrUrl = route('technical.equipment.inspection-qr', $equipment);
@endphp

<section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]" aria-labelledby="inspection-qr-title">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
        <a href="{{ $qrUrl }}" target="_blank" rel="noopener" class="shrink-0 rounded-xl border border-gray-200 bg-white p-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:border-gray-700" aria-label="{{ __('Open inspection QR code') }}">
            <img src="{{ $qrUrl }}" alt="{{ __('Inspection QR code for :equipment', ['equipment' => $equipment->name]) }}" width="176" height="176" class="size-44">
        </a>
        <div>
            <h2 id="inspection-qr-title" class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Inspection QR code') }}</h2>
            <p class="mt-2 max-w-xl text-sm leading-6 text-gray-600 dark:text-gray-300">{{ __('Scan this code after signing in to open an inspection form with this equipment already selected.') }}</p>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('The signed link is valid for 30 days.') }}</p>
            <a href="{{ $qrUrl }}" target="_blank" rel="noopener" class="mt-4 inline-flex min-h-10 items-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
                {{ __('Open QR code') }}
            </a>
        </div>
    </div>
</section>
