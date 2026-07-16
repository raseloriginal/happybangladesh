<?php $pageTitle = 'Dashboard'; ?>
<div class="px-4 pt-6 pb-4 bg-brand rounded-b-[40px] shadow-lg mb-6">
  <div class="flex items-center justify-between mb-6 text-white">
    <div class="flex items-center gap-3">
      <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center border-2 border-white/30 backdrop-blur-sm">
        <i class="fa-solid fa-user text-xl"></i>
      </div>
      <div>
        <div class="text-white/80 text-xs font-medium">Welcome back,</div>
        <div class="font-bold text-lg leading-tight"><?= h(Auth::name()) ?></div>
      </div>
    </div>
    <a href="<?= url('dsr/profile') ?>" class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm hover:bg-white/30 transition">
      <i class="fa-solid fa-bell text-white"></i>
    </a>
  </div>

  <div class="bg-white rounded-3xl p-5 shadow-xl flex items-center justify-between mt-2">
    <div>
      <div class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-1">Today's Deliveries</div>
      <div class="text-3xl font-black text-gray-800"><?= $stats['todays_deliveries'] ?></div>
    </div>
    <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center text-brand">
      <i class="fa-solid fa-truck-fast text-2xl"></i>
    </div>
  </div>
</div>

<div class="px-4 mb-6">
  <h3 class="text-sm font-bold text-gray-800 mb-3 px-1">Overview</h3>
  <div class="grid grid-cols-2 gap-3">
    <!-- Stat 1 -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-50 flex flex-col justify-between">
      <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-500 flex items-center justify-center mb-3">
        <i class="fa-solid fa-store text-sm"></i>
      </div>
      <div class="text-2xl font-bold text-gray-800"><?= $stats['ordered_retailers'] ?></div>
      <div class="text-xs text-gray-500 font-medium">Ordered Retailers</div>
    </div>
    
    <!-- Stat 2 -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-50 flex flex-col justify-between">
      <div class="w-8 h-8 rounded-full bg-green-50 text-green-500 flex items-center justify-center mb-3">
        <i class="fa-solid fa-check-double text-sm"></i>
      </div>
      <div class="text-2xl font-bold text-gray-800"><?= $stats['completed_deliveries'] ?></div>
      <div class="text-xs text-gray-500 font-medium">Completed</div>
    </div>

    <!-- Stat 3 -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-50 flex flex-col justify-between">
      <div class="w-8 h-8 rounded-full bg-orange-50 text-orange-500 flex items-center justify-center mb-3">
        <i class="fa-solid fa-clock text-sm"></i>
      </div>
      <div class="text-2xl font-bold text-gray-800"><?= $stats['due_deliveries'] ?></div>
      <div class="text-xs text-gray-500 font-medium">Due Deliveries</div>
    </div>

    <!-- Stat 4 -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-50 flex flex-col justify-between">
      <div class="w-8 h-8 rounded-full bg-purple-50 text-purple-500 flex items-center justify-center mb-3">
        <i class="fa-solid fa-tags text-sm"></i>
      </div>
      <div class="text-2xl font-bold text-gray-800"><?= $stats['ready_sales'] ?></div>
      <div class="text-xs text-gray-500 font-medium">Ready Sales</div>
    </div>
  </div>
</div>

<div class="px-4 mb-4">
  <h3 class="text-sm font-bold text-gray-800 mb-3 px-1">Quick Actions</h3>
  <div class="grid grid-cols-4 gap-2">
    <a href="<?= url('dsr/scanner') ?>" class="flex flex-col items-center gap-2 p-3 bg-white rounded-2xl shadow-sm hover:shadow-md transition active:scale-95">
      <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center">
        <i class="fa-solid fa-qrcode text-lg"></i>
      </div>
      <span class="text-[10px] font-semibold text-gray-600">Scanner</span>
    </a>
    
    <a href="<?= url('dsr/delivery') ?>" class="flex flex-col items-center gap-2 p-3 bg-white rounded-2xl shadow-sm hover:shadow-md transition active:scale-95">
      <div class="w-12 h-12 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center">
        <i class="fa-solid fa-map-location-dot text-lg"></i>
      </div>
      <span class="text-[10px] font-semibold text-gray-600">Map View</span>
    </a>
    
    <a href="<?= url('dsr/collection') ?>" class="flex flex-col items-center gap-2 p-3 bg-white rounded-2xl shadow-sm hover:shadow-md transition active:scale-95">
      <div class="w-12 h-12 bg-indigo-50 text-indigo-500 rounded-full flex items-center justify-center">
        <i class="fa-solid fa-box-open text-lg"></i>
      </div>
      <span class="text-[10px] font-semibold text-gray-600">Collection</span>
    </a>
    
    <a href="<?= url('dsr/settlement') ?>" class="flex flex-col items-center gap-2 p-3 bg-white rounded-2xl shadow-sm hover:shadow-md transition active:scale-95 relative">
      <?php if($stats['pending_settlement'] > 0): ?>
      <span class="absolute top-2 right-2 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white"></span>
      <?php endif; ?>
      <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center">
        <i class="fa-solid fa-money-bill-transfer text-lg"></i>
      </div>
      <span class="text-[10px] font-semibold text-gray-600">Settlement</span>
    </a>
  </div>
</div>
