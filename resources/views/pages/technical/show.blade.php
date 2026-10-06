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
        <div class="mt-6 flex flex-wrap items-center gap-3">
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
