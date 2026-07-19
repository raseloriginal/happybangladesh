<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title><?= isset($pageTitle) ? h($pageTitle) . ' — ' : '' ?><?= APP_NAME ?></title>
  <meta name="description" content="<?= APP_NAME ?> — SR Mobile App">
  <meta name="theme-color" content="#2563eb">

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



  </div><!-- /.sr-app-shell -->

  <!-- App JS -->
  <script src="<?= asset('js/app.js') ?>"></script>
  <?= $extraScripts ?? '' ?>
</body>
</html>
