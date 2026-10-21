{{-- resources/views/auth/two-factor/setup.blade.php --}}
@extends('layouts.app')

@section('title', 'Two-Factor Authentication Setup')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-8">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Two-Factor Authentication Setup
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">
                Scan the QR code with your authenticator app
            </p>
        </div>

        @if(session('status') === '2fa-already-enabled')
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <p class="text-green-800 dark:text-green-200">
                    Two-factor authentication is already enabled for your account.
                </p>
            </div>
        @endif

        <div class="space-y-6">
            <div class="text-center">
                @if($qrCodeUrl)
                    <div class="inline-block p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <img src="{{ $qrCodeUrl }}" alt="2FA QR Code" class="w-64 h-64">
                    </div>
                @else
                    <div class="w-64 h-64 mx-auto bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                @endif
            </div>

            <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Secret Key (Manual Entry)</h3>
                <code class="text-sm text-gray-600 dark:text-gray-400 break-all">
                    {{ $user->two_factor_secret ?? 'Not generated yet' }}
                </code>
            </div>

            <form method="POST" action="{{ route('two-factor.enable') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Enter 6-digit code from your authenticator app
                    </label>
                    <input type="text" name="code" id="code" required autocomplete="one-time-code"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                           placeholder="123456" maxlength="6" pattern="\d{6}">
                    @error('code')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit"
                        class="w-full py-3 px-4 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition-colors">
                    Enable Two-Factor Authentication
                </button>
            </form>

            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Recovery Codes</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                    Save these codes in a safe place. Each code can be used once to access your account if you lose access to your authenticator app.
                </p>
                <div class="grid grid-cols-2 gap-2 text-sm">
                    @foreach($recoveryCodes as $code)
                        <code class="px-3 py-2 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded font-mono text-center">
                            {{ $code }}
                        </code>
                    @endforeach
                </div>
                <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    Each code can only be used once. Regenerate them if you use too many.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection