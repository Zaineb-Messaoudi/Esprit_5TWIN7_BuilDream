@extends('layouts.front')

@section('content')
    <section class="bg-gray-950 text-white">
        <div class="mx-auto max-w-7xl px-4 py-9 sm:px-8 lg:px-10">
            <p class="text-theme-xs font-semibold uppercase tracking-[0.2em] text-warning-300">{{ $owner ? __('Owner workspace') : __('Renter workspace') }}</p>
            <h1 class="mt-2 text-title-sm font-semibold sm:text-title-md">{{ $title }}</h1>
            <p class="mt-2 text-theme-sm text-gray-300">{{ $rental->equipment->name }} · {{ $rental->reference }}</p>
        </div>
    </section>
    @if ($owner) @include('pages.front.partials.owner-nav') @else @include('pages.front.partials.buyer-nav') @endif
    <main class="mx-auto max-w-2xl px-4 py-8 sm:px-8 lg:px-10">
        @if (session('status'))
            <div role="status" class="mb-6 rounded-xl border border-success-200 bg-success-50 p-4 text-theme-sm text-success-800 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-200">
                {{ session('status') }}
            </div>
        @endif
        @if ($errors->any())
            <div role="alert" class="mb-6 rounded-xl border border-error-200 bg-error-50 p-4 text-theme-sm text-error-800 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-200">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <p class="text-theme-xs uppercase tracking-wide text-gray-500">{{ __('Rental reference') }}</p>
                    <h2 class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ $rental->reference }}</h2>
                </div>
                <span class="rounded-full bg-brand-50 px-3 py-1 text-theme-xs font-semibold text-brand-800 dark:bg-brand-500/15 dark:text-brand-300">{{ __($rental->status->label()) }}</span>
            </div>

            <dl class="mb-6 grid gap-4 sm:grid-cols-2">
                @foreach ([[__('Equipment'), $rental->equipment->name], [__('Renter'), $rental->user->name], [__('Current end date'), $rental->end_date->format('M j, Y')], [__('Total'), number_format((float) $rental->total_amount, 2).' TND']] as [$label, $value])
                    <div class="rounded-xl bg-gray-50 p-4 dark:bg-white/5">
                        <dt class="text-theme-xs text-gray-500">{{ $label }}</dt>
                        <dd class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>

            <form method="POST" action="{{ route('rental.extensions.store', $rental) }}" class="space-y-6">
                @csrf

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Extension details') }}</h3>
                    <p class="mt-1 text-theme-xs text-gray-500">{{ __('Request additional rental days. The owner will review and approve or decline.') }}</p>
                </div>

                <div>
                    <label for="new_end_date" class="block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('New end date') }}</label>
                    <input id="new_end_date" name="new_end_date" type="date" required
                           min="{{ $rental->end_date->addDay()->format('Y-m-d') }}"
                           class="mt-1.5 min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                           value="{{ old('new_end_date') }}">
                    @error('new_end_date')
                        <p class="mt-1.5 text-theme-xs text-error-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1.5 text-theme-xs text-gray-500">{{ __('Must be after current end date: ') }}{{ $rental->end_date->format('M j, Y') }}</p>
                </div>

                <div>
                    <label for="reason" class="block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Reason (optional)') }}</label>
                    <textarea id="reason" name="reason" rows="3" class="mt-1.5 min-h-[80px] w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white placeholder:text-gray-400" placeholder="{{ __('Why do you need the extension?') }}">{{ old('reason') }}</textarea>
                    @error('reason')
                        <p class="mt-1.5 text-theme-xs text-error-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-4 border-t border-gray-200 dark:border-gray-800">
                    <div class="rounded-xl bg-warning-50 p-4 dark:bg-warning-500/10">
                        <p class="text-theme-xs font-semibold text-warning-800 dark:text-warning-300">{{ __('Important') }}</p>
                        <ul class="mt-2 list-disc list-inside text-theme-xs text-warning-700 dark:text-warning-400 space-y-1">
                            <li>{{ __('The owner will review your request and can approve or decline.') }}</li>
                            <li>{{ __('If approved, the rental end date and total amount will be updated.') }}</li>
                            <li>{{ __('The additional amount is calculated automatically based on the daily rate.') }}</li>
                            <li>{{ __('You will be notified when the owner makes a decision.') }}</li>
                        </ul>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="button-base button-primary min-h-11 flex-1 px-5 text-theme-sm">
                        {{ __('Submit extension request') }}
                    </button>
                    <a href="{{ route('front.my-rental-detail', $rental->reference) }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-gray-300 px-5 text-theme-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                        {{ __('Cancel') }}
                    </a>
                </div>
            </form>
        </section>
    </main>
@endsection