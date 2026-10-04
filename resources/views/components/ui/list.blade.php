@props([
    'items' => [],
    'variant' => 'default',
])

@php
    $tag = $variant === 'ordered' ? 'ol' : 'ul';
    $listClass = $variant === 'ordered' ? 'list-decimal ps-5' : 'divide-y divide-gray-100 dark:divide-gray-800';
@endphp

<{{ $tag }} {{ $attributes->class(['space-y-0', $listClass]) }}>
    @foreach ($items as $item)
        <li class="py-3 first:pt-0 last:pb-0">
            <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ is_array($item) ? $item['title'] : $item }}</p>
            @if (is_array($item) && !empty($item['description']))
                <p class="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">{{ $item['description'] }}</p>
            @endif
        </li>
    @endforeach
</{{ $tag }}>
