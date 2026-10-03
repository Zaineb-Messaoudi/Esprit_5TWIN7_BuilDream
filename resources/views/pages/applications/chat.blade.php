@extends('layouts.app')

@section('content')
    <div class="grid grid-cols-12 gap-4 md:gap-6 h-[calc(100vh-180px)]" x-data>
        <!-- Contacts List -->
        <div class="col-span-12 md:col-span-4 lg:col-span-3 flex flex-col bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="relative">
                    <input type="text" placeholder="Search messages..." class="w-full pl-9 pr-4 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-white focus:ring-brand focus:border-brand" />
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto">
                <template x-for="contact in Object.keys($store.chat.messages)" :key="contact">
                    <div
                        @click="$store.chat.setActiveContact(contact)"
                        :class="$store.chat.activeContact === contact ? 'bg-brand-50 dark:bg-brand-900/20' : ''"
                        class="p-4 flex items-center gap-3 hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer border-b border-gray-100 dark:border-gray-800 transition-colors"
                    >
                        <div class="relative">
                            <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center font-bold text-gray-600 dark:text-gray-400">
                                <span x-text="contact[0]"></span>
                            </div>
                            <span class="absolute bottom-0 right-0 w-3 h-3 bg-success border-2 border-white dark:border-gray-800 rounded-full"></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-baseline">
                                <p class="text-sm font-medium text-gray-800 dark:text-white truncate" x-text="contact"></p>
                                <span class="text-[10px] text-gray-400">12:45 PM</span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">Click to chat...</p>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Chat Window -->
        <div class="col-span-12 md:col-span-8 lg:col-span-9 flex flex-col bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <!-- Chat Header -->
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-brand text-white flex items-center justify-center font-bold">
                        <span x-text="$store.chat.activeContact[0]"></span>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-800 dark:text-white" x-text="$store.chat.activeContact"></p>
                        <span class="text-xs text-success">Online</span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.655l1.892 5.135a2 2 0 01-.512 1.787l-3.35 3.35a1 1 0 01-1.414 0L3.84 10.34A2 2 0 013 8V5z"></path></svg>
                    </button>
                    <button class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                    </button>
                </div>
            </div>

            <!-- Messages Area -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50 dark:bg-gray-900/30">
                <template x-for="msg in $store.chat.messages[$store.chat.activeContact]" :key="msg.text">
                    <div :class="msg.sent ? 'flex justify-end' : 'flex justify-start'">
                        <div :class="msg.sent ? 'bg-brand text-white rounded-tr-none' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 rounded-tl-none border border-gray-100 dark:border-gray-700'"
                             class="p-3 rounded-lg text-sm shadow-sm max-w-[70%]">
                            <span x-text="msg.text"></span>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Input Area -->
            <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                <div class="flex gap-3" x-data="{ message: '' }">
                    <input
                        x-model="message"
                        @keydown.enter="$store.chat.sendMessage($store.chat.activeContact, message); message = ''"
                        type="text"
                        placeholder="Type a message..."
                        class="flex-1 px-4 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-white focus:ring-brand focus:border-brand"
                    />
                    <button
                        @click="$store.chat.sendMessage($store.chat.activeContact, message); message = ''"
                        class="px-4 py-2 bg-brand text-white rounded-lg text-sm font-medium hover:bg-brand-600 transition-colors"
                    >
                        Send
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
