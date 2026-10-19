@php($energy = old('energy', isset($equipment) && $equipment->energyProfile ? $equipment->energyProfile->toArray() : []))
<div class="grid gap-5 sm:grid-cols-2">
    <div>
        <label for="name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Name') }}</label>
        <input id="name" name="name" value="{{ old('name', $equipment->name ?? '') }}" required class="min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        @error('name')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="category_id" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300 flex items-center justify-between">
            {{ __('Category') }}
            <button type="button" x-data="aiAutoCategorize()" @click="suggest()" :disabled="loading" class="ml-3 px-3 py-1.5 text-xs font-medium text-brand-700 bg-brand-50 border border-brand-200 rounded-lg hover:bg-brand-100 dark:text-brand-300 dark:bg-brand-500/10 dark:border-brand-500/30 disabled:opacity-50">
                <span x-show="!loading">{{ __('Suggest Category') }}</span>
                <span x-show="loading">{{ __('Analyzing...') }}</span>
            </button>
        </label>
        <select id="category_id" name="category_id" required class="min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">{{ __('Select category') }}</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $equipment->category_id ?? '') === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        @error('category_id')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
        <div x-show="suggestion" class="mt-2 p-3 rounded-lg bg-brand-50 border border-brand-200 dark:bg-brand-500/10 dark:border-brand-500/30" x-cloak>
            <p class="text-xs font-medium text-brand-700 dark:text-brand-300">{{ __('Suggested Category:') }} <strong x-text="suggestion.category"></strong></p>
            <p class="mt-1 text-xs text-brand-600 dark:text-brand-400">{{ __('Confidence:') }} <span x-text="suggestion.confidence + '%'"></span></p>
            <button @click="applySuggestion()" class="mt-2 text-xs font-medium text-brand-700 hover:underline dark:text-brand-300">{{ __('Apply this category') }}</button>
        </div>
        <div x-show="error" class="mt-2 p-2 rounded-lg bg-error-50 border border-error-200 text-xs text-error-600 dark:bg-error-500/10 dark:border-error-500/30 dark:text-error-400" x-cloak x-text="error"></div>
    </div>
    <div>
        <label for="brand" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Brand') }}</label>
        <input id="brand" name="brand" value="{{ old('brand', $equipment->brand ?? '') }}" class="min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
    </div>
    <div>
        <label for="model" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Model') }}</label>
        <input id="model" name="model" value="{{ old('model', $equipment->model ?? '') }}" class="min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
    </div>
    <div>
        <label for="price_per_day" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300 flex items-center justify-between">
            {{ __('Price per day') }}
            <button type="button" x-data="aiPriceSuggestion()" @click="suggest()" :disabled="loading" class="ml-3 px-3 py-1.5 text-xs font-medium text-brand-700 bg-brand-50 border border-brand-200 rounded-lg hover:bg-brand-100 dark:text-brand-300 dark:bg-brand-500/10 dark:border-brand-500/30 disabled:opacity-50">
                <span x-show="!loading">{{ __('Suggest Price') }}</span>
                <span x-show="loading">{{ __('Analyzing...') }}</span>
            </button>
        </label>
        <input id="price_per_day" name="price_per_day" type="number" step="0.01" min="0" value="{{ old('price_per_day', $equipment->price_per_day ?? '') }}" required class="min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        @error('price_per_day')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
        <div x-show="suggestion" class="mt-2 p-3 rounded-lg bg-success-50 border border-success-200 dark:bg-success-500/10 dark:border-success-500/30" x-cloak>
            <p class="text-xs font-medium text-success-700 dark:text-success-300">{{ __('AI Suggestion:') }} <span x-text="suggestion.suggested_price + ' TND/day'"></span></p>
            <p class="mt-1 text-xs text-success-600 dark:text-success-400" x-text="suggestion.explanation"></p>
            <button @click="applySuggestion()" class="mt-2 text-xs font-medium text-brand-700 hover:underline dark:text-brand-300">{{ __('Apply this price') }}</button>
        </div>
        <div x-show="error" class="mt-2 p-2 rounded-lg bg-error-50 border border-error-200 text-xs text-error-600 dark:bg-error-500/10 dark:border-error-500/30 dark:text-error-400" x-cloak x-text="error"></div>
    </div>
    <div>
        <label for="condition" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Condition') }}</label>
        <select id="condition" name="condition" required class="min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="">{{ __('Select condition') }}</option>
            @foreach(['new', 'good', 'fair', 'damaged'] as $condition)
                <option value="{{ $condition }}" @selected((string) old('condition', $equipment->condition ?? '') === $condition)>{{ __(ucfirst($condition)) }}</option>
            @endforeach
        </select>
        @error('condition')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="location" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Location') }}</label>
        <input id="location" name="location" value="{{ old('location', $equipment->location ?? '') }}" required class="min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        @error('location')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="status" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Status') }}</label>
        <select id="status" name="status" class="min-h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="available">{{ __('Available') }}</option>
            <option value="unavailable">{{ __('Unavailable') }}</option>
            <option value="maintenance">{{ __('Maintenance') }}</option>
            <option value="inactive">{{ __('Inactive') }}</option>
        </select>
        @error('status')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="description" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300 flex items-center justify-between">
            {{ __('Description') }}
            <button type="button" x-data="aiDescriptionGenerator()" @click="generate()" :disabled="loading" class="ml-3 px-3 py-1.5 text-xs font-medium text-brand-700 bg-brand-50 border border-brand-200 rounded-lg hover:bg-brand-100 dark:text-brand-300 dark:bg-brand-500/10 dark:border-brand-500/30 disabled:opacity-50">
                <span x-show="!loading">{{ __('Generate with AI') }}</span>
                <span x-show="loading">{{ __('Generating...') }}</span>
            </button>
        </label>
        <textarea id="description" name="description" rows="4" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">{{ old('description', $equipment->description ?? '') }}</textarea>
    </div>
    <div class="sm:col-span-2">
        <label for="image_url" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Equipment image URL') }}</label>
        <input id="image_url" name="image_url" type="url" value="{{ old('image_url', $equipment->image_url ?? '') }}" placeholder="https://images.unsplash.com/..." class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Optional. Use a public image URL that represents this equipment.') }}</p>
        @error('image_url')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="image" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Or upload an equipment image') }}</label>
        <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        @error('image')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
    </div>
    <fieldset class="rounded-xl border border-gray-200 p-4 dark:border-gray-800 sm:col-span-2">
        <legend class="px-2 text-theme-sm font-semibold text-gray-900 dark:text-white">{{ __('Energy profile') }}</legend>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([['power_watts','Power (W)','number'],['voltage','Voltage (V)','number'],['capacity_wh','Capacity (Wh)','number'],['efficiency','Efficiency (%)','number'],['technology','Technology','text'],['max_output','Max output (W)','number'],['operating_duration','Operating duration (h)','number']] as [$key,$label,$type])
                <div>
                    <label for="energy_{{ $key }}" class="mb-1 block text-theme-xs font-medium text-gray-700 dark:text-gray-300">{{ __($label) }}</label>
                    <input id="energy_{{ $key }}" name="energy[{{ $key }}]" type="{{ $type }}" step="0.01" value="{{ $energy[$key] ?? '' }}" class="min-h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-theme-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>
            @endforeach
        </div>
    </fieldset>
    <div class="rounded-2xl border border-brand-100 bg-brand-50/60 p-4 dark:border-brand-500/20 dark:bg-brand-500/10 sm:col-span-2" x-data="aiImageValidation()">
        <label for="images" class="mb-1.5 block text-theme-sm font-semibold text-gray-800 dark:text-white flex items-center justify-between">
            {{ __('Add gallery images') }}
            <button type="button" @click="validateImages()" :disabled="validating" class="ml-3 px-3 py-1.5 text-xs font-medium text-brand-700 bg-brand-50 border border-brand-200 rounded-lg hover:bg-brand-100 dark:text-brand-300 dark:bg-brand-500/10 dark:border-brand-500/30 disabled:opacity-50">
                <span x-show="!validating">{{ __('Validate with AI') }}</span>
                <span x-show="validating">{{ __('Validating...') }}</span>
            </button>
        </label>
        <input id="images" name="images[]" type="file" multiple accept="image/jpeg,image/png,image/webp" @change="onImagesSelected($event)" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-theme-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ __('Up to 8 photos. The first one becomes the gallery cover.') }}</p>
        @error('images.*')<p class="mt-1 text-xs text-error-600">{{ $message }}</p>@enderror
        
        <div x-show="results.length" class="mt-3 space-y-2" x-cloak>
            <template x-for="(result, index) in results" :key="index">
                <div class="flex items-center justify-between p-2 rounded-lg" :class="result.valid ? 'bg-success-50 border border-success-200 dark:bg-success-500/10 dark:border-success-500/30' : 'bg-error-50 border border-error-200 dark:bg-error-500/10 dark:border-error-500/30'">
                    <div class="flex items-center gap-2">
                        <span :class="result.valid ? 'text-success-600 dark:text-success-400' : 'text-error-600 dark:text-error-400'">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path x-show="result.valid" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/><path x-show="!result.valid" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        </span>
                        <span class="text-xs font-medium" x-text="result.filename"></span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs" x-text="result.confidence + '% confidence'"></span>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5" x-text="result.reason"></p>
                    </div>
                </div>
            </template>
        </div>
        
        <div x-show="error" class="mt-2 p-2 rounded-lg bg-error-50 border border-error-200 text-xs text-error-600 dark:bg-error-500/10 dark:border-error-500/30 dark:text-error-400" x-cloak x-text="error"></div>
    </div>
    <x-form.location-picker :location="old('location', $equipment->location ?? '')" :latitude="old('latitude', $equipment->latitude ?? null)" :longitude="old('longitude', $equipment->longitude ?? null)" location-id="location" :show-input="false" />
