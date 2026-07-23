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

    <!-- Floating Bottom Navigation Bar (SR Panel Floating Pill Style) -->
    <div class="fixed bottom-4 left-1/2 -translate-x-1/2 max-w-sm sm:max-w-md w-[92%] bg-white/95 backdrop-blur-md rounded-full shadow-2xl border border-slate-200/80 px-5 py-2 flex items-center justify-between z-50">
      
      <!-- Home Tab -->
      <a href="<?= url('dsr/dashboard') ?>" class="flex flex-col items-center <?= strpos($_SERVER['REQUEST_URI'], '/dsr/dashboard') !== false ? 'text-blue-600 font-bold' : 'text-slate-400 hover:text-slate-700 font-medium' ?> text-[10px]">
        <i class="fa-solid fa-house text-base mb-0.5"></i>
        <span>হোম</span>
      </a>

      <!-- Inventory Tab -->
      <a href="<?= url('dsr/van-stock') ?>" class="flex flex-col items-center <?= strpos($_SERVER['REQUEST_URI'], '/dsr/van-stock') !== false ? 'text-blue-600 font-bold' : 'text-slate-400 hover:text-slate-700 font-medium' ?> text-[10px]">
        <i class="fa-solid fa-boxes-stacked text-base mb-0.5"></i>
        <span>মাল (স্টক)</span>
      </a>

      <!-- Delivery Center Floating FAB -->
      <a href="<?= url('dsr/delivery') ?>" title="ডেলিভারি রুট" class="dsr-float-loc-btn w-12 h-12 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white flex items-center justify-center shadow-lg shadow-blue-600/40 -mt-6 hover:scale-105 transition">
        <i class="fa-solid fa-truck-fast text-lg"></i>
      </a>

      <!-- Settlement Tab -->
      <a href="<?= url('dsr/settlement') ?>" class="flex flex-col items-center <?= strpos($_SERVER['REQUEST_URI'], '/dsr/settlement') !== false ? 'text-blue-600 font-bold' : 'text-slate-400 hover:text-slate-700 font-medium' ?> text-[10px]">
        <i class="fa-solid fa-file-invoice-dollar text-base mb-0.5"></i>
        <span>হিসাব মিলাও</span>
      </a>

      <!-- Profile Tab -->
      <a href="<?= url('dsr/profile') ?>" class="flex flex-col items-center <?= strpos($_SERVER['REQUEST_URI'], '/dsr/profile') !== false ? 'text-blue-600 font-bold' : 'text-slate-400 hover:text-slate-700 font-medium' ?> text-[10px]">
        <i class="fa-solid fa-user text-base mb-0.5"></i>
        <span>প্রোফাইল</span>
      </a>

    </div>
  </div>

  <style>
  @keyframes dsrSubtleFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-4px); }
  }
  .dsr-float-loc-btn {
    animation: dsrSubtleFloat 2.5s infinite ease-in-out;
  }
  </style>

  <?= $extraScripts ?? '' ?>
</body>
</html>
