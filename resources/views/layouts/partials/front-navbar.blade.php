@php
    $publicLinks = [
        ['label' => __('Home'), 'href' => route('home'), 'active' => request()->routeIs('home'), 'icon' => 'home'],
        ['label' => __('Catalog'), 'href' => route('front.catalog'), 'active' => request()->routeIs('front.catalog'), 'icon' => 'catalog'],
        ['label' => __('How it works'), 'href' => route('front.how-it-works'), 'active' => request()->routeIs('front.how-it-works'), 'icon' => 'overview'],
        ['label' => __('Plans'), 'href' => route('front.plans'), 'active' => request()->routeIs('front.plans'), 'icon' => 'contract'],
        ['label' => __('For owners'), 'href' => route('front.owners'), 'active' => request()->routeIs('front.owners'), 'icon' => 'equipment'],
        ['label' => __('Help'), 'href' => route('front.help'), 'active' => request()->routeIs('front.help'), 'icon' => 'notifications'],
    ];
    $showWorkspaceNavigation = auth()->check()
        && ! auth()->user()->isAdmin()
        && auth()->user()->role_setup_completed;
    $links = $showWorkspaceNavigation
        ? (auth()->user()->isOwner() ? [
            ['label' => __('Home'), 'href' => route('home'), 'active' => request()->routeIs('home')],
            ['label' => __('Catalog'), 'href' => route('front.catalog'), 'active' => request()->routeIs('front.catalog')],
            ['label' => __('Owner workspace'), 'href' => route('front.owner-dashboard'), 'active' => request()->routeIs('front.owner-dashboard', 'front.my-equipment', 'front.my-publish', 'front.my-equipment-detail', 'front.my-equipment-edit', 'front.my-calendar', 'front.my-reservations', 'front.my-reservation-detail', 'front.my-rentals', 'front.my-rental-detail', 'front.my-contract', 'front.my-extensions', 'front.my-maintenance', 'front.my-inspections', 'front.my-earnings')],
        ] : [
            ['label' => __('Home'), 'href' => route('home'), 'active' => request()->routeIs('home')],
            ['label' => __('Catalog'), 'href' => route('front.catalog'), 'active' => request()->routeIs('front.catalog')],
            ['label' => __('Dashboard'), 'href' => route('front.my-dashboard'), 'active' => request()->routeIs('front.my-dashboard')],
            ['label' => __('Reservations'), 'href' => route('front.my-reservations'), 'active' => request()->routeIs('front.my-reservations')],
            ['label' => __('Rentals'), 'href' => route('front.my-rentals'), 'active' => request()->routeIs('front.my-rentals')],
            ['label' => __('Contract'), 'href' => route('front.my-contract'), 'active' => request()->routeIs('front.my-contract')],
            ['label' => __('Extensions'), 'href' => route('front.my-extensions'), 'active' => request()->routeIs('front.my-extensions')],
            ['label' => __('Notifications'), 'href' => route('front.my-notifications'), 'active' => request()->routeIs('front.my-notifications')],
            ['label' => __('Profile'), 'href' => route('profile.edit'), 'active' => request()->routeIs('profile.edit')],
        ])
        : $publicLinks;
    $sidebarGlobalLinks = [
        ['label' => __('Home'), 'href' => route('home'), 'active' => request()->routeIs('home'), 'icon' => 'home'],
        ['label' => __('Catalog'), 'href' => route('front.catalog'), 'active' => request()->routeIs('front.catalog'), 'icon' => 'catalog'],
    ];
    $sidebarWorkspaceLabel = null;
    $sidebarWorkspaceLinks = [];
    if ($showWorkspaceNavigation && auth()->user()->isOwner()) {
        $sidebarWorkspaceLabel = __('OWNER STUDIO');
        $sidebarWorkspaceLinks = [
            ['label' => __('Overview'), 'href' => route('front.owner-dashboard'), 'active' => request()->routeIs('front.owner-dashboard'), 'icon' => 'overview'],
            ['label' => __('Equipment'), 'href' => route('front.my-equipment'), 'active' => request()->routeIs('front.my-equipment', 'front.my-publish', 'front.my-equipment-detail', 'front.my-equipment-edit'), 'icon' => 'equipment'],
            ['label' => __('Availability'), 'href' => route('front.my-calendar'), 'active' => request()->routeIs('front.my-calendar'), 'icon' => 'calendar'],
            ['label' => __('Reservations'), 'href' => route('front.my-reservations'), 'active' => request()->routeIs('front.my-reservations', 'front.my-reservation-detail'), 'icon' => 'reservations'],
            ['label' => __('Rentals'), 'href' => route('front.my-rentals'), 'active' => request()->routeIs('front.my-rentals', 'front.my-rental-detail'), 'icon' => 'rentals'],
            ['label' => __('Contract'), 'href' => route('front.my-contract'), 'active' => request()->routeIs('front.my-contract'), 'icon' => 'contract'],
            ['label' => __('Extensions'), 'href' => route('front.my-extensions'), 'active' => request()->routeIs('front.my-extensions'), 'icon' => 'extensions'],
            ['label' => __('Maintenance'), 'href' => route('front.my-maintenance'), 'active' => request()->routeIs('front.my-maintenance'), 'icon' => 'maintenance'],
            ['label' => __('Inspections'), 'href' => route('front.my-inspections'), 'active' => request()->routeIs('front.my-inspections'), 'icon' => 'inspections'],
            ['label' => __('Earnings'), 'href' => route('front.my-earnings'), 'active' => request()->routeIs('front.my-earnings'), 'icon' => 'earnings'],
            ['label' => __('Notifications'), 'href' => route('front.my-notifications'), 'active' => request()->routeIs('front.my-notifications'), 'icon' => 'notifications'],
        ];
    } elseif ($showWorkspaceNavigation) {
        $sidebarWorkspaceLabel = __('My SolarShare');
        $sidebarWorkspaceLinks = [
            ['label' => __('Overview'), 'href' => route('front.my-dashboard'), 'active' => request()->routeIs('front.my-dashboard'), 'icon' => 'overview'],
            ['label' => __('Reservations'), 'href' => route('front.my-reservations'), 'active' => request()->routeIs('front.my-reservations', 'front.buyer-reservation-detail'), 'icon' => 'reservations'],
            ['label' => __('Rentals'), 'href' => route('front.my-rentals'), 'active' => request()->routeIs('front.my-rentals', 'front.buyer-rental-detail', 'front.my-contract'), 'icon' => 'rentals'],
            ['label' => __('Extensions'), 'href' => route('front.my-extensions'), 'active' => request()->routeIs('front.my-extensions'), 'icon' => 'extensions'],
            ['label' => __('Payments & invoices'), 'href' => route('front.my-payments'), 'active' => request()->routeIs('front.my-payments'), 'icon' => 'earnings'],
            ['label' => __('Notifications'), 'href' => route('front.my-notifications'), 'active' => request()->routeIs('front.my-notifications'), 'icon' => 'notifications'],
        ];
    } else {
        $sidebarGlobalLinks = $publicLinks;
    }
    $linkClass = 'inline-flex min-h-11 items-center rounded-lg px-3 py-2.5 text-theme-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500';
