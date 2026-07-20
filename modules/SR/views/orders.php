<?php $pageTitle = 'My Orders'; ?>

<!-- ── Page Header ─────────────────────────────────────────── -->
<div class="sr-page-header" style="padding-bottom:24px;">
  <div style="font-size:0.75rem;color:rgba(255,255,255,0.6);margin-bottom:4px;">
    <i class="fa-solid fa-house" style="margin-right:4px;"></i>Home › Orders
  </div>
  <div style="display:flex;align-items:center;justify-content:space-between;">
    <div style="display:flex;align-items:center;gap:12px;">
      <a href="<?= url('sr/dashboard') ?>" style="color:#fff;font-size:1.1rem;display:inline-flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.15);width:38px;height:38px;border-radius:50%;text-decoration:none;transition:background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
        <i class="fa-solid fa-arrow-left"></i>
      </a>
      <div>
        <div class="sr-page-title">My Orders</div>
        <div class="sr-page-sub"><?= count($items) ?> total order<?= count($items)!==1?'s':'' ?></div>
      </div>
    </div>
    <a href="<?= url('sr/sales') ?>"
       style="background:rgba(255,255,255,0.2);color:#fff;border-radius:12px;padding:10px 16px;text-decoration:none;font-size:0.8rem;font-weight:600;display:inline-flex;align-items:center;gap:6px;">
      <i class="fa-solid fa-plus"></i> New Sale
    </a>
  </div>
</div>

