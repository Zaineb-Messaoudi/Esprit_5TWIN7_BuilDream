@extends('layouts.app')

@section('content')
<div class="space-y-6">
    {{-- Breadcrumb: Dashboard / Rentals (same pattern as the users page) --}}
    <x-common.page-breadcrumb>
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard') }}" class="text-gray-500 transition-colors hover:text-brand-600 dark:text-gray-400">Dashboard</a>
            <span class="text-gray-400">/</span>
            <span class="text-gray-800 dark:text-white">Rentals</span>
        </div>
    </x-common.page-breadcrumb>

    {{-- Page title + "Create" button --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-title-md font-semibold text-gray-800 dark:text-white/90">All Rentals</h1>
        <a href="{{ route('admin.rentals.create') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white shadow-theme-xs transition-colors hover:bg-brand-600">
            + Create Rental
        </a>
    </div>

    {{-- Flash message: the controller sends 'rental-created', 'rental-updated' or 'rental-deleted' --}}
    @if (session('status'))
        <p role="status" aria-live="polite" class="rounded-lg border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-300">
            {{ match (session('status')) {
                'rental-created' => __('Rental created successfully.'),
                'rental-updated' => __('Rental updated successfully.'),
                'rental-deleted' => __('Rental deleted successfully.'),
                default => session('status'),
            } }}
        </p>
    @endif

    <x-common.component-card>
        {{-- Search + status filter (sent with GET, read in RentalController@index) --}}
        <div class="mb-4">
            <form method="GET" action="{{ route('admin.rentals.index') }}" class="flex flex-col gap-3 sm:flex-row">
                <label class="sr-only" for="rental-search">Search rentals</label>
                <input id="rental-search" type="search" name="search" value="{{ request('search') }}" placeholder="Search reference or renter..." class="min-h-10 w-full min-w-0 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white sm:max-w-64">

                <label class="sr-only" for="rental-status">Filter by status</label>
                <select id="rental-status" name="status" class="min-h-10 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-theme-xs focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white sm:w-auto">
                    <option value="">All Statuses</option>
                    @foreach (\App\Enums\RentalStatus::options() as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <div class="flex items-center gap-3">
                    <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-brand-600">Filter</button>
                    <a href="{{ route('admin.rentals.index') }}" class="text-sm font-medium text-gray-500 underline-offset-4 hover:text-gray-700 hover:underline dark:text-gray-400 dark:hover:text-gray-200">Reset</a>
                </div>
            </form>
        </div>

        {{-- Table of rentals --}}
        <div class="overflow-x-auto">
            <table class="w-full min-w-[800px] border-collapse text-start">
                <thead>
                    <tr class="bg-gray-50 text-xs uppercase text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                        <th class="p-3 border-b dark:border-gray-700">Reference</th>
                        <th class="p-3 border-b dark:border-gray-700">Renter</th>
                        <th class="p-3 border-b dark:border-gray-700">Equipment</th>
                        <th class="p-3 border-b dark:border-gray-700">Period</th>
                        <th class="p-3 border-b dark:border-gray-700">Amount</th>
                        <th class="p-3 border-b dark:border-gray-700">Status</th>
                        <th class="p-3 border-b dark:border-gray-700 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-800 dark:text-white">
                    @forelse ($rentals as $rental)
                        <tr class="border-b transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800/50">
                            <td class="p-3 font-medium">{{ $rental->reference }}</td>
                            {{-- $rental->user is the relation defined in the Rental model --}}
                            <td class="p-3">{{ $rental->user?->name ?? '—' }}</td>
                            {{-- equipment_label is the accessor defined in the Rental model --}}
                            <td class="p-3">{{ $rental->equipment_label }}</td>
                            <td class="p-3 text-sm">{{ $rental->start_date->format('d/m/Y') }} → {{ $rental->end_date->format('d/m/Y') }}</td>
                            <td class="p-3">{{ number_format($rental->total_amount, 2) }}</td>
                            <td class="p-3">
                                {{-- The enum gives both the label and the colour of the badge --}}
                                <x-ui.badge :color="$rental->status->color()">{{ $rental->status->label() }}</x-ui.badge>
                            </td>
                            <td class="space-x-2 p-3 text-end">
                                <a href="{{ route('admin.rentals.show', $rental) }}" class="text-sm text-gray-600 underline-offset-4 hover:underline dark:text-gray-300">View</a>
                                <a href="{{ route('admin.rentals.edit', $rental) }}" class="text-sm text-brand-600 underline-offset-4 hover:underline dark:text-brand-400">Edit</a>
                                {{-- A form is needed because browsers can only send GET/POST: @method('DELETE') fakes the DELETE verb --}}
                                <form action="{{ route('admin.rentals.destroy', $rental) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Delete this rental and its contract and extensions?')" class="text-sm text-error-600 underline-offset-4 hover:underline dark:text-error-400">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">No rentals found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Page links (10 rentals per page) --}}
        <div class="mt-4">
            {{ $rentals->links() }}
        </div>
    </x-common.component-card>
</div>
@endsection