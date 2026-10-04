@extends('layouts.fullscreen-layout')

@php
    $title = $page['title'];
    $toneClasses = [
        'primary' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300',
        'success' => 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-300',
        'warning' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-300',
        'error' => 'bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-300',
    ];
@endphp

@section('content')
    <main class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-8 text-center shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 sm:p-10">
        <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full text-lg font-semibold {{ $toneClasses[$page['tone']] ?? $toneClasses['primary'] }}">
            {{ $page['code'] }}
        </span>
        <p class="mt-6 text-sm font-medium text-brand-600 dark:text-brand-300">SolarShare Admin · Page example</p>
        <h1 class="mt-2 text-title-md font-semibold text-gray-800 dark:text-white/90">{{ $page['heading'] }}</h1>
        <p class="mt-3 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $page['message'] }}</p>
        <a href="{{ route($page['action_route'] ?? 'dashboard') }}" class="mt-7 inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white transition hover:bg-brand-600">
            {{ $page['action'] }}
        </a>
    </main>
@endsection
