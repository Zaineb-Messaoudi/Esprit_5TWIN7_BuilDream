@props(['before', 'after'])

<figure class="overflow-hidden rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900" x-data="{ position: 50 }">
    <div class="relative aspect-video overflow-hidden rounded-xl bg-gray-100 dark:bg-gray-800">
        <img src="{{ $before->url }}" alt="{{ __('Before maintenance') }}" class="size-full object-cover">
        <img src="{{ $after->url }}" alt="{{ __('After maintenance') }}" class="absolute inset-0 size-full object-cover" :style="`clip-path: inset(0 ${100 - position}% 0 0)`">
        <span class="absolute start-3 top-3 rounded-full bg-gray-950/75 px-2.5 py-1 text-theme-xs text-white">{{ __('Before') }}</span>
        <span class="absolute end-3 top-3 rounded-full bg-gray-950/75 px-2.5 py-1 text-theme-xs text-white">{{ __('After') }}</span>
    </div>
    <label class="mt-4 block text-sm font-medium text-gray-700 dark:text-gray-300">
        {{ __('Compare before and after') }}
        <input type="range" min="0" max="100" x-model="position" class="mt-2 w-full accent-brand-600" aria-label="{{ __('Before and after comparison position') }}">
    </label>
</figure>
