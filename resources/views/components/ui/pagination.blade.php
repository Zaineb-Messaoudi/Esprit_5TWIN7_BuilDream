@props([
    'pages' => 5,
    'current' => 1,
    'label' => __('Pagination'),
])

@php
    $pageCount = max(1, (int) $pages);
    $initialPage = max(1, min((int) $current, $pageCount));
@endphp

<nav {{ $attributes->merge(['class' => 'flex flex-wrap items-center justify-between gap-3']) }} aria-label="{{ $label }}" x-data="{
    page: {{ $initialPage }},
    pageCount: {{ $pageCount }},
    pageNumbers() {
        const start = Math.max(1, Math.min(this.page - 2, this.pageCount - 4));
        return Array.from({ length: Math.min(5, this.pageCount) }, (_, index) => start + index);
    }
}">
    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Page') }} <span x-text="page"></span> {{ __('of') }} {{ $pageCount }}</p>
    <div class="inline-flex items-center gap-1">
        <button type="button" @click="page = Math.max(1, page - 1)" :disabled="page === 1" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 disabled:cursor-not-allowed disabled:opacity-40 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:border-gray-700 dark:text-gray-300" aria-label="{{ __('Previous page') }}"><span aria-hidden="true" class="inline-block rtl:rotate-180">‹</span></button>
        <template x-for="number in pageNumbers()" :key="number">
            <button
                type="button"
                @click="page = number"
                :aria-label="'{{ __('Page') }} ' + number"
                :aria-current="page === number ? 'page' : null"
                :class="page === number ? 'bg-brand-500 text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'"
                class="h-9 min-w-9 rounded-lg px-2 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500"
                x-text="number"
            ></button>
        </template>
        <button type="button" @click="page = Math.min(pageCount, page + 1)" :disabled="page === pageCount" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 disabled:cursor-not-allowed disabled:opacity-40 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:border-gray-700 dark:text-gray-300" aria-label="{{ __('Next page') }}"><span aria-hidden="true" class="inline-block rtl:rotate-180">›</span></button>
    </div>
</nav>
