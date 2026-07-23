<?php $pageTitle = 'DSR Profile'; ?>

<div class="p-3.5 sm:p-5 space-y-4 pb-32 max-w-lg mx-auto font-sans">

  <!-- 1. Header Bar -->
  <div class="flex items-center justify-between bg-white p-3.5 rounded-2xl border border-slate-200/90 shadow-xs">
    <div class="flex items-center gap-3">
      <a href="<?= url('dsr/dashboard') ?>" class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200/80 flex items-center justify-center text-slate-700 hover:bg-slate-200 transition">
        <i class="fa-solid fa-arrow-left text-sm"></i>
      </a>
      <div>
        <h1 class="text-base font-black text-slate-900 leading-tight">আমার প্রোফাইল</h1>
        <p class="text-[11px] text-slate-500 font-medium">অ্যাকাউন্ট ও সেটিংস বিবরণ</p>
      </div>
    </div>
  </div>

  <!-- 2. Profile Summary Card -->
  <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs flex flex-col items-center text-center space-y-3">
    
    <!-- Avatar -->
    <div class="w-20 h-20 bg-gradient-to-br from-blue-600 to-indigo-600 text-white rounded-2xl shadow-md flex items-center justify-center text-3xl border-2 border-white">
      <?php if (!empty($user['avatar'])): ?>
        <img src="<?= asset('uploads/avatars/'.$user['avatar']) ?>" class="w-full h-full rounded-2xl object-cover">
      <?php else: ?>
        <i class="fa-solid fa-user-tie"></i>
      <?php endif; ?>
    </div>

    <!-- Name & Role -->
    <div>
      <h2 class="text-lg font-black text-slate-900 leading-tight"><?= h($user['name']) ?></h2>
      <span class="inline-block mt-1 bg-blue-50 text-blue-700 border border-blue-200/80 text-[11px] font-bold px-3 py-0.5 rounded-full">
        ডেলিভারি ম্যান (DSR)
      </span>
    </div>

    <!-- Contact Info -->
    <div class="w-full space-y-2 pt-2 border-t border-slate-100 text-left">
      
      <!-- Email -->
      <div class="flex items-center gap-3 p-2.5 bg-slate-50 rounded-xl border border-slate-200/60">
        <div class="w-8 h-8 rounded-lg bg-white text-slate-500 flex items-center justify-center text-xs shadow-2xs">
          <i class="fa-solid fa-envelope"></i>
        </div>
        <div>
          <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">ইমেইল এড্রেস</div>
          <div class="text-xs font-bold text-slate-800"><?= h($user['email']) ?></div>
        </div>
      </div>

      <!-- Phone -->
      <div class="flex items-center gap-3 p-2.5 bg-slate-50 rounded-xl border border-slate-200/60">
        <div class="w-8 h-8 rounded-lg bg-white text-slate-500 flex items-center justify-center text-xs shadow-2xs">
          <i class="fa-solid fa-phone"></i>
        </div>
        <div>
          <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">মোবাইল নাম্বার</div>
          <div class="text-xs font-bold text-slate-800"><?= h($user['phone'] ?? 'N/A') ?></div>
        </div>
      </div>

    </div>

  </div>

  <!-- 3. Navigation Shortcuts -->
  <div class="bg-white p-3 rounded-2xl border border-slate-200/90 shadow-xs space-y-1.5">
    <div class="text-[11px] font-black text-slate-400 uppercase tracking-wider px-2 py-1">
      জরুরি শর্টকাট মেনু
    </div>

    <!-- Expenses -->
    <a href="<?= url('dsr/expenses') ?>" class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition group">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-sm border border-purple-200/60">
          <i class="fa-solid fa-receipt"></i>
        </div>
        <div>
          <div class="text-xs font-bold text-slate-800">সারাদিনের খরচ এন্ট্রি</div>
          <div class="text-[10px] text-slate-400 font-medium">ফুয়েল, টোল ও নাস্তা খরচ</div>
        </div>
      </div>
      <i class="fa-solid fa-chevron-right text-xs text-slate-300 group-hover:text-slate-500 transition"></i>
    </a>

    <!-- Inventory -->
    <a href="<?= url('dsr/van-stock') ?>" class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition group">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm border border-indigo-200/60">
          <i class="fa-solid fa-boxes-stacked"></i>
        </div>
        <div>
          <div class="text-xs font-bold text-slate-800">ভ্যান স্টক ও মাল</div>
          <div class="text-[10px] text-slate-400 font-medium">পণ্য ও মালের স্টক হিসাব</div>
        </div>
      </div>
      <i class="fa-solid fa-chevron-right text-xs text-slate-300 group-hover:text-slate-500 transition"></i>
    </a>

    <!-- Settlement -->
    <a href="<?= url('dsr/settlement') ?>" class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition group">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm border border-emerald-200/60">
          <i class="fa-solid fa-file-invoice-dollar"></i>
        </div>
        <div>
          <div class="text-xs font-bold text-slate-800">হিসাব মিলাও (সেটেলমেন্ট)</div>
          <div class="text-[10px] text-slate-400 font-medium">ক্যাশ হ্যান্ডওভার হিসাব</div>
        </div>
      </div>
      <i class="fa-solid fa-chevron-right text-xs text-slate-300 group-hover:text-slate-500 transition"></i>
    </a>
  </div>

  <!-- 4. Log Out -->
  <a href="<?= url('dsr/logout') ?>" class="flex items-center justify-between p-3.5 bg-rose-50 hover:bg-rose-100/80 border border-rose-200/80 rounded-2xl text-rose-700 transition active:scale-95">
    <div class="flex items-center gap-3">
      <div class="w-8 h-8 rounded-xl bg-rose-600 text-white flex items-center justify-center text-xs shadow-2xs">
        <i class="fa-solid fa-arrow-right-from-bracket"></i>
      </div>
      <span class="text-xs font-black">লগআউট করুন</span>
    </div>
    <i class="fa-solid fa-chevron-right text-xs text-rose-400"></i>
  </a>

</div>
