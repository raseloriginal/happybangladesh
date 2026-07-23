<?php $pageTitle = 'Select Portal'; ?>

<div class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
  <div class="max-w-md w-full bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden p-8">
    
    <!-- Clean Header -->
    <div class="text-center mb-8">
      <div class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center text-white text-2xl font-black mx-auto mb-3 shadow-md shadow-blue-500/20">
        HB
      </div>
      <h1 class="text-2xl font-bold text-slate-900">Happy Bangladesh</h1>
      <p class="text-xs text-slate-500 mt-1">Distribution Management System</p>
    </div>

    <!-- Role Options -->
    <div class="space-y-3 mb-6">
      <!-- Admin Portal -->
      <a href="<?= url('admin/login') ?>" class="flex items-center gap-3.5 p-4 rounded-2xl border border-slate-200 hover:border-blue-600 hover:bg-blue-50/50 transition group">
        <div class="w-11 h-11 rounded-xl bg-blue-600 text-white flex items-center justify-center text-base font-bold group-hover:scale-105 transition-transform">
          <i class="fa-solid fa-user-shield"></i>
        </div>
        <div class="flex-1 min-w-0">
          <div class="font-bold text-sm text-slate-900 group-hover:text-blue-600">Admin Portal</div>
          <div class="text-xs text-slate-500 truncate">System configuration & control</div>
        </div>
        <i class="fa-solid fa-chevron-right text-xs text-slate-300 group-hover:text-blue-600"></i>
      </a>

      <!-- Manager Portal -->
      <a href="<?= url('manager/login') ?>" class="flex items-center gap-3.5 p-4 rounded-2xl border border-slate-200 hover:border-blue-600 hover:bg-blue-50/50 transition group">
        <div class="w-11 h-11 rounded-xl bg-blue-700 text-white flex items-center justify-center text-base font-bold group-hover:scale-105 transition-transform">
          <i class="fa-solid fa-users-gear"></i>
        </div>
        <div class="flex-1 min-w-0">
          <div class="font-bold text-sm text-slate-900 group-hover:text-blue-600">Manager Portal</div>
          <div class="text-xs text-slate-500 truncate">Inventory & dispatches</div>
        </div>
        <i class="fa-solid fa-chevron-right text-xs text-slate-300 group-hover:text-blue-600"></i>
      </a>

      <!-- SR Portal -->
      <a href="<?= url('sr/login') ?>" class="flex items-center gap-3.5 p-4 rounded-2xl border border-slate-200 hover:border-blue-600 hover:bg-blue-50/50 transition group">
        <div class="w-11 h-11 rounded-xl bg-blue-800 text-white flex items-center justify-center text-base font-bold group-hover:scale-105 transition-transform">
          <i class="fa-solid fa-briefcase"></i>
        </div>
        <div class="flex-1 min-w-0">
          <div class="font-bold text-sm text-slate-900 group-hover:text-blue-600">Sales Rep (SR)</div>
          <div class="text-xs text-slate-500 truncate">Orders & market tracking</div>
        </div>
        <i class="fa-solid fa-chevron-right text-xs text-slate-300 group-hover:text-blue-600"></i>
      </a>

      <!-- DSR Portal -->
      <a href="<?= url('dsr/login') ?>" class="flex items-center gap-3.5 p-4 rounded-2xl border border-slate-200 hover:border-blue-600 hover:bg-blue-50/50 transition group">
        <div class="w-11 h-11 rounded-xl bg-blue-900 text-white flex items-center justify-center text-base font-bold group-hover:scale-105 transition-transform">
          <i class="fa-solid fa-truck-fast"></i>
        </div>
        <div class="flex-1 min-w-0">
          <div class="font-bold text-sm text-slate-900 group-hover:text-blue-600">Delivery Rep (DSR)</div>
          <div class="text-xs text-slate-500 truncate">Van stock & deliveries</div>
        </div>
        <i class="fa-solid fa-chevron-right text-xs text-slate-300 group-hover:text-blue-600"></i>
      </a>
    </div>

    <!-- Bottom Footer -->
    <div class="pt-4 border-t border-slate-100 flex items-center justify-center gap-2 text-xs text-slate-400 font-medium">
      <i class="fa-solid fa-shield-halved text-blue-600"></i>
      <span>Happy Bangladesh DMS &bull; v2.4</span>
    </div>

  </div>
</div>


