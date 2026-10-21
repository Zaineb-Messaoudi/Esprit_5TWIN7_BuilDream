{{-- resources/views/auth/two-factor/verify.blade.php --}}
@extends('layouts.app')

@section('title', 'Two-Factor Authentication Verification')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-8">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary-100 dark:bg-primary-900/30 mb-4">
                <svg class="w-8 h-8 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Two-Factor Authentication
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">
                Enter the 6-digit code from your authenticator app
            </p>
        </div>

        <form method="POST" action="{{ route('two-factor.verify.code') }}" class="space-y-4">
            @csrf
            <div>
                <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    6-Digit Code
                </label>
                <input type="text" name="code" id="code" required autocomplete="one-time-code"
                       class="w-full px-4 py-3 text-center text-2xl tracking-widest border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                       placeholder="000000" maxlength="6" pattern="\d{6}" autofocus>
                @error('code')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="remember" value="1" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <span class="text-gray-600 dark:text-gray-400">Remember this device for 30 days</span>
                </label>
                <a href="{{ route('two-factor.recovery-codes') }}" class="text-primary-600 hover:text-primary-700">
                    Use a recovery code instead
                </a>
            </div>

            <button type="submit"
                    class="w-full py-3 px-4 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition-colors">
                Verify
            </button>
        </form>

        <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-sm text-gray-600 dark:text-gray-400 hover:text-primary-600">
                    Log out instead
                </button>
            </form>
        </div>
    </div>
</div>
@endsection