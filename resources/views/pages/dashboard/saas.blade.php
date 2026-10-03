@extends('layouts.app')

@section('content')
  <div class="grid grid-cols-12 gap-4 md:gap-6">
    <div class="col-span-12 xl:col-span-8 space-y-6">
      <x-common.component-card title="MRR Growth">
        <div class="h-80 flex items-center justify-center bg-gray-50 dark:bg-gray-800/50 rounded-lg border-2 border-dashed border-gray-200 dark:border-gray-700">
           <p class="text-gray-400 italic">Revenue Growth Chart coming soon...</p>
        </div>
      </x-common.component-card>
    </div>

    <div class="col-span-12 xl:col-span-4 space-y-6">
      <x-common.component-card title="Subscription Tiers">
        <div class="space-y-4">
          <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700">
             <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-800 dark:text-white">Basic</span>
                <span class="text-xs font-bold text-brand">450 Users</span>
             </div>
             <div class="w-full bg-gray-200 dark:bg-gray-700 h-2 rounded-full overflow-hidden">
                <div class="bg-brand h-full" style="width: 45%"></div>
             </div>
          </div>
          <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700">
             <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-800 dark:text-white">Pro</span>
                <span class="text-xs font-bold text-brand">210 Users</span>
             </div>
             <div class="w-full bg-gray-200 dark:bg-gray-700 h-2 rounded-full overflow-hidden">
                <div class="bg-brand h-full" style="width: 21%"></div>
             </div>
          </div>
          <div class="p-4 rounded-lg border border-gray-200 dark:border-gray-700">
             <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-800 dark:text-white">Enterprise</span>
                <span class="text-xs font-bold text-brand">45 Users</span>
             </div>
             <div class="w-full bg-gray-200 dark:bg-gray-700 h-2 rounded-full overflow-hidden">
                <div class="bg-brand h-full" style="width: 5%"></div>
             </div>
          </div>
        </div>
      </x-common.component-card>
    </div>
  </div>
@endsection