</div>

@push('scripts')
<script>
function aiDescriptionGenerator() {
    return {
        loading: false,

        async generate() {
            this.loading = true;
            const name = document.getElementById('name')?.value || '';
            const brand = document.getElementById('brand')?.value || '';
            const model = document.getElementById('model')?.value || '';
            const categorySelect = document.getElementById('category_id');
            const category = categorySelect ? categorySelect.options[categorySelect.selectedIndex]?.text || '' : '';
            const energy = {};
            document.querySelectorAll('[name^="energy["]').forEach(input => {
                const key = input.name.match(/energy\[(.+)\]/);
                if (key && input.value) {
                    energy[key[1]] = input.value;
                }
            });

            if (!name && !brand && !model) {
                alert('{{ __('Please fill in at least name, brand, or model first.') }}');
                this.loading = false;
                return;
            }

            try {
                const response = await fetch('{{ route("ai.generate-description") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({
                        name,
                        brand,
                        model,
                        category,
                        energy_profile: energy,
                    }),
                });

                const data = await response.json();

                if (data.description) {
                    const textarea = document.getElementById('description');
                    textarea.value = data.description;
                    textarea.dispatchEvent(new Event('input', { bubbles: true }));
                } else if (data.error) {
                    alert(data.error);
                }
            } catch (e) {
                alert('{{ __('Failed to generate description. Please try again.') }}');
            } finally {
                this.loading = false;
            }
        },
    };
}

