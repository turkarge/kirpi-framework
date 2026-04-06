const CACHE_NAME = 'kirpi-pwa-v3';
const OFFLINE_URL = '/offline.html';
const CORE_CACHE = [
  OFFLINE_URL,
  '/assets/admin.css',
  '/vendor/tabler/dist/css/tabler.css',
  '/vendor/tabler/static/logo-small.svg',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(CORE_CACHE))
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(
        keys
          .filter((key) => key !== CACHE_NAME)
          .map((key) => caches.delete(key))
      )
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') {
    return;
  }

  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request).catch(async () => {
        const offlinePage = await caches.match(OFFLINE_URL);
        return offlinePage || new Response('Offline', {
          status: 503,
          headers: {'Content-Type': 'text/plain; charset=utf-8'},
        });
      })
    );
    return;
  }

  const url = new URL(event.request.url);
  if (url.origin !== self.location.origin) {
    return;
  }

  // API responses should always be fresh. Do not cache them in SW.
  if (url.pathname.startsWith('/kirpi/api/')) {
    event.respondWith(
      fetch(event.request).catch(() => new Response(JSON.stringify({
        ok: false,
        error: 'offline',
      }), {
        status: 503,
        headers: {'Content-Type': 'application/json; charset=utf-8'},
      }))
    );
    return;
  }

  event.respondWith(
    caches.match(event.request).then((cached) => {
      const network = fetch(event.request)
        .then((response) => {
          if (response && response.status === 200) {
            const copy = response.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(event.request, copy));
          }
          return response;
        })
        .catch(() => cached || new Response('', {status: 504, statusText: 'Gateway Timeout'}));

      return cached || network;
    })
  );
});
