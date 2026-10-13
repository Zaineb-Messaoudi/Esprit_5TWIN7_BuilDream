@extends('layouts.front')

@section('content')
<section class="relative isolate overflow-hidden bg-gray-950 text-white">
    <div class="pointer-events-none absolute inset-0 -z-10 bg-linear-to-br from-gray-950 via-brand-950 to-gray-900"></div>
    <div class="mx-auto max-w-7xl px-6 py-12 sm:px-8 sm:py-16 lg:px-10">
        <p class="text-theme-xs font-semibold uppercase tracking-[0.2em] text-warning-300">{{ __('SolarShare / live map') }}</p>
        <h1 class="mt-4 max-w-3xl text-title-lg font-semibold tracking-tight sm:text-title-xl">{{ __('Find useful energy equipment near you.') }}</h1>
        <p class="mt-4 max-w-2xl text-lg leading-7 text-gray-300">{{ __('Browse available listings by location, then open the listing to plan your rental.') }}</p>
    </div>
</section>

<main class="mx-auto grid w-full max-w-7xl gap-6 px-6 py-8 sm:px-8 sm:py-12 lg:grid-cols-[minmax(0,1fr)_21rem] lg:px-10">
    <section class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03]">
        <div id="equipment-map" class="min-h-[34rem] w-full"></div>
    </section>
    <aside class="rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
        <div class="flex items-center justify-between gap-3">
            <div><p class="text-theme-xs font-semibold uppercase tracking-[0.16em] text-brand-700 dark:text-brand-300">{{ __('Available now') }}</p><h2 class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ $equipment->count() }} {{ __('listings') }}</h2></div>
            <span class="grid size-10 place-items-center rounded-2xl bg-warning-100 text-xl dark:bg-warning-500/15">⌖</span>
        </div>
        <div class="mt-6 space-y-3">
            @forelse($equipment as $item)
                <a href="{{ $item['url'] }}" class="block rounded-2xl border border-gray-200 p-4 transition hover:border-brand-400 hover:bg-brand-50/60 dark:border-gray-800 dark:hover:border-brand-400 dark:hover:bg-brand-500/10" data-equipment-card="{{ $item['id'] }}">
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $item['name'] }}</p>
                    <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ $item['location'] }} · {{ $item['price'] }}</p>
                </a>
            @empty
                <p class="text-theme-sm text-gray-500">{{ __('No available equipment has a mapped location yet.') }}</p>
            @endforelse
        </div>
    </aside>
</main>
@endsection

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>#equipment-map { min-height: 34rem; } .leaflet-container { font-family: inherit; z-index: 0; }</style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (!window.L || !document.getElementById('equipment-map')) return;
            const items = @json($equipment);
            const map = L.map('equipment-map').setView([36.4, 10.2], 7);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
            const bounds = [];
            items.forEach((item) => {
                const point = [item.latitude, item.longitude]; bounds.push(point);
                L.marker(point).addTo(map).bindPopup(`<strong>${item.name}</strong><br>${item.location}<br>${item.price}<br><a href="${item.url}">View listing →</a>`);
            });
            if (bounds.length) map.fitBounds(bounds, { padding: [28, 28], maxZoom: 12 });
            document.querySelectorAll('[data-equipment-card]').forEach((card) => card.addEventListener('mouseenter', () => {
                const item = items.find((entry) => String(entry.id) === card.dataset.equipmentCard);
                if (item) map.setView([item.latitude, item.longitude], 12, { animate: true });
            }));
        });
    </script>
@endpush
