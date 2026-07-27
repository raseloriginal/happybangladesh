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
  <link rel="manifest" href="<?= BASE_URL ?>/sr.webmanifest">

  <!-- PWA: iOS Safari meta tags -->
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="apple-mobile-web-app-title" content="SR App">

  <!-- PWA: Service Worker base URL (resolved by sw-register.js) -->
  <meta name="sw-base-url" content="<?= BASE_URL ?>/">

  <!-- Tailwind CSS CDN (for compatibility with existing views) -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config = { theme: { extend: { colors: { brand: { DEFAULT:'#2563eb', light:'#3b82f6', dark:'#1d4ed8' } } } } }</script>

  <!-- Leaflet.js for maps -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>


  <!-- Font Awesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Base CSS (for compatibility) -->
  <link rel="stylesheet" href="<?= asset('css/app.css') ?>">

  <!-- SR App CSS -->
  <link rel="stylesheet" href="<?= asset('css/sr_app.css') ?>?v=<?= time() ?>">

  <?= $extraHead ?? '' ?>
</head>
<body class="sr-app-body">

  <!-- App Shell -->
  <div class="sr-app-shell" id="srAppShell">

    <!-- Page Content -->
    <main class="sr-app-main" id="srMain">
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
  <?= $extraScripts ?? '' ?>

  <!-- PWA: Service Worker Registration -->
  <script src="<?= BASE_URL ?>/sr-sw-register.js" defer></script>
</body>
</html>
