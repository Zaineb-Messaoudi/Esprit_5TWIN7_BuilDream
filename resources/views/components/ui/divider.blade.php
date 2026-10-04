@props([
    'orientation' => 'horizontal',
])

@if ($orientation === 'vertical')
    <span aria-hidden="true" {{ $attributes->class(['mx-2 inline-block h-5 w-px self-center bg-gray-200 dark:bg-gray-700']) }}></span>
@else
    <hr {{ $attributes->class(['border-gray-200 dark:border-gray-800']) }}>
@endif
