<?php $pageTitle = 'DSR Dashboard'; ?>
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-7">
  <?php
  $cards = [
    ['label'=>'Van Stock Items',     'value'=>Helpers::number($stats['van_stock_items']),    'icon'=>'fa-truck',        'cls'=>'bg-blue-100 text-blue-700'],
    ['label'=>'Active Deliveries',   'value'=>$stats['active_deliveries'],                   'icon'=>'fa-motorcycle',   'cls'=>'bg-amber-100 text-amber-700'],
    ['label'=>'Delivered Today',     'value'=>$stats['delivered_today'],                     'icon'=>'fa-circle-check', 'cls'=>'bg-green-100 text-green-700'],
    ['label'=>"Today's Expenses",    'value'=>Helpers::money($stats['today_expenses']),      'icon'=>'fa-receipt',      'cls'=>'bg-red-100 text-red-700'],
  ];
  foreach ($cards as $c): ?>
  <div class="stat-card flex items-center gap-4">
    <div class="w-11 h-11 rounded-xl <?= $c['cls'] ?> flex items-center justify-center flex-shrink-0">
      <i class="fa-solid <?= $c['icon'] ?> text-lg"></i>
    </div>
    <div>
      <div class="text-2xl font-bold text-gray-800"><?= $c['value'] ?></div>
      <div class="text-xs text-gray-500 font-medium"><?= $c['label'] ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
  <!-- Van stock preview -->
  <div class="card">
    <div class="card-header">
      <h2 class="card-title"><i class="fa-solid fa-truck text-blue-500 mr-2"></i>Van Stock (Top 5)</h2>
      <a href="<?= url('dsr/van-stock') ?>" class="text-xs text-blue-600 hover:underline">View all</a>
    </div>
    <div class="divide-y">
      <?php foreach ($vanStock as $vs): ?>
      <div class="flex items-center justify-between px-5 py-3">
        <div>
          <div class="font-medium text-sm"><?= h($vs['product_name']) ?></div>
          <div class="text-xs text-gray-400"><?= h($vs['sku']) ?></div>
        </div>
        <span class="badge bg-blue-100 text-blue-700 text-sm"><?= Helpers::number($vs['quantity']) ?> units</span>
      </div>
      <?php endforeach; ?>
      <?php if (empty($vanStock)): ?>
        <div class="px-5 py-6 text-sm text-gray-400 text-center">No stock loaded.</div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Quick actions -->
  <div class="card">
    <div class="card-header"><h2 class="card-title">Quick Actions</h2></div>
    <div class="card-body grid gap-3">
      <a href="<?= url('dsr/scanner') ?>" class="flex items-center gap-4 p-4 rounded-xl border border-blue-100 bg-blue-50 hover:bg-blue-100 transition">
        <div class="w-10 h-10 rounded-lg bg-blue-600 text-white flex items-center justify-center"><i class="fa-solid fa-qrcode"></i></div>
        <div><div class="font-semibold text-blue-700">Scan Product</div><div class="text-xs text-blue-400">QR / Barcode scanner</div></div>
      </a>
      <a href="<?= url('dsr/expenses') ?>" class="flex items-center gap-4 p-4 rounded-xl border border-green-100 bg-green-50 hover:bg-green-100 transition">
        <div class="w-10 h-10 rounded-lg bg-green-600 text-white flex items-center justify-center"><i class="fa-solid fa-receipt"></i></div>
        <div><div class="font-semibold text-green-700">Log Expense</div><div class="text-xs text-green-400">Record daily expenses</div></div>
      </a>
      <a href="<?= url('dsr/delivery') ?>" class="flex items-center gap-4 p-4 rounded-xl border border-amber-100 bg-amber-50 hover:bg-amber-100 transition">
        <div class="w-10 h-10 rounded-lg bg-amber-500 text-white flex items-center justify-center"><i class="fa-solid fa-truck-fast"></i></div>
        <div><div class="font-semibold text-amber-700">Delivery Actions</div><div class="text-xs text-amber-400">Update delivery status</div></div>
      </a>
    </div>
  </div>
</div>
