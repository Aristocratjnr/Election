const CACHE_NAME = 'smartvote-v1';
const OFFLINE_URL = '/Election/offline.html';
const ASSETS_TO_CACHE = [
  '/Election/',
  '/Election/index.php',
  '/Election/login.php',
  '/Election/student.php',
  '/Election/dashboard.php',
  '/Election/offline.html', // offline fallback
  '/Election/assets/css/student.css',
  '/Election/assets/css/dashboard.css',
  '/Election/assets/js/main.js',
  '/Election/assets/js/dashboard.js',
  '/Election/assets/img/favicon/favicon.ico',
  '/Election/assets/img/favicon/apple-touch-icon.png',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css',
  'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => cache.addAll(ASSETS_TO_CACHE))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

self.addEventListener('fetch', (event) => {
  // Handle navigation requests (HTML pages)
  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request)
        .then((response) => response)
        .catch(() => caches.match(OFFLINE_URL))
    );
    return;
  }
  // Handle other requests
  event.respondWith(
    caches.match(event.request)
      .then((response) => response || fetch(event.request)
        .then((networkResponse) => {
          if (!networkResponse || networkResponse.status !== 200 || networkResponse.type !== 'basic') {
            return networkResponse;
          }
          const responseClone = networkResponse.clone();
          caches.open(CACHE_NAME).then(cache => cache.put(event.request, responseClone));
          return networkResponse;
        })
      )
  );
});
