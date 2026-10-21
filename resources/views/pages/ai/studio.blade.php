@extends(auth()->user()->isAdmin() ? 'layouts.app' : 'layouts.front')

@section('content')
    <div class="space-y-6" x-data="{
        kind: @js($tool['kind']),
        prompt: @js($initialPrompt ?? ''),
        loading: false,
        error: '',
        result: '',
        mediaUrl: '',
        messages: [{ role: 'assistant', text: @js(__('Hello! I am the SolarShare AI assistant. What would you like help with?')) }],
        async submit() {
            const prompt = this.prompt.trim();
            if (!prompt || this.loading) return;
            this.loading = true;
            this.error = '';
            this.result = '';
            this.mediaUrl = '';
            if (this.kind === 'chat') this.messages.push({ role: 'user', text: prompt });
            try {
                const response = await fetch(@js(route('ai.generate')), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': @js(csrf_token()),
                    },
                    body: JSON.stringify({
                        tool: this.kind,
                        prompt,
                        history: this.kind === 'chat' ? this.messages.slice(-21, -1) : [],
                    }),
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || @js(__('The AI request could not be completed. Please try again.')));
                this.result = data.text || '';
                this.mediaUrl = data.media_url || '';
                if (this.kind === 'chat' && this.result) this.messages.push({ role: 'assistant', text: this.result });
                this.prompt = '';
            } catch (error) {
                this.error = error.message || @js(__('The AI request could not be completed. Please try again.'));
                if (this.kind === 'chat' && this.messages.at(-1)?.role === 'user') this.messages.pop();
            } finally {
                this.loading = false;
            }
        }
    }">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400"><a href="{{ route('ai.index') }}" class="hover:text-brand-600">{{ __('AI Studio') }}</a> / {{ __($tool['title']) }}</p>
                <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">{{ __($tool['title']) }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __($tool['description']) }}</p>
            </div>
            @if ($configured)
                <x-ui.badge variant="light" color="success">{{ __('AI provider connected') }}</x-ui.badge>
            @else
                <x-ui.badge variant="light" color="error">{{ __('Provider unavailable') }}</x-ui.badge>
            @endif
        </div>

        <div class="grid grid-cols-1 items-start gap-6 xl:grid-cols-4">
            <nav class="rounded-2xl border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-white/[0.03]" aria-label="{{ __('AI Studio tools') }}">
                <p class="px-3 py-2 text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('Tools') }}</p>
                <ul class="space-y-1">
                    @foreach ($tools as $key => $item)
                        <li><a href="{{ route('ai.'.$key) }}" @class([
                            'flex rounded-lg px-3 py-2.5 text-sm font-medium transition',
                            'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300' => $tool['kind'] === $item['kind'],
                            'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' => $tool['kind'] !== $item['kind'],
                        ]) @if($tool['kind'] === $item['kind']) aria-current="page" @endif>{{ __($item['title']) }}</a></li>
                    @endforeach
                    <li><a href="{{ route('ai.usage') }}" class="flex rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800">{{ __('AI usage') }}</a></li>
                    <li><a href="{{ route('dashboard.ai') }}" class="flex rounded-lg px-3 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800">{{ __('AI dashboard') }}</a></li>
                </ul>
            </nav>

            <div class="min-w-0 space-y-6 xl:col-span-3">
                @if ($tool['kind'] === 'settings')
                    <x-common.component-card :title="__('Model preferences')" :desc="__('Choose the text model and response limits for your account. The provider key is managed securely by the server.')">
                        <form method="POST" action="{{ route('ai.settings.update') }}" class="space-y-5">
                            @csrf
                            <label for="ai-model" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Text model') }}
                                <select id="ai-model" name="text_model" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    @foreach (['openai/gpt-oss-20b' => 'GPT OSS 20B (Groq)'] as $model => $label)
                                        <option value="{{ $model }}" @selected(old('text_model', $settings['text_model']) === $model)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label for="ai-max-tokens" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Maximum output tokens') }}
                                <input id="ai-max-tokens" name="max_output_tokens" type="number" min="256" max="8192" step="256" value="{{ old('max_output_tokens', $settings['max_output_tokens']) }}" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                            </label>
                            <label for="ai-temperature" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Temperature') }}
                                <input id="ai-temperature" name="temperature" type="number" min="0" max="1" step="0.1" value="{{ old('temperature', $settings['temperature']) }}" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                            </label>
                            <p class="rounded-lg bg-warning-50 px-3 py-2.5 text-xs leading-5 text-warning-700 dark:bg-warning-500/10 dark:text-warning-300">{{ __('Your text prompts are sent to Groq for processing and saved in your account history. Do not submit passwords or payment details. Image generation can use Cloudflare Workers AI; video generation requires Gemini.') }}</p>
                            <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">{{ __('Save preferences') }}</button>
                            @if (session('status') === 'ai-settings-saved')<p role="status" class="text-sm text-success-600 dark:text-success-400">{{ __('AI preferences saved.') }}</p>@endif
                            @if ($errors->any())<p role="alert" class="text-sm text-error-600">{{ $errors->first() }}</p>@endif
                        </form>
                    </x-common.component-card>
                    <x-common.component-card :title="__('Provider status')">
                        <div class="flex items-center gap-3">
                            <span @class(['size-2.5 rounded-full', 'bg-success-500' => $configured, 'bg-error-500' => ! $configured]) aria-hidden="true"></span>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $configured ? __('Groq is ready for text requests.') : __('Groq is not configured on the server.') }}</p>
                        </div>
                    </x-common.component-card>
                @elseif ($tool['kind'] === 'history')
                    <x-common.component-card :title="__('Your generation history')" :desc="__('Generated text and media are private to your account.')">
                        @if ($recentGenerations->isEmpty())
                            <x-ui.empty-state :title="__('No generations yet')" :message="__('Your completed and failed AI requests will appear here.')" />
                        @else
                            <div class="space-y-4">
                                @foreach ($recentGenerations as $generation)
                                    <article class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                                        <div class="flex flex-wrap items-start justify-between gap-3">
                                            <div class="min-w-0"><p class="font-medium text-gray-800 dark:text-white/90">{{ Str::limit($generation->prompt, 180) }}</p><p class="mt-1 text-xs text-gray-500">{{ __($generation->tool) }} · {{ $generation->model }} · {{ $generation->created_at->format('M j, Y H:i') }}</p></div>
                                            <x-ui.badge variant="light" :color="$generation->status === 'succeeded' ? 'success' : 'error'">{{ __($generation->status) }}</x-ui.badge>
                                        </div>
                                        @if ($generation->result_text)<p class="mt-3 whitespace-pre-wrap text-sm text-gray-600 dark:text-gray-300">{{ Str::limit($generation->result_text, 1200) }}</p>@endif
                                        @if ($generation->media_path)
                                            @if (str_starts_with((string) $generation->media_mime_type, 'image/'))<img src="{{ route('ai.generations.media', $generation) }}" alt="{{ __('AI generated result') }}" class="mt-4 max-h-96 rounded-lg" loading="lazy" />
                                            @else<a href="{{ route('ai.generations.media', $generation) }}" class="mt-3 inline-flex text-sm font-medium text-brand-600 hover:underline dark:text-brand-300" download>{{ __('Download generated video') }}</a>@endif
                                        @endif
                                        @if ($generation->error_message)<p class="mt-3 text-sm text-error-600">{{ $generation->error_message }}</p>@endif
                                        <form method="POST" action="{{ route('ai.generations.destroy', $generation) }}" class="mt-3">@csrf @method('DELETE')<button type="submit" class="text-xs font-medium text-gray-500 hover:text-error-600">{{ __('Delete record') }}</button></form>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </x-common.component-card>
                @elseif ($tool['kind'] === 'chat')
                    <x-common.component-card :title="__('SolarShare AI chat')" :desc="__('Messages are sent to Groq and saved in your generation history.')">
                        <div class="flex min-h-[440px] flex-col">
                            <div class="flex-1 space-y-4 overflow-y-auto p-1" aria-live="polite">
                                <template x-for="(message, index) in messages" :key="index">
                                    <div class="flex" :class="message.role === 'user' ? 'justify-end' : 'justify-start'"><div class="max-w-[85%] whitespace-pre-wrap rounded-2xl px-4 py-3 text-sm" :class="message.role === 'user' ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'" x-text="message.text"></div></div>
                                </template>
                            </div>
                            <form @submit.prevent="submit()" class="mt-5 flex items-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                                <label class="sr-only" for="chat-prompt">{{ __('Write a message') }}</label>
                                <textarea id="chat-prompt" x-model="prompt" rows="2" maxlength="1200" placeholder="{{ __('Write a message...') }}" class="min-w-0 flex-1 resize-none rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
                                <button type="submit" :disabled="loading || !prompt.trim()" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50"><span x-text="loading ? @js(__('Thinking...')) : @js(__('Send'))"></span></button>
                            </form>
                        </div>
                    </x-common.component-card>
                @else
                    <x-common.component-card :title="__($tool['title'])" :desc="__('Your prompt is sent to the configured AI provider. Results are saved privately in your account history.')">
                        <form @submit.prevent="submit()" class="space-y-5">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="ai-prompt">{{ __('Describe what you need') }}
                                <textarea id="ai-prompt" x-model="prompt" rows="5" maxlength="1200" required placeholder="{{ __('Enter a detailed prompt...') }}" class="mt-2 w-full resize-y rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
                            </label>
                            <div class="flex flex-wrap items-center gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                                <button type="submit" :disabled="loading || !prompt.trim()" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50"><span x-text="loading ? @js(__('Generating...')) : @js(__('Generate'))"></span></button>
                                <span class="text-xs text-gray-400"><span x-text="prompt.length"></span>/1200 {{ __('characters') }}</span>
                            </div>
                        </form>
                    </x-common.component-card>
                @endif

                <div x-show="error" x-cloak role="alert" class="rounded-lg border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-300" x-text="error"></div>
                <div x-show="result || mediaUrl" x-cloak class="space-y-4" aria-live="polite">
                    <x-common.component-card :title="__('Generated result')">
                        <p x-show="result" class="whitespace-pre-wrap text-sm leading-7 text-gray-700 dark:text-gray-300" x-text="result"></p>
                        <img x-show="mediaUrl && kind === 'image'" :src="mediaUrl" alt="{{ __('AI generated result') }}" class="mt-4 max-h-[600px] rounded-lg" />
                        <video x-show="mediaUrl && kind === 'video'" :src="mediaUrl" controls class="mt-4 max-h-[600px] w-full rounded-lg"></video>
                        <a x-show="mediaUrl" :href="mediaUrl" download class="mt-4 inline-flex text-sm font-medium text-brand-600 hover:underline dark:text-brand-300">{{ __('Download result') }}</a>
                    </x-common.component-card>
                    <p class="text-xs text-gray-500"><a href="{{ route('ai.history') }}" class="font-medium text-brand-600 hover:underline dark:text-brand-300">{{ __('Open generation history') }}</a></p>
                </div>
            </div>
        </div>
    </div>
@endsection
