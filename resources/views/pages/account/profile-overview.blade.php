@extends('layouts.app')

@section('content')
    @php($user = auth()->user())

    <div class="space-y-6">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Account') }} / {{ __('Profile overview') }}</p>
            <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">{{ __('My Profile') }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Review your account details and manage profile settings.') }}</p>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <x-common.component-card :title="__('Profile details')" class="xl:col-span-2">
                <dl class="grid grid-cols-1 gap-x-8 gap-y-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ __('Name') }}</dt>
                        <dd class="mt-2 text-sm font-medium text-gray-800 dark:text-white/90">{{ $user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ __('Email address') }}</dt>
                        <dd class="mt-2 break-all text-sm font-medium text-gray-800 dark:text-white/90">{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ __('Email verification') }}</dt>
                        <dd class="mt-2">
                            @if ($user->hasVerifiedEmail())
                                <x-ui.badge color="success" variant="light">{{ __('Verified') }}</x-ui.badge>
                            @else
                                <x-ui.badge color="warning" variant="light">{{ __('Pending verification') }}</x-ui.badge>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ __('Phone number') }}</dt>
                        <dd class="mt-2 text-sm font-medium text-gray-800 dark:text-white/90">{{ $user->phone_number ?: __('Not provided') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ __('Address') }}</dt>
                        <dd class="mt-2 text-sm font-medium text-gray-800 dark:text-white/90">{{ $user->address ?: __('Not provided') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ __('Account role') }}</dt>
                        <dd class="mt-2"><x-ui.badge color="{{ $user->isAdmin() ? 'success' : 'gray' }}" variant="light">{{ $user->role?->label() ?? __('User') }}</x-ui.badge></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ __('Account created') }}</dt>
                        <dd class="mt-2 text-sm font-medium text-gray-800 dark:text-white/90">{{ $user->created_at?->format('M d, Y') ?? __('Not available') }}</dd>
                    </div>
                </dl>
                <div class="mt-7 flex flex-wrap gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                    <a href="{{ route('profile.edit') }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">{{ __('Edit profile') }}</a>
                    <a href="{{ route('settings.password') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Security settings') }}</a>
                </div>
            </x-common.component-card>

            <x-common.component-card :title="__('Account shortcuts')">
                <nav aria-label="{{ __('Account shortcuts') }}" class="space-y-2">
                    <a href="{{ route('settings') }}" class="block rounded-lg px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Preferences') }}</a>
                    <a href="{{ route('settings.notifications') }}" class="block rounded-lg px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Notifications') }}</a>
                    <a href="{{ route('settings.security') }}" class="block rounded-lg px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Sessions and security') }}</a>
                    <a href="{{ route('integrations') }}" class="block rounded-lg px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Connected accounts') }}</a>
                </nav>
            </x-common.component-card>
        </div>
    </div>
@endsection
