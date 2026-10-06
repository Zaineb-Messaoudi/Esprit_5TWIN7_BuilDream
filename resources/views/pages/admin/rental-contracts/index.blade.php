@extends('layouts.app')

@section('content')
<div class="space-y-6">
    {{-- Breadcrumb: Dashboard / Rental Contracts --}}
    <x-common.page-breadcrumb>
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard') }}" class="text-gray-500 transition-colors hover:text-brand-600 dark:text-gray-400">Dashboard</a>
            <span class="text-gray-400">/</span>
            <span class="text-gray-800 dark:text-white">Rental Contracts</span>
        </div>
    </x-common.page-breadcrumb>

    {{-- Page title + "Create" button --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-title-md font-semibold text-gray-800 dark:text-white/90">All Contracts</h1>
        <a href="{{ route('admin.rental-contracts.create') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white shadow-theme-xs transition-colors hover:bg-brand-600">
            + Create Contract
        </a>
    </div>

    {{-- Flash message sent by the controller: 'contract-created', 'contract-updated' or 'contract-deleted' --}}
    @if (session('status'))
        <p role="status" aria-live="polite" class="rounded-lg border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-300">
            {{ match (session('status')) {
                'contract-created' => __('Contract created successfully.'),
                'contract-updated' => __('Contract updated successfully.'),
                'contract-deleted' => __('Contract deleted successfully.'),
                default => session('status'),
            } }}
        </p>
    @endif

    <x-common.component-card>
        {{-- Search + status filter (sent with GET, read in RentalContractController@index) --}}
        <div class="mb-4">
            <form method="GET" action="{{ route('admin.rental-contracts.index') }}" class="flex flex-col gap-3 sm:flex-row">
                <label class="sr-only" for="contract-search">Search contracts</label>
                <input id="contract-search" type="search" name="search" value="{{ request('search') }}" placeholder="Search contract, rental or renter..." class="min-h-10 w-full min-w-0 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white sm:max-w-72">

                <label class="sr-only" for="contract-status">Filter by status</label>
                <select id="contract-status" name="status" class="min-h-10 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-theme-xs focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white sm:w-auto">
                    <option value="">All Statuses</option>
                    @foreach (\App\Enums\ContractStatus::options() as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <div class="flex items-center gap-3">
                    <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-brand-600">Filter</button>
                    <a href="{{ route('admin.rental-contracts.index') }}" class="text-sm font-medium text-gray-500 underline-offset-4 hover:text-gray-700 hover:underline dark:text-gray-400 dark:hover:text-gray-200">Reset</a>
                </div>
            </form>
        </div>

        {{-- Table of contracts --}}
        <div class="overflow-x-auto">
            <table class="w-full min-w-[800px] border-collapse text-start">
                <thead>
                    <tr class="bg-gray-50 text-xs uppercase text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                        <th class="p-3 border-b dark:border-gray-700">Contract</th>
                        <th class="p-3 border-b dark:border-gray-700">Rental</th>
                        <th class="p-3 border-b dark:border-gray-700">Renter</th>
                        <th class="p-3 border-b dark:border-gray-700">Deposit</th>
                        <th class="p-3 border-b dark:border-gray-700">Signed at</th>
                        <th class="p-3 border-b dark:border-gray-700">Status</th>
                        <th class="p-3 border-b dark:border-gray-700 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-800 dark:text-white">
                    @forelse ($contracts as $contract)
                        <tr class="border-b transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800/50">
                            <td class="p-3 font-medium">{{ $contract->contract_number }}</td>
                            {{-- $contract->rental is the relation defined in the RentalContract model --}}
                            <td class="p-3">
                                @if ($contract->rental)
                                    <a href="{{ route('admin.rentals.show', $contract->rental) }}" class="text-brand-600 underline-offset-4 hover:underline dark:text-brand-400">{{ $contract->rental->reference }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="p-3">{{ $contract->rental?->user?->name ?? '—' }}</td>
                            <td class="p-3">{{ number_format($contract->deposit_amount, 2) }}</td>
                            <td class="p-3 text-sm">{{ $contract->signed_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="p-3">
                                {{-- The enum gives both the label and the colour of the badge --}}
                                <x-ui.badge :color="$contract->contract_status->color()">{{ $contract->contract_status->label() }}</x-ui.badge>
                            </td>
                            <td class="space-x-2 p-3 text-end">
                                <a href="{{ route('admin.rental-contracts.show', $contract) }}" class="text-sm text-gray-600 underline-offset-4 hover:underline dark:text-gray-300">View</a>
                                <a href="{{ route('admin.rental-contracts.edit', $contract) }}" class="text-sm text-brand-600 underline-offset-4 hover:underline dark:text-brand-400">Edit</a>
                                {{-- @method('DELETE') fakes the DELETE verb (browsers only send GET/POST) --}}
                                <form action="{{ route('admin.rental-contracts.destroy', $contract) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Delete this contract?')" class="text-sm text-error-600 underline-offset-4 hover:underline dark:text-error-400">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">No contracts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Page links (10 contracts per page) --}}
        <div class="mt-4">
            {{ $contracts->links() }}
        </div>
    </x-common.component-card>
</div>
@endsection