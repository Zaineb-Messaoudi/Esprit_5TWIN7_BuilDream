@extends('layouts.fullscreen-layout')

@php($title = $demo['title'])

@section('content')
    <main
        class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-8 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 sm:p-10"
        x-data="{ complete: false, value: '', submitDemo() { this.complete = true; } }"
    >
        <a href="{{ route('dashboard') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-300">
            SolarShare Admin
        </a>
        <p class="mt-8 text-sm font-medium text-gray-500 dark:text-gray-400">{{ $demo['eyebrow'] }}</p>
        <h1 class="mt-2 text-title-md font-semibold text-gray-800 dark:text-white/90">{{ $demo['heading'] }}</h1>
        <p class="mt-3 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $demo['description'] }}</p>

        <form class="mt-7 space-y-5" @submit.prevent="submitDemo()">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ $demo['field'] }}
                <input
                    x-model="value"
                    type="{{ $demo['mode'] === 'password' ? 'password' : 'text' }}"
                    @if ($demo['mode'] === 'code') inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" @endif
                    required
                    class="mt-2 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                >
            </label>
            <button type="submit" class="w-full rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">
                {{ $demo['button'] }}
            </button>
        </form>

        <p x-show="complete" x-cloak role="status" class="mt-5 rounded-lg bg-success-50 px-4 py-3 text-sm text-success-700 dark:bg-success-500/10 dark:text-success-300">
            {{ $demo['success'] }}
        </p>

        @if ($demo['mode'] === 'code')
            <p class="mt-5 text-center text-sm text-gray-500 dark:text-gray-400">
                Need another option?
                <a href="{{ route('login') }}" class="font-medium text-brand-600 hover:text-brand-700 dark:text-brand-300">Return to sign in</a>
            </p>
        @endif
    </main>
@endsection
