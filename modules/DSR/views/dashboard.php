<?php $pageTitle = 'ড্যাশবোর্ড'; ?>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap');
  .font-siliguri {
    font-family: 'Hind Siliguri', 'Inter', sans-serif;
  }
</style>

<div class="p-3 sm:p-5 space-y-5 pb-28 max-w-5xl mx-auto font-siliguri text-slate-800">

  <!-- 1. Top Header Profile Bar -->
  <div class="bg-white/95 backdrop-blur-md px-4 py-3 sm:px-5 sm:py-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between gap-3 transition-all">
    <div class="flex items-center gap-3">
      <div class="relative shrink-0 select-none">
        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-slate-50 border border-slate-200 flex items-center justify-center font-black text-slate-700 text-sm sm:text-base shadow-inner">
          <?= Helpers::initials(Auth::name()) ?>
        </div>
        <span class="w-3 h-3 bg-emerald-500 border-2 border-white rounded-full absolute bottom-0 right-0 shadow-xs"></span>
      </div>
      <div>
        <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-50 text-[9px] font-black text-emerald-700 uppercase tracking-wide border border-emerald-100">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
          ডেলিভারি একটিভ
        </div>
        <h2 class="text-base sm:text-lg font-black text-slate-900 leading-tight mt-1"><?= h(Auth::name()) ?></h2>
      </div>
    </div>

    <!-- DSR Logout Button -->
    <a href="<?= url('dsr/logout') ?>" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 text-xs font-bold shadow-3xs hover:bg-rose-100 transition duration-150 active:scale-95" title="লগআউট করুন">
      <i class="fa-solid fa-right-from-bracket text-xs"></i>
      <span>লগআউট</span>
    </a>
  </div>

  <!-- 2. Performance & Delivery Metrics (Excel Style Table) -->
  <?php
    $completed = $stats['completed_deliveries'];
    $total = $stats['todays_deliveries'];
    $deliveryPct = $total > 0 ? min(100, round(($completed / $total) * 100)) : 0;
    $todayRate = min(100, max(0, $stats['today_rate']));
    $avgRate = min(100, max(0, $stats['avg_rate']));
  ?>
  <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm font-mono text-xs">
    <table class="w-full text-left border-collapse">
      <thead>
        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold text-[10px] uppercase tracking-wider select-none">
          <th class="p-3 border-r border-slate-200 font-sans">মেট্রিক বিবরণ</th>
          <th class="p-3 text-center border-r border-slate-200 font-sans w-[25%]">অগ্রগতি</th>
          <th class="p-3 text-center font-sans w-[35%]">স্ট্যাটাস</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-200 text-slate-800">
        <tr class="hover:bg-slate-50/50 transition">
          <td class="p-3 font-sans font-semibold border-r border-slate-200 text-slate-700 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-blue-500 shrink-0 shadow-2xs"></span>
            আজকের ডেলিভারি সম্পন্ন
          </td>
          <td class="p-3 text-center font-black border-r border-slate-200 text-slate-900 text-xs font-mono">
            <?= $completed ?> / <?= $total ?>
          </td>
          <td class="p-3 text-center font-sans">
            <div class="flex flex-col items-center gap-1">
              <span class="inline-block text-[10px] px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-bold border border-blue-100">
                <?= $deliveryPct ?>% সম্পন্ন
              </span>
              <div class="w-16 h-1 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-blue-500 rounded-full" style="width: <?= $deliveryPct ?>%"></div>
              </div>
            </div>
          </td>
        </tr>
        <tr class="hover:bg-slate-50/50 transition">
          <td class="p-3 font-sans font-semibold border-r border-slate-200 text-slate-700 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 shrink-0 shadow-2xs"></span>
            আজকের সাকসেস হার
          </td>
          <td class="p-3 text-center font-black border-r border-slate-200 text-slate-900 text-xs font-mono">
            <?= $todayRate ?>%
          </td>
          <td class="p-3 text-center font-sans">
            <div class="flex flex-col items-center gap-1">
              <span class="inline-block text-[10px] px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 font-bold border border-indigo-100">
                <?= $todayRate ?>% হার
              </span>
              <div class="w-16 h-1 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-indigo-500 rounded-full" style="width: <?= $todayRate ?>%"></div>
              </div>
            </div>
          </td>
        </tr>
        <tr class="hover:bg-slate-50/50 transition">
          <td class="p-3 font-sans font-semibold border-r border-slate-200 text-slate-700 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-purple-500 shrink-0 shadow-2xs"></span>
            গড় ডেলিভারি হার
          </td>
          <td class="p-3 text-center font-black border-r border-slate-200 text-slate-900 text-xs font-mono">
            <?= $avgRate ?>%
          </td>
          <td class="p-3 text-center font-sans">
            <div class="flex flex-col items-center gap-1">
              <span class="inline-block text-[10px] px-2 py-0.5 rounded bg-purple-50 text-purple-700 font-bold border border-purple-100">
                <?= $avgRate ?>% হার
              </span>
              <div class="w-16 h-1 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-purple-500 rounded-full" style="width: <?= $avgRate ?>%"></div>
              </div>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>



  <!-- 4. Quick Actions Menu (Excel Style Table) -->
  <div class="space-y-2">
    <div class="text-[10px] font-extrabold text-slate-400 tracking-wider uppercase px-1">কুইক অ্যাকশন মেনু</div>
    
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm font-sans text-xs">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold text-[10px] uppercase tracking-wider select-none">
            <th class="p-3 border-r border-slate-200">মেনু আইটেম</th>
            <th class="p-3 text-center">অ্যাকশন</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 text-slate-700">
          <!-- Item 1: Delivery Route -->
          <tr class="hover:bg-slate-50/50 transition cursor-pointer group" onclick="window.location.href='<?= url('dsr/delivery') ?>'">
            <td class="p-3 border-r border-slate-200 font-bold text-slate-800 flex items-center gap-2.5">
              <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center text-xs shrink-0 group-hover:scale-105 transition">
                <i class="fa-solid fa-truck-fast"></i>
              </div>
              <span>ডেলিভারি রুট</span>
            </td>
            <td class="p-3 text-center">
              <span class="inline-block text-[10px] px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 font-bold border border-blue-100 group-hover:bg-blue-600 group-hover:text-white transition duration-200 font-sans">
                প্রবেশ করুন
              </span>
            </td>
          </tr>

          <!-- Item 2: Van Stock -->
          <tr class="hover:bg-slate-50/50 transition cursor-pointer group" onclick="window.location.href='<?= url('dsr/van-stock') ?>'">
            <td class="p-3 border-r border-slate-200 font-bold text-slate-800 flex items-center gap-2.5">
              <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 border border-indigo-100 flex items-center justify-center text-xs shrink-0 group-hover:scale-105 transition">
                <i class="fa-solid fa-boxes-stacked"></i>
              </div>
              <span>মাল (স্টক তালিকা)</span>
            </td>
            <td class="p-3 text-center">
              <span class="inline-block text-[10px] px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-bold border border-indigo-100 group-hover:bg-indigo-600 group-hover:text-white transition duration-200 font-sans">
                প্রবেশ করুন
              </span>
            </td>
          </tr>

          <!-- Item 3: Settlement -->
          <?php if (!empty($stats['can_settle'])): ?>
            <tr class="hover:bg-slate-50/50 transition cursor-pointer group" onclick="window.location.href='<?= url('dsr/settlement') ?>'">
              <td class="p-3 border-r border-slate-200 font-bold text-slate-800 flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center text-xs shrink-0 group-hover:scale-105 transition relative">
                  <?php if (!empty($stats['pending_settlement'])): ?>
                    <span class="absolute top-0 right-0 w-2 h-2 bg-rose-500 rounded-full animate-ping"></span>
                  <?php endif; ?>
                  <i class="fa-solid fa-file-invoice-dollar"></i>
                </div>
                <span>হিসাব মিলাও</span>
              </td>
              <td class="p-3 text-center">
                <span class="inline-block text-[10px] px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-bold border border-emerald-100 group-hover:bg-emerald-600 group-hover:text-white transition duration-200 font-sans">
                  প্রবেশ করুন
                </span>
              </td>
            </tr>
          <?php else: ?>
            <tr class="opacity-60 cursor-not-allowed bg-slate-50/20" onclick="alert('ডেলিভারি স্ট্যাটাস রিটার্ন হওয়ার পর হিসাব মিলাতে পারবেন।')">
              <td class="p-3 border-r border-slate-200 font-bold text-slate-500 flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-400 flex items-center justify-center text-xs shrink-0">
                  <i class="fa-solid fa-file-invoice-dollar"></i>
                </div>
                <span>হিসাব মিলাও</span>
              </td>
              <td class="p-3 text-center">
                <span class="inline-block text-[10px] px-2.5 py-1 rounded-lg bg-slate-150 text-slate-450 border border-slate-200 font-bold font-sans">
                  লকড
                </span>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
