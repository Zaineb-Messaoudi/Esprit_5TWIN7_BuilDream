@extends('layouts.fullscreen-layout')

@section('title', __('Sign in'))

@section('content')
    <main class="grid min-h-[calc(100vh-2rem)] w-full max-w-6xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xl dark:border-gray-800 dark:bg-gray-900 lg:grid-cols-2">
        <section class="flex items-center justify-center px-5 py-10 sm:px-10 lg:px-12" aria-labelledby="signin-title">
            <div class="w-full max-w-md">
                <a href="{{ route('login') }}" class="mb-10 inline-flex items-center gap-3 rounded-sm text-lg font-semibold text-gray-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-white lg:hidden">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-300" aria-hidden="true">
                        <svg class="size-6" viewBox="0 0 24 24" fill="none">
                            <path d="M12 2.75v2.5m0 13.5v2.5M2.75 12h2.5m13.5 0h2.5M5.46 5.46l1.77 1.77m9.54 9.54 1.77 1.77m0-13.08-1.77 1.77m-9.54 9.54-1.77 1.77M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                        </svg>
                    </span>
                    SolarShare
                </a>

                <div class="mb-8">
                    <h1 id="signin-title" class="text-title-md font-semibold text-gray-900 dark:text-white/90">{{ __('Sign in') }}</h1>
                    <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ __('Sign in to continue to your account.') }}</p>
                </div>

                <x-auth-session-status class="mb-5" :status="session('status')" />

                <form id="signin-form" method="POST" action="{{ route('login') }}" aria-labelledby="signin-title">
                    @csrf

                    <div class="space-y-5">
                        <div>
                            <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Email address') }}</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" inputmode="email"
                                @class([
                                    'h-11 w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-gray-900 shadow-theme-xs outline-none transition placeholder:text-gray-400 focus:ring-3 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30',
                                    'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-500' => $errors->has('email'),
                                    'border-gray-300 focus:border-brand-400 focus:ring-brand-500/10 dark:border-gray-700 dark:focus:border-brand-400' => !$errors->has('email'),
                                ])
                                @if ($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif
                            />
                            @error('email')
                                <p id="email-error" class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div x-data="{ showPassword: false }">
                            <div class="mb-1.5 flex items-center justify-between gap-3">
                                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Password') }}</label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="rounded-sm text-sm font-medium text-brand-600 hover:text-brand-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-brand-400 dark:hover:text-brand-300">{{ __('Forgot your password?') }}</a>
                                @endif
                            </div>
                            <div class="relative">
                                <input id="password" :type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password"
                                    @class([
                                        'h-11 w-full rounded-lg border bg-white px-4 py-2.5 pe-12 text-sm text-gray-900 shadow-theme-xs outline-none transition placeholder:text-gray-400 focus:ring-3 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30',
                                        'border-error-500 focus:border-error-500 focus:ring-error-500/10 dark:border-error-500' => $errors->has('password'),
                                        'border-gray-300 focus:border-brand-400 focus:ring-brand-500/10 dark:border-gray-700 dark:focus:border-brand-400' => !$errors->has('password'),
                                    ])
                                    @if ($errors->has('password')) aria-invalid="true" aria-describedby="password-error" @endif
                                />
                                <button type="button" @click="showPassword = !showPassword" :aria-label="showPassword ? @js(__('Hide password')) : @js(__('Show password'))" class="absolute inset-y-0 end-0 inline-flex items-center justify-center px-4 text-gray-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-brand-500 dark:text-gray-400">
                                    <span x-text="showPassword ? @js(__('Hide password')) : @js(__('Show password'))" class="sr-only"></span>
                                    <svg x-show="!showPassword" class="size-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                        <path d="M2.5 10s2.7-4.5 7.5-4.5 7.5 4.5 7.5 4.5-2.7 4.5-7.5 4.5S2.5 10 2.5 10Z" stroke="currentColor" stroke-width="1.5" />
                                        <circle cx="10" cy="10" r="2" stroke="currentColor" stroke-width="1.5" />
                                    </svg>
                                    <svg x-cloak x-show="showPassword" class="size-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                        <path d="m3 3 14 14M8.6 5.7A8 8 0 0 1 10 5.5c4.8 0 7.5 4.5 7.5 4.5a12 12 0 0 1-2.3 2.8M5.2 6.7A13.3 13.3 0 0 0 2.5 10s2.7 4.5 7.5 4.5c.6 0 1.2-.1 1.7-.2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <p id="password-error" class="mt-2 text-sm text-error-600 dark:text-error-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <label for="remember" class="flex w-fit cursor-pointer items-center gap-2.5 text-sm text-gray-600 dark:text-gray-400">
                            <input id="remember" type="checkbox" name="remember" value="1" @checked(old('remember')) class="size-4 rounded border-gray-300 text-brand-600 focus:ring-2 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900" />
                            {{ __('Remember me') }}
                        </label>

                        <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900">
                            {{ __('Sign in') }}
                        </button>
                    </div>
                </form>

                <p class="mt-8 text-center text-sm text-gray-600 dark:text-gray-400">
                    {{ __("Don't have an account?") }}
                    <a href="{{ route('register') }}" class="rounded-sm font-medium text-brand-600 hover:text-brand-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-brand-400 dark:hover:text-brand-300">{{ __('Create an account') }}</a>
                </p>
            </div>
        </section>

        <x-auth.split-panel />
    </main>
@endsection
