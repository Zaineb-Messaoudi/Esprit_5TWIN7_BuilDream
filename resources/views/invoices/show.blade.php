@extends('layouts.app')
@section('content')
<div class="mx-auto max-w-2xl p-6">
    <div class="rounded-xl bg-white p-6 shadow-theme-xs dark:bg-gray-900">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <h1 class="text-title-lg font-semibold">{{ $invoice->invoice_number }}</h1>
            <div class="flex items-center gap-3">
                <a href="{{ route('rental.invoices.export-pdf', $invoice) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    PDF
                </a>
                <a class="text-brand-600" href="{{ route('rental.invoices.edit',$invoice) }}">{{ __('Edit') }}</a>
                <form method="POST" action="{{ route('rental.invoices.destroy',$invoice) }}" onsubmit="return confirm('{{ __('Delete this invoice?') }}')">@csrf @method('DELETE')<button class="text-error-600">{{ __('Delete') }}</button></form>
            </div>
        </div>
        <p class="mt-3">{{ $invoice->reservation->reference }} · {{ $invoice->total }} · {{ $invoice->status }}</p>
    </div>
</div>
@endsection
