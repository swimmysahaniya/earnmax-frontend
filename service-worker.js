const CACHE_NAME = 'pwa-static-v1';
const STATIC_ASSETS = [
  '/manifest.json',
  '/style.css',
  '/images/icons/icon-192x192.png',
  '/images/icons/icon-512x512.png'
];

// Install and cache static assets only
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      return cache.addAll(STATIC_ASSETS);
    })
  );
  self.skipWaiting();
});

// Activate: clear old caches
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => {
      return Promise.all(
        keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
      );
    })
  );
  self.clients.claim();
});

// Fetch handler
self.addEventListener('fetch', event => {
  const req = event.request;

  // Avoid caching login/auth or HTML pages (e.g. index.html)
  if (req.url.includes('/login') || req.url.endsWith('.php')) {
    return event.respondWith(fetch(req)); // Always fetch fresh
  }

  // For static assets, use cache-first
  event.respondWith(
    caches.match(req).then(cached => {
      return cached || fetch(req);
    })
  );
});
