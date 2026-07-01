const CACHE_NAME = 'sanctuaryhub-static-v1';
const STATIC_ASSETS = [
    '/manifest.json',
    '/assets/css/style.css',
    '/assets/css/sidebar.css',
    '/assets/css/dashboard.css',
    '/assets/css/table.css',
    '/assets/css/wizard.css',
    '/assets/css/profile.css',
    '/assets/css/hasil.css',
    '/assets/img/sanctuary-hub-mark.svg',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(STATIC_ASSETS).catch(() => undefined))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
        ))
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);
    const isStaticAsset = url.pathname.startsWith('/assets/')
        || url.pathname === '/manifest.json'
        || url.pathname === '/favicon.ico';

    if (!isStaticAsset) {
        return;
    }

    event.respondWith(
        caches.match(event.request).then((cached) => cached || fetch(event.request))
    );
});
