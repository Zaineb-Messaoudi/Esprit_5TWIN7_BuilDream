@extends('layouts.front')

@section('content')
    <section class="bg-gray-950 text-white"><div class="mx-auto max-w-7xl px-4 py-9 sm:px-8 lg:px-10"><p class="text-theme-xs font-semibold uppercase tracking-[0.2em] text-warning-300">{{ $owner ? __('Owner workspace') : __('Renter workspace') }}</p><h1 class="mt-2 text-title-sm font-semibold sm:text-title-md">{{ $title }}</h1></div></section>
    @if ($owner) @include('pages.front.partials.owner-nav') @else @include('pages.front.partials.buyer-nav') @endif
    <main class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-8 lg:px-10">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @if ($owner)
                @php($cards = [[__('Equipment listings'), $equipmentCount], [__('Pending reservation requests'), $pendingReservations], [__('Active rentals'), $activeRentals], [__('Verified payments'), number_format((float) $revenue, 2).' TND']])
            @else
                @php($cards = [[__('Reservations'), $reservationCount], [__('Awaiting owner review'), $pendingReservations], [__('Active rentals'), $activeRentals], [__('Verified payments'), number_format((float) $revenue, 2).' TND']])
            @endif
            @foreach ($cards as [$label, $value])
                <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]"><p class="text-theme-xs text-gray-500 dark:text-gray-400">{{ $label }}</p><p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">{{ $value }}</p></article>
            @endforeach
        </section>

        <section class="overflow-x-auto rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 p-5 dark:border-gray-800"><div><h2 class="font-semibold text-gray-900 dark:text-white">{{ __('Recent reservations') }}</h2><p class="mt-1 text-theme-xs text-gray-500">{{ __('Live reservation activity for your account.') }}</p></div><a href="{{ route('front.my-reservations') }}" class="button-base button-secondary min-h-10 px-4 text-theme-xs">{{ __('View all') }}</a></div>
            <table class="w-full min-w-[640px] text-start text-theme-sm"><thead class="bg-gray-50 text-theme-xs text-gray-500 dark:bg-white/5"><tr><th class="p-4 text-start">{{ __('Reference') }}</th><th class="p-4 text-start">{{ __('Equipment') }}</th><th class="p-4 text-start">{{ $owner ? __('Renter') : __('Dates') }}</th><th class="p-4 text-start">{{ __('Status') }}</th></tr></thead><tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($reservations as $reservation)<tr><td class="p-4 font-semibold">{{ $reservation->reference }}</td><td class="p-4">{{ $reservation->equipment?->name ?? __('Deleted equipment') }}</td><td class="p-4">{{ $owner ? $reservation->user?->name : $reservation->start_date->format('M j').' – '.$reservation->end_date->format('M j, Y') }}</td><td class="p-4">{{ __(ucfirst($reservation->status)) }}</td></tr>
                @empty<tr><td colspan="4" class="p-8 text-center text-gray-500">{{ __('No reservation activity yet.') }}</td></tr>@endforelse
            </tbody></table>
        </section>
        @if (! $owner)<a href="{{ route('front.catalog') }}" class="button-base button-primary inline-flex min-h-11 items-center px-5 text-theme-sm">{{ __('Browse equipment') }}</a>@else<a href="{{ route('front.my-equipment') }}" class="button-base button-primary inline-flex min-h-11 items-center px-5 text-theme-sm">{{ __('Manage equipment') }}</a>@endif
    </main>
@endsection
