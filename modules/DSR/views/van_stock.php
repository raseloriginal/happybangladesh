<?php $pageTitle = 'Inventory'; ?>

<div class="p-3 sm:p-5 space-y-4 pb-32 max-w-lg mx-auto font-sans">

  <!-- 1. Header Bar -->
  <div class="flex items-center justify-between bg-white p-3.5 rounded-2xl border border-slate-200/90 shadow-xs">
    <div class="flex items-center gap-3">
      <a href="<?= url('dsr/dashboard') ?>" class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200/80 flex items-center justify-center text-slate-700 hover:bg-slate-200 transition">
        <i class="fa-solid fa-arrow-left text-sm"></i>
      </a>
      <div>
        <h1 class="text-base font-black text-slate-900 leading-tight">Van Inventory</h1>
        <p class="text-[11px] text-slate-500 font-medium">ভ্যান স্টক ও মালের বিবরণ</p>
      </div>
    </div>

    <!-- Print / Export -->
    <button type="button" onclick="window.print()" class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-200/80 flex items-center justify-center text-blue-600 hover:bg-blue-100 transition" title="Print Inventory">
      <i class="fa-solid fa-print text-sm"></i>
    </button>
  </div>

  <!-- 2. Date Picker Strip -->
  <div class="bg-white p-3 rounded-2xl border border-slate-200/90 shadow-xs flex items-center justify-between gap-3">
    <div class="flex items-center gap-2 text-xs font-bold text-slate-700">
      <i class="fa-regular fa-calendar text-blue-600"></i>
      <span>Select Date:</span>
    </div>
    <input type="date" id="inventoryDate" value="<?= $selectedDate ?>" onchange="changeDate()"
      class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 text-xs font-bold font-mono text-slate-800 outline-none focus:border-blue-500 transition">
  </div>

  <!-- 3. Stock Summary KPI Grid -->
  <div class="grid grid-cols-2 gap-2.5">
    
    <!-- Outside / Dispatched Stock -->
    <div class="bg-white p-3.5 rounded-2xl border border-slate-200/90 shadow-xs flex flex-col justify-between">
      <div class="flex items-center justify-between">
        <span class="text-[11px] font-bold text-slate-500">Outside (মালের শুরু)</span>
        <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
          <i class="fa-solid fa-arrow-up"></i>
        </div>
      </div>
      <div class="text-xl font-black text-emerald-600 font-mono mt-2">৳ <?= number_format($totals['outside']) ?></div>
      <div class="text-[10px] text-slate-400 font-medium mt-0.5">Total Loaded Stock</div>
    </div>

    <!-- Total Sales -->
    <div class="bg-white p-3.5 rounded-2xl border border-slate-200/90 shadow-xs flex flex-col justify-between">
      <div class="flex items-center justify-between">
        <span class="text-[11px] font-bold text-blue-700">Sale (মোট বিক্রি)</span>
        <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
          <i class="fa-solid fa-chart-column"></i>
        </div>
      </div>
      <div class="text-xl font-black text-blue-600 font-mono mt-2">৳ <?= number_format($totals['sale']) ?></div>
      <div class="text-[10px] text-blue-600/80 font-medium mt-0.5">Delivered Stock</div>
    </div>

    <!-- Inside / Remaining Stock -->
    <div class="bg-white p-3.5 rounded-2xl border border-slate-200/90 shadow-xs flex flex-col justify-between">
      <div class="flex items-center justify-between">
        <span class="text-[11px] font-bold text-indigo-700">Inside (ভ্যানে অবশিষ্ট)</span>
        <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
          <i class="fa-solid fa-arrow-down"></i>
        </div>
      </div>
      <div class="text-xl font-black text-indigo-600 font-mono mt-2">৳ <?= number_format($totals['inside']) ?></div>
      <div class="text-[10px] text-indigo-600/80 font-medium mt-0.5">Remaining Stock</div>
    </div>

    <!-- Damage -->
    <div class="bg-white p-3.5 rounded-2xl border border-slate-200/90 shadow-xs flex flex-col justify-between">
      <div class="flex items-center justify-between">
        <span class="text-[11px] font-bold text-rose-700">Damage (ক্ষতিগ্রস্ত)</span>
        <div class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-xs">
          <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
      </div>
      <div class="text-xl font-black text-rose-600 font-mono mt-2">৳ <?= number_format($totals['damage']) ?></div>
      <div class="text-[10px] text-rose-600/80 font-medium mt-0.5">Damaged / Broken</div>
    </div>

  </div>

  <!-- 4. Filter Tabs & Search Box -->
  <div class="bg-white p-3 rounded-2xl border border-slate-200/90 shadow-xs space-y-2.5">
    
    <!-- Search Bar -->
    <div class="relative">
      <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
      <input type="text" id="invSearchInput" onkeyup="renderList()" placeholder="পণ্য বা কোম্পানির নাম খুঁজুন..."
        class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-8 pr-3 py-2 text-xs font-semibold text-slate-800 placeholder:text-slate-400 outline-none focus:border-blue-500 transition">
    </div>

    <!-- Category Tabs -->
    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none">
      <button onclick="switchTab('outside')" id="tab-outside" class="inv-tab-btn px-3 py-1.5 rounded-xl text-xs font-bold bg-blue-600 text-white transition">
        Outside (মাল আউট)
      </button>
      <button onclick="switchTab('sale')" id="tab-sale" class="inv-tab-btn px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
        Sale (বিক্রি)
      </button>
      <button onclick="switchTab('inside')" id="tab-inside" class="inv-tab-btn px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
        Inside (অবশিষ্ট)
      </button>
      <button onclick="switchTab('damage')" id="tab-damage" class="inv-tab-btn px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
        Damage (ক্ষতিগ্রস্ত)
      </button>
    </div>

  </div>

  <!-- 5. Product Stock List -->
  <div class="space-y-2" id="prodList"></div>

