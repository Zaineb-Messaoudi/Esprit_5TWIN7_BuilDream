@extends('layouts.app')

@section('content')
  <div class="grid grid-cols-12 gap-4 md:gap-6">
    <div class="col-span-12 xl:col-span-4 space-y-6">
       <x-common.component-card title="Quick Add Lead">
          <form class="space-y-4">
             <x-form.input label="Full Name" placeholder="John Doe" />
             <x-form.input label="Email" type="email" placeholder="john@example.com" />
             <x-form.select label="Lead Source" />
             <x-ui.button label="Add Lead" className="w-full" />
          </form>
       </x-common.component-card>
    </div>

    <div class="col-span-12 xl:col-span-8">
      <x-common.component-card title="Pipeline Overview">
        <div class="overflow-x-auto">
          <table class="w-full text-start">
            <thead class="text-sm font-medium text-gray-400 border-b border-gray-200 dark:border-gray-700">
              <tr>
                <th class="pb-3 px-4">Lead Name</th>
                <th class="pb-3 px-4">Value</th>
                <th class="pb-3 px-4">Stage</th>
                <th class="pb-3 px-4">Score</th>
                <th class="pb-3 px-4">Action</th>
              </tr>
            </thead>
            <tbody class="text-sm text-gray-600 dark:text-gray-400">
              <tr class="border-b border-gray-100 dark:border-gray-800">
                <td class="py-4 px-4 font-medium text-gray-800 dark:text-white">Acme Corp</td>
                <td class="py-4 px-4">$5,000</td>
                <td class="py-4 px-4"><x-ui.badge color="blue-light">Negotiation</x-ui.badge></td>
                <td class="py-4 px-4">85%</td>
                <td class="py-4 px-4"><a href="#" class="text-brand hover:underline">View</a></td>
              </tr>
              <tr class="border-b border-gray-100 dark:border-gray-800">
                <td class="py-4 px-4 font-medium text-gray-800 dark:text-white">Global Tech</td>
                <td class="py-4 px-4">$12,000</td>
                <td class="py-4 px-4"><x-ui.badge color="warning">Discovery</x-ui.badge></td>
                <td class="py-4 px-4">40%</td>
                <td class="py-4 px-4"><a href="#" class="text-brand hover:underline">View</a></td>
              </tr>
            </tbody>
          </table>
        </div>
      </x-common.component-card>
    </div>
  </div>
@endsection
