<?php
// Reusable user list table
// Variables expected: $items (users), $role (slug), $roleLabel
$role       = $role ?? 'manager';
$roleLabel  = $roleLabel ?? 'Manager';
$pageTitle  = $roleLabel . 's';
$createUrl  = url("admin/{$role}s/create");
?>
<div class="page-header">
  <div>
    <h1 class="page-title"><?= $pageTitle ?></h1>
    <div class="breadcrumb">Admin &rsaquo; <?= $pageTitle ?></div>
  </div>
  <a href="<?= $createUrl ?>" class="btn btn-primary">
    <i class="fa-solid fa-plus"></i> Add <?= $roleLabel ?>
  </a>
</div>

<div class="card">
  <div class="card-header">
    <h2 class="card-title">All <?= $pageTitle ?> (<?= count($items) ?>)</h2>
    <input type="text" placeholder="Search…" data-table-search="users-table"
           class="form-input w-48 text-sm py-1.5">
  </div>
  <div class="overflow-x-auto">
    <table class="data-table" id="users-table">
      <thead>
        <tr>
          <th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Warehouse</th><th>Status</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $i => $u): ?>
        <tr>
          <td class="text-gray-400 text-xs"><?= $i+1 ?></td>
          <td>
            <div class="flex items-center gap-2">
              <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">
                <?= Helpers::initials($u['name']) ?>
              </div>
              <span class="font-medium"><?= h($u['name']) ?></span>
            </div>
          </td>
          <td class="text-gray-500"><?= h($u['email']) ?></td>
          <td><?= h($u['phone'] ?? '—') ?></td>
          <td><?= h($u['warehouse_name'] ?? '—') ?></td>
          <td><?= Helpers::statusBadge($u['status'] ? 'active' : 'inactive') ?></td>
          <td>
            <div class="flex items-center gap-1">
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
          <tr><td colspan="7" class="text-center py-8 text-gray-400">No <?= strtolower($pageTitle) ?> found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
