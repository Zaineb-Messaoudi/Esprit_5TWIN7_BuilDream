@php
    $fieldClass = 'mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white';
@endphp
@php
    $checklistResults = collect(old('checklist_results', $record?->checklist_results ?? []))
        ->mapWithKeys(fn ($result) => [(string) ($result['item_id'] ?? '') => isset($result['passed']) ? (string) (int) $result['passed'] : ''])
        ->all();
@endphp
<form action="{{ $record ? route($prefix.$resource.'.update', $record) : route($prefix.$resource.'.store') }}" method="POST" class="space-y-5"
      x-data="technicalInspectionForm({
        checklistId: @js((string) old('inspection_checklist_id', $record?->inspection_checklist_id ?? '')),
        equipmentId: @js((string) old('equipment_id', $record?->equipment_id ?? request('equipment_id'))),
        equipmentCategories: @js($options['equipment_categories'] ?? []),
        checklistOptions: @js($options['checklist_results'] ?? []),
        results: @js($checklistResults),
        analyzeUrl: @js(route($prefix.'ai.analyze-damage')),
        estimateUrl: @js(route($prefix.'ai.estimate-repair')),
        transcribeUrl: @js(route($prefix.'ai.transcribe')),
        csrf: @js(csrf_token()),
        prefilledMessage: @js(__('The form was prefilled. You can edit the result.')),
        unavailableMessage: @js(__('The image analysis service is temporarily unavailable.')),
        estimateUnavailableMessage: @js(__('The repair estimate could not be calculated.')),
        currencyLabel: @js(__('TND')),
        aiLabel: @js(__('AI classification')),
        hoursLabel: @js(__('hours')),
        analysisResultLabel: @js(__('AI photo analysis result')),
        detectedDamageLabel: @js(__('Detected damage')),
        noDamageLabel: @js(__('No damage detected')),
        descriptionLabel: @js(__('Description')),
        estimatedSeverityLabel: @js(__('Estimated severity')),
        estimatedDurationLabel: @js(__('Estimated repair duration'))
      })">
    @csrf
    @if ($record) @method('PUT') @endif
    @foreach ($fields as $name => $field)
        @php
            $value = old($name, $record?->$name ?? request($name));
            if ($value instanceof \Carbon\CarbonInterface) $value = $value->format('Y-m-d');
        @endphp
        <div>
            <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __($field['label']) }} @if ($field['required'] ?? false) <span aria-hidden="true">*</span> @endif</label>
            @if ($field['type'] === 'select')
                <select id="{{ $name }}" name="{{ $name }}" class="{{ $fieldClass }}" @if ($name === 'inspection_checklist_id') x-model="checklistId" @endif @if ($name === 'equipment_id') x-model="equipmentId" @endif @if ($field['required'] ?? false) required @endif aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}">
                    <option value="">{{ __('Select an option') }}</option>
                    @foreach ($options[$name] ?? [] as $optionValue => $optionLabel)
                        <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
                    @endforeach
                </select>
            @elseif ($field['type'] === 'checklist')
                <div x-show="checklistId && (checklistOptions[checklistId] || []).length" x-cloak class="mt-3 space-y-3">
                    <template x-for="(item, index) in (checklistOptions[checklistId] || [])" :key="item.id">
                        <fieldset class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                            <input type="hidden" :name="'checklist_results[' + index + '][item_id]'" :value="item.id">
                            <legend class="px-1 text-sm font-medium text-gray-800 dark:text-gray-200">
                                <span x-text="item.label"></span>
                                <span x-show="item.is_critical" class="ms-1 text-error-600 dark:text-error-400">{{ __('Critical') }}</span>
                            </legend>
                            <div class="mt-2 flex gap-5">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                    <input type="radio" :name="'checklist_results[' + index + '][passed]'" value="1" x-model="results[item.id]" required class="text-success-600 focus:ring-success-500">
                                    {{ __('Validated') }}
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                    <input type="radio" :name="'checklist_results[' + index + '][passed]'" value="0" x-model="results[item.id]" required class="text-error-600 focus:ring-error-500">
                                    {{ __('Not validated') }}
                                </label>
                            </div>
                        </fieldset>
                    </template>
                    <p x-show="!checklistId" class="text-sm text-gray-500 dark:text-gray-400">{{ __('Select a checklist to display its items.') }}</p>
                </div>
            @elseif ($field['type'] === 'textarea')
                <textarea id="{{ $name }}" name="{{ $name }}" rows="4" class="{{ $fieldClass }}" @if ($field['required'] ?? false) required @endif aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}">{{ $value }}</textarea>
                @if ($resource === 'inspections' && $name === 'comments')
                    <div class="mt-2 flex flex-wrap items-center gap-3">
                        <button type="button" x-show="speechSupported" x-cloak @click="toggleDictation()" class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-200" x-text="listening ? @js(__('Stop dictation')) : @js(__('Dictate comments'))"></button>
                        <span x-show="!speechSupported" class="text-sm text-gray-500 dark:text-gray-400">{{ __('Voice dictation is not supported by this browser.') }}</span>
                    </div>
                @endif
            @elseif ($field['type'] === 'checkbox')
                <input type="hidden" name="{{ $name }}" value="0">
                <input id="{{ $name }}" type="checkbox" name="{{ $name }}" value="1" @checked((bool) $value) class="mt-2 rounded border-gray-300 text-brand-600 dark:border-gray-700 dark:bg-gray-900" aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}">
            @else
                <input id="{{ $name }}" name="{{ $name }}" type="{{ $field['type'] }}" value="{{ $value }}" @if ($field['type'] === 'number') min="0" step="{{ $field['step'] ?? '1' }}" @endif class="{{ $fieldClass }}" @if ($field['required'] ?? false) required @endif aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}">
            @endif
            @error($name) <p class="mt-1 text-sm text-error-600 dark:text-error-400" role="alert">{{ $message }}</p> @enderror
        </div>
    @endforeach
    @if ($resource === 'inspections')
        <section class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
            <label for="damage_photo_analysis" class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('Analyze a damage photo') }}</label>
            <input id="damage_photo_analysis" type="file" accept="image/jpeg,image/png,image/webp" @change="photo = $event.target.files[0]" class="mt-2 block w-full text-sm text-gray-700 dark:text-gray-300">
            <button type="button" @click="analyzePhoto()" :disabled="!photo || busy" class="mt-3 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white disabled:opacity-50">{{ __('Analyze photo and estimate') }}</button>
            <p x-show="aiMessage" x-text="aiMessage" class="mt-2 text-sm text-gray-600 dark:text-gray-300"></p>
            <div x-show="analysisResult" x-cloak class="mt-4 rounded-lg border border-success-200 bg-success-50 p-4 text-sm text-gray-800 dark:border-success-800 dark:bg-success-950/30 dark:text-gray-100">
                <h2 class="font-semibold" x-text="analysisResultLabel"></h2>
                <dl class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div>
                        <dt class="text-gray-600 dark:text-gray-300" x-text="detectedDamageLabel"></dt>
                        <dd class="mt-1 font-medium" x-text="analysisResult.damage_detected ? detectedDamageLabel : noDamageLabel"></dd>
                    </div>
                    <div>
                        <dt class="text-gray-600 dark:text-gray-300" x-text="estimatedSeverityLabel"></dt>
                        <dd class="mt-1 font-medium" x-text="severityLabel(analysisResult.severity)"></dd>
                    </div>
                    <div>
                        <dt class="text-gray-600 dark:text-gray-300" x-text="estimatedDurationLabel"></dt>
                        <dd class="mt-1 font-medium" x-text="formatRepairHours(analysisResult.repair_hours)"></dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-gray-600 dark:text-gray-300" x-text="descriptionLabel"></dt>
                        <dd class="mt-1 font-medium" x-text="analysisResult.comments"></dd>
                    </div>
                </dl>
            </div>
        </section>
        <section class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
            <h2 class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('Repair cost estimate') }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Photo analysis selects the severity automatically. You can adjust it before recalculating the estimate.') }}</p>
            <div class="mt-3 flex flex-wrap items-end gap-3">
                <div>
                    <label for="estimate_severity" class="block text-sm text-gray-700 dark:text-gray-300">{{ __('Severity') }}</label>
                    <select id="estimate_severity" x-model="estimateSeverity" class="{{ $fieldClass }}">
                        <option value="minor">{{ __('Minor') }}</option>
                        <option value="moderate">{{ __('Moderate') }}</option>
                        <option value="severe">{{ __('Severe') }}</option>
                    </select>
                </div>
                <button type="button" @click="estimateRepairCost()" :disabled="estimateBusy" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white disabled:opacity-50">{{ __('Estimate repair cost') }}</button>
            </div>
            <div x-show="estimateResult" x-cloak class="mt-4 grid gap-3 rounded-lg bg-gray-50 p-3 text-sm dark:bg-gray-800 sm:grid-cols-4">
                <p><span class="block text-gray-500 dark:text-gray-400">{{ __('Average') }}</span><strong x-text="formatEstimate(estimateResult?.average)"></strong></p>
                <p><span class="block text-gray-500 dark:text-gray-400">{{ __('Median') }}</span><strong x-text="formatEstimate(estimateResult?.median)"></strong></p>
                <p><span class="block text-gray-500 dark:text-gray-400">{{ __('Range') }}</span><strong x-text="formatRange()"></strong></p>
                <p><span class="block text-gray-500 dark:text-gray-400">{{ __('History records') }}</span><strong x-text="estimateResult?.sample_size"></strong></p>
            </div>
            <p x-show="estimateMessage" x-text="estimateMessage" class="mt-2 text-sm text-error-600 dark:text-error-400"></p>
            <p x-show="estimateSource" x-text="estimateSource" class="mt-2 text-xs text-gray-500 dark:text-gray-400"></p>
        </section>
    @endif
    <div class="flex flex-wrap gap-3">
        <button type="submit" class="rounded-lg bg-brand-500 px-5 py-2 text-sm font-medium text-white hover:bg-brand-600">{{ $record ? __('Save changes') : __('Create record') }}</button>
        <a href="{{ route($prefix.$resource.'.index') }}" class="rounded-lg border border-gray-300 px-5 py-2 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-200">{{ __('Cancel') }}</a>
    </div>
</form>
