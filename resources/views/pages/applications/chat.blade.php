@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Applications') }} / {{ __('Chat') }}</p>
            <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">{{ __('Team chat') }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Local conversation demo. Messages and attachments stay in this browser session.') }}</p>
        </div>

        <div
            x-data="{ search: '', mobileChatOpen: false, draft: '', attachment: '' }"
            class="grid h-[calc(100vh-230px)] min-h-[480px] grid-cols-12 gap-4 md:gap-6"
        >
            <aside
                :class="mobileChatOpen ? 'hidden md:flex' : 'flex'"
                class="col-span-12 min-h-0 flex-col overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 md:col-span-4 xl:col-span-3"
                aria-label="{{ __('Conversations') }}"
            >
                <div class="border-b border-gray-200 p-4 dark:border-gray-700">
                    <label for="chat-search" class="sr-only">{{ __('Search contacts and messages') }}</label>
                    <input id="chat-search" x-model="search" type="search" placeholder="{{ __('Search contacts and messages...') }}" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                </div>
                <div class="min-h-0 flex-1 overflow-y-auto">
                    <template x-for="contact in $store.chat.visibleContacts(search)" :key="contact">
                        <button
                            type="button"
                            @click="$store.chat.setActiveContact(contact); mobileChatOpen = true"
                            :aria-current="$store.chat.activeContact === contact ? 'true' : null"
                            :class="$store.chat.activeContact === contact ? 'bg-brand-50 dark:bg-brand-500/10' : 'hover:bg-gray-50 dark:hover:bg-gray-900/50'"
                            class="flex w-full items-center gap-3 border-b border-gray-100 p-4 text-start transition-colors dark:border-gray-700"
                        >
                            <span class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-50 font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-300" x-text="contact.slice(0, 1)" aria-hidden="true">
                                <span class="absolute -end-0.5 -bottom-0.5 h-3 w-3 rounded-full border-2 border-white dark:border-gray-800" :class="$store.chat.contacts[contact].online ? 'bg-success-500' : 'bg-gray-400'"></span>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="flex items-center justify-between gap-2">
                                    <span class="truncate text-sm font-medium text-gray-800 dark:text-white" x-text="contact"></span>
                                    <span x-show="$store.chat.contacts[contact].unread" x-text="$store.chat.contacts[contact].unread" class="flex h-5 min-w-5 items-center justify-center rounded-full bg-brand-500 px-1 text-[10px] font-semibold text-white" :aria-label="$store.chat.contacts[contact].unread + ' unread messages'"></span>
                                </span>
                                <span class="mt-1 block truncate text-xs text-gray-500 dark:text-gray-400" x-text="$store.chat.messages[contact].at(-1)?.text || '{{ __('Attachment') }}'"></span>
                            </span>
                        </button>
                    </template>
                    <p x-show="$store.chat.visibleContacts(search).length === 0" x-cloak class="p-6 text-center text-sm text-gray-500 dark:text-gray-400" role="status">{{ __('No conversations match your search.') }}</p>
                </div>
            </aside>

            <section
                :class="mobileChatOpen ? 'flex' : 'hidden md:flex'"
                class="col-span-12 min-h-0 flex-col overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 md:col-span-8 xl:col-span-9"
                aria-label="{{ __('Conversation') }}"
            >
                <header class="flex items-center justify-between gap-3 border-b border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/70">
                    <div class="flex min-w-0 items-center gap-3">
                        <button type="button" @click="mobileChatOpen = false" class="rounded-lg p-2 text-gray-500 hover:bg-gray-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:hover:bg-gray-700 md:hidden" aria-label="{{ __('Back to conversations') }}">‹</button>
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-500 font-semibold text-white" x-text="$store.chat.activeContact.slice(0, 1)" aria-hidden="true"></span>
                        <div class="min-w-0">
                            <h2 class="truncate text-sm font-semibold text-gray-800 dark:text-white" x-text="$store.chat.activeContact"></h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400" x-text="$store.chat.contacts[$store.chat.activeContact].online ? '{{ __('Online') }}' : '{{ __('Away') }}'"></p>
                        </div>
                    </div>
                    <x-ui.badge variant="light" color="gray">{{ __('Demo') }}</x-ui.badge>
                </header>

                <div class="min-h-0 flex-1 space-y-4 overflow-y-auto bg-gray-50 p-4 dark:bg-gray-900/40 sm:p-6" aria-live="polite" aria-relevant="additions">
                    <template x-for="message in $store.chat.messages[$store.chat.activeContact]" :key="message.id">
                        <div :class="message.sent ? 'justify-end' : 'justify-start'" class="flex">
                            <div :class="message.sent ? 'rounded-te-none bg-brand-500 text-white' : 'rounded-ts-none border border-gray-100 bg-white text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300'" class="max-w-[85%] rounded-xl px-4 py-3 text-sm shadow-theme-xs sm:max-w-[70%]">
                                <p x-show="message.text" x-text="message.text"></p>
                                <p x-show="message.attachment" x-cloak class="mt-2 flex items-center gap-2 text-xs" x-text="'📎 ' + message.attachment"></p>
                            </div>
                        </div>
                    </template>
                    <p x-show="!$store.chat.messages[$store.chat.activeContact].length" class="py-10 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('No messages yet. Start the conversation below.') }}</p>
                </div>

                <form class="border-t border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-800 sm:p-4" @submit.prevent="$store.chat.sendMessage($store.chat.activeContact, draft, attachment); draft = ''; attachment = ''; $refs.chatAttachment.value = ''">
                    <label for="chat-message" class="sr-only">{{ __('Write a message') }}</label>
                    <textarea id="chat-message" x-model="draft" @keydown.ctrl.enter="$store.chat.sendMessage($store.chat.activeContact, draft, attachment); draft = ''; attachment = ''; $refs.chatAttachment.value = ''" rows="2" placeholder="{{ __('Write a message...') }}" class="w-full resize-y rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
                    <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-2">
                            <label for="chat-attachment" class="cursor-pointer rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-50 focus-within:ring-2 focus-within:ring-brand-500 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700">{{ __('Attach file') }}</label>
                            <input x-ref="chatAttachment" id="chat-attachment" type="file" class="sr-only" @change="attachment = $event.target.files[0]?.name || ''" />
                            <span x-show="attachment" x-cloak class="max-w-40 truncate text-xs text-gray-500 dark:text-gray-400" x-text="attachment"></span>
                        </div>
                        <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">{{ __('Send message') }}</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection
