@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb><div class="flex items-center gap-2"><a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-brand-600">{{ __('Dashboard') }}</a><span>/</span><span class="text-gray-800 dark:text-white">{{ __('Categories') }}</span></div></x-common.page-breadcrumb>
    <div class="flex flex-wrap items-center justify-between gap-4"><h1 class="text-title-md font-semibold text-gray-800 dark:text-white">{{ __('Categories') }}</h1><a href="{{ route('admin.categories.create') }}" class="inline-flex min-h-10 items-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">+ {{ __('Create category') }}</a></div>
    @if(session('status'))<p role="status" class="rounded-lg bg-success-50 px-4 py-3 text-sm text-success-700 dark:bg-success-500/10 dark:text-success-300">{{ __('Category saved successfully.') }}</p>@endif
    @error('category')<p role="alert" class="rounded-lg bg-error-50 px-4 py-3 text-sm text-error-700 dark:bg-error-500/10 dark:text-error-300">{{ $message }}</p>@enderror
    <x-common.component-card>
        <div class="overflow-x-auto"><table class="w-full min-w-[620px] text-start"><thead><tr class="border-b border-gray-200 text-xs uppercase text-gray-500 dark:border-gray-800"><th class="p-3">{{ __('Name') }}</th><th class="p-3">{{ __('Status') }}</th><th class="p-3">{{ __('Equipment') }}</th><th class="p-3 text-end">{{ __('Actions') }}</th></tr></thead><tbody class="divide-y divide-gray-100 dark:divide-gray-800">@forelse($categories as $category)<tr><td class="p-3 font-medium text-gray-900 dark:text-white">{{ $category->name }}<p class="text-xs text-gray-500">{{ Str::limit($category->description, 80) }}</p></td><td class="p-3">{{ __(ucfirst($category->status)) }}</td><td class="p-3">{{ $category->equipment_count }}</td><td class="p-3 text-end"><a href="{{ route('admin.categories.edit', $category) }}" class="text-brand-600 hover:underline">{{ __('Edit') }}</a><form class="ms-3 inline" action="{{ route('admin.categories.destroy', $category) }}" method="POST">@csrf @method('DELETE')<button class="text-error-600 hover:underline" onclick="return confirm(@js(__('Delete this category?')))" type="submit">{{ __('Delete') }}</button></form></td></tr>@empty<tr><td colspan="4" class="p-8 text-center text-sm text-gray-500">{{ __('No categories found.') }}</td></tr>@endforelse</tbody></table></div>
        <div class="mt-4">{{ $categories->links() }}</div>
    </x-common.component-card>
</div>
@endsection
