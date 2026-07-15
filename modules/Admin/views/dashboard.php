<?php $pageTitle = 'Admin Dashboard'; ?>

<!-- Stat cards -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 mb-7">

  <?php
  $cards = [
    ['label'=>'Total Users',      'value'=>$stats['total_users'],      'icon'=>'fa-users',         'color'=>'blue'],
    ['label'=>'Managers',         'value'=>$stats['total_managers'],   'icon'=>'fa-user-tie',       'color'=>'indigo'],
    ['label'=>'Sales Reps',       'value'=>$stats['total_srs'],        'icon'=>'fa-person-walking', 'color'=>'violet'],
    ['label'=>'DSRs',             'value'=>$stats['total_dsrs'],       'icon'=>'fa-truck',          'color'=>'purple'],
    ['label'=>'Products',         'value'=>$stats['total_products'],   'icon'=>'fa-boxes-stacked',  'color'=>'sky'],
    ['label'=>'Companies',        'value'=>$stats['total_companies'],  'icon'=>'fa-building',       'color'=>'cyan'],
    ['label'=>'Dealers',          'value'=>$stats['total_dealers'],    'icon'=>'fa-store',          'color'=>'teal'],
    ['label'=>'Warehouses',       'value'=>$stats['total_warehouses'], 'icon'=>'fa-warehouse',      'color'=>'emerald'],
    ['label'=>'Pending Orders',   'value'=>$stats['pending_orders'],   'icon'=>'fa-file-invoice',   'color'=>'amber'],
    ['label'=>'Pending Approvals','value'=>$stats['pending_approvals'],'icon'=>'fa-circle-check',   'color'=>'orange'],
    ['label'=>'Today Attendance', 'value'=>$stats['today_attendance'], 'icon'=>'fa-calendar-check', 'color'=>'green'],
    ['label'=>'Today Expenses',   'value'=>'৳ '.number_format($stats['today_expenses'],0), 'icon'=>'fa-receipt', 'color'=>'red'],
  ];
  $colorMap = [
    'blue'   => 'bg-blue-100 text-blue-700',
    'indigo' => 'bg-indigo-100 text-indigo-700',
    'violet' => 'bg-violet-100 text-violet-700',
    'purple' => 'bg-purple-100 text-purple-700',
    'sky'    => 'bg-sky-100 text-sky-700',
    'cyan'   => 'bg-cyan-100 text-cyan-700',
    'teal'   => 'bg-teal-100 text-teal-700',
    'emerald'=> 'bg-emerald-100 text-emerald-700',
    'amber'  => 'bg-amber-100 text-amber-700',
    'orange' => 'bg-orange-100 text-orange-700',
    'green'  => 'bg-green-100 text-green-700',
    'red'    => 'bg-red-100 text-red-700',
  ];
  foreach ($cards as $c):
    $cls = $colorMap[$c['color']] ?? 'bg-gray-100 text-gray-700';
  ?>
  <div class="stat-card flex items-center gap-4">
    <div class="w-11 h-11 rounded-xl <?= $cls ?> flex items-center justify-center flex-shrink-0">
      <i class="fa-solid <?= $c['icon'] ?> text-lg"></i>
    </div>
    <div>
      <div class="text-2xl font-bold text-gray-800"><?= $c['value'] ?></div>
      <div class="text-xs text-gray-500 font-medium"><?= $c['label'] ?></div>
    </div>
  </div>
  <?php endforeach; ?>

</div>

<!-- Bottom: Recent orders + Activity log -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

  <!-- Recent Orders -->
  <div class="card">
    <div class="card-header">
      <h2 class="card-title"><i class="fa-solid fa-file-invoice text-blue-500 mr-2"></i>Recent Orders</h2>
      <a href="<?= url('admin/reports') ?>" class="text-xs text-blue-600 hover:underline">View all</a>
    </div>
    <div class="overflow-x-auto">
      <table class="data-table">
        <thead>
          <tr>
            <th>#</th>
            <th>SR</th>
            <th>Dealer</th>
            <th>Amount</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentOrders as $o): ?>
          <tr>
            <td class="font-mono text-xs text-gray-500">#<?= $o['id'] ?></td>
            <td><?= h($o['sr_name'] ?? '—') ?></td>
            <td><?= h($o['dealer_name'] ?? '—') ?></td>
            <td class="font-semibold"><?= Helpers::money($o['total_amount']) ?></td>
            <td><?= Helpers::statusBadge($o['status']) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($recentOrders)): ?>
            <tr><td colspan="5" class="text-center text-gray-400 py-4">No orders yet</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Activity Log -->
  <div class="card">
    <div class="card-header">
      <h2 class="card-title"><i class="fa-solid fa-clock-rotate-left text-gray-400 mr-2"></i>Recent Activity</h2>
    </div>
    <div class="divide-y divide-gray-50">
      <?php foreach ($recentLogs as $log): ?>
      <div class="flex items-start gap-3 px-5 py-3">
        <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">
          <?= Helpers::initials($log['user_name'] ?? 'SY') ?>
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm text-gray-700">
            <span class="font-medium"><?= h($log['user_name'] ?? 'System') ?></span>
            <?= h($log['description'] ?? $log['action']) ?>
          </p>
          <p class="text-xs text-gray-400 mt-0.5"><?= Helpers::timeAgo($log['created_at']) ?></p>
        </div>
        <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-500 flex-shrink-0"><?= h($log['module']) ?></span>
      </div>
      <?php endforeach; ?>
      <?php if (empty($recentLogs)): ?>
        <div class="px-5 py-4 text-sm text-gray-400 text-center">No activity yet</div>
      <?php endif; ?>
    </div>
  </div>

</div>
