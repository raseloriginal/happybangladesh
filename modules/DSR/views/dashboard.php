<?php $pageTitle = 'ড্যাশবোর্ড'; ?>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap');
  .font-siliguri {
    font-family: 'Hind Siliguri', 'Inter', sans-serif;
  }
</style>

<div class="p-3 sm:p-5 space-y-5 pb-28 max-w-5xl mx-auto font-siliguri text-slate-900">

  <!-- 1. Clean Top Header Profile Bar -->
  <div class="bg-white px-4 py-3 sm:px-5 sm:py-4 rounded-2xl border border-slate-200/90 shadow-2xs flex items-center justify-between gap-3">
    <div class="flex items-center gap-3">
      <div class="relative shrink-0 select-none">
        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl bg-blue-50 border border-blue-200 text-blue-600 flex items-center justify-center font-black text-sm sm:text-base shadow-2xs">
          <?= Helpers::initials(Auth::name()) ?>
        </div>
        <span class="w-3 h-3 bg-emerald-500 border-2 border-white rounded-full absolute -bottom-0.5 -right-0.5 shadow-2xs"></span>
      </div>
      <div>
        <h2 class="text-base sm:text-lg font-black text-slate-900 leading-tight mt-0.5"><?= h(Auth::name()) ?></h2>
      </div>
    </div>

    <!-- DSR Logout Button -->
    <a href="<?= url('dsr/logout') ?>" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 text-xs font-bold hover:bg-rose-600 hover:text-white transition duration-200 active:scale-95 shadow-2xs" title="লগআউট করুন">
      <i class="fa-solid fa-right-from-bracket text-xs"></i>
      <span>লগআউট</span>
    </a>
  </div>

  <!-- 2. Performance Metrics Summary (Minimal Excel Spreadsheet Table) -->
  <?php
    $completed = $stats['completed_deliveries'];
    $total = $stats['todays_deliveries'];
    $deliveryPct = $total > 0 ? min(100, round(($completed / $total) * 100)) : 0;
    $todayRate = min(100, max(0, $stats['today_rate']));
    $avgRate = min(100, max(0, $stats['avg_rate']));
  ?>
  <div class="space-y-2">
    <div class="text-[11px] font-black text-slate-400 tracking-wider uppercase px-1">আজকের ডেলিভারি মেট্রিক্স (Excel View)</div>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xs font-mono text-xs">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-black text-[10.5px] uppercase tracking-wider select-none">
            <th class="p-3 border-r border-slate-200 font-sans">মেট্রিক বিবরণ</th>
            <th class="p-3 text-center border-r border-slate-200 font-sans w-[25%]">সংখ্যা</th>
            <th class="p-3 text-center font-sans w-[35%]">অগ্রগতি স্ট্যাটাস</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-800">
          <tr class="hover:bg-blue-50/30 transition">
            <td class="p-3 font-sans font-bold border-r border-slate-100 text-slate-800 flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-blue-600 shrink-0"></span>
              ডেলিভারি সম্পন্ন (আজ)
            </td>
            <td class="p-3 text-center font-black border-r border-slate-100 text-slate-900 text-xs font-mono">
              <?= $completed ?> / <?= $total ?>
            </td>
            <td class="p-3 text-center font-sans">
              <div class="flex flex-col items-center gap-1">
                <span class="inline-block text-[10px] px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 font-black border border-blue-200">
                  <?= $deliveryPct ?>% সম্পন্ন
                </span>
                <div class="w-20 bg-slate-100 h-1.5 rounded-full overflow-hidden">
                  <div class="h-full bg-blue-600 rounded-full" style="width: <?= $deliveryPct ?>%"></div>
                </div>
              </div>
            </td>
          </tr>
          <tr class="hover:bg-indigo-50/30 transition">
            <td class="p-3 font-sans font-bold border-r border-slate-100 text-slate-800 flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 shrink-0"></span>
              আজকের সাকসেস রেট
            </td>
            <td class="p-3 text-center font-black border-r border-slate-100 text-slate-900 text-xs font-mono">
              <?= $todayRate ?>%
            </td>
            <td class="p-3 text-center font-sans">
              <div class="flex flex-col items-center gap-1">
                <span class="inline-block text-[10px] px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 font-black border border-indigo-200">
                  <?= $todayRate ?>% হার
                </span>
                <div class="w-20 bg-slate-100 h-1.5 rounded-full overflow-hidden">
                  <div class="h-full bg-indigo-600 rounded-full" style="width: <?= $todayRate ?>%"></div>
                </div>
              </div>
            </td>
          </tr>
          <tr class="hover:bg-purple-50/30 transition">
            <td class="p-3 font-sans font-bold border-r border-slate-100 text-slate-800 flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-purple-600 shrink-0"></span>
              গড় পারফরম্যান্স হার
            </td>
            <td class="p-3 text-center font-black border-r border-slate-100 text-slate-900 text-xs font-mono">
              <?= $avgRate ?>%
            </td>
            <td class="p-3 text-center font-sans">
              <div class="flex flex-col items-center gap-1">
                <span class="inline-block text-[10px] px-2 py-0.5 rounded-full bg-purple-50 text-purple-700 font-black border border-purple-200">
                  <?= $avgRate ?>% হার
                </span>
                <div class="w-20 bg-slate-100 h-1.5 rounded-full overflow-hidden">
                  <div class="h-full bg-purple-600 rounded-full" style="width: <?= $avgRate ?>%"></div>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- 3. Quick Actions Menu Cards (Clean Modern Grid) -->
  <div class="space-y-3 pt-1">
    <div class="flex items-center justify-between px-1">
      <div class="text-[11px] font-black text-slate-400 tracking-wider uppercase">কুইক অ্যাকশন মেনু</div>
      <span class="text-[10px] font-bold text-slate-400">৫ টি বিষয়বস্তু</span>
    </div>
    
    <div class="grid grid-cols-2 gap-3">
      <!-- Card 1: Delivery Route -->
      <a href="<?= url('dsr/delivery') ?>" class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs hover:shadow-md hover:-translate-y-0.5 hover:border-blue-400 transition-all duration-200 flex flex-col justify-between space-y-4 group">
        <div class="flex items-center justify-between">
          <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-600 border border-blue-200 flex items-center justify-center text-xl group-hover:bg-blue-600 group-hover:text-white transition duration-200 shadow-2xs">
            <i class="fa-solid fa-truck-fast"></i>
          </div>
          <span class="w-7 h-7 rounded-full bg-slate-100 group-hover:bg-blue-600 group-hover:text-white flex items-center justify-center text-slate-400 text-xs transition duration-200">
            <i class="fa-solid fa-arrow-right -rotate-45 group-hover:rotate-0 transition-transform duration-200"></i>
          </span>
        </div>
        <div>
          <h3 class="font-extrabold text-xs sm:text-sm text-slate-900 group-hover:text-blue-600 transition">ডেলিভারি রুট</h3>
          <p class="text-[10px] text-slate-500 font-medium mt-0.5">আজকের সব অর্ডার বিতরণ করুন</p>
        </div>
      </a>

      <!-- Card 2: Van Stock -->
      <a href="<?= url('dsr/van-stock') ?>" class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs hover:shadow-md hover:-translate-y-0.5 hover:border-indigo-400 transition-all duration-200 flex flex-col justify-between space-y-4 group">
        <div class="flex items-center justify-between">
          <div class="w-11 h-11 rounded-2xl bg-indigo-50 text-indigo-600 border border-indigo-200 flex items-center justify-center text-xl group-hover:bg-indigo-600 group-hover:text-white transition duration-200 shadow-2xs">
            <i class="fa-solid fa-boxes-stacked"></i>
          </div>
          <span class="w-7 h-7 rounded-full bg-slate-100 group-hover:bg-indigo-600 group-hover:text-white flex items-center justify-center text-slate-400 text-xs transition duration-200">
            <i class="fa-solid fa-arrow-right -rotate-45 group-hover:rotate-0 transition-transform duration-200"></i>
          </span>
        </div>
        <div>
          <h3 class="font-extrabold text-xs sm:text-sm text-slate-900 group-hover:text-indigo-600 transition">মাল (স্টক তালিকা)</h3>
          <p class="text-[10px] text-slate-500 font-medium mt-0.5">ভ্যান স্টকের হিসাব বিবরণী</p>
        </div>
      </a>

      <!-- Card 3: Expenses (আজকের খরচ) -->
      <a href="<?= url('dsr/expenses') ?>" class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs hover:shadow-md hover:-translate-y-0.5 hover:border-amber-400 transition-all duration-200 flex flex-col justify-between space-y-4 group">
        <div class="flex items-center justify-between">
          <div class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center text-xl group-hover:bg-amber-600 group-hover:text-white transition duration-200 shadow-2xs">
            <i class="fa-solid fa-receipt"></i>
          </div>
          <span class="w-7 h-7 rounded-full bg-slate-100 group-hover:bg-amber-600 group-hover:text-white flex items-center justify-center text-slate-400 text-xs transition duration-200">
            <i class="fa-solid fa-arrow-right -rotate-45 group-hover:rotate-0 transition-transform duration-200"></i>
          </span>
        </div>
        <div>
          <h3 class="font-extrabold text-xs sm:text-sm text-slate-900 group-hover:text-amber-600 transition">আজকের খরচ</h3>
          <p class="text-[10px] text-slate-500 font-medium mt-0.5">দৈনিক যাবতীয় খরচের এনট্রি</p>
        </div>
      </a>

      <!-- Card 4: Settlement -->
      <a href="<?= url('dsr/settlement') ?>" class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs hover:shadow-md hover:-translate-y-0.5 hover:border-emerald-400 transition-all duration-200 flex flex-col justify-between space-y-4 group">
        <div class="flex items-center justify-between">
          <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center text-xl group-hover:bg-emerald-600 group-hover:text-white transition duration-200 shadow-2xs relative">
            <i class="fa-solid fa-file-invoice-dollar"></i>
            <?php if (!empty($stats['pending_settlement'])): ?>
              <span class="absolute -top-1 -right-1 w-3 h-3 bg-rose-500 rounded-full border-2 border-white animate-ping"></span>
            <?php endif; ?>
          </div>
          <span class="w-7 h-7 rounded-full bg-slate-100 group-hover:bg-emerald-600 group-hover:text-white flex items-center justify-center text-slate-400 text-xs transition duration-200">
            <i class="fa-solid fa-arrow-right -rotate-45 group-hover:rotate-0 transition-transform duration-200"></i>
          </span>
        </div>
        <div>
          <h3 class="font-extrabold text-xs sm:text-sm text-slate-900 group-hover:text-emerald-600 transition">হিসাব মিলাও</h3>
          <p class="text-[10px] text-slate-500 font-medium mt-0.5">ক্যাশ নোট সেটেলমেন্ট মিলাও</p>
        </div>
      </a>

      <!-- Card 5: QR Code -->
      <a href="<?= url('dsr/qr-code') ?>" class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs hover:shadow-md hover:-translate-y-0.5 hover:border-purple-400 transition-all duration-200 flex flex-col justify-between space-y-4 group">
        <div class="flex items-center justify-between">
          <div class="w-11 h-11 rounded-2xl bg-purple-50 text-purple-600 border border-purple-200 flex items-center justify-center text-xl group-hover:bg-purple-600 group-hover:text-white transition duration-200 shadow-2xs">
            <i class="fa-solid fa-qrcode"></i>
          </div>
          <span class="w-7 h-7 rounded-full bg-slate-100 group-hover:bg-purple-600 group-hover:text-white flex items-center justify-center text-slate-400 text-xs transition duration-200">
            <i class="fa-solid fa-arrow-right -rotate-45 group-hover:rotate-0 transition-transform duration-200"></i>
          </span>
        </div>
        <div>
          <h3 class="font-extrabold text-xs sm:text-sm text-slate-900 group-hover:text-purple-600 transition">QR কোড</h3>
          <p class="text-[10px] text-slate-500 font-medium mt-0.5">পেমেন্ট QR কোড দেখুন</p>
        </div>
      </a>
    </div>
  </div>

</div>
