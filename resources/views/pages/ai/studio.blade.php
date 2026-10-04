@extends('layouts.app')

@section('content')
    <div
        class="space-y-6"
        x-data="{
            submitted: false,
            copied: false,
            prompt: '',
            maxTokens: 2048,
            temperature: 0.4,
            messages: [{ role: 'assistant', text: 'Hello! I can help you explore SolarShare demo workflows. This local UI does not call an AI service.' }],
            sendMessage() {
                const message = this.prompt.trim();
                if (!message) return;
                this.messages.push({ role: 'user', text: message });
                this.prompt = '';
            },
            submitDemo() {
                this.submitted = true;
            }
        }"
    >
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">AI Studio / {{ $tool['title'] }}</p>
                <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">{{ $tool['title'] }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $tool['description'] }}</p>
            </div>
            <x-ui.badge variant="light" color="warning">UI demo · no AI connected</x-ui.badge>
        </div>

        <div class="grid grid-cols-1 items-start gap-6 xl:grid-cols-4">
            <nav class="rounded-2xl border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-white/[0.03]" aria-label="AI Studio tools">
                <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wide text-gray-400">Tools</p>
                <ul class="space-y-1">
                    @foreach ($tools as $key => $item)
                        <li>
                            <a href="{{ route('ai.' . $key) }}" @class([
                                'flex rounded-lg px-3 py-2.5 text-sm font-medium transition',
                                'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300' => $tool['kind'] === $item['kind'],
                                'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' => $tool['kind'] !== $item['kind'],
                            ]) @if($tool['kind'] === $item['kind']) aria-current="page" @endif>{{ $item['title'] }}</a>
                        </li>
                    @endforeach
                    <li><a href="{{ route('dashboard.ai') }}" class="flex rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800">AI dashboard</a></li>
                </ul>
            </nav>

            <div class="min-w-0 space-y-6 xl:col-span-3">
                @if (in_array($tool['kind'], ['text', 'image', 'video', 'code'], true))
                    <x-common.component-card :title="$tool['title']" desc="Enter a prompt and review the demo interaction. No generated media or code is produced.">
                        <form @submit.prevent="submitDemo()" class="space-y-5">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="ai-prompt">
                                @if ($tool['kind'] === 'video')
                                    Describe your storyboard
                                @elseif ($tool['kind'] === 'image')
                                    Describe your image
                                @elseif ($tool['kind'] === 'code')
                                    Describe the code task
                                @else
                                    What would you like to write?
                                @endif
                                <textarea id="ai-prompt" x-model="prompt" rows="5" maxlength="1200" placeholder="@if($tool['kind'] === 'image') A rooftop solar installation at golden hour... @elseif($tool['kind'] === 'video') A short overview of a residential solar system... @elseif($tool['kind'] === 'code') A reusable Blade card component for a solar product... @else Write a clear product description for a 420W residential solar panel... @endif" class="mt-2 w-full resize-y rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
                            </label>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Demo model
                                    <select class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                        @if ($tool['kind'] === 'image')
                                            <option>Illustration preview</option><option>Photorealistic preview</option>
                                        @elseif ($tool['kind'] === 'video')
                                            <option>Storyboard preview</option><option>Product showcase preview</option>
                                        @elseif ($tool['kind'] === 'code')
                                            <option>Code assistant preview</option><option>Code review preview</option>
                                        @else
                                            <option>Writing assistant preview</option><option>Campaign copy preview</option>
                                        @endif
                                    </select>
                                </label>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Output style
                                    <select class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"><option>Professional</option><option>Concise</option><option>Friendly</option></select>
                                </label>
                            </div>
                            @if ($tool['kind'] === 'video')
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Duration<select class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"><option>15 seconds</option><option>30 seconds</option><option>60 seconds</option></select></label>
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Aspect ratio<select class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"><option>16:9 landscape</option><option>1:1 square</option><option>9:16 portrait</option></select></label>
                                </div>
                            @endif
                            <div class="flex flex-wrap items-center gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                                <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                                    @if ($tool['kind'] === 'image') Preview image request @elseif ($tool['kind'] === 'video') Preview storyboard request @elseif ($tool['kind'] === 'code') Preview code request @else Preview writing request @endif
                                </button>
                                <span class="text-xs text-gray-400"><span x-text="prompt.length"></span>/1200 characters</span>
                            </div>
                        </form>
                    </x-common.component-card>

                    <div x-show="submitted" x-cloak role="status">
                        <x-common.component-card title="Demo request received">
                            <div class="flex gap-3">
                                <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-300" aria-hidden="true">i</span>
                                <div>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white/90">This interface is not connected to an AI service.</p>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Your prompt stays in this browser page. No text, image, video, or code was generated or sent.</p>
                                </div>
                            </div>
                        </x-common.component-card>
                    </div>
                @elseif ($tool['kind'] === 'chat')
                    <x-common.component-card title="SolarShare AI chat" desc="Local interface preview · messages are not saved">
                        <div class="flex min-h-[440px] flex-col">
                            <div class="flex-1 space-y-4 overflow-y-auto p-1" aria-live="polite">
                                <template x-for="(message, index) in messages" :key="index">
                                    <div class="flex" :class="message.role === 'user' ? 'justify-end' : 'justify-start'">
                                        <div class="max-w-[85%] rounded-2xl px-4 py-3 text-sm" :class="message.role === 'user' ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'" x-text="message.text"></div>
                                    </div>
                                </template>
                            </div>
                            <form @submit.prevent="sendMessage()" class="mt-5 flex items-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                                <label class="sr-only" for="chat-prompt">Write a message</label>
                                <textarea id="chat-prompt" x-model="prompt" rows="2" maxlength="1000" placeholder="Write a message..." class="min-w-0 flex-1 resize-none rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
                                <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Send</button>
                            </form>
                        </div>
                    </x-common.component-card>
                @elseif ($tool['kind'] === 'history')
                    <x-common.component-card title="Recent generations" desc="Illustrative history records; no real generations have taken place.">
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[640px] text-start text-sm">
                                <thead><tr class="border-b border-gray-100 text-xs text-gray-400 dark:border-gray-800"><th class="px-3 py-3 font-medium">Request</th><th class="px-3 py-3 font-medium">Type</th><th class="px-3 py-3 font-medium">Model</th><th class="px-3 py-3 font-medium">Date</th><th class="px-3 py-3 font-medium">Status</th></tr></thead>
                                <tbody>
                                    @foreach ([
                                        ['Product description: 420W solar panel', 'Text', 'Writing preview', 'Oct 2, 2026', 'Demo'],
                                        ['Residential rooftop storyboard', 'Video', 'Storyboard preview', 'Oct 1, 2026', 'Demo'],
                                        ['Support ticket summary', 'Text', 'Writing preview', 'Sep 29, 2026', 'Demo'],
                                        ['Installation checklist helper', 'Code', 'Code preview', 'Sep 28, 2026', 'Demo'],
                                    ] as [$request, $type, $model, $date, $status])
                                        <tr class="border-b border-gray-50 last:border-0 dark:border-gray-800"><td class="px-3 py-4 font-medium text-gray-800 dark:text-white/90">{{ $request }}</td><td class="px-3 py-4 text-gray-500">{{ $type }}</td><td class="px-3 py-4 text-gray-500">{{ $model }}</td><td class="px-3 py-4 text-gray-500">{{ $date }}</td><td class="px-3 py-4"><x-ui.badge variant="light" color="gray">{{ $status }}</x-ui.badge></td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-common.component-card>
                    <x-common.component-card title="Usage summary">
                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                            <div><p class="text-xs text-gray-400">Total requests</p><p class="mt-1 font-semibold text-gray-800 dark:text-white">0 live</p></div>
                            <div><p class="text-xs text-gray-400">Generated tokens</p><p class="mt-1 font-semibold text-gray-800 dark:text-white">0 live</p></div>
                            <div><p class="text-xs text-gray-400">Images generated</p><p class="mt-1 font-semibold text-gray-800 dark:text-white">0 live</p></div>
                            <div><p class="text-xs text-gray-400">Videos generated</p><p class="mt-1 font-semibold text-gray-800 dark:text-white">0 live</p></div>
                        </div>
                    </x-common.component-card>
                @else
                    <x-common.component-card :title="__('Model configuration')" :desc="__('Choose a provider, model, and generation limits for the visual demo.')">
                        <form @submit.prevent="submitDemo()" class="space-y-5">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <label for="ai-provider" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Provider') }}
                                    <select id="ai-provider" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"><option>OpenAI-compatible</option><option>Anthropic-compatible</option><option>Custom endpoint</option></select>
                                </label>
                                <label for="ai-model" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Model') }}
                                    <select id="ai-model" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"><option>gpt-4o-mini · demo</option><option>claude-3-haiku · demo</option><option>Custom model ID</option></select>
                                </label>
                            </div>
                            <label for="ai-base-url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('API base URL') }}
                                <input id="ai-base-url" type="url" placeholder="https://api.example.test/v1" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                            </label>
                            <label for="ai-api-key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('API key') }}
                                <input id="ai-api-key" type="password" autocomplete="new-password" placeholder="{{ __('Enter a demo key (not saved or sent)') }}" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                            </label>
                            <p class="rounded-lg bg-warning-50 px-3 py-2.5 text-xs leading-5 text-warning-700 dark:bg-warning-500/10 dark:text-warning-300">{{ __('Demo only: never enter a real API key. This form does not save, transmit, or connect credentials.') }}</p>

                            <div class="grid grid-cols-1 gap-5 border-t border-gray-100 pt-5 dark:border-gray-800 sm:grid-cols-2">
                                <label for="ai-max-tokens" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Maximum output tokens') }}
                                    <div class="mt-2 flex items-center gap-3"><input id="ai-max-tokens" x-model.number="maxTokens" type="range" min="256" max="8192" step="256" class="min-w-0 flex-1 accent-brand-500" /><output for="ai-max-tokens" class="w-16 text-end text-sm font-semibold text-gray-800 dark:text-white" x-text="maxTokens"></output></div>
                                    <span class="mt-1 block text-xs text-gray-400">{{ __('Allowed range: 256–8192 tokens') }}</span>
                                </label>
                                <label for="ai-temperature" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Temperature') }}
                                    <div class="mt-2 flex items-center gap-3"><input id="ai-temperature" x-model.number="temperature" type="range" min="0" max="1" step="0.1" class="min-w-0 flex-1 accent-brand-500" /><output for="ai-temperature" class="w-16 text-end text-sm font-semibold text-gray-800 dark:text-white" x-text="Number(temperature).toFixed(1)"></output></div>
                                    <span class="mt-1 block text-xs text-gray-400">{{ __('Lower values produce more consistent output.') }}</span>
                                </label>
                            </div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <label for="ai-fallback-model" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Fallback model') }}
                                    <select id="ai-fallback-model" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"><option>{{ __('None') }}</option><option>gpt-4o-mini · demo</option><option>claude-3-haiku · demo</option></select>
                                </label>
                                <label for="ai-timeout" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Request timeout') }}
                                    <select id="ai-timeout" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"><option>30 seconds</option><option>60 seconds</option><option>120 seconds</option></select>
                                </label>
                            </div>
                            <div class="space-y-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                                <label class="flex items-center justify-between gap-4 text-sm text-gray-700 dark:text-gray-300"><span><span class="block font-medium">{{ __('Log usage metrics') }}</span><span class="mt-0.5 block text-xs text-gray-400">{{ __('Display request and token totals in the demo.') }}</span></span><input type="checkbox" checked class="size-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500" /></label>
                                <label class="flex items-center justify-between gap-4 text-sm text-gray-700 dark:text-gray-300"><span><span class="block font-medium">{{ __('Show provider health checks') }}</span><span class="mt-0.5 block text-xs text-gray-400">{{ __('Show configuration readiness in the settings panel.') }}</span></span><input type="checkbox" class="size-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500" /></label>
                            </div>
                            <div class="flex flex-wrap items-center gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                                <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">{{ __('Save demo settings') }}</button>
                                <p x-show="submitted" x-cloak role="status" class="text-sm text-gray-500 dark:text-gray-400">{{ __('Settings preview updated in this page only; nothing was saved.') }}</p>
                            </div>
                        </form>
                    </x-common.component-card>
                    <x-common.component-card :title="__('Provider status')">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div><p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ __('No AI provider configured') }}</p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Add credentials only in a secure backend integration.') }}</p></div>
                            <x-ui.badge variant="light" color="gray">{{ __('Not connected') }}</x-ui.badge>
                        </div>
                        <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
                            @foreach ([__('Model endpoint'), __('Credential check'), __('Token budget')] as $item)
                                <div class="rounded-lg border border-gray-100 p-3 dark:border-gray-800"><p class="text-xs text-gray-500 dark:text-gray-400">{{ $item }}</p><p class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Demo only') }}</p></div>
                            @endforeach
                        </div>
                    </x-common.component-card>
                @endif
            </div>
        </div>
    </div>
@endsection
