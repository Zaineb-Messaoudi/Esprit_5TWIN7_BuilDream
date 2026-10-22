import { createPopper } from '@popperjs/core';
import './bootstrap';
import Alpine from 'alpinejs';

Alpine.data('technicalInspectionForm', (config) => ({
    ...config,
    photo: null,
    busy: false,
    aiMessage: '',
    analysisResult: null,
    estimateSeverity: 'moderate',
    estimateBusy: false,
    estimateResult: null,
    estimateMessage: '',
    estimateSource: '',
    speechSupported: false,
    listening: false,
    recognition: null,
    init() {
        const Recognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        this.speechSupported = Boolean(Recognition);
        if (!Recognition) return;
        this.recognition = new Recognition();
        this.recognition.lang = 'fr-FR';
        this.recognition.continuous = true;
        this.recognition.onresult = (event) => {
            const text = Array.from(event.results).slice(event.resultIndex).map((result) => result[0].transcript).join(' ');
            const field = document.getElementById('comments');
            if (!field) return;
            field.value = [field.value, text].filter(Boolean).join(' ').trim();
        };
        this.recognition.onend = () => { this.listening = false; };
    },
    toggleDictation() {
        if (this.listening) this.recognition.stop(); else this.recognition.start();
        this.listening = !this.listening;
    },
    async analyzePhoto() {
        if (!this.photo) return;
        this.busy = true;
        this.aiMessage = '';
        const body = new FormData();
        body.append('photo', this.photo);
        try {
            const response = await fetch(this.analyzeUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': this.csrf, Accept: 'application/json' }, body });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message);
            const damageField = document.getElementById('damage_detected');
            const commentsField = document.getElementById('comments');
            if (damageField) damageField.checked = data.damage_detected;
            if (commentsField) commentsField.value = data.comments;
            this.estimateSeverity = data.severity;
            this.analysisResult = data;
            this.aiMessage = this.prefilledMessage;
            if (data.damage_detected) {
                this.estimateSource = `${this.aiLabel}: ${data.severity} · ${Number(data.repair_hours).toFixed(1)} ${this.hoursLabel}`;
                await this.estimateRepairCost();
            }
        } catch (error) {
            this.aiMessage = error.message || this.unavailableMessage;
        } finally {
            this.busy = false;
        }
    },
    async estimateRepairCost() {
        this.estimateBusy = true;
        this.estimateMessage = '';
        try {
            const payload = { severity: this.estimateSeverity };
            const categoryId = this.equipmentCategories[this.equipmentId];
            if (categoryId) payload.category_id = categoryId;
            const response = await fetch(this.estimateUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf, Accept: 'application/json' },
                body: JSON.stringify(payload),
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message);
            this.estimateResult = data;
        } catch (error) {
            this.estimateMessage = error.message || this.estimateUnavailableMessage;
        } finally {
            this.estimateBusy = false;
        }
    },
    formatEstimate(value) {
        return value == null ? '' : `${Number(value).toFixed(2)} ${this.currencyLabel}`;
    },
    formatRange() {
        if (!this.estimateResult) return '';
        return `${this.formatEstimate(this.estimateResult.minimum)} – ${this.formatEstimate(this.estimateResult.maximum)}`;
    },
    severityLabel(severity) {
        return { minor: 'Léger', moderate: 'Modéré', severe: 'Grave' }[severity] || severity;
    },
    formatRepairHours(hours) {
        return `${Number(hours || 0).toFixed(1)} ${this.hoursLabel}`;
    },
}));

let apexChartsPromise;
let chartJsPromise;

function loadChartJs() {
    if (!chartJsPromise) {
        chartJsPromise = import('chart.js/auto').then(({ default: Chart }) => Chart);
    }

    return chartJsPromise;
}

function loadApexCharts() {
    if (!apexChartsPromise) {
        apexChartsPromise = import('apexcharts').then(({ default: ApexCharts }) => {
            window.ApexCharts = ApexCharts;
            return ApexCharts;
        });
    }

    return apexChartsPromise;
}

