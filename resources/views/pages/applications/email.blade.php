@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Applications') }} / {{ __('Email') }}</p>
            <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">{{ __('Email') }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Local mailbox demo. Messages and attachments are not sent to an email service.') }}</p>
        </div>

        <div x-data="{ search: '', reply: '', mobileDetail: false }" class="grid h-[calc(100vh-230px)] min-h-[480px] grid-cols-12 gap-4 md:gap-6">
            <aside class="col-span-12 flex min-h-0 flex-col overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 md:col-span-3 xl:col-span-2" aria-label="{{ __('Mail folders') }}">
                <div class="p-4">
                    <button type="button" @click="$store.email.openComposer()" class="w-full rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">{{ __('Compose email') }}</button>
                </div>
                <nav class="min-h-0 flex-1 overflow-y-auto p-2" aria-label="{{ __('Mail folders') }}">
                    <template x-for="folder in Object.keys($store.email.folders)" :key="folder">
                        <button
                            type="button"
                            @click="$store.email.setFolder(folder); mobileDetail = false"
                            :aria-current="$store.email.activeFolder === folder ? 'page' : null"
                            :class="$store.email.activeFolder === folder ? 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700'"
                            class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-start text-sm font-medium"
                        >
                            <span x-text="folder"></span>
                            <span x-show="$store.email.folders[folder].filter(email => email.unread).length" x-cloak x-text="$store.email.folders[folder].filter(email => email.unread).length" class="rounded-full bg-brand-500 px-2 py-0.5 text-[10px] font-semibold text-white" :aria-label="$store.email.folders[folder].filter(email => email.unread).length + ' unread'"></span>
                        </button>
                    </template>
                </nav>
            </aside>

            <section
                :class="mobileDetail ? 'hidden md:flex' : 'flex'"
                class="col-span-12 min-h-0 flex-col overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 md:col-span-4"
                aria-label="{{ __('Messages') }}"
            >
                <div class="border-b border-gray-200 p-4 dark:border-gray-700">
                    <label for="email-search" class="sr-only">{{ __('Search emails') }}</label>
                    <input id="email-search" x-model="search" type="search" placeholder="{{ __('Search emails...') }}" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                </div>
                <div class="min-h-0 flex-1 overflow-y-auto">
                    <template x-for="email in $store.email.visibleEmails(search)" :key="email.id">
                        <article :class="$store.email.selectedEmail?.id === email.id ? 'bg-brand-50/70 dark:bg-brand-500/10' : 'hover:bg-gray-50 dark:hover:bg-gray-700/60'" class="border-b border-gray-100 p-4 dark:border-gray-700">
                            <div class="flex items-start gap-2">
                                <button type="button" @click="$store.email.selectEmail(email); mobileDetail = true" class="min-w-0 flex-1 text-start" :aria-label="email.from + ': ' + email.subject">
                                    <span class="flex items-center justify-between gap-2">
                                        <span class="truncate text-sm font-medium text-gray-800 dark:text-white" :class="email.unread ? 'font-bold' : ''" x-text="email.from"></span>
                                        <span class="shrink-0 text-[10px] text-gray-400" x-text="email.date"></span>
                                    </span>
                                    <span class="mt-1 block truncate text-xs font-medium text-gray-700 dark:text-gray-300" x-text="email.subject"></span>
                                    <span class="mt-1 block truncate text-xs text-gray-500 dark:text-gray-400" x-text="email.body"></span>
                                    <span x-show="email.attachment" x-cloak class="mt-2 inline-flex items-center gap-1 text-[10px] text-gray-500 dark:text-gray-400" x-text="'📎 ' + email.attachment"></span>
                                </button>
                                <button type="button" @click="$store.email.toggleStar(email)" :aria-pressed="email.starred" :aria-label="email.starred ? '{{ __('Remove star') }}' : '{{ __('Star email') }}'" class="rounded p-1 text-warning-500 hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:hover:bg-gray-800" x-text="email.starred ? '★' : '☆'"></button>
                            </div>
                        </article>
                    </template>
                    <p x-show="$store.email.visibleEmails(search).length === 0" x-cloak class="p-8 text-center text-sm text-gray-500 dark:text-gray-400" role="status">{{ __('No emails match this folder or search.') }}</p>
                </div>
            </section>

            <section
                :class="mobileDetail ? 'flex' : 'hidden md:flex'"
                class="col-span-12 min-h-0 flex-col overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 md:col-span-5 xl:col-span-6"
                aria-label="{{ __('Email message') }}"
            >
                <template x-if="$store.email.selectedEmail">
                    <div class="flex min-h-0 flex-1 flex-col">
                        <header class="flex items-center justify-between gap-3 border-b border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/70">
                            <div class="flex min-w-0 items-center gap-3">
                                <button type="button" @click="mobileDetail = false" class="rounded-lg p-2 text-gray-500 hover:bg-gray-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:hover:bg-gray-700 md:hidden" aria-label="{{ __('Back to messages') }}">‹</button>
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100 font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-300" x-text="$store.email.selectedEmail.from.slice(0, 1)" aria-hidden="true"></span>
                                <div class="min-w-0">
                                    <h2 class="truncate text-sm font-semibold text-gray-800 dark:text-white" x-text="$store.email.selectedEmail.from"></h2>
                                    <p class="truncate text-xs text-gray-500 dark:text-gray-400" x-text="$store.email.selectedEmail.email || '{{ __('No sender address') }}'"></p>
                                </div>
                            </div>
                            <div class="flex shrink-0 items-center gap-1">
                                <button type="button" @click="$store.email.forwardEmail($store.email.selectedEmail)" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:hover:bg-gray-700" aria-label="{{ __('Forward email') }}">{{ __('Forward') }}</button>
                                <button type="button" @click="$store.email.toggleStar($store.email.selectedEmail)" :aria-pressed="$store.email.selectedEmail.starred" :aria-label="$store.email.selectedEmail.starred ? '{{ __('Remove star') }}' : '{{ __('Star email') }}'" class="rounded-lg p-2 text-warning-500 hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:hover:bg-gray-700" x-text="$store.email.selectedEmail.starred ? '★' : '☆'"></button>
                                <button type="button" @click="$store.email.moveSelectedToTrash(); mobileDetail = false" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:hover:bg-gray-700" aria-label="{{ __('Move to trash') }}">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7h16M10 11v6m4-6v6M5 7l1 13h12l1-13M9 7V4h6v3"/></svg>
                                </button>
                            </div>
                        </header>
                        <div class="min-h-0 flex-1 overflow-y-auto p-5 sm:p-6">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white" x-text="$store.email.selectedEmail.subject"></h3>
                            <p class="mt-5 whitespace-pre-line text-sm leading-6 text-gray-600 dark:text-gray-300" x-text="$store.email.selectedEmail.body"></p>
                            <template x-if="$store.email.selectedEmail.attachment">
                                <div class="mt-6 inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-xs text-gray-600 dark:border-gray-700 dark:text-gray-300">
                                    <span aria-hidden="true">📎</span><span x-text="$store.email.selectedEmail.attachment"></span>
                                </div>
                            </template>
                        </div>
                        <form class="border-t border-gray-200 p-4 dark:border-gray-700" @submit.prevent="$store.email.sendReply(reply); reply = ''">
                            <label for="email-reply" class="sr-only">{{ __('Reply to email') }}</label>
                            <textarea id="email-reply" x-model="reply" rows="3" required placeholder="{{ __('Write a reply...') }}" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
                            <div class="mt-2 flex justify-end">
                                <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">{{ __('Save reply to Sent') }}</button>
                            </div>
                        </form>
                    </div>
                </template>
                <div x-show="!$store.email.selectedEmail" class="flex flex-1 items-center justify-center p-8 text-center">
                    <div>
                        <svg class="mx-auto mb-4 h-14 w-14 text-gray-300 dark:text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m3 7 8.1 5.4a1.6 1.6 0 0 0 1.8 0L21 7M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z"/></svg>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Select a message to read it.') }}</p>
                    </div>
                </div>
            </section>

            <div x-show="$store.email.composerOpen" x-cloak @keydown.escape.window="$store.email.closeComposer()" class="fixed inset-0 z-999999 flex items-center justify-center bg-gray-900/50 p-4" role="presentation">
                <section class="w-full max-w-xl rounded-2xl border border-gray-200 bg-white shadow-theme-lg dark:border-gray-700 dark:bg-gray-900" role="dialog" aria-modal="true" aria-labelledby="compose-title" @click.stop>
                    <form class="space-y-4 p-5 sm:p-6" @submit.prevent="$store.email.send()">
                        <div class="flex items-center justify-between gap-4">
                            <h2 id="compose-title" class="text-lg font-semibold text-gray-800 dark:text-white">{{ __('Compose email') }}</h2>
                            <button type="button" @click="$store.email.closeComposer()" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:hover:bg-gray-800" aria-label="{{ __('Close compose window') }}">×</button>
                        </div>
                        <label for="compose-to" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('To') }}<input id="compose-to" x-model="$store.email.composer.to" type="email" required class="mt-1.5 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" /></label>
                        <label for="compose-subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Subject') }}<input id="compose-subject" x-model="$store.email.composer.subject" required class="mt-1.5 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" /></label>
                        <label for="compose-body" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Message') }}<textarea id="compose-body" x-model="$store.email.composer.body" rows="5" required class="mt-1.5 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:outline-none focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea></label>
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <label for="email-attachment" class="cursor-pointer rounded-lg border border-gray-300 px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Attach file') }}</label>
                                <input id="email-attachment" type="file" class="sr-only" @change="$store.email.composer.attachment = $event.target.files[0]?.name || ''" />
                                <span x-show="$store.email.composer.attachment" x-cloak class="max-w-36 truncate text-xs text-gray-500 dark:text-gray-400" x-text="$store.email.composer.attachment"></span>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" @click="$store.email.saveDraft()" class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Save draft') }}</button>
                                <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500">{{ __('Save to Sent') }}</button>
                            </div>
                        </div>
                        <p class="text-xs text-gray-400" role="note">{{ __('Demo only: no email is transmitted.') }}</p>
                    </form>
                </section>
            </div>
        </div>
    </div>
@endsection
