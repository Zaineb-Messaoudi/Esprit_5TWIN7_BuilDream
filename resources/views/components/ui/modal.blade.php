@props([
    'isOpen' => false,
    'showCloseButton' => true,
    'title' => '',
    'label' => null,
    'id' => 'modal-' . \Illuminate\Support\Str::uuid(),
])

<div x-data="{
    open: @js($isOpen),
    previousOverflow: '',
    previousFocus: null,
    init() {
        this.previousOverflow = document.body.style.overflow;
        this.$watch('open', value => this.syncOpen(value));
        if (this.open) this.syncOpen(true);
    },
    syncOpen(value) {
        if (value) {
            this.previousFocus = document.activeElement;
            document.body.style.overflow = 'hidden';
            this.$nextTick(() => {
                const firstFocusable = [...this.$refs.dialog.querySelectorAll('[autofocus], button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]')].find(element => element.tabIndex >= 0);
                (firstFocusable || this.$refs.dialog).focus();
            });
        } else {
            document.body.style.overflow = this.previousOverflow;
            if (this.previousFocus instanceof HTMLElement && this.previousFocus.isConnected) {
                this.previousFocus.focus();
            }
        }
    }
}" x-show="open" x-cloak @keydown.escape.window="if (open) { open = false; $event.stopPropagation(); }"
    @keydown.tab="const focusable = [...$refs.dialog.querySelectorAll('button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]')].filter(element => element.tabIndex >= 0 && element.offsetParent !== null); const first = focusable[0]; const last = focusable[focusable.length - 1]; if (!first) { $event.preventDefault(); $refs.dialog.focus(); } else if ($event.shiftKey && (document.activeElement === first || document.activeElement === $refs.dialog)) { $event.preventDefault(); last.focus(); } else if (!$event.shiftKey && document.activeElement === last) { $event.preventDefault(); first.focus(); }"
    class="modal fixed inset-0 z-99999 flex items-center justify-center overflow-y-auto p-5"
    role="presentation"
    {{ $attributes->except('class') }}>

    <button type="button" @click="open = false" class="fixed inset-0 h-full w-full cursor-default bg-gray-400/50 backdrop-blur-[32px]" aria-label="{{ __('Close dialog') }}"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></button>

    <section x-ref="dialog" id="{{ $id }}" tabindex="-1" role="dialog" aria-modal="true" @if($title) aria-labelledby="{{ $id }}-title" @elseif($label) aria-label="{{ $label }}" @endif @click.stop class="relative w-full max-w-lg rounded-3xl bg-white dark:bg-gray-900 {{ $attributes->get('class') }}"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95">

        @if ($showCloseButton)
            <button type="button" @click="open = false"
                aria-label="{{ __('Close dialog') }}"
                class="absolute end-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition-colors hover:bg-gray-200 hover:text-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white sm:end-6 sm:top-6 sm:h-11 sm:w-11">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path fillRule="evenodd" clipRule="evenodd"
                        d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z"
                        fill="currentColor" />
                </svg>
            </button>
        @endif

        @if($title)
            <div class="px-6 py-4 border-b dark:border-gray-800">
                <h2 id="{{ $id }}-title" class="text-lg font-semibold text-gray-800 dark:text-white">{{ $title }}</h2>
            </div>
        @endif

        <div class="p-6">
            {{ $slot }}
        </div>
    </section>
</div>
