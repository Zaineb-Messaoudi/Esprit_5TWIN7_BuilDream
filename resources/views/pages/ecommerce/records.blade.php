@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Ecommerce
                    @if ($type !== 'list')
                        <span aria-hidden="true">/</span> {{ $page['title'] }}
                    @endif
                </p>
                <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">{{ $page['title'] }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $page['description'] }}</p>
            </div>
            @if ($type === 'list' && !empty($page['create_route']))
                <a href="{{ route($page['create_route']) }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 focus:outline-hidden focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                    @if ($resource === 'products') Add product @else Create invoice @endif
                </a>
            @elseif ($type === 'detail' && !empty($page['detail_route']))
                <div class="flex flex-wrap gap-3">
                    @if ($resource === 'products')
                        <a href="{{ route('products.edit', ['id' => $record['id']]) }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">{{ __('Edit product') }}</a>
                    @endif
                    <a href="{{ route($resource . '.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-hidden focus:ring-2 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                        Back to {{ $page['title'] }}
                    </a>
                </div>
            @endif
        </div>

        @if ($type === 'list')
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <x-common.component-card title="Total {{ $page['title'] }}"><p class="text-2xl font-semibold text-gray-800 dark:text-white">{{ number_format(count($page['records'])) }}</p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Demo records in this view</p></x-common.component-card>
                <x-common.component-card title="Active this month"><p class="text-2xl font-semibold text-gray-800 dark:text-white">{{ number_format(max(1, count($page['records']) - 1)) }}</p><p class="mt-1 text-xs text-success-600">Illustrative sample metric</p></x-common.component-card>
                <x-common.component-card title="Data source"><p class="text-lg font-semibold text-gray-800 dark:text-white">Demo dataset</p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Not connected to a payment or inventory service</p></x-common.component-card>
            </div>

            <x-common.component-card :title="$page['title'] . ' directory'" desc="Search sample records and open a detail view">
                <div x-data="{ query: '', status: 'All' }">
                    <div class="mb-5 flex flex-col gap-3 sm:flex-row">
                        <label class="sr-only" for="records-search">Search {{ strtolower($page['title']) }}</label>
                        <input id="records-search" x-model="query" type="search" placeholder="Search records..." class="min-w-0 flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                        <label class="sr-only" for="records-status">Filter by status</label>
                        <select id="records-status" x-model="status" class="rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            <option>All</option>
                            @foreach (collect($page['records'])->pluck('status')->filter()->unique() as $status)
                                <option>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[760px] text-start text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 text-xs text-gray-400 dark:border-gray-800">
                                    <th scope="col" class="px-3 py-3 font-medium">{{ $resource === 'customers' ? 'Customer' : ($resource === 'products' ? 'Product' : ($resource === 'orders' ? 'Order' : ($resource === 'invoices' ? 'Invoice' : ($resource === 'transactions' ? 'Transaction' : 'Category')))) }}</th>
                                    @foreach ($page['columns'] as $label)
                                        <th scope="col" class="px-3 py-3 font-medium">{{ $label }}</th>
                                    @endforeach
                                    <th scope="col" class="px-3 py-3 font-medium">Status</th>
                                    @if (!empty($page['detail_route']))
                                        <th scope="col" class="px-3 py-3 text-end font-medium">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($page['records'] as $row)
                                    @php
                                        $searchText = strtolower(implode(' ', array_map('strval', $row)));
                                    @endphp
                                    <tr
                                        data-record-row
                                        data-record-search="{{ $searchText }}"
                                        data-record-status="{{ $row['status'] }}"
                                        x-show="(status === 'All' || status === $el.dataset.recordStatus) && (!query || $el.dataset.recordSearch.includes(query.toLowerCase()))"
                                        class="border-b border-gray-50 last:border-0 dark:border-gray-800"
                                    >
                                        <th scope="row" class="px-3 py-4 text-start">
                                            @if (!empty($page['detail_route']))
                                                <a href="{{ route($page['detail_route'], ['id' => $row['id']]) }}" class="font-medium text-gray-800 hover:text-brand-600 dark:text-white/90 dark:hover:text-brand-300">{{ $row['name'] }}</a>
                                            @else
                                                <span class="font-medium text-gray-800 dark:text-white/90">{{ $row['name'] }}</span>
                                            @endif
                                            <p class="mt-1 text-xs font-normal text-gray-400">{{ $row['subtitle'] }}</p>
                                        </th>
                                        @foreach ($page['columns'] as $key => $label)
                                            <td class="px-3 py-4 text-gray-600 dark:text-gray-400">{{ $row[$key] }}</td>
                                        @endforeach
                                        <td class="px-3 py-4"><x-ui.badge variant="light" :color="$row['tone']">{{ $row['status'] }}</x-ui.badge></td>
                                        @if (!empty($page['detail_route']))
                                            <td class="px-3 py-4 text-end"><a href="{{ route($page['detail_route'], ['id' => $row['id']]) }}" class="font-medium text-brand-600 hover:underline dark:text-brand-300">View</a></td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <p class="mt-4 border-t border-gray-100 pt-4 text-xs text-gray-400 dark:border-gray-800">Showing illustrative records only. Changes and totals are not persisted.</p>
                </div>
            </x-common.component-card>
        @elseif ($type === 'detail')
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <x-common.component-card :title="$record['name']" :desc="$record['subtitle']" class="xl:col-span-2">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        @foreach ($record as $key => $value)
                            @continue(in_array($key, ['id', 'name', 'subtitle', 'tone']))
                            <div class="rounded-xl border border-gray-100 p-4 dark:border-gray-800">
                                <p class="text-xs text-gray-400">{{ \Illuminate\Support\Str::headline($key) }}</p>
                                <p class="mt-2 text-sm font-medium text-gray-800 dark:text-white/90">{{ $value }}</p>
                            </div>
                        @endforeach
                    </div>
                    @if ($resource === 'products')
                        <div class="mt-5 rounded-xl bg-gray-50 p-5 dark:bg-gray-800/50">
                            <p class="text-sm font-semibold text-gray-800 dark:text-white/90">Product overview</p>
                            <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">This solar equipment record is sample content for the admin UI. Inventory and pricing are not connected to the SolarShare database.</p>
                        </div>
                    @elseif ($resource === 'orders')
                        <div class="mt-5">
                            <h2 class="mb-3 text-sm font-semibold text-gray-800 dark:text-white/90">Order items</h2>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between rounded-lg border border-gray-100 p-3 text-sm dark:border-gray-800"><span class="text-gray-600 dark:text-gray-300">Residential Solar Panel 420W <span class="text-gray-400">× 4</span></span><span class="font-medium text-gray-800 dark:text-white">$1,156.00</span></div>
                                <div class="flex items-center justify-between rounded-lg border border-gray-100 p-3 text-sm dark:border-gray-800"><span class="text-gray-600 dark:text-gray-300">Hybrid Inverter 5kW <span class="text-gray-400">× 1</span></span><span class="font-medium text-gray-800 dark:text-white">$1,249.00</span></div>
                            </div>
                        </div>
                    @endif
                </x-common.component-card>

                <div class="space-y-6">
                    <x-common.component-card title="Record status">
                        <x-ui.badge variant="light" :color="$record['tone']">{{ $record['status'] }}</x-ui.badge>
                        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">This detail page uses static sample data and does not change live records.</p>
                    </x-common.component-card>
                    <x-common.component-card title="Activity">
                        <div class="space-y-4 text-sm">
                            <div><p class="font-medium text-gray-800 dark:text-white/90">Record created</p><p class="mt-1 text-xs text-gray-400">Oct 1, 2026 · Demo activity</p></div>
                            <div><p class="font-medium text-gray-800 dark:text-white/90">Last updated</p><p class="mt-1 text-xs text-gray-400">Oct 3, 2026 · Demo activity</p></div>
                        </div>
                    </x-common.component-card>
                </div>
            </div>
        @elseif (in_array($type, ['create', 'edit'], true))
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3" x-data="{ saved: false }">
                <x-common.component-card :title="$resource === 'products' ? 'Product information' : 'Invoice information'" :desc="$resource === 'products' ? 'Enter catalog details for the demo form.' : 'Prepare a sample customer invoice.' " class="xl:col-span-2">
                    <form @submit.prevent="saved = true" class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 sm:col-span-2">
                            {{ $resource === 'products' ? 'Product name' : 'Invoice title' }}
                            <input required maxlength="120" value="{{ $type === 'edit' ? $record['name'] : '' }}" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" placeholder="{{ $resource === 'products' ? 'e.g. Residential Solar Panel 420W' : 'e.g. October installation invoice' }}" />
                        </label>
                        @if ($resource === 'products')
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">SKU<input required value="{{ $type === 'edit' ? $record['sku'] : '' }}" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" placeholder="SOL-PNL-420" /></label>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Category<select class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">@foreach (['Solar panels', 'Inverters', 'Energy storage', 'Accessories'] as $category)<option @selected($type === 'edit' && $record['category'] === $category)>{{ $category }}</option>@endforeach</select></label>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Price<input type="number" min="0" step="0.01" required value="{{ $type === 'edit' ? (float) str_replace(['$', ','], '', $record['price']) : '' }}" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" placeholder="0.00" /></label>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Stock quantity<input type="number" min="0" required value="{{ $type === 'edit' ? (int) preg_replace('/\D/', '', $record['stock']) : '' }}" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" placeholder="0" /></label>
                        @else
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Customer<select class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"><option>Amira Ben Salem</option><option>Karim Mansour</option><option>Leila Trabelsi</option><option>Omar Gharbi</option></select></label>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Due date<input type="date" required class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" /></label>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 sm:col-span-2">Amount<input type="number" min="0" step="0.01" required class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" placeholder="0.00" /></label>
                        @endif
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 sm:col-span-2">Description<textarea rows="4" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" placeholder="Add notes for this demo record...">{{ $type === 'edit' ? $record['subtitle'] : '' }}</textarea></label>
                        <div class="flex flex-wrap items-center gap-3 sm:col-span-2">
                            <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">{{ $type === 'edit' ? 'Preview product changes' : 'Save demo record' }}</button>
                            <a href="{{ route($resource . '.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Cancel</a>
                            <p x-show="saved" x-cloak role="status" class="text-sm text-success-600">{{ $type === 'edit' ? 'Product changes previewed; no data was stored.' : 'Demo form submitted; no data was stored.' }}</p>
                        </div>
                    </form>
                </x-common.component-card>
                <x-common.component-card title="Before you save">
                    <p class="text-sm leading-6 text-gray-500 dark:text-gray-400">This is a UI demonstration only. Form submissions are not stored, emailed, billed, or connected to the SolarShare catalog.</p>
                    <div class="mt-5 rounded-lg bg-warning-50 p-4 text-sm text-warning-700 dark:bg-warning-500/10 dark:text-warning-300">Do not enter real customer or payment information.</div>
                </x-common.component-card>
            </div>
        @elseif ($type === 'pricing')
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
                @foreach ($page['records'] as $plan)
                    <x-common.component-card :title="$plan['name']" :desc="$plan['description']">
                        <p class="text-4xl font-semibold text-gray-800 dark:text-white">{{ $plan['price'] }}<span class="text-sm font-normal text-gray-400"> / month</span></p>
                        <ul class="my-6 space-y-3 border-t border-gray-100 pt-5 text-sm text-gray-600 dark:border-gray-800 dark:text-gray-300">
                            @foreach ($plan['features'] as $feature)
                                <li class="flex items-center gap-2"><span class="text-success-500" aria-hidden="true">✓</span>{{ $feature }}</li>
                            @endforeach
                        </ul>
                        <a href="{{ route('billing') }}" class="inline-flex w-full justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Select {{ $plan['name'] }}</a>
                    </x-common.component-card>
                @endforeach
            </div>
            <p class="text-center text-xs text-gray-400">Illustrative plans only. No subscription is created.</p>
        @elseif ($type === 'billing')
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <x-common.component-card title="Current plan" desc="Example subscription details">
                    <x-ui.badge variant="light" color="primary">Professional</x-ui.badge>
                    <p class="mt-4 text-3xl font-semibold text-gray-800 dark:text-white">$49<span class="text-sm font-normal text-gray-400"> / month</span></p>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Next renewal · November 3, 2026</p>
                    <a href="{{ route('pricing') }}" class="mt-5 inline-flex rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">View plans</a>
                </x-common.component-card>
                <x-common.component-card title="Payment method" desc="Demo billing information">
                    <p class="font-medium text-gray-800 dark:text-white">Visa ending in 4242</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Expires 08/2028</p>
                    <p class="mt-5 text-xs text-gray-400">No payment method is stored or charged.</p>
                </x-common.component-card>
                <x-common.component-card title="Recent invoices" desc="Sample subscription receipts">
                    <div class="space-y-4 text-sm">
                        <div class="flex justify-between gap-3"><span class="text-gray-600 dark:text-gray-300">October 2026</span><span class="font-medium text-gray-800 dark:text-white">$49.00</span></div>
                        <div class="flex justify-between gap-3"><span class="text-gray-600 dark:text-gray-300">September 2026</span><span class="font-medium text-gray-800 dark:text-white">$49.00</span></div>
                        <a href="{{ route('invoices.index') }}" class="inline-flex text-brand-600 hover:underline dark:text-brand-300">View invoice examples</a>
                    </div>
                </x-common.component-card>
            </div>
            <p class="text-center text-xs text-gray-400">Billing is a visual demo and is not connected to a payment provider.</p>
        @endif
    </div>
@endsection
