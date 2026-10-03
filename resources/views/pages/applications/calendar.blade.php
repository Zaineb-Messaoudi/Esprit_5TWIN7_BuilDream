@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Applications') }} / {{ __('Calendar') }}</p>
            <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">{{ __('Calendar') }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Schedule and manage events in the local calendar demonstration.') }}</p>
        </div>

        <x-calender-area />
    </div>
@endsection
