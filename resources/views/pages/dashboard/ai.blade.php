@extends('layouts.app')

@section('content')
  <div class="grid grid-cols-12 gap-4 md:gap-6">
    <div class="col-span-12 xl:col-span-8 space-y-6">
      <x-common.component-card title="AI Insight Stream">
        <div class="space-y-4">
          <div class="p-4 rounded-lg bg-blue-light/10 border border-blue-light/20 flex gap-4">
             <div class="p-2 bg-blue-light text-white rounded-lg h-fit">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
             </div>
             <div>
                <p class="text-sm font-medium text-gray-800 dark:text-white">Anomaly Detected</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Traffic spike detected in European region. Suggest scaling resources.</p>
                <span class="text-[10px] text-gray-400">2 mins ago</span>
             </div>
          </div>
          <div class="p-4 rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex gap-4">
             <div class="p-2 bg-gray-400 text-white rounded-lg h-fit">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
             </div>
             <div>
                <p class="text-sm font-medium text-gray-800 dark:text-white">Optimization Complete</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Database query optimization reduced latency by 15%.</p>
                <span class="text-[10px] text-gray-400">1 hour ago</span>
             </div>
          </div>
        </div>
      </x-common.component-card>
    </div>

    <div class="col-span-12 xl:col-span-4">
      <x-common.component-card title="AI Assistant">
        <div class="flex flex-col h-[400px]">
           <div class="flex-1 p-4 overflow-y-auto space-y-4">
              <div class="flex justify-start">
                 <div class="p-3 rounded-lg bg-gray-100 dark:bg-gray-800 text-xs text-gray-600 dark:text-gray-400 max-w-[80%]">
                    Hello! I'm your SolarShare AI. How can I help you optimize your dashboard today?
                 </div>
              </div>
              <div class="flex justify-end">
                 <div class="p-3 rounded-lg bg-brand text-white text-xs max-w-[80%]">
                    Show me the MRR growth for last quarter.
                 </div>
              </div>
           </div>
           <div class="p-4 border-t border-gray-200 dark:border-gray-700">
              <div class="relative">
                 <input type="text" placeholder="Ask AI..." class="w-full pl-3 pr-10 py-2 text-xs rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-800 dark:text-white focus:ring-brand focus:border-brand" />
                 <button class="absolute right-2 top-1/2 -translate-y-1/2 text-brand">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"></path></svg>
                 </button>
              </div>
           </div>
        </div>
      </x-common.component-card>
    </div>
  </div>
@endsection
