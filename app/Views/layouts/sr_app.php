<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title><?= isset($pageTitle) ? h($pageTitle) . ' — ' : '' ?><?= APP_NAME ?></title>
  <meta name="description" content="<?= APP_NAME ?> — SR Mobile App">
  <link rel="icon" type="image/png" href="<?= asset('images/icons/sr/icon-192.png') ?>">
  <link rel="apple-touch-icon" href="<?= asset('images/icons/sr/apple-touch-icon.png') ?>">
  <meta name="theme-color" content="#2563eb">

  <!-- PWA: Web App Manifest -->
  <link rel="manifest" href="<?= BASE_URL ?>/sr-manifest.php">

  <!-- PWA: iOS Safari meta tags -->
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="apple-mobile-web-app-title" content="SR App">

  <!-- PWA: Service Worker base URL (resolved by sw-register.js) -->
  <meta name="sw-base-url" content="<?= BASE_URL ?>/">

  <!-- Tailwind CSS -->
  <link rel="stylesheet" href="<?= asset('css/tailwind.css') ?>">

  <!-- Leaflet.js for maps -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin="" defer></script>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" defer></script>


  <!-- Font Awesome 6 (Deferred) -->
  <link rel="preload" as="style" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" media="print" onload="this.media='all'">
  <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"></noscript>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Base CSS (for compatibility) -->
  <link rel="stylesheet" href="<?= asset('css/app.css') ?>">

  <!-- SR App CSS -->
  <link rel="stylesheet" href="<?= asset('css/sr_app.css') ?>?v=<?= time() ?>">

  <?= $extraHead ?? '' ?>
