<?php $pageTitle = 'Warehouses'; ?>
<div class="page-header">
  <div>
    <h1 class="page-title">Warehouses</h1>
    <div class="breadcrumb">Admin &rsaquo; Warehouses</div>
  </div>
  <a href="<?= url('admin/warehouses/create') ?>" class="btn btn-primary">
    <i class="fa-solid fa-plus"></i> Add Warehouse
  </a>
</div>

<div class="card">
  <div class="card-header">
    <h2 class="card-title">All Warehouses (<?= count($items) ?>)</h2>
    <input type="text" placeholder="Search warehouses…" data-table-search="warehouses-table"
           class="form-input w-48 text-sm py-1.5">
  </div>
  <div class="overflow-x-auto">
    <table class="data-table" id="warehouses-table">
      <thead>
        <tr>
          <th>#</th><th>Name</th><th>Location</th><th>Phone</th><th>Status</th><th>Created</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $i => $w): ?>
        <tr>
          <td class="text-gray-400 text-xs"><?= $i+1 ?></td>
          <td class="font-semibold"><?= h($w['name']) ?></td>
          <td><?= h($w['location']) ?></td>
          <td><?= h($w['phone'] ?? '—') ?></td>
          <td><?= Helpers::statusBadge($w['status'] ? 'active' : 'inactive') ?></td>
          <td class="text-gray-400 text-xs"><?= Helpers::date($w['created_at']) ?></td>
          <td>
            <div class="flex items-center gap-1">
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
