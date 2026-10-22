@extends('layouts.front')

@section('content')
    <section class="bg-gray-950 text-white"><div class="mx-auto max-w-7xl px-4 py-9 sm:px-8 lg:px-10"><p class="text-theme-xs font-semibold uppercase tracking-[0.2em] text-warning-300">{{ __('My SolarShare') }}</p><h1 class="mt-2 text-title-sm font-semibold sm:text-title-md">{{ $title }}</h1><p class="mt-2 max-w-2xl text-theme-sm text-gray-300">{{ __('Keep the equipment you may want to rent close at hand.') }}</p></div></section>
    @include('pages.front.partials.buyer-nav')
    <main class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-8 lg:px-10">
        @if (session('status'))<div role="status" class="rounded-xl border border-success-200 bg-success-50 p-4 text-theme-sm text-success-800 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-200">{{ session('status') }}</div>@endif
        @if ($favorites->isEmpty())
            <section class="rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center dark:border-gray-700 dark:bg-white/[0.03]"><p class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Your favorites list is empty.') }}</p><p class="mt-2 text-theme-sm text-gray-500 dark:text-gray-400">{{ __('Save equipment from the catalogue to compare it later.') }}</p><a href="{{ route('front.catalog') }}" class="button-base button-primary mt-5 inline-flex min-h-10 px-4 text-theme-sm">{{ __('Browse catalogue') }}</a></section>
        @else
            <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($favorites as $equipment)
                    <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]"><div class="h-36 bg-linear-to-br from-brand-950 via-gray-900 to-gray-950 p-5 text-white"><p class="text-theme-xs uppercase tracking-[0.16em] text-warning-300">{{ $equipment->category?->name }}</p><h2 class="mt-2 text-lg font-semibold">{{ $equipment->name }}</h2></div><div class="space-y-3 p-5"><p class="text-theme-sm text-gray-600 dark:text-gray-300">{{ $equipment->location }}</p><div class="flex items-center justify-between"><span class="font-semibold text-gray-900 dark:text-white">{{ number_format((float) $equipment->price_per_day, 2) }} TND / {{ __('day') }}</span><span class="rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-semibold text-success-700 dark:bg-success-500/15 dark:text-success-300">{{ __(ucfirst($equipment->status)) }}</span></div><div class="flex gap-2"><a href="{{ route('front.equipment.show', $equipment) }}" class="button-base button-primary min-h-10 flex-1 text-theme-xs">{{ __('View details') }}</a><form method="POST" action="{{ route('front.equipment.favorite', $equipment) }}">@csrf<button type="submit" class="min-h-10 rounded-lg border border-gray-300 px-3 text-theme-xs font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200" aria-label="{{ __('Remove from favorites') }}">♥</button></form></div></div></article>
                @endforeach
            </section>
            <div>{{ $favorites->links() }}</div>
        @endif
    </main>
@endsection
