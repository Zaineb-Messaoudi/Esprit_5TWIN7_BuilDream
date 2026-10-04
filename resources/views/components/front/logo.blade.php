@props(['inverted' => false])

<span {{ $attributes->class(['inline-flex items-center gap-2.5']) }} translate="no">
    <img src="{{ asset('images/brand/solarshare-icon.png') }}" alt="" width="48" height="48" class="size-10 shrink-0 object-contain sm:size-11" />
    <span class="text-2xl font-semibold tracking-tight {{ $inverted ? 'text-white' : 'text-gray-950 dark:text-white' }}">
        Solar<span class="{{ $inverted ? 'text-brand-400' : 'text-brand-600 dark:text-brand-400' }}">Share</span>
    </span>
</span>
