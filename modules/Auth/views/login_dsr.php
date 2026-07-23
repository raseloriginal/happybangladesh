<?php $pageTitle = 'DSR Login'; ?>

<div class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
  <div class="max-w-md w-full bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden p-8">
    
    <!-- Clean Header -->
    <div class="text-center mb-8">
      <div class="w-14 h-14 bg-blue-900 rounded-2xl flex items-center justify-center text-white text-xl font-bold mx-auto mb-3 shadow-md shadow-blue-800/20">
        <i class="fa-solid fa-truck-fast"></i>
      </div>
      <h1 class="text-2xl font-bold text-slate-900">Happy Bangladesh</h1>
      <p class="text-xs text-slate-500 mt-1 font-medium">Delivery Rep Portal Sign In</p>
    </div>

    <?php $flash = Auth::getFlash(); if ($flash): ?>
      <div class="rounded-xl bg-rose-50 border border-rose-200 p-3.5 mb-6 flex items-center gap-2.5 text-xs text-rose-700 font-bold">
        <i class="fa-solid fa-circle-exclamation text-rose-500 text-sm"></i>
        <span><?= h($flash['message']) ?></span>
      </div>
    <?php endif; ?>

    <!-- Login Form -->
    <form action="<?= url('dsr/login') ?>" method="POST" class="space-y-4">
      <?= Helpers::csrfField() ?>
      
      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1.5">Phone or Email ID</label>
        <input type="text" name="email" required placeholder="01700000000 / dsr@happybd.com" 
               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-medium text-slate-900 outline-none focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition">
      </div>

      <div>
        <div class="flex items-center justify-between mb-1.5">
          <label class="block text-xs font-bold text-slate-700">Password</label>
          <a href="<?= url('forgot') ?>" class="text-xs text-blue-600 font-bold hover:underline">Forgot?</a>
        </div>
        <input type="password" name="password" required placeholder="••••••••" 
               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-medium text-slate-900 outline-none focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition">
      </div>

      <div class="flex items-center gap-2 mt-1">
        <input type="checkbox" name="remember" value="1" id="remember_dsr"
               class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500 cursor-pointer">
        <label for="remember_dsr" class="text-xs text-slate-600 font-medium cursor-pointer select-none">Remember me for 30 days</label>
      </div>

      <button type="submit" class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-sm shadow-md shadow-blue-600/20 transition mt-2">
        Sign In to Delivery App
      </button>
    </form>



  </div>
</div>


