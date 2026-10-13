@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        {{-- Breadcrumb: Dashboard / Rental Extensions / Edit Request --}}
        <x-common.page-breadcrumb>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="text-gray-500 transition-colors hover:text-brand-600 dark:text-gray-400">Dashboard</a>
                <span class="text-gray-400">/</span>
                <a href="{{ route('admin.rental-extensions.index') }}" class="text-gray-500 transition-colors hover:text-brand-600 dark:text-gray-400">Rental Extensions</a>
                <span class="text-gray-400">/</span>
                <span class="text-gray-800 dark:text-white">Edit Request</span>
            </div>
        </x-common.page-breadcrumb>

        <div class="mx-auto max-w-4xl">
            <x-common.component-card :title="__('Edit extension request')" :desc="__('Only pending requests can be edited.')">
                {{-- Same shared form, filled with the current request and sent with PUT --}}
                @include('pages.admin.rental-extensions._form', [
                    'action'           => route('admin.rental-extensions.update', $extension),
                    'method'           => 'PUT',
                    'submitLabel'      => __('Save changes'),
                    'extension'        => $extension,
                    'rentals'          => collect(),
                    'selectedRentalId' => null,
                ])
            </x-common.component-card>
        </div>
    </div>
@endsection