function aiPriceSuggestion() {
    return {
        loading: false,
        suggestion: null,
        error: null,

        async suggest() {
            this.loading = true;
            this.error = null;
            this.suggestion = null;

            const name = document.getElementById('name')?.value || '';
            const brand = document.getElementById('brand')?.value || '';
            const categorySelect = document.getElementById('category_id');
            const category = categorySelect ? categorySelect.options[categorySelect.selectedIndex]?.text || '' : '';
            const powerWatts = document.getElementById('energy_power_watts')?.value || '';
            const capacityWh = document.getElementById('energy_capacity_wh')?.value || '';

            if (!name && !category) {
                this.error = '{{ __('Please fill in at least name and category first.') }}';
                this.loading = false;
                return;
            }

            try {
                const response = await fetch('{{ route("ai.suggest-price") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({
                        category,
                        brand: brand || null,
                        power_watts: powerWatts ? parseInt(powerWatts) : null,
                        capacity_wh: capacityWh ? parseInt(capacityWh) : null,
                    }),
                });

                const data = await response.json();

                if (data.suggestion) {
                    this.suggestion = data.suggestion;
                } else if (data.error) {
                    this.error = data.error;
                }
            } catch (e) {
                this.error = '{{ __('Failed to get price suggestion. Please try again.') }}';
            } finally {
                this.loading = false;
            }
        },

        applySuggestion() {
            if (this.suggestion?.suggested_price) {
                document.getElementById('price_per_day').value = this.suggestion.suggested_price;
                document.getElementById('price_per_day').dispatchEvent(new Event('input', { bubbles: true }));
                this.suggestion = null;
            }
        },
    };
}

