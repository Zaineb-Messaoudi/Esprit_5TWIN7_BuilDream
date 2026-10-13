@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        {{-- Breadcrumb: Dashboard / Rental Extensions / Request details --}}
        <x-common.page-breadcrumb>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="text-gray-500 transition-colors hover:text-brand-600 dark:text-gray-400">Dashboard</a>
                <span class="text-gray-400">/</span>
                <a href="{{ route('admin.rental-extensions.index') }}" class="text-gray-500 transition-colors hover:text-brand-600 dark:text-gray-400">Rental Extensions</a>
                <span class="text-gray-400">/</span>
                <span class="text-gray-800 dark:text-white">Request #{{ $extension->id }}</span>
            </div>
        </x-common.page-breadcrumb>

        {{-- Success / error messages sent by the controller --}}
        @if (session('status'))
            <p role="status" aria-live="polite" class="rounded-lg border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-300">
                {{ match (session('status')) {
                    'extension-approved' => __('Extension approved: the rental end date and amount were updated.'),
                    'extension-rejected' => __('Extension rejected: the rental was not changed.'),
                    default => session('status'),
                } }}
            </p>
        @endif
        @if ($errors->has('extension'))
            <p role="alert" class="rounded-lg border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-300">{{ $errors->first('extension') }}</p>
        @endif

        {{-- Title + actions --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-title-md font-semibold text-gray-800 dark:text-white/90">Extension request #{{ $extension->id }}</h1>
            <div class="flex flex-wrap items-center gap-3">
                {{-- Approve / Reject / Edit are only offered while the request is pending --}}
                @if ($extension->status === \App\Enums\ExtensionStatus::PENDING)
                    <form action="{{ route('admin.rental-extensions.approve', $extension) }}" method="POST">
                        @csrf
                        <button type="submit" onclick="return confirm('Approve this extension? The rental end date and amount will be updated.')" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-success-500 px-4 py-2 text-sm font-medium text-white hover:bg-success-600">Approve</button>
                    </form>
                    <form action="{{ route('admin.rental-extensions.reject', $extension) }}" method="POST">
                        @csrf
                        <button type="submit" onclick="return confirm('Reject this extension request?')" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-error-500 px-4 py-2 text-sm font-medium text-white hover:bg-error-600">Reject</button>
                    </form>
                    <a href="{{ route('admin.rental-extensions.edit', $extension) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Edit</a>
                @endif
                <a href="{{ route('admin.rental-extensions.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Back to list</a>
            </div>
        </div>

        {{-- Request details --}}
        <x-common.component-card :title="__('Request details')">
            <dl class="grid grid-cols-1 gap-5 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Rental</dt>
                    <dd class="mt-1 font-medium text-gray-800 dark:text-white">
                        @if ($extension->rental)
                            <a href="{{ route('admin.rentals.show', $extension->rental) }}" class="text-brand-600 underline-offset-4 hover:underline dark:text-brand-400">{{ $extension->rental->reference }}</a>
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Renter</dt>
                    <dd class="mt-1 font-medium text-gray-800 dark:text-white">{{ $extension->rental?->user?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Requested on</dt>
                    <dd class="mt-1 font-medium text-gray-800 dark:text-white">{{ $extension->requested_date->format('d/m/Y') }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Status</dt>
                    <dd class="mt-1">
                        <x-ui.badge :color="$extension->status->color()">{{ $extension->status->label() }}</x-ui.badge>
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">End date</dt>
                    <dd class="mt-1 font-medium text-gray-800 dark:text-white">
                        {{ $extension->old_end_date->format('d/m/Y') }} → {{ $extension->new_end_date->format('d/m/Y') }}
                        ({{ (int) $extension->old_end_date->diffInDays($extension->new_end_date) }} extra day(s))
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Additional amount</dt>
                    <dd class="mt-1 font-medium text-gray-800 dark:text-white">{{ number_format($extension->additional_amount, 2) }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-gray-500 dark:text-gray-400">Reason</dt>
                    {{-- e() escapes any HTML for safety, nl2br keeps the line breaks --}}
                    <dd class="mt-1 text-gray-800 dark:text-white">{!! $extension->reason ? nl2br(e($extension->reason)) : '—' !!}</dd>
                </div>
            </dl>
        </x-common.component-card>
    </div>
@endsection