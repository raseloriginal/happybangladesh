<?php $pageTitle = 'Approvals'; ?>
<div class="page-header">
  <div><h1 class="page-title">Approval Requests</h1><div class="breadcrumb">Admin &rsaquo; Approvals</div></div>
  <span class="badge bg-amber-100 text-amber-700 text-sm px-3 py-1">
    <?= count(array_filter($items, fn($i) => $i['status']==='pending')) ?> Pending
  </span>
</div>

<div class="card">
  <div class="card-header">
    <h2 class="card-title">All Requests (<?= count($items) ?>)</h2>
    <input type="text" placeholder="Search…" data-table-search="approvals-table" class="form-input w-48 text-sm py-1.5">
  </div>
  <div class="overflow-x-auto">
    <table class="data-table" id="approvals-table">
      <thead>
        <tr><th>#</th><th>Requested By</th><th>Module</th><th>Action</th><th>Status</th><th>Date</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($items as $i => $a): ?>
        <tr>
          <td class="text-gray-400 text-xs"><?= $i+1 ?></td>
          <td class="font-medium"><?= h($a['requester_name']) ?></td>
          <td><?= h(ucfirst($a['module'])) ?></td>
          <td>
            <span class="badge <?= $a['action']==='delete' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' ?>">
              <?= ucfirst($a['action']) ?>
            </span>
          </td>
          <td><?= Helpers::statusBadge($a['status']) ?></td>
          <td class="text-gray-400 text-xs"><?= Helpers::timeAgo($a['created_at']) ?></td>
          <td>
            <?php if ($a['status'] === 'pending'): ?>
            <div class="flex items-center gap-1">
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
              <span class="text-xs text-gray-400">Closed</span>
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
