import { createPopper } from '@popperjs/core';
import './bootstrap';
import Alpine from 'alpinejs';

let apexChartsPromise;

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
    if (document.querySelector('.custom-datepicker, [data-class="flatpickr-right"]')) {
        const [{ default: flatpickr }] = await Promise.all([
            import('flatpickr'),
            import('flatpickr/dist/flatpickr.min.css'),
        ]);

        window.flatpickr = flatpickr;
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
