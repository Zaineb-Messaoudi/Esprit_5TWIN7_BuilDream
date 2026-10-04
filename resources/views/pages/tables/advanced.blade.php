@extends('layouts.app')

@section('content')
    <div
        class="space-y-6"
        x-data="{
            query: '',
            status: 'All statuses',
            sortKey: 'date',
            sortDirection: 'desc',
            page: 1,
            pageSize: 5,
            selected: [],
            expanded: null,
            loading: false,
            density: 'comfortable',
            striped: true,
            rows: [
                { id: 'SS-2084', customer: 'Amira Ben Salem', email: 'amira.bensalem@example.test', date: '2026-10-03', total: 6240, status: 'Processing', initials: 'AB', detail: 'Residential solar system · 4 line items' },
                { id: 'SS-2081', customer: 'Karim Mansour', email: 'karim.mansour@example.test', date: '2026-10-02', total: 1840, status: 'Shipped', initials: 'KM', detail: 'Inverter replacement · 2 line items' },
                { id: 'SS-2079', customer: 'Leila Trabelsi', email: 'leila.trabelsi@example.test', date: '2026-10-01', total: 5420, status: 'Delivered', initials: 'LT', detail: 'Home storage upgrade · 3 line items' },
                { id: 'SS-2076', customer: 'Omar Gharbi', email: 'omar.gharbi@example.test', date: '2026-09-29', total: 984, status: 'Pending payment', initials: 'OG', detail: 'Mounting accessories · 6 line items' },
                { id: 'SS-2074', customer: 'Nour Ben Ali', email: 'nour.benali@example.test', date: '2026-09-28', total: 2760, status: 'Delivered', initials: 'NB', detail: 'Solar panel bundle · 5 line items' },
                { id: 'SS-2071', customer: 'Sami Gharbi', email: 'sami.gharbi@example.test', date: '2026-09-26', total: 1249, status: 'Processing', initials: 'SG', detail: 'Hybrid inverter · 1 line item' },
                { id: 'SS-2068', customer: 'Maya Haddad', email: 'maya.haddad@example.test', date: '2026-09-24', total: 4890, status: 'Cancelled', initials: 'MH', detail: 'Home battery · 1 line item' },
                { id: 'SS-2065', customer: 'Youssef Trabelsi', email: 'youssef.trabelsi@example.test', date: '2026-09-22', total: 3180, status: 'Shipped', initials: 'YT', detail: 'Residential panel set · 8 line items' }
            ],
            filteredRows() {
                const filtered = this.rows.filter(row => {
                    const matchesSearch = `${row.id} ${row.customer} ${row.email}`.toLowerCase().includes(this.query.toLowerCase());
                    return matchesSearch && (this.status === 'All statuses' || row.status === this.status);
                });
                return filtered.sort((a, b) => {
                    const result = a[this.sortKey] > b[this.sortKey] ? 1 : (a[this.sortKey] < b[this.sortKey] ? -1 : 0);
                    return this.sortDirection === 'asc' ? result : -result;
                });
            },
            pagedRows() {
                const start = (this.page - 1) * this.pageSize;
                return this.filteredRows().slice(start, start + this.pageSize);
            },
            toggleSort(key) {
                this.sortDirection = this.sortKey === key && this.sortDirection === 'asc' ? 'desc' : 'asc';
                this.sortKey = key;
                this.page = 1;
            },
            toggleAll(event) {
                this.selected = event.target.checked ? this.pagedRows().map(row => row.id) : [];
            },
            archiveSelected() {
                this.rows.forEach(row => {
                    if (this.selected.includes(row.id)) row.status = 'Archived';
                });
                this.selected = [];
            },
            statusTone(status) {
                return {
                    Processing: 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-300',
                    Shipped: 'bg-blue-light-50 text-blue-light-600 dark:bg-blue-light-500/15 dark:text-blue-light-300',
                    Delivered: 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-300',
                    'Pending payment': 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-300',
                    Cancelled: 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-300',
                    Archived: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300'
                }[status] || 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300';
            }
        }"
    >
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Tables / Advanced Table</p>
            <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">Advanced data table</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Search, filter, sort, select rows, and inspect responsive order records.</p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <x-common.component-card title="Total orders"><p class="text-2xl font-semibold text-gray-800 dark:text-white" x-text="rows.length"></p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">In this illustrative dataset</p></x-common.component-card>
            <x-common.component-card title="Filtered results"><p class="text-2xl font-semibold text-gray-800 dark:text-white" x-text="filteredRows().length"></p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Updates as you search</p></x-common.component-card>
            <x-common.component-card title="Selected rows"><p class="text-2xl font-semibold text-gray-800 dark:text-white" x-text="selected.length"></p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Bulk actions apply to this page selection</p></x-common.component-card>
        </div>

        <x-common.component-card title="Orders" desc="Interactive table demo · no backend updates">
            <div class="space-y-4">
                <div class="flex flex-col gap-3 xl:flex-row xl:items-center">
                    <label class="sr-only" for="table-search">Search orders</label>
                    <input id="table-search" x-model="query" @input="page = 1" type="search" placeholder="Search order, customer, or email..." class="min-w-0 flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    <div class="flex flex-wrap items-center gap-2">
                        <label class="sr-only" for="table-status">Filter order status</label>
                        <select id="table-status" x-model="status" @change="page = 1" class="rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            <option>All statuses</option><option>Processing</option><option>Shipped</option><option>Delivered</option><option>Pending payment</option><option>Cancelled</option><option>Archived</option>
                        </select>
                        <label class="sr-only" for="table-density">Table density</label>
                        <select id="table-density" x-model="density" class="rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"><option value="comfortable">Comfortable</option><option value="compact">Compact</option></select>
                        <label class="inline-flex items-center gap-2 px-2 text-sm text-gray-600 dark:text-gray-300"><input type="checkbox" x-model="striped" class="rounded border-gray-300 text-brand-500 focus:ring-brand-500" /> Striped</label>
                        <button type="button" @click="loading = !loading" class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800" x-text="loading ? 'Hide loading state' : 'Preview loading'"></button>
                    </div>
                </div>

                <div x-show="selected.length > 0" x-cloak class="flex flex-wrap items-center justify-between gap-3 rounded-lg bg-brand-50 p-3 dark:bg-brand-500/10">
                    <p class="text-sm font-medium text-brand-700 dark:text-brand-300"><span x-text="selected.length"></span> orders selected</p>
                    <button type="button" @click="archiveSelected()" class="rounded-lg border border-brand-200 px-3 py-1.5 text-xs font-medium text-brand-700 hover:bg-white dark:border-brand-500/30 dark:text-brand-300 dark:hover:bg-gray-900">Archive selected</button>
                </div>

                <div x-show="loading" role="status" class="space-y-3 py-2">
                    <span class="sr-only">Loading orders</span>
                    @foreach ([1, 2, 3] as $row)
                        <div class="flex animate-pulse items-center gap-4 rounded-lg border border-gray-100 p-4 dark:border-gray-800">
                            <span class="h-9 w-9 rounded-full bg-gray-100 dark:bg-gray-800"></span>
                            <span class="h-3 flex-1 rounded bg-gray-100 dark:bg-gray-800"></span>
                            <span class="h-3 w-24 rounded bg-gray-100 dark:bg-gray-800"></span>
                        </div>
                    @endforeach
                </div>

                <div x-show="!loading" class="space-y-3 sm:hidden">
                    <template x-for="row in pagedRows()" :key="'mobile-' + row.id">
                        <article class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-50 text-xs font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-300" x-text="row.initials"></span>
                                    <div class="min-w-0"><p class="truncate font-medium text-gray-800 dark:text-white/90" x-text="row.customer"></p><p class="text-xs text-gray-400" x-text="row.id"></p></div>
                                </div>
                                <input type="checkbox" :value="row.id" x-model="selected" :aria-label="'Select order ' + row.id" class="mt-1 rounded border-gray-300 text-brand-500 focus:ring-brand-500" />
                            </div>
                            <div class="mt-4 flex items-center justify-between gap-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium" :class="statusTone(row.status)" x-text="row.status"></span>
                                <span class="font-semibold text-gray-800 dark:text-white/90" x-text="'$' + row.total.toLocaleString('en-US', { minimumFractionDigits: 2 })"></span>
                            </div>
                            <button type="button" @click="expanded = expanded === row.id ? null : row.id" class="mt-3 text-xs font-medium text-brand-600 dark:text-brand-300" x-text="expanded === row.id ? 'Hide details' : 'Show details'"></button>
                            <p x-show="expanded === row.id" x-cloak class="mt-2 text-xs text-gray-500 dark:text-gray-400" x-text="row.detail"></p>
                        </article>
                    </template>
                    <div x-show="filteredRows().length === 0" x-cloak class="py-10 text-center">
                        <p class="font-medium text-gray-700 dark:text-gray-300">No orders match these filters</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try another search or status.</p>
                    </div>
                </div>

                <div x-show="!loading" class="hidden overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800 sm:block">
                    <table class="w-full min-w-[900px] text-start text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th scope="col" class="w-12 px-4 py-3">
                                    <input type="checkbox" @change="toggleAll($event)" :checked="pagedRows().length > 0 && pagedRows().every(row => selected.includes(row.id))" aria-label="Select all orders on this page" class="rounded border-gray-300 text-brand-500 focus:ring-brand-500" />
                                </th>
                                <th scope="col" class="px-4 py-3"><button type="button" @click="toggleSort('id')" class="inline-flex items-center gap-1 font-medium text-gray-500 dark:text-gray-400">Order <span aria-hidden="true" x-text="sortKey === 'id' ? (sortDirection === 'asc' ? '↑' : '↓') : '↕'"></span></button></th>
                                <th scope="col" class="px-4 py-3"><button type="button" @click="toggleSort('customer')" class="inline-flex items-center gap-1 font-medium text-gray-500 dark:text-gray-400">Customer <span aria-hidden="true" x-text="sortKey === 'customer' ? (sortDirection === 'asc' ? '↑' : '↓') : '↕'"></span></button></th>
                                <th scope="col" class="px-4 py-3"><button type="button" @click="toggleSort('date')" class="inline-flex items-center gap-1 font-medium text-gray-500 dark:text-gray-400">Date <span aria-hidden="true" x-text="sortKey === 'date' ? (sortDirection === 'asc' ? '↑' : '↓') : '↕'"></span></button></th>
                                <th scope="col" class="px-4 py-3"><span class="font-medium text-gray-500 dark:text-gray-400">Status</span></th>
                                <th scope="col" class="px-4 py-3 text-end"><button type="button" @click="toggleSort('total')" class="ms-auto inline-flex items-center gap-1 font-medium text-gray-500 dark:text-gray-400">Total <span aria-hidden="true" x-text="sortKey === 'total' ? (sortDirection === 'asc' ? '↑' : '↓') : '↕'"></span></button></th>
                                <th scope="col" class="px-4 py-3 text-end"><span class="font-medium text-gray-500 dark:text-gray-400">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, index) in pagedRows()" :key="row.id">
                                    <tr :class="[striped && index % 2 === 1 ? 'bg-gray-50/70 dark:bg-gray-900/50' : 'bg-white dark:bg-gray-950', density === 'compact' ? 'text-xs' : 'text-sm']">
                                        <td class="px-4" :class="density === 'compact' ? 'py-2' : 'py-4'"><input type="checkbox" :value="row.id" x-model="selected" :aria-label="'Select order ' + row.id" class="rounded border-gray-300 text-brand-500 focus:ring-brand-500" /></td>
                                        <td class="px-4" :class="density === 'compact' ? 'py-2' : 'py-4'"><span class="font-medium text-gray-800 dark:text-white/90" x-text="row.id"></span></td>
                                        <td class="px-4" :class="density === 'compact' ? 'py-2' : 'py-4'">
                                            <div class="flex items-center gap-3">
                                                <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-50 text-xs font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-300" x-text="row.initials"></span>
                                                <div class="min-w-0"><p class="truncate font-medium text-gray-800 dark:text-white/90" x-text="row.customer"></p><p class="truncate text-xs text-gray-400" x-text="row.email"></p></div>
                                            </div>
                                        </td>
                                        <td class="px-4 text-gray-600 dark:text-gray-400" :class="density === 'compact' ? 'py-2' : 'py-4'" x-text="new Date(row.date + 'T12:00:00').toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })"></td>
                                        <td class="px-4" :class="density === 'compact' ? 'py-2' : 'py-4'"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium" :class="statusTone(row.status)" x-text="row.status"></span></td>
                                        <td class="px-4 text-end font-medium text-gray-800 dark:text-white/90" :class="density === 'compact' ? 'py-2' : 'py-4'" x-text="'$' + row.total.toLocaleString('en-US', { minimumFractionDigits: 2 })"></td>
                                        <td class="px-4 text-end" :class="density === 'compact' ? 'py-2' : 'py-4'">
                                            <div class="inline-flex items-center gap-2">
                                                <button type="button" @click="expanded = expanded === row.id ? null : row.id" class="rounded-md px-2 py-1 text-xs text-brand-600 hover:bg-brand-50 dark:text-brand-300 dark:hover:bg-brand-500/10" :aria-expanded="expanded === row.id" :aria-label="'Toggle details for ' + row.id">Details</button>
                                                <details class="relative">
                                                    <summary class="cursor-pointer list-none rounded-md px-2 py-1 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800" :aria-label="'Actions for ' + row.id">•••</summary>
                                                    <div class="absolute end-0 z-10 mt-1 w-36 rounded-lg border border-gray-200 bg-white p-1 shadow-theme-lg dark:border-gray-700 dark:bg-gray-900">
                                                        <button type="button" @click="expanded = row.id" class="block w-full rounded px-3 py-2 text-start text-xs text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800">View details</button>
                                                        <button type="button" @click="row.status = 'Archived'" class="block w-full rounded px-3 py-2 text-start text-xs text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800">Archive</button>
                                                    </div>
                                                </details>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr x-show="expanded === row.id" x-cloak class="border-t border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-gray-900/50">
                                        <td colspan="7" class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300"><span class="font-medium">Order details:</span> <span x-text="row.detail"></span> <span class="ms-2 text-xs text-gray-400">Contact: </span><span class="text-xs text-gray-500" x-text="row.email"></span></td>
                                    </tr>
                            </template>
                            <tr x-show="filteredRows().length === 0" x-cloak>
                                <td colspan="7" class="px-4 py-12 text-center">
                                    <p class="font-medium text-gray-700 dark:text-gray-300">No orders match these filters</p>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try another search or select a different status.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div x-show="!loading" class="flex flex-col gap-3 border-t border-gray-100 pt-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Showing <span x-text="filteredRows().length ? ((page - 1) * pageSize) + 1 : 0"></span>–<span x-text="Math.min(page * pageSize, filteredRows().length)"></span> of <span x-text="filteredRows().length"></span> orders</p>
                    <nav class="flex items-center gap-2" aria-label="Order table pagination">
                        <button type="button" @click="page = Math.max(1, page - 1); selected = []" :disabled="page === 1" class="rounded-lg border border-gray-300 px-3 py-2 text-xs text-gray-600 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-700 dark:text-gray-300">Previous</button>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Page <span x-text="page"></span> of <span x-text="Math.max(1, Math.ceil(filteredRows().length / pageSize))"></span></span>
                        <button type="button" @click="page = Math.min(Math.ceil(filteredRows().length / pageSize), page + 1); selected = []" :disabled="page >= Math.ceil(filteredRows().length / pageSize)" class="rounded-lg border border-gray-300 px-3 py-2 text-xs text-gray-600 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-700 dark:text-gray-300">Next</button>
                    </nav>
                </div>
            </div>
        </x-common.component-card>
    </div>
@endsection