function aiAutoCategorize() {
    return {
        loading: false,
        suggestion: null,
        error: null,

        async suggest() {
            this.loading = true;
            this.error = null;
            this.suggestion = null;

            const name = document.getElementById('name')?.value || '';
            const description = document.getElementById('description')?.value || '';

            if (!name && !description) {
                this.error = '{{ __('Please fill in at least name or description first.') }}';
                this.loading = false;
                return;
            }

            try {
                const response = await fetch('{{ route("ai.auto-categorize") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({
                        name,
                        description,
                    }),
                });

                const data = await response.json();

                if (data.category) {
                    this.suggestion = data;
                } else if (data.error) {
                    this.error = data.error;
                }
            } catch (e) {
                this.error = '{{ __('Failed to get category suggestion. Please try again.') }}';
            } finally {
                this.loading = false;
            }
        },

        applySuggestion() {
            if (this.suggestion?.category) {
                const select = document.getElementById('category_id');
                const options = select.options;
                for (let i = 0; i < options.length; i++) {
                    if (options[i].text.toLowerCase().includes(this.suggestion.category.toLowerCase()) ||
                        this.suggestion.category.toLowerCase().includes(options[i].text.toLowerCase())) {
                        select.value = options[i].value;
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                        break;
                    }
                }
                this.suggestion = null;
            }
        },
    };
}

function aiImageValidation() {
    return {
        selectedFiles: [],
        results: [],
        validating: false,
        error: null,

        onImagesSelected(event) {
            this.selectedFiles = Array.from(event.target.files);
            this.results = [];
            this.error = null;
        },

        async validateImages() {
            if (!this.selectedFiles.length) {
                this.error = '{{ __('Please select images first.') }}';
                return;
            }

            const name = document.getElementById('name')?.value || '';
            const categorySelect = document.getElementById('category_id');
            const category = categorySelect ? categorySelect.options[categorySelect.selectedIndex]?.text || '' : '';

            if (!name || !category) {
                this.error = '{{ __('Please fill in name and category first.') }}';
                return;
            }

            this.validating = true;
            this.error = null;
            this.results = [];

            for (const file of this.selectedFiles) {
                try {
                    const formData = new FormData();
                    formData.append('image', file);
                    formData.append('equipment_name', name);
                    formData.append('category', category);

                    const response = await fetch('{{ route("ai.validate-image") }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: formData,
                    });

                    const data = await response.json();

                    this.results.push({
                        filename: file.name,
                        valid: data.valid ?? true,
                        confidence: data.confidence ?? 50,
                        reason: data.reason ?? '{{ __('Validation completed') }}',
                    });
                } catch (e) {
                    this.results.push({
                        filename: file.name,
                        valid: false,
                        confidence: 0,
                        reason: '{{ __('Validation failed') }}',
                    });
                }
            }

            this.validating = false;
        },
    };
}
</script>
@endpush