@extends($layout)

@section('content')
<div class="mx-auto w-full max-w-4xl px-6 py-8 sm:px-8 lg:px-10">
    @if ($layout === 'layouts.app') <x-common.page-breadcrumb :title="$title" /> @endif
    <h1 class="mb-5 text-title-md font-semibold text-gray-900 dark:text-white">{{ $title }} #{{ $record->id }}</h1>
    @include('pages.technical.partials.navigation')
    @if (session('status')) <p role="status" class="mb-4 rounded-lg bg-success-50 p-3 text-success-700 dark:bg-success-500/15 dark:text-success-300">{{ session('status') }}</p> @endif
    <x-common.component-card :title="$title">
        <dl class="grid gap-4 sm:grid-cols-2">
            @foreach ($fields as $name => $field)
                @php $value = $record->$name; @endphp
                <div class="min-w-0"><dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __($field['label']) }}</dt>
                    <dd class="mt-1 break-words text-gray-900 dark:text-white">
                        @if ($name === 'equipment_id') {{ $record->equipment?->name ?? '#'.$value }}
                        @elseif ($name === 'maintenance_id') {{ '#'.$value.' — '.($record->maintenance?->equipment?->name ?? '') }}
                        @elseif ($name === 'rental_id') {{ $value ? '#'.$value : '—' }}
                        @elseif ($name === 'inspection_checklist_id') {{ $record->checklist?->name ?? '—' }}
                        @elseif ($name === 'checklist_results')
                            @forelse ($value ?? [] as $result)
                                <span class="mb-1 block">{{ $result['label'] }}: {{ $result['passed'] ? __('Validated') : __('Not validated') }}</span>
                            @empty — @endforelse
                        @elseif ($name === 'damage_detected') {{ $value ? __('Yes') : __('No') }}
                        @elseif ($value instanceof \Carbon\CarbonInterface) {{ $value->format('Y-m-d') }}
                        @else {{ $value ?: '—' }} @endif
                    </dd>
                </div>
            @endforeach
        </dl>
        @if ($resource === 'maintenances' && $record->report)
            <p class="mt-5"><a class="text-brand-600 hover:underline dark:text-brand-400" href="{{ route($prefix.'reports.show', $record->report) }}">{{ __('View maintenance report') }}</a></p>
        @endif
        @if ($resource === 'maintenances')
            @php
                $beforePhoto = $record->photos->firstWhere('type', 'before');
                $afterPhoto = $record->photos->firstWhere('type', 'after');
            @endphp
            <section class="mt-7 border-t border-gray-200 pt-6 dark:border-gray-800" aria-labelledby="maintenance-photos-title">
                <h2 id="maintenance-photos-title" class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Before and after photos') }}</h2>
                @if ($beforePhoto && $afterPhoto)
                    <div class="mt-4"><x-maintenance.before-after-comparison :before="$beforePhoto" :after="$afterPhoto" /></div>
                @endif
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    @foreach (['before' => __('Before'), 'after' => __('After')] as $photoType => $photoLabel)
                        <form method="POST" enctype="multipart/form-data" action="{{ route($prefix.'maintenances.photos.store', $record) }}" class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                            @csrf
                            <input type="hidden" name="type" value="{{ $photoType }}">
                            <label for="photo-{{ $photoType }}" class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ $photoLabel }}</label>
                            <input id="photo-{{ $photoType }}" type="file" name="photo" accept="image/jpeg,image/png,image/webp" required class="mt-2 block w-full text-sm text-gray-700 file:me-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-brand-700 dark:text-gray-300 dark:file:bg-brand-500/15 dark:file:text-brand-300">
                            <button type="submit" class="mt-3 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">{{ __('Upload photo') }}</button>
                        </form>
                    @endforeach
                </div>
                @if ($record->photos->isNotEmpty())
                    <ul class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach ($record->photos as $photo)
                            <li class="flex items-center gap-3 rounded-xl border border-gray-200 p-3 dark:border-gray-700">
                                <img src="{{ $photo->url }}" alt="{{ $photo->type === 'before' ? __('Before maintenance') : __('After maintenance') }}" class="size-16 rounded-lg object-cover">
                                <span class="min-w-0 flex-1 truncate text-sm text-gray-700 dark:text-gray-300">{{ $photo->original_name }}</span>
                                <form method="POST" action="{{ route($prefix.'maintenances.photos.destroy', [$record, $photo]) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-sm text-error-600 dark:text-error-400">{{ __('Delete') }}</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
                @error('photo') <p class="mt-2 text-sm text-error-600 dark:text-error-400" role="alert">{{ $message }}</p> @enderror
            </section>
        @endif
        <div class="mt-6 flex flex-wrap items-center gap-3">
            @if (in_array($resource, ['maintenances', 'inspections'], true))
                <a href="{{ route($prefix.$resource.'.pdf', $record) }}" class="rounded-lg border border-brand-300 px-4 py-2 text-sm text-brand-700 dark:border-brand-700 dark:text-brand-300">{{ __('Download PDF') }}</a>
            @endif
            <a href="{{ route($prefix.$resource.'.edit', $record) }}" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">{{ __('Edit record') }}</a>
            <a href="{{ route($prefix.$resource.'.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm dark:border-gray-700 dark:text-white">{{ __('Back to list') }}</a>
            <form method="POST" action="{{ route($prefix.$resource.'.destroy', $record) }}" class="ms-auto">
                @csrf @method('DELETE')
                <button type="submit" data-confirm="{{ __('Delete this record?') }}" onclick="return window.confirm(this.dataset.confirm)" class="rounded-lg border border-error-500 px-4 py-2 text-sm text-error-600 dark:text-error-400">{{ __('Delete') }}</button>
            </form>
        </div>
    </x-common.component-card>
</div>
@endsection
