<?php
/**
 * Excel-Style Sheet Tab Bottom Navigation Bar for DSR Mobile App
 */
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';

$isHome       = str_contains($uri, '/dsr/dashboard') || $uri === '/dsr' || $uri === '/dsr/';
$isDelivery   = str_contains($uri, '/dsr/delivery');
$isStock      = str_contains($uri, '/dsr/van-stock');
$isSettlement = str_contains($uri, '/dsr/settlement');
$isExpenses   = str_contains($uri, '/dsr/expenses');
 
$dsr_can_settle = true;
if (class_exists('Database') && class_exists('Auth') && Auth::check()) {
    $db = Database::getInstance();
    $q = $db->prepare("SELECT COUNT(*) FROM dispatch_schedules WHERE dsr_id=? AND (delivery_date=CURDATE() OR (delivery_date IS NULL AND dispatch_date=CURDATE())) AND status != 'returned'");
    $q->execute([Auth::id()]);
    $dsr_can_settle = ($q->fetchColumn() == 0);
}
?>

<!-- ── Excel-Style Bottom Sheet Tabs Nav for DSR Panel ───────────────────────── -->
<div class="fixed bottom-0 left-0 right-0 w-full bg-slate-100 border-t border-slate-300 flex items-stretch h-[65px] z-50 select-none shadow-md" style="font-family: 'Hind Siliguri', 'Inter', sans-serif;">
  
  <!-- Tab 1: Home -->
  <a href="<?= url('dsr/dashboard') ?>" 
     class="flex-1 flex flex-col items-center justify-center gap-1 border-r border-slate-200 transition-all pt-1 pb-1 <?= $isHome ? 'bg-white border-t-[3.5px] border-blue-600 text-blue-600 font-extrabold shadow-2xs' : 'bg-slate-50 text-slate-500 hover:bg-slate-100 border-t-[3.5px] border-transparent' ?>">
    <i class="fa-solid fa-house text-lg <?= $isHome ? 'scale-110' : '' ?> transition duration-200"></i>
    <span class="text-[12px] tracking-tight font-bold">হোম</span>
  </a>

  <!-- Tab 2: Delivery -->
  <a href="<?= url('dsr/delivery') ?>" 
     class="flex-1 flex flex-col items-center justify-center gap-1 border-r border-slate-200 transition-all pt-1 pb-1 <?= $isDelivery ? 'bg-white border-t-[3.5px] border-blue-600 text-blue-600 font-extrabold shadow-2xs' : 'bg-slate-50 text-slate-500 hover:bg-slate-100 border-t-[3.5px] border-transparent' ?>">
    <i class="fa-solid fa-truck-fast text-lg <?= $isDelivery ? 'scale-110' : '' ?> transition duration-200"></i>
    <span class="text-[12px] tracking-tight font-bold">ডেলিভারি</span>
  </a>

  <!-- Tab 3: Stock -->
  <a href="<?= url('dsr/van-stock') ?>" 
     class="flex-1 flex flex-col items-center justify-center gap-1 border-r border-slate-200 transition-all pt-1 pb-1 <?= $isStock ? 'bg-white border-t-[3.5px] border-blue-600 text-blue-600 font-extrabold shadow-2xs' : 'bg-slate-50 text-slate-500 hover:bg-slate-100 border-t-[3.5px] border-transparent' ?>">
    <i class="fa-solid fa-boxes-stacked text-lg <?= $isStock ? 'scale-110' : '' ?> transition duration-200"></i>
    <span class="text-[12px] tracking-tight font-bold">স্টক</span>
  </a>

  <!-- Tab 4: Settlement -->
  <?php if ($dsr_can_settle): ?>
  <a href="<?= url('dsr/settlement') ?>" 
     class="flex-1 flex flex-col items-center justify-center gap-1 border-r border-slate-200 transition-all pt-1 pb-1 <?= $isSettlement ? 'bg-white border-t-[3.5px] border-blue-600 text-blue-600 font-extrabold shadow-2xs' : 'bg-slate-50 text-slate-500 hover:bg-slate-100 border-t-[3.5px] border-transparent' ?>">
    <i class="fa-solid fa-file-invoice-dollar text-lg <?= $isSettlement ? 'scale-110' : '' ?> transition duration-200"></i>
    <span class="text-[12px] tracking-tight font-bold">হিসাব</span>
  </a>
  <?php else: ?>
  <div onclick="alert('ডেলিভারি স্ট্যাটাস রিটার্ন হওয়ার পর হিসাব মিলাতে পারবেন।')" 
       class="flex-1 flex flex-col items-center justify-center gap-1 border-r border-slate-200 transition-all pt-1 pb-1 bg-slate-50 text-slate-400 opacity-60 cursor-pointer border-t-[3.5px] border-transparent">
    <i class="fa-solid fa-file-invoice-dollar text-lg"></i>
    <span class="text-[12px] tracking-tight font-bold">হিসাব</span>
  </div>
  <?php endif; ?>

  <!-- Tab 5: Expenses -->
  <a href="<?= url('dsr/expenses') ?>" 
     class="flex-1 flex flex-col items-center justify-center gap-1 transition-all pt-1 pb-1 <?= $isExpenses ? 'bg-white border-t-[3.5px] border-blue-600 text-blue-600 font-extrabold shadow-2xs' : 'bg-slate-50 text-slate-500 hover:bg-slate-100 border-t-[3.5px] border-transparent' ?>">
    <i class="fa-solid fa-receipt text-lg <?= $isExpenses ? 'scale-110' : '' ?> transition duration-200"></i>
    <span class="text-[12px] tracking-tight font-bold">খরচ</span>
  </a>

</div>
