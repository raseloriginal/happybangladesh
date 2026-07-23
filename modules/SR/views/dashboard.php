<?php $pageTitle = 'Dashboard'; ?>

<div class="p-4 sm:p-5 space-y-5 pb-28 max-w-md mx-auto font-sans">

  <!-- 1. Top Header Profile Bar -->
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-3">
      <div class="relative">
        <div class="w-12 h-12 rounded-full bg-slate-200 border-2 border-blue-600 p-0.5 overflow-hidden flex items-center justify-center font-bold text-slate-700 text-base">
          <?= Helpers::initials(Auth::name()) ?>
        </div>
        <span class="w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full absolute bottom-0 right-0"></span>
      </div>
      <div>
        <div class="flex items-center gap-1.5 text-[10px] font-extrabold text-blue-600 tracking-wider uppercase">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
          ফিল্ড একটিভ
        </div>
        <h2 class="text-lg font-black text-slate-900 leading-tight"><?= h(Auth::name()) ?></h2>
      </div>
    </div>

    <!-- SR Logout Button -->
    <a href="<?= url('sr/logout') ?>" class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-rose-50 border border-rose-200/80 text-rose-600 text-xs font-bold shadow-2xs hover:bg-rose-100 transition active:scale-95" title="লগআউট করুন">
      <i class="fa-solid fa-right-from-bracket text-xs"></i>
      <span>লগআউট</span>
    </a>
  </div>

  <!-- 2. Today's Sales & Target Hero Card with Integrated Background Chart -->
  <div class="bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 text-white rounded-3xl p-5 shadow-xl shadow-blue-600/25 relative overflow-hidden space-y-4">
    
    <!-- Top Content Overlay -->
    <div class="flex items-start justify-between relative z-10">
      <div>
        <div class="text-[11px] font-bold text-blue-100 tracking-wider uppercase flex items-center gap-1.5">
          <span>আজকের মোট বিক্রি</span>
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
        </div>
        <div class="text-3xl font-black tracking-tight mt-1 font-mono">
          ৳ <?= number_format($stats['today_sales'] > 0 ? $stats['today_sales'] : $stats['total_value'], 0) ?>
        </div>
        <div class="inline-flex items-center gap-1 text-[11px] font-semibold text-blue-200 mt-1">
          <i class="fa-solid fa-chart-line text-amber-300"></i>
          <span>সেলস ওভারভিউ (গত ৭ দিন)</span>
        </div>
      </div>

      <!-- Hero Action Icon -->
      <div class="w-11 h-11 rounded-2xl bg-white/15 backdrop-blur border border-white/20 flex items-center justify-center text-white text-lg shadow-2xs">
        <i class="fa-solid fa-chart-area"></i>
      </div>
    </div>

    <!-- Embedded Background Chart Container -->
    <div class="relative z-10 bg-white/10 backdrop-blur-md rounded-2xl p-3 border border-white/20">
      <div class="flex items-center justify-between text-[11px] font-bold text-blue-100 mb-1.5">
        <span>বিক্রি পারফরম্যান্স (গত ৭ দিন)</span>
        <span class="text-amber-300 font-mono font-black">7 Days</span>
      </div>
      <div class="h-28 relative">
        <canvas id="srSalesChart"></canvas>
      </div>
    </div>

    <!-- Daily Target Bar -->
    <div class="pt-2 border-t border-white/15 relative z-10">
      <div class="flex items-center justify-between text-xs font-bold mb-1.5">
        <span class="text-blue-100">দৈনিক টার্গেট (৳ ৬০,০০০)</span>
        <span class="text-amber-300 font-extrabold font-mono">75%</span>
      </div>
      <div class="w-full h-2.5 bg-black/20 rounded-full overflow-hidden p-0.5">
        <div class="h-full bg-amber-400 rounded-full transition-all duration-500" style="width: 75%;"></div>
      </div>
    </div>

  </div>

  <!-- 3. Sales Toolkit Grid (সেলস মেনু) -->
  <div class="space-y-2.5">
    <div class="text-xs font-extrabold text-slate-400 tracking-wider uppercase px-1">সেলস মেনু</div>
    
    <div class="grid grid-cols-3 gap-2.5">
      
      <!-- Action 1: Shops -->
      <a href="<?= url('sr/retailers') ?>" class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm flex flex-col items-center justify-center text-center space-y-1.5 hover:border-blue-500 hover:shadow-md transition active:scale-95">
        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base">
          <i class="fa-solid fa-store"></i>
        </div>
        <span class="text-xs font-bold text-slate-800">Shops</span>
      </a>

      <!-- Action 2: Map -->
      <a href="<?= url('sr/sales') ?>" class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm flex flex-col items-center justify-center text-center space-y-1.5 hover:border-emerald-500 hover:shadow-md transition active:scale-95">
        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base">
          <i class="fa-solid fa-map-location-dot"></i>
        </div>
        <span class="text-xs font-bold text-slate-800">Map</span>
      </a>

      <!-- Action 3: Orders -->
      <a href="<?= url('sr/orders') ?>" class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm flex flex-col items-center justify-center text-center space-y-1.5 hover:border-amber-500 hover:shadow-md transition active:scale-95">
        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base">
          <i class="fa-solid fa-file-invoice"></i>
        </div>
        <span class="text-xs font-bold text-slate-800">Orders</span>
      </a>

    </div>
  </div>

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
        label: 'Sales (৳)',
        data,
        backgroundColor: function(c) {
          const g = c.chart.ctx.createLinearGradient(0,0,0,110);
          g.addColorStop(0,'rgba(255,255,255,0.95)');
          g.addColorStop(1,'rgba(255,255,255,0.4)');
          return g;
        },
        borderRadius: 6,
        borderSkipped: false,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display: false }, ticks: { color: 'rgba(255,255,255,0.9)', font: { size: 10, family:'Inter', weight:'bold' } } },
        y: { display: false, beginAtZero: true }
      }
    }
  });
})();
</script>
