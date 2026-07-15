<?php $pageTitle = 'Dealers'; ?>
<div class="page-header">
  <div><h1 class="page-title">Dealers</h1><div class="breadcrumb">Admin &rsaquo; Dealers</div></div>
  <a href="<?= url('admin/dealers/create') ?>" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add Dealer</a>
</div>

<div class="card">
  <div class="card-header">
    <h2 class="card-title">All Dealers (<?= count($items) ?>)</h2>
    <input type="text" placeholder="Search…" data-table-search="dealers-table" class="form-input w-48 text-sm py-1.5">
  </div>
  <div class="overflow-x-auto">
    <table class="data-table" id="dealers-table">
      <thead>
        <tr><th>#</th><th>Dealer Name</th><th>Business Name</th><th>Warehouse</th><th>Phone</th><th>Happy Comm.</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($items as $i => $d): ?>
        <tr>
          <td class="text-gray-400 text-xs"><?= $i+1 ?></td>
          <td class="font-semibold"><?= h($d['name']) ?></td>
          <td><?= h($d['business_name'] ?? '—') ?></td>
          <td class="text-gray-500"><?= h($d['warehouse_name'] ?? '—') ?></td>
          <td><?= h($d['phone'] ?? '—') ?></td>
          <td class="font-semibold"><?= $d['happy_commission'] ?>%</td>
          <td><?= Helpers::statusBadge($d['status'] ? 'active' : 'inactive') ?></td>
          <td>
            <div class="flex items-center gap-1">
              <a href="<?= url('admin/dealers/edit/'.$d['id']) ?>" class="btn btn-secondary btn-sm"><i class="fa-solid fa-pen"></i></a>
              <form method="POST" action="<?= url('admin/dealers/delete/'.$d['id']) ?>" data-confirm-form="Delete this dealer?">
                <?= Helpers::csrfField() ?><button class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($items)): ?><tr><td colspan="8" class="text-center py-8 text-gray-400">No dealers found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