<!-- ── Orders Summary (New) ────────────────────────────────── -->
<div style="padding: 0 16px 16px 16px;">
  <div style="background: white; border-radius: 16px; padding: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #f1f5f9;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
      <h3 style="margin: 0; font-size: 0.9rem; font-weight: 700; color: var(--sr-text);">
        <i class="fa-solid fa-chart-pie" style="color: var(--sr-primary); margin-right: 6px;"></i> Order Summary (Today)
      </h3>
      <div style="background: #eff6ff; color: #2563eb; padding: 4px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 700;">
        <i class="fa-solid fa-store" style="margin-right: 4px;"></i> <?= $retailerCount ?? 0 ?> Retailers
      </div>
    </div>
    
    <?php if (empty($productSummary)): ?>
      <div style="text-align: center; color: #94a3b8; font-size: 0.8rem; padding: 10px 0;">No products ordered yet.</div>
    <?php else: ?>
      <div style="max-height: 150px; overflow-y: auto; border: 1px solid #f1f5f9; border-radius: 8px;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.75rem;">
          <thead style="background: #f8fafc; position: sticky; top: 0;">
            <tr>
              <th style="padding: 8px; text-align: left; color: #64748b; font-weight: 600; border-bottom: 1px solid #e2e8f0;">Product</th>
              <th style="padding: 8px; text-align: right; color: #64748b; font-weight: 600; border-bottom: 1px solid #e2e8f0;">Qty</th>
              <th style="padding: 8px; text-align: right; color: #64748b; font-weight: 600; border-bottom: 1px solid #e2e8f0;">Value</th>
            </tr>
          </thead>
          <tbody>
            <?php 
              $grandTotalQty = 0;
              $grandTotalVal = 0;
              foreach ($productSummary as $ps): 
                $grandTotalQty += $ps['qty'];
                $grandTotalVal += $ps['total_val'];
                $boxes = floor($ps['qty'] / $ps['ppb']);
                $pcs = $ps['qty'] % $ps['ppb'];
                $qtyStr = ($boxes > 0 ? $boxes . 'B ' : '') . ($pcs > 0 || $boxes == 0 ? $pcs . 'P' : '');
            ?>
            <tr style="border-bottom: 1px solid #f1f5f9;">
              <td style="padding: 8px; font-weight: 600; color: #334155;"><?= h($ps['name']) ?></td>
              <td style="padding: 8px; text-align: right; color: #475569;"><span style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-weight: 700;"><?= $qtyStr ?></span></td>
              <td style="padding: 8px; text-align: right; font-weight: 700; color: #0f172a;">৳<?= number_format($ps['total_val'], 0) ?></td>
            </tr>
            <?php endforeach; ?>
            <tr style="background: #f8fafc;">
              <td style="padding: 8px; font-weight: 700; color: #0f172a;">Total</td>
              <td style="padding: 8px; text-align: right; font-weight: 700; color: #2563eb;"><?= $grandTotalQty ?> Units</td>
              <td style="padding: 8px; text-align: right; font-weight: 700; color: #ef4444;">৳<?= number_format($grandTotalVal, 0) ?></td>
            </tr>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
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
  <div id="order-detail-<?= $o['id'] ?>" style="display:none;margin:-8px 0 12px;background:#fff;border-radius:0 0 12px 12px;padding:12px 16px;box-shadow:0 4px 12px rgba(0,0,0,.06);border-top:1px solid #f1f5f9;">
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

    <div style="margin-top:12px;border-top:1px dashed #e2e8f0;padding-top:12px;">
      <div style="font-size:0.72rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Products Ordered</div>
      <div style="display:flex;flex-direction:column;gap:10px;">
        <?php 
          $total_oc = 0;
          foreach ($o['products'] as $p): 
            $ppb = (int)($p['pieces_per_box'] ?: 1);
            $qty = (int)$p['quantity'];
            $boxes = floor($qty / $ppb);
            $pcs = $qty % $ppb;
            
            $base_price = (float)$p['base_price'];
            $unit_price = (float)$p['unit_price'];
            $item_oc = ($unit_price - $base_price) * $qty;
            $total_oc += $item_oc;
        ?>
          <div style="display:flex;justify-content:space-between;align-items:center;font-size:0.8rem;border-bottom:1px solid #f8fafc;padding-bottom:6px;">
            <div style="flex:1;font-weight:600;color:#334155;padding-right:12px;">
              <?= h($p['product_name']) ?>
              <?php if ($item_oc != 0): ?>
                <span style="font-size:0.65rem;font-weight:700;color:<?= $item_oc < 0 ? '#dc2626' : '#16a34a' ?>;background:<?= $item_oc < 0 ? '#fee2e2' : '#d1fae5' ?>;padding:1px 6px;border-radius:4px;margin-left:6px;display:inline-block;vertical-align:middle;">
                  O/C <?= $item_oc > 0 ? '+' : '' ?><?= round($item_oc) ?>
                </span>
              <?php endif; ?>
            </div>
            <div style="display:flex;align-items:center;gap:14px;flex-shrink:0;">
              <div style="font-size:0.75rem;color:#64748b;background:#f1f5f9;padding:2px 8px;border-radius:6px;font-weight:700;">
                <?= $boxes ?> B / <?= $pcs ?> P
              </div>
              <div style="font-size:0.75rem;color:#94a3b8;"><?= Helpers::money($p['unit_price']) ?></div>
              <div style="font-weight:700;color:#0f172a;min-width:65px;text-align:right;"><?= Helpers::money($p['total_price']) ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- O/C and Commission Summary -->
    <?php 
      $comm_pct = (float)($o['happy_commission'] ?? 0);
      $commission = (float)$o['total_amount'] * ($comm_pct / 100);
    ?>
    <?php if ($total_oc != 0 || $comm_pct > 0): ?>
    <div style="margin-top:12px;border-top:1px dashed #e2e8f0;padding-top:12px;display:flex;justify-content:space-between;align-items:center;font-size:0.8rem;">
      <div>
        <?php if ($total_oc != 0): ?>
          <span style="color:#64748b;font-weight:600;">Total O/C:</span>
          <span style="font-weight:700;color:<?= $total_oc < 0 ? '#ef4444' : '#10b981' ?>;">
            <?= $total_oc > 0 ? '+' : '' ?><?= Helpers::money($total_oc) ?>
          </span>
        <?php endif; ?>
      </div>
      <div>
        <?php if ($comm_pct > 0): ?>
          <span style="color:#64748b;font-weight:600;">Commission (<?= $comm_pct ?>%):</span>
          <span style="font-weight:700;color:#2563eb;"><?= Helpers::money($commission) ?></span>
        <?php endif; ?>
      </div>
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
