<?php $pageTitle = 'Companies'; ?>
<div class="page-header">
  <div>
    <h1 class="page-title flex items-center gap-2">
      <i class="fa-solid fa-building text-blue-600"></i> Companies Sheet
    </h1>
    <div class="breadcrumb">Admin &rsaquo; Companies</div>
  </div>
  <a href="<?= url('admin/companies/create') ?>" class="btn btn-primary">
    <i class="fa-solid fa-plus"></i> Add Company
  </a>
</div>

<div class="excel-container">
  <!-- Excel Ribbon Toolbar -->
  <div class="excel-ribbon">
    <div class="flex items-center gap-3">
      <div class="excel-ribbon-badge">
        <i class="fa-solid fa-file-excel text-blue-200"></i>
        <span>Companies Data Sheet</span>
      </div>
      <span class="text-xs text-blue-100 hidden sm:inline-block">Total <?= count($items) ?> Records</span>
    </div>

    <div class="flex items-center gap-2">
      <input type="text" placeholder="Search company..." data-table-search="companies-table" 
             class="px-3 py-1.5 bg-white/20 text-white placeholder-blue-100 text-xs rounded-lg outline-none border border-white/30 focus:bg-white focus:text-gray-900 focus:placeholder-gray-400 transition">
      <button onclick="exportTableToCSV('companies-table', 'Companies_List.csv')" class="excel-action-btn">
        <i class="fa-solid fa-file-csv"></i> Export CSV
      </button>
      <button onclick="printTable('companies-table', 'Companies Sheet')" class="excel-action-btn excel-action-btn-secondary">
        <i class="fa-solid fa-print"></i> Print
      </button>
    </div>
  </div>

  <!-- Formula Bar -->
  <div class="excel-formula-bar">
    <span class="fx-symbol">fx</span>
    <div class="excel-pill">
      <i class="fa-solid fa-calculator text-blue-600"></i>
      <span>TOTAL COMPANIES: <strong class="text-blue-700 font-mono"><?= count($items) ?></strong></span>
    </div>
  </div>

  <div class="overflow-x-auto">
    <table class="excel-table" id="companies-table">
      <thead>
        <tr>
          <th class="excel-row-num">#</th>
          <th>Company Name</th>
          <th>Contact Person</th>
          <th>Email Address</th>
          <th>Phone Number</th>
          <th class="text-center">Status</th>
          <th class="text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $i => $c): ?>
        <tr>
          <td class="excel-row-num"><?= $i+1 ?></td>
          <td class="font-bold text-gray-900"><?= h($c['name']) ?></td>
          <td class="text-gray-700"><?= h($c['contact'] ?? '—') ?></td>
          <td class="text-gray-500 font-mono"><?= h($c['email'] ?? '—') ?></td>
          <td class="font-mono text-gray-700"><?= h($c['phone'] ?? '—') ?></td>
          <td class="text-center"><?= Helpers::statusBadge($c['status'] ? 'active' : 'inactive') ?></td>
          <td class="text-center">
            <div class="flex items-center justify-center gap-1">
              <a href="<?= url('admin/companies/edit/'.$c['id']) ?>" class="btn btn-secondary btn-sm"><i class="fa-solid fa-pen"></i></a>
              <form method="POST" action="<?= url('admin/companies/delete/'.$c['id']) ?>" data-confirm-form="Delete this company?">
                <?= Helpers::csrfField() ?><button class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($items)): ?><tr><td colspan="7" class="text-center py-8 text-gray-400">No companies found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

