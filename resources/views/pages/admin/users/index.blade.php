@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb>
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard') }}" class="text-gray-500 transition-colors hover:text-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-gray-400">Dashboard</a>
            <span class="text-gray-400">/</span>
            <span class="text-gray-800 dark:text-white">User Management</span>
        </div>
    </x-common.page-breadcrumb>

    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-title-md font-semibold text-gray-800 dark:text-white/90">All Users</h1>
        <a href="{{ route('admin.users.create') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white shadow-theme-xs transition-colors hover:bg-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900 motion-reduce:transition-none">
            + Create User
        </a>
    </div>

    @if (session('status'))
        <p role="status" aria-live="polite" class="rounded-lg border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-300">
            {{ match (session('status')) {
                'user-created' => __('User created successfully.'),
                'user-updated' => __('User updated successfully.'),
                'user-deleted' => __('User deleted successfully.'),
                default => session('status'),
            } }}
        </p>
    @endif
    @if ($errors->has('user'))
        <p role="alert" class="rounded-lg border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-300">{{ $errors->first('user') }}</p>
    @endif

    <x-common.component-card>
        <div class="mb-4">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col gap-3 sm:flex-row">
                <label class="sr-only" for="user-search">Search users</label>
                <input id="user-search" type="search" name="search" value="{{ request('search') }}" placeholder="Search users..." class="min-h-10 w-full min-w-0 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white sm:max-w-64">
                <label class="sr-only" for="user-role">Filter by role</label>
                <select id="user-role" name="role" class="min-h-10 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-theme-xs focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white sm:w-auto">
                    <option value="">All Roles</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>User</option>
                </select>
                <div class="flex items-center gap-3">
                    <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900">Filter</button>
                    <a href="{{ route('admin.users.index') }}" class="rounded text-sm font-medium text-gray-500 underline-offset-4 hover:text-gray-700 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-gray-400 dark:hover:text-gray-200">Reset</a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[600px] border-collapse text-start">
                <thead>
                    <tr class="bg-gray-50 text-xs uppercase text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                        <th class="p-3 border-b dark:border-gray-700">User</th>
                        <th class="p-3 border-b dark:border-gray-700">Role</th>
                        <th class="p-3 border-b dark:border-gray-700">Status</th>
                        <th class="p-3 border-b dark:border-gray-700 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-800 dark:text-white">
                    @forelse($users as $user)
                        <tr class="border-b transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800/50">
                            <td class="p-3">
                                <div class="flex items-center gap-3">
                                    <x-ui.avatar :src="$user->profile_photo_url" />
                                    <div class="min-w-0">
                                        <div class="font-medium">{{ $user->name }}</div>
                                        <div class="break-all text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3">
                                <x-ui.badge :color="$user->isAdmin() ? 'success' : 'gray'">
                                    {{ $user->role->label() }}
                                </x-ui.badge>
                            </td>
                            <td class="p-3">
                                @if($user->hasVerifiedEmail())
                                    <span class="text-success-500 text-xs flex items-center gap-1">
                                        <span class="w-2 h-2 bg-success-500 rounded-full"></span> Verified
                                    </span>
                                @else
                                    <span class="text-warning-500 text-xs flex items-center gap-1">
                                        <span class="w-2 h-2 bg-warning-500 rounded-full"></span> Unverified
                                    </span>
                                @endif
                            </td>
                            <td class="space-x-2 p-3 text-end">
                                <a href="{{ route('admin.users.show', $user) }}" class="rounded text-sm text-gray-600 underline-offset-4 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-gray-300">{{ __('View') }}</a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="rounded text-sm text-brand-600 underline-offset-4 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-brand-400">Edit</a>
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Are you sure?')" class="rounded text-sm text-error-600 underline-offset-4 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-error-500 dark:text-error-400">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </x-common.component-card>
</div>
@endsection
