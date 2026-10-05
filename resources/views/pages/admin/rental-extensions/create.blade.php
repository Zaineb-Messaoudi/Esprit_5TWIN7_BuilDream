@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        {{-- Breadcrumb: Dashboard / Rental Extensions / New Request --}}
        <x-common.page-breadcrumb>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="text-gray-500 transition-colors hover:text-brand-600 dark:text-gray-400">Dashboard</a>
                <span class="text-gray-400">/</span>
                <a href="{{ route('admin.rental-extensions.index') }}" class="text-gray-500 transition-colors hover:text-brand-600 dark:text-gray-400">Rental Extensions</a>
                <span class="text-gray-400">/</span>
                <span class="text-gray-800 dark:text-white">New Request</span>
            </div>
        </x-common.page-breadcrumb>

        <div class="mx-auto max-w-4xl">
            <x-common.component-card :title="__('New extension request')" :desc="__('Ask for more time on a rental. The request starts as pending until it is approved or rejected.')">
                {{-- The form itself lives in _form.blade.php (shared with the edit page) --}}
                @include('pages.admin.rental-extensions._form', [
                    'action'           => route('admin.rental-extensions.store'),
                    'method'           => 'POST',
                    'submitLabel'      => __('Create request'),
                    'extension'        => null,
                    'rentals'          => $rentals,
                    'selectedRentalId' => $selectedRentalId,
                ])
            </x-common.component-card>
        </div>
    </div>
@endsection