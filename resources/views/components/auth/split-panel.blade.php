<aside class="bg-brand-950 relative hidden min-h-screen w-full items-center justify-center overflow-hidden dark:bg-white/5 lg:grid lg:w-1/2">
    <div class="relative z-1 flex items-center justify-center">
        <x-common.common-grid-shape />
        <div class="flex flex-col items-center">
            <a href="{{ route('home') }}" class="mb-4 block rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white">
                <x-front.logo :inverted="true" />
            </a>
            <p class="max-w-xs text-center text-gray-400 dark:text-white/60">
                {{ __('Shared renewable energy, made easier for everyone.') }}
            </p>
        </div>
    </div>
</aside>
