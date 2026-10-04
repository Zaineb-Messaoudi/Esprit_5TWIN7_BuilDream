@extends('layouts.fullscreen-layout')

@section('title', __('Choose how to use SolarShare'))
@section('full-bleed', true)

@section('content')
    <main class="flex min-h-screen items-center justify-center bg-gray-50 p-4 dark:bg-gray-950 sm:p-8">
        <section class="w-full max-w-3xl rounded-[2rem] bg-white p-6 shadow-theme-md dark:bg-gray-900 sm:p-10">
            <a href="{{ route('home') }}" class="mb-8 inline-flex rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
                <x-front.logo />
            </a>
            <p class="mb-3 text-xs font-semibold uppercase tracking-[0.18em] text-brand-600 dark:text-brand-400">{{ __('One last step') }}</p>
            <h1 class="text-3xl font-semibold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                {{ __('How will you use SolarShare?') }}
            </h1>
            <p class="mt-3 text-sm leading-6 text-gray-500 dark:text-gray-400">
                {{ __('Choose the experience that fits you. You can contact support if your needs change later.') }}
            </p>

            <form method="POST" action="{{ route('role.setup.store') }}" class="mt-8">
                @csrf
                <fieldset>
                    <legend class="sr-only">{{ __('Choose your account type') }}</legend>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="flex min-h-36 cursor-pointer items-start gap-4 rounded-2xl border border-gray-200 p-5 transition hover:border-brand-300 has-checked:border-brand-500 has-checked:bg-brand-50 focus-within:ring-2 focus-within:ring-brand-500/20 dark:border-gray-700 dark:hover:border-brand-500 dark:has-checked:bg-brand-500/10">
                            <input type="radio" name="role" value="buyer" @checked(old('role', 'buyer') === 'buyer') required class="mt-1 text-brand-600 focus:ring-brand-500" />
                            <span>
                                <span class="block text-base font-semibold text-gray-900 dark:text-white">{{ __('I want to rent equipment') }}</span>
                                <span class="mt-2 block text-sm leading-6 text-gray-500 dark:text-gray-400">{{ __('Browse and borrow clean-energy gear nearby.') }}</span>
                            </span>
                        </label>
                        <label class="flex min-h-36 cursor-pointer items-start gap-4 rounded-2xl border border-gray-200 p-5 transition hover:border-brand-300 has-checked:border-brand-500 has-checked:bg-brand-50 focus-within:ring-2 focus-within:ring-brand-500/20 dark:border-gray-700 dark:hover:border-brand-500 dark:has-checked:bg-brand-500/10">
                            <input type="radio" name="role" value="owner" @checked(old('role') === 'owner') required class="mt-1 text-brand-600 focus:ring-brand-500" />
                            <span>
                                <span class="block text-base font-semibold text-gray-900 dark:text-white">{{ __('I want to share my equipment') }}</span>
                                <span class="mt-2 block text-sm leading-6 text-gray-500 dark:text-gray-400">{{ __('List your gear and manage it from an owner dashboard.') }}</span>
                            </span>
                        </label>
                    </div>
                    @error('role')
                        <p class="mt-3 text-sm text-error-500" role="alert">{{ $message }}</p>
                    @enderror
                </fieldset>
                <button type="submit" class="mt-7 inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-brand-600 px-5 py-3 text-sm font-semibold text-white shadow-theme-xs transition hover:bg-brand-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 sm:w-auto">
                    {{ __('Continue to SolarShare') }}
                </button>
            </form>
        </section>
    </main>
@endsection
