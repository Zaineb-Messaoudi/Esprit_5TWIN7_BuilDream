@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Pages') }} / {{ __('Help center') }}</p>
            <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Answers about demo content, existing account features, and page-library behavior.') }}</p>
        </div>

        <div
            class="mx-auto max-w-4xl space-y-5"
            x-data="{ query: '', open: 0, visibleCount: {{ count($questions) }} }"
            x-effect="visibleCount = [...$el.querySelectorAll('[data-faq-item]')].filter((item) => !query || item.textContent.toLowerCase().includes(query.toLowerCase())).length"
        >
            <x-common.component-card :title="__('Search help topics')">
                <x-ui.search-box name="faq-search" :label="__('Search questions')" :placeholder="__('Type a keyword or question...')" x-model="query" />
            </x-common.component-card>

            <x-common.component-card :title="__('Popular questions')">
                <div class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach ($questions as $index => $item)
                        <section
                            data-faq-item
                            x-show="!query || $el.innerText.toLowerCase().includes(query.toLowerCase())"
                            class="py-1 first:pt-0 last:pb-0"
                        >
                            <h2>
                                <button
                                    type="button"
                                    @click="open = open === {{ $index }} ? -1 : {{ $index }}"
                                    :aria-expanded="open === {{ $index }}"
                                    aria-controls="faq-answer-{{ $index }}"
                                    class="flex w-full items-center justify-between gap-4 py-4 text-start text-sm font-medium text-gray-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-white/90"
                                >
                                    {{ $item['question'] }}
                                    <span class="shrink-0 text-gray-400" aria-hidden="true" x-text="open === {{ $index }} ? '−' : '+'"></span>
                                </button>
                            </h2>
                            <div id="faq-answer-{{ $index }}" role="region" x-show="open === {{ $index }}" x-cloak class="pb-4 pe-8 text-sm leading-6 text-gray-500 dark:text-gray-400">
                                {{ $item['answer'] }}
                            </div>
                        </section>
                    @endforeach
                    <p x-show="query && visibleCount === 0" x-cloak class="py-6 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('No questions match that search.') }}
                    </p>
                </div>
            </x-common.component-card>

            <p class="text-center text-sm text-gray-500 dark:text-gray-400">
                {{ __('Still need help?') }}
                <a href="{{ route('app.support') }}" class="font-medium text-brand-600 hover:text-brand-700 dark:text-brand-300">{{ __('Open support tickets') }}</a>
            </p>
        </div>
    </div>
@endsection
