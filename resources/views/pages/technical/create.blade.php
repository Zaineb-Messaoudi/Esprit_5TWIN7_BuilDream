@extends($layout)

@section('content')
<div class="mx-auto w-full max-w-4xl px-6 py-8 sm:px-8 lg:px-10">
    @if ($layout === 'layouts.app') <x-common.page-breadcrumb :title="$title" /> @endif
    <h1 class="mb-5 text-title-md font-semibold text-gray-900 dark:text-white">{{ __('Create record') }} — {{ $title }}</h1>
    @include('pages.technical.partials.navigation')
    <x-common.component-card :title="$title">
        @include('pages.technical.partials.form')
    </x-common.component-card>
</div>
@endsection