@endphp

<div x-data="{
        open: false,
        sidebarMode: localStorage.getItem('navigation-layout') === 'sidebar',
        toggleNavigation() {
            this.sidebarMode = !this.sidebarMode;
            this.open = false;
            localStorage.setItem('navigation-layout', this.sidebarMode ? 'sidebar' : 'top');
            document.documentElement.classList.toggle('nav-sidebar', this.sidebarMode);
            if (this.sidebarMode) {
                this.$nextTick(() => document.querySelector('#front-sidebar a, #front-sidebar button')?.focus());
            } else {
                this.$nextTick(() => this.$refs.navigationToggle.focus());
            }
        },
        closeMobileMenu() {
            if (this.open) {
                this.open = false;
                this.$nextTick(() => this.$refs.mobileNavigationToggle.focus());
            }
        },
        toggleMobileNavigation() {
            this.open = !this.open;
        }
    }" @keydown.escape.window="closeMobileMenu()">
    <header class="front-site-header sticky top-0 z-999 border-b border-gray-200 bg-white/90 backdrop-blur dark:border-gray-800 dark:bg-gray-900/90 print:hidden">
    <div class="mx-auto w-full max-w-screen-2xl px-4 sm:px-8 lg:px-10">
        <div class="flex min-h-18 items-center gap-3">
            <button type="button" @click="toggleNavigation()" :aria-pressed="sidebarMode"
                aria-label="{{ __('Use sidebar navigation') }}"
                :aria-label="sidebarMode ? @js(__('Use top navigation')) : @js(__('Use sidebar navigation'))"
                id="front-navigation-toggle" x-ref="navigationToggle"
                class="inline-flex size-10 shrink-0 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-700 transition-colors hover:border-brand-300 hover:bg-brand-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-brand-500 dark:hover:bg-brand-500/10">
                <svg x-show="!sidebarMode" class="size-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="2.5" y="3" width="15" height="14" rx="2"></rect><path d="M7.5 3v14"></path></svg>
                <svg x-show="sidebarMode" x-cloak class="size-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="2.5" y="3" width="15" height="14" rx="2"></rect><path d="M3 8h14"></path></svg>
            </button>
            <a href="{{ route('home') }}" aria-label="{{ __('SolarShare') }}" class="shrink-0 rounded-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
                <x-front.logo />
            </a>

            <nav class="front-main-navigation hidden min-w-0 flex-1 items-center justify-center gap-1 rounded-full border border-gray-200 bg-gray-50/90 p-1 dark:border-gray-800 dark:bg-gray-950/50 2xl:flex" aria-label="{{ __('Main navigation') }}">
                @foreach ($links as $link)
                    <a href="{{ $link['href'] }}" @if ($link['active']) aria-current="page" @endif
                        class="inline-flex min-h-10 shrink-0 items-center whitespace-nowrap rounded-full px-3 text-[13px] font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 {{ $link['active'] ? 'bg-brand-700 text-white shadow-theme-xs dark:bg-brand-500' : 'text-gray-600 hover:bg-white hover:text-gray-950 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white' }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="ms-auto flex shrink-0 items-center gap-2">
                <x-locale-switcher />
                <button type="button" aria-label="{{ __('Toggle theme') }}"
                x-data="{ dark: document.documentElement.classList.contains('dark') }"
                :aria-pressed="dark"
                @click="dark = !dark; document.documentElement.classList.toggle('dark', dark); document.documentElement.style.colorScheme = dark ? 'dark' : 'light'; localStorage.setItem('theme', dark ? 'dark' : 'light'); window.dispatchEvent(new CustomEvent('theme-changed', { detail: dark ? 'dark' : 'light' }))"
                class="flex size-11 items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:bg-gray-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-white/5">
                <svg x-show="!dark" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z" /></svg>
                <svg x-show="dark" x-cloak class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true"><path d="M12 2.75v2.5m0 13.5v2.5M2.75 12h2.5m13.5 0h2.5M5.46 5.46l1.77 1.77m9.54 9.54 1.77 1.77m0-13.08-1.77 1.77m-9.54 9.54-1.77 1.77M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" /></svg>
            </button>

            @auth
                <div class="relative" x-data="{ menu: false }" @click.outside="menu = false" @keydown.escape="menu = false">
                    <button type="button" @click="menu = !menu" :aria-expanded="menu" aria-controls="front-account-menu" aria-label="{{ __('Account for :name', ['name' => auth()->user()->name]) }}"
                        class="flex min-h-11 min-w-11 items-center justify-center gap-2 rounded-full focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">
                        <img src="{{ auth()->user()->profile_photo_url }}" alt="" width="44" height="44" class="size-11 rounded-full object-cover" />
                        <span class="hidden max-w-28 truncate text-theme-sm font-medium text-gray-700 dark:text-gray-200 2xl:inline">{{ auth()->user()->name }}</span>
                    </button>
                    <div id="front-account-menu" x-show="menu" x-cloak x-transition
                        class="absolute end-0 mt-3 w-64 rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark">
                        <p class="truncate text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ auth()->user()->name }}</p>
                        <p class="truncate text-theme-xs text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</p>
                        <div class="mt-3 flex flex-col gap-1 border-t border-gray-200 pt-3 dark:border-gray-800">
                            <a href="{{ route('profile.edit') }}" class="{{ $linkClass }} text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">{{ __('My profile') }}</a>
                            @can('admin-only')
                                <a href="{{ route('dashboard.ecommerce') }}" class="{{ $linkClass }} text-brand-700 hover:bg-brand-50 dark:text-brand-300 dark:hover:bg-brand-500/10">{{ __('Back Office') }}</a>
                            @else
                                <a href="{{ route(auth()->user()->isOwner() ? 'front.owner-dashboard' : 'front.my-dashboard') }}" class="{{ $linkClass }} text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">{{ __('Dashboard') }}</a>
                            @endcan
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="{{ $linkClass }} w-full text-start text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">{{ __('Sign out') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="hidden">
                    @csrf
                    <button type="submit" class="button-base border border-gray-300 px-3 text-theme-sm text-gray-700 hover:bg-gray-50 focus-visible:ring-brand-500 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/5">{{ __('Sign out') }}</button>
                </form>
            @else
                <div class="hidden items-center gap-2 sm:flex">
                    <a href="{{ route('login') }}" class="{{ $linkClass }} text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">{{ __('Sign in') }}</a>
                    <a href="{{ route('register') }}" class="button-base button-primary px-4 py-2.5 text-theme-sm">{{ __('Create account') }}</a>
                </div>
            @endauth

            <button type="button" @click="toggleMobileNavigation()" :aria-expanded="open" aria-controls="front-mobile-menu" aria-label="{{ __('Toggle menu') }}"
                id="front-navigation-toggle-mobile" x-ref="mobileNavigationToggle"
                class="flex size-11 items-center justify-center rounded-lg text-gray-600 hover:bg-gray-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-gray-300 dark:hover:bg-white/5 2xl:hidden">
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
                    <path x-show="!open" d="M4 7h16M4 12h16M4 17h16" />
                    <path x-show="open" x-cloak d="M6 6l12 12M18 6 6 18" />
                </svg>
            </button>
            </div>
        </div>
    </div>
    </header>

    <aside id="front-sidebar" class="front-sidebar-panel" role="complementary" aria-label="{{ __('Sidebar navigation') }}" :aria-hidden="!sidebarMode" :inert="!sidebarMode">
        <nav class="flex flex-col gap-1 p-3" aria-label="{{ __('Main navigation') }}">
            @foreach ($sidebarGlobalLinks as $link)
                <a href="{{ $link['href'] }}" aria-label="{{ $link['label'] }}" title="{{ $link['label'] }}" @if ($link['active']) aria-current="page" @endif
                    class="front-sidebar-link {{ $link['active'] ? 'is-active' : '' }}">
                    <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        @switch($link['icon'] ?? '')
                            @case('home')<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-6v-7h-4v7H4a1 1 0 0 1-1-1z" />@break
                            @case('catalog')<circle cx="10.5" cy="10.5" r="6.5" /><path d="m16 16 5 5M8 10h5M10.5 7.5v5" />@break
                            @case('overview')<rect x="3" y="3" width="8" height="8" rx="1.5" /><rect x="13" y="3" width="8" height="5" rx="1.5" /><rect x="13" y="10" width="8" height="11" rx="1.5" /><rect x="3" y="13" width="8" height="8" rx="1.5" />@break
                            @case('equipment')<rect x="4" y="4" width="16" height="16" rx="2" /><path d="M8 8h8M8 12h8M8 16h5" />@break
                            @case('contract')<path d="M6 3h9l4 4v14H6zM15 3v5h4M9 12h7M9 16h7" />@break
                            @default<circle cx="12" cy="12" r="8" />
                        @endswitch
                    </svg>
                    <span>{{ $link['label'] }}</span>
                </a>
            @endforeach

            @if ($sidebarWorkspaceLabel)
                <div class="front-sidebar-divider" aria-hidden="true"></div>
                <h2 class="sr-only">{{ $sidebarWorkspaceLabel }}</h2>
                @foreach ($sidebarWorkspaceLinks as $link)
                    <a href="{{ $link['href'] }}" aria-label="{{ $link['label'] }}" title="{{ $link['label'] }}" @if ($link['active']) aria-current="page" @endif
                        class="front-sidebar-link {{ $link['active'] ? 'is-active' : '' }}">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            @switch($link['icon'])
                                @case('overview')<rect x="3" y="3" width="8" height="8" rx="1.5" /><rect x="13" y="3" width="8" height="5" rx="1.5" /><rect x="13" y="10" width="8" height="11" rx="1.5" /><rect x="3" y="13" width="8" height="8" rx="1.5" />@break
                                @case('equipment')<rect x="4" y="4" width="16" height="16" rx="2" /><path d="M8 8h8M8 12h8M8 16h5" />@break
                                @case('calendar')<rect x="3" y="5" width="18" height="16" rx="2" /><path d="M16 3v4M8 3v4M3 10h18" />@break
                                @case('reservations')<path d="M7 3h10l4 4v14H3V3z" /><path d="M7 3v5h10V3M7 13h10M7 17h6" />@break
                                @case('rentals')<path d="M3 8h18v12H3zM7 8V5h10v3M3 12h18M10 12v3h4v-3" />@break
                                @case('contract')<path d="M6 3h9l4 4v14H6zM15 3v5h4M9 12h7M9 16h7" />@break
                                @case('extensions')<path d="M12 3v18M3 12h18M5.6 5.6l12.8 12.8m0-12.8L5.6 18.4" />@break
                                @case('maintenance')<path d="m14 6 4 4M4 20l5.5-1.2L20 8.3 15.7 4 5.2 14.5zM13 6l5 5" />@break
                                @case('inspections')<path d="m4 12 5 5L20 6" /><circle cx="12" cy="12" r="9" />@break
                                @case('earnings')<path d="M3 6h18v14H3zM3 10h18M7 15h4" /><path d="M7 3h10" />@break
                                @case('notifications')<path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4" />@break
                                @default<circle cx="12" cy="12" r="8" />
                            @endswitch
                        </svg>
                        <span>{{ $link['label'] }}</span>
                    </a>
                @endforeach
            @endif
        </nav>
    </aside>

    <div id="front-mobile-menu" x-show="open" x-cloak x-transition class="border-t border-gray-200 px-4 py-3 dark:border-gray-800 sm:px-8 2xl:hidden">
        <nav class="flex flex-col gap-1" aria-label="{{ __('Mobile navigation') }}">
            @foreach ($links as $link)
                <a href="{{ $link['href'] }}" @click="open = false" @if ($link['active']) aria-current="page" @endif
                    class="{{ $linkClass }} {{ $link['active'] ? 'bg-brand-50 text-brand-800 dark:bg-brand-500/15 dark:text-brand-300' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5' }}">{{ $link['label'] }}</a>
            @endforeach
            @guest
                <a href="{{ route('login') }}" class="{{ $linkClass }} text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">{{ __('Sign in') }}</a>
                <a href="{{ route('register') }}" class="button-base button-primary px-3 py-2.5 text-theme-sm">{{ __('Create account') }}</a>
            @else
                @can('admin-only')
                    <a href="{{ route('dashboard.ecommerce') }}" class="{{ $linkClass }} text-brand-700 hover:bg-brand-50 dark:text-brand-300 dark:hover:bg-brand-500/10">{{ __('Back Office') }}</a>
                @endcan
                <form method="POST" action="{{ route('logout') }}" class="px-3 py-2">
                    @csrf
                    <button type="submit" class="button-base border border-gray-300 px-4 text-theme-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/5">{{ __('Sign out') }}</button>
                </form>
            @endguest
        </nav>
    </div>
</div>
