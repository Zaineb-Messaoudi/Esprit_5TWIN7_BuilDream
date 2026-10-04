@props([
    'trigger' => __('More information'),
    'label' => __('Additional information'),
])

<div {{ $attributes->class(['relative inline-block']) }} x-data="{ open: false }">
    <button
        type="button"
        @click="open = !open"
        @keydown.escape.window="open = false"
        @click.outside="open = false"
        :aria-expanded="open"
        class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
    >{{ $trigger }}</button>
    <div x-show="open" x-cloak role="region" aria-label="{{ $label }}" class="absolute start-0 top-full z-20 mt-2 w-64 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-lg dark:border-gray-700 dark:bg-gray-900">
        {{ $slot }}
    </div>
</div>
