<?php $pageTitle = 'Van Stock'; ?>
<div class="h-full flex flex-col bg-gray-50 pb-20 overflow-y-auto">
  
  <!-- Header -->
  <div class="bg-white rounded-b-3xl shadow-sm px-4 pt-10 pb-5 relative z-10 flex items-center justify-between mb-4">
    <div class="flex items-center gap-3">
      <a href="<?= url('dsr/profile') ?>" class="w-8 h-8 flex items-center justify-center text-gray-500 active:text-brand transition">
        <i class="fa-solid fa-arrow-left"></i>
      </a>
      <h1 class="text-xl font-bold text-gray-800">Van Stock</h1>
    </div>
    <div class="text-xs bg-indigo-50 text-indigo-600 font-bold px-3 py-1.5 rounded-lg">
      <?= count($items) ?> Items
    </div>
  </div>

  <div class="px-4">
    <?php if(empty($items)): ?>
      <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center mt-4">
        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center text-gray-400 mb-4">
          <i class="fa-solid fa-truck-ramp-box text-3xl"></i>
        </div>
        <h2 class="text-lg font-bold text-gray-800 mb-1">Stock Empty</h2>
        <p class="text-sm text-gray-500">Your van is currently empty. Collect dispatch items to load your van.</p>
        <a href="<?= url('dsr/collection') ?>" class="mt-6 bg-brand text-white font-bold py-2 px-6 rounded-xl shadow-md active:scale-95 transition">Go to Collection</a>
      </div>
    <?php else: ?>
      <div class="space-y-3">
        <?php foreach($items as $idx => $item): ?>
          <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-indigo-50 text-indigo-500 rounded-xl flex items-center justify-center flex-shrink-0">
              <i class="fa-solid fa-box text-xl"></i>
            </div>
            <div class="flex-1">
              <div class="font-bold text-sm text-gray-800 mb-0.5 line-clamp-1"><?= h($item['product_name']) ?></div>
              <div class="flex items-center gap-2 text-[10px] text-gray-500">
                <span class="font-semibold">SKU: <?= h($item['sku']) ?></span>
                <?php if($item['lot_number']): ?>
                  <span>&bull; Lot: <?= h($item['lot_number']) ?></span>
                <?php endif; ?>
              </div>
            </div>
            <div class="text-right">
              <div class="text-lg font-black text-brand"><?= $item['quantity'] ?></div>
              <div class="text-[9px] uppercase font-bold text-gray-400">Units</div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
