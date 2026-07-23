<?php $pageTitle = 'DSR Dashboard'; ?>

<div class="p-3.5 sm:p-5 space-y-4 pb-28 font-sans">

  <!-- 1. Unified Hero Banner (DSR Theme) -->
  <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 text-white rounded-3xl p-4 sm:p-5 shadow-md space-y-3.5">
    
    <!-- Profile Header Row -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-11 h-11 bg-white/20 rounded-2xl flex items-center justify-center text-white border border-white/30 backdrop-blur-xs shadow-2xs">
          <i class="fa-solid fa-user-tie text-lg"></i>
        </div>
        <div>
          <div class="text-[11px] text-blue-100 font-medium">ডেলিভারি ম্যান (DSR)</div>
          <div class="font-black text-base leading-tight tracking-tight"><?= h(Auth::name()) ?></div>
        </div>
      </div>

      <a href="<?= url('dsr/profile') ?>" class="w-9 h-9 bg-white/20 hover:bg-white/30 rounded-xl flex items-center justify-center text-white backdrop-blur-xs transition" title="প্রোফাইল">
        <i class="fa-solid fa-gear text-sm"></i>
      </a>
    </div>

    <!-- Today's Delivery Tracker -->
    <div class="bg-white/10 backdrop-blur-md rounded-2xl p-3.5 border border-white/20 flex items-center justify-between">
      <div>
        <div class="text-[11px] text-blue-100 font-bold uppercase tracking-wider">আজকের ডেলিভারি অগ্রগতি</div>
        <div class="text-xl font-black text-white font-mono mt-0.5">
          <?= $stats['completed_deliveries'] ?> <span class="text-xs text-blue-200 font-medium">/ <?= $stats['todays_deliveries'] ?> দোকান সম্পন্ন</span>
        </div>
      </div>
      <div class="w-11 h-11 rounded-xl bg-white text-blue-600 flex items-center justify-center shadow-sm">
        <i class="fa-solid fa-truck-fast text-lg"></i>
      </div>
    </div>

  </div>

  <!-- 2. Delivery Completion Rates (%) -->
  <div class="grid grid-cols-2 gap-2.5">
    
    <!-- Today's Rate -->
    <div class="bg-white p-3.5 rounded-2xl border border-slate-200/90 shadow-2xs space-y-2">
      <div class="flex items-center justify-between">
        <span class="text-[11px] font-bold text-slate-700">আজকের সাকসেস হার</span>
        <span class="bg-blue-50 text-blue-700 text-[10px] font-black px-2 py-0.5 rounded-md font-mono"><?= $stats['today_rate'] ?>%</span>
      </div>
      <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
        <div class="bg-blue-600 h-full rounded-full transition-all duration-500" style="width: <?= min(100, max(0, $stats['today_rate'])) ?>%;"></div>
      </div>
      <div class="text-[10px] text-slate-400 font-medium">আজকের মেমো ডেলিভারি রেট</div>
    </div>

    <!-- Average Rate -->
    <div class="bg-white p-3.5 rounded-2xl border border-slate-200/90 shadow-2xs space-y-2">
      <div class="flex items-center justify-between">
        <span class="text-[11px] font-bold text-slate-700">গড় ডেলিভারি হার</span>
        <span class="bg-purple-50 text-purple-700 text-[10px] font-black px-2 py-0.5 rounded-md font-mono"><?= $stats['avg_rate'] ?>%</span>
      </div>
      <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
        <div class="bg-purple-600 h-full rounded-full transition-all duration-500" style="width: <?= min(100, max(0, $stats['avg_rate'])) ?>%;"></div>
      </div>
      <div class="text-[10px] text-slate-400 font-medium">সকল ডেলিভারির গড় হার</div>
    </div>

  </div>

  <!-- 3. Store KPI Summary Cards Grid -->
  <div class="grid grid-cols-2 gap-2.5">
    
    <!-- Ordered Retailers -->
    <div class="bg-white p-3.5 rounded-2xl border border-slate-200/90 shadow-2xs flex flex-col justify-between">
      <div class="flex items-center justify-between">
        <span class="text-[11px] font-bold text-slate-600">মোট দোকান</span>
        <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
          <i class="fa-solid fa-store"></i>
        </div>
      </div>
      <div class="text-2xl font-black text-slate-900 font-mono mt-2"><?= $stats['ordered_retailers'] ?></div>
      <div class="text-[10px] text-slate-400 font-medium mt-0.5">অ্যাসাইন করা কাস্টমার</div>
    </div>

    <!-- Completed Deliveries -->
    <div class="bg-white p-3.5 rounded-2xl border border-slate-200/90 shadow-2xs flex flex-col justify-between">
      <div class="flex items-center justify-between">
        <span class="text-[11px] font-bold text-emerald-700">ডেলিভারি শেষ</span>
        <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
          <i class="fa-solid fa-circle-check"></i>
        </div>
      </div>
      <div class="text-2xl font-black text-emerald-700 font-mono mt-2"><?= $stats['completed_deliveries'] ?></div>
      <div class="text-[10px] text-emerald-600/80 font-medium mt-0.5">মাল দেওয়া ও ক্যাশ শেষ</div>
    </div>

    <!-- Due Deliveries -->
    <div class="bg-white p-3.5 rounded-2xl border border-slate-200/90 shadow-2xs flex flex-col justify-between">
      <div class="flex items-center justify-between">
        <span class="text-[11px] font-bold text-amber-700">ডেলিভারি বাকী</span>
        <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
          <i class="fa-solid fa-clock"></i>
        </div>
      </div>
      <div class="text-2xl font-black text-amber-700 font-mono mt-2"><?= $stats['due_deliveries'] ?></div>
      <div class="text-[10px] text-amber-600/80 font-medium mt-0.5">পেন্ডিং মেমো</div>
    </div>

    <!-- Ready Sales -->
    <div class="bg-white p-3.5 rounded-2xl border border-slate-200/90 shadow-2xs flex flex-col justify-between">
      <div class="flex items-center justify-between">
        <span class="text-[11px] font-bold text-purple-700">ইনস্ট্যান্ট বিক্রি</span>
        <div class="w-7 h-7 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-xs">
          <i class="fa-solid fa-tags"></i>
        </div>
      </div>
      <div class="text-2xl font-black text-purple-700 font-mono mt-2"><?= $stats['ready_sales'] ?></div>
      <div class="text-[10px] text-purple-600/80 font-medium mt-0.5">স্পট স্পট বিক্রি</div>
    </div>

  </div>

  <!-- 4. Quick Action Menu -->
  <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-xs space-y-3">
    <div class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
      <i class="fa-solid fa-bolt text-blue-600"></i> কুইক মেনু
    </div>

    <div class="grid grid-cols-3 gap-2.5 text-center">
      
      <!-- Delivery Route -->
      <a href="<?= url('dsr/delivery') ?>" class="p-3 bg-slate-50 hover:bg-blue-50/70 border border-slate-200/60 rounded-xl flex flex-col items-center gap-1.5 transition active:scale-95">
        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-base">
          <i class="fa-solid fa-truck-fast"></i>
        </div>
        <span class="text-[11px] font-bold text-slate-800">ডেলিভারি রুট</span>
      </a>

      <!-- Van Stock Inventory -->
      <a href="<?= url('dsr/van-stock') ?>" class="p-3 bg-slate-50 hover:bg-indigo-50/70 border border-slate-200/60 rounded-xl flex flex-col items-center gap-1.5 transition active:scale-95">
        <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-base">
          <i class="fa-solid fa-boxes-stacked"></i>
        </div>
        <span class="text-[11px] font-bold text-slate-800">মাল (স্টক)</span>
      </a>

      <!-- Settlement -->
      <a href="<?= url('dsr/settlement') ?>" class="p-3 bg-slate-50 hover:bg-emerald-50/70 border border-slate-200/60 rounded-xl flex flex-col items-center gap-1.5 transition active:scale-95 relative">
        <?php if (!empty($stats['pending_settlement'])): ?>
          <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-rose-500 rounded-full animate-ping"></span>
        <?php endif; ?>
        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-base">
          <i class="fa-solid fa-file-invoice-dollar"></i>
        </div>
        <span class="text-[11px] font-bold text-slate-800">হিসাব মিলাও</span>
      </a>

    </div>
  </div>

</div>
