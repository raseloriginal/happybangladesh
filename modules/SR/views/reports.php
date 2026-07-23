<?php $pageTitle = 'Reports — Coming Soon'; ?>

<!-- Top Header -->
<div class="bg-gradient-to-r from-blue-900 via-blue-800 to-blue-600 px-5 pt-10 pb-8 text-white relative overflow-hidden">
  <!-- Background Decorative Shapes -->
  <div class="absolute -right-10 -bottom-10 w-44 h-44 rounded-full bg-white/5 blur-2xl pointer-events-none"></div>
  <div class="absolute right-20 -top-10 w-32 h-32 rounded-full bg-blue-400/10 blur-xl pointer-events-none"></div>

  <div class="relative z-10 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <a href="<?= url('sr/dashboard') ?>" class="w-9 h-9 bg-white/10 hover:bg-white/20 active:scale-95 rounded-full flex items-center justify-center text-white transition">
        <i class="fa-solid fa-arrow-left text-sm"></i>
      </a>
      <div>
        <h1 class="text-xl font-black tracking-tight flex items-center gap-2">
          <i class="fa-solid fa-chart-pie opacity-90"></i>
          রিপোর্টস ও অ্যানালিটিক্স
        </h1>
        <p class="text-xs text-blue-200 font-medium mt-0.5">Sales & Performance Analytics</p>
      </div>
    </div>
  </div>
</div>

<!-- Main Coming Soon Container -->
<div class="p-5 max-w-md mx-auto flex flex-col items-center justify-center min-h-[60vh] text-center">

  <!-- Floating Animated Icon Box -->
  <div class="relative mb-6">
    <!-- Pulse Ring -->
    <div class="absolute inset-0 rounded-3xl bg-blue-500/20 animate-ping"></div>
    <div class="relative w-24 h-24 rounded-3xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center text-white shadow-xl shadow-blue-500/30">
      <i class="fa-solid fa-rocket text-4xl animate-bounce"></i>
    </div>
    <div class="absolute -bottom-2 -right-2 bg-amber-400 text-amber-950 font-black text-[10px] uppercase px-2.5 py-1 rounded-full shadow-md border-2 border-white tracking-wider">
      Soon
    </div>
  </div>

  <!-- Heading & Description -->
  <h2 class="text-2xl font-black text-slate-900 mb-2">
    Coming Soon!
  </h2>
  <p class="text-xs font-semibold text-blue-600 bg-blue-50 px-3 py-1 rounded-full mb-4 inline-block">
    নতুন ফিচার শীঘ্রই আসছে 🚀
  </p>

  <p class="text-sm text-slate-600 leading-relaxed max-w-xs mb-8">
    রিপোর্টস মডিউলটি আপডেট করা হচ্ছে। খুব শীঘ্রই এখানে আপনার বিক্রয়ের বিস্তারিত অ্যানালিটিক্স, কমিশন ও পারফরম্যান্স রিপোর্টস দেখতে পাবেন।
  </p>

  <!-- Feature Preview Cards -->
  <div class="w-full space-y-3 mb-8">
    
    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-3.5 text-left">
      <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-lg font-bold flex-shrink-0">
        <i class="fa-solid fa-chart-line"></i>
      </div>
      <div>
        <div class="text-xs font-bold text-slate-900">অ্যাডভান্সড সেলস অ্যানালিটিক্স</div>
        <div class="text-[11px] text-slate-500 font-medium mt-0.5">দৈনিক, সাপ্তাহিক ও মাসিক সেলস গ্রাফ</div>
      </div>
    </div>

    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-3.5 text-left">
      <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg font-bold flex-shrink-0">
        <i class="fa-solid fa-hand-holding-dollar"></i>
      </div>
      <div>
        <div class="text-xs font-bold text-slate-900">কমিশন ও ইনসেন্টিভ ট্র্যাকিং</div>
        <div class="text-[11px] text-slate-500 font-medium mt-0.5">আপনার সকল কমিশনের লাইভ হিসাব</div>
      </div>
    </div>

    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-3.5 text-left">
      <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center text-lg font-bold flex-shrink-0">
        <i class="fa-solid fa-store"></i>
      </div>
      <div>
        <div class="text-xs font-bold text-slate-900">দোকানভিত্তিক বিক্রয়ের রিপোর্ট</div>
        <div class="text-[11px] text-slate-500 font-medium mt-0.5">শীর্ষ দোকান ও সেরা বিক্রিত পণ্য</div>
      </div>
    </div>

  </div>

  <!-- Back to Dashboard Button -->
  <a href="<?= url('sr/dashboard') ?>" class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 active:scale-98 text-white rounded-xl font-bold text-sm shadow-lg shadow-blue-600/25 transition-all text-center flex items-center justify-center gap-2">
    <i class="fa-solid fa-house"></i>
    ড্যাশবোর্ডে ফিরে যান
  </a>

</div>

<!-- Bottom Spacing for Floating Nav -->
<div class="h-24"></div>
