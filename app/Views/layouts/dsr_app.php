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
</body>
</html>
