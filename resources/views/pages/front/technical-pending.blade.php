@extends('layouts.front')

@section('content')
<section class="mx-auto w-full max-w-4xl px-6 py-12 sm:px-8 lg:px-10">
    <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
        <h1 class="text-title-sm font-semibold text-gray-900 dark:text-white">{{ $title }}</h1>
        <p class="mt-4 text-theme-sm leading-6 text-gray-600 dark:text-gray-300">{{ __('Technical records will appear here after equipment ownership is connected to the database.') }}</p>
        <a href="{{ route('front.owner-dashboard') }}" class="mt-6 inline-flex rounded-lg bg-brand-500 px-4 py-2 text-theme-sm font-medium text-white hover:bg-brand-600">{{ __('Back to owner workspace') }}</a>
    </div>
</section>
@endsection
