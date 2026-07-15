<?php $pageTitle = 'Returns'; ?>
<div class="page-header">
  <div><h1 class="page-title">Returns</h1><div class="breadcrumb">Manager &rsaquo; Returns</div></div>
</div>

<div class="card">
  <div class="card-header">
    <h2 class="card-title">All Returns (<?= count($items) ?>)</h2>
    <input type="text" placeholder="Search…" data-table-search="returns-table" class="form-input w-48 text-sm py-1.5">
  </div>
  <div class="overflow-x-auto">
    <table class="data-table" id="returns-table">
      <thead>
        <tr><th>#</th><th>DSR</th><th>Return Date</th><th>Reason</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($items as $i => $r): ?>
        <tr>
          <td class="text-gray-400 text-xs"><?= $i+1 ?></td>
          <td class="font-medium"><?= h($r['dsr_name'] ?? '—') ?></td>
          <td><?= Helpers::date($r['return_date']) ?></td>
          <td class="text-gray-500 text-sm max-w-48 truncate"><?= h($r['reason'] ?? '—') ?></td>
          <td><?= Helpers::statusBadge($r['status']) ?></td>
          <td>
            <?php if ($r['status'] === 'pending'): ?>
            <form method="POST" action="<?= url('manager/returns/approve/'.$r['id']) ?>">
              <?= Helpers::csrfField() ?>
              <button class="btn btn-success btn-sm"><i class="fa-solid fa-check"></i> Approve</button>
            </form>
            <?php else: ?>
              <span class="text-xs text-gray-400">Closed</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($items)): ?><tr><td colspan="6" class="text-center py-8 text-gray-400">No returns found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
