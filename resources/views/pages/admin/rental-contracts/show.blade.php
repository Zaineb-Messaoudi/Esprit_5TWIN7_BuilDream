@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        {{-- Breadcrumb: Dashboard / Rental Contracts / contract number --}}
        <x-common.page-breadcrumb>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="text-gray-500 transition-colors hover:text-brand-600 dark:text-gray-400">Dashboard</a>
                <span class="text-gray-400">/</span>
                <a href="{{ route('admin.rental-contracts.index') }}" class="text-gray-500 transition-colors hover:text-brand-600 dark:text-gray-400">Rental Contracts</a>
                <span class="text-gray-400">/</span>
                <span class="text-gray-800 dark:text-white">{{ $contract->contract_number }}</span>
            </div>
        </x-common.page-breadcrumb>

        {{-- Title + actions --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-title-md font-semibold text-gray-800 dark:text-white/90">Contract {{ $contract->contract_number }}</h1>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.rental-contracts.export-pdf', $contract) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    PDF
                </a>
                <a href="{{ route('admin.rental-contracts.edit', $contract) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Edit</a>
                <a href="{{ route('admin.rental-contracts.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Back to list</a>
            </div>
        </div>

        {{-- Contract details --}}
        <x-common.component-card :title="__('Contract details')">
            <dl class="grid grid-cols-1 gap-5 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Rental</dt>
                    <dd class="mt-1 font-medium text-gray-800 dark:text-white">
                        @if ($contract->rental)
                            <a href="{{ route('admin.rentals.show', $contract->rental) }}" class="text-brand-600 underline-offset-4 hover:underline dark:text-brand-400">{{ $contract->rental->reference }}</a>
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Renter</dt>
                    <dd class="mt-1 font-medium text-gray-800 dark:text-white">{{ $contract->rental?->user?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Contract status</dt>
                    <dd class="mt-1">
                        <x-ui.badge :color="$contract->contract_status->color()">{{ $contract->contract_status->label() }}</x-ui.badge>
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Signed at</dt>
                    <dd class="mt-1 font-medium text-gray-800 dark:text-white">{{ $contract->signed_at?->format('d/m/Y H:i') ?? 'Not signed yet' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Deposit amount</dt>
                    <dd class="mt-1 font-medium text-gray-800 dark:text-white">{{ number_format($contract->deposit_amount, 2) }} TND</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Deposit status</dt>
                    <dd class="mt-1">
                        <x-ui.badge :color="$contract->deposit_status->color()">{{ $contract->deposit_status->label() }}</x-ui.badge>
                        @if ($contract->deposit_held_at)
                            <p class="mt-1 text-theme-xs text-gray-500">Held: {{ $contract->deposit_held_at->format('d/m/Y H:i') }}</p>
                        @endif
                        @if ($contract->deposit_released_at)
                            <p class="mt-1 text-theme-xs text-gray-500">Released: {{ $contract->deposit_released_at->format('d/m/Y H:i') }}</p>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Rental period</dt>
                    <dd class="mt-1 font-medium text-gray-800 dark:text-white">
                        @if ($contract->rental)
                            {{ $contract->rental->start_date->format('d/m/Y') }} → {{ $contract->rental->end_date->format('d/m/Y') }}
                        @else
                            —
                        @endif
                    </dd>
                </div>
            </dl>
        </x-common.component-card>

        {{-- Deposit Actions --}}
        @if ($contract->deposit_amount > 0)
        <x-common.component-card :title="__('Deposit management')">
            <div class="flex flex-wrap gap-3">
                @if ($contract->canHoldDeposit())
                    <form method="POST" action="{{ route('admin.rental-contracts.deposit.hold', $contract) }}" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            {{ __('Hold deposit') }}
                        </button>
                    </form>
                @endif
                @if ($contract->canReleaseDeposit())
                    <form method="POST" action="{{ route('admin.rental-contracts.deposit.release', $contract) }}" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-success-500 px-4 py-2 text-sm font-medium text-white hover:bg-success-600">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ __('Release deposit') }}
                        </button>
                    </form>
                @endif
                @if ($contract->canForfeitDeposit())
                    <form method="POST" action="{{ route('admin.rental-contracts.deposit.forfeit', $contract) }}" class="inline" onsubmit="return confirm('{{ __('Forfeit the deposit? This action cannot be undone.') }}')">
                        @csrf
                        <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-error-500 px-4 py-2 text-sm font-medium text-white hover:bg-error-600">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ __('Forfeit deposit') }}
                        </button>
                    </form>
                @endif
                @if ($contract->deposit_notes)
                    <div class="mt-3 text-theme-xs text-gray-500">
                        <strong>Notes:</strong> {{ $contract->deposit_notes }}
                    </div>
                @endif
            </div>
        </x-common.component-card>
        @endif

        {{-- Terms (nl2br + e() keeps the line breaks and escapes any HTML for safety) --}}
        <x-common.component-card :title="__('Terms and conditions')">
            <p class="text-sm leading-6 text-gray-700 dark:text-gray-300">{!! nl2br(e($contract->terms)) !!}</p>
        </x-common.component-card>
    </div>
@endsection