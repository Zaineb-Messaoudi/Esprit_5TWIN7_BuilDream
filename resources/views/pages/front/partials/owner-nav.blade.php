@php
    $workspaceLinks = [
        ['label' => __('Overview'), 'route' => 'front.owner-dashboard', 'active' => request()->routeIs('front.owner-dashboard')],
        ['label' => __('Equipment'), 'route' => 'front.my-equipment', 'active' => request()->routeIs('front.my-equipment', 'front.my-publish', 'front.my-equipment-detail', 'front.my-equipment-edit')],
        ['label' => __('Availability'), 'route' => 'front.my-calendar', 'active' => request()->routeIs('front.my-calendar')],
        ['label' => __('Reservations'), 'route' => 'front.my-reservations', 'active' => request()->routeIs('front.my-reservations', 'front.my-reservation-detail')],
        ['label' => __('Rentals'), 'route' => 'front.my-rentals', 'active' => request()->routeIs('front.my-rentals', 'front.my-rental-detail')],
        ['label' => __('Contract'), 'route' => 'front.my-contract', 'active' => request()->routeIs('front.my-contract')],
        ['label' => __('Extensions'), 'route' => 'front.my-extensions', 'active' => request()->routeIs('front.my-extensions')],
        ['label' => __('Maintenance'), 'route' => 'front.my-maintenance', 'active' => request()->routeIs('front.my-maintenance', 'front.my-inspections')],
        ['label' => __('Inspections'), 'route' => 'front.my-inspections', 'active' => request()->routeIs('front.my-inspections')],
        ['label' => __('Earnings'), 'route' => 'front.my-earnings', 'active' => request()->routeIs('front.my-earnings')],
        ['label' => __('Notifications'), 'route' => 'front.my-notifications', 'active' => request()->routeIs('front.my-notifications')],
        ['label' => __('Profile'), 'route' => 'profile.edit', 'active' => request()->routeIs('profile.edit')],
    ];
@endphp

<nav aria-label="{{ __('Owner workspace navigation') }}" class="workspace-navigation-shell mx-4 my-5 rounded-2xl border border-gray-200 bg-white/95 shadow-theme-sm ring-1 ring-black/[0.02] dark:border-gray-800 dark:bg-gray-900 dark:ring-white/[0.03] print:hidden sm:mx-auto sm:max-w-7xl">
    <div class="flex flex-wrap items-center gap-x-1 gap-y-2 px-3 py-3 sm:px-5">
        <span class="me-2 inline-flex min-h-9 shrink-0 items-center gap-2 rounded-lg bg-brand-50 px-3 text-[10px] font-bold uppercase tracking-[0.16em] text-brand-800 dark:bg-brand-500/10 dark:text-brand-200"><span class="size-1.5 rounded-full bg-warning-500" aria-hidden="true"></span>{{ __('OWNER STUDIO') }}</span>
        @foreach ($workspaceLinks as $link)
            <a href="{{ route($link['route']) }}"
                @if ($link['active']) aria-current="page" @endif
                class="inline-flex min-h-10 shrink-0 items-center rounded-xl px-3 text-theme-xs font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 {{ $link['active'] ? 'bg-brand-700 text-white shadow-theme-xs dark:bg-brand-500' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-950 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white' }}">
                {{ $link['label'] }}
            </a>
        @endforeach
    </div>
</nav>
