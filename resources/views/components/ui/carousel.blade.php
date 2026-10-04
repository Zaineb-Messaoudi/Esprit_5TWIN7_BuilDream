@props([
    'slides' => [],
    'label' => __('Featured slides'),
])

@php
    $slides = array_values(array_filter($slides, fn ($slide) => is_array($slide) && !empty($slide['image'])));
@endphp

@if (count($slides))
    <section
        x-data="{
            active: 0,
            count: {{ count($slides) }},
            previous() { this.active = (this.active - 1 + this.count) % this.count; },
            next() { this.active = (this.active + 1) % this.count; }
        }"
        @keydown.left.prevent="previous()"
        @keydown.right.prevent="next()"
        class="overflow-hidden rounded-xl"
        aria-roledescription="{{ __('carousel') }}"
        aria-label="{{ $label }}"
        tabindex="0"
    >
        <div class="relative">
            @foreach ($slides as $index => $slide)
                <article
                    x-show="active === {{ $index }}"
                    x-cloak
                    x-transition.opacity.duration.300ms
                    class="relative aspect-[16/8] min-h-48 bg-gray-100 dark:bg-gray-800 sm:aspect-[16/7]"
                    role="group"
                    aria-roledescription="{{ __('slide') }}"
                    aria-label="{{ __('Slide :current of :total', ['current' => $index + 1, 'total' => count($slides)]) }}"
                >
                    <img
                        src="{{ $slide['image'] }}"
                        alt="{{ $slide['alt'] ?? '' }}"
                        class="absolute inset-0 h-full w-full object-cover"
                        @if ($index > 0) loading="lazy" @endif
                    />
                    @if (!empty($slide['title']) || !empty($slide['description']))
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-gray-950/90 via-gray-950/50 to-transparent p-5 pt-16 text-white sm:p-8 sm:pt-20">
                            @if (!empty($slide['title']))
                                <h3 class="text-lg font-semibold sm:text-xl">{{ $slide['title'] }}</h3>
                            @endif
                            @if (!empty($slide['description']))
                                <p class="mt-1 max-w-2xl text-sm text-white/80">{{ $slide['description'] }}</p>
                            @endif
                        </div>
                    @endif
                </article>
            @endforeach

            @if (count($slides) > 1)
                <button type="button" @click="previous()" class="absolute start-3 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-white/60 bg-white/90 text-gray-700 shadow-theme-sm transition hover:bg-white focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500" aria-label="{{ __('Previous slide') }}">
                    <span aria-hidden="true" class="rtl:rotate-180">‹</span>
                </button>
                <button type="button" @click="next()" class="absolute end-3 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-white/60 bg-white/90 text-gray-700 shadow-theme-sm transition hover:bg-white focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500" aria-label="{{ __('Next slide') }}">
                    <span aria-hidden="true" class="rtl:rotate-180">›</span>
                </button>
            @endif
        </div>

        @if (count($slides) > 1)
            <div class="flex items-center justify-center gap-2 bg-white py-3 dark:bg-gray-900" role="group" aria-label="{{ __('Choose a slide') }}">
                @foreach ($slides as $index => $slide)
                    <button
                        type="button"
                        @click="active = {{ $index }}"
                        :aria-current="active === {{ $index }} ? 'true' : null"
                        :class="active === {{ $index }} ? 'w-6 bg-brand-500' : 'w-2 bg-gray-300 hover:bg-gray-400 dark:bg-gray-700 dark:hover:bg-gray-500'"
                        class="h-2 rounded-full transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2"
                        aria-label="{{ __('Go to slide :number', ['number' => $index + 1]) }}"
                    ></button>
                @endforeach
            </div>
        @endif
    </section>
@else
    <x-ui.empty-state :title="__('No slides available')" :message="__('Add slide image data to display the carousel.')" />
@endif
