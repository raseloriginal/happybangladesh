<?php $pageTitle = 'Dashboard'; ?>

<div class="p-4 sm:p-5 space-y-5 pb-28 max-w-md mx-auto">

  <!-- 1. Top Header Profile Bar -->
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-3">
      <div class="relative">
        <div class="w-12 h-12 rounded-full bg-slate-200 border-2 border-blue-500 p-0.5 overflow-hidden flex items-center justify-center font-bold text-slate-700 text-base">
          <?= Helpers::initials(Auth::name()) ?>
        </div>
        <span class="w-3.5 h-3.5 bg-blue-600 border-2 border-white rounded-full absolute bottom-0 right-0"></span>
      </div>
      <div>
        <div class="flex items-center gap-1.5 text-[10px] font-extrabold text-blue-600 tracking-wider uppercase">
          <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span>
          Field Active
        </div>
        <h2 class="text-lg font-black text-slate-900 leading-tight"><?= h(Auth::name()) ?></h2>
      </div>
    </div>

    <!-- Notification Bell -->
    <a href="<?= url('sr/orders') ?>" class="w-10 h-10 rounded-full bg-white shadow-sm border border-slate-200 flex items-center justify-center text-slate-700 relative hover:bg-slate-50 transition">
      <i class="fa-regular fa-bell text-base"></i>
      <span class="w-2.5 h-2.5 bg-rose-500 rounded-full absolute top-2 right-2 border border-white"></span>
    </a>
  </div>

  <!-- 2. Today's Sales & Target Hero Card (Solid Brand Blue Theme) -->
  <div class="bg-blue-600 text-white rounded-3xl p-5 shadow-xl shadow-blue-600/20 relative overflow-hidden">
    
    <div class="flex items-start justify-between relative z-10">
      <div>
        <div class="text-[11px] font-bold text-blue-100 tracking-wider uppercase">Today's Sales</div>
        <div class="text-3xl font-black tracking-tight mt-1">
          ৳ <?= number_format($stats['today_sales'] > 0 ? $stats['today_sales'] : $stats['total_value'], 0) ?>
        </div>
        <div class="inline-flex items-center gap-1 text-[11px] font-semibold text-blue-200 mt-1">
          <i class="fa-solid fa-arrow-trend-up text-blue-300"></i>
          <span>+12% from yesterday</span>
        </div>
      </div>

      <!-- Glassmorphic Chart Icon Button -->
      <div class="w-11 h-11 rounded-2xl bg-white/15 backdrop-blur border border-white/20 flex items-center justify-center text-white text-lg">
        <i class="fa-solid fa-chart-line"></i>
      </div>
    </div>

    <!-- Daily Target Bar -->
    <div class="mt-6 pt-4 border-t border-white/15 relative z-10">
      <div class="flex items-center justify-between text-xs font-bold mb-1.5">
        <span class="text-blue-100">Daily Target (৳ 60K)</span>
        <span class="text-amber-300 font-extrabold">75%</span>
      </div>
      <div class="w-full h-2.5 bg-black/20 rounded-full overflow-hidden p-0.5">
        <div class="h-full bg-amber-400 rounded-full transition-all duration-500" style="width: 75%;"></div>
      </div>
    </div>

  </div>

  <!-- 3. Metrics Cards Grid (4 Rounded White Cards) -->
  <div class="grid grid-cols-2 gap-3.5">
    
    <!-- Card 1: Route Retailers -->
    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between space-y-3">
      <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base">
        <i class="fa-solid fa-store"></i>
      </div>
      <div>
        <div class="text-2xl font-black text-slate-900"><?= $stats['total_retailers'] ?? 25 ?></div>
        <div class="text-xs font-medium text-slate-500 mt-0.5">Route Retailers</div>
      </div>
    </div>

    <!-- Card 2: Visited Today -->
    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between space-y-3">
      <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base">
        <i class="fa-solid fa-circle-check"></i>
      </div>
      <div>
        <div class="text-2xl font-black text-slate-900"><?= $stats['visited_today'] ?? 18 ?></div>
        <div class="text-xs font-medium text-slate-500 mt-0.5">Visited Today</div>
      </div>
    </div>

    <!-- Card 3: Successful Orders -->
    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between space-y-3">
      <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-base">
        <i class="fa-solid fa-cart-shopping"></i>
      </div>
      <div>
        <div class="text-2xl font-black text-slate-900"><?= $stats['confirmed'] ?></div>
        <div class="text-xs font-medium text-slate-500 mt-0.5">Successful Orders</div>
      </div>
    </div>

    <!-- Card 4: LPC / Total Orders -->
    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between space-y-3">
      <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base">
        <i class="fa-solid fa-boxes-packing"></i>
      </div>
      <div>
        <div class="text-2xl font-black text-slate-900"><?= $stats['total_orders'] ?></div>
        <div class="text-xs font-medium text-slate-500 mt-0.5">LPC (Lines Per Call)</div>
      </div>
    </div>

  </div>

  <!-- 4. Sales Toolkit Grid (3 Action Cards) -->
  <div class="space-y-2.5">
    <div class="text-xs font-extrabold text-slate-400 tracking-wider uppercase px-1">Sales Toolkit</div>
    
    <div class="grid grid-cols-3 gap-3">
      
      <!-- Action 1: New Shop -->
      <a href="<?= url('sr/retailers') ?>" class="bg-white p-3.5 rounded-2xl border border-slate-100 shadow-sm flex flex-col items-center justify-center text-center space-y-2 hover:border-blue-500 hover:shadow-md transition">
        <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
          <i class="fa-solid fa-user-plus"></i>
        </div>
        <span class="text-xs font-bold text-slate-800">New Shop</span>
      </a>

      <!-- Action 2: Catalog -->
      <a href="<?= url('sr/sales') ?>" class="bg-white p-3.5 rounded-2xl border border-slate-100 shadow-sm flex flex-col items-center justify-center text-center space-y-2 hover:border-purple-500 hover:shadow-md transition">
        <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg">
          <i class="fa-solid fa-book-open"></i>
        </div>
        <span class="text-xs font-bold text-slate-800">Catalog</span>
      </a>

      <!-- Action 3: Reports -->
      <a href="<?= url('sr/reports') ?>" class="bg-white p-3.5 rounded-2xl border border-slate-100 shadow-sm flex flex-col items-center justify-center text-center space-y-2 hover:border-rose-500 hover:shadow-md transition">
        <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg">
          <i class="fa-solid fa-chart-pie"></i>
        </div>
        <span class="text-xs font-bold text-slate-800">Reports</span>
      </a>

    </div>
  </div>

  <!-- 5. Sales Performance Chart Card (Preserved Chart.js!) -->
  <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-sm">
    <div class="flex items-center justify-between mb-3">
      <div>
        <h3 class="text-sm font-extrabold text-slate-900">Sales Overview</h3>
        <p class="text-xs text-slate-400">Last 7 days performance</p>
      </div>
      <span class="text-[11px] font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-full">This Week</span>
    </div>
    <canvas id="srSalesChart" height="130"></canvas>
  </div>

  <!-- 6. Recent Orders List (Preserved List!) -->
  <div class="space-y-3">
    <div class="flex items-center justify-between px-1">
      <h3 class="text-xs font-extrabold text-slate-400 tracking-wider uppercase">Recent Orders</h3>
      <a href="<?= url('sr/orders') ?>" class="text-xs font-bold text-blue-600 hover:underline">View All</a>
    </div>

    <div class="space-y-2.5">
      <?php if (empty($recentOrders)): ?>
        <div class="bg-white rounded-2xl p-6 text-center text-slate-400 border border-slate-100">
          <i class="fa-solid fa-inbox text-3xl opacity-30 mb-2"></i>
          <p class="text-xs">No orders created yet.</p>
          <a href="<?= url('sr/sales') ?>" class="inline-block text-xs font-bold text-blue-600 mt-2">Start Selling &rarr;</a>
        </div>
      <?php endif; ?>

      <?php foreach ($recentOrders as $o): ?>
        <div class="bg-white p-3.5 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-sm">
              <i class="fa-solid fa-bag-shopping"></i>
            </div>
            <div>
              <div class="font-bold text-xs text-slate-900"><?= h($o['dealer_name'] ?? 'Direct Sale') ?></div>
              <div class="text-[11px] text-slate-400"><?= Helpers::date($o['created_at']) ?></div>
            </div>
          </div>
          <div class="text-right">
            <div class="font-bold text-xs text-slate-900"><?= Helpers::money($o['total_amount']) ?></div>
            <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full capitalize
              <?= $o['status'] === 'confirmed' ? 'bg-blue-50 text-blue-700' : ($o['status'] === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600') ?>">
              <?= h($o['status']) ?>
            </span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

</div>

<!-- 7. Floating Bottom Navigation Bar -->
<div class="fixed bottom-4 left-1/2 -translate-x-1/2 max-w-sm w-[90%] bg-white/95 backdrop-blur-md rounded-full shadow-2xl border border-slate-200/80 px-5 py-2.5 flex items-center justify-between z-50">
  
  <!-- Home Tab -->
  <a href="<?= url('sr/dashboard') ?>" class="flex flex-col items-center text-blue-600 font-bold text-[10px]">
    <i class="fa-solid fa-house text-lg mb-0.5"></i>
    <span>Home</span>
  </a>

  <!-- Shops Tab -->
  <a href="<?= url('sr/retailers') ?>" class="flex flex-col items-center text-slate-400 hover:text-slate-700 font-medium text-[10px]">
    <i class="fa-solid fa-store text-lg mb-0.5"></i>
    <span>Shops</span>
  </a>

  <!-- Center Floating Action Button (Location Sales App) -->
  <a href="<?= url('sr/sales') ?>" class="sr-float-loc-btn w-12 h-12 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white flex items-center justify-center shadow-lg shadow-blue-600/40 -mt-6 hover:scale-105 transition">
    <i class="fa-solid fa-location-dot text-xl"></i>
  </a>

  <!-- History Tab -->
  <a href="<?= url('sr/orders') ?>" class="flex flex-col items-center text-slate-400 hover:text-slate-700 font-medium text-[10px]">
    <i class="fa-solid fa-clock-rotate-left text-lg mb-0.5"></i>
    <span>History</span>
  </a>

  <!-- Profile Tab -->
  <a href="<?= url('sr/profile') ?>" class="flex flex-col items-center text-slate-400 hover:text-slate-700 font-medium text-[10px]">
    <i class="fa-solid fa-user text-lg mb-0.5"></i>
    <span>Profile</span>
  </a>

</div>

<style>
@keyframes srSubtleFloat {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-4px); }
}
.sr-float-loc-btn {
  animation: srSubtleFloat 2.5s infinite ease-in-out;
}
</style>

<script>
// Sales Performance Chart initialization
(function() {
  const ctx = document.getElementById('srSalesChart');
  if (!ctx) return;
  const labels = <?= json_encode($chartLabels) ?>;
  const data   = <?= json_encode($chartValues) ?>;
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Sales Value (৳)',
        data,
        backgroundColor: function(c) {
          const g = c.chart.ctx.createLinearGradient(0,0,0,160);
          g.addColorStop(0,'rgba(37,99,235,.9)');
          g.addColorStop(1,'rgba(37,99,235,.5)');
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

