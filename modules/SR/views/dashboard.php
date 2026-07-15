<?php $pageTitle = 'Dashboard'; ?>

<!-- ── Dashboard Header ─────────────────────────────────────── -->
<div class="sr-dash-header">
  <div style="display:flex;align-items:center;justify-content:space-between;position:relative;z-index:2;">
    <div>
      <div class="sr-dash-greeting">Good <?= date('H') < 12 ? 'Morning' : (date('H') < 18 ? 'Afternoon' : 'Evening') ?> 👋</div>
      <div class="sr-dash-name"><?= h(Auth::name()) ?></div>
      <div class="sr-dash-role">Sales Representative</div>
    </div>
    <div class="sr-dash-avatar"><?= Helpers::initials(Auth::name()) ?></div>
  </div>
</div>

<!-- ── Stats Cards ────────────────────────────────────────────── -->
<div class="sr-dash-cards-wrap">
  <div class="sr-stats-grid">
    <div class="sr-stat-card blue">
      <div class="sr-stat-icon"><i class="fa-solid fa-file-invoice"></i></div>
      <div class="sr-stat-value"><?= $stats['total_orders'] ?></div>
      <div class="sr-stat-label">Total Orders</div>
    </div>
    <div class="sr-stat-card amber">
      <div class="sr-stat-icon"><i class="fa-solid fa-clock"></i></div>
      <div class="sr-stat-value"><?= $stats['pending_orders'] ?></div>
      <div class="sr-stat-label">Pending</div>
    </div>
    <div class="sr-stat-card green">
      <div class="sr-stat-icon"><i class="fa-solid fa-circle-check"></i></div>
      <div class="sr-stat-value"><?= $stats['confirmed'] ?></div>
      <div class="sr-stat-label">Confirmed</div>
    </div>
    <div class="sr-stat-card purple">
      <div class="sr-stat-icon"><i class="fa-solid fa-coins"></i></div>
      <div class="sr-stat-value" style="font-size:1.1rem;"><?= Helpers::money($stats['total_value']) ?></div>
      <div class="sr-stat-label">Total Value</div>
    </div>
  </div>
</div>

<!-- ── Quick Action Buttons ───────────────────────────────────── -->
<div style="padding:0 16px 16px;">
  <div class="sr-section-header">
    <span class="sr-section-title">Quick Actions</span>
  </div>
  <div class="sr-quick-actions">
    <a href="<?= url('sr/orders') ?>" class="sr-action-btn">
      <div class="sr-action-icon" style="background:rgba(79,70,229,.1);color:#4f46e5;">
        <i class="fa-solid fa-file-invoice"></i>
      </div>
      <span class="sr-action-label">Orders</span>
    </a>
    <a href="<?= url('sr/retailers') ?>" class="sr-action-btn">
      <div class="sr-action-icon" style="background:rgba(6,182,212,.1);color:#0891b2;">
        <i class="fa-solid fa-store"></i>
      </div>
      <span class="sr-action-label">Retailers</span>
    </a>
    <a href="<?= url('sr/sales') ?>" class="sr-action-btn">
      <div class="sr-action-icon" style="background:rgba(16,185,129,.1);color:#059669;">
        <i class="fa-solid fa-map-location-dot"></i>
      </div>
      <span class="sr-action-label">Sales</span>
    </a>
    <a href="<?= url('sr/reports') ?>" class="sr-action-btn">
      <div class="sr-action-icon" style="background:rgba(245,158,11,.1);color:#d97706;">
        <i class="fa-solid fa-chart-bar"></i>
      </div>
      <span class="sr-action-label">Reports</span>
    </a>
    <a href="<?= url('sr/profile') ?>" class="sr-action-btn">
      <div class="sr-action-icon" style="background:rgba(245,158,11,.1);color:#f59e0b;">
        <i class="fa-solid fa-user"></i>
      </div>
      <span class="sr-action-label">Profile</span>
    </a>
    <a href="<?= url('logout') ?>" class="sr-action-btn">
      <div class="sr-action-icon" style="background:rgba(239,68,68,.1);color:#ef4444;">
        <i class="fa-solid fa-right-from-bracket"></i>
      </div>
      <span class="sr-action-label">Logout</span>
    </a>
  </div>
</div>

<!-- ── Sales Chart ────────────────────────────────────────────── -->
<div class="sr-chart-card" style="margin:0 16px 16px;">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
    <div>
      <div class="sr-section-title">Sales Overview</div>
      <div style="font-size:0.72rem;color:var(--sr-text-muted);">Last 7 days performance</div>
    </div>
    <div style="background:rgba(79,70,229,.1);color:#4f46e5;padding:4px 10px;border-radius:999px;font-size:0.7rem;font-weight:600;">
      This Week
    </div>
  </div>
  <canvas id="srSalesChart" height="140"></canvas>
</div>

<!-- ── Recent Orders ─────────────────────────────────────────── -->
<div class="sr-section-header" style="padding:0 16px;margin-bottom:12px;">
  <span class="sr-section-title">Recent Orders</span>
  <a href="<?= url('sr/orders') ?>" class="sr-section-link">View All</a>
</div>

<div class="sr-order-list">
  <?php if (empty($recentOrders)): ?>
  <div style="text-align:center;padding:32px 20px;color:var(--sr-text-muted);">
    <i class="fa-solid fa-inbox" style="font-size:2.5rem;opacity:.3;margin-bottom:10px;display:block;"></i>
    No orders yet.<br>
    <a href="<?= url('sr/sales') ?>" style="color:var(--sr-primary);font-weight:600;">Start Selling →</a>
  </div>
  <?php endif; ?>
  <?php foreach ($recentOrders as $o): ?>
  <div class="sr-order-item">
    <div class="sr-order-icon"><i class="fa-solid fa-bag-shopping"></i></div>
    <div style="flex:1;min-width:0;">
      <div class="sr-order-dealer"><?= h($o['dealer_name'] ?? 'Direct Sale') ?></div>
      <div class="sr-order-date"><?= Helpers::date($o['created_at']) ?></div>
    </div>
    <div style="text-align:right;">
      <div class="sr-order-amount"><?= Helpers::money($o['total_amount']) ?></div>
      <span class="sr-order-badge badge-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Bottom spacing -->
<div style="height:16px;"></div>

<script>
// ── Sales Chart ────────────────────────────────────────────────
(function() {
  const ctx = document.getElementById('srSalesChart');
  if (!ctx) return;
  const labels = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
  const data   = [12,19,8,25,14,30,22]; // demo data — replace with real
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Orders',
        data,
        backgroundColor: function(c) {
          const g = c.chart.ctx.createLinearGradient(0,0,0,160);
          g.addColorStop(0,'rgba(79,70,229,.9)');
          g.addColorStop(1,'rgba(6,182,212,.5)');
          return g;
        },
        borderRadius: 8,
        borderSkipped: false,
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display: false }, ticks: { font: { size: 11, family:'Inter' } } },
        y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 11, family:'Inter' } }, beginAtZero: true }
      }
    }
  });
})();
</script>
