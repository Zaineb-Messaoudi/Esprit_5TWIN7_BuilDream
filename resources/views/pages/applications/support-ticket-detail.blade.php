@extends('layouts.app')

@section('content')
    <div
        class="space-y-6"
        x-data="{
            status: 'Pending',
            reply: '',
            sent: false,
            messages: [
                { name: 'Amira Ben Salem', role: 'Customer', time: 'Today, 9:42 AM', text: 'The inverter is reporting a connection timeout. I have restarted it and checked the network connection, but the warning is still visible.' },
                { name: 'SolarShare Support', role: 'Support agent', time: 'Today, 10:06 AM', text: 'Thanks for the details. We are checking the device logs and will follow up with the next troubleshooting step.' }
            ],
            sendReply() {
                const body = this.reply.trim();
                if (!body) return;
                this.messages.push({ name: 'You', role: 'Support agent', time: 'Just now', text: body });
                this.reply = '';
                this.sent = true;
            }
        }"
    >
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Applications') }} / <a href="{{ route('app.support') }}" class="text-brand-600 hover:underline dark:text-brand-300">{{ __('Support Tickets') }}</a> / {{ __('Reply') }}</p>
                <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">{{ __('Ticket Reply') }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Review ticket details and continue the conversation. Replies are local to this demo.') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('app.support') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Back to tickets') }}</a>
                <label class="sr-only" for="detail-ticket-status">{{ __('Ticket status') }}</label>
                <select id="detail-ticket-status" x-model="status" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                    <option value="Open">{{ __('Open') }}</option><option value="Pending">{{ __('Pending') }}</option><option value="Resolved">{{ __('Resolved') }}</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 items-start gap-6 xl:grid-cols-3">
            <div class="min-w-0 space-y-6 xl:col-span-2">
                <x-common.component-card title="TKT-8838 · Inverter connection timeout" desc="Technical issue · Created October 2, 2026">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ __('Connection drops during inverter sync') }}</h2>
                            <p class="mt-2 max-w-3xl text-sm leading-6 text-gray-600 dark:text-gray-300">{{ __('The customer reports repeated connection timeouts during sync. Network restart did not resolve the issue; support is reviewing the device logs.') }}</p>
                        </div>
                        <span class="rounded-full bg-warning-50 px-2.5 py-1 text-xs font-medium text-warning-700 dark:bg-warning-500/10 dark:text-warning-300" x-text="status"></span>
                    </div>
                </x-common.component-card>

                <x-common.component-card :title="__('Conversation')" :desc="__('Customer and support replies for this sample ticket.')">
                    <ol class="space-y-5" aria-live="polite">
                        <template x-for="(message, index) in messages" :key="index">
                            <li class="flex gap-3 sm:gap-4">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-semibold" :class="message.role === 'Customer' ? 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' : 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300'" x-text="message.name.split(' ').map(part => part[0]).join('').slice(0, 2)"></span>
                                <article class="min-w-0 flex-1 rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-800/50">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div class="flex items-center gap-2"><h3 class="text-sm font-semibold text-gray-800 dark:text-white" x-text="message.name"></h3><span class="text-xs text-gray-400" x-text="message.role"></span></div>
                                        <time class="text-xs text-gray-400" x-text="message.time"></time>
                                    </div>
                                    <p class="mt-3 whitespace-pre-line text-sm leading-6 text-gray-600 dark:text-gray-300" x-text="message.text"></p>
                                </article>
                            </li>
                        </template>
                    </ol>
                    <form class="mt-6 border-t border-gray-100 pt-5 dark:border-gray-800" @submit.prevent="sendReply()">
                        <label for="support-detail-reply" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Write a reply') }}</label>
                        <textarea id="support-detail-reply" x-model="reply" rows="4" maxlength="2000" required placeholder="{{ __('Share an update with the customer...') }}" class="w-full resize-y rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
                        <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                            <p x-show="sent" x-cloak role="status" class="text-xs text-gray-500 dark:text-gray-400">{{ __('Reply added to this page only; no message was sent.') }}</p>
                            <button type="submit" class="ms-auto rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">{{ __('Send reply') }}</button>
                        </div>
                    </form>
                </x-common.component-card>
            </div>

            <aside class="space-y-6" aria-label="{{ __('Ticket metadata and actions') }}">
                <x-common.component-card :title="__('Ticket information')">
                    <dl class="space-y-4">
                        @foreach ([__('Ticket ID') => 'TKT-8838', __('Requester') => 'Amira Ben Salem', __('Email') => 'amira.bensalem@example.test', __('Category') => __('Technical issue'), __('Created') => 'October 2, 2026', __('Last response') => __('Today, 10:06 AM')] as $label => $value)
                            <div class="flex items-start justify-between gap-3"><dt class="text-xs text-gray-500 dark:text-gray-400">{{ $label }}</dt><dd class="text-end text-sm font-medium text-gray-800 dark:text-gray-200">{{ $value }}</dd></div>
                        @endforeach
                    </dl>
                </x-common.component-card>
                <x-common.component-card :title="__('Assignment and priority')">
                    <label for="ticket-assignee" class="block text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Assignee') }}</label>
                    <select id="ticket-assignee" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"><option>{{ __('Support team') }}</option><option>{{ __('Zaineb Messaoudi') }}</option><option>{{ __('Unassigned') }}</option></select>
                    <label for="ticket-priority" class="mt-4 block text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Priority') }}</label>
                    <select id="ticket-priority" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"><option>{{ __('Normal') }}</option><option>{{ __('High') }}</option><option>{{ __('Low') }}</option></select>
                    <div class="mt-5 flex flex-wrap gap-2">
                        <button type="button" @click="status = 'Pending'" class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Mark pending') }}</button>
                        <button type="button" @click="status = 'Resolved'" class="rounded-lg bg-success-50 px-3 py-2 text-xs font-medium text-success-700 hover:bg-success-100 dark:bg-success-500/10 dark:text-success-300">{{ __('Resolve ticket') }}</button>
                    </div>
                </x-common.component-card>
                <x-common.component-card :title="__('Attachments')">
                    <div class="flex items-center gap-3 rounded-lg border border-gray-100 p-3 dark:border-gray-800">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-xs font-semibold text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">PNG</span>
                        <div class="min-w-0"><p class="truncate text-sm font-medium text-gray-800 dark:text-white/90">inverter-status.png</p><p class="mt-1 text-xs text-gray-400">1.8 MB · {{ __('Sample attachment') }}</p></div>
                    </div>
                </x-common.component-card>
            </aside>
        </div>
    </div>
@endsection
