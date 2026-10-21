@extends('layouts.front')

@section('content')
    <section class="bg-gray-950 text-white">
        <div class="mx-auto max-w-7xl px-4 py-9 sm:px-8 lg:px-10">
            <p class="text-theme-xs font-semibold uppercase tracking-[0.2em] text-warning-300">{{ __('Guided booking') }}</p>
            <h1 class="mt-2 text-title-sm font-semibold sm:text-title-md">{{ __('Reservation assistant') }}</h1>
            <p class="mt-2 max-w-3xl text-theme-sm text-gray-300">{{ __('Describe your project or choose equipment. You can review and edit all details before sending a request.') }}</p>
        </div>
    </section>

    <main class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-8 lg:px-10">
        @if (session('requestReference'))
            <div role="status" class="rounded-xl border border-success-200 bg-success-50 p-4 text-theme-sm text-success-800 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-200">
                {{ __('Request saved. Reference: :reference', ['reference' => session('requestReference')]) }}
            </div>
        @endif
        @if ($recommendationRequest)
            <section
                class="rounded-2xl border border-brand-200 bg-white p-5 shadow-theme-xs dark:border-brand-500/30 dark:bg-white/[0.03] sm:p-7"
                x-data="{
                    loading: false,
                    error: '',
                    message: '',
                    recommendations: [],
                    async loadRecommendations() {
                        this.loading = true;
                        this.error = '';
                        try {
                            const response = await fetch(@js(route('front.reservation-assistant.service-providers', $recommendationRequest)), {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': this.$refs.providerCsrf.value,
                                },
                            });
                            const payload = await response.json();
                            if (!response.ok) {
                                throw new Error(payload.message || @js(__('Recommendations could not be loaded. Please try again.')));
                            }
                            this.recommendations = payload.recommendations;
                            this.message = payload.message;
                        } catch (error) {
                            this.error = error.message;
                        } finally {
                            this.loading = false;
                        }
                    }
                }"
                x-init="loadRecommendations()">
                <input type="hidden" x-ref="providerCsrf" value="{{ csrf_token() }}">
                <p class="text-theme-xs font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-300">{{ __('Artisan recommendations') }}</p>
                <h2 class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">{{ __('Find a suitable artisan') }}</h2>
                <p class="mt-1 text-theme-xs leading-5 text-gray-500 dark:text-gray-400">
                    {{ __('Based on the confirmed :service request in :city, with a budget of :budget TND.', [
                        'service' => __($serviceTypes[$recommendationRequest->service_type]['label'] ?? __('Custom quote')),
                        'city' => $recommendationRequest->city,
                        'budget' => number_format((float) $recommendationRequest->budget, 2),
                    ]) }}
                </p>
                <p class="mt-2 text-theme-xs leading-5 text-gray-500 dark:text-gray-400">{{ __('Location uses the artisan’s declared service areas, not GPS distance. Availability is self-declared, not a live calendar. Customer ratings are marketplace-wide and may relate to equipment rentals.') }}</p>
                <p x-show="loading" x-cloak class="mt-4 text-theme-sm text-gray-600 dark:text-gray-300">{{ __('Looking for suitable artisans…') }}</p>
                <p x-show="error" x-cloak role="alert" class="mt-4 rounded-lg border border-error-200 bg-error-50 p-3 text-theme-xs text-error-800 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-200" x-text="error"></p>
                <p x-show="message" x-cloak role="status" class="mt-4 rounded-lg bg-brand-50 p-3 text-theme-xs leading-5 text-brand-900 dark:bg-brand-500/10 dark:text-brand-100" x-text="message"></p>

                <template x-if="recommendations.length">
                    <ol class="mt-4 grid gap-4 lg:grid-cols-2">
                        <template x-for="item in recommendations" :key="item.id">
                            <li class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <h3 class="font-semibold text-gray-900 dark:text-white" x-text="item.business_name"></h3>
                                        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400"><span x-text="item.specialty"></span> · <span x-text="item.name"></span></p>
                                    </div>
                                    <span class="rounded-full bg-brand-50 px-2.5 py-1 text-theme-xs font-semibold text-brand-800 dark:bg-brand-500/15 dark:text-brand-200"><span x-text="item.score"></span>% {{ __('match') }}</span>
                                </div>
                                <ul class="mt-3 space-y-1 text-theme-xs text-gray-600 dark:text-gray-300">
                                    <template x-for="reason in item.reasons" :key="reason">
                                        <li x-text="reason"></li>
                                    </template>
                                </ul>
                                <p class="mt-3 text-theme-xs text-gray-500 dark:text-gray-400">
                                    {{ __('Other service areas') }}:
                                    <span x-text="item.service_areas.join(', ')"></span>
                                </p>
                                <a x-show="item.contact_email" :href="`mailto:${item.contact_email}`" x-cloak class="mt-3 inline-flex min-h-10 items-center font-semibold text-brand-700 hover:underline dark:text-brand-300" x-text="item.contact_email"></a>
                                <p x-show="!item.contact_email" class="mt-3 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('No public contact email provided') }}</p>
                            </li>
                        </template>
                    </ol>
                </template>
                <p class="mt-4 text-theme-xs leading-5 text-gray-500 dark:text-gray-400">{{ __('These are suggestions only. No artisan is selected, contacted, or booked automatically.') }}</p>
            </section>
        @endif
        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-error-200 bg-error-50 p-4 text-theme-sm text-error-800 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-200">{{ $errors->first() }}</div>
        @endif

        <div class="grid items-start gap-6 lg:grid-cols-2">
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-7"
                x-data="{
                    description: '',
                    aiEnabled: @js($aiEnabled),
                    externalAiConsent: false,
                    loading: false,
                    error: '',
                    result: null,
                    async analyze() {
                        this.loading = true;
                        this.error = '';
                        try {
                            const response = await fetch(@js(route('front.reservation-assistant.analyze')), {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': this.$refs.csrf.value,
                                },
                                body: JSON.stringify({
                                    description: this.description,
                                    external_ai_consent: this.externalAiConsent,
                                }),
                            });
                            const payload = await response.json();
                            if (!response.ok) {
                                throw new Error(payload.message || @js(__('The request could not be analyzed. Please try again.')));
                            }
                            this.result = payload;
                        } catch (error) {
                            this.error = error.message;
                        } finally {
                            this.loading = false;
                        }
                    }
                }">
                <div>
                    <p class="text-theme-xs font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-300">{{ __('Custom work request') }}</p>
                    <h2 class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">{{ __('Tell us what you need') }}</h2>
                    @if ($aiEnabled)
                        <p class="mt-1 text-theme-xs leading-5 text-gray-500 dark:text-gray-400">{{ __('AI assistance is enabled. If you agree, your project description may be sent to Groq when local rules need help extracting request details. Review all extracted values before sending your request.') }}</p>
                        <label class="mt-3 flex items-start gap-3 rounded-xl border border-warning-200 bg-warning-50 p-3 text-theme-xs leading-5 text-warning-900 dark:border-warning-500/30 dark:bg-warning-500/10 dark:text-warning-200">
                            <input type="checkbox" x-model="externalAiConsent" required class="mt-0.5 size-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <span>{{ __('I agree that my project description may be sent to Groq for analysis.') }}</span>
                        </label>
                    @else
                        <p class="mt-1 text-theme-xs leading-5 text-gray-500 dark:text-gray-400">{{ __('AI assistance is disabled. Your project description is analyzed with local rules and is not sent to an external AI service.') }}</p>
                    @endif
                </div>

                <label for="assistant-description" class="mt-5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('Describe your project') }}
                    <textarea id="assistant-description" x-model="description" rows="4" maxlength="1500" required class="mt-1.5 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" placeholder="{{ __('Example: I need urgent plumbing work, about 12 m2, budget 900 TND, address: 12 Lake Street, Tunis.') }}"></textarea>
                </label>
                <input type="hidden" x-ref="csrf" value="{{ csrf_token() }}">

                <button type="button" @click="analyze()" :disabled="loading || description.trim().length < 5 || (aiEnabled && !externalAiConsent)" class="button-base button-primary mt-4 min-h-11 w-full px-4 text-theme-sm disabled:cursor-not-allowed disabled:opacity-50">
                    <span x-show="!loading">{{ __('Prepare my request') }}</span>
                    <span x-show="loading" x-cloak>{{ __('Analyzing request…') }}</span>
                </button>

                <p x-show="error" x-cloak role="alert" class="mt-4 rounded-lg border border-error-200 bg-error-50 p-3 text-theme-xs text-error-800 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-200" x-text="error"></p>

                <template x-if="result">
                    <div class="mt-5 space-y-5 border-t border-gray-200 pt-5 dark:border-gray-800">
                        <p role="status" class="rounded-lg bg-brand-50 p-3 text-theme-xs leading-5 text-brand-900 dark:bg-brand-500/10 dark:text-brand-100" x-text="result.message"></p>

                        <form method="POST" action="{{ route('front.reservation-assistant.store') }}" class="space-y-4">
                            @csrf
                            <label class="block text-theme-xs font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Service type') }}
                                <select name="service_type" x-model="result.fields.service_type" required class="mt-1.5 min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    <option value="">{{ __('Choose a service') }}</option>
                                    @foreach ($serviceTypes as $key => $service)
                                        <option value="{{ $key }}">{{ $service['label'] }}</option>
                                    @endforeach
                                    <option value="custom_quote">{{ __('Custom quote') }}</option>
                                </select>
                            </label>

                            <label x-show="result.fields.service_type === 'custom_quote'" x-cloak class="block text-theme-xs font-medium text-gray-700 dark:text-gray-300">
                                {{ __('Describe the unlisted work') }}
                                <textarea name="custom_description" x-model="result.fields.custom_description" rows="2" maxlength="2000" :required="result.fields.service_type === 'custom_quote'" class="mt-1.5 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
                            </label>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <label class="block text-theme-xs font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('Surface (m²)') }}
                                    <input name="surface_m2" type="number" min="0.01" max="100000" step="0.01" x-model="result.fields.surface_m2" class="mt-1.5 min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                </label>
                                <label class="block text-theme-xs font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('Urgency') }}
                                    <select name="urgency" x-model="result.fields.urgency" required class="mt-1.5 min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                        <option value="">{{ __('Choose urgency') }}</option>
                                        <option value="urgent">{{ __('Urgent') }}</option>
                                        <option value="soon">{{ __('Soon') }}</option>
                                        <option value="flexible">{{ __('Flexible') }}</option>
                                    </select>
                                </label>
                                <label class="block text-theme-xs font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('Budget (TND)') }}
                                    <input name="budget" type="number" min="0" max="999999999" step="0.01" x-model="result.fields.budget" required class="mt-1.5 min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                </label>
                                <label class="block text-theme-xs font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('Address') }}
                                    <input name="address" type="text" maxlength="255" x-model="result.fields.address" required class="mt-1.5 min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                </label>
                                <label class="block text-theme-xs font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('City for artisan recommendations') }}
                                    <input name="city" type="text" maxlength="100" x-model="result.fields.city" required class="mt-1.5 min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" placeholder="{{ __('For example: Tunis') }}">
                                </label>
                            </div>

                            <label class="flex items-start gap-3 rounded-xl bg-gray-50 p-3 text-theme-xs leading-5 text-gray-700 dark:bg-white/5 dark:text-gray-300">
                                <input type="checkbox" name="confirmed" value="1" required class="mt-0.5 size-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                <span>{{ __('I have reviewed and confirm these details. The request will only be sent after this confirmation.') }}</span>
                            </label>
                            <button type="submit" class="button-base button-primary min-h-11 w-full px-4 text-theme-sm">{{ __('Confirm and send request') }}</button>
                        </form>
                    </div>
                </template>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-7"
                x-data="{
                    loading: false,
                    error: '',
                    message: '',
                    recommendations: [],
                    async findRecommendations() {
                        this.loading = true;
                        this.error = '';
                        this.message = '';
                        this.recommendations = [];
                        try {
                            const response = await fetch(@js(route('front.reservation-assistant.recommend')), {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': this.$refs.recommendationCsrf.value,
                                },
                                body: JSON.stringify({
                                    category_id: this.$refs.category.value,
                                    location: this.$refs.location.value,
                                    max_daily_budget: this.$refs.budget.value,
                                    start_date: this.$refs.startDate.value,
                                    end_date: this.$refs.endDate.value,
                                }),
                            });
                            const payload = await response.json();
                            if (!response.ok) {
                                throw new Error(payload.message || @js(__('Recommendations could not be loaded. Please try again.')));
                            }
                            this.recommendations = payload.recommendations;
                            this.message = payload.message;
                        } catch (error) {
                            this.error = error.message;
                        } finally {
                            this.loading = false;
                        }
                    }
                }">
                <p class="text-theme-xs font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-300">{{ __('Equipment rental') }}</p>
                <h2 class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">{{ __('Find the best equipment for your trip') }}</h2>
                <p class="mt-1 text-theme-xs leading-5 text-gray-500 dark:text-gray-400">{{ __('Get recommendations based on real published listings, city, daily budget, availability, and owners’ public reviews.') }}</p>

                <form @submit.prevent="findRecommendations()" class="mt-5 space-y-4">
                    <input type="hidden" x-ref="recommendationCsrf" value="{{ csrf_token() }}">
                    <label class="block text-theme-xs font-medium text-gray-700 dark:text-gray-300">
                        {{ __('Equipment category') }}
                        <select x-ref="category" required class="mt-1.5 min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <option value="">{{ __('Choose a category') }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ __($category->name) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block text-theme-xs font-medium text-gray-700 dark:text-gray-300">
                        {{ __('City or area') }}
                        <input x-ref="location" type="text" maxlength="100" class="mt-1.5 min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" placeholder="{{ __('For example: Tunis') }}">
                    </label>
                    <label class="block text-theme-xs font-medium text-gray-700 dark:text-gray-300">
                        {{ __('Maximum daily budget (TND)') }}
                        <input x-ref="budget" type="number" min="0.01" max="999999999" step="0.01" class="mt-1.5 min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </label>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('Start date') }}<input x-ref="startDate" type="date" min="{{ now()->toDateString() }}" required class="mt-1.5 min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"></label>
                        <label class="block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __('End date') }}<input x-ref="endDate" type="date" min="{{ now()->toDateString() }}" required class="mt-1.5 min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"></label>
                    </div>
                    <button type="submit" :disabled="loading || {{ $categories->isEmpty() ? 'true' : 'false' }}" class="button-base button-primary min-h-11 w-full px-4 text-theme-sm disabled:cursor-not-allowed disabled:opacity-50">
                        <span x-show="!loading">{{ __('Show recommendations') }}</span>
                        <span x-show="loading" x-cloak>{{ __('Finding available equipment…') }}</span>
                    </button>
                </form>

                <p x-show="error" x-cloak role="alert" class="mt-4 rounded-lg border border-error-200 bg-error-50 p-3 text-theme-xs text-error-800 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-200" x-text="error"></p>
                <p x-show="message" x-cloak role="status" class="mt-4 rounded-lg bg-brand-50 p-3 text-theme-xs leading-5 text-brand-900 dark:bg-brand-500/10 dark:text-brand-100" x-text="message"></p>

                <template x-if="recommendations.length">
                    <ol class="mt-4 space-y-3">
                        <template x-for="item in recommendations" :key="item.id">
                            <li class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <h3 class="font-semibold text-gray-900 dark:text-white" x-text="item.name"></h3>
                                        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400"><span x-text="item.category"></span> · <span x-text="item.location"></span> · {{ __('Shared by') }} <span x-text="item.owner"></span></p>
                                    </div>
                                    <div class="text-end">
                                        <p class="font-semibold text-gray-900 dark:text-white"><span x-text="Number(item.price_per_day).toFixed(2)"></span> TND / {{ __('day') }}</p>
                                        <p class="text-theme-xs text-gray-500 dark:text-gray-400"><span x-text="item.rating === null ? @js(__('No public rating')) : `${Number(item.rating).toFixed(1)} / 5 (${item.reviews_count})`"></span></p>
                                    </div>
                                </div>
                                <div class="mt-3 flex items-center gap-2">
                                    <span class="rounded-full bg-brand-50 px-2.5 py-1 text-theme-xs font-semibold text-brand-800 dark:bg-brand-500/15 dark:text-brand-200"><span x-text="item.score"></span>% {{ __('match') }}</span>
                                </div>
                                <ul class="mt-3 flex flex-wrap gap-2">
                                    <template x-for="reason in item.reasons" :key="reason">
                                        <li class="rounded-full bg-gray-100 px-2.5 py-1 text-theme-xs text-gray-700 dark:bg-white/5 dark:text-gray-300" x-text="reason"></li>
                                    </template>
                                </ul>
                                <div class="mt-4 flex flex-wrap gap-4">
                                    <a :href="item.url" class="inline-flex min-h-10 items-center font-semibold text-brand-700 hover:underline dark:text-brand-300">{{ __('Review this listing') }} <span aria-hidden="true" class="ms-1">→</span></a>
                                    <a :href="item.reservation_url" class="inline-flex min-h-10 items-center font-semibold text-brand-700 hover:underline dark:text-brand-300">{{ __('Reserve these dates') }} <span aria-hidden="true" class="ms-1">→</span></a>
                                </div>
                            </li>
                        </template>
                    </ol>
                </template>

                <p class="mt-4 text-theme-xs leading-5 text-gray-500 dark:text-gray-400">{{ __('Match score is an estimate based on your selected criteria and review history. New owners start with a neutral score until they receive reviews; you make the final choice.') }}</p>
                @if ($categories->isEmpty())
                    <p role="status" class="mt-4 rounded-lg border border-warning-200 bg-warning-50 p-3 text-theme-xs text-warning-900 dark:border-warning-500/30 dark:bg-warning-500/10 dark:text-warning-200">{{ __('No equipment categories are published yet. Recommendations will appear when the catalogue is ready.') }}</p>
                @endif
            </section>
        </div>

        @if ($requests->isNotEmpty())
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-7">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Your recent custom quote requests') }}</h2>
                <ul class="mt-4 divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($requests as $request)
                        <li class="flex flex-wrap items-center justify-between gap-2 py-3 text-theme-xs">
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $request->reference }} · {{ $request->service_type === 'custom_quote' ? __('Custom quote') : __($serviceTypes[$request->service_type]['label'] ?? $request->service_type) }}</span>
                            <span class="text-gray-500 dark:text-gray-400">{{ __(ucfirst($request->status)) }} · {{ $request->created_at->format('d/m/Y') }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </main>
@endsection
