import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.Pusher = Pusher;

const pusherKey = document.querySelector('meta[name="pusher-key"]')?.getAttribute('content');
const pusherCluster = document.querySelector('meta[name="pusher-cluster"]')?.getAttribute('content') || 'mt1';
const reverbPort = Number(document.querySelector('meta[name="reverb-port"]')?.getAttribute('content') || 8081);

if (pusherKey) {
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: pusherKey,
        cluster: pusherCluster,
        forceTLS: false,
        wsHost: window.location.hostname,
        wsPort: reverbPort,
        wssPort: reverbPort,
        disableStats: true,
        enabledTransports: ['ws', 'wss'],
    });
}
