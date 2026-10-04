@props([
    'tabs' => [],
    'label' => __('Tabs'),
])

@php
    $tabsId = 'tabs-' . \Illuminate\Support\Str::uuid();
    $firstTab = array_key_first($tabs);
@endphp

<div {{ $attributes }} x-data="{ active: @js($firstTab) }">
    <div
        class="flex gap-5 overflow-x-auto border-b border-gray-200 dark:border-gray-800"
        role="tablist"
        aria-label="{{ $label }}"
        @keydown="const tabs = [...$el.querySelectorAll('[role=tab]')]; const current = tabs.indexOf($event.target); let next = null; if (['ArrowRight', 'ArrowLeft'].includes($event.key)) { const step = $event.key === 'ArrowRight' ? 1 : -1; next = tabs[(current + step + tabs.length) % tabs.length]; } else if ($event.key === 'Home') { next = tabs[0]; } else if ($event.key === 'End') { next = tabs[tabs.length - 1]; } if (next) { $event.preventDefault(); next.focus(); next.click(); }"
    >
        @foreach ($tabs as $key => $tab)
            <button
                type="button"
                role="tab"
                id="{{ $tabsId }}-tab-{{ $key }}"
                aria-controls="{{ $tabsId }}-panel-{{ $key }}"
                :aria-selected="active === @js($key)"
                :tabindex="active === @js($key) ? 0 : -1"
                @click="active = @js($key)"
                :class="active === @js($key) ? 'border-brand-500 text-brand-600 dark:text-brand-300' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                class="-mb-px whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500"
            >{{ $tab['label'] }}</button>
        @endforeach
    </div>
    @foreach ($tabs as $key => $tab)
        <div
            id="{{ $tabsId }}-panel-{{ $key }}"
            role="tabpanel"
            aria-labelledby="{{ $tabsId }}-tab-{{ $key }}"
            tabindex="0"
            x-show="active === @js($key)"
            @if ($key !== $firstTab) x-cloak @endif
            class="pt-5 text-sm text-gray-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-gray-300"
        >{{ $tab['content'] }}</div>
    @endforeach
</div>
