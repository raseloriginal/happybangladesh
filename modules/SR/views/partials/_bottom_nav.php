<?php
/**
 * Modern Floating Bottom Navigation Bar for SR Mobile App
 * Design inspired by floating dock with center elevated FAB and halo ring
 */
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';

$isHome    = str_contains($uri, '/sr/dashboard') || $uri === '/sr' || $uri === '/sr/';
$isShops   = str_contains($uri, '/sr/retailers');
$isSales   = str_contains($uri, '/sr/sales');
$isOrders  = str_contains($uri, '/sr/orders');
$isReports = str_contains($uri, '/sr/reports');
?>

<!-- ── Modern Floating Bottom Nav Dock ────────────────────────────────────── -->
<div class="fixed bottom-3 left-1/2 -translate-x-1/2 max-w-md w-[92%] bg-white/95 backdrop-blur-md rounded-3xl shadow-[0_12px_40px_rgba(37,99,235,0.15)] border border-slate-200/80 px-4 pt-2 pb-2.5 flex items-center justify-between z-50 select-none">
  
  <!-- 1. Home Tab -->
  <a href="<?= url('sr/dashboard') ?>" 
     class="flex flex-col items-center justify-center w-14 py-1 group transition-all duration-200 <?= $isHome ? 'text-blue-600 font-bold' : 'text-slate-400 hover:text-slate-600 font-medium' ?>">
    <div class="relative flex items-center justify-center">
      <i class="fa-solid fa-house text-lg transition-transform duration-200 group-active:scale-90"></i>
      <?php if ($isHome): ?>
        <span class="absolute -bottom-1 w-1 h-1 bg-blue-600 rounded-full"></span>
      <?php endif; ?>
    </div>
    <span class="text-[10px] mt-1 tracking-tight">হোম</span>
  </a>

  <!-- 2. Shops Tab -->
  <a href="<?= url('sr/retailers') ?>" 
     class="flex flex-col items-center justify-center w-14 py-1 group transition-all duration-200 <?= $isShops ? 'text-blue-600 font-bold' : 'text-slate-400 hover:text-slate-600 font-medium' ?>">
    <div class="relative flex items-center justify-center">
      <i class="fa-solid fa-store text-lg transition-transform duration-200 group-active:scale-90"></i>
      <?php if ($isShops): ?>
        <span class="absolute -bottom-1 w-1 h-1 bg-blue-600 rounded-full"></span>
      <?php endif; ?>
    </div>
    <span class="text-[10px] mt-1 tracking-tight">Shops</span>
  </a>

  <!-- 3. Center Elevated FAB (Map / Sales View with Halo Ring) -->
  <a href="<?= url('sr/sales') ?>" 
     title="Map View" 
     class="relative group flex items-center justify-center -mt-7">
    <!-- Outer Soft Halo Ring -->
    <div class="w-14 h-14 rounded-full p-1.5 flex items-center justify-center transition-all duration-300 <?= $isSales ? 'bg-blue-500/20 ring-4 ring-blue-500/15 scale-105' : 'bg-blue-100/70 hover:bg-blue-200/80 hover:scale-105' ?>">
      <!-- Main Center Floating Circle Button -->
      <div class="w-11 h-11 rounded-full bg-blue-600 group-hover:bg-blue-700 text-white flex items-center justify-center shadow-lg shadow-blue-600/40 transition-transform duration-200 group-active:scale-95">
        <i class="fa-solid fa-map-location-dot text-base"></i>
      </div>
    </div>
  </a>

  <!-- 4. Orders Tab -->
  <a href="<?= url('sr/orders') ?>" 
     class="flex flex-col items-center justify-center w-14 py-1 group transition-all duration-200 <?= $isOrders ? 'text-blue-600 font-bold' : 'text-slate-400 hover:text-slate-600 font-medium' ?>">
    <div class="relative flex items-center justify-center">
      <i class="fa-solid fa-file-invoice text-lg transition-transform duration-200 group-active:scale-90"></i>
      <?php if ($isOrders): ?>
        <span class="absolute -bottom-1 w-1 h-1 bg-blue-600 rounded-full"></span>
      <?php endif; ?>
    </div>
    <span class="text-[10px] mt-1 tracking-tight">Orders</span>
  </a>

  <!-- 5. Report Tab -->
  <a href="<?= url('sr/reports') ?>" 
     class="flex flex-col items-center justify-center w-14 py-1 group transition-all duration-200 <?= $isReports ? 'text-blue-600 font-bold' : 'text-slate-400 hover:text-slate-600 font-medium' ?>">
    <div class="relative flex items-center justify-center">
      <i class="fa-solid fa-chart-pie text-lg transition-transform duration-200 group-active:scale-90"></i>
      <?php if ($isReports): ?>
        <span class="absolute -bottom-1 w-1 h-1 bg-blue-600 rounded-full"></span>
      <?php endif; ?>
    </div>
    <span class="text-[10px] mt-1 tracking-tight">Report</span>
  </a>

</div>
