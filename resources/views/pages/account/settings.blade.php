@extends('layouts.app')

@section('content')
    <div
        class="space-y-6"
        x-data="{
            saved: false,
            preferences: { workspace: 'SolarShare', timeZone: 'Africa/Tunis (UTC+1)', language: 'English', dateFormat: 'DD MMM YYYY' },
            notificationPreferences: { orders: true, inventory: true, team: false, updates: false, desktop: true },
            init() {
                Object.keys(this.preferences).forEach(key => {
                    const value = localStorage.getItem('solarshare-demo-preference-' + key);
                    if (value !== null) this.preferences[key] = value;
                });
                Object.keys(this.notificationPreferences).forEach(key => {
                    const value = localStorage.getItem('solarshare-demo-notification-' + key);
                    if (value !== null) this.notificationPreferences[key] = value === 'true';
                });
            },
            savePreferences() {
                Object.entries(this.preferences).forEach(([key, value]) => {
                    localStorage.setItem('solarshare-demo-preference-' + key, value);
                });
                saved = true;
            },
            saveNotifications() {
                Object.entries(this.notificationPreferences).forEach(([key, value]) => {
                    localStorage.setItem('solarshare-demo-notification-' + key, String(value));
                });
                saved = true;
            }
        }"
    >
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Account / {{ $pageTitle }}</p>
            <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">{{ $pageTitle }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage workspace preferences and connected tools.</p>
        </div>

        <div class="grid grid-cols-1 items-start gap-6 xl:grid-cols-4">
            <nav class="rounded-2xl border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-white/[0.03]" aria-label="Account settings">
                <ul class="space-y-1">
                    @foreach ([
                        'settings' => 'General',
                        'settings.notifications' => 'Notifications',
                        'settings.security' => 'Security',
                        'api-keys' => 'API keys',
                        'integrations' => 'Integrations',
                    ] as $routeName => $label)
                        <li>
                            <a href="{{ route($routeName) }}" @class([
                                'flex rounded-lg px-3 py-2.5 text-sm font-medium transition',
                                'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300' => request()->routeIs($routeName),
                                'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' => !request()->routeIs($routeName),
                            ]) @if(request()->routeIs($routeName)) aria-current="page" @endif>{{ $label }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <div class="space-y-6 xl:col-span-3">
                @if ($section === 'general')
                    <x-common.component-card title="Workspace preferences" desc="Defaults are shown for this local UI demonstration.">
                        <form @submit.prevent="savePreferences()" class="space-y-5">
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Workspace name
                                    <input x-model="preferences.workspace" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                                </label>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Time zone
                                    <select x-model="preferences.timeZone" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white"><option>Africa/Tunis (UTC+1)</option><option>UTC</option><option>Europe/Paris</option></select>
                                </label>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Language
                                    <select x-model="preferences.language" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white"><option>English</option><option>Arabic</option><option>French</option></select>
                                </label>
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Date format
                                    <select x-model="preferences.dateFormat" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white"><option>DD MMM YYYY</option><option>MM/DD/YYYY</option><option>YYYY-MM-DD</option></select>
                                </label>
                            </div>
                            <div class="flex flex-wrap items-center gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                                <button class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Save preferences</button>
                                <p x-show="saved" x-cloak role="status" class="text-sm text-success-600">{{ __('Demo preferences saved in this browser only.') }}</p>
                            </div>
                        </form>
                    </x-common.component-card>
                    <x-common.component-card title="Regional format" desc="Preview dates and currency used in the dashboard.">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800/50"><p class="text-xs text-gray-400">Date</p><p class="mt-1 text-sm font-medium text-gray-800 dark:text-white">03 Oct 2026</p></div>
                            <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800/50"><p class="text-xs text-gray-400">Currency</p><p class="mt-1 text-sm font-medium text-gray-800 dark:text-white">TND · Tunisian Dinar</p></div>
                            <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800/50"><p class="text-xs text-gray-400">Time zone</p><p class="mt-1 text-sm font-medium text-gray-800 dark:text-white">Africa/Tunis</p></div>
                        </div>
                    </x-common.component-card>
                @elseif ($section === 'notifications')
                    <x-common.component-card title="Email notifications" desc="Choose which workspace updates should send an email.">
                        <form method="POST" action="{{ route('settings.notifications.update') }}" class="divide-y divide-gray-100 dark:divide-gray-800">
                            @csrf
                            @foreach (\App\Support\NotificationPreferenceCatalog::all() as $pref)
                                @php
                                    $key = $pref['key'];
                                    $name = $pref['name'];
                                    $description = $pref['description'];
                                @endphp
                                <label class="flex cursor-pointer items-center justify-between gap-4 py-4">
                                    <span><span class="block text-sm font-medium text-gray-800 dark:text-white/90">{{ $name }}</span><span class="mt-1 block text-xs text-gray-500 dark:text-gray-400">{{ $description }}</span></span>
                                    <input type="checkbox" name="preferences[{{ $key }}]" value="1"
                                        @checked(old("preferences.$key", $user->wantsEmail($key)))
                                        class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500" />
                                </label>
                            @endforeach
                            <div class="flex flex-wrap items-center gap-3 pt-5">
                                <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Save notification settings</button>
                                @if (session('status'))
                                    <p role="status" class="text-sm text-success-600">{{ __('Notification preferences saved.') }}</p>
                                @endif
                            </div>
                        </form>
                    </x-common.component-card>
                    <x-common.component-card title="In-app notifications">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Real-time notifications are always on: every reservation, payment, rental, extension, maintenance and return event is pushed to your notification centre and, when this page is open, to your browser.</p>
                    </x-common.component-card>
                @elseif ($section === 'security')
                    <x-common.component-card title="Password" desc="Password changes continue to use the existing Breeze authentication flow.">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div><p class="text-sm font-medium text-gray-800 dark:text-white/90">Update your password</p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Use a strong password you do not use elsewhere.</p></div>
                            <a href="{{ route('profile.edit') }}#update-password" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Manage password</a>
                        </div>
                    </x-common.component-card>
                    <x-common.component-card title="Two-factor authentication" desc="Example state only; two-factor authentication is not configured in this project.">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div><x-ui.badge variant="light" color="warning">Not enabled</x-ui.badge><p class="mt-3 text-sm text-gray-600 dark:text-gray-400">Add an extra verification step when signing in.</p></div>
                            <button type="button" @click="saved = true" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Review options</button>
                        </div>
                        <p x-show="saved" x-cloak role="status" class="mt-3 text-sm text-gray-500 dark:text-gray-400">This demo does not configure an authentication factor.</p>
                    </x-common.component-card>
                    <x-common.component-card title="Active sessions" desc="Sample sessions; session revocation is not connected here.">
                        <div class="space-y-4">
                            <div class="flex flex-wrap items-center justify-between gap-3"><div><p class="text-sm font-medium text-gray-800 dark:text-white/90">Current browser · Windows</p><p class="mt-1 text-xs text-gray-400">Tunis, Tunisia · Active now</p></div><x-ui.badge variant="light" color="success">Current</x-ui.badge></div>
                            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-4 dark:border-gray-800"><div><p class="text-sm font-medium text-gray-800 dark:text-white/90">Mobile browser · Android</p><p class="mt-1 text-xs text-gray-400">Tunis, Tunisia · 2 days ago</p></div><button type="button" class="text-sm text-error-500 hover:underline">Revoke demo session</button></div>
                        </div>
                    </x-common.component-card>
                @elseif ($section === 'api-keys')
                    <x-common.component-card title="API keys" desc="No real keys are exposed or generated by this UI demo.">
                        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Use API keys to connect trusted applications to your workspace.</p>
                            <button type="button" @click="saved = true" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Create demo key</button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[560px] text-start text-sm">
                                <thead><tr class="border-b border-gray-100 text-xs text-gray-400 dark:border-gray-800"><th class="px-3 py-3 font-medium">Name</th><th class="px-3 py-3 font-medium">Key</th><th class="px-3 py-3 font-medium">Created</th><th class="px-3 py-3 font-medium">Last used</th></tr></thead>
                                <tbody><tr class="border-b border-gray-50 dark:border-gray-800"><td class="px-3 py-4 font-medium text-gray-800 dark:text-white/90">Reporting demo</td><td class="px-3 py-4 font-mono text-xs text-gray-500">demo_••••••••••••••••</td><td class="px-3 py-4 text-gray-500">Sep 18, 2026</td><td class="px-3 py-4 text-gray-500">Never</td></tr></tbody>
                            </table>
                        </div>
                        <p x-show="saved" x-cloak role="status" class="mt-4 text-sm text-success-600">Demo key action acknowledged. No credential was generated.</p>
                    </x-common.component-card>
                    <x-common.component-card title="Keep credentials safe"><p class="text-sm leading-6 text-gray-500 dark:text-gray-400">This page contains only a masked example. Do not paste real secrets into this demo; API authentication is not implemented.</p></x-common.component-card>
                @else
                    <x-common.component-card title="Connected integrations" desc="Example integrations for the SolarShare workspace.">
                        <div class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ([
                                ['Google Workspace', 'Calendar and team collaboration', 'Connected', 'success', 'G'],
                                ['Stripe', 'Payments and subscription billing', 'Not connected', 'gray', 'S'],
                                ['Mailchimp', 'Email campaigns and audience lists', 'Not connected', 'gray', 'M'],
                                ['Slack', 'Team alerts and order notifications', 'Connected', 'success', 'S'],
                            ] as [$name, $description, $status, $tone, $icon])
                                <div class="flex flex-wrap items-center justify-between gap-4 py-4">
                                    <div class="flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-100 font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ $icon }}</span><span><span class="block text-sm font-medium text-gray-800 dark:text-white/90">{{ $name }}</span><span class="mt-1 block text-xs text-gray-500 dark:text-gray-400">{{ $description }}</span></span></div>
                                    <div class="flex items-center gap-3"><x-ui.badge variant="light" :color="$tone">{{ $status }}</x-ui.badge><button type="button" @click="saved = true" class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ $status === 'Connected' ? 'Manage' : 'Connect' }}</button></div>
                                </div>
                            @endforeach
                        </div>
                        <p x-show="saved" x-cloak role="status" class="mt-3 text-sm text-gray-500 dark:text-gray-400">Integration controls are visual examples only; no external service was contacted.</p>
                    </x-common.component-card>
                @endif
            </div>
        </div>
    </div>
@endsection
