// Đây là Service Worker cơ bản để trình duyệt cho phép cài đặt App
self.addEventListener('install', (e) => {
  console.log('[Service Worker] Install');
});

self.addEventListener('fetch', (e) => {
  // Bạn có thể thêm code cache offline ở đây nếu muốn
  e.respondWith(
    fetch(e.request).catch(() => {
      return new Response("Bạn đang ngoại tuyến. Vui lòng kết nối internet.");
    })
  );
});