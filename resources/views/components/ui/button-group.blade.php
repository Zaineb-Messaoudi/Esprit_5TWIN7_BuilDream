@props([
    'items' => [],
    'value' => null,
    'label' => __('Button group'),
])

<div
    x-data="{ selected: @js($value) }"
    class="inline-flex max-w-full overflow-x-auto rounded-lg shadow-theme-xs"
    role="group"
    aria-label="{{ $label }}"
>
    @foreach ($items as $index => $item)
        <button
            type="button"
            @click="selected = @js($item['value']); $dispatch('selection-change', selected)"
            :aria-pressed="selected === @js($item['value'])"
            :class="selected === @js($item['value']) ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300' : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'"
            class="whitespace-nowrap border border-gray-300 px-4 py-2 text-sm font-medium first:rounded-s-lg last:rounded-e-lg first:border-e-0 last:border-s-0 focus:z-10 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:border-gray-700"
        >{{ $item['label'] }}</button>
    @endforeach
</div>
