<?php $pageTitle = 'ড্যাশবোর্ড'; ?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap');
.font-siliguri {
  font-family: 'Hind Siliguri', 'Inter', sans-serif;
}
</style>

<div class="p-3 sm:p-5 space-y-4 pb-28 max-w-5xl mx-auto font-siliguri text-slate-800">

  <!-- 1. Top Header Profile & Status Bar -->
  <div class="bg-white/95 backdrop-blur-md px-4 py-3 sm:px-5 sm:py-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between gap-3">
    <div class="flex items-center gap-3">
      <div class="relative shrink-0 select-none">
        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-blue-50 border border-blue-200 flex items-center justify-center font-black text-blue-700 text-sm sm:text-base shadow-inner">
          <?= Helpers::initials(Auth::name()) ?>
        </div>
        <span class="w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full absolute bottom-0 right-0 shadow-2xs"></span>
      </div>
      <div>
        <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 text-[10px] font-bold text-emerald-700 uppercase tracking-wide border border-emerald-100">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
          ফিল্ড একটিভ
        </div>
        <h2 class="text-base sm:text-lg font-black text-slate-900 leading-tight mt-0.5"><?= h(Auth::name()) ?></h2>
        <div class="text-[11px] text-slate-400 font-medium"><i class="fa-regular fa-calendar mr-1"></i><?= date('d M, Y') ?></div>
      </div>
    </div>

    <!-- SR Logout Button -->
    <a href="<?= url('sr/logout') ?>" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 text-xs font-bold shadow-2xs hover:bg-rose-100 transition active:scale-95" title="লগআউট করুন">
      <i class="fa-solid fa-right-from-bracket text-xs"></i>
      <span class="hidden sm:inline">লগআউট</span>
    </a>
  </div>

  <!-- 2. Quick Action Launcher Bar -->
  <div class="grid grid-cols-3 gap-2 sm:gap-3">
    <a href="<?= url('sr/sales') ?>" class="bg-blue-600 hover:bg-blue-700 text-white p-3 rounded-2xl shadow-md flex flex-col items-center justify-center text-center transition active:scale-95 group">
      <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-lg mb-1 group-hover:scale-110 transition">
        <i class="fa-solid fa-cart-plus"></i>
      </div>
      <span class="text-xs font-bold leading-tight">নতুন অর্ডার</span>
    </a>

    <a href="<?= url('sr/sales') ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white p-3 rounded-2xl shadow-md flex flex-col items-center justify-center text-center transition active:scale-95 group">
      <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-lg mb-1 group-hover:scale-110 transition">
        <i class="fa-solid fa-map-location-dot"></i>
      </div>
      <span class="text-xs font-bold leading-tight">রুট ম্যাপ</span>
    </a>

    <a href="<?= url('sr/orders') ?>" class="bg-slate-800 hover:bg-slate-900 text-white p-3 rounded-2xl shadow-md flex flex-col items-center justify-center text-center transition active:scale-95 group">
      <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-lg mb-1 group-hover:scale-110 transition">
        <i class="fa-solid fa-file-invoice"></i>
      </div>
      <span class="text-xs font-bold leading-tight">আজকের অর্ডার</span>
    </a>
  </div>

  <!-- 3. Excel Formula Summary Ribbon (fx) -->
  <div class="excel-container shadow-sm border border-slate-300">
    <div class="excel-ribbon py-2 px-3 justify-between">
      <div class="excel-ribbon-badge py-0.5 px-2 text-xs font-bold">
        <i class="fa-solid fa-file-excel text-blue-200"></i>
        <span>সেলস সমারি স্প্রেডশীট</span>
      </div>
      <span class="text-[11px] text-blue-100 font-mono">LIVE METRICS</span>
    </div>

    <div class="excel-formula-bar py-2 px-3 gap-2 text-xs bg-slate-50 border-b border-slate-300 flex-wrap">
      <span class="fx-symbol text-xs">fx</span>
      <div class="excel-pill">
        <span class="text-slate-500">আজকের সেলস:</span>
        <strong class="text-blue-700 font-mono">৳<?= number_format($stats['today_sales']) ?></strong>
      </div>
      <div class="excel-pill">
        <span class="text-slate-500">আজকের ভিজিট:</span>
        <strong class="text-emerald-700 font-mono"><?= $stats['visited_today'] ?> / <?= $stats['total_retailers'] ?></strong>
      </div>
      <div class="excel-pill">
        <span class="text-slate-500">পেন্ডিং অর্ডার:</span>
        <strong class="text-amber-700 font-mono"><?= $stats['pending_orders'] ?></strong>
      </div>
      <div class="excel-pill">
        <span class="text-slate-500">চলতি মাস সেলস:</span>
        <strong class="text-purple-700 font-mono">৳<?= number_format($stats['this_month_sales']) ?></strong>
      </div>
    </div>
  </div>

  <!-- 4. Monthly Target & Delivery Metrics Grid (Excel Table) -->
  <?php
    $target = $stats['target_amount'] > 0 ? $stats['target_amount'] : 0;
    $sales = $stats['this_month_sales'];
    $delivery = $stats['this_month_delivery'] ?? 0;
    $targetPct = $target > 0 ? min(100, round(($sales / $target) * 100)) : 0;
    $deliveryPct = $target > 0 ? min(100, round(($delivery / $target) * 100)) : ($sales > 0 ? min(100, round(($delivery / $sales) * 100)) : 0);
  ?>
  <div class="excel-container shadow-sm border border-slate-300">
    <div class="bg-slate-100 px-3 py-1.5 border-b border-slate-300 text-xs font-bold text-slate-800 flex justify-between items-center">
      <span>মাসিক লক্ষ্যমাত্রা ও ডেলিভারি পারফরম্যান্স</span>
      <span class="text-[10px] text-slate-500 font-mono">TARGET SHEET</span>
    </div>

    <div class="overflow-x-auto">
      <table class="excel-table text-xs">
        <thead>
          <tr>
            <th class="excel-row-num">#</th>
            <th>মেট্রিক বিবরণ</th>
            <th class="text-right">পরিমাণ (৳)</th>
            <th class="text-center w-36 sm:w-44">অর্জিত শতাংশ</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="excel-row-num">1</td>
            <td class="font-bold text-slate-800 flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-blue-500 shrink-0"></span>
              মাসিক টার্গেট সেলস
            </td>
            <td class="excel-money text-slate-900 font-bold">৳ <?= number_format($target) ?></td>
            <td class="p-2 text-center">
              <div class="flex flex-col items-center gap-1">
                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200">
                  <?= $targetPct ?>% অর্জিত
                </span>
                <div class="w-full bg-slate-200 h-1.5 rounded-full overflow-hidden">
                  <div class="bg-blue-600 h-full rounded-full" style="width: <?= $targetPct ?>%"></div>
                </div>
              </div>
            </td>
          </tr>
          <tr>
            <td class="excel-row-num">2</td>
            <td class="font-bold text-slate-800 flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
              ডেলিভারি সম্পন্ন (Delivered)
            </td>
            <td class="excel-money text-emerald-700 font-bold">৳ <?= number_format($delivery) ?></td>
            <td class="p-2 text-center">
              <div class="flex flex-col items-center gap-1">
                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">
                  <?= $deliveryPct ?>% ডেলিভার্ড
                </span>
                <div class="w-full bg-slate-200 h-1.5 rounded-full overflow-hidden">
                  <div class="bg-emerald-600 h-full rounded-full" style="width: <?= $deliveryPct ?>%"></div>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- 5. Recent Orders Excel Table Grid -->
  <div class="excel-container shadow-sm border border-slate-300">
    <div class="bg-slate-100 px-3 py-2 border-b border-slate-300 flex justify-between items-center text-xs font-bold text-slate-800">
      <span class="flex items-center gap-1.5"><i class="fa-solid fa-clock-rotate-left text-blue-600"></i> সাম্প্রতিক অর্ডারসমূহ</span>
      <a href="<?= url('sr/orders') ?>" class="text-[11px] font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1">
        সকল অর্ডার <i class="fa-solid fa-arrow-right text-[10px]"></i>
      </a>
    </div>

    <div class="overflow-x-auto">
      <table class="excel-table text-xs">
        <thead>
          <tr>
            <th class="excel-row-num">#</th>
            <th>অর্ডার নং</th>
            <th>দোকান / ডিলার</th>
            <th class="text-right">মূল্য (৳)</th>
            <th class="text-center">স্ট্যাটাস</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recentOrders)): ?>
          <tr>
            <td colspan="5" class="p-6 text-center text-slate-400">কোনো সাম্প্রতিক অর্ডার নেই।</td>
          </tr>
          <?php else: ?>
          <?php foreach (array_slice($recentOrders, 0, 5) as $idx => $ord): ?>
          <tr class="hover:bg-blue-50/40 transition cursor-pointer" onclick="window.location.href='<?= url('sr/orders') ?>'">
            <td class="excel-row-num"><?= $idx + 1 ?></td>
            <td class="font-mono font-bold text-slate-900">#<?= htmlspecialchars($ord['order_no'] ?? $ord['id']) ?></td>
            <td class="font-bold text-slate-800"><?= htmlspecialchars($ord['dealer_name'] ?? 'General Store') ?></td>
            <td class="excel-money text-blue-700">৳ <?= number_format($ord['total_amount'], 2) ?></td>
            <td class="text-center">
              <?php 
                $st = $ord['status'] ?? 'pending';
                $badgeClass = $st === 'confirmed' || $st === 'delivered' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($st === 'cancelled' ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-amber-50 text-amber-700 border-amber-200');
              ?>
              <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border <?= $badgeClass ?> uppercase">
                <?= strtoupper($st) ?>
              </span>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- 6. Sales Menu Grid -->
  <div class="space-y-1.5">
    <div class="text-[10px] font-extrabold text-slate-400 tracking-wider uppercase px-1">সেলস মেনু</div>
    
    <div class="grid grid-cols-3 gap-2 sm:gap-3">
      <a href="<?= url('sr/retailers') ?>" class="bg-white p-3 rounded-xl border border-slate-200 shadow-2xs hover:border-blue-300 transition flex flex-col items-center text-center group active:scale-95">
        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs mb-1 group-hover:scale-110 transition">
          <i class="fa-solid fa-store"></i>
        </div>
        <span class="text-xs font-bold text-slate-800">দোকান তালিকা</span>
      </a>

      <a href="<?= url('sr/orders') ?>" class="bg-white p-3 rounded-xl border border-slate-200 shadow-2xs hover:border-amber-300 transition flex flex-col items-center text-center group active:scale-95">
        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs mb-1 group-hover:scale-110 transition">
          <i class="fa-solid fa-file-invoice"></i>
        </div>
        <span class="text-xs font-bold text-slate-800">অর্ডার ইতিহাস</span>
      </a>

      <a href="<?= url('sr/transactions') ?>" class="bg-white p-3 rounded-xl border border-slate-200 shadow-2xs hover:border-purple-300 transition flex flex-col items-center text-center group active:scale-95">
        <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-xs mb-1 group-hover:scale-110 transition">
          <i class="fa-solid fa-chart-pie"></i>
        </div>
        <span class="text-xs font-bold text-slate-800">সেলস সামারি</span>
      </a>
    </div>
  </div>

</div>
