@props(['variant'])

@php
    $variantNames = [
        'classic' => __('Classic'),
        'sectioned' => __('Sectioned'),
        'documentation' => __('Documentation'),
        'collapsible' => __('Collapsible'),
        'nested' => __('Nested'),
        'toggle' => __('Toggle'),
    ];
    abort_unless(array_key_exists($variant, $variantNames), 404);
@endphp

<div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900" x-data="{ expanded: true, groupOpen: true }">
    <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-gray-800">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-white">{{ $variantNames[$variant] }}</h3>
        @if ($variant === 'toggle')
            <button type="button" @click="expanded = !expanded" :aria-expanded="expanded" class="rounded-md px-2 py-1 text-xs font-medium text-brand-600 hover:bg-brand-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-brand-300 dark:hover:bg-brand-500/10">{{ __('Toggle') }}</button>
        @endif
    </div>

    <aside class="min-h-64 p-3 text-sm text-gray-600 dark:text-gray-300" aria-label="{{ $variantNames[$variant] }} {{ __('sidebar preview') }}">
        @if ($variant === 'classic')
            <div class="mb-4 flex items-center gap-2 font-semibold text-gray-800 dark:text-white"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-500 text-white">S</span><span>SolarShare</span></div>
            <ul class="space-y-1">
                <li class="rounded-lg bg-brand-50 px-3 py-2 font-medium text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">▦ &nbsp; {{ __('Dashboard') }}</li>
                <li class="rounded-lg px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800">▤ &nbsp; {{ __('Products') }}</li>
                <li class="rounded-lg px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800">▣ &nbsp; {{ __('Calendar') }}</li>
            </ul>
        @elseif ($variant === 'sectioned')
            @foreach ([__('WORKSPACE') => [__('Overview'), __('Projects')], __('TOOLS') => [__('Reports'), __('Integrations')]] as $section => $items)
                <p class="mb-2 mt-3 px-3 text-[10px] font-semibold tracking-widest text-gray-400 first:mt-0">{{ $section }}</p>
                @foreach ($items as $item)
                    <a href="#" @click.prevent class="mb-1 block rounded-lg px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800">{{ $item }}</a>
                @endforeach
            @endforeach
        @elseif ($variant === 'documentation')
            <label class="sr-only" for="sidebar-doc-search">{{ __('Search documentation') }}</label>
            <input id="sidebar-doc-search" type="search" placeholder="{{ __('Search docs...') }}" class="mb-4 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800" />
            <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-widest text-gray-400">{{ __('Getting started') }}</p>
            @foreach ([__('Introduction'), __('Installation'), __('Project structure'), __('Components')] as $item)
                <a href="#" @click.prevent class="mb-1 block rounded-lg px-3 py-2 {{ $item === __('Introduction') ? 'bg-brand-50 font-medium text-brand-700 dark:bg-brand-500/10 dark:text-brand-300' : 'hover:bg-gray-50 dark:hover:bg-gray-800' }}">{{ $item }}</a>
            @endforeach
        @elseif ($variant === 'collapsible')
            <button type="button" @click="groupOpen = !groupOpen" :aria-expanded="groupOpen" class="flex w-full items-center justify-between rounded-lg px-3 py-2 font-medium text-gray-800 hover:bg-gray-50 dark:text-white dark:hover:bg-gray-800"><span>{{ __('Dashboards') }}</span><span x-text="groupOpen ? '−' : '+'"></span></button>
            <ul x-show="groupOpen" x-collapse class="mt-1 space-y-1 border-s-2 border-gray-100 ps-3 dark:border-gray-800">
                @foreach ([__('E-commerce'), __('Analytics'), __('Marketing')] as $item)
                    <li><a href="#" @click.prevent class="block rounded-lg px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800">{{ $item }}</a></li>
                @endforeach
            </ul>
            <a href="#" @click.prevent class="mt-1 block rounded-lg px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('Customers') }}</a>
        @elseif ($variant === 'nested')
            <ul class="space-y-1">
                <li><a href="#" @click.prevent class="block rounded-lg px-3 py-2 font-medium hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('Commerce') }}</a>
                    <ul class="ms-4 space-y-1 border-s-2 border-gray-100 ps-3 dark:border-gray-800">
                        <li><a href="#" @click.prevent class="block rounded-lg px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('Catalog') }}</a>
                            <ul class="ms-3 border-s-2 border-gray-100 ps-3 dark:border-gray-800">
                                <li><a href="#" @click.prevent class="block rounded-lg bg-brand-50 px-3 py-2 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">{{ __('Product list') }}</a></li>
                            </ul>
                        </li>
                        <li><a href="#" @click.prevent class="block rounded-lg px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800">{{ __('Orders') }}</a></li>
                    </ul>
                </li>
            </ul>
        @else
            <div class="flex items-center justify-between gap-3" :class="expanded ? '' : 'justify-center'">
                <div x-show="expanded" class="flex items-center gap-2 font-semibold text-gray-800 dark:text-white"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-500 text-white">S</span><span>SolarShare</span></div>
                <button type="button" @click="expanded = !expanded" :aria-expanded="expanded" class="rounded-lg border border-gray-200 p-2 text-gray-500 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800" aria-label="{{ __('Toggle sidebar width') }}">☰</button>
            </div>
            <ul class="mt-4 space-y-1">
                <li><a href="#" @click.prevent class="flex items-center gap-3 rounded-lg bg-brand-50 px-3 py-2 font-medium text-brand-700 dark:bg-brand-500/10 dark:text-brand-300"><span>▦</span><span x-show="expanded">{{ __('Dashboard') }}</span></a></li>
                <li><a href="#" @click.prevent class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800"><span>▤</span><span x-show="expanded">{{ __('Products') }}</span></a></li>
                <li><a href="#" @click.prevent class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800"><span>▣</span><span x-show="expanded">{{ __('Calendar') }}</span></a></li>
            </ul>
        @endif
    </aside>
</div>
