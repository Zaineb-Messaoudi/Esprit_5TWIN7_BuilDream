{{-- resources/views/auth/two-factor/recovery-codes.blade.php --}}
@extends('layouts.app')

@section('title', 'Two-Factor Authentication Recovery Codes')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-8">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-amber-100 dark:bg-amber-900/30 mb-4">
                <svg class="w-8 h-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Recovery Codes
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">
                @if($regenerated ?? false)
                    Your recovery codes have been regenerated.
                @else
                    Save these codes in a safe place. Each code can be used once.
                @endif
            </p>
        </div>

        @if($regenerated ?? false)
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <p class="text-green-800 dark:text-green-200">
                    New recovery codes generated. The old codes are no longer valid.
                </p>
            </div>
        @endif

        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-2">
                @foreach($recoveryCodes as $code)
                    <code class="px-4 py-3 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg font-mono text-lg text-center break-all select-all">
                        {{ $code }}
                    </code>
                @endforeach
            </div>

            @if(empty($recoveryCodes))
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-gray-600 dark:text-gray-400">No recovery codes available.</p>
                    <p class="text-sm mt-1">Enable 2FA to generate recovery codes.</p>
                </div>
            @endif

            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                <form method="POST" action="{{ route('two-factor.regenerate-recovery-codes') }}" class="inline">
                    @csrf
                    <button type="submit"
                            class="w-full py-3 px-4 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg transition-colors"
                            onclick="return confirm('This will invalidate all current recovery codes. Continue?')">
                        Regenerate Recovery Codes
                    </button>
                </form>
            </div>

            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('profile.edit') }}" class="w-full py-3 px-4 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-white font-semibold rounded-lg transition-colors text-center block">
                    Back to Profile
                </a>
            </div>
        </div>
    </div>
</div>
@endsection