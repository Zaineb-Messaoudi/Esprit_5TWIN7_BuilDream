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
                    <dt class="text-gray-500 dark:text-gray-400">Status</dt>
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
                    <dd class="mt-1 font-medium text-gray-800 dark:text-white">{{ number_format($contract->deposit_amount, 2) }}</dd>
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

        {{-- Terms (nl2br + e() keeps the line breaks and escapes any HTML for safety) --}}
        <x-common.component-card :title="__('Terms and conditions')">
            <p class="text-sm leading-6 text-gray-700 dark:text-gray-300">{!! nl2br(e($contract->terms)) !!}</p>
        </x-common.component-card>
    </div>
@endsection