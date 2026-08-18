<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title><?= isset($pageTitle) ? h($pageTitle) . ' — ' : '' ?><?= APP_NAME ?></title>
  <meta name="description" content="<?= APP_NAME ?> — DSR Mobile App">
  <link rel="icon" type="image/png" href="<?= asset('images/icons/dsr/icon-192.png') ?>">
  <link rel="apple-touch-icon" href="<?= asset('images/icons/dsr/apple-touch-icon.png') ?>">
  <meta name="theme-color" content="#1e40af">

  <!-- PWA: Web App Manifest -->
  <link rel="manifest" href="<?= BASE_URL ?>/dsr-manifest.php">

  <!-- PWA: iOS Safari meta tags -->
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="apple-mobile-web-app-title" content="DSR App">

  <!-- PWA: Service Worker base URL (resolved by sw-register.js) -->
  <meta name="sw-base-url" content="<?= BASE_URL ?>/">

  <!-- Tailwind CSS -->
  <link rel="stylesheet" href="<?= asset('css/tailwind.css') ?>">

  <!-- Leaflet.js for maps -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

  <!-- Font Awesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Base CSS -->
  <link rel="stylesheet" href="<?= asset('css/app.css') ?>">

  <!-- DSR App CSS -->
  <link rel="stylesheet" href="<?= asset('css/dsr_app.css') ?>?v=<?= time() ?>">

  <?= $extraHead ?? '' ?>
</head>
<body class="dsr-app-body bg-gray-50 text-gray-800 antialiased" style="font-family: 'Hind Siliguri', 'Inter', sans-serif;">

  <div class="dsr-app-shell flex flex-col h-screen overflow-hidden max-w-[480px] mx-auto bg-gray-50 relative shadow-2xl">
    
    <!-- Main Content Area -->
    <main class="dsr-main flex-1 overflow-y-auto pb-20 relative scroll-smooth" id="dsrMain">
      
      <!-- Global Toast Notification Container -->
      <div id="toastContainer" class="fixed top-4 left-1/2 transform -translate-x-1/2 z-[999999] w-11/12 max-w-[420px] pointer-events-none flex flex-col gap-2"></div>
      <script>
        function showToast(message, type = 'default') {
          let container = document.getElementById('toastContainer');
          if (!container) {
            container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'fixed top-4 left-1/2 transform -translate-x-1/2 z-[999999] w-11/12 max-w-[420px] pointer-events-none flex flex-col gap-2';
            document.body.appendChild(container);
          }
          const toast = document.createElement('div');
          
          let bgClass = 'bg-slate-900 text-white border-slate-700';
          if (type === 'error' || (typeof message === 'string' && (message.includes('❌') || message.includes('ত্রুটি') || message.includes('ব্যর্থ')))) {
            bgClass = 'bg-rose-600 text-white border-rose-500';
          } else if (type === 'warning' || (typeof message === 'string' && (message.includes('⚠️') || message.includes('সতর্কতা')))) {
            bgClass = 'bg-amber-600 text-white border-amber-500';
          } else if (type === 'success' || (typeof message === 'string' && (message.includes('✅') || message.includes('সফল')))) {
            bgClass = 'bg-emerald-600 text-white border-emerald-500';
          }

          toast.className = `${bgClass} backdrop-blur-md px-4 py-3 rounded-2xl shadow-2xl border text-xs sm:text-sm font-bold flex items-center justify-center gap-2 pointer-events-auto transform -translate-y-2 opacity-0 transition-all duration-300 text-center`;
          toast.innerHTML = `<span>${message}</span>`;
          container.appendChild(toast);

          setTimeout(() => {
            toast.classList.remove('-translate-y-2', 'opacity-0');
          }, 10);

          setTimeout(() => {
            toast.classList.add('opacity-0', '-translate-y-2');
            setTimeout(() => toast.remove(), 300);
          }, 3500);
        }
      </script>

      <!-- Flash alerts -->
      <?php $flash = Auth::getFlash(); if ($flash): ?>
        <div class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 w-11/12 max-w-[440px]" id="dsrFlash">
          <div class="flex items-center gap-3 p-4 rounded-2xl shadow-xl text-white <?= $flash['type'] === 'success' ? 'bg-green-600' : 'bg-red-600' ?>">
            <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?> text-xl"></i>
            <span class="text-sm font-medium"><?= h($flash['message']) ?></span>
          </div>
        </div>
        <script>setTimeout(()=>{ const f=document.getElementById('dsrFlash'); if(f){ f.style.transition='opacity 0.4s'; f.style.opacity='0'; setTimeout(()=>f.remove(),400); }},3500);</script>
      <?php endif; ?>

      <!-- View Content -->
      <?= $content ?>
      
    </main>

    <!-- Bottom Navigation Bar -->
    <?php if (empty($hideBottomNav)): ?>
      <?php include MOD_PATH . '/DSR/views/partials/_bottom_nav.php'; ?>
    <?php endif; ?>

  </div>

  <?= $extraScripts ?? '' ?>

  <!-- PWA: Service Worker Registration -->
  <script src="<?= BASE_URL ?>/dsr-sw-register.js" defer></script>

  <!-- Background Geolocation Preloader & Tracker -->
  <script>
    (function() {
      if (!navigator.geolocation) return;

      const geoOptions = {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
      };

      // ── Location push to server ─────────────────────────────
      const PUSH_URL      = '<?= BASE_URL ?>/dsr/api/location/push';
      const PUSH_INTERVAL = 60000; // 60 seconds
      let lastPushTime    = 0;
      let lastPushedLat   = null;
      let lastPushedLng   = null;

      function reverseGeocode(lat, lng, callback) {
        fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`)
          .then(r => r.json())
          .then(d => callback(d.display_name || null))
          .catch(() => callback(null));
      }

      function pushLocationToServer(lat, lng, accuracy) {
        const now = Date.now();
        if (now - lastPushTime < PUSH_INTERVAL) return; // throttle

        lastPushTime  = now;
        lastPushedLat = lat;
        lastPushedLng = lng;

        function doPost(address) {
          fetch(PUSH_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ lat, lng, address, accuracy })
          }).catch(() => {}); // silent — background push
        }

        // Try to get address, fall back to null if it times out
        const geocodeTimeout = setTimeout(() => doPost(null), 3000);
        reverseGeocode(lat, lng, addr => {
          clearTimeout(geocodeTimeout);
          doPost(addr);
        });
      }
      // ────────────────────────────────────────────────────────

      function updateLocationCache(pos) {
        const lat      = pos.coords.latitude;
        const lng      = pos.coords.longitude;
        const accuracy = pos.coords.accuracy;
        
        // Save to cache for instant preloading
        localStorage.setItem('dsr_last_lat', lat);
        localStorage.setItem('dsr_last_lng', lng);

        // Push to server every 60 seconds
        pushLocationToServer(lat, lng, accuracy);
        
        // If a map is currently open on this page, update coordinates dynamically
        if (typeof mainMap !== 'undefined' && mainMap && typeof placeMyLocationMarker === 'function') {
          const oldLat = typeof myLat !== 'undefined' ? myLat : null;
          const oldLng = typeof myLng !== 'undefined' ? myLng : null;
          
          myLat = lat;
          myLng = lng;
          placeMyLocationMarker();
        }
      }

      function handleGeoError(err) {
        console.warn("Background geolocation tracking error:", err.message);
      }

      // Immediately ask for location permission and watch position in the background
      // across all DSR app pages to pre-fill coordinates in localStorage
      navigator.geolocation.watchPosition(updateLocationCache, handleGeoError, geoOptions);
    })();
  </script>
</body>
</html>
