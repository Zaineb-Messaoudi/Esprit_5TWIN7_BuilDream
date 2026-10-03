@extends('layouts.app')

@section('content')
    <div
        class="space-y-6"
        x-data="{
            showForm: false,
            view: 'list',
            selectedTask: null,
            editingTaskId: null,
            editDraft: { title: '', project: '', priority: 'Medium', due: '', assignee: '' },
            query: '',
            statusFilter: 'All',
            priorityFilter: 'All',
            newTask: { title: '', project: 'SolarShare website', priority: 'Medium', due: '', assignee: 'You' },
            tasks: [
                { id: 1, title: 'Review residential solar product catalog', project: 'SolarShare website', due: 'Today', priority: 'High', status: 'In progress', assignee: 'AB' },
                { id: 2, title: 'Prepare September campaign performance report', project: 'Marketing', due: 'Today', priority: 'Medium', status: 'To do', assignee: 'YT' },
                { id: 3, title: 'Confirm Sousse warehouse delivery window', project: 'Operations', due: 'Tomorrow', priority: 'High', status: 'In progress', assignee: 'MH' },
                { id: 4, title: 'Update installation partner onboarding guide', project: 'Customer success', due: 'Oct 8', priority: 'Low', status: 'To do', assignee: 'KM' },
                { id: 5, title: 'Audit inverter product specifications', project: 'SolarShare website', due: 'Oct 9', priority: 'Medium', status: 'Done', assignee: 'AB' },
                { id: 6, title: 'Schedule customer feedback interviews', project: 'Research', due: 'Oct 10', priority: 'Low', status: 'To do', assignee: 'YT' }
            ],
            filteredTasks() {
                return this.tasks.filter(task => {
                    const matchesQuery = `${task.title} ${task.project} ${task.assignee}`.toLowerCase().includes(this.query.toLowerCase());
                    const matchesStatus = this.statusFilter === 'All' || task.status === this.statusFilter;
                    const matchesPriority = this.priorityFilter === 'All' || task.priority === this.priorityFilter;
                    return matchesQuery && matchesStatus && matchesPriority;
                });
            },
            addTask() {
                const title = this.newTask.title.trim();
                if (!title) return;
                this.tasks.unshift({
                    id: Date.now(),
                    title,
                    project: this.newTask.project,
                    priority: this.newTask.priority,
                    due: this.newTask.due || 'No due date',
                    status: 'To do',
                    assignee: this.newTask.assignee
                });
                this.newTask = { title: '', project: 'SolarShare website', priority: 'Medium', due: '', assignee: 'You' };
                this.showForm = false;
            },
            advanceTask(task) {
                const order = ['To do', 'In progress', 'Done'];
                task.status = order[(order.indexOf(task.status) + 1) % order.length];
            },
            openDetails(task) {
                this.selectedTask = task;
            },
            startEdit(task) {
                this.editingTaskId = task.id;
                this.editDraft = { title: task.title, project: task.project, priority: task.priority, due: task.due === 'No due date' ? '' : task.due, assignee: task.assignee };
                this.selectedTask = null;
            },
            saveEdit() {
                const task = this.tasks.find(item => item.id === this.editingTaskId);
                if (!task || !this.editDraft.title.trim()) return;
                Object.assign(task, this.editDraft, { title: this.editDraft.title.trim(), due: this.editDraft.due || 'No due date' });
                this.editingTaskId = null;
            },
            removeTask(task) {
                this.tasks = this.tasks.filter(item => item.id !== task.id);
                if (this.selectedTask?.id === task.id) this.selectedTask = null;
                if (this.editingTaskId === task.id) this.editingTaskId = null;
            },
            priorityColor(priority) {
                return { High: 'error', Medium: 'warning', Low: 'gray' }[priority] || 'gray';
            }
        }"
    >
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Applications / Tasks') }}</p>
                <h1 class="mt-1 text-title-md font-semibold text-gray-800 dark:text-white/90">{{ __('Tasks') }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Plan work and follow progress · changes stay in this browser session') }}</p>
            </div>
            <button type="button" @click="showForm = !showForm" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 focus:outline-hidden focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                <span x-show="!showForm">{{ __('Create task') }}</span>
                <span x-show="showForm" x-cloak>{{ __('Close form') }}</span>
            </button>
        </div>

        <div x-show="showForm" x-cloak class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <form @submit.prevent="addTask()" class="grid grid-cols-1 items-end gap-4 md:grid-cols-2 xl:grid-cols-5">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300 xl:col-span-2">{{ __('Task name') }}
                    <input x-model="newTask.title" required maxlength="120" placeholder="{{ __('What needs to be done?') }}" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                </label>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Project') }}
                    <select x-model="newTask.project" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option>SolarShare website</option><option>Marketing</option><option>Operations</option><option>Customer success</option><option>Research</option>
                    </select>
                </label>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Priority') }}
                    <select x-model="newTask.priority" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option>Low</option><option>Medium</option><option>High</option>
                    </select>
                </label>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Due date') }}
                    <input x-model="newTask.due" type="date" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                </label>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Assignee') }}
                    <select x-model="newTask.assignee" class="mt-2 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"><option>You</option><option>AB</option><option>YT</option><option>MH</option><option>KM</option></select>
                </label>
                <div class="flex gap-2">
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">{{ __('Add task') }}</button>
                    <button type="button" @click="showForm = false" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Cancel') }}</button>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <x-common.component-card :title="__('Total tasks')"><p class="text-2xl font-semibold text-gray-800 dark:text-white" x-text="tasks.length"></p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Across all projects') }}</p></x-common.component-card>
            <x-common.component-card :title="__('In progress')"><p class="text-2xl font-semibold text-gray-800 dark:text-white" x-text="tasks.filter(task => task.status === 'In progress').length"></p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Currently being worked on') }}</p></x-common.component-card>
            <x-common.component-card :title="__('Completed')"><p class="text-2xl font-semibold text-gray-800 dark:text-white" x-text="tasks.filter(task => task.status === 'Done').length"></p><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Marked as done') }}</p></x-common.component-card>
        </div>

        <x-common.component-card :title="__('Task list')" :desc="__('Filter tasks, review priorities, and advance statuses')">
            <div class="mb-5 flex flex-col gap-3 xl:flex-row xl:items-end">
                <div class="flex-1">
                    <label class="sr-only" for="task-search">{{ __('Search tasks') }}</label>
                    <input id="task-search" x-model="query" type="search" placeholder="{{ __('Search tasks or projects...') }}" class="min-w-0 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                </div>
                <label class="sr-only" for="task-status">{{ __('Filter by status') }}</label>
                <select id="task-status" x-model="statusFilter" class="rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"><option>All</option><option>To do</option><option>In progress</option><option>Done</option></select>
                <label class="sr-only" for="task-priority">{{ __('Filter by priority') }}</label>
                <select id="task-priority" x-model="priorityFilter" class="rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"><option>All</option><option>High</option><option>Medium</option><option>Low</option></select>
                <div class="inline-flex w-fit rounded-lg border border-gray-200 p-1 dark:border-gray-700" role="group" aria-label="{{ __('Task view') }}">
                    <button type="button" @click="view = 'list'" :aria-pressed="view === 'list'" :class="view === 'list' ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300' : 'text-gray-500'" class="rounded-md px-3 py-1.5 text-xs font-medium">{{ __('List') }}</button>
                    <button type="button" @click="view = 'board'" :aria-pressed="view === 'board'" :class="view === 'board' ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300' : 'text-gray-500'" class="rounded-md px-3 py-1.5 text-xs font-medium">{{ __('Kanban board') }}</button>
                </div>
            </div>

            <div x-show="view === 'list'" class="space-y-3">
                <template x-for="task in filteredTasks()" :key="task.id">
                    <article class="flex flex-col gap-4 rounded-xl border border-gray-200 p-4 dark:border-gray-800 sm:flex-row sm:items-center">
                        <div class="min-w-0 flex-1">
                            <button type="button" @click="openDetails(task)" class="text-start font-medium text-gray-800 hover:text-brand-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-white/90 dark:hover:text-brand-300" x-text="task.title"></button>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400"><span x-text="task.project"></span><span class="mx-2" aria-hidden="true">·</span>Due <span x-text="task.due"></span></p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-300" x-text="task.assignee"></span>
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium" :class="{
                                'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400': task.priority === 'High',
                                'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400': task.priority === 'Medium',
                                'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300': task.priority === 'Low'
                            }" x-text="task.priority"></span>
                            <button type="button" @click="advanceTask(task)" class="min-w-28 rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 focus:outline-hidden focus:ring-2 focus:ring-brand-500 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800" :aria-label="'Advance status for ' + task.title" x-text="task.status"></button>
                            <button type="button" @click="startEdit(task)" class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">{{ __('Edit') }}</button>
                            <button type="button" @click="removeTask(task)" class="rounded-lg px-3 py-1.5 text-xs font-medium text-error-600 hover:bg-error-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-error-500 dark:text-error-400 dark:hover:bg-error-500/10" :aria-label="'{{ __('Delete task') }} ' + task.title">{{ __('Delete') }}</button>
                        </div>
                    </article>
                </template>
            </div>

            <div x-show="view === 'board'" x-cloak class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <template x-for="status in ['To do', 'In progress', 'Done']" :key="status">
                    <section class="min-w-0 rounded-xl bg-gray-50 p-3 dark:bg-gray-900/50" :aria-label="status">
                        <div class="mb-3 flex items-center justify-between gap-2 px-1">
                            <h2 class="text-sm font-semibold text-gray-800 dark:text-white" x-text="status"></h2>
                            <span class="rounded-full bg-white px-2 py-0.5 text-xs text-gray-500 dark:bg-gray-800 dark:text-gray-300" x-text="filteredTasks().filter(task => task.status === status).length"></span>
                        </div>
                        <div class="space-y-3">
                            <template x-for="task in filteredTasks().filter(item => item.status === status)" :key="'board-' + task.id">
                                <article class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-700 dark:bg-gray-800">
                                    <button type="button" @click="openDetails(task)" class="text-start text-sm font-medium text-gray-800 hover:text-brand-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:text-white dark:hover:text-brand-300" x-text="task.title"></button>
                                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400" x-text="task.project + ' · ' + task.due"></p>
                                    <div class="mt-4 flex items-center justify-between gap-2">
                                        <span class="rounded-full px-2.5 py-1 text-xs" :class="{
                                            'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400': task.priority === 'High',
                                            'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400': task.priority === 'Medium',
                                            'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300': task.priority === 'Low'
                                        }" x-text="task.priority"></span>
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="advanceTask(task)" class="rounded-lg border border-gray-300 px-2.5 py-1.5 text-xs text-gray-600 hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700" :aria-label="'Advance status for ' + task.title">{{ __('Move to next status') }}</button>
                                            <button type="button" @click="removeTask(task)" class="rounded-lg px-2 py-1.5 text-xs text-error-600 hover:bg-error-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-error-500 dark:text-error-400 dark:hover:bg-error-500/10" :aria-label="'{{ __('Delete task') }} ' + task.title">{{ __('Delete') }}</button>
                                        </div>
                                    </div>
                                </article>
                            </template>
                            <p x-show="filteredTasks().filter(task => task.status === status).length === 0" class="rounded-lg border border-dashed border-gray-300 px-3 py-6 text-center text-xs text-gray-500 dark:border-gray-700 dark:text-gray-400">{{ __('No tasks in this status.') }}</p>
                        </div>
                    </section>
                </template>
            </div>

            <div x-show="filteredTasks().length === 0" x-cloak class="py-8 text-center">
                <p class="font-medium text-gray-700 dark:text-gray-300">{{ __('No matching tasks') }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Clear the filters or add a task to this list.') }}</p>
            </div>
        </x-common.component-card>

        <div x-show="selectedTask" x-cloak @keydown.escape.window="selectedTask = null" @click.self="selectedTask = null" class="fixed inset-0 z-999999 flex items-center justify-center bg-gray-900/50 p-4" role="presentation">
            <section class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-lg dark:border-gray-700 dark:bg-gray-900 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="task-detail-title">
                <template x-if="selectedTask">
                    <div>
                        <div class="flex items-start justify-between gap-4">
                            <div><p class="text-xs text-gray-500 dark:text-gray-400" x-text="selectedTask.project"></p><h2 id="task-detail-title" class="mt-1 text-lg font-semibold text-gray-800 dark:text-white" x-text="selectedTask.title"></h2></div>
                            <button type="button" @click="selectedTask = null" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 dark:hover:bg-gray-800" aria-label="{{ __('Close task details') }}">×</button>
                        </div>
                        <dl class="mt-5 grid grid-cols-2 gap-4 border-t border-gray-100 pt-5 text-sm dark:border-gray-800">
                            <div><dt class="text-xs text-gray-400">{{ __('Status') }}</dt><dd class="mt-1 font-medium text-gray-800 dark:text-white" x-text="selectedTask.status"></dd></div>
                            <div><dt class="text-xs text-gray-400">{{ __('Priority') }}</dt><dd class="mt-1 font-medium text-gray-800 dark:text-white" x-text="selectedTask.priority"></dd></div>
                            <div><dt class="text-xs text-gray-400">{{ __('Assignee') }}</dt><dd class="mt-1 font-medium text-gray-800 dark:text-white" x-text="selectedTask.assignee"></dd></div>
                            <div><dt class="text-xs text-gray-400">{{ __('Due date') }}</dt><dd class="mt-1 font-medium text-gray-800 dark:text-white" x-text="selectedTask.due"></dd></div>
                        </dl>
                        <div class="mt-6 flex justify-end gap-2">
                            <button type="button" @click="startEdit(selectedTask)" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">{{ __('Edit task') }}</button>
                            <button type="button" @click="removeTask(selectedTask)" class="rounded-lg border border-error-200 px-4 py-2 text-sm font-medium text-error-600 hover:bg-error-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-error-500 dark:border-error-500/30 dark:text-error-400 dark:hover:bg-error-500/10">{{ __('Delete') }}</button>
                            <button type="button" @click="selectedTask = null" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">{{ __('Close') }}</button>
                        </div>
                    </div>
                </template>
            </section>
        </div>

        <div x-show="editingTaskId !== null" x-cloak @keydown.escape.window="editingTaskId = null" @click.self="editingTaskId = null" class="fixed inset-0 z-999999 flex items-center justify-center bg-gray-900/50 p-4" role="presentation">
            <section class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-lg dark:border-gray-700 dark:bg-gray-900 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="edit-task-title">
                <form class="space-y-4" @submit.prevent="saveEdit()">
                    <h2 id="edit-task-title" class="text-lg font-semibold text-gray-800 dark:text-white">{{ __('Edit task') }}</h2>
                    <label for="edit-task-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Task name') }}<input id="edit-task-name" x-model="editDraft.title" required maxlength="120" class="mt-1.5 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white" /></label>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Project') }}<select x-model="editDraft.project" class="mt-1.5 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"><option>SolarShare website</option><option>Marketing</option><option>Operations</option><option>Customer success</option><option>Research</option></select></label>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Priority') }}<select x-model="editDraft.priority" class="mt-1.5 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"><option>{{ __('Low') }}</option><option>{{ __('Medium') }}</option><option>{{ __('High') }}</option></select></label>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Due date') }}<input x-model="editDraft.due" type="text" placeholder="e.g. Oct 8" class="mt-1.5 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" /></label>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Assignee') }}<select x-model="editDraft.assignee" class="mt-1.5 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"><option>You</option><option>AB</option><option>YT</option><option>MH</option><option>KM</option></select></label>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="editingTaskId = null" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">{{ __('Cancel') }}</button>
                        <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">{{ __('Save changes') }}</button>
                    </div>
                    <p class="text-xs text-gray-400">{{ __('Changes apply only in this page session.') }}</p>
                </form>
            </section>
        </div>
    </div>
@endsection
