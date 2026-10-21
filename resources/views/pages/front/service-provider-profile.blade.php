@extends('layouts.front')

@section('content')
    <section class="bg-gray-950 text-white">
        <div class="mx-auto max-w-7xl px-4 py-9 sm:px-8 lg:px-10">
            <p class="text-theme-xs font-semibold uppercase tracking-[0.2em] text-warning-300">{{ __('Owner workspace') }}</p>
            <h1 class="mt-2 text-title-sm font-semibold sm:text-title-md">{{ __('Artisan profile') }}</h1>
            <p class="mt-2 max-w-3xl text-theme-sm text-gray-300">{{ __('Create a separate public profile if you also provide renovation or repair services. This does not change your equipment-owner account.') }}</p>
        </div>
    </section>

    <main class="mx-auto max-w-4xl px-4 py-8 sm:px-8 lg:px-10">
        @if (session('status'))
            <div role="status" class="mb-6 rounded-xl border border-success-200 bg-success-50 p-4 text-theme-sm text-success-800 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-200">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div role="alert" class="mb-6 rounded-xl border border-error-200 bg-error-50 p-4 text-theme-sm text-error-800 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-200">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('front.owner-service-provider-profile.update') }}" class="space-y-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-7">
            @csrf
            @method('PUT')

            <label class="block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                {{ __('Business or artisan name') }}
                <input name="business_name" value="{{ old('business_name', $profile?->business_name) }}" required maxlength="120" class="mt-1.5 min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            </label>

            <label class="block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                {{ __('About your services') }}
                <textarea name="description" rows="3" maxlength="1500" class="mt-1.5 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">{{ old('description', $profile?->description) }}</textarea>
            </label>

            <fieldset>
                <legend class="text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Services offered') }}</legend>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    @foreach ($serviceTypes as $key => $service)
                        <label class="flex min-h-11 items-center gap-3 rounded-lg border border-gray-200 px-3 text-theme-sm text-gray-700 dark:border-gray-700 dark:text-gray-300">
                            <input type="checkbox" name="specialties[]" value="{{ $key }}" @checked(in_array($key, old('specialties', $profile?->specialties ?? []), true)) class="size-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            {{ $service['label'] }}
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <label class="block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                {{ __('Cities or areas you serve') }}
                <textarea name="service_areas" rows="2" required class="mt-1.5 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" placeholder="{{ __('For example: Tunis, Ariana, Ben Arous') }}">{{ old('service_areas', implode(', ', $profile?->service_areas ?? [])) }}</textarea>
                <span class="mt-1 block text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Separate locations with commas. Matching is by city or area name, not GPS distance.') }}</span>
            </label>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('Years of experience') }}
                    <input name="experience_years" type="number" min="0" max="60" step="1" value="{{ old('experience_years', $profile?->experience_years ?? 0) }}" required class="mt-1.5 min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </label>
                <label class="block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('Minimum project budget (TND), if known') }}
                    <input name="minimum_project_budget" type="number" min="0.01" max="999999999" step="0.01" value="{{ old('minimum_project_budget', $profile?->minimum_project_budget) }}" class="mt-1.5 min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </label>
            </div>

            <div x-data="{ accepting: {{ old('accepting_requests', $profile?->accepting_requests) ? 'true' : 'false' }} }" class="space-y-4">
                <label class="flex items-start gap-3 rounded-xl bg-gray-50 p-3 text-theme-sm text-gray-700 dark:bg-white/5 dark:text-gray-300">
                    <input name="accepting_requests" type="checkbox" value="1" x-model="accepting" class="mt-0.5 size-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                    <span>{{ __('Show my profile to buyers and indicate that I accept requests') }}</span>
                </label>
                <label x-show="accepting" x-cloak class="block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('Available from') }}
                    <input name="available_from" type="date" min="{{ now()->toDateString() }}" value="{{ old('available_from', $profile?->available_from?->toDateString()) }}" :required="accepting" class="mt-1.5 min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <span class="mt-1 block text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Availability is self-declared and is not checked against a live calendar.') }}</span>
                </label>
            </div>

            <label class="block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                {{ __('Public contact email (optional)') }}
                <input name="contact_email" type="email" value="{{ old('contact_email', $profile?->contact_email) }}" maxlength="255" class="mt-1.5 min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <span class="mt-1 block text-theme-xs text-gray-500 dark:text-gray-400">{{ __('This email is shown to buyers only when your profile appears in recommendations.') }}</span>
            </label>

            <button type="submit" class="button-base button-primary min-h-11 w-full px-4 text-theme-sm">{{ __('Save artisan profile') }}</button>
        </form>
    </main>
@endsection
