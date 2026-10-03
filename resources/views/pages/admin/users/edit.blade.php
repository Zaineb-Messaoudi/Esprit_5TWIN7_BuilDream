@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6">
    <x-common.page-breadcrumb>
        <div class="flex items-center gap-2">
            <a href="/" class="text-gray-500 hover:text-brand">Dashboard</a>
            <span class="text-gray-400">/</span>
            <a href="{{ route('admin.users.index') }}" class="text-gray-500 hover:text-brand">User Management</a>
            <span class="text-gray-400">/</span>
            <span class="text-gray-800 dark:text-white">Edit User</span>
        </div>
    </x-common.page-breadcrumb>

    <div class="max-w-4xl mx-auto">
        <x-common.component-card>
            <div class="mb-6">
                <h2 class="text-title-md font-bold text-gray-800 dark:text-white">Edit User Profile</h2>
                <p class="text-sm text-gray-500">Update user information and account privileges.</p>
            </div>

            <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <x-form.input label="Full Name" name="name" value="{{ old('name', $user->name) }}" required />
                        @error('name') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <x-form.input label="Email Address" name="email" type="email" value="{{ old('email', $user->email) }}" required />
                        @error('email') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <x-form.input label="Phone Number" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}" />
                        @error('phone_number') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <x-form.select label="User Role" name="role">
                            <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>Regular User</option>
                            <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Administrator</option>
                        </x-form.select>
                        @error('role') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="space-y-2">
                    <x-form.input label="Address" name="address" type="textarea" value="{{ old('address', $user->address) }}" />
                    @error('address') <span class="text-error-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end gap-4 pt-6 border-t dark:border-gray-700">
                    <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-brand rounded-md hover:bg-brand-600 transition-colors">
                        Save Changes
                    </button>
                </div>
            </form>
        </x-common.component-card>
    </div>
</div>
@endsection
