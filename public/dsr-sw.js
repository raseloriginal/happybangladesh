/**
 * ============================================================
 *  HappyBangladesh DMS — DSR Panel Service Worker
 *
 *  All paths are derived dynamically from self.location so
 *  this works correctly on BOTH:
 *    • localhost subfolder : http://localhost/happybangladesh/
 *    • live server root    : https://domain.com/
 *
 *  Cache Strategy:
 *    Static shell (CSS/JS/fonts/icons) → Cache-First
 *    DSR page navigations              → Network-First → Cache → Offline page
 *    Mutable actions (delivery, settle)→ Network-Only  (always fresh)
 * ============================================================
 */

'use strict';

const CACHE_VERSION = 'v2';
const SHELL_CACHE = `dsr-shell-${CACHE_VERSION}`;
const PAGES_CACHE = `dsr-pages-${CACHE_VERSION}`;

// ── Derive base URL from the SW script's own location ────────
// SW lives at: http(s)://host/[subfolder]/dsr-sw.js
// So its directory is: http(s)://host/[subfolder]/
const SW_DIR = self.location.href.replace(/\/[^/]*$/, '/');
// e.g. "http://localhost/happybangladesh/" or "https://domain.com/"

// Build all asset URLs relative to the SW's directory
const OFFLINE_URL = SW_DIR + 'dsr-offline.html';

const SHELL_ASSETS = [
    SW_DIR + 'dsr/dashboard',
    SW_DIR + 'dsr/login',
    OFFLINE_URL,
    SW_DIR + 'assets/css/dsr_app.css',
    SW_DIR + 'assets/js/app.js',
    SW_DIR + 'assets/images/icons/dsr/icon-192.png',
    SW_DIR + 'assets/images/icons/dsr/icon-512.png',
    SW_DIR + 'assets/images/logo.png',
    // CDN assets (cached by full URL)
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css',
    'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap',
];

// ── Install: pre-cache shell assets ──────────────────────────
self.addEventListener('install', event => {
    console.log('[DSR-SW] Installing, cache version:', CACHE_VERSION);
    event.waitUntil(
        caches.open(SHELL_CACHE).then(cache => {
            return Promise.allSettled(
                SHELL_ASSETS.map(url =>
                    cache.add(url).catch(err =>
                        console.warn('[DSR-SW] Pre-cache skipped:', url, err.message)
                    )
                )
            );
        }).then(() => {
            console.log('[DSR-SW] Shell pre-cached. Skipping waiting.');
            return self.skipWaiting();
        })
    );
});

// ── Activate: delete old caches ───────────────────────────────
self.addEventListener('activate', event => {
    const KEEP = [SHELL_CACHE, PAGES_CACHE];
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys
                    .filter(k => k.startsWith('dsr-') && !KEEP.includes(k))
                    .map(k => { console.log('[DSR-SW] Purging old cache:', k); return caches.delete(k); })
            )
        ).then(() => {
            console.log('[DSR-SW] Active and controlling clients.');
            return self.clients.claim();
        })
    );
});

// ── Fetch: routing ────────────────────────────────────────────
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // Ignore non-GET and browser extension requests
    if (request.method !== 'GET') return;
    if (url.protocol === 'chrome-extension:') return;

    // 1. Mutable DSR actions → Network-Only (never cache)
    //    These are form submissions, updates, etc. that must always hit the server.
    const NETWORK_ONLY_PATTERNS = [
        '/dsr/api/', '/dsr/delivery/update', '/dsr/collection/',
        '/dsr/settlement/', '/dsr/damage/', '/dsr/expenses/store',
        '/dsr/scanner/scan', '/dsr/logout',
    ];
    if (NETWORK_ONLY_PATTERNS.some(p => url.pathname.includes(p))) {
        event.respondWith(fetch(request));
        return;
    }

    // 2. Static assets (own + CDN) → Cache-First
    if (isStaticAsset(url)) {
        event.respondWith(cacheFirst(request, SHELL_CACHE));
        return;
    }

    // 3. DSR page navigations → Network-First + offline fallback
    if (request.mode === 'navigate') {
        event.respondWith(networkFirstWithOffline(request));
        return;
    }

    // 4. Everything else → Network-First
    event.respondWith(networkFirst(request, PAGES_CACHE));
});

// ── Strategy helpers ──────────────────────────────────────────

function isStaticAsset(url) {
    const cdnHosts = [
        'fonts.googleapis.com', 'fonts.gstatic.com',
        'cdnjs.cloudflare.com', 'cdn.tailwindcss.com',
        'cdn.jsdelivr.net', 'unpkg.com',
    ];
    if (cdnHosts.some(h => url.hostname.includes(h))) return true;
    return /\.(css|js|png|jpg|jpeg|webp|svg|ico|woff|woff2|ttf|eot)(\?.*)?$/i.test(url.pathname);
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
    } catch (err) {
        console.warn('[DSR-SW] Cache-first fetch failed:', request.url);
        return new Response('Asset unavailable offline.', { status: 503, headers: { 'Content-Type': 'text/plain' } });
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
        return cached || new Response('Unavailable offline.', { status: 503, headers: { 'Content-Type': 'text/plain' } });
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
        // Try the exact cached URL first
        const cached = await caches.match(request);
        if (cached) return cached;

        // Fall back to the offline page (cached by absolute URL during install)
        const offline = await caches.match(OFFLINE_URL);
        if (offline) return offline;

        return new Response(
            '<!DOCTYPE html><html lang="bn"><head><meta charset="UTF-8"><title>অফলাইন</title></head><body style="text-align:center;padding:40px;font-family:sans-serif"><h1>ইন্টারনেট নেই</h1><p>অনুগ্রহ করে আবার চেষ্টা করুন।</p><button onclick="location.reload()">Retry</button></body></html>',
            { status: 503, headers: { 'Content-Type': 'text/html; charset=utf-8' } }
        );
    }
}
