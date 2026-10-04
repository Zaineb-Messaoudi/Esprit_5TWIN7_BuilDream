@props([
    'title' => '',
    'items' => [],
    'active' => '',
])

<nav {{ $attributes->merge(['class' => 'flex items-center gap-2 text-theme-xs font-medium text-gray-500 dark:text-gray-400']) }}>
    @if($title)
        <span class="mr-2">{{ $title }}</span>
    @endif

    <ul class="flex items-center gap-1">
        @foreach($items as $item)
            <li>
                <a href="{{ $item['url'] }}"
                   class="px-3 py-1 rounded-md transition-colors {{ $active === $item['url'] ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400' : 'hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/5 dark:hover:text-gray-300' }}">
                    {{ $item['label'] }}
                </a>
            </li>
        @endforeach
    </ul>
</nav>