</head>
<body class="sr-app-body">

  <!-- Top Glowing Progress Bar (YouTube/Next.js Style) -->
  <div id="srTopProgressBar"></div>

  <!-- Global Frosted Glass Loading Overlay with Spinning Animation -->
  <div id="srGlobalLoadingOverlay">
    <div class="sr-loading-card">
      <div class="sr-spinner-wrapper">
        <div class="sr-spinner-ring sr-ring-1"></div>
        <div class="sr-spinner-ring sr-ring-2"></div>
        <div class="sr-spinner-ring sr-ring-3"></div>
        <div class="sr-spinner-center-dot"></div>
      </div>
      <div class="sr-loading-title" id="srGlobalLoadingTitle">লোড হচ্ছে...</div>
      <div class="sr-loading-subtext" id="srGlobalLoadingSubtext">অনুগ্রহ করে কিছুক্ষণ অপেক্ষা করুন</div>
    </div>
  </div>

  <!-- App Shell -->
  <div class="sr-app-shell" id="srAppShell">

    <!-- Page Content -->
    <main class="sr-app-main sr-page-enter <?= !empty($hideBottomNav) ? 'has-no-nav' : '' ?>" id="srMain">
      <!-- Flash alerts -->
      <?php
        $flash = Auth::getFlash();
        if ($flash): ?>
        <div class="sr-flash sr-flash-<?= $flash['type'] ?>" id="srFlash">
          <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
          <?= h($flash['message']) ?>
        </div>
        <script>setTimeout(()=>{ const f=document.getElementById('srFlash'); if(f){ f.style.opacity='0'; setTimeout(()=>f.remove(),400); }},3500);</script>
      <?php endif; ?>

      <?= $content ?>
    </main>

    <!-- Bottom Navigation Bar -->
    <?php if (empty($hideBottomNav)): ?>
      <?php include MOD_PATH . '/SR/views/partials/_bottom_nav.php'; ?>
    <?php endif; ?>

  </div><!-- /.sr-app-shell -->

  <!-- App JS -->
  <script src="<?= asset('js/app.js') ?>"></script>

  <!-- Global SRLoader Loading & Navigation Controller -->
  <script>
    window.SRLoader = (function() {
      let progress = 0;
      let timer = null;
      let safetyTimer = null;
      let bar = null;
      let overlay = null;
      let overlayTitle = null;
      let overlaySubtext = null;

      function getEls() {
        if (!bar) bar = document.getElementById('srTopProgressBar');
        if (!overlay) overlay = document.getElementById('srGlobalLoadingOverlay');
        if (!overlayTitle) overlayTitle = document.getElementById('srGlobalLoadingTitle');
        if (!overlaySubtext) overlaySubtext = document.getElementById('srGlobalLoadingSubtext');
      }

      function set(n) {
        getEls();
        if (!bar) return;
        progress = Math.max(0, Math.min(100, n));
        bar.style.width = progress + '%';
        if (progress > 0 && progress < 100) {
          bar.classList.add('active');
        }
      }

      function inc() {
        if (progress < 25) set(progress + 15);
        else if (progress < 60) set(progress + 8);
        else if (progress < 88) set(progress + 3);
      }

      function start(title = 'লোড হচ্ছে...', subtext = 'অনুগ্রহ করে কিছুক্ষণ অপেক্ষা করুন') {
        getEls();
        if (progress === 0 || progress >= 100) {
          set(15);
          clearInterval(timer);
          timer = setInterval(inc, 180);
        }
        showOverlay(title, subtext);
      }

      function done() {
        getEls();
        clearInterval(timer);
        timer = null;
        clearTimeout(safetyTimer);
        safetyTimer = null;
        if (bar) {
          set(100);
          setTimeout(() => {
            if (bar) bar.classList.remove('active');
            setTimeout(() => {
              if (bar) set(0);
            }, 280);
          }, 180);
        }
        hideOverlay();
      }

      function showOverlay(title = 'লোড হচ্ছে...', subtext = 'অনুগ্রহ করে কিছুক্ষণ অপেক্ষা করুন') {
        getEls();
        if (overlayTitle) overlayTitle.innerText = title;
        if (overlaySubtext) {
          overlaySubtext.innerText = subtext;
          overlaySubtext.style.display = subtext ? 'block' : 'none';
        }
        if (overlay) overlay.classList.add('active');

        clearTimeout(safetyTimer);
        safetyTimer = setTimeout(() => {
          hideOverlay();
          if (bar) bar.classList.remove('active');
        }, 8000);
      }

      function hideOverlay() {
        getEls();
        if (overlay) overlay.classList.remove('active');
      }

      function buttonLoading(btn, text = 'লোড হচ্ছে...') {
        if (!btn) return;
        if (!btn.dataset.originalHtml) {
          btn.dataset.originalHtml = btn.innerHTML;
        }
        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin" style="margin-right:6px;"></i> ${text}`;
      }

      function buttonReset(btn) {
        if (!btn || !btn.dataset.originalHtml) return;
        btn.disabled = false;
        btn.innerHTML = btn.dataset.originalHtml;
        delete btn.dataset.originalHtml;
      }

      return {
        start,
        set,
        inc,
        done,
        showOverlay,
        hideOverlay,
        buttonLoading,
        buttonReset
      };
    })();

    // ── Global Page Jump / Transition Event Interceptor ────────
    document.addEventListener('DOMContentLoaded', () => {
      // Complete loader on page load
      SRLoader.done();

      // Intercept link clicks across the SR panel for instant spinning loading feedback
      document.addEventListener('click', (e) => {
        const link = e.target.closest('a');
        if (!link) return;

        const href = link.getAttribute('href');
        // Ignore anchor tags with no href, hash jumps, javascript, or external/blank targets
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('tel:') || href.startsWith('mailto:') || link.target === '_blank' || link.hasAttribute('download')) {
          return;
        }

        // Add tactile feedback
        link.classList.add('sr-nav-tab-active-tap');
        SRLoader.start('লোড হচ্ছে...', 'পেজ লোড হচ্ছে...');
      });

      // Intercept form submissions (e.g., date filters, search forms)
      document.addEventListener('submit', (e) => {
        const form = e.target;
        if (form.getAttribute('data-no-loader') !== null) return;
        const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
        if (submitBtn) {
          SRLoader.buttonLoading(submitBtn);
        }
        SRLoader.start('লোড হচ্ছে...', 'ডাটা প্রসেস হচ্ছে...');
      });
    });

    // Handle bfcache (browser back/forward navigation)
    window.addEventListener('pageshow', (e) => {
      SRLoader.done();
    });

    // Handle beforeunload to kick off loader if page is transitioning
    window.addEventListener('beforeunload', () => {
      SRLoader.start('লোড হচ্ছে...', 'পেজ লোড হচ্ছে...');
    });

    // Hook global fetch for seamless non-intrusive progress animation on API calls
    (function() {
      const origFetch = window.fetch;
      let activeFetches = 0;

      window.fetch = function(...args) {
        activeFetches++;
        SRLoader.set(20);

        return origFetch.apply(this, args)
          .then(res => {
            activeFetches = Math.max(0, activeFetches - 1);
            if (activeFetches === 0) {
              SRLoader.done();
            }
            return res;
          })
          .catch(err => {
            activeFetches = Math.max(0, activeFetches - 1);
            if (activeFetches === 0) {
              SRLoader.done();
            }
            throw err;
          });
      };
    })();
  </script>
  <?= $extraScripts ?? '' ?>

  <!-- PWA: Service Worker Registration -->
  <script src="<?= BASE_URL ?>/sr-sw-register.js" defer></script>

  <!-- Background Geolocation Preloader & Tracker -->
  <script>
    (function() {
      if (!navigator.geolocation) return;

      const geoOptions = {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
      };

      function updateLocationCache(pos) {
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;
        
        // Save to cache for instant preloading
        localStorage.setItem('sr_last_lat', lat);
        localStorage.setItem('sr_last_lng', lng);
        
        // If the Sales map is currently open on this page, update coordinates dynamically
        if (typeof mainMap !== 'undefined' && mainMap && typeof placeMyLocationMarker === 'function') {
          const oldLat = typeof myLat !== 'undefined' ? myLat : null;
          const oldLng = typeof myLng !== 'undefined' ? myLng : null;
          
          myLat = lat;
          myLng = lng;
          placeMyLocationMarker();
          
          // Calculate distance from previous coordinate; if > 10 meters, refresh retailers
          if (oldLat && oldLng) {
            const distance = Math.round(6371000 * 2 * Math.asin(Math.sqrt(
              Math.pow(Math.sin((lat - oldLat) * Math.PI / 360), 2) +
              Math.cos(oldLat * Math.PI / 180) * Math.cos(lat * Math.PI / 180) *
              Math.pow(Math.sin((lng - oldLng) * Math.PI / 360), 2)
            )));
            if (distance > 10 && typeof loadRetailersOnMap === 'function') {
              loadRetailersOnMap();
            }
          }
        }
      }

      function handleGeoError(err) {
        console.warn("Background geolocation tracking error:", err.message);
      }

      // Immediately ask for location permission and watch position in the background
      // across all SR app pages to pre-fill coordinates in localStorage
      navigator.geolocation.watchPosition(updateLocationCache, handleGeoError, geoOptions);
    })();
  </script>
</body>
</html>
