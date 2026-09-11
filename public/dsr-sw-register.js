/**
 * DSR Panel — Service Worker Registration
 * Registers dsr-sw.js scoped to the /dsr/ path.
 * This script is loaded from the dsr_app.php layout.
 */
(function () {
  'use strict';

  if (!('serviceWorker' in navigator)) {
    console.info('[DSR-PWA] Service Workers not supported in this browser.');
    return;
  }

  // Determine the base path (works on localhost subfolders and live root)
  const swUrl = document.querySelector('meta[name="sw-base-url"]')?.content || '/';
  // Build path: if swUrl = '/happybangladesh/', sw lives at '/happybangladesh/dsr-sw.js'
  const swPath = swUrl.replace(/\/$/, '') + '/dsr-sw.js';
  const swScope = swUrl.replace(/\/$/, '') + '/dsr/';

  window.addEventListener('load', function () {
    navigator.serviceWorker
      .register(swPath, { scope: swScope })
      .then(function (registration) {
        console.info('[DSR-PWA] Service Worker registered. Scope:', registration.scope);

        // Listen for SW updates and notify user
        registration.addEventListener('updatefound', function () {
          const newWorker = registration.installing;
          if (!newWorker) return;
          newWorker.addEventListener('statechange', function () {
            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
              console.info('[DSR-PWA] New version available. Refreshing...');
              newWorker.postMessage({ type: 'SKIP_WAITING' });
              window.location.reload();
            }
          });
        });
      })
      .catch(function (err) {
        console.warn('[DSR-PWA] Service Worker registration failed:', err);
      });

    // Reload when SW takes control (after update)
    navigator.serviceWorker.addEventListener('controllerchange', function () {
      window.location.reload();
    });
  });
})();
