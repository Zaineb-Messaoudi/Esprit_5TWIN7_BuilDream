@extends($layout)

@section('content')
<div class="mx-auto w-full max-w-7xl px-6 py-8 sm:px-8 lg:px-10">
    @if ($layout === 'layouts.app') <x-common.page-breadcrumb :title="$title" /> @endif
    <h1 class="mb-5 text-title-md font-semibold text-gray-900 dark:text-white">{{ $title }}</h1>
    @include('pages.technical.partials.navigation')
    <div class="mb-5 flex justify-end">
        <a href="{{ route($prefix.$resource.'.create') }}" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">{{ __('Create record') }}</a>
    </div>
    @if (session('status')) <p role="status" class="mb-4 rounded-lg bg-success-50 p-3 text-success-700 dark:bg-success-500/15 dark:text-success-300">{{ session('status') }}</p> @endif
    <x-common.component-card :title="$title">
        <div class="overflow-x-auto">
            <table class="w-full text-start text-sm text-gray-700 dark:text-gray-200">
                <thead><tr class="border-b border-gray-200 text-start dark:border-gray-700"><th class="p-3">{{ __('ID') }}</th><th class="p-3">{{ __('Equipment / maintenance') }}</th><th class="p-3">{{ __('Date') }}</th><th class="p-3">{{ __('Status / damage') }}</th><th class="p-3 text-end">{{ __('Actions') }}</th></tr></thead>
                <tbody>
                @forelse ($records as $item)
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <td class="p-3">#{{ $item->id }}</td>
                        <td class="p-3">{{ $resource === 'reports' ? ($item->maintenance?->equipment?->name ?? '#'.$item->maintenance_id) : ($item->equipment?->name ?? '#'.$item->equipment_id) }}</td>
                        <td class="p-3">{{ ($resource === 'maintenances' ? $item->start_date : ($resource === 'reports' ? $item->report_date : $item->inspection_date))?->format('Y-m-d') }}</td>
                        <td class="p-3">{{ $resource === 'maintenances' ? __($item->status) : ($resource === 'inspections' ? ($item->damage_detected ? __('Damage detected') : __('No damage')) : '—') }}</td>
                        <td class="p-3 text-end"><a class="text-brand-600 hover:underline dark:text-brand-400" href="{{ route($prefix.$resource.'.show', $item) }}">{{ __('Details') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-6 text-center text-gray-500 dark:text-gray-400">{{ __('No records yet.') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $records->links() }}</div>
    </x-common.component-card>
</div>
@endsection
