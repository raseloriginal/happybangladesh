<?php $pageTitle = 'My Orders'; ?>

<!-- ── Page Header ─────────────────────────────────────────── -->
<div class="sr-page-header" style="padding-bottom:24px;">
  <div style="font-size:0.75rem;color:rgba(255,255,255,0.6);margin-bottom:4px;">
    <i class="fa-solid fa-house" style="margin-right:4px;"></i>Home › Orders
  </div>
  <div style="display:flex;align-items:center;justify-content:space-between;">
    <div>
      <div class="sr-page-title">My Orders</div>
      <div class="sr-page-sub"><?= count($items) ?> total order<?= count($items)!==1?'s':'' ?></div>
    </div>
    <a href="<?= url('sr/sales') ?>"
       style="background:rgba(255,255,255,0.2);color:#fff;border-radius:12px;padding:10px 16px;text-decoration:none;font-size:0.8rem;font-weight:600;display:inline-flex;align-items:center;gap:6px;">
      <i class="fa-solid fa-plus"></i> New Sale
    </a>
  </div>
</div>

<!-- ── Filter Tabs ─────────────────────────────────────────── -->
<div style="display:flex;gap:8px;padding:12px 16px;overflow-x:auto;scrollbar-width:none;">
  <?php
    $statuses = ['all'=>'All','pending'=>'Pending','confirmed'=>'Confirmed','delivered'=>'Delivered','rejected'=>'Rejected'];
    $activeStatus = $_GET['status'] ?? 'all';
  ?>
  <?php foreach ($statuses as $key => $label): ?>
  <a href="?status=<?= $key ?>"
     style="flex-shrink:0;padding:6px 16px;border-radius:999px;font-size:0.75rem;font-weight:600;text-decoration:none;
            <?= $activeStatus===$key ? 'background:var(--sr-primary);color:#fff;' : 'background:#fff;color:var(--sr-text-muted);' ?>">
    <?= $label ?>
  </a>
  <?php endforeach; ?>
</div>

<!-- ── Orders List ─────────────────────────────────────────── -->
<div class="sr-orders-wrap">
  <?php
    $filtered = $items;
    if ($activeStatus !== 'all') {
      $filtered = array_filter($items, fn($o) => $o['status'] === $activeStatus);
    }
  ?>

  <?php if (empty($filtered)): ?>
  <div style="text-align:center;padding:60px 20px;color:var(--sr-text-muted);">
    <i class="fa-solid fa-inbox" style="font-size:3rem;opacity:.25;display:block;margin-bottom:12px;"></i>
    <div style="font-weight:600;font-size:1rem;color:var(--sr-text);margin-bottom:6px;">No orders here</div>
    <div style="font-size:0.85rem;">Start selling to see orders appear here.</div>
    <a href="<?= url('sr/sales') ?>"
       style="display:inline-flex;align-items:center;gap:6px;margin-top:16px;background:var(--sr-primary);color:#fff;padding:10px 20px;border-radius:12px;text-decoration:none;font-weight:600;font-size:0.875rem;">
      <i class="fa-solid fa-map-location-dot"></i> Go to Sales Map
    </a>
  </div>
  <?php endif; ?>

  <?php foreach ($filtered as $o):
    $statusColors = [
      'pending'   => ['bg'=>'#fef3c7','color'=>'#d97706','icon'=>'fa-clock'],
      'confirmed' => ['bg'=>'#d1fae5','color'=>'#059669','icon'=>'fa-circle-check'],
      'delivered' => ['bg'=>'#dbeafe','color'=>'#2563eb','icon'=>'fa-truck'],
      'rejected'  => ['bg'=>'#fee2e2','color'=>'#dc2626','icon'=>'fa-circle-xmark'],
    ];
    $sc = $statusColors[$o['status']] ?? ['bg'=>'#f1f5f9','color'=>'#64748b','icon'=>'fa-circle'];
  ?>
  <div class="sr-order-card" onclick="toggleOrderDetail(<?= $o['id'] ?>)" style="cursor:pointer;">
    <div class="sr-order-card-icon">
      <i class="fa-solid fa-bag-shopping"></i>
    </div>
    <div class="sr-order-card-body">
      <div class="sr-order-card-dealer"><?= h($o['dealer_name'] ?? 'Direct Sale') ?></div>
      <div class="sr-order-card-meta">
        <i class="fa-solid fa-warehouse" style="font-size:0.65rem;margin-right:3px;"></i><?= h($o['warehouse_name'] ?? '—') ?>
        &nbsp;·&nbsp;
        <i class="fa-regular fa-calendar" style="font-size:0.65rem;margin-right:3px;"></i><?= Helpers::date($o['created_at']) ?>
      </div>
    </div>
    <div class="sr-order-card-right">
      <div class="sr-order-card-amount"><?= Helpers::money($o['total_amount']) ?></div>
      <span style="display:inline-flex;align-items:center;gap:3px;font-size:0.65rem;font-weight:600;padding:2px 8px;border-radius:999px;background:<?= $sc['bg'] ?>;color:<?= $sc['color'] ?>;">
        <i class="fa-solid <?= $sc['icon'] ?>" style="font-size:0.6rem;"></i>
        <?= ucfirst($o['status']) ?>
      </span>
    </div>
  </div>

  <!-- Expandable detail -->
  <div id="order-detail-<?= $o['id'] ?>" style="display:none;margin:-8px 0 12px;background:#fff;border-radius:0 0 12px 12px;padding:12px 16px;box-shadow:0 4px 12px rgba(0,0,0,.06);">
    <div style="font-size:0.8rem;color:var(--sr-text-muted);">
      <span style="font-weight:600;color:var(--sr-text);">Order #<?= $o['id'] ?></span>
      &nbsp;·&nbsp;
      <?= Helpers::datetime($o['created_at']) ?>
    </div>
    <?php if (!empty($o['notes'])): ?>
    <div style="font-size:0.8rem;color:var(--sr-text-muted);margin-top:6px;">
      <i class="fa-solid fa-note-sticky" style="color:var(--sr-warning);margin-right:4px;"></i><?= h($o['notes']) ?>
    </div>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>

<!-- Bottom spacing -->
<div style="height:16px;"></div>

<script>
function toggleOrderDetail(id) {
  const el = document.getElementById('order-detail-' + id);
  if (el) el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>
