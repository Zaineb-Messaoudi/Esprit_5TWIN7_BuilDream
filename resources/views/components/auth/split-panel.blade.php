<aside class="relative hidden min-h-full flex-col justify-between overflow-hidden bg-brand-950 p-10 text-white lg:flex xl:p-14">
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -end-24 -top-24 size-80 rounded-full border border-white/10"></div>
        <div class="absolute -end-12 -top-12 size-56 rounded-full border border-white/10"></div>
        <div class="absolute -bottom-40 -start-24 size-96 rounded-full bg-brand-500/20 blur-3xl"></div>
    </div>

    <a href="{{ route('login') }}" class="relative inline-flex w-fit items-center gap-3 rounded-sm text-lg font-semibold text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white">
        <span class="flex size-10 items-center justify-center rounded-xl bg-white/10 text-brand-200" aria-hidden="true">
            <svg class="size-6" viewBox="0 0 24 24" fill="none">
                <path d="M12 2.75v2.5m0 13.5v2.5M2.75 12h2.5m13.5 0h2.5M5.46 5.46l1.77 1.77m9.54 9.54 1.77 1.77m0-13.08-1.77 1.77m-9.54 9.54-1.77 1.77M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
            </svg>
        </span>
        SolarShare
    </a>

    <div class="relative my-12 max-w-lg">
        <span class="mb-4 inline-flex items-center rounded-full border border-white/15 bg-white/5 px-3 py-1 text-xs font-medium tracking-wide text-brand-100">
            {{ __('SolarShare workspace') }}
        </span>
        <h2 class="text-3xl font-semibold leading-tight text-white xl:text-4xl">
            {{ __('A clearer view of your solar business.') }}
        </h2>
        <p class="mt-4 max-w-md text-sm leading-6 text-white/70">
            {{ __('Manage your workspace with the tools and account features available to your team.') }}
        </p>

        <div class="mt-10 rounded-2xl border border-white/10 bg-white/5 p-5 shadow-2xl backdrop-blur-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-medium text-white/60">{{ __('Workspace overview') }}</p>
                    <p class="mt-1 text-sm font-semibold text-white">{{ __('Your account, in one place') }}</p>
                </div>
                <span class="flex size-10 items-center justify-center rounded-xl bg-brand-400/15 text-brand-200" aria-hidden="true">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none">
                        <path d="m4 15 5-5 4 4 7-8M15 6h5v5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
            </div>
            <div class="mt-6 grid grid-cols-3 gap-3" aria-hidden="true">
                <div class="h-2 rounded-full bg-brand-300/80"></div>
                <div class="h-2 rounded-full bg-white/20"></div>
                <div class="h-2 rounded-full bg-white/10"></div>
            </div>
            <div class="mt-3 h-24 rounded-xl border border-white/10 bg-gradient-to-br from-brand-400/15 to-transparent"></div>
        </div>
    </div>

    <p class="relative text-xs text-white/50">{{ __('Secure access to your SolarShare account.') }}</p>
</aside>
