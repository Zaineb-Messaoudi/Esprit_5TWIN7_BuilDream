@auth
@php
    $aiRole = auth()->user()->isAdmin()
        ? __('administrator')
        : (auth()->user()->isOwner() ? __('equipment owner') : __('renter'));
    $aiPage = match (true) {
        request()->routeIs('front.energy-calculator*') => __('solar energy calculator'),
        request()->routeIs('front.reservation-assistant*') => __('reservation assistant'),
        request()->routeIs('front.reserve', 'front.booking-summary', 'front.payment', 'front.confirmed', 'front.invoice') => __('equipment reservation'),
        request()->routeIs('front.catalog', 'front.equipment.*') => __('equipment catalogue'),
        request()->routeIs('front.owner-*', 'technical.*') => __('owner workspace'),
        request()->routeIs('front.my-*', 'front.buyer-*') => __('renter workspace'),
        default => __($title ?? 'SolarShare'),
    };
    $aiContext = __('I am using the :page page in SolarShare. My role is :role. Help me complete the current workflow using only details I provide. Do not claim to see private account data or perform actions for me.', [
        'page' => $aiPage,
        'role' => $aiRole,
    ]);
@endphp

<div class="fixed bottom-4 end-4 z-999" x-data="{
    open: false,
    prompt: '',
    loading: false,
    error: '',
    messages: [],
    async send() {
        const question = this.prompt.trim();
        if (!question || this.loading) return;

        this.loading = true;
        this.error = '';
        this.messages.push({ role: 'user', text: question });
        this.prompt = '';

        try {
            const response = await fetch(@js(route('ai.generate')), {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': @js(csrf_token()),
                },
                body: JSON.stringify({
                    tool: 'chat',
                    prompt: @js($aiContext) + '\n\nQuestion: ' + question,
                    history: this.messages.slice(-21, -1),
                }),
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || @js(__('AI could not respond. Please try again.')));
            if (data.text) this.messages.push({ role: 'assistant', text: data.text });
            this.messages = this.messages.slice(-20);
        } catch (error) {
            this.error = error.message || @js(__('AI could not respond. Please try again.'));
            if (this.messages.at(-1)?.role === 'user') this.messages.pop();
            this.prompt = question;
        } finally {
            this.loading = false;
        }
    }
}">
    <section x-show="open" x-cloak x-transition id="platform-ai-assistant" class="mb-3 flex max-h-[min(70vh,38rem)] w-[min(24rem,calc(100vw-2rem))] flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-lg dark:border-gray-700 dark:bg-gray-900" aria-label="{{ __('AI Assistant') }}">
        <header class="flex items-start justify-between gap-3 border-b border-gray-200 p-4 dark:border-gray-800">
            <div>
                <h2 class="font-semibold text-gray-900 dark:text-white">{{ __('SolarShare AI Assistant') }}</h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('You are on the :page page. Ask about this screen or your next step.', ['page' => $aiPage]) }}</p>
            </div>
            <button type="button" @click="open = false" class="rounded-lg p-1 text-gray-500 hover:bg-gray-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:hover:bg-gray-800" aria-label="{{ __('Close') }}">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" /></svg>
            </button>
        </header>

        <div class="min-h-24 flex-1 space-y-3 overflow-y-auto p-4" aria-live="polite">
            <p x-show="messages.length === 0" class="rounded-xl bg-brand-50 p-3 text-sm leading-5 text-brand-900 dark:bg-brand-500/10 dark:text-brand-200">{{ __('I am here to help with the current SolarShare page and workflow.') }}</p>
            <template x-for="(message, index) in messages" :key="index">
                <div :class="message.role === 'user' ? 'ms-8 rounded-xl bg-brand-600 p-3 text-sm leading-5 text-white' : 'me-8 rounded-xl bg-gray-100 p-3 text-sm leading-5 text-gray-800 dark:bg-gray-800 dark:text-gray-100'" x-text="message.text"></div>
            </template>
            <p x-show="loading" x-cloak class="text-sm text-gray-500 dark:text-gray-400">{{ __('Thinking…') }}</p>
            <p x-show="error" x-cloak role="alert" class="rounded-lg bg-error-50 p-3 text-xs text-error-700 dark:bg-error-500/10 dark:text-error-300" x-text="error"></p>
        </div>

        <form @submit.prevent="send()" class="space-y-2 border-t border-gray-200 p-3 dark:border-gray-800">
            <label class="sr-only" for="platform-ai-prompt">{{ __('Ask a question') }}</label>
            <textarea id="platform-ai-prompt" x-model="prompt" rows="2" maxlength="1200" required placeholder="{{ __('Ask a question…') }}" class="w-full resize-none rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-950 dark:text-white"></textarea>
            <div class="flex items-center justify-between gap-3">
                <p class="text-[10px] leading-4 text-gray-500 dark:text-gray-400">{{ __('Messages go to Groq and are saved in your account history. Do not send passwords or payment details.') }}</p>
                <button type="submit" :disabled="loading || !prompt.trim()" class="inline-flex min-h-9 shrink-0 items-center rounded-lg bg-brand-600 px-3 text-xs font-semibold text-white hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50">
                    {{ __('Send') }}
                </button>
            </div>
            <a href="{{ route('ai.chat') }}" class="block text-xs font-medium text-brand-700 hover:underline dark:text-brand-300">{{ __('Open full AI chat') }}</a>
        </form>
    </section>

    <button type="button" @click="open = !open" :aria-expanded="open" aria-controls="platform-ai-assistant" class="inline-flex min-h-12 items-center gap-2 rounded-full bg-brand-700 px-5 text-sm font-semibold text-white shadow-theme-lg transition hover:bg-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 dark:bg-brand-500 dark:text-gray-950 dark:hover:bg-brand-400">
        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v2m0 14v2M3 12h2m14 0h2M5.6 5.6 7 7m10 10 1.4 1.4m0-12.8L17 7M7 17l-1.4 1.4"/><circle cx="12" cy="12" r="5"/><path d="M10 12h.01M14 12h.01"/></svg>
        {{ __('Ask AI') }}
    </button>
</div>
@else
<div class="fixed bottom-4 end-4 z-999" x-data="{ open: false }">
    <section x-show="open" x-cloak x-transition class="mb-3 w-[min(22rem,calc(100vw-2rem))] rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-lg dark:border-gray-700 dark:bg-gray-900" aria-label="{{ __('AI Assistant') }}">
        <h2 class="font-semibold text-gray-900 dark:text-white">{{ __('SolarShare AI Assistant') }}</h2>
        <p class="mt-2 text-sm leading-5 text-gray-600 dark:text-gray-300">{{ __('Sign in to get help from AI with this page and continue the workflow in your account.') }}</p>
        <a href="{{ route('login') }}" class="button-base button-primary mt-4 min-h-10 px-4 text-sm">{{ __('Sign in to use AI') }}</a>
    </section>
    <button type="button" @click="open = !open" :aria-expanded="open" class="inline-flex min-h-12 items-center gap-2 rounded-full bg-brand-700 px-5 text-sm font-semibold text-white shadow-theme-lg transition hover:bg-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 dark:bg-brand-500 dark:text-gray-950 dark:hover:bg-brand-400">
        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v2m0 14v2M3 12h2m14 0h2M5.6 5.6 7 7m10 10 1.4 1.4m0-12.8L17 7M7 17l-1.4 1.4"/><circle cx="12" cy="12" r="5"/><path d="M10 12h.01M14 12h.01"/></svg>
        {{ __('Ask AI') }}
    </button>
</div>
@endauth
