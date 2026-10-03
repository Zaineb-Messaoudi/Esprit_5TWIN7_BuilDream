@extends('layouts.app')

@section('content')
    <div class="space-y-6" x-data="{
        step: 1,
        complete: false,
        enabled: true,
        passwordVisible: false,
        passwordLabels: { show: @js(__('Show password')), hide: @js(__('Hide password')) },
        fileHint: @js(__('PDF, image, or spreadsheet')),
        uploadedName: '',
        fullName: '',
        email: '',
        next() {
            if (this.step === 1 && (!this.fullName.trim() || !this.email.trim() || !this.$refs.email.checkValidity())) {
                this.$refs.name.reportValidity();
                this.$refs.email.reportValidity();
                return;
            }
            this.step = Math.min(3, this.step + 1);
        },
        submit() {
            this.complete = true;
            this.step = 3;
        }
    }">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Forms / Advanced Elements</p>
            <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">Advanced form patterns</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Field states, upload controls, and an interactive multi-step form.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <x-common.component-card title="Input states" desc="Examples for feedback and disabled states">
                <div class="space-y-5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Success state
                        <input value="solar@example.test" class="mt-2 w-full rounded-lg border border-success-500 bg-white px-3 py-2.5 text-sm text-gray-800 focus:outline-hidden focus:ring-3 focus:ring-success-500/10 dark:bg-gray-900 dark:text-white" aria-describedby="field-success" />
                        <span id="field-success" class="mt-1 block text-xs text-success-600">Email address is available.</span>
                    </label>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Error state
                        <input value="not-an-email" aria-invalid="true" aria-describedby="field-error" class="mt-2 w-full rounded-lg border border-error-500 bg-white px-3 py-2.5 text-sm text-gray-800 focus:outline-hidden focus:ring-3 focus:ring-error-500/10 dark:bg-gray-900 dark:text-white" />
                        <span id="field-error" class="mt-1 block text-xs text-error-500">Enter a valid email address.</span>
                    </label>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Disabled
                        <input disabled value="This field is disabled" class="mt-2 w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-400 dark:border-gray-800 dark:bg-gray-900" />
                    </label>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Loading
                        <div class="relative">
                            <input disabled value="Checking availability..." class="mt-2 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 pe-10 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400" />
                            <span class="absolute end-3 top-1/2 mt-1 h-4 w-4 animate-spin rounded-full border-2 border-brand-500 border-t-transparent" role="status" aria-label="Checking"></span>
                        </div>
                    </label>
                </div>
            </x-common.component-card>

            <x-common.component-card title="Input patterns" desc="Common controls used in admin forms">
                <div class="space-y-5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Multi-select
                        <select multiple size="3" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"><option>Solar panels</option><option>Inverters</option><option>Energy storage</option><option>Accessories</option></select>
                        <span class="mt-1 block text-xs text-gray-400">Use Ctrl / Command to select multiple values.</span>
                    </label>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Start date') }}<input type="date" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" /></label>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('End date') }}<input type="date" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" /></label>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Time') }}<input type="time" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" /></label>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('One-time verification code') }}<input inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" placeholder="{{ __('Enter 6-digit code') }}" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm tracking-[0.35em] dark:border-gray-700 dark:bg-gray-900 dark:text-white" /></label>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="demo-password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Password visibility') }}</label>
                            <div class="relative mt-2">
                                <input id="demo-password" :type="passwordVisible ? 'text' : 'password'" value="SolarDemo2026!" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 pe-20 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                                <button type="button" @click="passwordVisible = !passwordVisible" class="absolute end-2 top-1/2 -translate-y-1/2 rounded px-2 py-1 text-xs font-medium text-brand-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-brand-300" :aria-label="passwordVisible ? passwordLabels.hide : passwordLabels.show" x-text="passwordVisible ? passwordLabels.hide : passwordLabels.show"></button>
                            </div>
                        </div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Price input group') }}
                            <span class="mt-2 flex overflow-hidden rounded-lg border border-gray-300 dark:border-gray-700">
                                <span class="inline-flex items-center border-e border-gray-300 bg-gray-50 px-3 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">{{ __('USD') }}</span>
                                <input type="number" min="0" step="0.01" placeholder="0.00" class="min-w-0 flex-1 bg-white px-3 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:bg-gray-900 dark:text-white" />
                            </span>
                        </label>
                    </div>
                    <label class="flex cursor-pointer items-center gap-3">
                        <input type="checkbox" x-model="enabled" class="peer sr-only" role="switch" :aria-checked="enabled" />
                        <span class="relative h-6 w-11 rounded-full transition-colors peer-focus-visible:ring-2 peer-focus-visible:ring-brand-500 peer-focus-visible:ring-offset-2" :class="enabled ? 'bg-brand-500' : 'bg-gray-300 dark:bg-gray-700'"><span class="absolute top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform" :class="enabled ? 'translate-x-5' : 'translate-x-0.5'"></span></span>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Enable email notifications</span>
                    </label>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Attachment
                        <input type="file" @change="uploadedName = $event.target.files[0]?.name || ''" class="mt-2 block w-full text-sm text-gray-500 file:me-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-500/10 dark:file:text-brand-300" />
                        <span class="mt-1 block text-xs text-gray-400" x-text="uploadedName || 'PDF, image, or spreadsheet'"></span>
                    </label>
                    <div
                        @dragover.prevent
                        @drop.prevent="uploadedName = $event.dataTransfer.files[0]?.name || uploadedName"
                        class="rounded-xl border-2 border-dashed border-gray-300 p-5 text-center transition hover:border-brand-400 focus-within:border-brand-500 dark:border-gray-700 dark:hover:border-brand-500"
                    >
                        <label for="demo-drop-file" class="cursor-pointer text-sm font-medium text-brand-600 dark:text-brand-300">{{ __('Drag and drop a file or browse') }}</label>
                        <input id="demo-drop-file" type="file" class="sr-only" @change="uploadedName = $event.target.files[0]?.name || ''" />
                        <p class="mt-1 text-xs text-gray-400" x-text="uploadedName || fileHint"></p>
                    </div>
                    <label class="relative block">
                        <input placeholder=" " class="peer w-full rounded-lg border border-gray-300 bg-white px-3 pb-2.5 pt-5 text-sm text-gray-800 focus:border-brand-500 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                        <span class="pointer-events-none absolute start-3 top-2 text-xs text-gray-400 transition-all peer-placeholder-shown:top-3.5 peer-placeholder-shown:text-sm peer-focus:top-2 peer-focus:text-xs peer-focus:text-brand-500">Floating label</span>
                    </label>
                </div>
            </x-common.component-card>
        </div>

        <x-common.component-card title="Multi-step onboarding form" desc="Step state is local to the page; submission does not create an account">
            <div class="mb-6 grid grid-cols-3 gap-3" aria-label="Form progress">
                @foreach (['Contact details', 'Preferences', 'Review'] as $index => $label)
                    <div class="border-t-2 pt-3" :class="step >= {{ $index + 1 }} ? 'border-brand-500' : 'border-gray-200 dark:border-gray-700'">
                        <p class="text-xs font-medium" :class="step >= {{ $index + 1 }} ? 'text-brand-600 dark:text-brand-300' : 'text-gray-400'"><span>{{ $index + 1 }}.</span> {{ $label }}</p>
                    </div>
                @endforeach
            </div>

            <form @submit.prevent="submit()">
                <div x-show="step === 1" class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Full name
                        <input x-ref="name" x-model="fullName" required maxlength="100" autocomplete="name" placeholder="Your name" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </label>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Work email
                        <input x-ref="email" x-model="email" required type="email" autocomplete="email" placeholder="you@company.com" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </label>
                </div>
                <div x-show="step === 2" x-cloak class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Team size
                        <select class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"><option>1–5 people</option><option>6–20 people</option><option>21–50 people</option><option>50+ people</option></select>
                    </label>
                    <fieldset>
                        <legend class="text-sm font-medium text-gray-700 dark:text-gray-300">Primary use</legend>
                        <div class="mt-3 flex flex-wrap gap-4">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400"><input type="radio" name="use-case" value="inventory" checked class="border-gray-300 text-brand-500 focus:ring-brand-500" /> Inventory</label>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400"><input type="radio" name="use-case" value="sales" class="border-gray-300 text-brand-500 focus:ring-brand-500" /> Sales</label>
                            <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400"><input type="radio" name="use-case" value="operations" class="border-gray-300 text-brand-500 focus:ring-brand-500" /> Operations</label>
                        </div>
                    </fieldset>
                </div>
                <div x-show="step === 3" x-cloak class="rounded-xl bg-gray-50 p-5 dark:bg-gray-800/50">
                    <div x-show="!complete">
                        <p class="font-semibold text-gray-800 dark:text-white/90">Review your information</p>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300"><span x-text="fullName"></span> · <span x-text="email"></span></p>
                        <p class="mt-3 text-xs text-gray-400">This example does not submit information to a server.</p>
                    </div>
                    <div x-show="complete" role="status" class="text-sm font-medium text-success-600">Demo complete. No account or record was created.</div>
                </div>
                <div class="mt-6 flex flex-wrap justify-between gap-3">
                    <button x-show="step > 1 && !complete" type="button" @click="step = Math.max(1, step - 1)" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Back</button>
                    <span x-show="step === 1 || complete"></span>
                    <button x-show="step < 3" type="button" @click="next()" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Continue</button>
                    <button x-show="step === 3 && !complete" type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Complete demo</button>
                </div>
            </form>
        </x-common.component-card>
    </div>
@endsection
