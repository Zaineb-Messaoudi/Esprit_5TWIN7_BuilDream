@props([
    'items' => [],
    'label' => __('Expandable sections'),
    'id' => 'accordion-' . \Illuminate\Support\Str::uuid(),
])

<div {{ $attributes->merge(['class' => 'divide-y divide-gray-200 dark:divide-gray-800']) }} x-data="{ open: null }">
    @foreach ($items as $index => $item)
        <section class="py-1">
            <h3>
                <button
                    type="button"
                    id="{{ $id }}-trigger-{{ $index }}"
                    @click="open = open === {{ $index }} ? null : {{ $index }}"
                    :aria-expanded="open === {{ $index }}"
                    aria-controls="{{ $id }}-panel-{{ $index }}"
                    class="flex w-full items-center justify-between py-3 text-start text-sm font-medium text-gray-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-white/90"
                >
                    {{ $item['title'] }}
                    <span aria-hidden="true" class="text-gray-400" x-text="open === {{ $index }} ? '−' : '+'"></span>
                </button>
            </h3>
            <div
                id="{{ $id }}-panel-{{ $index }}"
                x-show="open === {{ $index }}"
                x-cloak
                role="region"
                aria-labelledby="{{ $id }}-trigger-{{ $index }}"
                class="pb-4 text-sm text-gray-500 dark:text-gray-400"
            >{{ $item['content'] }}</div>
        </section>
    @endforeach
</div>
