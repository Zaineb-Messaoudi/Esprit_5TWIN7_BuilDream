@extends('layouts.front')

@php
    $equipment = $record->equipment;
    $reservation = $recordType === 'reservation' ? $record : $record->reservation;
@endphp

@section('content')
    <section class="bg-gray-950 text-white"><div class="mx-auto max-w-7xl px-4 py-9 sm:px-8 lg:px-10"><p class="text-theme-xs font-semibold uppercase tracking-[0.2em] text-warning-300">{{ $owner ? __('Owner workspace') : __('Renter workspace') }}</p><h1 class="mt-2 text-title-sm font-semibold sm:text-title-md">{{ $title }}</h1></div></section>
    @if ($owner) @include('pages.front.partials.owner-nav') @else @include('pages.front.partials.buyer-nav') @endif
    <main class="mx-auto max-w-4xl space-y-5 px-4 py-8 sm:px-8 lg:px-10">
        @if (session('status'))<div role="status" class="rounded-xl border border-success-200 bg-success-50 p-4 text-theme-sm text-success-800 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-200">{{ session('status') }}</div>@endif
        @if ($recordType === 'reservation')
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex flex-wrap items-start justify-between gap-4"><div><p class="text-theme-xs uppercase tracking-wide text-gray-500">{{ __('Reservation reference') }}</p><h2 class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ $record->reference }}</h2></div><span class="rounded-full bg-warning-50 px-3 py-1 text-theme-xs font-semibold text-warning-800 dark:bg-warning-500/15 dark:text-warning-300">{{ __(ucfirst($record->status)) }}</span></div>
                <dl class="mt-6 grid gap-4 sm:grid-cols-2">@foreach ([[__('Equipment'), $equipment?->name], [$owner ? __('Renter') : __('Owner'), $owner ? $record->user?->name : $equipment?->owner?->name], [__('Start date'), $record->start_date->format('M j, Y')], [__('End date'), $record->end_date->format('M j, Y')], [__('Subtotal'), number_format((float) $record->total_amount, 2).' TND'], [__('Total with tax'), number_format((float) $record->total_amount * 1.19, 2).' TND']] as [$label, $value])<div class="rounded-xl bg-gray-50 p-4 dark:bg-white/5"><dt class="text-theme-xs text-gray-500">{{ $label }}</dt><dd class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $value }}</dd></div>@endforeach</dl>
                @if ($owner && $record->status === 'pending')<div class="mt-6 flex gap-3"><form method="POST" action="{{ route('owner.reservations.decision', $record) }}">@csrf<input type="hidden" name="decision" value="approve"><button class="button-base button-primary min-h-10 px-4 text-theme-xs">{{ __('Approve request') }}</button></form><form method="POST" action="{{ route('owner.reservations.decision', $record) }}">@csrf<input type="hidden" name="decision" value="reject"><button class="min-h-10 rounded-lg border border-error-300 px-4 text-theme-xs font-semibold text-error-700 dark:border-error-500/40 dark:text-error-300">{{ __('Decline') }}</button></form></div>@endif
                @if (! $owner && $record->status === 'confirmed')<a href="{{ route('front.booking-summary', ['equipment' => $equipment->id, 'reservation' => $record->id]) }}" class="mt-6 inline-flex button-base button-primary min-h-10 items-center px-4 text-theme-xs">{{ __('Continue reservation') }}</a>@endif
            </section>
        @else
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]"><div class="flex flex-wrap items-start justify-between gap-4"><div><p class="text-theme-xs uppercase tracking-wide text-gray-500">{{ __('Rental reference') }}</p><h2 class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ $record->reference }}</h2></div><span class="rounded-full bg-brand-50 px-3 py-1 text-theme-xs font-semibold text-brand-800 dark:bg-brand-500/15 dark:text-brand-300">{{ __($record->status->label()) }}</span></div>
                <dl class="mt-6 grid gap-4 sm:grid-cols-2">@foreach ([[__('Equipment'), $equipment?->name], [__('Renter'), $record->user?->name], [__('Start date'), $record->start_date->format('M j, Y')], [__('End date'), $record->end_date->format('M j, Y')], [__('Total'), number_format((float) $record->total_amount, 2).' TND'], [__('Contract'), $record->contract?->contract_number ?? __('Pending')]] as [$label, $value])<div class="rounded-xl bg-gray-50 p-4 dark:bg-white/5"><dt class="text-theme-xs text-gray-500">{{ $label }}</dt><dd class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $value }}</dd></div>@endforeach</dl>
                @if ($record->contract)
                <div class="mt-5 rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ __('Rental terms') }}</h3>
                    <p class="mt-2 text-theme-sm text-gray-600 dark:text-gray-300">{{ $record->contract->terms }}</p>
                    @if (! $owner && $record->contract->contract_status->value === 'draft')
                    <form method="POST" action="{{ route('buyer.contracts.sign', $record->contract) }}" class="mt-4">
                        @csrf
                        <button class="button-base button-primary min-h-10 px-4 text-theme-xs">{{ __('Sign contract') }}</button>
                    </form>
                    @endif
                    <div class="mt-4 flex gap-3">
                        <a href="{{ route('front.rental-contract.export-pdf', $record) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            {{ __('Download contract (PDF)') }}
                        </a>
                        @if ($owner)
                        <a href="{{ route('front.owner-rental-contract.export-pdf', $record) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                            {{ __('Owner copy') }}
                        </a>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Deposit Management (Owner only) --}}
                @if ($owner && $record->contract && $record->contract->deposit_amount > 0)
                <div class="mt-5 rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ __('Deposit') }}</h3>
                    <dl class="mt-3 grid gap-3 sm:grid-cols-3 text-theme-sm">
                        <div>
                            <dt class="text-gray-500">{{ __('Amount') }}</dt>
                            <dd class="font-semibold text-gray-900 dark:text-white">{{ number_format($record->contract->deposit_amount, 2) }} TND</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">{{ __('Status') }}</dt>
                            <dd>
                                <x-ui.badge :color="$record->contract->deposit_status->color()">{{ $record->contract->deposit_status->label() }}</x-ui.badge>
                            </dd>
                        </div>
                        <div class="sm:col-span-3">
                            <dt class="text-gray-500">{{ __('Notes') }}</dt>
                            <dd class="text-gray-700 dark:text-gray-300">{{ $record->contract->deposit_notes ?: '—' }}</dd>
                        </div>
                    </dl>
                    <div class="mt-4 flex flex-wrap gap-3">
                        @if ($record->contract->canHoldDeposit())
                            <form method="POST" action="{{ route('owner.rental-contracts.deposit.hold', $record->contract) }}" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    {{ __('Hold deposit') }}
                                </button>
                            </form>
                        @endif
                        @if ($record->contract->canReleaseDeposit())
                            <form method="POST" action="{{ route('owner.rental-contracts.deposit.release', $record->contract) }}" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-success-500 px-4 py-2 text-sm font-medium text-white hover:bg-success-600">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ __('Release deposit') }}
                                </button>
                            </form>
                        @endif
                        @if ($record->contract->canForfeitDeposit())
                            <form method="POST" action="{{ route('owner.rental-contracts.deposit.forfeit', $record->contract) }}" class="inline" onsubmit="return confirm('{{ __('Forfeit the deposit? This action cannot be undone.') }}')">
                                @csrf
                                <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-error-500 px-4 py-2 text-sm font-medium text-white hover:bg-error-600">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ __('Forfeit deposit') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                @endif

                @if ($owner && in_array($record->status->value, ['active', 'completed']) && ! $record->inspections->first())
                <div class="mt-5">
                    <a href="{{ route('rental.return.form', $record) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-warning-500 px-4 py-2 text-sm font-medium text-white hover:bg-warning-600">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        {{ __('Return equipment') }}
                    </a>
                </div>
                @endif
                @if ($reservation?->invoice)<a href="{{ route('front.invoice', ['equipment' => $equipment->id, 'reservation' => $reservation->id]) }}" class="mt-5 inline-flex button-base button-secondary min-h-10 items-center px-4 text-theme-xs">{{ __('View invoice') }}</a>@endif

                {{-- Request Extension (Buyer only) --}}
                @if (! $owner && $record->status->value === 'active')
                <div class="mt-5">
                    <a href="{{ route('rental.extensions.create', $record) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-warning-500 px-4 py-2 text-sm font-medium text-white hover:bg-warning-600">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        {{ __('Request extension') }}
                    </a>
                </div>
                @endif

                {{-- Extension Requests (Owner only) --}}
                @if ($owner && $record->extensions->where('status', 'pending')->first())
                <div class="mt-5">
                    @foreach ($record->extensions->where('status', 'pending') as $extension)
                    <div class="mt-4 rounded-xl border border-warning-200 bg-warning-50 p-4 dark:border-warning-500/30 dark:bg-warning-500/10">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-theme-xs font-semibold text-warning-800 dark:text-warning-300">{{ __('Extension requested') }}</p>
                                <p class="mt-1 text-theme-sm text-gray-700 dark:text-gray-300">
                                    {{ __('Extend to') }} {{ $extension->new_end_date->format('M j, Y') }}
                                    ({{ __('+') }}{{ $extension->old_end_date->diffInDays($extension->new_end_date) }} {{ __('days') }},
                                    {{ number_format($extension->additional_amount, 2) }} TND)
                                </p>
                                @if ($extension->reason)
                                <p class="mt-1 text-theme-xs text-gray-500">{{ $extension->reason }}</p>
                                @endif
                            </div>
                            <div class="flex gap-3">
                                <form method="POST" action="{{ route('rental.extensions.approve', $extension) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-success-500 px-4 py-2 text-sm font-medium text-white hover:bg-success-600">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ __('Approve') }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('rental.extensions.reject', $extension) }}" class="inline" onsubmit="return confirm('{{ __('Reject this extension request?') }}')">
                                    @csrf
                                    <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-error-500 px-4 py-2 text-sm font-medium text-white hover:bg-error-600">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ __('Reject') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </section>
        @endif
    </main>
@endsection