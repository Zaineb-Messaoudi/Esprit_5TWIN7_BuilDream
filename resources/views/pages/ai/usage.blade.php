@extends(auth()->user()->isAdmin() ? 'layouts.app' : 'layouts.front')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400"><a href="{{ route('ai.index') }}" class="hover:text-brand-600">{{ __('AI Studio') }}</a> / {{ __('Usage') }}</p>
                <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Usage totals are based on requests recorded by this application.') }}</p>
            </div>
            <a href="{{ route('ai.history') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">{{ __('View history') }}</a>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ([
                [__('Successful requests'), number_format($totalRequests)],
                [__('Failed requests'), number_format($failedRequests)],
                [__('Input tokens'), number_format($inputTokens)],
                [__('Output tokens'), number_format($outputTokens)],
                [__('Images generated'), number_format($imagesGenerated)],
                [__('Videos generated'), number_format($videosGenerated)],
            ] as [$label, $value])
                <x-common.component-card :title="$label"><p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $value }}</p></x-common.component-card>
            @endforeach
        </div>

        <x-common.component-card :title="__('Recent usage')" :desc="__('Latest requests recorded by the AI Studio.')">
            @if ($recentGenerations->isEmpty())
                <x-ui.empty-state :title="__('No usage to report')" :message="__('Your AI requests will appear here after you use a studio tool.')" />
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[640px] text-start text-sm">
                        <thead><tr class="border-b border-gray-100 text-xs text-gray-400 dark:border-gray-800"><th class="px-3 py-3 font-medium">{{ __('Request') }}</th><th class="px-3 py-3 font-medium">{{ __('Tool') }}</th><th class="px-3 py-3 font-medium">{{ __('Model') }}</th><th class="px-3 py-3 font-medium">{{ __('Tokens') }}</th><th class="px-3 py-3 font-medium">{{ __('Status') }}</th><th class="px-3 py-3 font-medium">{{ __('Date') }}</th></tr></thead>
                        <tbody>
                            @foreach ($recentGenerations as $generation)
                                <tr class="border-b border-gray-50 last:border-0 dark:border-gray-800"><td class="px-3 py-4 font-medium text-gray-800 dark:text-white/90">{{ Str::limit($generation->prompt, 80) }}</td><td class="px-3 py-4 text-gray-500">{{ __($generation->tool) }}</td><td class="px-3 py-4 text-gray-500">{{ $generation->model }}</td><td class="px-3 py-4 text-gray-500">{{ number_format(($generation->input_tokens ?? 0) + ($generation->output_tokens ?? 0)) }}</td><td class="px-3 py-4"><x-ui.badge variant="light" :color="$generation->status === 'succeeded' ? 'success' : 'error'">{{ __($generation->status) }}</x-ui.badge></td><td class="px-3 py-4 text-gray-500">{{ $generation->created_at->format('M j, Y H:i') }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-common.component-card>
    </div>
@endsection
