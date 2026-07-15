<?php $pageTitle = 'Manager Dashboard'; ?>
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 mb-7">
  <?php
  $cards = [
    ['label'=>'Products',          'value'=>$stats['total_products'],  'icon'=>'fa-boxes-stacked', 'color'=>'bg-blue-100 text-blue-700'],
    ['label'=>'Lots',              'value'=>$stats['total_lots'],      'icon'=>'fa-layer-group',   'color'=>'bg-indigo-100 text-indigo-700'],
    ['label'=>'Total Stock',       'value'=>Helpers::number($stats['total_inventory']), 'icon'=>'fa-cubes', 'color'=>'bg-emerald-100 text-emerald-700'],
    ['label'=>'Pending Dispatch',  'value'=>$stats['pending_dispatch'],'icon'=>'fa-truck-fast',    'color'=>'bg-amber-100 text-amber-700'],
    ['label'=>'Pending Returns',   'value'=>$stats['pending_returns'], 'icon'=>'fa-rotate-left',   'color'=>'bg-red-100 text-red-700'],
    ['label'=>'Today Attendance',  'value'=>$stats['today_attendance'],'icon'=>'fa-calendar-check','color'=>'bg-green-100 text-green-700'],
    ['label'=>'Ready Sale Items',  'value'=>$stats['total_readysale'], 'icon'=>'fa-tags',          'color'=>'bg-violet-100 text-violet-700'],
  ];
  foreach ($cards as $c): ?>
  <div class="stat-card flex items-center gap-4">
    <div class="w-11 h-11 rounded-xl <?= $c['color'] ?> flex items-center justify-center flex-shrink-0">
      <i class="fa-solid <?= $c['icon'] ?> text-lg"></i>
    </div>
    <div>
      <div class="text-2xl font-bold text-gray-800"><?= $c['value'] ?></div>
      <div class="text-xs text-gray-500 font-medium"><?= $c['label'] ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Recent Products -->
<div class="card">
  <div class="card-header">
    <h2 class="card-title"><i class="fa-solid fa-boxes-stacked text-blue-500 mr-2"></i>Recent Products</h2>
    <a href="<?= url('manager/products') ?>" class="text-xs text-blue-600 hover:underline">View all</a>
  </div>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
    <?php foreach ($recentProducts as $p): ?>
    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-100">
      <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center flex-shrink-0">
        <i class="fa-solid fa-box"></i>
      </div>
      <div class="min-w-0">
        <div class="font-medium text-sm text-gray-800 truncate"><?= h($p['name']) ?></div>
        <div class="text-xs text-gray-400"><?= h($p['sku']) ?> &bull; <?= Helpers::money($p['price']) ?></div>
        <div class="text-xs text-gray-400"><?= h($p['company_name'] ?? '—') ?></div>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($recentProducts)): ?>
      <div class="col-span-3 text-center py-6 text-gray-400">No products yet. <a href="<?= url('manager/products/create') ?>" class="text-blue-600">Add one</a>.</div>
    <?php endif; ?>
  </div>
</div>
