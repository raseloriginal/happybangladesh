<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title><?= isset($pageTitle) ? h($pageTitle) . ' — ' : '' ?><?= APP_NAME ?></title>
  <meta name="description" content="<?= APP_NAME ?> — DSR Mobile App">
  <meta name="theme-color" content="#2563eb">

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config = { theme: { extend: { colors: { brand: { DEFAULT:'#2563eb', light:'#3b82f6', dark:'#1d4ed8' } } } } }</script>

  <!-- Leaflet.js for maps -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

  <!-- Font Awesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- DSR App CSS -->
  <link rel="stylesheet" href="<?= asset('css/dsr_app.css') ?>">

  <?= $extraHead ?? '' ?>
</head>
<body class="dsr-app-body bg-gray-50 text-gray-800 antialiased" style="font-family: 'Inter', sans-serif;">

  <div class="dsr-app-shell flex flex-col h-screen overflow-hidden max-w-[480px] mx-auto bg-gray-50 relative shadow-2xl">
    
    <!-- Main Content Area -->
    <main class="dsr-main flex-1 overflow-y-auto pb-24 relative scroll-smooth" id="dsrMain">
      
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

    <!-- Bottom Navigation -->
    <nav class="dsr-bottom-nav absolute bottom-0 left-0 w-full bg-white border-t border-gray-100 shadow-[0_-10px_40px_rgba(0,0,0,0.08)] z-40 rounded-t-3xl pb-[env(safe-area-inset-bottom)]">
      <div class="flex justify-around items-end h-16 px-2 pb-2">
        <a href="<?= url('dsr/dashboard') ?>" class="nav-item flex flex-col items-center justify-center w-1/5 <?= strpos($_SERVER['REQUEST_URI'], '/dsr/dashboard') !== false ? 'text-brand' : 'text-gray-400 hover:text-gray-600' ?> transition-colors">
          <i class="fa-solid fa-house text-xl mb-1"></i>
          <span class="text-[10px] font-semibold">Home</span>
        </a>
        <a href="<?= url('dsr/van-stock') ?>" class="nav-item flex flex-col items-center justify-center w-1/5 <?= strpos($_SERVER['REQUEST_URI'], '/dsr/van-stock') !== false ? 'text-brand' : 'text-gray-400 hover:text-gray-600' ?> transition-colors">
          <i class="fa-solid fa-boxes-stacked text-xl mb-1"></i>
          <span class="text-[10px] font-semibold">Inventory</span>
        </a>
        <a href="<?= url('dsr/delivery') ?>" class="nav-item flex flex-col items-center justify-center w-1/5 relative">
          <div class="nav-fab bg-brand text-white w-14 h-14 rounded-full flex items-center justify-center shadow-[0_8px_20px_rgba(37,99,235,0.4)] absolute -top-8 left-1/2 transform -translate-x-1/2 ring-4 ring-white transition-transform active:scale-95">
            <i class="fa-solid fa-motorcycle text-2xl"></i>
          </div>
          <span class="text-[10px] font-semibold text-gray-700 mt-7 <?= strpos($_SERVER['REQUEST_URI'], '/dsr/delivery') !== false ? 'text-brand' : '' ?>">Delivery</span>
        </a>
        <a href="<?= url('dsr/settlement') ?>" class="nav-item flex flex-col items-center justify-center w-1/5 <?= strpos($_SERVER['REQUEST_URI'], '/dsr/settlement') !== false ? 'text-brand' : 'text-gray-400 hover:text-gray-600' ?> transition-colors">
          <i class="fa-solid fa-file-invoice-dollar text-xl mb-1"></i>
          <span class="text-[10px] font-semibold">Settle</span>
        </a>
        <a href="<?= url('dsr/profile') ?>" class="nav-item flex flex-col items-center justify-center w-1/5 <?= strpos($_SERVER['REQUEST_URI'], '/dsr/profile') !== false ? 'text-brand' : 'text-gray-400 hover:text-gray-600' ?> transition-colors">
          <i class="fa-solid fa-user text-xl mb-1"></i>
          <span class="text-[10px] font-semibold">Profile</span>
        </a>
      </div>
    </nav>
  </div>

  <?= $extraScripts ?? '' ?>
</body>
</html>
