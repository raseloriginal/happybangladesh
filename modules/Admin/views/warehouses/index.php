<?php $pageTitle = 'Warehouses'; ?>
<div class="page-header">
  <div>
    <h1 class="page-title flex items-center gap-2">
      <i class="fa-solid fa-warehouse text-blue-600"></i> Warehouses Sheet
    </h1>
    <div class="breadcrumb">Admin &rsaquo; Warehouses</div>
  </div>
  <a href="<?= url('admin/warehouses/create') ?>" class="btn btn-primary">
    <i class="fa-solid fa-plus"></i> Add Warehouse
  </a>
</div>

<div class="excel-container">
  <!-- Excel Ribbon Toolbar -->
  <div class="excel-ribbon">
    <div class="flex items-center gap-3">
      <div class="excel-ribbon-badge">
        <i class="fa-solid fa-file-excel text-blue-200"></i>
        <span>Warehouses Master Sheet</span>
      </div>
      <span class="text-xs text-blue-100 hidden sm:inline-block">Total <?= count($items) ?> Locations</span>
    </div>

    <div class="flex items-center gap-2">
      <input type="text" placeholder="Search warehouses..." data-table-search="warehouses-table" 
             class="px-3 py-1.5 bg-white/20 text-white placeholder-blue-100 text-xs rounded-lg outline-none border border-white/30 focus:bg-white focus:text-gray-900 focus:placeholder-gray-400 transition">
      <button onclick="exportTableToCSV('warehouses-table', 'Warehouses_List.csv')" class="excel-action-btn">
        <i class="fa-solid fa-file-csv"></i> Export CSV
      </button>
      <button onclick="printTable('warehouses-table', 'Warehouses Sheet')" class="excel-action-btn excel-action-btn-secondary">
        <i class="fa-solid fa-print"></i> Print
      </button>
    </div>
  </div>

  <!-- Formula Bar -->
  <div class="excel-formula-bar">
    <span class="fx-symbol">fx</span>
    <div class="excel-pill">
      <i class="fa-solid fa-calculator text-blue-600"></i>
      <span>TOTAL WAREHOUSES: <strong class="text-blue-700 font-mono"><?= count($items) ?></strong></span>
    </div>
  </div>

  <div class="overflow-x-auto">
    <table class="excel-table" id="warehouses-table">
      <thead>
        <tr>
          <th class="excel-row-num">#</th>
          <th>Warehouse Name</th>
          <th>Location</th>
          <th>Phone Number</th>
          <th class="text-center">Status</th>
          <th>Created Date</th>
          <th class="text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $i => $w): ?>
        <tr>
          <td class="excel-row-num"><?= $i+1 ?></td>
          <td class="font-bold text-gray-900"><?= h($w['name']) ?></td>
          <td class="text-gray-700"><?= h($w['location']) ?></td>
          <td class="font-mono text-gray-700"><?= h($w['phone'] ?? '—') ?></td>
          <td class="text-center"><?= Helpers::statusBadge($w['status'] ? 'active' : 'inactive') ?></td>
          <td class="text-gray-500 font-mono text-xs"><?= Helpers::date($w['created_at']) ?></td>
          <td class="text-center">
            <div class="flex items-center justify-center gap-1">
              <a href="<?= url('admin/warehouses/edit/'.$w['id']) ?>" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-pen"></i>
              </a>
              <form method="POST" action="<?= url('admin/warehouses/delete/'.$w['id']) ?>" data-confirm-form="Delete this warehouse?">
                <?= Helpers::csrfField() ?>
                <button class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($items)): ?>
          <tr><td colspan="7" class="text-center py-8 text-gray-400">No warehouses found. <a href="<?= url('admin/warehouses/create') ?>" class="text-blue-600">Add one</a>.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

