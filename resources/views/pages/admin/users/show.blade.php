@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6">
    <x-common.page-breadcrumb>
        <div class="flex items-center gap-2">
            <a href="/" class="text-gray-500 hover:text-brand">Dashboard</a>
            <span class="text-gray-400">/</span>
            <a href="{{ route('admin.users.index') }}" class="text-gray-500 hover:text-brand">User Management</a>
            <span class="text-gray-400">/</span>
            <span class="text-gray-800 dark:text-white">User Details</span>
        </div>
    </x-common.page-breadcrumb>

    <div class="max-w-4xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-1">
                <x-common.component-card>
                    <div class="flex flex-col items-center text-center p-4">
                        <x-ui.avatar :src="$user->profile_photo_url" class="w-24 h-24 mb-4" />
                        <h2 class="text-title-md font-bold text-gray-800 dark:text-white">{{ $user->name }}</h2>
                        <p class="text-sm text-gray-500 mb-4">{{ $user->email }}</p>

                        <x-ui.badge :color="$user->role === 'admin' ? 'success' : 'gray'" class="mb-6">
                            {{ ucfirst($user->role) }}
                        </x-ui.badge>

                        <div class="flex flex-col w-full gap-2">
                            <a href="{{ route('admin.users.edit', $user) }}" class="w-full text-center bg-brand text-white px-4 py-2 rounded-md hover:bg-brand-600 transition-colors text-sm font-medium">
                                Edit Profile
                            </a>
                            <a href="{{ route('admin.users.index') }}" class="w-full text-center text-gray-500 px-4 py-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors text-sm font-medium">
                                Back to List
                            </a>
                        </div>
                    </div>
                </x-common.component-card>
            </div>

            <div class="md:col-span-2">
                <x-common.component-card>
                    <div class="mb-6">
                        <h2 class="text-title-md font-bold text-gray-800 dark:text-white">Account Information</h2>
                    </div>

                    <div class="space-y-4">
                        <div class="flex justify-between py-3 border-b dark:border-gray-700">
                            <span class="text-sm text-gray-500">Full Name</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">{{ $user->name }}</span>
                        </div>
                        <div class="flex justify-between py-3 border-b dark:border-gray-700">
                            <span class="text-sm text-gray-500">Email Address</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">{{ $user->email }}</span>
                        </div>
                        <div class="flex justify-between py-3 border-b dark:border-gray-700">
                            <span class="text-sm text-gray-500">Phone Number</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">{{ $user->phone_number ?? 'Not provided' }}</span>
                        </div>
                        <div class="flex justify-between py-3 border-b dark:border-gray-700">
                            <span class="text-sm text-gray-500">Address</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">{{ $user->address ?? 'Not provided' }}</span>
                        </div>
                        <div class="flex justify-between py-3 border-b dark:border-gray-700">
                            <span class="text-sm text-gray-500">Account Role</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">{{ ucfirst($user->role) }}</span>
                        </div>
                        <div class="flex justify-between py-3 border-b dark:border-gray-700">
                            <span class="text-sm text-gray-500">Email Verified</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">{{ $user->hasVerifiedEmail() ? 'Yes' : 'No' }}</span>
                        </div>
                        <div class="flex justify-between py-3">
                            <span class="text-sm text-gray-500">Joined Date</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">{{ $user->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                </x-common.component-card>
            </div>
        </div>
    </div>
</div>
@endsection
