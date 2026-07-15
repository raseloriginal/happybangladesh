<?php $pageTitle = 'Companies'; ?>
<div class="page-header">
  <div>
    <h1 class="page-title">Companies</h1>
    <div class="breadcrumb">Admin &rsaquo; Companies</div>
  </div>
  <a href="<?= url('admin/companies/create') ?>" class="btn btn-primary">
    <i class="fa-solid fa-plus"></i> Add Company
  </a>
</div>

<div class="card">
  <div class="card-header">
    <h2 class="card-title">All Companies (<?= count($items) ?>)</h2>
    <input type="text" placeholder="Search…" data-table-search="companies-table" class="form-input w-48 text-sm py-1.5">
  </div>
  <div class="overflow-x-auto">
    <table class="data-table" id="companies-table">
      <thead>
        <tr><th>#</th><th>Name</th><th>Contact</th><th>Email</th><th>Phone</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($items as $i => $c): ?>
        <tr>
          <td class="text-gray-400 text-xs"><?= $i+1 ?></td>
          <td class="font-semibold"><?= h($c['name']) ?></td>
          <td><?= h($c['contact'] ?? '—') ?></td>
          <td class="text-gray-500"><?= h($c['email'] ?? '—') ?></td>
          <td><?= h($c['phone'] ?? '—') ?></td>
          <td><?= Helpers::statusBadge($c['status'] ? 'active' : 'inactive') ?></td>
          <td>
            <div class="flex items-center gap-1">
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
