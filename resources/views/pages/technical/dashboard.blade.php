@extends('layouts.app')

@section('content')
<div x-data="maintenanceDashboard({ costs: @js(route('admin.technical.dashboard.costs')), failures: @js(route('admin.technical.dashboard.failures')), intervals: @js(route('admin.technical.dashboard.intervals')) })" x-init="load()">
    <x-common.page-breadcrumb :title="$title" />
    <div class="grid gap-6 xl:grid-cols-2">
        <x-common.component-card :title="__('Maintenance costs by month')"><div class="h-80"><canvas x-ref="costs"></canvas></div></x-common.component-card>
        <x-common.component-card :title="__('Failures by category')"><div class="h-80"><canvas x-ref="failures"></canvas></div></x-common.component-card>
        <div class="xl:col-span-2"><x-common.component-card :title="__('Average time between failures')"><div class="h-80"><canvas x-ref="intervals"></canvas></div></x-common.component-card></div>
    </div>
    <p x-show="error" x-cloak class="mt-4 rounded-lg bg-error-50 p-3 text-error-700 dark:bg-error-500/15 dark:text-error-400" role="alert">{{ __('Dashboard data could not be loaded.') }}</p>
</div>
@endsection
