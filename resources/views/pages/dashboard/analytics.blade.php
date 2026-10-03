@extends('layouts.app')

@section('content')
  <div class="grid grid-cols-12 gap-4 md:gap-6">
    <div class="col-span-12 space-y-6 xl:col-span-7">
      <x-common.component-card title="Analytics Overview">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="p-4 rounded-lg bg-gray-100 dark:bg-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Visitors</p>
            <h3 class="text-2xl font-bold text-gray-800 dark:text-white">124.5k</h3>
            <span class="text-xs text-success font-medium">↑ 12% from last month</span>
          </div>
          <div class="p-4 rounded-lg bg-gray-100 dark:bg-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">Bounce Rate</p>
            <h3 class="text-2xl font-bold text-gray-800 dark:text-white">42.3%</h3>
            <span class="text-xs text-error font-medium">↓ 2% from last month</span>
          </div>
        </div>
      </x-common.component-card>

      <x-common.component-card title="Traffic Sources">
        <div class="h-64 flex items-center justify-center bg-gray-50 dark:bg-gray-800/50 rounded-lg border-2 border-dashed border-gray-200 dark:border-gray-700">
           <p class="text-gray-400 italic">Traffic Sources Chart coming soon...</p>
        </div>
      </x-common.component-card>
    </div>

    <div class="col-span-12 xl:col-span-5">
      <x-common.component-card title="User Demographics">
        <div class="h-full min-h-[300px] flex items-center justify-center bg-gray-50 dark:bg-gray-800/50 rounded-lg border-2 border-dashed border-gray-200 dark:border-gray-700">
           <p class="text-gray-400 italic">Demographics Chart coming soon...</p>
        </div>
      </x-common.component-card>
    </div>
  </div>
@endsection
