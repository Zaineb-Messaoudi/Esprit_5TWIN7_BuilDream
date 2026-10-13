@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        {{-- Breadcrumb: Dashboard / Rental Contracts / Edit Contract --}}
        <x-common.page-breadcrumb>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="text-gray-500 transition-colors hover:text-brand-600 dark:text-gray-400">Dashboard</a>
                <span class="text-gray-400">/</span>
                <a href="{{ route('admin.rental-contracts.index') }}" class="text-gray-500 transition-colors hover:text-brand-600 dark:text-gray-400">Rental Contracts</a>
                <span class="text-gray-400">/</span>
                <span class="text-gray-800 dark:text-white">Edit Contract</span>
            </div>
        </x-common.page-breadcrumb>

        <div class="mx-auto max-w-4xl">
            <x-common.component-card :title="__('Edit contract') . ' ' . $contract->contract_number" :desc="__('Update the contract details.')">
                {{-- Same shared form, filled with the current contract and sent with PUT --}}
                @include('pages.admin.rental-contracts._form', [
                    'action'           => route('admin.rental-contracts.update', $contract),
                    'method'           => 'PUT',
                    'submitLabel'      => __('Save changes'),
                    'contract'         => $contract,
                    'rentals'          => collect(),
                    'selectedRentalId' => null,
                ])
            </x-common.component-card>
        </div>
    </div>
@endsection