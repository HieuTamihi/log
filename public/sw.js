const CACHE_NAME = 'leverage-fluency-v4';
const urlsToCache = [
    '/',
    '/css/style.css',
    '/icons/icon-192.png',
    '/icons/icon-512.png'
];

self.addEventListener('install', event => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('Opened cache');
                // Try to cache, but don't fail if some URLs don't exist
                return Promise.allSettled(
                    urlsToCache.map(url => 
                        cache.add(url).catch(err => console.log('Failed to cache:', url))
                    )
                );
            })
    );
});

self.addEventListener('activate', event => {
    const cacheWhitelist = [CACHE_NAME];
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (!cacheWhitelist.includes(cacheName)) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    return self.clients.claim();
});

// NETWORK FIRST STRATEGY - Skip POST requests
self.addEventListener('fetch', event => {
    // Skip cross-origin requests
    if (!event.request.url.startsWith(self.location.origin)) {
        return;
    }

    // IMPORTANT: Never cache POST requests (form submissions)
    if (event.request.method !== 'GET') {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then(response => {
                // Only cache successful GET requests for static assets
                if (response && response.status === 200 && response.type === 'basic') {
                    const url = new URL(event.request.url);
                    // Only cache CSS, JS, images
                    if (url.pathname.match(/\.(css|js|png|jpg|jpeg|gif|svg|ico)$/)) {
                        const responseToCache = response.clone();
                        caches.open(CACHE_NAME).then(cache => {
                            cache.put(event.request, responseToCache);
                        });
                    }
                }
                return response;
            })
            .catch(() => {
                // Network failed, try cache
                return caches.match(event.request);
            })
    );
});
