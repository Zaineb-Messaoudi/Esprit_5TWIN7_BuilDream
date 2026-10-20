@props(['icon' => '', 'title' => '', 'value' => '', 'subtext' => '', 'trend' => null])

<div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="flex items-start justify-between">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center w-10 h-10 bg-gray-100 rounded-xl dark:bg-gray-800">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    @if($icon === 'ecommerce')
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    @elseif($icon === 'task')
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    @elseif($icon === 'finance')
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    @elseif($icon === 'logistics')
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    @endif
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $title }}</p>
                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $value }}</p>
            </div>
        </div>
        @if($trend)
            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium
                @if($trend['type'] === 'up')
                    bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400
                @else
                    bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400
                @endif
            ">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    @if($trend['type'] === 'up')
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                    @else
                        <path fill-rule="evenodd" d="M14.77 12.79a.75.75 0 01-1.06.02L10 8.832l-3.71 3.938a.75.75 0 01-1.08-1.04l4.25-4.5a.75.75 0 011.08 0l4.25 4.5a.75.75 0 01-.02 1.06z" clip-rule="evenodd" />
                    @endif
                </svg>
                {{ $trend['value'] }}
            </span>
        @endif
    </div>

    @if($subtext)
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $subtext }}</p>
    @endif
</div>