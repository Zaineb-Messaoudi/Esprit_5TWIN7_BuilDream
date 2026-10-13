@props(['categories'])

@php($allImage = $categories->first()->image ?? asset('images/front/solar-panel.svg'))

<section class="relative isolate overflow-hidden border-y border-brand-900 bg-gray-950 text-white" aria-labelledby="category-rail-title" x-data="{ scroll(direction) { this.$refs.rail.scrollBy({ left: direction * 360, behavior: 'smooth' }) } }">
    <div class="pointer-events-none absolute inset-0 -z-10 bg-linear-to-br from-gray-950 via-brand-950/80 to-gray-950" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -end-20 -top-28 -z-10 size-80 rounded-full bg-brand-400/20 blur-3xl" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -bottom-40 start-1/3 -z-10 size-96 rounded-full bg-warning-300/10 blur-3xl" aria-hidden="true"></div>
    <div class="mx-auto max-w-7xl px-6 py-7 sm:px-8 lg:px-10">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-theme-xs font-semibold uppercase tracking-[0.2em] text-warning-300">{{ __('Browse by energy source') }}</p>
                <h2 id="category-rail-title" class="mt-1 text-title-sm font-semibold tracking-tight text-white">{{ __('Pick a current') }}</h2>
            </div>
            <div class="hidden gap-2 sm:flex">
                <button type="button" @click="scroll(-1)" class="flex size-10 items-center justify-center rounded-full border border-white/20 bg-white/5 text-gray-200 shadow-theme-xs transition hover:border-warning-300 hover:bg-white/10 hover:text-warning-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-warning-300" aria-label="{{ __('Scroll categories backward') }}">
                    <span aria-hidden="true" class="text-lg rtl:rotate-180">←</span>
                </button>
                <button type="button" @click="scroll(1)" class="flex size-10 items-center justify-center rounded-full border border-white/20 bg-white/5 text-gray-200 shadow-theme-xs transition hover:border-warning-300 hover:bg-white/10 hover:text-warning-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-warning-300" aria-label="{{ __('Scroll categories forward') }}">
                    <span aria-hidden="true" class="text-lg rtl:rotate-180">→</span>
                </button>
            </div>
        </div>

        <div x-ref="rail" class="no-scrollbar mt-6 flex snap-x snap-mandatory gap-6 overflow-x-auto px-1 pb-3">
            <a href="{{ route('front.catalog') }}#catalog-results-title" class="group min-w-24 snap-start text-center sm:min-w-28">
                <span class="mx-auto block size-20 overflow-hidden rounded-full border-2 border-warning-300/80 bg-brand-950 p-1 shadow-[0_0_0_5px_rgba(251,191,36,0.08),0_0_30px_rgba(251,191,36,0.18)] transition duration-300 group-hover:-translate-y-1 group-hover:scale-105 group-hover:border-warning-200 sm:size-24">
                    <img src="{{ $allImage }}" alt="" loading="lazy" class="size-full rounded-full object-cover grayscale transition duration-500 group-hover:scale-110 group-hover:grayscale-0 motion-reduce:transition-none" />
                </span>
                <span class="mt-3 block text-theme-sm font-semibold text-white">{{ __('All equipment') }}</span>
                <span class="mt-0.5 block text-theme-xs text-gray-400">{{ __('Everything') }}</span>
            </a>

            @foreach ($categories as $category)
                <a href="{{ route('front.catalog', ['category' => $category->id]) }}#catalog-results-title" class="group min-w-24 snap-start text-center sm:min-w-28">
                    <span class="relative mx-auto block size-20 overflow-hidden rounded-full border-2 border-brand-300/70 bg-brand-950 p-1 shadow-[0_0_0_5px_rgba(99,102,241,0.08),0_0_30px_rgba(99,102,241,0.16)] transition duration-300 group-hover:-translate-y-1 group-hover:scale-105 group-hover:border-warning-300 sm:size-24">
                        <img src="{{ $category->image }}" alt="{{ $category->name }}" loading="lazy" class="size-full object-cover transition duration-500 group-hover:scale-110 motion-reduce:transition-none" />
                    </span>
                    <span class="mt-3 block text-theme-sm font-semibold leading-tight text-white">{{ __($category->name) }}</span>
                    <span class="mt-0.5 block text-theme-xs text-gray-400">{{ __($category->technology) }}</span>
                </a>
            @endforeach
        </div>
    </div>
</section>
