@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        {{-- Breadcrumb: Dashboard / Rentals / reference --}}
        <x-common.page-breadcrumb>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="text-gray-500 transition-colors hover:text-brand-600 dark:text-gray-400">Dashboard</a>
                <span class="text-gray-400">/</span>
                <a href="{{ route('admin.rentals.index') }}" class="text-gray-500 transition-colors hover:text-brand-600 dark:text-gray-400">Rentals</a>
                <span class="text-gray-400">/</span>
                <span class="text-gray-800 dark:text-white">{{ $rental->reference }}</span>
            </div>
        </x-common.page-breadcrumb>

        {{-- Title + actions --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-title-md font-semibold text-gray-800 dark:text-white/90">Rental {{ $rental->reference }}</h1>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.rentals.edit', $rental) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Edit</a>
                <a href="{{ route('admin.rentals.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Back to list</a>
            </div>
        </div>

        {{-- Main details of the rental --}}
        <x-common.component-card :title="__('Rental details')">
            <dl class="grid grid-cols-1 gap-5 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Renter</dt>
                    <dd class="mt-1 font-medium text-gray-800 dark:text-white">{{ $rental->user?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Equipment</dt>
                    <dd class="mt-1 font-medium text-gray-800 dark:text-white">{{ $rental->equipment_label }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Period</dt>
                    <dd class="mt-1 font-medium text-gray-800 dark:text-white">
                        {{ $rental->start_date->format('d/m/Y') }} → {{ $rental->end_date->format('d/m/Y') }}
                        {{-- diffInDays gives the number of days between the two dates --}}
                        ({{ (int) $rental->start_date->diffInDays($rental->end_date) }} day(s))
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Total amount</dt>
                    <dd class="mt-1 font-medium text-gray-800 dark:text-white">{{ number_format($rental->total_amount, 2) }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Status</dt>
                    <dd class="mt-1">
                        <x-ui.badge :color="$rental->status->color()">{{ $rental->status->label() }}</x-ui.badge>
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Reservation id</dt>
                    <dd class="mt-1 font-medium text-gray-800 dark:text-white">{{ $rental->reservation_id ?? '—' }}</dd>
                </div>
            </dl>
        </x-common.component-card>

        {{-- Contract of this rental (relation 1-1: $rental->contract, can be null) --}}
        <x-common.component-card :title="__('Contract')">
            @if ($rental->contract)
                <dl class="grid grid-cols-1 gap-5 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Contract number</dt>
                        <dd class="mt-1 font-medium text-gray-800 dark:text-white">{{ $rental->contract->contract_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Status</dt>
                        <dd class="mt-1">
                            <x-ui.badge :color="$rental->contract->contract_status->color()">{{ $rental->contract->contract_status->label() }}</x-ui.badge>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Deposit</dt>
                        <dd class="mt-1 font-medium text-gray-800 dark:text-white">{{ number_format($rental->contract->deposit_amount, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Signed at</dt>
                        <dd class="mt-1 font-medium text-gray-800 dark:text-white">{{ $rental->contract->signed_at?->format('d/m/Y H:i') ?? 'Not signed yet' }}</dd>
                    </div>
                </dl>
                {{-- Link to the full contract page --}}
                <a href="{{ route('admin.rental-contracts.show', $rental->contract) }}" class="mt-4 inline-block text-sm font-medium text-brand-600 underline-offset-4 hover:underline dark:text-brand-400">View contract</a>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">No contract for this rental yet.</p>
                {{-- Opens the contract form with this rental already selected (?rental_id=...) --}}
                <a href="{{ route('admin.rental-contracts.create', ['rental_id' => $rental->id]) }}" class="mt-4 inline-flex min-h-10 items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Create contract</a>
            @endif
        </x-common.component-card>

        {{-- Extension requests (relation 1-N: $rental->extensions, a list that can be empty) --}}
        <x-common.component-card :title="__('Extension requests')">
            {{-- Opens the request form with this rental already selected (?rental_id=...) --}}
            <div class="mb-4">
                <a href="{{ route('admin.rental-extensions.create', ['rental_id' => $rental->id]) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Request extension</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[500px] border-collapse text-start text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-xs uppercase text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                            <th class="p-3 border-b dark:border-gray-700">Requested</th>
                            <th class="p-3 border-b dark:border-gray-700">Old end date</th>
                            <th class="p-3 border-b dark:border-gray-700">New end date</th>
                            <th class="p-3 border-b dark:border-gray-700">Extra amount</th>
                            <th class="p-3 border-b dark:border-gray-700">Status</th>
                            <th class="p-3 border-b dark:border-gray-700 text-end">Details</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-800 dark:text-white">
                        @forelse ($rental->extensions as $extension)
                            <tr class="border-b dark:border-gray-700">
                                <td class="p-3">{{ $extension->requested_date->format('d/m/Y') }}</td>
                                <td class="p-3">{{ $extension->old_end_date->format('d/m/Y') }}</td>
                                <td class="p-3">{{ $extension->new_end_date->format('d/m/Y') }}</td>
                                <td class="p-3">{{ number_format($extension->additional_amount, 2) }}</td>
                                <td class="p-3">
                                    <x-ui.badge :color="$extension->status->color()">{{ $extension->status->label() }}</x-ui.badge>
                                </td>
                                <td class="p-3 text-end">
                                    <a href="{{ route('admin.rental-extensions.show', $extension) }}" class="text-sm text-brand-600 underline-offset-4 hover:underline dark:text-brand-400">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-6 text-center text-gray-500 dark:text-gray-400">No extension requests.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-common.component-card>
    </div>
@endsection