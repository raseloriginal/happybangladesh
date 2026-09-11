<?php $pageTitle = 'Inventory'; ?>
<div class="page-header">
  <div><h1 class="page-title">Inventory Overview</h1><div class="breadcrumb">Manager &rsaquo; Inventory</div></div>
</div>

<div class="card">
  <div class="card-header">
    <h2 class="card-title">Stock Levels (<?= count($items) ?> records)</h2>
    <input type="text" placeholder="Search inventory…" data-table-search="inv-table" class="form-input w-48 text-sm py-1.5">
  </div>
  <div class="overflow-x-auto">
    <table class="data-table" id="inv-table">
      <thead>
        <tr><th>#</th><th>Product</th><th>SKU</th><th>Lot</th><th>Warehouse</th><th>Available Boxes</th><th>Available Pieces</th></tr>
      </thead>
      <tbody>
        <?php foreach ($items as $i => $inv):
          $lowStock = ($inv['qty_boxes'] == 0 && $inv['qty_pieces'] < 10);
        ?>
        <tr>
          <td class="text-gray-400 text-xs"><?= $i+1 ?></td>
          <td class="font-medium"><?= h($inv['product_name']) ?></td>
          <td class="font-mono text-xs text-gray-400"><?= h($inv['sku']) ?></td>
          <td class="font-mono text-xs"><?= h($inv['lot_number'] ?? '—') ?></td>
          <td><?= h($inv['warehouse_name']) ?></td>
          <td>
            <span class="font-semibold <?= $lowStock ? 'text-red-600' : 'text-gray-800' ?>">
              <?= Helpers::number($inv['qty_boxes']) ?>
            </span>
          </td>
          <td>
            <span class="font-semibold <?= $lowStock ? 'text-red-600' : 'text-gray-800' ?>">
              <?= Helpers::number($inv['qty_pieces']) ?>
            </span>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($items)): ?><tr><td colspan="7" class="text-center py-8 text-gray-400">No inventory data.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
