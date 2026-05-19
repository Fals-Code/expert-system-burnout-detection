// ══════════════════════════════════════════════════════════════
// BurnoutXpert – Service Worker (PWA Offline Support)
// ══════════════════════════════════════════════════════════════

const CACHE_NAME = 'burnoutxpert-v1';
const STATIC_ASSETS = [
    '/',
    '/login',
    '/assets/css/style.css',
    '/assets/css/sidebar.css',
    '/assets/css/dashboard.css',
    '/assets/css/table.css',
    '/assets/css/wizard.css',
    '/assets/css/profile.css',
    '/assets/css/hasil.css',
    '/manifest.json',
];

// Install – Cache static assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS).catch(() => {
                // Ignore failures for individual assets
            });
        })
    );
    self.skipWaiting();
});

// Activate – Clean old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
            );
        })
    );
    self.clients.claim();
});

// Fetch – Network first, fallback to cache
self.addEventListener('fetch', (event) => {
    // Skip non-GET requests and API calls
    if (event.request.method !== 'GET') return;
    if (event.request.url.includes('/api/')) return;

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // Clone and cache successful responses
                if (response.status === 200) {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseClone);
                    });
                }
                return response;
            })
            .catch(() => {
                // Fallback to cache
                return caches.match(event.request).then((cachedResponse) => {
                    if (cachedResponse) return cachedResponse;
                    
                    // Return offline page for navigation requests
                    if (event.request.mode === 'navigate') {
                        return new Response(
                            `<!DOCTYPE html>
                            <html><head><title>Offline – BurnoutXpert</title>
                            <style>body{font-family:'Poppins',sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#0F172A;color:#E2E8F0;text-align:center;}
                            .c{max-width:400px;}.t{font-size:3rem;margin-bottom:1rem;}h1{font-size:1.5rem;margin-bottom:0.5rem;}p{color:#94A3B8;}</style></head>
                            <body><div class="c"><div class="t">📡</div><h1>Anda Sedang Offline</h1><p>Periksa koneksi internet Anda dan coba lagi.</p></div></body></html>`,
                            { headers: { 'Content-Type': 'text/html' } }
                        );
                    }
                });
            })
    );
});