function observeNearViewport(element, callback) {
    if (!('IntersectionObserver' in window)) {
        callback();
        return null;
    }

    const observer = new IntersectionObserver((entries) => {
        if (entries.some((entry) => entry.isIntersecting)) {
            observer.disconnect();
            callback();
        }
    }, { rootMargin: '200px' });

    observer.observe(element);
    return observer;
}

window.Alpine = Alpine;
window.createPopper = createPopper;

Alpine.data('apexChart', (options, height) => ({
    chart: null,
    observer: null,

    mount(element) {
        this.observer = observeNearViewport(element, () => this.renderChart(element));
    },

    async renderChart(element) {
        const ApexCharts = await loadApexCharts();
        this.chart = new ApexCharts(element, this.withTheme(options, height));
        await this.chart.render();
    },

    withTheme(config, chartHeight) {
        const isDark = document.documentElement.classList.contains('dark');
        return {
            ...config,
            chart: {
                ...config.chart,
                height: chartHeight,
                parentHeightOffset: 0,
                foreColor: isDark ? '#98a2b3' : '#667085',
            },
            theme: {
                ...config.theme,
                mode: isDark ? 'dark' : 'light',
            },
            grid: {
                ...config.grid,
                borderColor: isDark ? '#344054' : '#e4e7ec',
            },
        };
    },

    syncTheme() {
        if (this.chart) {
            this.chart.updateOptions(this.withTheme(options, height), false, true);
        }
    },

    destroy() {
        this.observer?.disconnect();
        if (this.chart) {
            this.chart.destroy();
        }
    },
}));

Alpine.data('maintenanceDashboard', (urls) => ({
    charts: [],
    error: false,
    async load() {
        try {
            const [Chart, costs, failures, intervals] = await Promise.all([
                loadChartJs(),
                fetch(urls.costs, { headers: { Accept: 'application/json' } }).then((response) => response.ok ? response.json() : Promise.reject(response)),
                fetch(urls.failures, { headers: { Accept: 'application/json' } }).then((response) => response.ok ? response.json() : Promise.reject(response)),
                fetch(urls.intervals, { headers: { Accept: 'application/json' } }).then((response) => response.ok ? response.json() : Promise.reject(response)),
            ]);
            const color = document.documentElement.classList.contains('dark') ? 'rgb(206 212 218)' : 'rgb(52 64 84)';
            const build = (element, type, data) => new Chart(element, {
                type,
                data,
                options: { responsive: true, maintainAspectRatio: false, color, scales: type === 'doughnut' ? {} : { y: { beginAtZero: true } } },
            });
            this.charts = [build(this.$refs.costs, 'bar', costs), build(this.$refs.failures, 'doughnut', failures), build(this.$refs.intervals, 'line', intervals)];
        } catch (_) {
            this.error = true;
        }
    },
    destroy() {
        this.charts.forEach((chart) => chart.destroy());
    },
}));

Alpine.store('chat', {
    activeContact: 'Claude',
    contacts: {
        'Claude': { online: true, unread: 0 },
        'Zaineb': { online: false, unread: 1 },
    },
    messages: {
        'Claude': [
            { id: 1, text: 'Hello! How can I assist you with the SolarShare admin today?', sent: false },
            { id: 2, text: 'I need to check the new user management views.', sent: true },
            { id: 3, text: 'They are ready! I\'ve implemented the resource controllers and the Blade views.', sent: false },
        ],
        'Zaineb': [{ id: 4, text: 'Welcome to the team!', sent: false }],
    },
    visibleContacts(query = '') {
        const term = query.trim().toLowerCase();

        return Object.keys(this.messages).filter((contact) => {
            return contact.toLowerCase().includes(term)
                || this.messages[contact].some((message) => message.text.toLowerCase().includes(term));
        });
    },
    sendMessage(contact, text, attachment = '') {
        if (!text.trim() && !attachment) return;

        this.messages[contact].push({
            id: Date.now(),
            text: text.trim(),
            attachment,
            sent: true,
        });
    },
    setActiveContact(contact) {
        this.activeContact = contact;
        this.contacts[contact].unread = 0;
    }
});

