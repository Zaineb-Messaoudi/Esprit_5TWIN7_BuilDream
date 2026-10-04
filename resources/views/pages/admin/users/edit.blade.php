@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb>
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard') }}" class="text-gray-500 transition-colors hover:text-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-gray-400">Dashboard</a>
            <span class="text-gray-400">/</span>
            <a href="{{ route('admin.users.index') }}" class="text-gray-500 transition-colors hover:text-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-gray-400">User Management</a>
            <span class="text-gray-400">/</span>
            <span class="text-gray-800 dark:text-white">{{ __('Edit User') }}</span>
        </div>
    </x-common.page-breadcrumb>

    <div class="mx-auto max-w-4xl">
        <x-common.component-card>
            <div class="mb-6">
                <h2 class="text-title-md font-bold text-gray-800 dark:text-white">{{ __('Edit User Profile') }}</h2>
                <p class="text-sm text-gray-500">{{ __('Update user information and account privileges.') }}</p>
            </div>

            <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <x-form.input :label="__('Full name')" name="name" value="{{ old('name', $user->name) }}" required />
                        @error('name') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <x-form.input :label="__('Email address')" name="email" type="email" value="{{ old('email', $user->email) }}" required />
                        @error('email') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <x-form.input :label="__('Phone number')" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}" />
                        @error('phone_number') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <x-form.select
                            :label="__('Account role')"
                            name="role"
                            :selected="old('role', $user->role->value)"
                            :options="['buyer' => __('Buyer'), 'owner' => __('Equipment Owner'), 'user' => __('Legacy buyer'), 'admin' => __('Administrator')]"
                            required
                        />
                        @error('role') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="address" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Address') }}</label>
                    <textarea id="address" name="address" rows="3" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-400 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white">{{ old('address', $user->address) }}</textarea>
                    @error('address') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end gap-4 pt-6 border-t dark:border-gray-700">
                    <a href="{{ route('admin.users.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 motion-reduce:transition-none">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900 motion-reduce:transition-none">
                        {{ __('Save changes') }}
                    </button>
                </div>
            </form>
        </x-common.component-card>
    </div>
</div>
@endsection
