@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('UI Elements') }} / {{ __('Components') }}</p>
            <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">{{ __('Component Library') }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Reusable TailAdmin-style controls with keyboard-friendly states and dark-mode styling.') }}</p>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <x-common.component-card :title="__('Buttons and button groups')" :desc="__('Use consistent variants for primary, secondary, outline, and quiet actions.')">
                <div class="flex flex-wrap gap-3">
                    <x-ui.button>{{ __('Primary') }}</x-ui.button>
                    <x-ui.button variant="outline">{{ __('Outline') }}</x-ui.button>
                    <x-ui.button variant="ghost">{{ __('Quiet') }}</x-ui.button>
                    <x-ui.button disabled>{{ __('Disabled') }}</x-ui.button>
                </div>
                <x-ui.divider class="my-5" />
                <x-ui.button-group :label="__('View options')" value="grid" :items="[
                    ['label' => __('List'), 'value' => 'list'],
                    ['label' => __('Grid'), 'value' => 'grid'],
                ]" />
                <x-ui.divider class="my-5" />
                <x-ui.avatar-group :label="__('Team members')" :items="[
                    ['name' => 'Amira Ben Salem', 'status' => 'online'],
                    ['name' => 'Karim Mansour', 'status' => 'busy'],
                    ['name' => 'Leila Trabelsi', 'status' => 'offline'],
                    ['name' => 'Omar Gharbi'],
                    ['name' => 'Nour Ben Ali'],
                ]" :max="4" />
            </x-common.component-card>

            <x-common.component-card :title="__('Tabs')" :desc="__('A keyboard-focusable tab pattern for switching related panels.')">
                <div x-data="{ active: 'overview' }">
                    <div
                        class="flex gap-5 overflow-x-auto border-b border-gray-200 dark:border-gray-800"
                        role="tablist"
                        aria-label="{{ __('Component examples') }}"
                        @keydown="if (['ArrowRight', 'ArrowLeft'].includes($event.key)) { const tabs = [...$el.querySelectorAll('[role=tab]')]; const current = tabs.indexOf($event.target); const step = $event.key === 'ArrowRight' ? 1 : -1; const next = tabs[(current + step + tabs.length) % tabs.length]; next.focus(); next.click(); }"
                    >
                        @foreach (['overview' => __('Overview'), 'activity' => __('Activity'), 'settings' => __('Settings')] as $tab => $label)
                            <button
                                type="button"
                                role="tab"
                                id="tab-{{ $tab }}"
                                aria-controls="panel-{{ $tab }}"
                                :aria-selected="active === '{{ $tab }}'"
                                @click="active = '{{ $tab }}'"
                                :class="active === '{{ $tab }}' ? 'border-brand-500 text-brand-600 dark:text-brand-300' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                                class="-mb-px whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500"
                            >{{ $label }}</button>
                        @endforeach
                    </div>
                    <div id="panel-overview" role="tabpanel" aria-labelledby="tab-overview" x-show="active === 'overview'" class="pt-5 text-sm text-gray-600 dark:text-gray-300">{{ __('Review the current overview and key workspace indicators.') }}</div>
                    <div id="panel-activity" role="tabpanel" aria-labelledby="tab-activity" x-show="active === 'activity'" x-cloak class="pt-5 text-sm text-gray-600 dark:text-gray-300">{{ __('Recent activity appears here when your workspace has events.') }}</div>
                    <div id="panel-settings" role="tabpanel" aria-labelledby="tab-settings" x-show="active === 'settings'" x-cloak class="pt-5 text-sm text-gray-600 dark:text-gray-300">{{ __('Workspace preferences can be organized in this panel.') }}</div>
                </div>
            </x-common.component-card>

            <x-common.component-card :title="__('Accordion')" :desc="__('Expandable content with state announced to assistive technology.')">
                <div x-data="{ open: 0 }" class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach ([__('Account details'), __('Notifications'), __('Privacy')] as $index => $item)
                        <div class="py-1">
                            <h3>
                                <button type="button" @click="open = open === {{ $index }} ? -1 : {{ $index }}" :aria-expanded="open === {{ $index }}" aria-controls="accordion-panel-{{ $index }}" class="flex w-full items-center justify-between py-3 text-start text-sm font-medium text-gray-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-white/90">
                                    {{ $item }}
                                    <span aria-hidden="true" class="text-gray-400" x-text="open === {{ $index }} ? '−' : '+'"></span>
                                </button>
                            </h3>
                            <div id="accordion-panel-{{ $index }}" x-show="open === {{ $index }}" x-cloak role="region" class="pb-4 text-sm text-gray-500 dark:text-gray-400">{{ __('This panel is sample content for the reusable accordion interaction.') }}</div>
                        </div>
                    @endforeach
                </div>
            </x-common.component-card>

            <x-common.component-card :title="__('Tooltip and popover')" :desc="__('Offer short hints or lightweight supplemental content.')">
                <div class="flex flex-wrap items-center gap-5">
                    <x-ui.tooltip id="help-tooltip" :text="__('Helpful context for this control.')">
                        <button type="button" aria-describedby="help-tooltip" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('More information') }}</button>
                    </x-ui.tooltip>
                    <div x-data="{ open: false }" class="relative">
                        <button type="button" @click="open = !open" @keydown.escape.window="open = false" :aria-expanded="open" aria-controls="demo-popover" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Toggle popover') }}</button>
                        <div id="demo-popover" x-show="open" x-cloak @click.outside="open = false" class="absolute start-0 top-full z-20 mt-2 w-64 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-lg dark:border-gray-700 dark:bg-gray-900">
                            <p class="text-sm font-medium text-gray-800 dark:text-white">{{ __('Quick information') }}</p>
                            <p class="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">{{ __('Popover content stays local and closes on Escape or outside click.') }}</p>
                        </div>
                    </div>
                </div>
            </x-common.component-card>

            <x-common.component-card :title="__('Progress and loading')" :desc="__('Accessible progress indicators and inline activity feedback.')">
                <div class="space-y-5">
                    <x-ui.progress :value="72" :label="__('Storage used')" />
                    <x-ui.progress :value="42" color="success" size="sm" :label="__('Monthly goal')" />
                    <div class="flex flex-wrap items-center gap-4">
                        <x-ui.spinner size="sm" :label="__('Loading records')" />
                        <x-ui.spinner :label="__('Refreshing dashboard')">{{ __('Refreshing') }}</x-ui.spinner>
                    </div>
                </div>
            </x-common.component-card>

            <x-common.component-card :title="__('Drawer')" :desc="__('A responsive off-canvas panel with Escape and backdrop dismissal.')">
                <div x-data="{ open: false, closeDrawer() { this.open = false; this.$nextTick(() => this.$refs.trigger.focus()); } }">
                    <x-ui.button x-ref="trigger" @click="open = true; $nextTick(() => $refs.close.focus())" aria-controls="component-drawer">{{ __('Open drawer') }}</x-ui.button>
                    <div x-show="open" x-cloak @keydown.escape.window="closeDrawer()" @keydown.tab="if ($event.shiftKey && document.activeElement === $refs.close) { $event.preventDefault(); $refs.done.focus(); } else if (!$event.shiftKey && document.activeElement === $refs.done) { $event.preventDefault(); $refs.close.focus(); }" class="fixed inset-0 z-999999" role="presentation">
                        <button type="button" @click="closeDrawer()" class="absolute inset-0 h-full w-full bg-gray-900/50" aria-label="{{ __('Close drawer') }}"></button>
                        <aside id="component-drawer" x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="ltr:translate-x-full rtl:-translate-x-full" x-transition:enter-end="translate-x-0" class="absolute end-0 top-0 flex h-full w-full max-w-sm flex-col bg-white shadow-theme-lg dark:bg-gray-900" role="dialog" aria-modal="true" aria-labelledby="drawer-title">
                            <div class="flex items-center justify-between border-b border-gray-200 p-5 dark:border-gray-800">
                                <h2 id="drawer-title" class="font-semibold text-gray-800 dark:text-white">{{ __('Drawer panel') }}</h2>
                                <button x-ref="close" type="button" @click="closeDrawer()" class="rounded p-2 text-gray-500 hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:hover:bg-gray-800" aria-label="{{ __('Close drawer') }}">×</button>
                            </div>
                            <p class="p-5 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ __('Use drawers for contextual details, filters, or quick actions on narrow screens.') }}</p>
                            <div class="mt-auto border-t border-gray-200 p-5 dark:border-gray-800"><x-ui.button x-ref="done" @click="closeDrawer()">{{ __('Done') }}</x-ui.button></div>
                        </aside>
                    </div>
                </div>
            </x-common.component-card>

            <x-common.component-card :title="__('Modal')" :desc="__('A dialog with Escape/backdrop dismissal, a focus trap, and focus restoration.')">
                <div x-data>
                    <x-ui.button @click="$dispatch('open-component-modal')">{{ __('Open modal') }}</x-ui.button>
                    <x-ui.modal
                        @open-component-modal.window="open = true"
                        :title="__('Accessible modal example')"
                        id="component-modal"
                        class="max-w-lg"
                    >
                        <p class="text-sm leading-6 text-gray-600 dark:text-gray-300">{{ __('Use the keyboard to move through the dialog controls. Focus stays in the modal until it is closed.') }}</p>
                        <div class="mt-5 flex justify-end">
                            <x-ui.button variant="outline" @click="open = false">{{ __('Close') }}</x-ui.button>
                        </div>
                    </x-ui.modal>
                </div>
            </x-common.component-card>

            <x-common.component-card :title="__('Pagination and stepper')" :desc="__('Compact navigation for tables and multi-step workflows.')">
                <div x-data="{ page: 1 }" class="space-y-6">
                    <nav class="flex flex-wrap items-center justify-between gap-3" aria-label="{{ __('Pagination') }}">
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Page') }} <span x-text="page"></span> {{ __('of 5') }}</p>
                        <div class="inline-flex items-center gap-1">
                            <button type="button" @click="page = Math.max(1, page - 1)" :disabled="page === 1" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 disabled:cursor-not-allowed disabled:opacity-40 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:border-gray-700 dark:text-gray-300" aria-label="{{ __('Previous page') }}">‹</button>
                            <template x-for="number in [1, 2, 3]" :key="number">
                                <button type="button" @click="page = number" :aria-current="page === number ? 'page' : null" :class="page === number ? 'bg-brand-500 text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'" class="h-9 min-w-9 rounded-lg px-2 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500" x-text="number"></button>
                            </template>
                            <button type="button" @click="page = Math.min(5, page + 1)" :disabled="page === 5" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 disabled:cursor-not-allowed disabled:opacity-40 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:border-gray-700 dark:text-gray-300" aria-label="{{ __('Next page') }}">›</button>
                        </div>
                    </nav>
                    <x-ui.divider />
                    <ol class="flex flex-wrap items-center gap-3 text-sm" aria-label="{{ __('Example steps') }}">
                        @foreach ([__('Details'), __('Review'), __('Complete')] as $index => $step)
                            <li class="inline-flex items-center gap-2">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full {{ $index === 0 ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' }}">{{ $index + 1 }}</span>
                                <span class="text-gray-600 dark:text-gray-300">{{ $step }}</span>
                                @if ($index < 2)<span class="text-gray-300 dark:text-gray-700" aria-hidden="true">/</span>@endif
                            </li>
                        @endforeach
                    </ol>
                </div>
            </x-common.component-card>

            <x-common.component-card :title="__('Tags and search')" :desc="__('Reusable filters and labelled search inputs.')">
                <div class="space-y-5">
                    <x-form.date-picker
                        id="component-date-selector"
                        :label="__('Date selector')"
                        :placeholder="__('Choose a date')"
                        name="component-date"
                    />
                    <x-ui.search-box name="component-search" :label="__('Search records')" :placeholder="__('Search records...')" />
                    <x-ui.filter
                        name="component-status"
                        :label="__('Status filter')"
                        :value="'all'"
                        :options="['all' => __('All statuses'), 'active' => __('Active'), 'pending' => __('Pending'), 'archived' => __('Archived')]"
                    />
                    <div class="flex flex-wrap gap-2">
                        <x-ui.tag color="primary">{{ __('Solar panels') }}</x-ui.tag>
                        <x-ui.tag color="success" :removable="true">{{ __('Active') }}</x-ui.tag>
                        <x-ui.tag color="warning">{{ __('Pending review') }}</x-ui.tag>
                        <x-ui.tag color="error">{{ __('Needs attention') }}</x-ui.tag>
                    </div>
                </div>
            </x-common.component-card>

            <x-common.component-card :title="__('Notifications and toast')" :desc="__('Status messages with accessible announcement and dismiss controls.')">
                <div class="space-y-3">
                    <x-ui.notification
                        :title="__('New order received')"
                        :message="__('A sample order has been added to the demo activity feed.')"
                        :time="__('2 minutes ago')"
                        :unread="true"
                    />
                    <x-ui.toast
                        :title="__('Changes saved')"
                        :message="__('Your demo preferences were updated locally.')"
                    />
                </div>
            </x-common.component-card>

            <x-common.component-card :title="__('Timeline')" :desc="__('A vertical sequence for activity and audit history.')">
                <x-ui.timeline :items="[
                    ['title' => __('Order placed'), 'description' => __('Demo order SS-2084 was created.'), 'time' => __('Today, 10:24 AM'), 'color' => 'bg-brand-500'],
                    ['title' => __('Payment confirmed'), 'description' => __('Payment state shown for demonstration only.'), 'time' => __('Today, 10:26 AM'), 'color' => 'bg-success-500'],
                    ['title' => __('Preparing shipment'), 'description' => __('The sample order is ready for dispatch.'), 'time' => __('Today, 11:10 AM'), 'color' => 'bg-warning-500'],
                ]" />
            </x-common.component-card>

            <x-common.component-card :title="__('Reusable interactive patterns')" :desc="__('Standalone components for common navigation and disclosure controls.')">
                <div class="space-y-6">
                    <x-ui.tabs :tabs="[
                        'summary' => ['label' => __('Summary'), 'content' => __('A reusable tab panel with keyboard arrow navigation.')],
                        'details' => ['label' => __('Details'), 'content' => __('Each tab panel is labelled and associated with its trigger.')],
                    ]" />
                    <x-ui.accordion :items="[
                        ['title' => __('When should I use a drawer?'), 'content' => __('Use a drawer for contextual actions or details that should not navigate away.')],
                        ['title' => __('Can these patterns be customized?'), 'content' => __('Pass labels, content, and component attributes to match the page context.')],
                    ]" />
                    <div class="flex flex-wrap items-start gap-4">
                        <x-ui.popover :trigger="__('Quick information')">
                            <p class="text-sm font-medium text-gray-800 dark:text-white">{{ __('Local popover') }}</p>
                            <p class="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">{{ __('Dismiss with Escape or by clicking outside.') }}</p>
                        </x-ui.popover>
                        <x-ui.drawer :title="__('Reusable drawer')" :trigger="__('Open reusable drawer')">
                            <p class="text-sm leading-6 text-gray-500 dark:text-gray-400">{{ __('Responsive drawer content can be supplied through the component slot.') }}</p>
                        </x-ui.drawer>
                    </div>
                    <x-ui.pagination :pages="5" :current="2" />
                    <x-ui.stepper :steps="[__('Details'), __('Review'), __('Complete')]" :current="2" />
                </div>
            </x-common.component-card>

            <x-common.component-card :title="__('Content primitives')" :desc="__('Accessible wrappers for icons, images, links, and structured lists.')">
                <div class="space-y-6">
                    <div class="flex items-center gap-3">
                        <x-ui.icon size="lg" :label="__('Solar energy')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-full w-full"><circle cx="12" cy="12" r="4" /><path stroke-linecap="round" d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42" /></svg>
                        </x-ui.icon>
                        <span class="text-sm text-gray-600 dark:text-gray-300">{{ __('Icon wrapper with accessible label') }}</span>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-ui.list :items="[
                            ['title' => __('Product summary'), 'description' => __('Structured item with supporting text.')],
                            ['title' => __('Inventory status'), 'description' => __('List dividers adapt to light and dark themes.')],
                        ]" />
                        <div>
                            <x-ui.image :src="asset('images/product/product-01.jpg')" :alt="__('Solar panel product example')" ratio="video" class="rounded-xl border border-gray-200 dark:border-gray-700" />
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('Responsive image with descriptive alternative text') }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-4">
                        <x-ui.link route-name="faq.index">{{ __('Visit the help center') }}</x-ui.link>
                        <x-ui.link route-name="faq.index" variant="button">{{ __('Open help center') }}</x-ui.link>
                        <x-ui.link href="https://example.com" external variant="muted">{{ __('External link example') }}</x-ui.link>
                    </div>
                </div>
            </x-common.component-card>

            <x-common.component-card :title="__('Carousel')" :desc="__('Responsive, keyboard-accessible image carousel.')">
                <x-ui.carousel
                    :label="__('Carousel component preview')"
                    :slides="[
                        ['image' => '/images/carousel/carousel-01.png', 'alt' => __('Solar panel installation'), 'title' => __('Clean energy')],
                        ['image' => '/images/carousel/carousel-02.png', 'alt' => __('Renewable energy products'), 'title' => __('Built for your home')],
                    ]"
                />
                <a href="{{ route('ui.carousel') }}" class="mt-4 inline-flex text-sm font-medium text-brand-600 hover:underline dark:text-brand-300">{{ __('View carousel examples') }}</a>
            </x-common.component-card>

            <x-common.component-card :title="__('Ribbon')" :desc="__('Reusable status and featured-content labels.')">
                <x-ui.ribbon :label="__('Featured')" color="brand">
                    <p class="text-sm font-semibold text-gray-800 dark:text-white">{{ __('A ribbon wraps any card content.') }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Choose color, corner, and appearance with component properties.') }}</p>
                </x-ui.ribbon>
                <a href="{{ route('ui.ribbons') }}" class="mt-4 inline-flex text-sm font-medium text-brand-600 hover:underline dark:text-brand-300">{{ __('View ribbon examples') }}</a>
            </x-common.component-card>

            <x-common.component-card :title="__('Sidebar variants')" :desc="__('Classic, sectioned, documentation, collapsible, nested, and toggle navigation previews.')">
                <p class="text-sm leading-6 text-gray-500 dark:text-gray-400">{{ __('Compare six patterns without changing the active application sidebar.') }}</p>
                <a href="{{ route('layouts.sidebar-variants') }}" class="mt-4 inline-flex text-sm font-medium text-brand-600 hover:underline dark:text-brand-300">{{ __('Explore sidebar variants') }}</a>
            </x-common.component-card>
        </div>
    </div>
@endsection
