<nav aria-label="{{ __('Language') }}" class="inline-flex min-h-11 items-center gap-1 rounded-full border border-gray-200 bg-gray-50 p-1 dark:border-gray-700 dark:bg-gray-800">
    <a href="{{ route('locale.switch', ['locale' => 'en']) }}"
        lang="en"
        @if (app()->getLocale() === 'en') aria-current="true" @endif
        class="inline-flex min-h-9 items-center justify-center rounded-full px-3 text-theme-xs font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 {{ app()->getLocale() === 'en' ? 'bg-white text-brand-800 shadow-theme-xs dark:bg-gray-700 dark:text-brand-200' : 'text-gray-600 hover:bg-white hover:text-gray-950 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}"
        aria-label="{{ __('Switch language to English') }}">EN</a>
    <a href="{{ route('locale.switch', ['locale' => 'fr']) }}"
        lang="fr"
        @if (app()->getLocale() === 'fr') aria-current="true" @endif
        class="inline-flex min-h-9 items-center justify-center rounded-full px-3 text-theme-xs font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 {{ app()->getLocale() === 'fr' ? 'bg-white text-brand-800 shadow-theme-xs dark:bg-gray-700 dark:text-brand-200' : 'text-gray-600 hover:bg-white hover:text-gray-950 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white' }}"
        aria-label="{{ __('Switch language to French') }}">FR</a>
</nav>
