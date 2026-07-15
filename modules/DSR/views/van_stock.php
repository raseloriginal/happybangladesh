<?php $pageTitle = 'Van Stock'; ?>
<div class="page-header">
  <div><h1 class="page-title">Van Stock</h1><div class="breadcrumb">DSR &rsaquo; Van Stock</div></div>
</div>

<div class="card">
  <div class="card-header">
    <h2 class="card-title">My Van Stock (<?= count($items) ?> items)</h2>
    <input type="text" placeholder="Search…" data-table-search="van-table" class="form-input w-48 text-sm py-1.5">
  </div>
  <div class="overflow-x-auto">
    <table class="data-table" id="van-table">
      <thead>
        <tr><th>#</th><th>Product</th><th>SKU</th><th>Lot</th><th>Expiry</th><th>Qty</th><th>Loaded</th></tr>
      </thead>
      <tbody>
        <?php foreach ($items as $i => $vs):
          $expired  = !empty($vs['expiry_date']) && strtotime($vs['expiry_date']) < time();
          $expiring = !$expired && !empty($vs['expiry_date']) && strtotime($vs['expiry_date']) < strtotime('+30 days');
        ?>
        <tr>
          <td class="text-gray-400 text-xs"><?= $i+1 ?></td>
          <td class="font-medium"><?= h($vs['product_name']) ?></td>
          <td class="font-mono text-xs text-gray-400"><?= h($vs['sku']) ?></td>
          <td class="font-mono text-xs"><?= h($vs['lot_number'] ?? '—') ?></td>
          <td>
            <?php if ($expired): ?>
              <span class="badge bg-red-100 text-red-700">EXPIRED</span>
            <?php elseif ($expiring): ?>
              <span class="badge bg-amber-100 text-amber-700">Expiring Soon</span>
            <?php else: ?>
              <span class="text-sm text-gray-600"><?= Helpers::date($vs['expiry_date'] ?? '') ?></span>
            <?php endif; ?>
          </td>
          <td>
            <span class="font-bold text-lg <?= $vs['quantity'] <= 5 ? 'text-red-600' : 'text-gray-800' ?>">
              <?= Helpers::number($vs['quantity']) ?>
            </span>
          </td>
          <td class="text-gray-400 text-xs"><?= Helpers::date($vs['loaded_at'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($items)): ?><tr><td colspan="7" class="text-center py-8 text-gray-400">No stock in your van.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
