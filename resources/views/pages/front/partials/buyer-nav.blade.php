@php
    $buyerLinks = [
        ['label' => __('Overview'), 'route' => 'front.my-dashboard', 'active' => request()->routeIs('front.my-dashboard')],
        ['label' => __('Reservations'), 'route' => 'front.my-reservations', 'active' => request()->routeIs('front.my-reservations', 'front.buyer-reservation-detail')],
        ['label' => __('Rentals'), 'route' => 'front.my-rentals', 'active' => request()->routeIs('front.my-rentals', 'front.buyer-rental-detail', 'front.my-contract')],
        ['label' => __('Extensions'), 'route' => 'front.my-extensions', 'active' => request()->routeIs('front.my-extensions')],
        ['label' => __('Payments & invoices'), 'route' => 'front.my-payments', 'active' => request()->routeIs('front.my-payments')],
        ['label' => __('Notifications'), 'route' => 'front.my-notifications', 'active' => request()->routeIs('front.my-notifications')],
        ['label' => __('Profile'), 'route' => 'profile.edit', 'active' => request()->routeIs('profile.edit')],
    ];
@endphp

<nav aria-label="{{ __('Buyer account navigation') }}" class="workspace-navigation-shell mx-4 my-5 rounded-2xl border border-gray-200 bg-white/95 shadow-theme-sm ring-1 ring-black/[0.02] dark:border-gray-800 dark:bg-gray-900 dark:ring-white/[0.03] print:hidden sm:mx-auto sm:max-w-7xl">
    <div class="flex flex-wrap items-center gap-x-1 gap-y-2 px-3 py-3 sm:px-5">
        <span class="me-2 inline-flex min-h-9 shrink-0 items-center gap-2 rounded-lg bg-brand-50 px-3 text-[10px] font-bold uppercase tracking-[0.16em] text-brand-800 dark:bg-brand-500/10 dark:text-brand-200"><span class="size-1.5 rounded-full bg-warning-500" aria-hidden="true"></span>{{ __('My SolarShare') }}</span>
        @foreach ($buyerLinks as $link)
            <a href="{{ route($link['route']) }}"
                @if ($link['active']) aria-current="page" @endif
                class="inline-flex min-h-10 shrink-0 items-center rounded-xl px-3 text-theme-xs font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 {{ $link['active'] ? 'bg-brand-700 text-white shadow-theme-xs dark:bg-brand-500' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-950 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white' }}">
                {{ $link['label'] }}
            </a>
        @endforeach
    </div>
</nav>
