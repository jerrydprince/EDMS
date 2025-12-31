/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

//window.baseUrl = "/images";

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

//import 'flowbite';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';

import Pusher from 'pusher-js';

window.Pusher = Pusher;

// window.Echo = new Echo({
//     broadcaster: "reverb",
//     key: "fq9sswciqq9qke6hudgh",
//     wsHost: "localhost",
//     wsPort: 8080,
//     wssPort: 8080,
//     forceTLS: "http",
//     enabledTransports: ['ws', 'wss'],
// });

// window.Echo = new Echo({
//     broadcaster: 'reverb', // Use 'pusher' if you're using Pusher, change to 'reverb' if it's correct
//     key: process.env.REVERB_APP_KEY,
//     wsHost: process.env.REVERB_HOST,
//     wsPort: process.env.REVERB_PORT,
//     wssPort: process.env.REVERB_PORT,
//     forceTLS: (process.env.REVERB_SCHEME ?? 'https') === 'https',
//     enabledTransports: ['ws', 'wss'],
// });

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: import.meta.env.VITE_PUSHER_APP_KEY,
//     cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
//     wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
//     wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
//     wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
//     forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
//     enabledTransports: ['ws', 'wss'],
// });

//for laravel mix
// window.Echo = new Echo({
//     broadcaster: 'reverb',
//     key: process.env.MIX_REVERB_APP_KEY,
//     wsHost: process.env.MIX_REVERB_HOST,
//     wsPort: process.env.MIX_REVERB_PORT,
//     wssPort: process.env.MIX_REVERB_PORT,
//     forceTLS: (process.env.MIX_REVERB_SCHEME ?? 'https') === 'https',
//     enabledTransports: ['ws', 'wss'],
// });

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    wssPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
