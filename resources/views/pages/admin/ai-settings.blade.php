@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb>
        <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-brand-600">{{ __('Dashboard') }}</a>
        / <a href="{{ route('admin.categories.index') }}" class="text-gray-500 hover:text-brand-600">{{ __('Admin') }}</a>
        / {{ __('AI Configuration') }}
    </x-common.page-breadcrumb>

    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-title-md font-semibold text-gray-800 dark:text-white">{{ __('AI / LLM Configuration') }}</h1>
        @if($ollamaHealth && $ollamaHealth['available'])
            <x-ui.badge variant="light" color="success">{{ __('Ollama Connected') }}</x-ui.badge>
        @elseif($ollamaHealth && !$ollamaHealth['available'])
            <x-ui.badge variant="light" color="error">{{ __('Ollama Disconnected') }}</x-ui.badge>
        @else
            <x-ui.badge variant="light" color="warning">{{ __('Cloud Provider') }}</x-ui.badge>
        @endif
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr_380px]">
        <!-- Main Form -->
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Provider Settings') }}</h2>

            <form method="POST" action="{{ route('admin.ai-settings.update') }}" class="space-y-6" x-data="aiSettingsForm()">
                @csrf

                <!-- Provider Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('AI Provider') }}</label>
                    <select name="provider" x-model="provider" @change="updateProviderModels()" class="w-full min-h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        @foreach($providers as $key => $label)
                            <option value="{{ $key }}" {{ $config['provider'] === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-show="provider === 'ollama'">FREE - Runs locally on your machine via Ollama</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-show="provider === 'openai'">Paid - Requires OpenAI API key</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-show="provider === 'anthropic'">Paid - Requires Anthropic API key</p>
                </div>

                <!-- Base URL -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('API Base URL') }}</label>
                    <input type="url" name="base_url" :value="config.base_url" x-model="config.base_url" placeholder="http://localhost:11434" class="w-full min-h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Default: http://localhost:11434 for Ollama</p>
                </div>

                <!-- Model Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Chat Model') }}</label>
                    <select name="model" x-model="config.model" class="w-full min-h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <template x-for="model in availableModels" :key="model.value">
                            <option :value="model.value" :selected="config.model === model.value" x-text="model.label"></option>
                        </template>
                    </select>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-show="provider === 'ollama'">Must be pulled first: <code>ollama pull llama3.2</code></p>
                </div>

                <!-- Embedding Model -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Embedding Model (Semantic Search)') }}</label>
                    <select name="embedding_model" x-model="config.embedding_model" class="w-full min-h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <template x-for="model in embeddingModels" :key="model.value">
                            <option :value="model.value" :selected="config.embedding_model === model.value" x-text="model.label"></option>
                        </template>
                    </select>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-show="provider === 'ollama'">For semantic search. Pull first: <code>ollama pull nomic-embed-text</code></p>
                </div>

                <!-- Vision Model -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Vision Model (Image Validation)') }}</label>
                    <select name="vision_model" x-model="config.vision_model" class="w-full min-h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <template x-for="model in visionModels" :key="model.value">
                            <option :value="model.value" :selected="config.vision_model === model.value" x-text="model.label"></option>
                        </template>
                    </select>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-show="provider === 'ollama'">For image validation. Pull first: <code>ollama pull llava</code></p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-show="provider === 'openai'">gpt-4o-mini, gpt-4o support vision</p>
                </div>

                <!-- API Key (only for cloud providers) -->
                <div x-show="provider !== 'ollama'">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('API Key') }}</label>
                    <div class="relative">
                        <input type="password" name="api_key" :value="config.api_key" x-model="config.api_key" autocomplete="new-password" placeholder="Enter API key" class="w-full min-h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white pr-10">
                        <button type="button" @click="toggleApiKey()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <svg x-show="!showApiKey" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="showApiKey" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Required for OpenAI/Anthropic. Stored in .env file.</p>
                </div>

                <!-- Generation Options -->
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Temperature') }} <span x-text="config.temperature.toFixed(1)"></span></label>
                        <input type="range" name="temperature" min="0" max="2" step="0.1" x-model.number="config.temperature" class="w-full accent-brand-500">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Lower = more deterministic, Higher = more creative</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Max Tokens') }} <span x-text="config.max_tokens"></span></label>
                        <input type="range" name="max_tokens" min="256" max="8192" step="256" x-model.number="config.max_tokens" class="w-full accent-brand-500">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Maximum response length</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-wrap gap-3 border-t border-gray-100 pt-4 dark:border-gray-800">
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                        {{ __('Save Configuration') }}
                    </button>
                    <button type="button" @click="testConnection()" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:border-brand-400 hover:text-brand-700 dark:border-gray-700 dark:text-gray-200" :disabled="testing">
                        <span x-show="!testing">{{ __('Test Connection') }}</span>
                        <span x-show="testing">{{ __('Testing...') }}</span>
                    </button>
                </div>
            </form>
        </section>

        <!-- Sidebar: Status & Actions -->
        <aside class="space-y-4">
            <!-- Connection Status -->
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">{{ __('Connection Status') }}</h3>
                
                <div class="space-y-3" x-data="{ health: @json($ollamaHealth ?? ['available' => false, 'reason' => 'Not checked']) }">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('Provider') }}</span>
                        <span class="font-medium" x-text="provider"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('Model') }}</span>
                        <span class="font-medium" x-text="config.model"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('Embedding Model') }}</span>
                        <span class="font-medium" x-text="config.embedding_model"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('Status') }}</span>
                        <template x-if="health.available">
                            <x-ui.badge variant="light" color="success">{{ __('Connected') }}</x-ui.badge>
                        </template>
                        <template x-if="!health.available">
                            <x-ui.badge variant="light" color="error">{{ __('Disconnected') }}</x-ui.badge>
                        </template>
                    </div>
                    <template x-if="health.reason && !health.available">
                        <p class="text-xs text-error-600 dark:text-error-400" x-text="health.reason"></p>
                    </template>
                    <template x-if="health.models && health.models.length">
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Available models') }}: <span x-text="health.models.join(', ')"></span></p>
                    </template>
                </div>
            </section>

            <!-- Ollama Model Management -->
            <section x-show="provider === 'ollama'" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">{{ __('Ollama Model Management') }}</h3>
                
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Pull New Model') }}</label>
                        <div class="flex gap-2">
                            <input type="text" x-model="newModel" placeholder="e.g. llama3.1, mistral, phi3" class="flex-1 min-h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <button @click="pullModel()" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600" :disabled="pulling">
                                <span x-show="!pulling">{{ __('Pull') }}</span>
                                <span x-show="pulling">{{ __('Pulling...') }}</span>
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Downloads model from Ollama registry. Large models may take several minutes.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Available Locally') }}</label>
                        <div class="max-h-40 overflow-y-auto space-y-1" x-data="{ localModels: @json($ollamaHealth['models'] ?? []) }">
                            <template x-for="model in localModels" :key="model">
                                <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-white/5 rounded-lg">
                                    <span class="text-sm font-mono" x-text="model"></span>
                                    <template x-if="model === config.model">
                                        <x-ui.badge variant="light" size="xs" color="brand">{{ __('Active') }}</x-ui.badge>
                                    </template>
                                </div>
                            </template>
                            <template x-if="!localModels.length">
                                <p class="text-sm text-gray-500 dark:text-gray-400 p-2">{{ __('No models pulled yet') }}</p>
                            </template>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Quick Help -->
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-3">{{ __('Quick Start') }}</h3>
                <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    <li class="flex gap-2"><span class="flex-shrink-0 w-5 h-5 rounded-full bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300 grid place-items-center text-xs font-bold">1</span> Install Ollama: <code class="bg-gray-100 dark:bg-gray-800 px-1.5 rounded">curl -fsSL https://ollama.com/install.sh | sh</code></li>
                    <li class="flex gap-2"><span class="flex-shrink-0 w-5 h-5 rounded-full bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300 grid place-items-center text-xs font-bold">2</span> Pull a model: <code class="bg-gray-100 dark:bg-gray-800 px-1.5 rounded">ollama pull llama3.2</code></li>
                    <li class="flex gap-2"><span class="flex-shrink-0 w-5 h-5 rounded-full bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300 grid place-items-center text-xs font-bold">3</span> For embeddings: <code class="bg-gray-100 dark:bg-gray-800 px-1.5 rounded">ollama pull nomic-embed-text</code></li>
                    <li class="flex gap-2"><span class="flex-shrink-0 w-5 h-5 rounded-full bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300 grid place-items-center text-xs font-bold">4</span> Set provider to <strong>Ollama</strong> above and save</li>
                    <li class="flex gap-2"><span class="flex-shrink-0 w-5 h-5 rounded-full bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300 grid place-items-center text-xs font-bold">5</span> Click <strong>Test Connection</strong></li>
                </ol>
            </section>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
