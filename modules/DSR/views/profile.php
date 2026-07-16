<?php $pageTitle = 'Profile'; ?>
<div class="h-full flex flex-col bg-gray-50 pb-20">
  
  <div class="bg-brand pt-10 pb-24 px-4 text-white relative rounded-b-[40px] shadow-lg">
    <div class="flex items-center justify-between mb-8 relative z-10">
      <h1 class="text-xl font-bold">My Profile</h1>
      <a href="<?= url('dsr/dashboard') ?>" class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-md active:bg-white/30 transition">
        <i class="fa-solid fa-xmark text-lg"></i>
      </a>
    </div>
  </div>

  <div class="px-4 -mt-20 relative z-20 flex-1">
    
    <!-- Profile Card -->
    <div class="bg-white rounded-3xl p-6 shadow-xl mb-6 border border-gray-100 flex flex-col items-center">
      <div class="w-24 h-24 bg-blue-50 text-blue-600 rounded-full border-4 border-white shadow-md flex items-center justify-center text-4xl mb-4">
        <?php if(!empty($user['avatar'])): ?>
          <img src="<?= asset('uploads/avatars/'.$user['avatar']) ?>" class="w-full h-full rounded-full object-cover">
        <?php else: ?>
          <i class="fa-solid fa-user-tie"></i>
        <?php endif; ?>
      </div>
      
      <h2 class="text-xl font-black text-gray-800"><?= h($user['name']) ?></h2>
      <div class="text-brand font-semibold text-sm bg-blue-50 px-3 py-1 rounded-full mt-1 mb-3">Delivery Sales Rep</div>
      
      <div class="w-full space-y-3 mt-2">
        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-2xl">
          <div class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center text-gray-500">
            <i class="fa-solid fa-envelope"></i>
          </div>
          <div>
            <div class="text-[10px] text-gray-400 font-bold uppercase">Email</div>
            <div class="font-semibold text-sm text-gray-700"><?= h($user['email']) ?></div>
          </div>
        </div>
        
        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-2xl">
          <div class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center text-gray-500">
            <i class="fa-solid fa-phone"></i>
          </div>
          <div>
            <div class="text-[10px] text-gray-400 font-bold uppercase">Phone</div>
            <div class="font-semibold text-sm text-gray-700"><?= h($user['phone'] ?? 'N/A') ?></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="space-y-3">
      <a href="<?= url('dsr/expenses') ?>" class="flex items-center justify-between p-4 bg-white rounded-2xl shadow-sm border border-gray-100 active:bg-gray-50 transition">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-blue-50 text-blue-500 rounded-xl flex items-center justify-center"><i class="fa-solid fa-receipt"></i></div>
          <span class="font-semibold text-gray-700">My Expenses</span>
        </div>
        <i class="fa-solid fa-chevron-right text-gray-300"></i>
      </a>

      <a href="<?= url('dsr/van-stock') ?>" class="flex items-center justify-between p-4 bg-white rounded-2xl shadow-sm border border-gray-100 active:bg-gray-50 transition">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-indigo-50 text-indigo-500 rounded-xl flex items-center justify-center"><i class="fa-solid fa-truck"></i></div>
          <span class="font-semibold text-gray-700">Van Stock</span>
        </div>
        <i class="fa-solid fa-chevron-right text-gray-300"></i>
      </a>

      <a href="<?= url('dsr/logout') ?>" class="flex items-center justify-between p-4 bg-red-50 rounded-2xl shadow-sm border border-red-100 mt-6 active:bg-red-100 transition">
        <div class="flex items-center gap-3 text-red-600">
          <div class="w-10 h-10 bg-white text-red-500 rounded-xl flex items-center justify-center shadow-sm"><i class="fa-solid fa-arrow-right-from-bracket"></i></div>
          <span class="font-bold">Log Out</span>
        </div>
      </a>
    </div>

  </div>
</div>