Alpine.store('email', {
    activeFolder: 'Inbox',
    selectedEmail: null,
    composerOpen: false,
    folders: {
        'Inbox': [
            { id: 1, from: 'Support Team', email: 'support@solshare.example', subject: 'Ticket #1001: Payment Issue', body: 'Dear User, we are sorry to hear about the payment issue. Our team is reviewing the transaction and will share an update shortly.', date: 'Oct 1', unread: true, starred: true },
            { id: 2, from: 'Marketing', email: 'marketing@solshare.example', subject: 'New Campaign Results', body: 'The campaign report is ready. The Solar for Every Home campaign increased qualified visits and product inquiries this week.', date: 'Oct 2', unread: false, starred: false },
        ],
        'Sent': [],
        'Drafts': [],
        'Trash': [],
        'Starred': [],
    },
    composer: { to: '', subject: '', body: '', attachment: '' },
    setFolder(folder) {
        this.activeFolder = folder;
        this.selectedEmail = null;
    },
    selectEmail(email) {
        this.selectedEmail = email;
        email.unread = false;
    },
    visibleEmails(query = '') {
        const emails = this.folders[this.activeFolder] || [];
        const term = query.trim().toLowerCase();

        return emails.filter((email) => {
            return `${email.from} ${email.email || ''} ${email.subject} ${email.body}`.toLowerCase().includes(term);
        });
    },
    openComposer() {
        this.composer = { to: '', subject: '', body: '', attachment: '' };
        this.composerOpen = true;
    },
    forwardEmail(email) {
        this.composer = {
            to: '',
            subject: `Fwd: ${email.subject}`,
            body: `\n\n---------- Forwarded message ----------\nFrom: ${email.from} <${email.email || 'unknown sender'}>\nSubject: ${email.subject}\n\n${email.body}`,
            attachment: email.attachment || '',
        };
        this.composerOpen = true;
    },
    closeComposer() {
        this.composerOpen = false;
    },
    saveDraft() {
        const draft = { ...this.composer, id: Date.now(), from: 'You', date: 'Just now', unread: false };
        this.folders.Drafts.unshift(draft);
        this.composerOpen = false;
    },
    send() {
        const message = { ...this.composer, id: Date.now(), from: 'You', email: 'you@solshare.example', date: 'Just now', unread: false };
        this.folders.Sent.unshift(message);
        this.composerOpen = false;
    },
    sendReply(body) {
        if (!body.trim() || !this.selectedEmail) return;

        this.folders.Sent.unshift({
            id: Date.now(),
            from: 'You',
            email: 'you@solshare.example',
            subject: `Re: ${this.selectedEmail.subject}`,
            body: body.trim(),
            date: 'Just now',
            unread: false,
        });
    },
    toggleStar(email) {
        email.starred = !email.starred;
        this.folders.Starred = Object.values(this.folders)
            .flat()
            .filter((item, index, all) => item.starred && all.findIndex((candidate) => candidate.id === item.id) === index);
    },
    moveSelectedToTrash() {
        if (!this.selectedEmail) return;

        const selectedId = this.selectedEmail.id;
        for (const [folder, emails] of Object.entries(this.folders)) {
            if (folder !== 'Trash') {
                this.folders[folder] = emails.filter((email) => email.id !== selectedId);
            }
        }
        this.folders.Trash.unshift(this.selectedEmail);
        this.selectedEmail = null;
    }
});

