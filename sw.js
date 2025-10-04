self.addEventListener('install', (event) => {
  event.waitUntil((async () => {
    const cache = await caches.open('manobhava-v1');
    await cache.addAll([
      '/index.html',
      '/manifest.webmanifest',
      'https://cdn.jsdelivr.net/npm/chart.js'
    ]);
  })());
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', (event) => {
  event.respondWith((async () => {
    try {
      const network = await fetch(event.request);
      const cache = await caches.open('manobhava-v1');
      cache.put(event.request, network.clone());
      return network;
    } catch (e) {
      const cached = await caches.match(event.request);
      if (cached) return cached;
      throw e;
    }
  })());
});
