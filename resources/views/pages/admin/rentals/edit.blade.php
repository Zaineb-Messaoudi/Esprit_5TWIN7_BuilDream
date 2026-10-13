@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        {{-- Breadcrumb: Dashboard / Rentals / Edit Rental --}}
        <x-common.page-breadcrumb>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="text-gray-500 transition-colors hover:text-brand-600 dark:text-gray-400">Dashboard</a>
                <span class="text-gray-400">/</span>
                <a href="{{ route('admin.rentals.index') }}" class="text-gray-500 transition-colors hover:text-brand-600 dark:text-gray-400">Rentals</a>
                <span class="text-gray-400">/</span>
                <span class="text-gray-800 dark:text-white">Edit Rental</span>
            </div>
        </x-common.page-breadcrumb>

        <div class="mx-auto max-w-4xl">
            <x-common.component-card :title="__('Edit rental') . ' ' . $rental->reference" :desc="__('Update the rental details.')">
                {{-- Same shared form, filled with the current rental ($rental) and sent with PUT --}}
                @include('pages.admin.rentals._form', [
                    'action'      => route('admin.rentals.update', $rental),
                    'method'      => 'PUT',
                    'submitLabel' => __('Save changes'),
                    'rental'      => $rental,
                ])
            </x-common.component-card>
        </div>
    </div>
@endsection