async function startAlpine() {
    if (document.querySelector('.custom-datepicker, [data-class="flatpickr-right"], [data-date-picker]')) {
        const [{ default: flatpickr }] = await Promise.all([
            import('flatpickr'),
            import('flatpickr/dist/flatpickr.min.css'),
        ]);

        window.flatpickr = flatpickr;
        document.querySelectorAll('input[type="date"], [data-date-picker]').forEach((input) => {
            flatpickr(input, {
                dateFormat: 'Y-m-d',
                minDate: input.dataset.minDate || input.min || null,
                disableMobile: true,
                allowInput: true,
                onChange: (selectedDates, datestr) => {
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                    const paired = input.dataset.pairedDate ? document.querySelector(input.dataset.pairedDate) : null;
                    if (paired && datestr) paired._flatpickr?.set('minDate', datestr);
                },
            });

            if (!input.closest('[data-calendar-control]')) {
                const wrapper = document.createElement('div');
                wrapper.dataset.calendarControl = 'true';
                wrapper.className = 'relative w-full';
                input.parentNode.insertBefore(wrapper, input);
                wrapper.appendChild(input);
                input.classList.add('pe-11', 'cursor-pointer');

                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'date-picker-trigger';
                button.setAttribute('aria-label', 'Open calendar');
                button.innerHTML = '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 3v3m10-3v3M4.75 9.25h14.5M6 5h12a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"/></svg>';
                button.addEventListener('click', () => input._flatpickr?.open());
                wrapper.appendChild(button);
            }
        });
    }

    Alpine.start();

    // Initialize Laravel Echo for real-time notifications
    if (window.Echo) {
        const userId = document.querySelector('meta[name="user-id"]')?.getAttribute('content');
        if (userId) {
            // Owner channel
            window.Echo.private(`owner.${userId}`)
                .listen('.reservation.created', (e) => showToast(e.message))
                .listen('.payment.received', (e) => showToast(e.message))
                .listen('.rental.started', (e) => showToast(e.message))
                .listen('.equipment.returned', (e) => showToast(e.message))
                .listen('.extension.requested', (e) => showToast(e.message))
                .listen('.maintenance.created', (e) => showToast(e.message))
                .listen('.maintenance.completed', (e) => showToast(e.message));

            // User channel (buyer)
            window.Echo.private(`user.${userId}`)
                .listen('.reservation.approved', (e) => showToast(e.message))
                .listen('.reservation.rejected', (e) => showToast(e.message))
                .listen('.rental.started', (e) => showToast(e.message))
                .listen('.equipment.returned', (e) => showToast(e.message))
                .listen('.extension.approved', (e) => showToast(e.message))
                .listen('.extension.rejected', (e) => showToast(e.message));
        }
    }

    function showToast(message) {
        if (window.Alpine && Alpine.store('notifications')) {
            Alpine.store('notifications').add(message);
        } else {
            // Fallback: create a simple toast
            const toast = document.createElement('div');
            toast.className = 'fixed bottom-4 right-4 bg-gray-900 text-white px-4 py-2 rounded-lg shadow-lg z-50';
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 5000);
        }
    }
}

startAlpine();

function initializeChartWhenVisible(selector, loadChart, initialize) {
    const element = document.querySelector(selector);

    if (!element) {
        return;
    }

    observeNearViewport(element, () => {
        loadApexCharts().then(loadChart).then((module) => module[initialize]());
    });
}

// Initialize components on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    // Map imports
    if (document.querySelector('#mapOne, [data-vector-map]')) {
        const element = document.querySelector('#mapOne, [data-vector-map]');
        observeNearViewport(element, () => import('./components/map').then(module => module.initMap()));
    }

    // Chart imports
    initializeChartWhenVisible('#chartOne', () => import('./components/chart/chart-1'), 'initChartOne');
    initializeChartWhenVisible('#chartTwo', () => import('./components/chart/chart-2'), 'initChartTwo');
    initializeChartWhenVisible('#chartThree', () => import('./components/chart/chart-3'), 'initChartThree');
    initializeChartWhenVisible('#chartSix', () => import('./components/chart/chart-6'), 'initChartSix');
    initializeChartWhenVisible('#chartEight', () => import('./components/chart/chart-8'), 'initChartEight');
    initializeChartWhenVisible('#chartThirteen', () => import('./components/chart/chart-13'), 'initChartThirteen');

    // Calendar init
    if (document.querySelector('#calendar')) {
        const element = document.querySelector('#calendar');
        observeNearViewport(element, () => import('./components/calendar-init').then(module => module.calendarInit()));
    }
});
