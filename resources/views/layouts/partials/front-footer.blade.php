<footer class="relative isolate overflow-hidden bg-gray-950 text-white print:hidden">
    <div class="hero-energy-grid pointer-events-none absolute inset-0 -z-10 opacity-20" aria-hidden="true"></div>
    <div class="mx-auto grid w-full max-w-7xl gap-x-10 gap-y-12 px-6 py-12 sm:px-8 sm:py-16 md:grid-cols-6 lg:px-10">
        <div class="md:col-span-2">
            <x-front.logo :inverted="true" />
            <p class="mt-5 max-w-sm text-theme-sm leading-6 text-gray-300">
                {{ __('Rent and share portable solar panels, batteries and small wind turbines between neighbours. Use clean energy without buying gear you will only need twice a year.') }}
            </p>
            <a href="{{ route('front.catalog') }}" class="button-base mt-6 border border-white/20 px-4 text-theme-sm text-white hover:border-brand-300 hover:bg-white/5 focus-visible:ring-brand-300">{{ __('Explore the catalog') }} <span aria-hidden="true">↗</span></a>
        </div>

        @foreach (config('front.footer') as $group => $links)
            <nav aria-label="{{ __($group) }}" class="md:col-span-1">
                <h2 class="text-theme-xs font-semibold uppercase tracking-[0.16em] text-warning-300">{{ __($group) }}</h2>
                <ul class="mt-5 space-y-3 text-theme-sm text-gray-300">
                    @foreach ($links as $routeName => $label)
                        @if (\Illuminate\Support\Facades\Route::has($routeName))
                            <li><a href="{{ route($routeName) }}" class="rounded-sm hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-300">{{ __($label) }}</a></li>
                        @endif
                    @endforeach
                </ul>
            </nav>
        @endforeach
    </div>

    <div class="border-t border-white/10">
        <div class="mx-auto flex w-full max-w-7xl flex-col gap-2 px-6 py-5 text-theme-xs text-gray-400 sm:flex-row sm:items-center sm:justify-between sm:px-8 lg:px-10">
            <p>&copy; {{ date('Y') }} SolarShare &middot; {{ __('Esprit 5TWIN7 – BuilDream project') }}</p>
            <p>{{ __('Good energy is better when it moves.') }}</p>
        </div>
    </div>
</footer>
