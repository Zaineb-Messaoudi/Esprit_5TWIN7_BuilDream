@extends(auth()->user()->isAdmin() ? 'layouts.app' : 'layouts.front')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Dashboard') }} / {{ __('AI') }}</p>
                <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">{{ __('AI operations overview') }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $configured ? __('Live generation activity from your Groq connection.') : __('Configure the Groq API key on the server to enable generation.') }}</p>
            </div>
            <a href="{{ route('ai.settings') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">{{ __('AI settings') }}</a>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 md:gap-6">
            @foreach ([
                [__('Successful generations'), number_format($totalRequests)],
                [__('Input tokens'), number_format($totalInputTokens)],
                [__('Output tokens'), number_format($totalOutputTokens)],
                [__('Success rate'), $successRate.'%'],
            ] as [$label, $value])
                <x-common.component-card :title="$label"><p class="text-title-md font-semibold text-gray-800 dark:text-white/90">{{ $value }}</p></x-common.component-card>
            @endforeach
        </div>

        <x-common.component-card :title="__('AI Studio tools')" :desc="__('Choose a tool to generate content with AI.')">
            <div class="flex flex-wrap gap-3">
                @foreach (['Text generator' => 'ai.text', 'Image generator' => 'ai.image', 'Video generator' => 'ai.video', 'Code generator' => 'ai.code', 'AI chat' => 'ai.chat'] as $label => $routeName)
                    <a href="{{ route($routeName) }}" class="inline-flex min-h-10 items-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">{{ __($label) }}</a>
                @endforeach
                <a href="{{ route('ai.history') }}" class="inline-flex min-h-10 items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">{{ __('Generation history') }}</a>
                <a href="{{ route('ai.usage') }}" class="inline-flex min-h-10 items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">{{ __('AI usage') }}</a>
            </div>
        </x-common.component-card>

        <x-common.component-card :title="__('Recent generations')" :desc="__('Your latest AI requests and their status.')">
            @if ($recentGenerations->isEmpty())
                <x-ui.empty-state :title="__('No generations yet')" :message="__('Start with a text, image, video, code, or chat request.')" />
            @else
                <div class="space-y-3">
                    @foreach ($recentGenerations as $generation)
                        <a href="{{ route('ai.history') }}" class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-gray-100 p-3 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800/50">
                            <span class="min-w-0"><span class="block truncate text-sm font-medium text-gray-800 dark:text-white/90">{{ Str::limit($generation->prompt, 110) }}</span><span class="mt-1 block text-xs text-gray-500">{{ __($generation->tool) }} · {{ $generation->model }} · {{ $generation->created_at->diffForHumans() }}</span></span>
                            <x-ui.badge variant="light" :color="$generation->status === 'succeeded' ? 'success' : 'error'">{{ __($generation->status) }}</x-ui.badge>
                        </a>
                    @endforeach
                </div>
            @endif
        </x-common.component-card>
    </div>
@endsection
