/**
 * ============================================================
 *  HappyBangladesh DMS — SR Panel Service Worker
 *  Cache Strategy:
 *    - App Shell (CSS/JS/fonts/icons): Cache-First
 *    - SR Page navigations: Network-First → Cache fallback
 *    - API endpoints (/sr/api/):  Network-Only (always fresh)
 *    - Offline: Serve sr-offline.html for failed navigations
 * ============================================================
 */

const CACHE_VERSION = 'v1';
const SHELL_CACHE   = `sr-shell-${CACHE_VERSION}`;
const PAGES_CACHE   = `sr-pages-${CACHE_VERSION}`;

// Static assets to pre-cache on install (app shell)
const SHELL_ASSETS = [
  './dashboard',
  '../sr-offline.html',
  '../assets/css/sr_app.css',
  '../assets/css/app.css',
  '../assets/js/app.js',
  '../assets/images/icons/sr/icon-192.png',
  '../assets/images/icons/sr/icon-512.png',
  '../assets/images/logo.png',
  'https://cdn.tailwindcss.com',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css',
  'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap',
];

// ── Install: pre-cache shell assets ──────────────────────────
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(SHELL_CACHE).then(cache => {
      // Add assets one-by-one so a single failure doesn't abort all
      return Promise.allSettled(
        SHELL_ASSETS.map(url =>
          cache.add(url).catch(err => console.warn('[SR-SW] Pre-cache failed:', url, err))
        )
      );
    }).then(() => self.skipWaiting())
  );
});

// ── Activate: purge old caches ────────────────────────────────
self.addEventListener('activate', event => {
  const CURRENT_CACHES = [SHELL_CACHE, PAGES_CACHE];
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys
          .filter(key => key.startsWith('sr-') && !CURRENT_CACHES.includes(key))
          .map(key => {
            console.log('[SR-SW] Deleting old cache:', key);
            return caches.delete(key);
          })
      )
    ).then(() => self.clients.claim())
  );
});

// ── Fetch: routing logic ──────────────────────────────────────
self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip non-GET, cross-origin (except known CDNs), and browser extensions
  if (request.method !== 'GET') return;
  if (url.protocol === 'chrome-extension:') return;

  // 1. API calls — always network only, no caching
  if (url.pathname.includes('/sr/api/')) {
    event.respondWith(fetch(request));
    return;
  }

  // 2. Static shell assets — cache-first
  if (isShellAsset(url)) {
    event.respondWith(cacheFirst(request, SHELL_CACHE));
    return;
  }

  // 3. SR page navigations — network-first with cache fallback + offline page
  if (request.mode === 'navigate' || url.pathname.includes('/sr/')) {
    event.respondWith(networkFirstWithOffline(request));
    return;
  }

  // 4. Everything else — network-first
  event.respondWith(networkFirst(request, PAGES_CACHE));
});

// ── Helpers ───────────────────────────────────────────────────

function isShellAsset(url) {
  const shellOrigins = ['fonts.googleapis.com', 'fonts.gstatic.com', 'cdnjs.cloudflare.com', 'cdn.tailwindcss.com', 'cdn.jsdelivr.net', 'unpkg.com'];
  if (shellOrigins.some(o => url.hostname.includes(o))) return true;
  return url.pathname.match(/\.(css|js|png|jpg|jpeg|svg|ico|woff|woff2|ttf)$/i);
}

async function cacheFirst(request, cacheName) {
  const cached = await caches.match(request);
  if (cached) return cached;
  try {
    const response = await fetch(request);
    if (response.ok) {
      const cache = await caches.open(cacheName);
      cache.put(request, response.clone());
    }
    return response;
  } catch {
    return new Response('Asset unavailable offline', { status: 503 });
  }
}

async function networkFirst(request, cacheName) {
  try {
    const response = await fetch(request);
    if (response.ok) {
      const cache = await caches.open(cacheName);
      cache.put(request, response.clone());
    }
    return response;
  } catch {
    const cached = await caches.match(request);
    return cached || new Response('Unavailable offline', { status: 503 });
  }
}

async function networkFirstWithOffline(request) {
  try {
    const response = await fetch(request);
    if (response.ok) {
      const cache = await caches.open(PAGES_CACHE);
      cache.put(request, response.clone());
    }
    return response;
  } catch {
    const cached = await caches.match(request);
    if (cached) return cached;
    // Serve the offline fallback page
    const offlinePage = await caches.match('../sr-offline.html') ||
                        await caches.match('./sr-offline.html');
    return offlinePage || new Response('<h1>You are offline</h1>', {
      status: 503,
      headers: { 'Content-Type': 'text/html' }
    });
  }
}
