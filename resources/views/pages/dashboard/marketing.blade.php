@extends('layouts.app')

@section('content')
  <div class="grid grid-cols-12 gap-4 md:gap-6">
    <div class="col-span-12 space-y-6 xl:col-span-8">
      <x-common.component-card title="Campaign Performance">
        <div class="overflow-x-auto">
          <table class="w-full text-start">
            <thead>
              <tr class="text-sm font-medium text-gray-400 border-b border-gray-200 dark:border-gray-700">
                <th class="pb-3 px-4">Campaign</th>
                <th class="pb-3 px-4">Status</th>
                <th class="pb-3 px-4">Spend</th>
                <th class="pb-3 px-4">ROAS</th>
                <th class="pb-3 px-4">Action</th>
              </tr>
            </thead>
            <tbody class="text-sm text-gray-600 dark:text-gray-400">
              <tr class="border-b border-gray-100 dark:border-gray-800">
                <td class="py-4 px-4 font-medium text-gray-800 dark:text-white">Summer Sale 2026</td>
                <td class="py-4 px-4"><x-ui.badge color="success">Active</x-ui.badge></td>
                <td class="py-4 px-4">$1,200</td>
                <td class="py-4 px-4">3.4x</td>
                <td class="py-4 px-4"><a href="#" class="text-brand hover:underline">Edit</a></td>
              </tr>
              <tr class="border-b border-gray-100 dark:border-gray-800">
                <td class="py-4 px-4 font-medium text-gray-800 dark:text-white">Winter Clearance</td>
                <td class="py-4 px-4"><x-ui.badge color="warning">Paused</x-ui.badge></td>
                <td class="py-4 px-4">$800</td>
                <td class="py-4 px-4">2.1x</td>
                <td class="py-4 px-4"><a href="#" class="text-brand hover:underline">Edit</a></td>
              </tr>
            </tbody>
          </table>
        </div>
      </x-common.component-card>
    </div>

    <div class="col-span-12 xl:col-span-4 space-y-6">
      <x-common.component-card title="Budget Allocation">
        <div class="h-64 flex items-center justify-center bg-gray-50 dark:bg-gray-800/50 rounded-lg border-2 border-dashed border-gray-200 dark:border-gray-700">
           <p class="text-gray-400 italic">Budget Pie Chart coming soon...</p>
        </div>
      </x-common.component-card>
    </div>
  </div>
@endsection
