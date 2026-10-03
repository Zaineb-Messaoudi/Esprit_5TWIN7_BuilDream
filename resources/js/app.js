import { createPopper } from '@popperjs/core';
import './bootstrap';
import Alpine from 'alpinejs';
import ApexCharts from 'apexcharts';

// flatpickr
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
// FullCalendar
import { Calendar } from 'fullcalendar';



window.Alpine = Alpine;
window.createPopper = createPopper;
window.ApexCharts = ApexCharts;
window.flatpickr = flatpickr;
window.FullCalendar = Calendar;

Alpine.store('chat', {
    activeContact: 'Claude',
    messages: {
        'Claude': [
            { text: 'Hello! How can I assist you with the SolarShare admin today?', sent: false },
            { text: 'I need to check the new user management views.', sent: true },
            { text: 'They are ready! I\'ve implemented the resource controllers and the Blade views.', sent: false },
        ],
        'Zaineb': [{ text: 'Welcome to the team!', sent: false }],
    },
    sendMessage(contact, text) {
        if (!text.trim()) return;
        this.messages[contact].push({ text, sent: true });
    },
    setActiveContact(contact) {
        this.activeContact = contact;
    }
});

Alpine.store('email', {
    activeFolder: 'Inbox',
    selectedEmail: null,
    folders: {
        'Inbox': [
            { id: 1, from: 'Support Team', subject: 'Ticket #1001: Payment Issue', body: 'Dear User, we are sorry to hear...', date: 'Oct 1' },
            { id: 2, from: 'Marketing', subject: 'New Campaign Results', body: 'The Summer Sale was a huge success...', date: 'Oct 2' },
        ],
        'Sent': [],
        'Drafts': [],
    },
    setFolder(folder) {
        this.activeFolder = folder;
        this.selectedEmail = null;
    },
    selectEmail(email) {
        this.selectedEmail = email;
    }
});

Alpine.start();

// Initialize components on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    // Map imports
    if (document.querySelector('#mapOne')) {
        import('./components/map').then(module => module.initMap());
    }

    // Chart imports
    if (document.querySelector('#chartOne')) {
        import('./components/chart/chart-1').then(module => module.initChartOne());
    }
    if (document.querySelector('#chartTwo')) {
        import('./components/chart/chart-2').then(module => module.initChartTwo());
    }
    if (document.querySelector('#chartThree')) {
        import('./components/chart/chart-3').then(module => module.initChartThree());
    }
    if (document.querySelector('#chartSix')) {
        import('./components/chart/chart-6').then(module => module.initChartSix());
    }
    if (document.querySelector('#chartEight')) {
        import('./components/chart/chart-8').then(module => module.initChartEight());
    }
    if (document.querySelector('#chartThirteen')) {
        import('./components/chart/chart-13').then(module => module.initChartThirteen());
    }

    // Calendar init
    if (document.querySelector('#calendar')) {
        import('./components/calendar-init').then(module => module.calendarInit());
    }
});
