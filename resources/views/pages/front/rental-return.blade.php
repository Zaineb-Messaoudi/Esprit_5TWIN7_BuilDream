@extends('layouts.front')

@section('content')
    <section class="bg-gray-950 text-white">
        <div class="mx-auto max-w-7xl px-4 py-9 sm:px-8 lg:px-10">
            <p class="text-theme-xs font-semibold uppercase tracking-[0.2em] text-warning-300">{{ $isBuyer ? __('Renter workspace') : ($owner ? __('Owner workspace') : __('Renter workspace')) }}</p>
            <h1 class="mt-2 text-title-sm font-semibold sm:text-title-md">{{ $title }}</h1>
            <p class="mt-2 text-theme-sm text-gray-300">{{ $rental->equipment->name }} · {{ $rental->reference }}</p>
        </div>
    </section>
    @if ($isBuyer) @include('pages.front.partials.buyer-nav') @elseif ($owner) @include('pages.front.partials.owner-nav') @else @include('pages.front.partials.buyer-nav') @endif
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
                    <h2 class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $rental->reference }}</h2>
                </div>
                <span class="rounded-full bg-brand-50 px-3 py-1 text-theme-xs font-semibold text-brand-800 dark:bg-brand-500/15 dark:text-brand-300">{{ __($rental->status->label()) }}</span>
            </div>

            <dl class="mb-6 grid gap-4 sm:grid-cols-2">
                @foreach ([[__('Equipment'), $rental->equipment->name], [__('Renter'), $rental->user->name], [__('Rental period'), $rental->start_date->format('M j, Y') . ' – ' . $rental->end_date->format('M j, Y')]] as [$label, $value])
                    <div class="rounded-xl bg-gray-50 p-4 dark:bg-white/5">
                        <dt class="text-theme-xs text-gray-500">{{ $label }}</dt>
                        <dd class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>

            <form method="POST" action="{{ route('rental.return.process', $rental) }}" class="space-y-6">
                @csrf

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Inspection details') }}</h3>
                    <p class="mt-1 text-theme-xs text-gray-500">{{ $isBuyer ? __('Record the equipment condition when you return it.') : __('Record the equipment condition upon return.') }}</p>
                </div>

                <div>
                    <label for="condition_before" class="block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Condition before rental') }}</label>
                    <textarea id="condition_before" name="condition_before" required rows="3" class="mt-1.5 min-h-[80px] w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white placeholder:text-gray-400" placeholder="{{ __('e.g. Clean, fully charged, no visible damage') }}">{{ old('condition_before') }}</textarea>
                    @error('condition_before')
                        <p class="mt-1.5 text-theme-xs text-error-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="condition_after" class="block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Condition after return') }}</label>
                    <textarea id="condition_after" name="condition_after" required rows="3" class="mt-1.5 min-h-[80px] w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white placeholder:text-gray-400" placeholder="{{ __('e.g. Clean, 80% charge, minor scratches on casing') }}">{{ old('condition_after') }}</textarea>
                    @error('condition_after')
                        <p class="mt-1.5 text-theme-xs text-error-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 p-4 transition-colors has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 dark:border-gray-800 dark:has-[:checked]:border-brand-500 dark:has-[:checked]:bg-brand-500/10">
                        <input type="checkbox" name="damage_detected" value="1" class="border-gray-300 text-brand-600 focus:ring-brand-500" @if(old('damage_detected')) checked @endif>
                        <span class="flex-1 text-theme-sm font-medium text-gray-900 dark:text-white">{{ __('Damage detected') }}</span>
                    </label>
                    @error('damage_detected')
                        <p class="mt-1.5 text-theme-xs text-error-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="comments" class="block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Comments (optional)') }}</label>
                    <textarea id="comments" name="comments" rows="4" class="mt-1.5 min-h-[100px] w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white placeholder:text-gray-400" placeholder="{{ __('Additional notes about the equipment condition...') }}">{{ old('comments') }}</textarea>
                    @error('comments')
                        <p class="mt-1.5 text-theme-xs text-error-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-4 border-t border-gray-200 dark:border-gray-800">
                    <div class="rounded-xl bg-warning-50 p-4 dark:bg-warning-500/10">
                        <p class="text-theme-xs font-semibold text-warning-800 dark:text-warning-300">{{ __('Important') }}</p>
                        <ul class="mt-2 list-disc list-inside text-theme-xs text-warning-700 dark:text-warning-400 space-y-1">
                            <li>{{ __('If damage is detected, the equipment will automatically be set to maintenance status.') }}</li>
                            <li>{{ __('The rental status will be updated to "Completed".') }}</li>
                            <li>{{ __('An inspection record will be created for the technical team.') }}</li>
                        </ul>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="button-base button-primary min-h-11 flex-1 px-5 text-theme-sm">
                        {{ __('Confirm return & create inspection') }}
                    </button>
                    <a href="{{ route('front.my-rental-detail', $rental->reference) }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-gray-300 px-5 text-theme-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                        {{ __('Cancel') }}
                    </a>
                </div>
            </form>
        </section>
    </main>
@endsection