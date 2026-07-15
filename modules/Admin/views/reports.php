<?php $pageTitle = 'Sales Reports'; ?>
<div class="page-header">
  <div><h1 class="page-title">Sales Reports</h1><div class="breadcrumb">Admin &rsaquo; Reports</div></div>
</div>

<!-- Date filter -->
<div class="card mb-5">
  <div class="card-body">
    <form method="GET" class="flex flex-wrap items-end gap-3">
      <div>
        <label class="form-label">From</label>
        <input type="date" id="date-from" name="from" value="<?= h($from) ?>" class="form-input w-40">
      </div>
      <div>
        <label class="form-label">To</label>
        <input type="date" id="date-to" name="to" value="<?= h($to) ?>" class="form-input w-40">
      </div>
      <div class="flex gap-2">
        <button type="button" data-date-preset="today" class="btn btn-secondary btn-sm">Today</button>
        <button type="button" data-date-preset="week" class="btn btn-secondary btn-sm">This Week</button>
        <button type="button" data-date-preset="month" class="btn btn-secondary btn-sm">This Month</button>
      </div>
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filter</button>
    </form>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

  <!-- Orders by day -->
  <div class="card">
    <div class="card-header"><h2 class="card-title">Orders by Day</h2></div>
    <div class="overflow-x-auto">
      <table class="data-table">
        <thead><tr><th>Date</th><th>Orders</th><th>Revenue</th></tr></thead>
        <tbody>
          <?php foreach ($orderStats as $row): ?>
          <tr>
            <td><?= Helpers::date($row['day']) ?></td>
            <td><span class="badge bg-blue-100 text-blue-700"><?= $row['count'] ?></span></td>
            <td class="font-semibold"><?= Helpers::money($row['revenue']) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($orderStats)): ?>
            <tr><td colspan="3" class="text-center py-6 text-gray-400">No data in selected range.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Top products -->
  <div class="card">
    <div class="card-header"><h2 class="card-title">Top Products (All Time)</h2></div>
    <div class="overflow-x-auto">
      <table class="data-table">
        <thead><tr><th>#</th><th>Product</th><th>Qty Sold</th><th>Revenue</th></tr></thead>
        <tbody>
          <?php foreach ($topProducts as $i => $p): ?>
          <tr>
            <td class="text-gray-400 text-xs"><?= $i+1 ?></td>
            <td><?= h($p['name']) ?></td>
            <td class="font-semibold"><?= Helpers::number($p['qty']) ?></td>
            <td class="font-semibold"><?= Helpers::money($p['revenue']) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($topProducts)): ?>
            <tr><td colspan="4" class="text-center py-6 text-gray-400">No product sales data.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
