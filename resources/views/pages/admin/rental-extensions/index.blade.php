@extends('layouts.app')

@section('content')
<div class="space-y-6">
    {{-- Breadcrumb: Dashboard / Rental Extensions --}}
    <x-common.page-breadcrumb>
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard') }}" class="text-gray-500 transition-colors hover:text-brand-600 dark:text-gray-400">Dashboard</a>
            <span class="text-gray-400">/</span>
            <span class="text-gray-800 dark:text-white">Rental Extensions</span>
        </div>
    </x-common.page-breadcrumb>

    {{-- Page title + "Create" button --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-title-md font-semibold text-gray-800 dark:text-white/90">All Extension Requests</h1>
        <a href="{{ route('admin.rental-extensions.create') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white shadow-theme-xs transition-colors hover:bg-brand-600">
            + New Request
        </a>
    </div>

    {{-- Success message sent by the controller with ->with('status', ...) --}}
    @if (session('status'))
        <p role="status" aria-live="polite" class="rounded-lg border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-300">
            {{ match (session('status')) {
                'extension-created'  => __('Extension request created successfully.'),
                'extension-updated'  => __('Extension request updated successfully.'),
                'extension-deleted'  => __('Extension request deleted successfully.'),
                'extension-approved' => __('Extension approved: the rental end date and amount were updated.'),
                'extension-rejected' => __('Extension rejected: the rental was not changed.'),
                default => session('status'),
            } }}
        </p>
    @endif

    {{-- Error message sent with ->withErrors(['extension' => ...]) --}}
    @if ($errors->has('extension'))
        <p role="alert" class="rounded-lg border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-300">{{ $errors->first('extension') }}</p>
    @endif

    <x-common.component-card>
        {{-- Search + status filter --}}
        <div class="mb-4">
            <form method="GET" action="{{ route('admin.rental-extensions.index') }}" class="flex flex-col gap-3 sm:flex-row">
                <label class="sr-only" for="extension-search">Search requests</label>
                <input id="extension-search" type="search" name="search" value="{{ request('search') }}" placeholder="Search rental or renter..." class="min-h-10 w-full min-w-0 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white sm:max-w-64">

                <label class="sr-only" for="extension-status">Filter by status</label>
                <select id="extension-status" name="status" class="min-h-10 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-theme-xs focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white sm:w-auto">
                    <option value="">All Statuses</option>
                    @foreach (\App\Enums\ExtensionStatus::options() as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <div class="flex items-center gap-3">
                    <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-brand-600">Filter</button>
                    <a href="{{ route('admin.rental-extensions.index') }}" class="text-sm font-medium text-gray-500 underline-offset-4 hover:text-gray-700 hover:underline dark:text-gray-400 dark:hover:text-gray-200">Reset</a>
                </div>
            </form>
        </div>

        {{-- Table of requests --}}
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] border-collapse text-start">
                <thead>
                    <tr class="bg-gray-50 text-xs uppercase text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                        <th class="p-3 border-b dark:border-gray-700">Rental</th>
                        <th class="p-3 border-b dark:border-gray-700">Renter</th>
                        <th class="p-3 border-b dark:border-gray-700">Requested</th>
                        <th class="p-3 border-b dark:border-gray-700">End date</th>
                        <th class="p-3 border-b dark:border-gray-700">Extra amount</th>
                        <th class="p-3 border-b dark:border-gray-700">Status</th>
                        <th class="p-3 border-b dark:border-gray-700 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-800 dark:text-white">
                    @forelse ($extensions as $extension)
                        <tr class="border-b transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800/50">
                            <td class="p-3">
                                @if ($extension->rental)
                                    <a href="{{ route('admin.rentals.show', $extension->rental) }}" class="font-medium text-brand-600 underline-offset-4 hover:underline dark:text-brand-400">{{ $extension->rental->reference }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="p-3">{{ $extension->rental?->user?->name ?? '—' }}</td>
                            <td class="p-3 text-sm">{{ $extension->requested_date->format('d/m/Y') }}</td>
                            <td class="p-3 text-sm">{{ $extension->old_end_date->format('d/m/Y') }} → {{ $extension->new_end_date->format('d/m/Y') }}</td>
                            <td class="p-3">{{ number_format($extension->additional_amount, 2) }}</td>
                            <td class="p-3">
                                <x-ui.badge :color="$extension->status->color()">{{ $extension->status->label() }}</x-ui.badge>
                            </td>
                            <td class="space-x-2 p-3 text-end whitespace-nowrap">
                                <a href="{{ route('admin.rental-extensions.show', $extension) }}" class="text-sm text-gray-600 underline-offset-4 hover:underline dark:text-gray-300">View</a>

                                {{-- Edit / Approve / Reject only make sense while the request is pending --}}
                                @if ($extension->status === \App\Enums\ExtensionStatus::PENDING)
                                    <a href="{{ route('admin.rental-extensions.edit', $extension) }}" class="text-sm text-brand-600 underline-offset-4 hover:underline dark:text-brand-400">Edit</a>

                                    <form action="{{ route('admin.rental-extensions.approve', $extension) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" onclick="return confirm('Approve this extension? The rental end date and amount will be updated.')" class="text-sm text-success-600 underline-offset-4 hover:underline dark:text-success-400">Approve</button>
                                    </form>
                                    <form action="{{ route('admin.rental-extensions.reject', $extension) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" onclick="return confirm('Reject this extension request?')" class="text-sm text-warning-600 underline-offset-4 hover:underline dark:text-warning-400">Reject</button>
                                    </form>
                                @endif

                                {{-- @method('DELETE') fakes the DELETE verb (browsers only send GET/POST) --}}
                                <form action="{{ route('admin.rental-extensions.destroy', $extension) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Delete this request?')" class="text-sm text-error-600 underline-offset-4 hover:underline dark:text-error-400">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">No extension requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Page links (10 requests per page) --}}
        <div class="mt-4">
            {{ $extensions->links() }}
        </div>
    </x-common.component-card>
</div>
@endsection