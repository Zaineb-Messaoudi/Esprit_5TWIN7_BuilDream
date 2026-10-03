@extends('layouts.app')

@section('content')
    <div class="grid grid-cols-12 gap-4 md:gap-6 h-[calc(100vh-180px)]" x-data>
        <!-- Email Folders -->
        <div class="col-span-12 md:col-span-3 lg:col-span-2 flex flex-col bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-4">
                <x-ui.button label="Compose" className="w-full justify-center" />
            </div>
            <div class="flex-1 overflow-y-auto p-2 space-y-1">
                <template x-for="folder in Object.keys($store.email.folders)" :key="folder">
                    <a
                        @click.prevent="$store.email.setFolder(folder)"
                        :class="$store.email.activeFolder === folder ? 'bg-brand text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors cursor-pointer"
                    >
                        <span x-text="folder"></span>
                        <template x-if="$store.email.folders[folder].length > 0">
                            <span class="ms-auto bg-brand-600 text-white text-[10px] px-2 py-0.5 rounded-full" x-text="$store.email.folders[folder].length"></span>
                        </template>
                    </a>
                </template>
            </div>
        </div>

        <!-- Email List -->
        <div class="col-span-12 md:col-span-4 lg:col-span-4 flex flex-col bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="relative">
                    <input type="text" placeholder="Search emails..." class="w-full pl-9 pr-4 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-white focus:ring-brand focus:border-brand" />
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto">
                <template x-for="email in $store.email.folders[$store.email.activeFolder]" :key="email.id">
                    <div
                        @click="$store.email.selectEmail(email)"
                        :class="$store.email.selectedEmail?.id === email.id ? 'bg-brand-50 dark:bg-brand-900/20' : ''"
                        class="p-4 border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer transition-colors"
                    >
                        <div class="flex justify-between items-start mb-1">
                            <p class="text-sm font-bold text-gray-800 dark:text-white" x-text="email.from"></p>
                            <span class="text-[10px] text-gray-400" x-text="email.date"></span>
                        </div>
                        <p class="text-xs font-medium text-gray-600 dark:text-gray-300 mb-1" x-text="email.subject"></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="email.body"></p>
                    </div>
                </template>
                <template x-if="$store.email.folders[$store.email.activeFolder].length === 0">
                    <div class="p-8 text-center text-sm text-gray-400">No emails in this folder.</div>
                </template>
            </div>
        </div>

        <!-- Email Content -->
        <div class="col-span-12 md:col-span-5 lg:col-span-6 flex flex-col bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <template x-if="$store.email.selectedEmail">
                <div class="flex flex-col h-full">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center font-bold text-gray-600 dark:text-gray-400" x-text="$store.email.selectedEmail.from[0]"></div>
                            <div>
                                <p class="text-sm font-bold text-gray-800 dark:text-white" x-text="$store.email.selectedEmail.from"></p>
                                <span class="text-xs text-gray-500 dark:text-gray-400">support@solshare.com</span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a2 2 0 012-2h3.22l.44 2.89l.56 3.37L8.4 10.3l-2.4-2.4a2 2 0 010-2.83l2.83-2.83z"></path></svg>
                            </button>
                            <button class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex-1 p-6 overflow-y-auto bg-white dark:bg-gray-900">
                        <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4" x-text="$store.email.selectedEmail.subject"></h2>
                        <div class="space-y-4 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                            <p x-text="$store.email.selectedEmail.body"></p>
                        </div>
                    </div>
                    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                        <textarea placeholder="Reply to email..." class="w-full px-4 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-white focus:ring-brand focus:border-brand" rows="3"></textarea>
                        <div class="flex justify-end mt-2">
                            <x-ui.button label="Send Reply" className="px-6" />
                        </div>
                    </div>
                </div>
            </template>
            <template x-if="!$store.email.selectedEmail">
                <div class="flex-1 flex items-center justify-center text-center p-8">
                    <div>
                        <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        <p class="text-gray-500 dark:text-gray-400">Select an email to read</p>
                    </div>
                </div>
            </template>
        </div>
    </div>
@endsection
