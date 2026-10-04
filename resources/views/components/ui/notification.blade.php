@props([
    'title',
    'message',
    'time',
    'unread' => false,
])

<article class="flex gap-3 rounded-xl p-3 {{ $unread ? 'bg-brand-50/70 dark:bg-brand-500/5' : '' }}">
    <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full {{ $unread ? 'bg-brand-500' : 'bg-gray-300 dark:bg-gray-700' }}" aria-hidden="true"></span>
    <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <h3 class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $title }}</h3>
            <time class="text-xs text-gray-400">{{ $time }}</time>
        </div>
        <p class="mt-1 text-sm leading-5 text-gray-500 dark:text-gray-400">{{ $message }}</p>
    </div>
</article>
