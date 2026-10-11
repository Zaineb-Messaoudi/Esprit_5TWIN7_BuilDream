<nav aria-label="{{ __('Technical records') }}" class="mb-6 flex flex-wrap gap-2">
    @foreach (['maintenances' => 'Maintenances', 'reports' => 'Maintenance reports', 'inspections' => 'Inspections'] as $key => $label)
        <a href="{{ route($prefix.$key.'.index') }}" class="rounded-lg border px-4 py-2 text-sm font-medium {{ $resource === $key ? 'border-brand-500 bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300' : 'border-gray-200 bg-white text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200' }}">{{ __($label) }}</a>
    @endforeach
</nav>
