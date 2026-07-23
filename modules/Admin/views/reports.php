<?php $pageTitle = 'Sales Reports'; ?>
<div class="page-header">
  <div>
    <h1 class="page-title flex items-center gap-2">
      <i class="fa-solid fa-chart-line text-blue-600"></i> Sales Reports Sheet
    </h1>
    <div class="breadcrumb">Admin &rsaquo; Reports</div>
  </div>
</div>

<!-- Date filter -->
<div class="excel-container mb-6 p-4">
  <form method="GET" class="flex flex-wrap items-end gap-3">
    <div>
      <label class="form-label text-xs font-bold text-gray-600">From Date</label>
      <input type="date" id="date-from" name="from" value="<?= h($from) ?>" class="form-input w-40 text-xs font-mono font-bold">
    </div>
    <div>
      <label class="form-label text-xs font-bold text-gray-600">To Date</label>
      <input type="date" id="date-to" name="to" value="<?= h($to) ?>" class="form-input w-40 text-xs font-mono font-bold">
    </div>
    <div class="flex gap-2">
      <button type="button" data-date-preset="today" class="btn btn-secondary btn-sm"><i class="fa-solid fa-calendar-day mr-1"></i> Today</button>
      <button type="button" data-date-preset="week" class="btn btn-secondary btn-sm"><i class="fa-solid fa-calendar-week mr-1"></i> This Week</button>
      <button type="button" data-date-preset="month" class="btn btn-secondary btn-sm"><i class="fa-solid fa-calendar-days mr-1"></i> This Month</button>
    </div>
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filter Sheet</button>
  </form>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

  <!-- Orders by day -->
  <div class="excel-container">
    <div class="excel-ribbon">
      <div class="flex items-center gap-2">
        <i class="fa-solid fa-file-excel text-blue-200"></i>
        <span class="font-bold text-sm">Orders by Day Sheet</span>
      </div>
      <button onclick="exportTableToCSV('daily-orders-table', 'Daily_Orders_Report.csv')" class="excel-action-btn">
        <i class="fa-solid fa-file-csv"></i> CSV
      </button>
    </div>
    <div class="overflow-x-auto">
      <table class="excel-table" id="daily-orders-table">
        <thead>
          <tr>
            <th class="excel-row-num">#</th>
            <th>Date</th>
            <th class="text-center">Total Orders</th>
            <th class="text-right">Total Revenue</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orderStats as $i => $row): ?>
          <tr>
            <td class="excel-row-num"><?= $i+1 ?></td>
            <td class="font-mono text-gray-800 font-bold"><?= Helpers::date($row['day']) ?></td>
            <td class="text-center"><span class="badge bg-blue-100 text-blue-800 font-bold px-2.5 py-0.5 rounded-full"><?= $row['count'] ?></span></td>
            <td class="excel-money">৳<?= number_format($row['revenue'], 2) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($orderStats)): ?>
            <tr><td colspan="4" class="text-center py-6 text-gray-400">No data in selected range.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Top products -->
  <div class="excel-container">
    <div class="excel-ribbon">
      <div class="flex items-center gap-2">
        <i class="fa-solid fa-file-excel text-blue-200"></i>
        <span class="font-bold text-sm">Top Products Sheet (All Time)</span>
      </div>
      <button onclick="exportTableToCSV('top-products-table', 'Top_Products_Report.csv')" class="excel-action-btn">
        <i class="fa-solid fa-file-csv"></i> CSV
      </button>
    </div>
    <div class="overflow-x-auto">
      <table class="excel-table" id="top-products-table">
        <thead>
          <tr>
            <th class="excel-row-num">#</th>
            <th>Product Name</th>
            <th class="text-center">Qty Sold</th>
            <th class="text-right">Total Revenue</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($topProducts as $i => $p): ?>
          <tr>
            <td class="excel-row-num"><?= $i+1 ?></td>
            <td class="font-bold text-gray-900"><?= h($p['name']) ?></td>
            <td class="excel-qty"><?= Helpers::number($p['qty']) ?></td>
            <td class="excel-money">৳<?= number_format($p['revenue'], 2) ?></td>
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

