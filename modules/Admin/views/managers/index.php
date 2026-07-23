<?php
// Reusable user list table (Managers, SRs, DSRs)
$role       = $role ?? 'manager';
$roleLabel  = $roleLabel ?? 'Manager';
$pageTitle  = $roleLabel . 's';
$createUrl  = url("admin/{$role}s/create");
?>
<div class="page-header">
  <div>
    <h1 class="page-title flex items-center gap-2">
      <i class="fa-solid fa-users-gear text-blue-600"></i> <?= $pageTitle ?> Sheet
    </h1>
    <div class="breadcrumb">Admin &rsaquo; <?= $pageTitle ?></div>
  </div>
  <a href="<?= $createUrl ?>" class="btn btn-primary">
    <i class="fa-solid fa-plus"></i> Add <?= $roleLabel ?>
  </a>
</div>

<div class="excel-container">
  <!-- Excel Ribbon Toolbar -->
  <div class="excel-ribbon">
    <div class="flex items-center gap-3">
      <div class="excel-ribbon-badge">
        <i class="fa-solid fa-file-excel text-blue-200"></i>
        <span><?= $pageTitle ?> Data Sheet</span>
      </div>
      <span class="text-xs text-blue-100 hidden sm:inline-block">Total <?= count($items) ?> Records</span>
    </div>

    <div class="flex items-center gap-2">
      <input type="text" placeholder="Search user..." data-table-search="users-table" 
             class="px-3 py-1.5 bg-white/20 text-white placeholder-blue-100 text-xs rounded-lg outline-none border border-white/30 focus:bg-white focus:text-gray-900 focus:placeholder-gray-400 transition">
      <button onclick="exportTableToCSV('users-table', '<?= $role ?>s_List.csv')" class="excel-action-btn">
        <i class="fa-solid fa-file-csv"></i> Export CSV
      </button>
      <button onclick="printTable('users-table', '<?= $pageTitle ?> Sheet')" class="excel-action-btn excel-action-btn-secondary">
        <i class="fa-solid fa-print"></i> Print
      </button>
    </div>
  </div>

  <!-- Formula Bar -->
  <div class="excel-formula-bar">
    <span class="fx-symbol">fx</span>
    <div class="excel-pill">
      <i class="fa-solid fa-calculator text-blue-600"></i>
      <span>TOTAL <?= strtoupper($pageTitle) ?>: <strong class="text-blue-700 font-mono"><?= count($items) ?></strong></span>
    </div>
  </div>

  <div class="overflow-x-auto">
    <table class="excel-table" id="users-table">
      <thead>
        <tr>
          <th class="excel-row-num">#</th>
          <th>Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th><?= $role === 'sr' ? 'Company' : 'Warehouse' ?></th>
          <?php if ($role === 'sr'): ?><th>Dealer</th><?php endif; ?>
          <th class="text-center">Status</th>
          <th class="text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $i => $u): ?>
        <tr>
          <td class="excel-row-num"><?= $i+1 ?></td>
          <td>
            <div class="flex items-center gap-2">
              <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">
                <?= Helpers::initials($u['name']) ?>
              </div>
              <span class="font-bold text-gray-900"><?= h($u['name']) ?></span>
            </div>
          </td>
          <td class="text-gray-500 font-mono"><?= h($u['email']) ?></td>
          <td class="font-mono text-gray-700"><?= h($u['phone'] ?? '—') ?></td>
          <td class="text-gray-700"><?= $role === 'sr' ? h($u['company_name'] ?? '—') : h($u['warehouse_name'] ?? '—') ?></td>
          <?php if ($role === 'sr'): ?>
          <td class="text-gray-600"><?= h($u['dealer_names'] ?? '—') ?></td>
          <?php endif; ?>
          <td class="text-center"><?= Helpers::statusBadge($u['status'] ? 'active' : 'inactive') ?></td>
          <td class="text-center">
            <div class="flex items-center justify-center gap-1">
              <a href="<?= url("admin/{$role}s/edit/".$u['id']) ?>" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-pen"></i>
              </a>
              <form method="POST" action="<?= url("admin/{$role}s/delete/".$u['id']) ?>" data-confirm-form="Delete this user?">
                <?= Helpers::csrfField() ?>
                <button class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($items)): ?>
          <tr><td colspan="<?= $role === 'sr' ? '8' : '7' ?>" class="text-center py-8 text-gray-400">No <?= strtolower($pageTitle) ?> found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

