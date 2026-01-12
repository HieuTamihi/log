const CACHE_NAME = 'leverage-fluency-v2'; // Đã cập nhật lên v2 để clear cache cũ
const urlsToCache = [
    '/',
    '/index.php',
    '/style.css',
    '/login.php',
    '/register.php'
];

// Install service worker
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('Opened cache');
                return cache.addAll(urlsToCache);
            })
    );
    self.skipWaiting(); // Kích hoạt ngay lập tức
});

// NETWORK FIRST STRATEGY (Ưu tiên mạng, rớt mạng mới dùng cache)
self.addEventListener('fetch', event => {
    event.respondWith(
        fetch(event.request)
            .then(response => {
                // 1. Tải thành công từ mạng -> Trả về dữ liệu này
                
                // (Tùy chọn) Lưu bản mới nhất vào cache để dành cho lần sau offline
                if (response && response.status === 200 && response.type === 'basic') {
                    const responseToCache = response.clone();
                    caches.open(CACHE_NAME).then(cache => {
                        cache.put(event.request, responseToCache);
                    });
                }
                
                return response;
            })
            .catch(() => {
                // 2. Mất mạng hoặc lỗi server -> Lấy từ cache
                return caches.match(event.request);
            })
    );
});

// Xóa cache cũ khi có version mới
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
