<?php $pageTitle = 'Manager Login'; ?>

<div class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
  <div class="max-w-md w-full bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden p-8">
    
    <!-- Clean Header -->
    <div class="text-center mb-8">
      <div class="w-14 h-14 bg-blue-700 rounded-2xl flex items-center justify-center text-white text-xl font-bold mx-auto mb-3 shadow-md shadow-blue-600/20">
        <i class="fa-solid fa-users-gear"></i>
      </div>
      <h1 class="text-2xl font-bold text-slate-900">Happy Bangladesh</h1>
      <p class="text-xs text-slate-500 mt-1 font-medium">Manager Portal Sign In</p>
    </div>

    <?php $flash = Auth::getFlash(); if ($flash): ?>
      <div class="rounded-xl bg-rose-50 border border-rose-200 p-3.5 mb-6 flex items-center gap-2.5 text-xs text-rose-700 font-bold">
        <i class="fa-solid fa-circle-exclamation text-rose-500 text-sm"></i>
        <span><?= h($flash['message']) ?></span>
      </div>
    <?php endif; ?>

    <!-- Login Form -->
    <form action="<?= url('manager/login') ?>" method="POST" class="space-y-4">
      <?= Helpers::csrfField() ?>
      
      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1.5">Email or Phone</label>
        <input type="text" name="email" required placeholder="manager@happybd.com" 
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

      <button type="submit" class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-sm shadow-md shadow-blue-600/20 transition mt-2">
        Sign In to Manager
      </button>
    </form>

    <!-- Excel Role Tabs at Bottom -->
    <div class="mt-8 pt-6 border-t border-slate-100 flex items-center justify-between text-xs">
      <span class="text-slate-400 font-medium">Select Portal:</span>
      <div class="flex items-center gap-1 font-bold">
        <a href="<?= url('admin/login') ?>" class="px-2.5 py-1 text-slate-600 hover:bg-slate-100 rounded">Admin</a>
        <a href="<?= url('manager/login') ?>" class="px-2.5 py-1 bg-blue-600 text-white rounded">Manager</a>
        <a href="<?= url('sr/login') ?>" class="px-2.5 py-1 text-slate-600 hover:bg-slate-100 rounded">SR</a>
        <a href="<?= url('dsr/login') ?>" class="px-2.5 py-1 text-slate-600 hover:bg-slate-100 rounded">DSR</a>
      </div>
    </div>

  </div>
</div>


