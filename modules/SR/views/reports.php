<?php $pageTitle = 'Reports'; ?>

<?php
$statusColors = [
    'pending'   => ['bg' => '#dbeafe', 'text' => '#1d4ed8', 'label' => 'Pending'],
    'confirmed' => ['bg' => '#fef9c3', 'text' => '#a16207', 'label' => 'Confirmed'],
    'delivered' => ['bg' => '#dcfce7', 'text' => '#15803d', 'label' => 'Delivered'],
    'cancelled' => ['bg' => '#fee2e2', 'text' => '#dc2626', 'label' => 'Cancelled'],
];
$maxRetailerValue = !empty($topRetailers) ? max(array_column($topRetailers, 'total_value')) : 1;
$maxProductQty    = !empty($topProducts)  ? max(array_column($topProducts,  'total_qty'))  : 1;
$totalOrders      = (int)$stats['total_orders'];
?>

<style>
:root{--rpt-primary:#2563eb;--rpt-green:#16a34a;--rpt-amber:#d97706;--rpt-red:#dc2626;--rpt-purple:#7c3aed;}
.rpt-header{background:linear-gradient(135deg,#1e3a8a 0%,#2563eb 50%,#0ea5e9 100%);padding:48px 20px 24px;color:#fff;}
.rpt-header h1{font-size:1.55rem;font-weight:900;margin:0 0 4px;letter-spacing:-.5px;}
.rpt-header p{font-size:.8rem;opacity:.75;margin:0;}
.rpt-filter-wrap{display:flex;gap:8px;overflow-x:auto;padding:16px;scrollbar-width:none;background:#f8fafc;}
.rpt-filter-wrap::-webkit-scrollbar{display:none;}
.rpt-filter-pill{white-space:nowrap;padding:8px 18px;border-radius:50px;font-size:.76rem;font-weight:700;border:1.5px solid #e2e8f0;color:#64748b;background:#fff;cursor:pointer;text-decoration:none;transition:all .2s;}
.rpt-filter-pill.active{background:var(--rpt-primary);color:#fff;border-color:var(--rpt-primary);}
.rpt-custom-dates{display:flex;gap:8px;padding:0 16px 16px;background:#f8fafc;}
.rpt-custom-dates input{flex:1;padding:8px 12px;border-radius:12px;border:1.5px solid #e2e8f0;font-size:.8rem;font-weight:600;color:#334155;background:#fff;outline:none;}
.rpt-custom-btn{padding:8px 16px;border-radius:12px;background:var(--rpt-primary);color:#fff;font-size:.8rem;font-weight:700;border:none;cursor:pointer;white-space:nowrap;}
.rpt-stats-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;padding:16px;}
.rpt-stat{background:#fff;border-radius:18px;padding:16px;box-shadow:0 1px 4px rgba(0,0,0,.06);border:1px solid #f1f5f9;}
.rpt-stat-icon{width:38px;height:38px;border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:10px;font-size:1rem;}
.rpt-stat-val{font-size:1.4rem;font-weight:900;color:#1e293b;line-height:1;}
.rpt-stat-lbl{font-size:.72rem;font-weight:600;color:#94a3b8;margin-top:4px;}
.rpt-stat-sub{font-size:.68rem;font-weight:700;margin-top:6px;}
.rpt-section{padding:0 16px 16px;}
.rpt-section-title{font-size:.7rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;margin-bottom:12px;}
.rpt-chart-card{background:#fff;border-radius:18px;padding:16px;border:1px solid #f1f5f9;box-shadow:0 1px 4px rgba(0,0,0,.06);}
.rpt-chart-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;}
.rpt-chart-title{font-size:.875rem;font-weight:800;color:#1e293b;}
.rpt-chart-legend{display:flex;gap:12px;}
.rpt-legend-dot{display:inline-block;width:8px;height:8px;border-radius:50%;margin-right:4px;}
.rpt-legend-text{font-size:.65rem;font-weight:600;color:#64748b;}
.rpt-donut-wrap{display:flex;align-items:center;gap:20px;}
.rpt-donut-labels{flex:1;display:flex;flex-direction:column;gap:10px;}
.rpt-donut-row{display:flex;align-items:center;justify-content:space-between;}
.rpt-donut-label{font-size:.72rem;font-weight:600;color:#475569;display:flex;align-items:center;gap:6px;}
.rpt-donut-count{font-size:.8rem;font-weight:900;color:#1e293b;}
.rpt-bar-row{margin-bottom:12px;}
.rpt-bar-info{display:flex;justify-content:space-between;margin-bottom:5px;}
.rpt-bar-name{font-size:.78rem;font-weight:700;color:#334155;max-width:55%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.rpt-bar-val{font-size:.78rem;font-weight:800;color:#1e293b;}
.rpt-bar-bg{height:8px;background:#f1f5f9;border-radius:99px;overflow:hidden;}
.rpt-bar-fill{height:100%;border-radius:99px;transition:width .5s ease;}
.rpt-order-row{display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid #f8fafc;}
.rpt-order-row:last-child{border-bottom:none;}
.rpt-order-name{font-size:.82rem;font-weight:700;color:#1e293b;}
.rpt-order-meta{font-size:.68rem;color:#94a3b8;margin-top:1px;font-weight:500;}
.rpt-order-right{text-align:right;}
.rpt-order-amt{font-size:.85rem;font-weight:800;color:#1e293b;}
.rpt-status-badge{display:inline-block;padding:2px 8px;border-radius:6px;font-size:.65rem;font-weight:700;margin-top:3px;}
.rpt-empty{text-align:center;padding:32px 16px;color:#94a3b8;font-size:.85rem;font-weight:600;}
.rpt-pb{height:90px;}
</style>

<!-- Header -->
<div class="rpt-header">
  <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
    <a href="<?= url('sr/dashboard') ?>" style="width:32px;height:32px;background:rgba(255,255,255,0.15);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;transition:background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
      <i class="fa-solid fa-arrow-left" style="font-size:0.9rem;"></i>
    </a>
    <div style="font-size:.72rem;color:rgba(255,255,255,.6);">
      <i class="fa-solid fa-house" style="margin-right:4px;"></i>Home › Reports
    </div>
  </div>
  <h1><i class="fa-solid fa-chart-line" style="margin-right:8px;opacity:.85;"></i>Sales Reports</h1>
  <p>
    <?php if ($period==='today'): ?>Today
    <?php elseif ($period==='week'): ?>This Week
    <?php elseif ($period==='custom'): ?><?= h($dateFrom) ?> – <?= h($dateTo) ?>
    <?php else: ?>This Month
    <?php endif; ?>
  </p>
</div>

<!-- Filter Pills -->
<div class="rpt-filter-wrap">
  <a href="<?= url('sr/reports') ?>?period=today"  class="rpt-filter-pill <?= $period==='today' ?'active':'' ?>">Today</a>
  <a href="<?= url('sr/reports') ?>?period=week"   class="rpt-filter-pill <?= $period==='week'  ?'active':'' ?>">This Week</a>
  <a href="<?= url('sr/reports') ?>?period=month"  class="rpt-filter-pill <?= $period==='month' ?'active':'' ?>">This Month</a>
  <a href="<?= url('sr/reports') ?>?period=custom" class="rpt-filter-pill <?= $period==='custom'?'active':'' ?>">Custom Range</a>
</div>
<?php if ($period==='custom'): ?>
<form method="GET" action="<?= url('sr/reports') ?>" class="rpt-custom-dates">
  <input type="hidden" name="period" value="custom">
  <input type="date" name="from" value="<?= h($customFrom) ?>">
  <input type="date" name="to"   value="<?= h($customTo) ?>">
  <button type="submit" class="rpt-custom-btn">Go</button>
</form>
<?php endif; ?>

<!-- Stats -->
<div class="rpt-stats-grid">
  <div class="rpt-stat">
    <div class="rpt-stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="fa-solid fa-file-invoice"></i></div>
    <div class="rpt-stat-val"><?= number_format($totalOrders) ?></div>
    <div class="rpt-stat-lbl">Total Orders</div>
    <div class="rpt-stat-sub" style="color:#2563eb;"><?= (int)$stats['unique_customers'] ?> customers</div>
  </div>
  <div class="rpt-stat">
    <div class="rpt-stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="fa-solid fa-coins"></i></div>
    <div class="rpt-stat-val" style="font-size:1.1rem;">৳<?= number_format((float)$stats['total_value'],0) ?></div>
    <div class="rpt-stat-lbl">Total Value</div>
    <?php if ($totalOrders>0): ?>
    <div class="rpt-stat-sub" style="color:#16a34a;">avg ৳<?= number_format((float)$stats['total_value']/$totalOrders,0) ?>/order</div>
    <?php endif; ?>
  </div>
  <div class="rpt-stat">
    <div class="rpt-stat-icon" style="background:#fef9c3;color:#d97706;"><i class="fa-solid fa-clock"></i></div>
    <div class="rpt-stat-val"><?= (int)$stats['pending'] ?></div>
    <div class="rpt-stat-lbl">Pending</div>
    <div class="rpt-stat-sub" style="color:#d97706;"><?= (int)$stats['confirmed'] ?> confirmed</div>
  </div>
  <div class="rpt-stat">
    <div class="rpt-stat-icon" style="background:#f3e8ff;color:#7c3aed;"><i class="fa-solid fa-truck-ramp-box"></i></div>
    <div class="rpt-stat-val"><?= (int)$stats['delivered'] ?></div>
    <div class="rpt-stat-lbl">Delivered</div>
    <div class="rpt-stat-sub" style="color:#dc2626;"><?= (int)$stats['cancelled'] ?> cancelled</div>
  </div>
</div>

<!-- Trend Chart -->
<?php if (!empty($chartLabels)): ?>
<div class="rpt-section">
  <div class="rpt-section-title">Order Value Trend</div>
  <div class="rpt-chart-card">
    <div class="rpt-chart-head">
      <div class="rpt-chart-title">Daily Sales (৳)</div>
      <div class="rpt-chart-legend">
        <span><span class="rpt-legend-dot" style="background:#2563eb;"></span><span class="rpt-legend-text">Value</span></span>
        <span><span class="rpt-legend-dot" style="background:#22c55e;"></span><span class="rpt-legend-text">Orders</span></span>
      </div>
    </div>
    <canvas id="rptTrend" height="140"></canvas>
  </div>
</div>
<?php endif; ?>

<!-- Donut -->
<?php if ($totalOrders>0): ?>
<div class="rpt-section">
  <div class="rpt-section-title">Order Status Breakdown</div>
  <div class="rpt-chart-card">
    <div class="rpt-donut-wrap">
      <canvas id="rptDonut" width="120" height="120" style="min-width:120px;"></canvas>
      <div class="rpt-donut-labels">
        <?php foreach ([
          ['pending','#3b82f6','Pending'],
          ['confirmed','#f59e0b','Confirmed'],
          ['delivered','#22c55e','Delivered'],
          ['cancelled','#ef4444','Cancelled'],
        ] as [$k,$color,$lbl]):
          $cnt = $donutData[$k];
          $pct = $totalOrders > 0 ? round($cnt/$totalOrders*100) : 0;
        ?>
        <div class="rpt-donut-row">
          <div class="rpt-donut-label">
            <span class="rpt-legend-dot" style="background:<?= $color ?>;width:10px;height:10px;"></span>
            <?= $lbl ?>
          </div>
          <div class="rpt-donut-count"><?= $cnt ?> <span style="font-size:.65rem;color:#94a3b8;">(<?= $pct ?>%)</span></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Top Customers -->
<?php if (!empty($topRetailers)): ?>
<div class="rpt-section">
  <div class="rpt-section-title">Top Customers by Value</div>
  <div class="rpt-chart-card">
    <?php
    $colors=['#2563eb','#0ea5e9','#6366f1','#8b5cf6','#ec4899','#f59e0b','#22c55e','#ef4444'];
    foreach ($topRetailers as $i => $rt):
      $pct = $maxRetailerValue > 0 ? ($rt['total_value']/$maxRetailerValue*100) : 0;
      $clr = $colors[$i%count($colors)];
    ?>
    <div class="rpt-bar-row">
      <div class="rpt-bar-info">
        <div class="rpt-bar-name"><?= h($rt['customer_name']) ?></div>
        <div class="rpt-bar-val">৳<?= number_format($rt['total_value'],0) ?> <span style="font-size:.65rem;color:#94a3b8;">(<?= $rt['order_count'] ?>)</span></div>
      </div>
      <div class="rpt-bar-bg"><div class="rpt-bar-fill" style="width:<?= $pct ?>%;background:<?= $clr ?>;"></div></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- Top Products -->
<?php if (!empty($topProducts)): ?>
<div class="rpt-section">
  <div class="rpt-section-title">Top Products by Quantity Sold</div>
  <div class="rpt-chart-card">
    <?php
    $pcolors=['#16a34a','#2563eb','#d97706','#7c3aed','#0891b2','#dc2626','#059669','#ea580c'];
    foreach ($topProducts as $i => $prod):
      $pct = $maxProductQty > 0 ? ($prod['total_qty']/$maxProductQty*100) : 0;
      $clr = $pcolors[$i%count($pcolors)];
    ?>
    <div class="rpt-bar-row">
      <div class="rpt-bar-info">
        <div class="rpt-bar-name"><?= h($prod['name']) ?></div>
        <div class="rpt-bar-val"><?= number_format((int)$prod['total_qty']) ?> pcs</div>
      </div>
      <div class="rpt-bar-bg"><div class="rpt-bar-fill" style="width:<?= $pct ?>%;background:<?= $clr ?>;"></div></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- Recent Orders -->
<div class="rpt-section">
  <div class="rpt-section-title">Recent Orders</div>
  <?php if (!empty($recentOrders)): ?>
  <div class="rpt-chart-card">
    <?php foreach ($recentOrders as $o):
      $sc = $statusColors[$o['status']] ?? ['bg'=>'#f1f5f9','text'=>'#64748b','label'=>ucfirst($o['status'])];
    ?>
    <div class="rpt-order-row">
      <div>
        <div class="rpt-order-name"><?= h($o['customer_name']) ?></div>
        <div class="rpt-order-meta">#<?= $o['id'] ?> · <?= date('d M, H:i', strtotime($o['created_at'])) ?></div>
      </div>
      <div class="rpt-order-right">
        <div class="rpt-order-amt">৳<?= number_format($o['total_amount'],0) ?></div>
        <div class="rpt-status-badge" style="background:<?= $sc['bg'] ?>;color:<?= $sc['text'] ?>;"><?= $sc['label'] ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="rpt-chart-card">
    <div class="rpt-empty"><i class="fa-solid fa-receipt" style="font-size:2rem;margin-bottom:8px;display:block;"></i>No orders found for this period.</div>
  </div>
  <?php endif; ?>
</div>

<div class="rpt-pb"></div>

<?php if (!empty($chartLabels)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function(){
  const labels = <?= json_encode($chartLabels) ?>;
  const orders = <?= json_encode($chartOrders) ?>;
  const values = <?= json_encode($chartValues) ?>;

  // Trend
  const tc = document.getElementById('rptTrend');
  if(tc && labels.length){
    new Chart(tc,{
      type:'bar',
      data:{
        labels,
        datasets:[
          {label:'Value',data:values,backgroundColor:'rgba(37,99,235,.15)',borderColor:'#2563eb',borderWidth:2,borderRadius:6,yAxisID:'y'},
          {label:'Orders',data:orders,type:'line',borderColor:'#22c55e',backgroundColor:'rgba(34,197,94,.1)',borderWidth:2.5,tension:.4,fill:true,pointBackgroundColor:'#22c55e',pointRadius:3,yAxisID:'y1'}
        ]
      },
      options:{
        responsive:true,interaction:{mode:'index',intersect:false},
        plugins:{legend:{display:false},tooltip:{callbacks:{label:(ctx)=>ctx.datasetIndex===0?'৳'+ctx.parsed.y.toLocaleString():ctx.parsed.y+' orders'}}},
        scales:{
          x:{ticks:{font:{size:10},maxTicksLimit:8},grid:{display:false}},
          y:{position:'left',ticks:{font:{size:10},callback:v=>'৳'+v.toLocaleString()},grid:{color:'#f1f5f9'}},
          y1:{position:'right',ticks:{font:{size:10}},grid:{display:false}}
        }
      }
    });
  }

  // Donut
  const dc = document.getElementById('rptDonut');
  if(dc){
    const d = <?= json_encode(array_values($donutData)) ?>;
    const total = d.reduce((a,b)=>a+b,0);
    new Chart(dc,{
      type:'doughnut',
      data:{
        labels:['Pending','Confirmed','Delivered','Cancelled'],
        datasets:[{data:total>0?d:[1],backgroundColor:total>0?['#3b82f6','#f59e0b','#22c55e','#ef4444']:['#f1f5f9'],borderWidth:0}]
      },
      options:{responsive:false,cutout:'68%',plugins:{legend:{display:false},tooltip:{enabled:total>0,callbacks:{label:ctx=>ctx.label+': '+ctx.parsed}}}}
    });
  }
})();
</script>
<?php endif; ?>
