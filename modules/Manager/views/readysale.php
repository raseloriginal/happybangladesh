<?php $pageTitle = 'Ready Sale'; ?>
<div class="page-header">
  <div><h1 class="page-title">Ready Sale</h1><div class="breadcrumb">Manager &rsaquo; Ready Sale</div></div>
  <button data-modal-open="add-readysale-modal" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add Record</button>
</div>

<div class="card">
  <div class="card-header">
    <h2 class="card-title">Ready Sale Records (<?= count($items) ?>)</h2>
    <input type="text" placeholder="Search…" data-table-search="rs-table" class="form-input w-48 text-sm py-1.5">
  </div>
  <div class="overflow-x-auto">
    <table class="data-table" id="rs-table">
      <thead>
        <tr><th>#</th><th>Product</th><th>Lot</th><th>Warehouse</th><th>Qty</th><th>Price</th><th>Status</th></tr>
      </thead>
      <tbody>
        <?php foreach ($items as $i => $rs): ?>
        <tr>
          <td class="text-gray-400 text-xs"><?= $i+1 ?></td>
          <td class="font-medium"><?= h($rs['product_name']) ?></td>
          <td class="font-mono text-xs"><?= h($rs['lot_number'] ?? '—') ?></td>
          <td><?= h($rs['warehouse_name']) ?></td>
          <td class="font-semibold"><?= Helpers::number($rs['quantity']) ?></td>
          <td><?= Helpers::money($rs['price']) ?></td>
          <td><?= Helpers::statusBadge($rs['status'] ? 'active' : 'inactive') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($items)): ?><tr><td colspan="7" class="text-center py-8 text-gray-400">No ready sale records.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add Modal -->
<div id="add-readysale-modal" class="modal-overlay hidden">
  <div class="modal-box">
    <div class="flex items-center justify-between px-6 py-4 border-b">
      <h3 class="text-base font-semibold">Add Ready Sale Record</h3>
      <button data-modal-close="add-readysale-modal" class="text-gray-400 hover:text-gray-700"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="p-6">
      <form method="POST" action="<?= url('manager/readysale/store') ?>">
        <?= Helpers::csrfField() ?>
        <div class="form-group">
          <label class="form-label">Warehouse</label>
          <select name="warehouse_id" required class="form-input">
            <option value="">— Select —</option>
            <?php foreach ($warehouses as $w): ?>
              <option value="<?= $w['id'] ?>"><?= h($w['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Product</label>
          <select name="product_id" required class="form-input">
            <option value="">— Select —</option>
            <?php foreach ($products as $p): ?>
              <option value="<?= $p['id'] ?>"><?= h($p['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Lot</label>
          <select name="lot_id" class="form-input">
            <option value="">— No Lot —</option>
            <?php foreach ($lots as $l): ?>
              <option value="<?= $l['id'] ?>"><?= h($l['product_name']) ?> — <?= h($l['lot_number']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div class="form-group">
            <label class="form-label">Quantity</label>
            <input type="number" name="quantity" min="1" required class="form-input" value="1">
          </div>
          <div class="form-group">
            <label class="form-label">Price (৳)</label>
            <input type="number" name="price" min="0" step="0.01" required class="form-input" value="0.00">
          </div>
        </div>
        <div class="flex gap-3 pt-2">
          <button type="submit" class="btn btn-primary flex-1"><i class="fa-solid fa-plus"></i> Add</button>
          <button type="button" data-modal-close="add-readysale-modal" class="btn btn-secondary">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>
