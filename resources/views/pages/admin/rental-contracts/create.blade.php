@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        {{-- Breadcrumb: Dashboard / Rental Contracts / Create Contract --}}
        <x-common.page-breadcrumb>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="text-gray-500 transition-colors hover:text-brand-600 dark:text-gray-400">Dashboard</a>
                <span class="text-gray-400">/</span>
                <a href="{{ route('admin.rental-contracts.index') }}" class="text-gray-500 transition-colors hover:text-brand-600 dark:text-gray-400">Rental Contracts</a>
                <span class="text-gray-400">/</span>
                <span class="text-gray-800 dark:text-white">Create Contract</span>
            </div>
        </x-common.page-breadcrumb>

        <div class="mx-auto max-w-4xl">
            <x-common.component-card :title="__('Create contract')" :desc="__('Create the contract of a rental. The contract number is generated automatically.')">
                {{-- The form itself lives in _form.blade.php (shared with the edit page) --}}
                @include('pages.admin.rental-contracts._form', [
                    'action'           => route('admin.rental-contracts.store'),
                    'method'           => 'POST',
                    'submitLabel'      => __('Create contract'),
                    'contract'         => null,
                    'rentals'          => $rentals,
                    'selectedRentalId' => $selectedRentalId,
                ])
            </x-common.component-card>
        </div>
    </div>
@endsection