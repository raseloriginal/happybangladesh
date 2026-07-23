<?php $pageTitle = 'Forgot Password'; ?>

<div class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
  <div class="max-w-md w-full bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden p-8">
    
    <!-- Clean Header -->
    <div class="text-center mb-8">
      <div class="w-14 h-14 bg-blue-600 rounded-2xl flex items-center justify-center text-white text-xl font-bold mx-auto mb-3 shadow-md shadow-blue-500/20">
        <i class="fa-solid fa-key"></i>
      </div>
      <h1 class="text-2xl font-bold text-slate-900">Happy Bangladesh</h1>
      <p class="text-xs text-slate-500 mt-1 font-medium">Forgot Password</p>
    </div>

    <?php $flash = Auth::getFlash(); if ($flash): ?>
      <div class="rounded-xl bg-blue-50 border border-blue-200 p-3.5 mb-6 flex items-center gap-2.5 text-xs text-blue-800 font-bold">
        <i class="fa-solid fa-circle-info text-blue-600 text-sm"></i>
        <span><?= h($flash['message']) ?></span>
      </div>
    <?php endif; ?>

    <!-- Form -->
    <form action="<?= url('forgot') ?>" method="POST" class="space-y-4">
      <?= Helpers::csrfField() ?>
      
      <div>
        <label class="block text-xs font-bold text-slate-700 mb-1.5">Registered Email</label>
        <input type="email" name="email" required placeholder="your@email.com" 
               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-medium text-slate-900 outline-none focus:bg-white focus:border-blue-600 focus:ring-2 focus:ring-blue-100 transition">
      </div>

      <div class="flex items-center justify-between text-xs pt-1">
        <a href="<?= url('login') ?>" class="text-slate-500 font-medium hover:text-slate-800">&larr; Back to Portals</a>
      </div>

      <button type="submit" class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-sm shadow-md shadow-blue-600/20 transition mt-2">
        Send Reset Link
      </button>
    </form>

    <!-- Excel Role Tabs at Bottom -->
    <div class="mt-8 pt-6 border-t border-slate-100 flex items-center justify-between text-xs">
      <span class="text-slate-400 font-medium">Select Portal:</span>
      <div class="flex items-center gap-1 font-bold">
        <a href="<?= url('admin/login') ?>" class="px-2.5 py-1 text-slate-600 hover:bg-slate-100 rounded">Admin</a>
        <a href="<?= url('manager/login') ?>" class="px-2.5 py-1 text-slate-600 hover:bg-slate-100 rounded">Manager</a>
        <a href="<?= url('sr/login') ?>" class="px-2.5 py-1 text-slate-600 hover:bg-slate-100 rounded">SR</a>
        <a href="<?= url('dsr/login') ?>" class="px-2.5 py-1 text-slate-600 hover:bg-slate-100 rounded">DSR</a>
      </div>
    </div>

  </div>
</div>


