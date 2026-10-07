@props(['customersByRole' => [], 'totalUsers' => 0])

@php
    $roles = !empty($customersByRole) ? $customersByRole : [];
    $total = $totalUsers ?? 0;
@endphp

<div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
    <div class="flex justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                {{ __('Users by Role') }}
            </h3>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">
                {{ $total }} total users
            </p>
        </div>
    </div>

    <div class="mt-6 space-y-4">
        @if(empty($roles))
            <p class="text-theme-sm text-gray-500 dark:text-gray-400 text-center py-8">
                {{ __('No users found.') }}
            </p>
        @else
            @foreach($roles as $role => $count)
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-brand-100 flex items-center justify-center dark:bg-brand-900">
                            <span class="text-brand-600 dark:text-brand-400 font-semibold text-sm">
                                {{ strtoupper(substr($role, 0, 1)) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">
                                {{ ucfirst($role) }}
                            </p>
                            <span class="block text-theme-xs text-gray-500 dark:text-gray-400">
                                {{ $count }} users
                            </span>
                        </div>
                    </div>

                    <div class="flex w-full max-w-[140px] items-center gap-3">
                        <div class="relative block h-2 w-full max-w-[100px] rounded-sm bg-gray-200 dark:bg-gray-800">
                            <div 
                                class="absolute left-0 top-0 flex h-full items-center justify-center rounded-sm bg-brand-500 text-xs font-medium text-white"
                                style="width: {{ $total > 0 ? round(($count / $total) * 100) : 0 }}%"
                            ></div>
                        </div>
                        <p class="text-theme-sm font-medium text-gray-800 dark:text-white/90">
                            {{ $total > 0 ? round(($count / $total) * 100) : 0 }}%
                        </p>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>