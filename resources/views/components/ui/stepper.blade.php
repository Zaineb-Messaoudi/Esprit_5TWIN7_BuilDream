@props([
    'steps' => [],
    'current' => 1,
    'label' => __('Steps'),
])

<ol {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-3 text-sm']) }} aria-label="{{ $label }}">
    @foreach ($steps as $index => $step)
        <li class="inline-flex items-center gap-2">
            <span @class([
                'flex h-7 w-7 items-center justify-center rounded-full text-xs font-medium',
                'bg-brand-500 text-white' => $current === $index + 1,
                'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-300' => $current > $index + 1,
                'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' => $current < $index + 1,
            ]) aria-current="{{ $current === $index + 1 ? 'step' : 'false' }}">
                @if ($current > $index + 1) ✓ @else {{ $index + 1 }} @endif
            </span>
            <span @class([
                'text-gray-600 dark:text-gray-300',
                'font-medium text-gray-800 dark:text-white' => $current === $index + 1,
            ])>{{ $step }}</span>
            @if ($index < count($steps) - 1)
                <span class="text-gray-300 dark:text-gray-700" aria-hidden="true">/</span>
            @endif
        </li>
    @endforeach
</ol>
