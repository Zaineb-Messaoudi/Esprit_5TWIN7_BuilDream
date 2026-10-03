@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Applications') }} / {{ __('Support tickets') }}</p>
                <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">{{ __('Support tickets') }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Ticket workflow demonstration. Updates are kept in this page only.') }}</p>
            </div>
            <button type="button" @click="$dispatch('open-ticket-form')" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">{{ __('Create ticket') }}</button>
        </div>

        <div
            x-data="{
                query: '',
                statusFilter: 'All',
                activeId: 'TKT-8842',
                reply: '',
                formOpen: false,
                newTitle: '',
                newCategory: 'Technical issue',
                newDescription: '',
                tickets: [
                    { id: 'TKT-8842', title: 'Database Connection Timeout', category: 'Technical issue', status: 'Pending', priority: 'High', created: 'Oct 2, 2026', customer: 'SolarShare Operations', description: 'I am getting a 504 Gateway Timeout when trying to access the analytics dashboard in the production environment.', assignee: 'Zaineb Messaoudi', messages: [
                        { author: 'SolarShare Operations', text: 'I have tried restarting the service but the issue persists. Any updates?', time: '10:15 AM', staff: false },
                        { author: 'Zaineb Messaoudi', text: 'We are reviewing the service logs and connection pool configuration. I will post an update here.', time: '10:30 AM', staff: true }
                    ] },
                    { id: 'TKT-8838', title: 'Invoice download request', category: 'Billing', status: 'Open', priority: 'Normal', created: 'Oct 1, 2026', customer: 'Amira Ben Salem', description: 'I need a copy of the invoice for the recent solar panel order.', assignee: 'Support team', messages: [
                        { author: 'Amira Ben Salem', text: 'Could you send me a copy of the invoice?', time: 'Yesterday', staff: false }
                    ] },
                    { id: 'TKT-8819', title: 'Product availability question', category: 'Inventory', status: 'Resolved', priority: 'Low', created: 'Sep 28, 2026', customer: 'Karim Mansour', description: 'Is the hybrid inverter available for pickup this week?', assignee: 'Support team', messages: [
                        { author: 'Karim Mansour', text: 'Checking availability for the 5kW hybrid inverter.', time: 'Sep 28', staff: false },
                        { author: 'Support team', text: 'The demo inventory shows units available in Tunis.', time: 'Sep 28', staff: true }
                    ] }
                ],
                visibleTickets() {
                    const term = this.query.trim().toLowerCase();
                    return this.tickets.filter(ticket =>
                        (this.statusFilter === 'All' || ticket.status === this.statusFilter)
                        && `${ticket.id} ${ticket.title} ${ticket.customer} ${ticket.category}`.toLowerCase().includes(term)
                    );
                },
                get activeTicket() {
                    return this.tickets.find(ticket => ticket.id === this.activeId) || null;
                },
                statusTone(status) {
                    return { Open: 'bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300', Pending: 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-300', Resolved: 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-300' }[status];
                },
                addReply() {
                    const text = this.reply.trim();
                    if (!text || !this.activeTicket) return;
                    this.activeTicket.messages.push({ author: 'You', text, time: 'Just now', staff: true });
                    if (this.activeTicket.status === 'Resolved') this.activeTicket.status = 'Open';
                    this.reply = '';
                },
                createTicket(title, category, description) {
                    const cleanTitle = title.trim();
                    const cleanDescription = description.trim();
                    if (!cleanTitle || !cleanDescription) return;
                    const id = `TKT-${Date.now().toString().slice(-6)}`;
                    this.tickets.unshift({ id, title: cleanTitle, category, status: 'Open', priority: 'Normal', created: 'Just now', customer: 'You', description: cleanDescription, assignee: 'Unassigned', messages: [] });
                    this.activeId = id;
                    this.statusFilter = 'All';
                    this.query = '';
                    this.formOpen = false;
                    this.newTitle = '';
                    this.newCategory = 'Technical issue';
                    this.newDescription = '';
                }
            }"
            @open-ticket-form.window="formOpen = true"
            class="space-y-4"
        >
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <x-common.component-card :title="__('Open tickets')"><p class="text-2xl font-semibold text-gray-800 dark:text-white" x-text="tickets.filter(ticket => ticket.status === 'Open').length"></p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Across this demo queue') }}</p></x-common.component-card>
                <x-common.component-card :title="__('Awaiting response')"><p class="text-2xl font-semibold text-gray-800 dark:text-white" x-text="tickets.filter(ticket => ticket.status === 'Pending').length"></p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Pending tickets') }}</p></x-common.component-card>
                <x-common.component-card :title="__('Resolved')"><p class="text-2xl font-semibold text-gray-800 dark:text-white" x-text="tickets.filter(ticket => ticket.status === 'Resolved').length"></p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Resolved tickets') }}</p></x-common.component-card>
            </div>

            <div class="grid grid-cols-1 items-start gap-6 xl:grid-cols-12">
                <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] xl:col-span-5" aria-label="{{ __('Ticket list') }}">
                    <div class="flex flex-col gap-3 border-b border-gray-100 p-4 dark:border-gray-800 sm:flex-row">
                        <label for="ticket-search" class="sr-only">{{ __('Search tickets') }}</label>
                        <input id="ticket-search" x-model="query" type="search" placeholder="{{ __('Search tickets...') }}" class="min-w-0 flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                        <label for="ticket-status" class="sr-only">{{ __('Filter by status') }}</label>
                        <select id="ticket-status" x-model="statusFilter" class="rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            <option value="All">{{ __('All statuses') }}</option><option value="Open">{{ __('Open') }}</option><option value="Pending">{{ __('Pending') }}</option><option value="Resolved">{{ __('Resolved') }}</option>
                        </select>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        <template x-for="ticket in visibleTickets()" :key="ticket.id">
                            <button type="button" @click="activeId = ticket.id" :aria-current="activeId === ticket.id ? 'true' : null" :class="activeId === ticket.id ? 'bg-brand-50/70 dark:bg-brand-500/10' : 'hover:bg-gray-50 dark:hover:bg-gray-800/60'" class="w-full p-4 text-start transition-colors">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400" x-text="ticket.id"></span>
                                    <span class="rounded-full px-2.5 py-1 text-[11px] font-medium" :class="statusTone(ticket.status)" x-text="ticket.status"></span>
                                </div>
                                <p class="mt-2 text-sm font-semibold text-gray-800 dark:text-white" x-text="ticket.title"></p>
                                <p class="mt-1 line-clamp-2 text-xs leading-5 text-gray-500 dark:text-gray-400" x-text="ticket.description"></p>
                                <div class="mt-3 flex items-center justify-between gap-2 text-[11px] text-gray-400"><span x-text="ticket.customer"></span><span x-text="ticket.created"></span></div>
                            </button>
                        </template>
                        <p x-show="visibleTickets().length === 0" x-cloak role="status" class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('No tickets match your filters.') }}</p>
                    </div>
                </section>

                <section class="space-y-6 xl:col-span-7" aria-label="{{ __('Ticket details') }}">
                    <template x-if="activeTicket">
                        <div class="space-y-6">
                            <x-common.component-card :title="__('Ticket details')" desc="{{ __('Selected ticket summary and routing information.') }}">
                                <div class="flex flex-wrap items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400" x-text="activeTicket.id + ' · ' + activeTicket.category"></p>
                                        <h2 class="mt-2 text-lg font-semibold text-gray-800 dark:text-white" x-text="activeTicket.title"></h2>
                                        <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300" x-text="activeTicket.description"></p>
                                    </div>
                                    <label class="shrink-0 text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Ticket status') }}
                                        <select x-model="activeTicket.status" class="mt-1 block rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                            <option value="Open">{{ __('Open') }}</option><option value="Pending">{{ __('Pending') }}</option><option value="Resolved">{{ __('Resolved') }}</option>
                                        </select>
                                    </label>
                                </div>
                                <div class="mt-5 grid grid-cols-2 gap-4 border-t border-gray-100 pt-4 dark:border-gray-800">
                                    <div><p class="text-xs text-gray-400">{{ __('Priority') }}</p><p class="mt-1 text-sm font-medium text-gray-800 dark:text-white" x-text="activeTicket.priority"></p></div>
                                    <div><p class="text-xs text-gray-400">{{ __('Assignee') }}</p><p class="mt-1 text-sm font-medium text-gray-800 dark:text-white" x-text="activeTicket.assignee"></p></div>
                                </div>
                            </x-common.component-card>

                            <x-common.component-card :title="__('Conversation history')" desc="{{ __('Messages remain local to this demonstration.') }}">
                                <div class="max-h-96 space-y-4 overflow-y-auto">
                                    <template x-for="(message, index) in activeTicket.messages" :key="activeTicket.id + '-' + index">
                                        <div :class="message.staff ? 'justify-end' : 'justify-start'" class="flex">
                                            <article :class="message.staff ? 'bg-brand-50 text-gray-700 dark:bg-brand-500/10 dark:text-gray-200' : 'bg-gray-50 text-gray-700 dark:bg-gray-800 dark:text-gray-300'" class="max-w-[90%] rounded-xl p-3 sm:max-w-[75%]">
                                                <div class="flex flex-wrap items-center justify-between gap-3"><p class="text-xs font-semibold" x-text="message.author"></p><time class="text-[10px] text-gray-400" x-text="message.time"></time></div>
                                                <p class="mt-2 whitespace-pre-line text-sm leading-6" x-text="message.text"></p>
                                            </article>
                                        </div>
                                    </template>
                                    <p x-show="activeTicket.messages.length === 0" class="py-5 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('No replies yet. Send the first update.') }}</p>
                                </div>
                                <form class="mt-5 border-t border-gray-100 pt-5 dark:border-gray-800" @submit.prevent="addReply()">
                                    <label for="ticket-reply" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Add a reply') }}</label>
                                    <textarea id="ticket-reply" x-model="reply" rows="3" required placeholder="{{ __('Write a response...') }}" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
                                    <div class="mt-3 flex justify-end"><button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">{{ __('Add reply') }}</button></div>
                                </form>
                            </x-common.component-card>
                        </div>
                    </template>
                    <div x-show="!activeTicket" class="rounded-2xl border border-gray-200 bg-white p-10 text-center dark:border-gray-800 dark:bg-white/[0.03]">
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Select a ticket to review its details.') }}</p>
                    </div>
                </section>
            </div>

            <div x-show="formOpen" x-cloak @keydown.escape.window="formOpen = false" class="fixed inset-0 z-999999 flex items-center justify-center bg-gray-900/50 p-4" role="presentation">
                <section class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white shadow-theme-lg dark:border-gray-700 dark:bg-gray-900" role="dialog" aria-modal="true" aria-labelledby="new-ticket-title" @click.stop>
                    <form class="space-y-4 p-5 sm:p-6" @submit.prevent="createTicket(newTitle, newCategory, newDescription)">
                        <h2 id="new-ticket-title" class="text-lg font-semibold text-gray-800 dark:text-white">{{ __('Create support ticket') }}</h2>
                        <label for="new-ticket-subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Subject') }}<input id="new-ticket-subject" x-model="newTitle" required class="mt-1.5 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" /></label>
                        <label for="new-ticket-category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Category') }}<select id="new-ticket-category" x-model="newCategory" class="mt-1.5 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white"><option>{{ __('Technical issue') }}</option><option>{{ __('Billing') }}</option><option>{{ __('Inventory') }}</option><option>{{ __('Other') }}</option></select></label>
                        <label for="new-ticket-description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Description') }}<textarea id="new-ticket-description" x-model="newDescription" rows="4" required class="mt-1.5 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea></label>
                        <div class="flex justify-end gap-2"><button type="button" @click="formOpen = false" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">{{ __('Cancel') }}</button><button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">{{ __('Create ticket') }}</button></div>
                        <p class="text-xs text-gray-400">{{ __('Demo only: the ticket is not saved to the server.') }}</p>
                    </form>
                </section>
            </div>
        </div>
    </div>
@endsection
