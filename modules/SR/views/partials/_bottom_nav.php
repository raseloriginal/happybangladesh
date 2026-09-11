<?php
/**
 * Excel-Style Sheet Tab Bottom Navigation Bar for SR Mobile App
 */
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';

$isHome    = str_contains($uri, '/sr/dashboard') || $uri === '/sr' || $uri === '/sr/';
$isSales   = str_contains($uri, '/sr/sales');
$isOrders  = str_contains($uri, '/sr/orders');
?>

<!-- ── Excel-Style Bottom Sheet Tabs Nav ────────────────────────────────────── -->
<div class="fixed bottom-0 left-0 right-0 w-full bg-slate-100 border-t border-slate-300 flex items-stretch h-[65px] z-50 select-none shadow-md" style="font-family: 'Hind Siliguri', sans-serif;">
  
  <!-- Tab 1: Home -->
  <a href="<?= url('sr/dashboard') ?>" 
     class="flex-1 flex flex-col items-center justify-center gap-1.5 border-r border-slate-200 transition-all pt-1 pb-1.5 <?= $isHome ? 'bg-white border-t-[3.5px] border-blue-600 text-blue-600 font-extrabold shadow-2xs' : 'bg-slate-50 text-slate-500 hover:bg-slate-100 border-t-[3.5px] border-transparent' ?>">
    <i class="fa-solid fa-house text-xl <?= $isHome ? 'scale-110' : 'group-hover:scale-105' ?> transition duration-200"></i>
    <span class="text-[13px] tracking-tight font-bold">হোম</span>
  </a>

  <!-- Tab 2: Sales (Map) -->
  <a href="<?= url('sr/sales') ?>" 
     class="flex-1 flex flex-col items-center justify-center gap-1.5 border-r border-slate-200 transition-all pt-1 pb-1.5 <?= $isSales ? 'bg-white border-t-[3.5px] border-blue-600 text-blue-600 font-extrabold shadow-2xs' : 'bg-slate-50 text-slate-500 hover:bg-slate-100 border-t-[3.5px] border-transparent' ?>">
    <i class="fa-solid fa-map-location-dot text-xl <?= $isSales ? 'scale-110' : 'group-hover:scale-105' ?> transition duration-200"></i>
    <span class="text-[13px] tracking-tight font-bold">ম্যাপ</span>
  </a>

  <!-- Tab 3: Orders -->
  <a href="<?= url('sr/orders') ?>" 
     class="flex-1 flex flex-col items-center justify-center gap-1.5 transition-all pt-1 pb-1.5 <?= $isOrders ? 'bg-white border-t-[3.5px] border-blue-600 text-blue-600 font-extrabold shadow-2xs' : 'bg-slate-50 text-slate-500 hover:bg-slate-100 border-t-[3.5px] border-transparent' ?>">
    <i class="fa-solid fa-file-invoice text-xl <?= $isOrders ? 'scale-110' : 'group-hover:scale-105' ?> transition duration-200"></i>
    <span class="text-[13px] tracking-tight font-bold">অর্ডার</span>
  </a>

</div>
