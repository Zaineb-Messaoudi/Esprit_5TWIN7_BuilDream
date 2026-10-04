@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('UI Elements') }} / {{ __('Carousel') }}</p>
            <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">{{ __('Carousel') }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Responsive image carousel with keyboard controls, indicators, and accessible slide labels.') }}</p>
        </div>

        <x-common.component-card :title="__('Image carousel')" :desc="__('Use the arrow controls, slide indicators, or left and right arrow keys.')">
            <x-ui.carousel
                :label="__('Solar product highlights')"
                :slides="[
                    ['image' => '/images/carousel/carousel-01.png', 'alt' => __('Solar panels on a residential roof'), 'title' => __('Power a brighter future'), 'description' => __('Explore clean energy solutions for every home.')],
                    ['image' => '/images/carousel/carousel-02.png', 'alt' => __('Solar energy system product showcase'), 'title' => __('Designed for every rooftop'), 'description' => __('Reliable components built for everyday energy needs.')],
                    ['image' => '/images/carousel/carousel-03.png', 'alt' => __('Modern solar installation'), 'title' => __('Make energy work smarter'), 'description' => __('Flexible systems for homes and businesses.')],
                    ['image' => '/images/carousel/carousel-04.png', 'alt' => __('Renewable energy installation'), 'title' => __('Start with clean energy'), 'description' => __('A simple, responsive carousel ready for your own content.')],
                ]"
            />
        </x-common.component-card>

        <x-common.component-card :title="__('Carousel behavior')">
            <div class="grid gap-4 text-sm text-gray-600 dark:text-gray-300 sm:grid-cols-3">
                <p>{{ __('Keyboard: focus the carousel and use the left or right arrow keys.') }}</p>
                <p>{{ __('Touch: swipe gestures are not required; large, reachable controls work on mobile.') }}</p>
                <p>{{ __('Data: pass slide image, alt text, title, and description through the reusable Blade component.') }}</p>
            </div>
        </x-common.component-card>
    </div>
@endsection
