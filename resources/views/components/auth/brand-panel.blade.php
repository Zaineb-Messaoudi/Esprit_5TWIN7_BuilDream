@props([
    'eyebrow' => __('A brighter way to share'),
    'heading' => __('Good energy is better shared.'),
    'description' => __('Find the power you need, or let someone else put your equipment to work.'),
])

<aside class="relative hidden min-h-[calc(100vh-2rem)] overflow-hidden rounded-[2rem] bg-gradient-to-br from-brand-950 via-green-950 to-gray-950 text-white shadow-2xl lg:flex lg:flex-col lg:justify-between lg:p-10 xl:p-14">
    <div aria-hidden="true" class="pointer-events-none absolute inset-0 overflow-hidden">
        <div class="absolute -end-24 -top-24 size-96 rounded-full bg-brand-500/20 blur-3xl"></div>
        <div class="absolute -bottom-32 -start-24 size-96 rounded-full bg-emerald-400/10 blur-3xl"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(255,255,255,0.08)_1px,transparent_0)] bg-[size:28px_28px] opacity-40"></div>
    </div>

    <div class="relative z-10 flex items-center justify-between">
        <a href="{{ route('home') }}" class="rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-300">
            <x-front.logo :inverted="true" />
        </a>
        <span class="rounded-full border border-white/15 bg-white/5 px-3.5 py-2 text-xs font-medium tracking-wide text-white/75">
            {{ __('POWERING SHARED MOMENTS') }}
        </span>
    </div>

    <div class="relative z-10 mx-auto w-full max-w-xl py-10">
        <p class="mb-4 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.2em] text-brand-300">
            <span class="size-1.5 rounded-full bg-brand-300"></span>
            {{ $eyebrow }}
        </p>
        <h2 class="max-w-lg text-4xl font-semibold leading-[1.08] tracking-tight text-white xl:text-5xl">
            {{ $heading }}
        </h2>
        <p class="mt-5 max-w-md text-base leading-7 text-white/65">
            {{ $description }}
        </p>

        <div class="relative mt-10 flex min-h-64 items-center justify-center overflow-hidden rounded-[1.75rem] border border-white/10 bg-white/[0.04] p-6">
            <div aria-hidden="true" class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-brand-500/15 to-transparent"></div>
            <svg class="relative w-full max-w-sm" viewBox="0 0 420 230" fill="none" role="img" aria-labelledby="solar-art-title">
                <title id="solar-art-title">{{ __('Illustration of shared solar power') }}</title>
                <circle cx="326" cy="57" r="33" fill="currentColor" class="text-amber-200/90" />
                <circle cx="326" cy="57" r="49" stroke="currentColor" stroke-opacity=".2" class="text-amber-100" />
                <path d="M0 179c57-35 104-35 157 0s101 35 155 0 77-34 108-19v70H0v-51Z" fill="currentColor" class="text-brand-900/80" />
                <path d="m40 173 18-71h148l25 71H40Z" fill="currentColor" class="text-brand-800 stroke-brand-300" stroke="currentColor" stroke-opacity=".55" />
                <path d="m59 108-14 57m49-57-8 57m42-57v57m42-57 9 57M50 130h159m-166 20h173" stroke="currentColor" stroke-opacity=".45" class="text-brand-200" />
                <path d="m256 183 9-13h54l10 13v28h-73v-28Z" fill="currentColor" class="text-gray-100" />
                <path d="M275 170v-13a17 17 0 0 1 34 0v13" stroke="currentColor" stroke-width="6" class="text-brand-300" />
                <rect x="282" y="182" width="10" height="13" rx="2" fill="currentColor" class="text-brand-500" />
                <path d="M210 173c18-13 30-15 48-14" stroke="currentColor" stroke-width="2" stroke-dasharray="4 7" class="text-brand-300" />
                <path d="M204 65v-20m-18 29-14-14m14 50-14 14m59-50 14-14m-14 50 14 14" stroke="currentColor" stroke-width="3" stroke-linecap="round" class="text-amber-100/80" />
            </svg>
        </div>

        <div class="mt-7 grid grid-cols-3 gap-3">
            @foreach ([
                [__('Discover'), __('Gear nearby')],
                [__('Share'), __('What you own')],
                [__('Reuse'), __('More, together')],
            ] as [$label, $detail])
                <div class="border-s border-white/15 ps-3 first:border-0 first:ps-0">
                    <p class="text-sm font-semibold text-white">{{ $label }}</p>
                    <p class="mt-1 text-xs leading-5 text-white/50">{{ $detail }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <div class="relative z-10 flex items-center justify-between border-t border-white/10 pt-5 text-xs text-white/45">
        <span>{{ __('A community-powered energy future.') }}</span>
        <span>© {{ date('Y') }} SolarShare</span>
    </div>
</aside>
