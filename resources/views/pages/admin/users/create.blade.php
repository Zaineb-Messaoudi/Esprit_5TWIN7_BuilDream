@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="text-gray-500 transition-colors hover:text-brand-600 dark:text-gray-400">{{ __('Dashboard') }}</a>
                <span class="text-gray-400">/</span>
                <a href="{{ route('admin.users.index') }}" class="text-gray-500 transition-colors hover:text-brand-600 dark:text-gray-400">{{ __('User Management') }}</a>
                <span class="text-gray-400">/</span>
                <span class="text-gray-800 dark:text-white">{{ __('Create user') }}</span>
            </div>
        </x-common.page-breadcrumb>

        <div class="mx-auto max-w-4xl">
            <x-common.component-card :title="__('Create user')" :desc="__('Create an account and assign its workspace role.')">
                <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <x-form.input :label="__('Full name')" name="name" :value="old('name')" required autocomplete="name" />
                            @error('name')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <x-form.input :label="__('Email address')" name="email" type="email" :value="old('email')" required autocomplete="email" />
                            @error('email')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <x-form.input :label="__('Phone number')" name="phone_number" type="tel" :value="old('phone_number')" autocomplete="tel" />
                            @error('phone_number')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="role" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Account role') }}</label>
                            <select id="role" name="role" required class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 focus:border-brand-400 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                <option value="buyer" @selected(old('role', 'buyer') === 'buyer')>{{ __('Buyer') }}</option>
                                <option value="owner" @selected(old('role') === 'owner')>{{ __('Equipment Owner') }}</option>
                                <option value="user" @selected(old('role') === 'user')>{{ __('Legacy buyer') }}</option>
                                <option value="admin" @selected(old('role') === 'admin')>{{ __('Administrator') }}</option>
                            </select>
                            @error('role')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="address" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Address') }}</label>
                            <textarea id="address" name="address" rows="3" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-400 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white">{{ old('address') }}</textarea>
                            @error('address')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <x-form.input :label="__('Temporary password')" name="password" type="password" required autocomplete="new-password" />
                            @error('password')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <x-form.input :label="__('Confirm password')" name="password_confirmation" type="password" required autocomplete="new-password" />
                        </div>
                    </div>
                    <p class="rounded-lg bg-brand-50 px-4 py-3 text-xs leading-5 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">{{ __('The new user will need to verify their email address before accessing verified workspace pages.') }}</p>
                    <div class="flex flex-wrap justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                        <a href="{{ route('admin.users.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Cancel') }}</a>
                        <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">{{ __('Create user') }}</button>
                    </div>
                </form>
            </x-common.component-card>
        </div>
    </div>
@endsection
