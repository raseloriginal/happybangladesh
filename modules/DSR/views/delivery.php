<?php $pageTitle = 'Deliveries'; ?>
<div class="page-header">
  <div><h1 class="page-title">Delivery Management</h1><div class="breadcrumb">DSR &rsaquo; Deliveries</div></div>
</div>

<div class="card">
  <div class="card-header">
    <h2 class="card-title">My Deliveries (<?= count($items) ?>)</h2>
    <input type="text" placeholder="Search…" data-table-search="del-table" class="form-input w-48 text-sm py-1.5">
  </div>
  <div class="overflow-x-auto">
    <table class="data-table" id="del-table">
      <thead>
        <tr><th>#</th><th>Order</th><th>Dispatch Date</th><th>Status</th><th>Amount</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($items as $i => $d): ?>
        <tr>
          <td class="text-gray-400 text-xs"><?= $i+1 ?></td>
          <td><?= $d['order_id'] ? '#'.$d['order_id'] : 'N/A' ?></td>
          <td><?= Helpers::date($d['dispatch_date']) ?></td>
          <td><?= Helpers::statusBadge($d['status']) ?></td>
          <td><?= $d['total_amount'] ? Helpers::money($d['total_amount']) : '—' ?></td>
          <td>
            <?php if (in_array($d['status'], ['pending', 'in_transit'])): ?>
            <div class="flex items-center gap-1">
              <!-- Update status form -->
              <form method="POST" action="<?= url('dsr/delivery/update/'.$d['id']) ?>" class="flex gap-1">
                <?= Helpers::csrfField() ?>
                <select name="status" class="form-input text-xs py-1 w-32">
                  <option value="in_transit" <?= $d['status']==='in_transit'?'selected':'' ?>>In Transit</option>
                  <option value="delivered"  <?= $d['status']==='delivered'?'selected':'' ?>>Delivered</option>
                  <option value="partial"    <?= $d['status']==='partial'?'selected':'' ?>>Partial</option>
                  <option value="returned"   <?= $d['status']==='returned'?'selected':'' ?>>Returned</option>
                </select>
                <button class="btn btn-primary btn-sm"><i class="fa-solid fa-floppy-disk"></i></button>
              </form>
            </div>
            <?php else: ?>
              <span class="text-xs text-gray-400">Closed</span>
            <?php endif; ?>
          </td>
        </tr>
        <!-- Expandable notes -->
        <?php if ($d['order_notes'] ?? false): ?>
        <tr>
          <td colspan="6" class="px-5 py-2 bg-gray-50 text-xs text-gray-500">
            <i class="fa-solid fa-note-sticky mr-1"></i><?= h($d['order_notes']) ?>
          </td>
        </tr>
        <?php endif; ?>
        <?php endforeach; ?>
        <?php if (empty($items)): ?><tr><td colspan="6" class="text-center py-8 text-gray-400">No deliveries found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
