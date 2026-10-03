@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6">
    <x-common.page-breadcrumb>
        <div class="flex items-center gap-2">
            <a href="/" class="text-gray-500 hover:text-brand">Dashboard</a>
            <span class="text-gray-400">/</span>
            <span class="text-gray-800 dark:text-white">User Management</span>
        </div>
    </x-common.page-breadcrumb>

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-title-md font-bold text-gray-800 dark:text-white">All Users</h2>
        <a href="{{ route('admin.users.create') }}" class="bg-brand text-white px-4 py-2 rounded-md hover:bg-brand-600 transition-colors">
            + Create User
        </a>
    </div>

    <x-common.component-card>
        <div class="mb-4 flex gap-4">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex gap-2 w-full">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search users..." class="border rounded px-3 py-2 dark:bg-gray-800 dark:border-gray-700 dark:text-white w-64">
                <select name="role" class="border rounded px-3 py-2 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                    <option value="">All Roles</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>User</option>
                </select>
                <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700 dark:bg-brand dark:hover:bg-brand-600">Filter</button>
                <a href="{{ route('admin.users.index') }}" class="text-gray-500 px-4 py-2 hover:underline">Reset</a>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-start border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-sm uppercase">
                        <th class="p-3 border-b dark:border-gray-700">User</th>
                        <th class="p-3 border-b dark:border-gray-700">Role</th>
                        <th class="p-3 border-b dark:border-gray-700">Status</th>
                        <th class="p-3 border-b dark:border-gray-700 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-800 dark:text-white">
                    @forelse($users as $user)
                        <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="p-3">
                                <div class="flex items-center gap-3">
                                    <x-ui.avatar :src="$user->profile_photo_url" />
                                    <div>
                                        <div class="font-medium">{{ $user->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3">
                                <x-ui.badge :color="$user->role === 'admin' ? 'success' : 'gray'">
                                    {{ ucfirst($user->role) }}
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
                            <td class="p-3 text-end space-x-2">
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-500 hover:underline text-sm">Edit</a>
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Are you sure?')" class="text-error-500 hover:underline text-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-gray-500">No users found.</td>
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
