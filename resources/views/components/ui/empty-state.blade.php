@props([
    'title' => __('No data found'),
    'message' => __('Try adjusting your filters or search terms to find what you are looking for.'),
    'icon' => null,
    'actionLabel' => null,
    'actionRoute' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center p-8 text-center']) }}>
    @if($icon)
        <div class="mb-4 text-gray-300 dark:text-gray-600">
            {!! $icon !!}
        </div>
    @else
        <div class="mb-4 text-gray-300 dark:text-gray-600">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 20C7.59 20 4 16.41 4 12C4 7.59 7.59 4 12 4C16.41 4 20 7.59 20 12C20 16.41 16.41 20 12 20ZM12 17C13.1 17 14 16.1 14 15C14 13.9 13.1 13 12 13C10.9 13 10 13.9 10 15C10 16.1 10.9 17 12 17ZM12 9C13.1 9 14 8.1 14 7C14 5.9 13.1 5 12 5C10.9 5 10 5.9 10 7C10 8.1 10.9 9 12 9Z" fill="currentColor"/>
            </svg>
        </div>
    @endif

    <h3 class="mb-1 text-lg font-semibold text-gray-800 dark:text-white">
        {{ $title }}
    </h3>
    <p class="mx-auto max-w-xs text-sm text-gray-500 dark:text-gray-400">
        {{ $message }}
    </p>

    @if($actionLabel && $actionRoute)
        <div class="mt-6">
            <a href="{{ $actionRoute }}" class="button-base button-primary px-4 py-2.5 text-sm">
                {{ $actionLabel }}
            </a>
        </div>
    @endif
</div>
