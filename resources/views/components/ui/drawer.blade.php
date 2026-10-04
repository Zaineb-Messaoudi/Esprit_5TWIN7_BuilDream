@props([
    'id' => 'drawer-' . \Illuminate\Support\Str::uuid(),
    'title' => __('Details'),
    'trigger' => __('Open drawer'),
])

<div x-data="{ open: false, close() { this.open = false; this.$nextTick(() => this.$refs.trigger.focus()); } }">
    <button
        x-ref="trigger"
        type="button"
        @click="open = true; $nextTick(() => $refs.close.focus())"
        aria-controls="{{ $id }}"
        :aria-expanded="open"
        class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2"
    >{{ $trigger }}</button>
    <div x-show="open" x-cloak @keydown.escape.window="close()" class="fixed inset-0 z-999999" role="presentation">
        <button type="button" @click="close()" class="absolute inset-0 h-full w-full bg-gray-900/50" aria-label="{{ __('Close drawer') }}"></button>
        <aside
            id="{{ $id }}"
            x-ref="panel"
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="ltr:translate-x-full rtl:-translate-x-full"
            x-transition:enter-end="translate-x-0"
            @keydown.tab="const focusable = [...$refs.panel.querySelectorAll('button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]')].filter(element => element.tabIndex >= 0 && element.offsetParent !== null); const first = focusable[0]; const last = focusable[focusable.length - 1]; if (!first) { $event.preventDefault(); $refs.close.focus(); } else if ($event.shiftKey && document.activeElement === first) { $event.preventDefault(); last.focus(); } else if (!$event.shiftKey && document.activeElement === last) { $event.preventDefault(); first.focus(); }"
            class="absolute inset-y-0 end-0 flex w-full max-w-sm flex-col bg-white shadow-theme-lg dark:bg-gray-900"
            role="dialog"
            aria-modal="true"
            aria-labelledby="{{ $id }}-title"
        >
            <div class="flex items-center justify-between border-b border-gray-200 p-5 dark:border-gray-800">
                <h2 id="{{ $id }}-title" class="font-semibold text-gray-800 dark:text-white">{{ $title }}</h2>
                <button x-ref="close" type="button" @click="close()" class="rounded p-2 text-gray-500 hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:hover:bg-gray-800" aria-label="{{ __('Close drawer') }}">×</button>
            </div>
            <div class="flex-1 overflow-y-auto p-5">{{ $slot }}</div>
            <div class="border-t border-gray-200 p-5 dark:border-gray-800">
                <button x-ref="last" type="button" @click="close()" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Close') }}</button>
            </div>
        </aside>
    </div>
</div>
