@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        {{-- Breadcrumb: Dashboard / Rentals / Create Rental --}}
        <x-common.page-breadcrumb>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="text-gray-500 transition-colors hover:text-brand-600 dark:text-gray-400">Dashboard</a>
                <span class="text-gray-400">/</span>
                <a href="{{ route('admin.rentals.index') }}" class="text-gray-500 transition-colors hover:text-brand-600 dark:text-gray-400">Rentals</a>
                <span class="text-gray-400">/</span>
                <span class="text-gray-800 dark:text-white">Create Rental</span>
            </div>
        </x-common.page-breadcrumb>

        <div class="mx-auto max-w-4xl">
            <x-common.component-card :title="__('Create rental')" :desc="__('Record an equipment rental. The reference is generated automatically.')">
                {{-- The form itself lives in _form.blade.php (shared with the edit page) --}}
                @include('pages.admin.rentals._form', [
                    'action'      => route('admin.rentals.store'),
                    'method'      => 'POST',
                    'submitLabel' => __('Create rental'),
                    'rental'      => null,
                ])
            </x-common.component-card>
        </div>
    </div>
@endsection