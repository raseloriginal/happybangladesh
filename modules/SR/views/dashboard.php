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
          ফিল্ড একটিভ
        </div>
        <h2 class="text-base sm:text-lg font-black text-slate-900 leading-tight mt-1"><?= h(Auth::name()) ?></h2>
      </div>
    </div>

    <!-- SR Logout Button -->
    <a href="<?= url('sr/logout') ?>" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 text-xs font-bold shadow-3xs hover:bg-rose-100 transition duration-150 active:scale-95" title="লগআউট করুন">
      <i class="fa-solid fa-right-from-bracket text-xs"></i>
      <span>লগআউট</span>
    </a>
  </div>

  <!-- 2. Target & Delivery Metrics Grid Cards (Excel Style) -->
  <?php
    $target = $stats['target_amount'] > 0 ? $stats['target_amount'] : 0;
    $sales = $stats['this_month_sales'];
    $delivery = $stats['this_month_delivery'] ?? 0;
    $targetPct = $target > 0 ? min(100, round(($sales / $target) * 100)) : 0;
    $deliveryPct = $target > 0 ? min(100, round(($delivery / $target) * 100)) : ($sales > 0 ? min(100, round(($delivery / $sales) * 100)) : 0);
  ?>
  <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm font-mono text-xs">
    <table class="w-full text-left border-collapse">
      <thead>
        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold text-[10px] uppercase tracking-wider select-none">
          <th class="p-3 border-r border-slate-200 font-sans">মেট্রিক বিবরণ</th>
          <th class="p-3 text-right border-r border-slate-200 font-sans">পরিমাণ</th>
          <th class="p-3 text-center font-sans">অগ্রগতি</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-200 text-slate-800">
        <tr class="hover:bg-slate-50/50 transition">
          <td class="p-3 font-sans font-semibold border-r border-slate-200 text-slate-700 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-blue-500 shrink-0 shadow-2xs"></span>
            মাসিক লক্ষ্যমাত্রা
          </td>
          <td class="p-3 text-right font-black border-r border-slate-200 text-slate-900 text-sm">
            ৳<?= number_format($target) ?>
          </td>
          <td class="p-3 text-center font-sans">
            <div class="flex flex-col items-center gap-1">
              <span class="inline-block text-[10px] px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-bold border border-blue-100">
                <?= $targetPct ?>% অর্জিত
              </span>
              <div class="w-16 h-1 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-blue-500 rounded-full" style="width: <?= $targetPct ?>%"></div>
              </div>
            </div>
          </td>
        </tr>
        <tr class="hover:bg-slate-50/50 transition">
          <td class="p-3 font-sans font-semibold border-r border-slate-200 text-slate-700 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0 shadow-2xs"></span>
            ডেলিভারি সম্পন্ন
          </td>
          <td class="p-3 text-right font-black border-r border-slate-200 text-slate-900 text-sm">
            ৳<?= number_format($delivery) ?>
          </td>
          <td class="p-3 text-center font-sans">
            <div class="flex flex-col items-center gap-1">
              <span class="inline-block text-[10px] px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 font-bold border border-emerald-100">
                <?= $deliveryPct ?>% ডেলিভার্ড
              </span>
              <div class="w-16 h-1 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-emerald-500 rounded-full" style="width: <?= $deliveryPct ?>%"></div>
              </div>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- 3. Sales Menu (Excel Style Table) -->
  <div class="space-y-2">
    <div class="text-[10px] font-extrabold text-slate-400 tracking-wider uppercase px-1">সেলস মেনু</div>
    
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm font-sans text-xs">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold text-[10px] uppercase tracking-wider select-none">
            <th class="p-3 border-r border-slate-200">মেনু আইটেম</th>
            <th class="p-3 text-center">অ্যাকশন</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 text-slate-700">
          <!-- Item 1: Shop List -->
          <tr class="hover:bg-slate-50/50 transition cursor-pointer group" onclick="window.location.href='<?= url('sr/retailers') ?>'">
            <td class="p-3 border-r border-slate-200 font-bold text-slate-800 flex items-center gap-2.5">
              <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center text-xs shrink-0 group-hover:scale-105 transition">
                <i class="fa-solid fa-store"></i>
              </div>
              <span>দোকান তালিকা</span>
            </td>
            <td class="p-3 text-center">
              <span class="inline-block text-[10px] px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 font-bold border border-blue-100 group-hover:bg-blue-600 group-hover:text-white transition duration-200 font-sans">
                প্রবেশ করুন
              </span>
            </td>
          </tr>

          <!-- Item 2: Order History -->
          <tr class="hover:bg-slate-50/50 transition cursor-pointer group" onclick="window.location.href='<?= url('sr/orders') ?>'">
            <td class="p-3 border-r border-slate-200 font-bold text-slate-800 flex items-center gap-2.5">
              <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center text-xs shrink-0 group-hover:scale-105 transition">
                <i class="fa-solid fa-file-invoice"></i>
              </div>
              <span>অর্ডার ইতিহাস</span>
            </td>
            <td class="p-3 text-center">
              <span class="inline-block text-[10px] px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 font-bold border border-amber-100 group-hover:bg-amber-500 group-hover:text-white transition duration-200 font-sans">
                প্রবেশ করুন
              </span>
            </td>
          </tr>

          <!-- Item 3: Transactions -->
          <tr class="hover:bg-slate-50/50 transition cursor-pointer group" onclick="window.location.href='<?= url('sr/transactions') ?>'">
            <td class="p-3 border-r border-slate-200 font-bold text-slate-800 flex items-center gap-2.5">
              <div class="w-7 h-7 rounded-lg bg-purple-50 text-purple-600 border border-purple-100 flex items-center justify-center text-xs shrink-0 group-hover:scale-105 transition">
                <i class="fa-solid fa-money-bill-transfer"></i>
              </div>
              <span>সমারি</span>
            </td>
            <td class="p-3 text-center">
              <span class="inline-block text-[10px] px-2.5 py-1 rounded-lg bg-purple-50 text-purple-700 font-bold border border-purple-100 group-hover:bg-purple-600 group-hover:text-white transition duration-200 font-sans">
                প্রবেশ করুন
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

</div>
