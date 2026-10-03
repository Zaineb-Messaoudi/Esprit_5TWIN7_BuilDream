@extends('layouts.app')

@section('content')
    <div class="grid grid-cols-12 gap-4 md:gap-6" x-data="{
        selectedDate: new Date().getDate(),
        currentMonth: 'October',
        currentYear: 2026,
        events: {
            3: [{ title: 'Project Sync', time: '10:00 AM', type: 'work' }],
            12: [{ title: 'Client Demo', time: '02:00 PM', type: 'client' }],
            25: [{ title: 'Team Lunch', time: '12:30 PM', type: 'social' }]
        }
    }">
        <!-- Calendar Sidebar -->
        <div class="col-span-12 xl:col-span-3 space-y-6">
            <x-common.component-card title="Calendar Controls">
                <div class="space-y-4">
                    <div class="flex justify-between items-center mb-4">
                        <button class="p-2 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        </button>
                        <span class="text-sm font-semibold text-gray-800 dark:text-white" x-text="currentMonth + ' ' + currentYear"></span>
                        <button class="p-2 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-7 gap-1 text-center text-xs font-medium text-gray-400 mb-2">
                        <div>Su</div><div>Mo</div><div>Tu</div><div>We</div><div>Th</div><div>Fr</div><div>Sa</div>
                    </div>
                    <div class="grid grid-cols-7 gap-1 text-center text-sm">
                        @for($i = 1; $i <= 31; $i++)
                            <div
                                @click="selectedDate = {{ $i }}"
                                :class="selectedDate === {{ $i }} ? 'bg-brand text-white font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'"
                                class="p-2 rounded-md cursor-pointer transition-colors"
                            >
                                {{ $i }}
                            </div>
                        @endfor
                    </div>
                </div>
            </x-common.component-card>

            <x-common.component-card title="Events for Selected Day">
                <div class="space-y-4">
                    <template x-for="event in events[selectedDate]">
                        <div class="flex gap-3 p-2 rounded-lg bg-gray-50 dark:bg-gray-800 border-l-4 border-brand">
                            <div class="text-xs">
                                <p class="font-bold text-gray-800 dark:text-white" x-text="event.title"></p>
                                <p class="text-gray-500 dark:text-gray-400" x-text="event.time"></p>
                            </div>
                        </div>
                    </template>
                    <template x-if="!events[selectedDate]">
                        <p class="text-xs text-center text-gray-400 italic">No events for this day.</p>
                    </template>
                </div>
            </x-common.component-card>
        </div>

        <!-- Main Calendar Area -->
        <div class="col-span-12 xl:col-span-9">
            <x-common.component-card>
                <div class="flex justify-between items-center mb-6">
                    <div class="flex gap-2">
                        <button class="px-3 py-1 text-xs font-medium rounded-md bg-brand text-white">Month</button>
                        <button class="px-3 py-1 text-xs font-medium rounded-md bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">Week</button>
                        <button class="px-3 py-1 text-xs font-medium rounded-md bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">Day</button>
                    </div>
                    <x-ui.button label="Add Event" icon="plus" />
                </div>

                <div class="h-[600px] w-full bg-gray-50 dark:bg-gray-800/30 rounded-lg border-2 border-dashed border-gray-200 dark:border-gray-700 flex items-center justify-center">
                    <div class="text-center">
                        <p class="text-gray-400 italic">FullCalendar.js integration active</p>
                        <p class="text-xs text-gray-400 mt-1">Viewing events for <span class="font-bold text-brand" x-text="selectedDate"></span> October</p>
                    </div>
                </div>
            </x-common.component-card>
        </div>
    </div>
@endsection
