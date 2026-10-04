@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb>
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard') }}" class="text-gray-500 transition-colors hover:text-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-gray-400">Dashboard</a>
            <span class="text-gray-400">/</span>
            <a href="{{ route('admin.users.index') }}" class="text-gray-500 transition-colors hover:text-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-gray-400">User Management</a>
            <span class="text-gray-400">/</span>
            <span class="text-gray-800 dark:text-white">{{ __('User details') }}</span>
        </div>
    </x-common.page-breadcrumb>

    <div class="mx-auto max-w-4xl">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-1">
                <x-common.component-card>
                    <div class="flex flex-col items-center text-center p-4">
                        <x-ui.avatar :src="$user->profile_photo_url" class="w-24 h-24 mb-4" />
                        <h2 class="text-title-md font-bold text-gray-800 dark:text-white">{{ $user->name }}</h2>
                        <p class="text-sm text-gray-500 mb-4">{{ $user->email }}</p>

                        <x-ui.badge :color="$user->isAdmin() ? 'success' : 'gray'" class="mb-6">
                            {{ $user->role->label() }}
                        </x-ui.badge>

                        <div class="flex flex-col w-full gap-2">
                            <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex min-h-10 w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-center text-sm font-medium text-white shadow-theme-xs transition-colors hover:bg-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900 motion-reduce:transition-none">
                                {{ __('Edit profile') }}
                            </a>
                            <a href="{{ route('admin.users.index') }}" class="inline-flex min-h-10 w-full items-center justify-center rounded-lg px-4 py-2 text-center text-sm font-medium text-gray-600 transition-colors hover:bg-gray-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-gray-300 dark:hover:bg-gray-800 motion-reduce:transition-none">
                                {{ __('Back to list') }}
                            </a>
                        </div>
                    </div>
                </x-common.component-card>
            </div>

            <div class="md:col-span-2">
                <x-common.component-card>
                    <div class="mb-6">
                        <h2 class="text-title-md font-bold text-gray-800 dark:text-white">{{ __('Account information') }}</h2>
                    </div>

                    <div class="space-y-4">
                        <div class="flex flex-col gap-1 border-b py-3 sm:flex-row sm:items-center sm:justify-between dark:border-gray-700">
                            <span class="text-sm text-gray-500">{{ __('Full name') }}</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">{{ $user->name }}</span>
                        </div>
                        <div class="flex flex-col gap-1 border-b py-3 sm:flex-row sm:items-center sm:justify-between dark:border-gray-700">
                            <span class="text-sm text-gray-500">{{ __('Email address') }}</span>
                            <span class="break-all text-sm font-medium text-gray-800 dark:text-white">{{ $user->email }}</span>
                        </div>
                        <div class="flex flex-col gap-1 border-b py-3 sm:flex-row sm:items-center sm:justify-between dark:border-gray-700">
                            <span class="text-sm text-gray-500">{{ __('Phone number') }}</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">{{ $user->phone_number ?? __('Not provided') }}</span>
                        </div>
                        <div class="flex flex-col gap-1 border-b py-3 sm:flex-row sm:items-center sm:justify-between dark:border-gray-700">
                            <span class="text-sm text-gray-500">{{ __('Address') }}</span>
                            <span class="break-words text-sm font-medium text-gray-800 dark:text-white">{{ $user->address ?? __('Not provided') }}</span>
                        </div>
                        <div class="flex flex-col gap-1 border-b py-3 sm:flex-row sm:items-center sm:justify-between dark:border-gray-700">
                            <span class="text-sm text-gray-500">{{ __('Account role') }}</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">{{ $user->role->label() }}</span>
                        </div>
                        <div class="flex flex-col gap-1 border-b py-3 sm:flex-row sm:items-center sm:justify-between dark:border-gray-700">
                            <span class="text-sm text-gray-500">{{ __('Email verified') }}</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">{{ $user->hasVerifiedEmail() ? __('Yes') : __('No') }}</span>
                        </div>
                        <div class="flex flex-col gap-1 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <span class="text-sm text-gray-500">{{ __('Joined date') }}</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">{{ $user->created_at->locale(app()->getLocale())->translatedFormat('M d, Y') }}</span>
                        </div>
                    </div>
                </x-common.component-card>
            </div>
        </div>
    </div>
</div>
@endsection
