const CACHE_NAME = 'playpayments-pwa-v1';
const urlsToCache = [
  '/offline',
  '/css/app.css',
  '/images/logo.png',
  '/images/playpayments-logo-top.webp'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(urlsToCache);
      })
  );
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  self.clients.claim();
});

self.addEventListener('fetch', event => {
  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request).catch(() => {
        return caches.match('/offline');
      })
    );
  } else {
    event.respondWith(
      caches.match(event.request)
        .then(response => {
           // Allow caching certain public static assets lazily
           if (response) {
               return response;
           }
           const fetchRequest = event.request.clone();
           return fetch(fetchRequest).then(
               function(res) {
                   if(!res || res.status !== 200 || res.type !== 'basic') {
                       return res;
                   }
                   if (event.request.url.includes('/images/') || event.request.url.includes('/css/')) {
                       const responseToCache = res.clone();
                       caches.open(CACHE_NAME).then(function(cache) {
                           cache.put(event.request, responseToCache);
                       });
                   }
                   return res;
               }
           ).catch(() => {
               // Fallback when fetch fails for a subresource
           });
        })
    );
  }
});
