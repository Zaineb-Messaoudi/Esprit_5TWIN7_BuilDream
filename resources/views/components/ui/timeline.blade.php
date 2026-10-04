@props([
    'items' => [],
])

<ol class="space-y-0">
    @foreach ($items as $index => $item)
        <li class="relative flex gap-4 pb-6 last:pb-0">
            @if ($index < count($items) - 1)
                <span class="absolute start-[9px] top-5 h-full w-px bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
            @endif
            <span class="relative mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full {{ $item['color'] ?? 'bg-brand-500' }}" aria-hidden="true"></span>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <h3 class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $item['title'] }}</h3>
                    @if (! empty($item['time']))
                        <time class="text-xs text-gray-400">{{ $item['time'] }}</time>
                    @endif
                </div>
                @if (! empty($item['description']))
                    <p class="mt-1 text-sm leading-5 text-gray-500 dark:text-gray-400">{{ $item['description'] }}</p>
                @endif
            </div>
        </li>
    @endforeach
</ol>
