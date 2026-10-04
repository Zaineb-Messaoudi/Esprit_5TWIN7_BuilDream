@extends('layouts.app')

@section('content')
    <div
        class="space-y-6"
        x-data="{
            activeFolder: 'All files',
            query: '',
            view: 'grid',
            sort: 'name',
            typeFilter: 'All types',
            storageLimitBytes: 50 * 1024 * 1024,
            files: [
                { id: 1, name: 'September sales report.pdf', type: 'PDF document', category: 'Documents', size: '2.4 MB', bytes: 2516582, modified: 'Today, 9:42 AM', modifiedRank: 0, folder: 'Reports', icon: 'PDF', color: 'text-error-500' },
                { id: 2, name: 'Solar panel product photos.zip', type: 'Archive', category: 'Archives', size: '18.6 MB', bytes: 19503514, modified: 'Yesterday', modifiedRank: 1, folder: 'Product images', icon: 'ZIP', color: 'text-warning-500' },
                { id: 3, name: 'Installation checklist.docx', type: 'Word document', category: 'Documents', size: '842 KB', bytes: 862208, modified: 'Sep 28, 2026', modifiedRank: 4, folder: 'Documents', icon: 'DOC', color: 'text-blue-light-500' },
                { id: 4, name: 'Q3 customer invoices.xlsx', type: 'Spreadsheet', category: 'Spreadsheets', size: '1.2 MB', bytes: 1258291, modified: 'Sep 26, 2026', modifiedRank: 6, folder: 'Invoices', icon: 'XLS', color: 'text-success-500' },
                { id: 5, name: 'Brand guidelines.pdf', type: 'PDF document', category: 'Documents', size: '5.8 MB', bytes: 6081741, modified: 'Sep 22, 2026', modifiedRank: 10, folder: 'Documents', icon: 'PDF', color: 'text-error-500' },
                { id: 6, name: 'Warehouse inventory.csv', type: 'CSV spreadsheet', category: 'Spreadsheets', size: '428 KB', bytes: 438272, modified: 'Sep 19, 2026', modifiedRank: 13, folder: 'Reports', icon: 'CSV', color: 'text-success-500' },
                { id: 7, name: 'Home battery hero.jpg', type: 'JPEG image', category: 'Images', size: '3.1 MB', bytes: 3250586, modified: 'Sep 16, 2026', modifiedRank: 16, folder: 'Product images', icon: 'IMG', color: 'text-brand-500' },
                { id: 8, name: 'Partner contract.pdf', type: 'PDF document', category: 'Documents', size: '960 KB', bytes: 983040, modified: 'Sep 12, 2026', modifiedRank: 20, folder: 'Invoices', icon: 'PDF', color: 'text-error-500' }
            ],
            folders: [
                { name: 'Documents', count: 2, icon: 'DOC' },
                { name: 'Product images', count: 2, icon: 'IMG' },
                { name: 'Reports', count: 2, icon: 'RPT' },
                { name: 'Invoices', count: 2, icon: 'INV' }
            ],
            visibleFiles() {
                return this.files
                    .filter(file => this.activeFolder === 'All files' || file.folder === this.activeFolder)
                    .filter(file => file.name.toLowerCase().includes(this.query.toLowerCase()))
                    .filter(file => this.typeFilter === 'All types' || file.category === this.typeFilter)
                    .sort((a, b) => {
                        if (this.sort === 'size') return b.bytes - a.bytes;
                        if (this.sort === 'oldest') return b.modifiedRank - a.modifiedRank;
                        if (this.sort === 'newest') return a.modifiedRank - b.modifiedRank;
                        return a.name.localeCompare(b.name);
                    });
            },
            storageUsed() {
                return this.files.reduce((total, file) => total + file.bytes, 0);
            },
            storagePercent() {
                return Math.min(100, this.storageUsed() / this.storageLimitBytes * 100);
            },
            formatBytes(bytes) {
                if (bytes >= 1024 * 1024 * 1024) return `${(bytes / 1024 / 1024 / 1024).toFixed(1)} GB`;
                if (bytes >= 1024 * 1024) return `${(bytes / 1024 / 1024).toFixed(1)} MB`;
                return `${Math.max(1, Math.round(bytes / 1024))} KB`;
            },
            addFiles(event) {
                for (const file of event.target.files) {
                    const extension = file.name.includes('.') ? file.name.split('.').pop().toUpperCase() : 'FILE';
                    const category = file.type.startsWith('image/') ? 'Images'
                        : file.type.startsWith('text/') || ['PDF', 'DOC', 'DOCX'].includes(extension) ? 'Documents'
                            : ['CSV', 'XLS', 'XLSX'].includes(extension) ? 'Spreadsheets'
                                : ['ZIP', 'RAR', '7Z'].includes(extension) ? 'Archives' : 'Other';
                    this.files.unshift({
                        id: Date.now() + Math.random(),
                        name: file.name,
                        type: file.type || 'Uploaded file',
                        size: file.size < 1024 * 1024 ? `${Math.max(1, Math.round(file.size / 1024))} KB` : `${(file.size / 1024 / 1024).toFixed(1)} MB`,
                        bytes: file.size,
                        modified: 'Just now',
                        modifiedRank: -1,
                        folder: 'Documents',
                        category,
                        objectUrl: URL.createObjectURL(file),
                        icon: extension.slice(0, 3),
                        color: 'text-brand-500'
                    });
                }
                event.target.value = '';
            },
            renameFile(file) {
                const name = window.prompt('Rename file', file.name);
                if (name && name.trim()) file.name = name.trim();
            },
            deleteFile(file) {
                if (window.confirm(`Remove ${file.name} from this demo list?`)) {
                    if (file.objectUrl) URL.revokeObjectURL(file.objectUrl);
                    this.files = this.files.filter(item => item.id !== file.id);
                }
            },
            downloadFile(file) {
                if (file.objectUrl) {
                    const link = document.createElement('a');
                    link.href = file.objectUrl;
                    link.download = file.name;
                    link.click();
                    return;
                }
                const blob = new Blob([`SolarShare file manager demo\\nFile: ${file.name}\\nThis is sample metadata only.`], { type: 'text/plain' });
                const link = document.createElement('a');
                const downloadUrl = URL.createObjectURL(blob);
                link.href = downloadUrl;
                link.download = `${file.name}.demo.txt`;
                link.click();
                window.setTimeout(() => URL.revokeObjectURL(downloadUrl), 1000);
            }
        }"
    >
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Applications / File Manager</p>
                <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">File Manager</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Sample workspace files · uploads and changes are temporary browser demos</p>
            </div>
            <label class="inline-flex cursor-pointer items-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 focus-within:outline-hidden focus-within:ring-2 focus-within:ring-brand-500 focus-within:ring-offset-2">
                Upload files
                <input type="file" multiple class="sr-only" aria-label="Upload files to the demo list" @change="addFiles($event)" />
            </label>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <x-common.component-card :title="__('Storage used')"><p class="text-2xl font-semibold text-gray-800 dark:text-white" x-text="formatBytes(storageUsed())"></p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-text="'{{ __('of') }} ' + formatBytes(storageLimitBytes) + ' {{ __('available in this demo') }}'"></p><div class="mt-4 h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800" role="progressbar" aria-label="{{ __('Demo storage used') }}" aria-valuemin="0" aria-valuemax="100" :aria-valuenow="Math.round(storagePercent())"><div class="h-full rounded-full bg-brand-500 transition-all" :style="{ width: storagePercent() + '%' }"></div></div></x-common.component-card>
            <x-common.component-card title="Files"><p class="text-2xl font-semibold text-gray-800 dark:text-white" x-text="files.length"></p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">In this sample workspace</p></x-common.component-card>
            <x-common.component-card title="Shared with you"><p class="text-2xl font-semibold text-gray-800 dark:text-white">6</p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Demo shared files</p></x-common.component-card>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-4">
            <x-common.component-card title="Folders" desc="Browse workspace folders">
                <div class="space-y-1">
                    <button type="button" @click="activeFolder = 'All files'" class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-start text-sm" :class="activeFolder === 'All files' ? 'bg-brand-50 font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-300' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800'">
                        <span>All files</span><span class="text-xs" x-text="files.length"></span>
                    </button>
                    <template x-for="folder in folders" :key="folder.name">
                        <button type="button" @click="activeFolder = folder.name" class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-start text-sm" :class="activeFolder === folder.name ? 'bg-brand-50 font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-300' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800'">
                            <span class="flex items-center gap-2"><span class="text-[10px] font-semibold text-gray-400" x-text="folder.icon"></span><span x-text="folder.name"></span></span>
                            <span class="text-xs" x-text="files.filter(file => file.folder === folder.name).length"></span>
                        </button>
                    </template>
                </div>
            </x-common.component-card>

            <x-common.component-card title="Workspace files" desc="Search, sort, and manage sample files" class="xl:col-span-3">
                <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <label class="sr-only" for="file-search">Search files</label>
                    <input id="file-search" x-model="query" type="search" placeholder="Search files..." class="min-w-0 flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    <label class="sr-only" for="file-sort">Sort files</label>
                    <select id="file-sort" x-model="sort" class="rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"><option value="name">{{ __('Name') }}</option><option value="newest">{{ __('Newest first') }}</option><option value="oldest">{{ __('Oldest first') }}</option><option value="size">{{ __('Largest first') }}</option></select>
                    <label class="sr-only" for="file-type">{{ __('Filter by file type') }}</label>
                    <select id="file-type" x-model="typeFilter" class="rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"><option>{{ __('All types') }}</option><option>{{ __('Documents') }}</option><option>{{ __('Images') }}</option><option>{{ __('Spreadsheets') }}</option><option>{{ __('Archives') }}</option><option>{{ __('Other') }}</option></select>
                    <div class="flex rounded-lg border border-gray-200 p-1 dark:border-gray-700" role="group" aria-label="File display mode">
                        <button type="button" @click="view = 'grid'" :aria-pressed="view === 'grid'" class="rounded-md px-3 py-1.5 text-xs" :class="view === 'grid' ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300' : 'text-gray-500'">Grid</button>
                        <button type="button" @click="view = 'list'" :aria-pressed="view === 'list'" class="rounded-md px-3 py-1.5 text-xs" :class="view === 'list' ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300' : 'text-gray-500'">List</button>
                    </div>
                </div>

                <div x-show="view === 'grid'" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <template x-for="file in visibleFiles()" :key="file.id">
                        <article class="min-w-0 rounded-xl border border-gray-200 p-4 dark:border-gray-800">
                            <div class="flex items-start justify-between gap-3">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-gray-50 text-xs font-bold dark:bg-gray-800" :class="file.color" x-text="file.icon"></span>
                                <div class="flex gap-1">
                                    <button type="button" @click="downloadFile(file)" class="rounded p-1.5 text-xs text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800" :aria-label="'Download demo for ' + file.name">Download</button>
                                    <button type="button" @click="renameFile(file)" class="rounded p-1.5 text-xs text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800" :aria-label="'Rename ' + file.name">Rename</button>
                                    <button type="button" @click="deleteFile(file)" class="rounded p-1.5 text-xs text-error-500 hover:bg-error-50 dark:hover:bg-error-500/10" :aria-label="'Delete ' + file.name">Delete</button>
                                </div>
                            </div>
                            <p class="mt-4 truncate text-sm font-medium text-gray-800 dark:text-white/90" :title="file.name" x-text="file.name"></p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400"><span x-text="file.size"></span><span class="mx-1" aria-hidden="true">·</span><span x-text="file.modified"></span></p>
                        </article>
                    </template>
                </div>

                <div x-show="view === 'list'" class="overflow-x-auto">
                    <table class="w-full min-w-[620px] text-start text-sm">
                        <thead><tr class="border-b border-gray-100 text-xs text-gray-400 dark:border-gray-800"><th class="px-3 py-3 font-medium">Name</th><th class="px-3 py-3 font-medium">Folder</th><th class="px-3 py-3 font-medium">Size</th><th class="px-3 py-3 font-medium">Modified</th><th class="px-3 py-3 font-medium">Actions</th></tr></thead>
                        <tbody>
                            <template x-for="file in visibleFiles()" :key="file.id">
                                <tr class="border-b border-gray-50 last:border-0 dark:border-gray-800">
                                    <td class="max-w-56 truncate px-3 py-4 font-medium text-gray-800 dark:text-white/90" x-text="file.name"></td><td class="px-3 py-4 text-gray-500" x-text="file.folder"></td><td class="px-3 py-4 text-gray-500" x-text="file.size"></td><td class="px-3 py-4 text-gray-500" x-text="file.modified"></td>
                                    <td class="px-3 py-4"><div class="flex gap-2"><button type="button" @click="downloadFile(file)" class="text-xs text-brand-600 hover:underline">Download</button><button type="button" @click="renameFile(file)" class="text-xs text-gray-500 hover:underline">Rename</button><button type="button" @click="deleteFile(file)" class="text-xs text-error-500 hover:underline">Delete</button></div></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div x-show="visibleFiles().length === 0" x-cloak class="py-10 text-center">
                    <p class="font-medium text-gray-700 dark:text-gray-300">No files found</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try another search or select a different folder.</p>
                </div>
            </x-common.component-card>
        </div>
    </div>
@endsection
