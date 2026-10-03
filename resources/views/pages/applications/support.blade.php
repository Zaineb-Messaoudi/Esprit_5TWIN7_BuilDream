@extends('layouts.app')

@section('content')
    <div class="grid grid-cols-12 gap-4 md:gap-6">
        <div class="col-span-12 xl:col-span-4 space-y-6">
            <x-common.component-card title="Ticket Details">
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Ticket #TKT-8842</span>
                        <x-ui.badge color="warning">Pending</x-ui.badge>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Database Connection Timeout</h3>
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-//9 5h.01M12 3h.01M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        <span>Created on Oct 2, 2026</span>
                    </div>
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                        <p class="text-xs text-gray-600 dark:text-gray-400 italic">"I'm getting a 504 Gateway Timeout when trying to access the analytics dashboard in the production environment."</p>
                    </div>
                    <div class="space-y-2">
                        <p class="text-xs font-bold text-gray-800 dark:text-white">Priority: High</p>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 h-2 rounded-full overflow-hidden">
                            <div class="bg-error h-full" style="width: 90%"></div>
                        </div>
                    </div>
                </div>
            </x-common.component-card>

            <x-common.component-card title="Assignee">
                <div class="flex items-center gap-3 p-2">
                    <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center font-bold text-gray-600 dark:text-gray-400">Z</div>
                    <div class="text-xs">
                        <p class="font-bold text-gray-800 dark:text-white">Zaineb Messaoudi</p>
                        <p class="text-gray-500 dark:text-gray-400">Senior DevOps Engineer</p>
                    </div>
                </div>
            </x-common.component-card>
        </div>

        <div class="col-span-12 xl:col-span-8">
            <x-common.component-card title="Conversation History">
                <div class="space-y-6">
                    <div class="flex gap-3">
                        <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 flex-shrink-0 flex items-center justify-center text-xs font-bold">U</div>
                        <div class="flex-1">
                            <div class="p-3 rounded-lg rounded-tl-none bg-gray-100 dark:bg-gray-800 text-sm text-gray-600 dark:text-gray-400">
                                I've tried restarting the server but the issue persists. Any updates?
                            </div>
                            <span class="text-[10px] text-gray-400 mt-1 block">10:15 AM</span>
                        </div>
                    </div>
                    <div class="flex gap-3 justify-end">
                        <div class="flex-1 text-end">
                            <div class="p-3 rounded-lg rounded-tr-none bg-brand text-white text-sm shadow-sm">
                                We are currently checking the VPC peering logs. It looks like a routing issue.
                            </div>
                            <span class="text-[10px] text-gray-400 mt-1 block text-right">10:30 AM</span>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-brand text-white flex-shrink-0 flex items-center justify-center text-xs font-bold">Z</div>
                    </div>
                </div>
                <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex gap-3">
                        <textarea placeholder="Reply to ticket..." class="flex-1 p-3 text-sm rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-800 dark:text-white focus:ring-brand focus:border-brand" rows="3"></textarea>
                        <x-ui.button label="Send" className="h-fit" />
                    </div>
                </div>
            </x-common.component-card>
        </div>
    </div>
@endsection