function aiSettingsForm() {
    return {
        provider: '@js($config["provider"])',
        config: @json($config),
        availableModels: @json($availableModelsFormatted),
        embeddingModels: [
            { value: 'nomic-embed-text', label: 'Nomic Embed Text (768 dims, recommended)' },
            { value: 'mxbai-embed-large', label: 'MXBAI Embed Large (1024 dims)' },
            { value: 'all-minilm', label: 'All-MiniLM (384 dims, fast)' },
            { value: 'text-embedding-3-small', label: 'OpenAI text-embedding-3-small (1536 dims)' },
        ],
        visionModels: [
            { value: 'llava', label: 'LLaVA (general purpose, recommended)' },
            { value: 'llava-llama3', label: 'LLaVA-Llama3 (LLaVA with Llama 3)' },
            { value: 'llava-mistral', label: 'LLaVA-Mistral (LLaVA with Mistral)' },
            { value: 'bakllava', label: 'BakLLaVA' },
            { value: 'gpt-4o-mini', label: 'GPT-4o Mini (OpenAI vision)' },
            { value: 'gpt-4o', label: 'GPT-4o (OpenAI vision)' },
        ],
        showApiKey: false,
        testing: false,
        pulling: false,
        newModel: '',

        updateProviderModels() {
            // Update available models based on provider
            const models = {
                ollama: [
                    { value: 'llama3.2', label: 'Llama 3.2 (3B, fast, recommended)' },
                    { value: 'llama3.1', label: 'Llama 3.1 (8B, better quality)' },
                    { value: 'mistral', label: 'Mistral 7B (good balance)' },
                    { value: 'phi3', label: 'Phi-3 Mini (3.8B, Microsoft)' },
                    { value: 'qwen2.5', label: 'Qwen 2.5 (multilingual)' },
                    { value: 'gemma2', label: 'Gemma 2 (Google, 9B)' },
                ],
                openai: [
                    { value: 'gpt-4o-mini', label: 'GPT-4o Mini (fast, cheap)' },
                    { value: 'gpt-4o', label: 'GPT-4o (best quality)' },
                    { value: 'gpt-3.5-turbo', label: 'GPT-3.5 Turbo (legacy)' },
                ],
                anthropic: [
                    { value: 'claude-3-haiku', label: 'Claude 3 Haiku (fast)' },
                    { value: 'claude-3-sonnet', label: 'Claude 3 Sonnet (balanced)' },
                    { value: 'claude-3-opus', label: 'Claude 3 Opus (best)' },
                ],
            };
            this.availableModels = models[this.provider] || [];
            if (this.availableModels.length && !this.availableModels.find(m => m.value === this.config.model)) {
                this.config.model = this.availableModels[0].value;
            }
        },

        toggleApiKey() {
            this.showApiKey = !this.showApiKey;
        },

        async testConnection() {
            this.testing = true;
            try {
                const response = await fetch('{{ route("admin.ai-settings.test") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                });
                const data = await response.json();
                alert(data.available ? '✅ Connection successful!\n' + (data.response || '') : '❌ Connection failed: ' + (data.message || 'Unknown error'));
            } catch (e) {
                alert('❌ Error: ' + e.message);
            } finally {
                this.testing = false;
            }
        },

        async pullModel() {
            if (!this.newModel.trim()) return;
            this.pulling = true;
            try {
                const response = await fetch('{{ route("admin.ai-settings.pull-model") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({ model: this.newModel }),
                });
                const data = await response.json();
                if (data.success) {
                    alert('✅ Model pulled successfully!');
                    this.newModel = '';
                    // Refresh health to show new model
                    const healthResponse = await fetch('{{ route("admin.ai-settings.test") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                    });
                    const health = await healthResponse.json();
                    this.$nextTick(() => {
                        // Update the health display
                        document.querySelector('[x-data]')?.__x.$data.health = health;
                    });
                } else {
                    alert('❌ Failed: ' + data.message);
                }
            } catch (e) {
                alert('❌ Error: ' + e.message);
            } finally {
                this.pulling = false;
            }
        },
    };
}
</script>
@endpush