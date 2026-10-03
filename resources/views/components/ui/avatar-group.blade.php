@props([
    'items' => [],
    'max' => 4,
    'label' => __('Team members'),
])

@php
    $visibleItems = array_slice($items, 0, max(0, (int) $max));
    $remaining = max(0, count($items) - count($visibleItems));
@endphp

<div class="flex items-center">
    <ul class="flex -space-x-2 rtl:space-x-reverse" aria-label="{{ $label }}">
        @foreach ($visibleItems as $item)
            <li class="rounded-full ring-2 ring-white dark:ring-gray-900" title="{{ $item['name'] }}">
                <x-ui.avatar
                    :src="$item['src'] ?? ''"
                    :alt="$item['name']"
                    size="small"
                    :status="$item['status'] ?? 'none'"
                />
            </li>
        @endforeach
        @if ($remaining > 0)
            <li class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-xs font-medium text-gray-600 ring-2 ring-white dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-900" aria-label="{{ $remaining }} {{ __('additional team members') }}">
                +{{ $remaining }}
            </li>
        @endif
    </ul>
</div>
