<?php $pageTitle = 'Approvals'; ?>
<div class="page-header">
  <div>
    <h1 class="page-title flex items-center gap-2">
      <i class="fa-solid fa-circle-check text-blue-600"></i> Approval Requests Sheet
    </h1>
    <div class="breadcrumb">Admin &rsaquo; Approvals</div>
  </div>
  <span class="badge bg-amber-100 text-amber-800 text-sm px-3 py-1 font-bold">
    <i class="fa-solid fa-clock text-amber-600 mr-1"></i> <?= count(array_filter($items, fn($i) => $i['status']==='pending')) ?> Pending
  </span>
</div>

<div class="excel-container">
  <!-- Excel Ribbon Toolbar -->
  <div class="excel-ribbon">
    <div class="flex items-center gap-3">
      <div class="excel-ribbon-badge">
        <i class="fa-solid fa-file-excel text-blue-200"></i>
        <span>Approvals Master Sheet</span>
      </div>
      <span class="text-xs text-blue-100 hidden sm:inline-block">Total <?= count($items) ?> Requests</span>
    </div>

    <div class="flex items-center gap-2">
      <input type="text" placeholder="Search request..." data-table-search="approvals-table" 
             class="px-3 py-1.5 bg-white/20 text-white placeholder-blue-100 text-xs rounded-lg outline-none border border-white/30 focus:bg-white focus:text-gray-900 focus:placeholder-gray-400 transition">
      <button onclick="exportTableToCSV('approvals-table', 'Approvals_List.csv')" class="excel-action-btn">
        <i class="fa-solid fa-file-csv"></i> Export CSV
      </button>
      <button onclick="printTable('approvals-table', 'Approvals Sheet')" class="excel-action-btn excel-action-btn-secondary">
        <i class="fa-solid fa-print"></i> Print
      </button>
    </div>
  </div>

  <!-- Formula Bar -->
  <div class="excel-formula-bar">
    <span class="fx-symbol">fx</span>
    <div class="excel-pill">
      <i class="fa-solid fa-calculator text-blue-600"></i>
      <span>TOTAL REQUESTS: <strong class="text-blue-700 font-mono"><?= count($items) ?></strong></span>
    </div>
    <div class="excel-pill">
      <i class="fa-solid fa-hourglass-half text-amber-600"></i>
      <span>PENDING APPROVAL: <strong class="text-amber-700 font-mono"><?= count(array_filter($items, fn($i) => $i['status']==='pending')) ?></strong></span>
    </div>
  </div>

  <div class="overflow-x-auto">
    <table class="excel-table" id="approvals-table">
      <thead>
        <tr>
          <th class="excel-row-num">#</th>
          <th>Requested By</th>
          <th>Module</th>
          <th class="text-center">Action Type</th>
          <th class="text-center">Status</th>
          <th>Date</th>
          <th class="text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $i => $a): ?>
        <tr>
          <td class="excel-row-num"><?= $i+1 ?></td>
          <td class="font-bold text-gray-900"><?= h($a['requester_name']) ?></td>
          <td class="text-gray-700"><?= h(ucfirst($a['module'])) ?></td>
          <td class="text-center">
            <span class="badge <?= $a['action']==='delete' ? 'bg-red-100 text-red-700 font-bold' : 'bg-blue-100 text-blue-700 font-bold' ?>">
              <?= ucfirst($a['action']) ?>
            </span>
          </td>
          <td class="text-center"><?= Helpers::statusBadge($a['status']) ?></td>
          <td class="text-gray-500 font-mono text-xs"><?= Helpers::timeAgo($a['created_at']) ?></td>
          <td class="text-center">
            <?php if ($a['status'] === 'pending'): ?>
            <div class="flex items-center justify-center gap-1">
              <form method="POST" action="<?= url('admin/approvals/approve/'.$a['id']) ?>">
                <?= Helpers::csrfField() ?>
                <button class="btn btn-success btn-sm"><i class="fa-solid fa-check"></i> Approve</button>
              </form>
              <form method="POST" action="<?= url('admin/approvals/reject/'.$a['id']) ?>">
                <?= Helpers::csrfField() ?>
                <button class="btn btn-danger btn-sm"><i class="fa-solid fa-xmark"></i> Reject</button>
              </form>
            </div>
            <?php else: ?>
              <span class="text-xs text-gray-400 font-mono">Closed</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($items)): ?>
          <tr><td colspan="7" class="text-center py-8 text-gray-400">No approval requests.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