</div>

<script>
const productsData = <?= json_encode($products) ?>;
let activeTab = 'outside';

const tabConfig = {
  outside: { icon: 'fa-arrow-up', color: 'text-emerald-600 bg-emerald-50 border-emerald-200' },
  inside:  { icon: 'fa-arrow-down', color: 'text-indigo-600 bg-indigo-50 border-indigo-200' },
  sale:    { icon: 'fa-chart-column', color: 'text-blue-600 bg-blue-50 border-blue-200' },
  damage:  { icon: 'fa-triangle-exclamation', color: 'text-rose-600 bg-rose-50 border-rose-200' }
};

function switchTab(tab) {
  document.querySelectorAll('.inv-tab-btn').forEach(b => {
    b.className = 'inv-tab-btn px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 text-slate-600 hover:bg-slate-200 transition';
  });
  const btn = document.getElementById('tab-' + tab);
  if (btn) btn.className = 'inv-tab-btn px-3 py-1.5 rounded-xl text-xs font-bold bg-blue-600 text-white shadow-2xs transition';
  
  activeTab = tab;
  renderList();
}

function renderList() {
  const container = document.getElementById('prodList');
  const query = (document.getElementById('invSearchInput')?.value || '').toLowerCase().trim();
  let list = productsData[activeTab] || [];

  if (query) {
    list = list.filter(p => (p.name || '').toLowerCase().includes(query));
  }

  const cfg = tabConfig[activeTab] || tabConfig.outside;

  if (list.length === 0) {
    container.innerHTML = `
      <div class="bg-white p-8 rounded-2xl border border-slate-200/90 text-center space-y-2 text-slate-400">
        <i class="fa-solid fa-box-open text-3xl opacity-40"></i>
        <div class="text-xs font-bold text-slate-600">কোনো পণ্যের ডাটা পাওয়া যায়নি</div>
        <div class="text-[11px] text-slate-400">নির্বাচিত তারিখে এই ক্যাটালগে কোনো স্টক এন্ট্রি নেই।</div>
      </div>
    `;
    return;
  }

  container.innerHTML = list.map((p, idx) => {
    const ppb = p.pcs_per_box > 0 ? p.pcs_per_box : 1;
    const boxes = Math.floor(p.qty / ppb);
    const packs = p.qty % ppb;
    const val = parseFloat(p.value || 0).toLocaleString('en-US', {maximumFractionDigits: 0});
    const prc = parseFloat(p.trade_price || 0).toFixed(0);

    let boxHtml = '';
    if (boxes > 0) boxHtml += `<span class="bg-slate-100 text-slate-800 px-2 py-0.5 rounded font-black text-[11px] font-mono">${String(boxes).padStart(2,'0')} কার্টন</span> `;
    if (packs > 0 || boxes === 0) boxHtml += `<span class="bg-blue-50 text-blue-700 px-2 py-0.5 rounded font-black text-[11px] font-mono">${String(packs).padStart(2,'0')} পিস</span>`;

    return `
      <div class="bg-white p-3.5 rounded-2xl border border-slate-200/90 shadow-2xs flex items-center justify-between gap-3 hover:border-blue-300 transition">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl ${cfg.color} border flex items-center justify-center text-sm shrink-0">
            <i class="fa-solid ${cfg.icon}"></i>
          </div>
          <div>
            <div class="font-bold text-slate-900 text-xs leading-tight">${p.name}</div>
            <div class="mt-1 flex items-center gap-1.5">${boxHtml}</div>
          </div>
        </div>

        <div class="text-right font-mono shrink-0">
          <div class="font-black text-slate-900 text-xs">৳ ${val}</div>
          <div class="text-[10px] text-slate-400 font-medium">প্রতি পিস ৳ ${prc}</div>
        </div>
      </div>
    `;
  }).join('');
}

function changeDate() {
  const date = document.getElementById('inventoryDate').value;
  window.location.href = `<?= url('dsr/van-stock') ?>?date=${encodeURIComponent(date)}`;
}

renderList();
</script>
