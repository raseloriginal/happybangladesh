<?php $pageTitle = 'Inventory'; ?>

<style>
  .inv-bg { background: #f7f8fa; }
  .date-pill {
    display: flex; align-items: center; justify-content: space-between;
    background: #fff; border-radius: 14px; padding: 13px 16px;
    margin: 14px 16px 14px; border: 1.5px solid #f0f0f0;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
  }
  .date-pill input[type="date"] {
    border: none; outline: none; background: transparent;
    font-size: 14px; font-weight: 600; color: #222; width: 100%;
  }
  .date-pill input[type="date"]::-webkit-calendar-picker-indicator { opacity: 0; width: 0; }
  .date-icon { color: #bbb; font-size: 17px; cursor: pointer; }

  /* Summary Cards */
  .summary-grid {
    display: grid; grid-template-columns: 1fr 1fr 1fr;
    gap: 11px; margin: 0 16px 10px;
  }
  .sum-card {
    border-radius: 18px; padding: 14px 12px 12px;
    display: flex; flex-direction: column; gap: 8px;
    border: 1.5px solid;
  }
  .sum-card.outside { background: #edfaf3; border-color: #d1f5e3; }
  .sum-card.inside  { background: #eef1fc; border-color: #d6dcf7; }
  .sum-card.sale    { background: #f2eefa; border-color: #dfd5f5; }

  .sum-icon {
    width: 32px; height: 32px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px; color: #fff;
  }
  .sum-icon.green  { background: #22c55e; }
  .sum-icon.blue   { background: #818cf8; }
  .sum-icon.purple { background: #a78bfa; }

  .sum-label { font-size: 11px; font-weight: 600; color: #888; margin-bottom: 1px; }
  .sum-value { font-size: 13px; font-weight: 800; color: #1a1a1a; }

  /* Damage row */
  .damage-row {
    display: flex; align-items: center; gap: 8px;
    margin: 6px 16px 18px; padding: 0 2px;
  }
  .damage-icon-wrap {
    width: 22px; height: 22px; border-radius: 50%;
    border: 2px solid #f43f5e; display: flex; align-items: center;
    justify-content: center; color: #f43f5e; font-size: 10px;
  }
  .damage-label { font-size: 14px; font-weight: 600; color: #222; }
  .damage-value { font-size: 14px; font-weight: 800; color: #f43f5e; margin-left: 4px; }

  /* Tabs */
  .inv-tabs {
    display: flex; gap: 8px; margin: 0 16px 16px;
    overflow-x: auto; scrollbar-width: none;
  }
  .inv-tabs::-webkit-scrollbar { display: none; }
  .inv-tab-btn {
    flex-shrink: 0; padding: 7px 18px; border-radius: 50px;
    font-size: 13px; font-weight: 600; cursor: pointer; border: 1.5px solid;
    transition: all 0.18s ease;
  }
  .inv-tab-btn.inactive {
    background: #fff; border-color: #e5e7eb; color: #555;
  }
  .inv-tab-btn.active {
    background: #2563eb; border-color: #2563eb; color: #fff;
  }

  /* Product list */
  .prod-list { padding: 0 16px 16px; display: flex; flex-direction: column; gap: 10px; }
  .prod-card {
    background: #fff; border-radius: 16px; padding: 13px 14px;
    display: flex; align-items: center; gap: 13px;
    box-shadow: 0 1px 6px rgba(0,0,0,0.06);
    border: 1.5px solid #f2f2f2;
  }
  .prod-icon-wrap {
    width: 44px; height: 44px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; border: 2px solid;
    background: #fff;
  }
  .prod-icon-inner {
    width: 30px; height: 30px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; color: #fff;
  }
  .prod-icon-wrap.green { border-color: #bbf7d0; }
  .prod-icon-wrap.green .prod-icon-inner { background: #22c55e; }
  .prod-icon-wrap.blue  { border-color: #c7d2fe; }
  .prod-icon-wrap.blue  .prod-icon-inner { background: #818cf8; }
  .prod-icon-wrap.purple { border-color: #ddd6fe; }
  .prod-icon-wrap.purple .prod-icon-inner { background: #a78bfa; }
  .prod-icon-wrap.red   { border-color: #fecdd3; }
  .prod-icon-wrap.red   .prod-icon-inner { background: #f43f5e; }

  .prod-info { flex: 1; min-width: 0; }
  .prod-name { font-size: 14px; font-weight: 700; color: #1a1a1a; margin-bottom: 3px; }
  .prod-qty  { display: flex; align-items: center; gap: 4px; font-size: 11px; }
  .qty-box {
    background: #f3f4f6; border-radius: 5px; padding: 1px 7px;
    font-size: 11px; font-weight: 700; color: #333;
  }
  .qty-label { color: #aaa; font-size: 10px; font-weight: 600; }

  .prod-right { text-align: right; flex-shrink: 0; }
  .prod-val { font-size: 14px; font-weight: 800; margin-bottom: 2px; }
  .prod-val.green  { color: #16a34a; }
  .prod-val.purple { color: #7c3aed; }
  .prod-val.blue   { color: #3730a3; }
  .prod-val.red    { color: #dc2626; }
  .prod-sub { font-size: 10px; font-weight: 600; color: #bbb; }

  .empty-state { text-align: center; padding: 40px 20px; color: #bbb; font-size: 13px; font-weight: 600; }
</style>

<div class="inv-bg min-h-full">

  <!-- Header -->
  <div class="bg-white flex items-center justify-between px-4 pt-12 pb-4 shadow-sm">
    <a href="<?= url('dsr/profile') ?>" class="w-8 h-8 flex items-center justify-center text-gray-500">
      <i class="fa-solid fa-angle-left text-xl"></i>
    </a>
    <h1 class="text-[17px] font-bold text-gray-900">Inventory</h1>
    <button onclick="printPage()" class="w-8 h-8 flex items-center justify-center text-gray-500">
      <i class="fa-solid fa-download text-lg"></i>
    </button>
  </div>

  <!-- Date Picker -->
  <div class="date-pill" onclick="document.getElementById('inventoryDate').showPicker()">
    <input type="date" id="inventoryDate" value="<?= $selectedDate ?>" onchange="changeDate()">
    <i class="fa-regular fa-calendar date-icon"></i>
  </div>

  <!-- Summary Cards -->
  <div class="summary-grid">
    <div class="sum-card outside">
      <div class="sum-icon green"><i class="fa-solid fa-arrow-up"></i></div>
      <div>
        <div class="sum-label">Outside</div>
        <div class="sum-value">Tk <?= number_format($totals['outside']) ?></div>
      </div>
    </div>
    <div class="sum-card inside">
      <div class="sum-icon blue"><i class="fa-solid fa-arrow-down"></i></div>
      <div>
        <div class="sum-label">Inside</div>
        <div class="sum-value">Tk <?= number_format($totals['inside']) ?></div>
      </div>
    </div>
    <div class="sum-card sale">
      <div class="sum-icon purple"><i class="fa-solid fa-chart-column"></i></div>
      <div>
        <div class="sum-label">Sale</div>
        <div class="sum-value">Tk <?= number_format($totals['sale']) ?></div>
      </div>
    </div>
  </div>

  <!-- Damage Row -->
  <div class="damage-row">
    <div class="damage-icon-wrap">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
      </svg>
    </div>
    <span class="damage-label">Damage</span>
    <span class="damage-value">Tk <?= number_format($totals['damage']) ?></span>
  </div>

  <!-- Tabs -->
  <div class="inv-tabs">
    <button onclick="switchTab('outside')" id="tab-outside" class="inv-tab-btn active">Outside</button>
    <button onclick="switchTab('sale')"    id="tab-sale"    class="inv-tab-btn inactive">Sale</button>
    <button onclick="switchTab('inside')"  id="tab-inside"  class="inv-tab-btn inactive">Inside</button>
    <button onclick="switchTab('damage')"  id="tab-damage"  class="inv-tab-btn inactive">Damage</button>
  </div>

  <!-- Product List -->
  <div class="prod-list" id="prodList"></div>

</div>

<script>
const productsData = <?= json_encode($products) ?>;
let activeTab = 'outside';

const tabConfig = {
  outside: { wrapClass: 'green', iconClass: 'fa-arrow-up',     valClass: 'green'  },
  inside:  { wrapClass: 'blue',  iconClass: 'fa-arrow-down',   valClass: 'blue'   },
  sale:    { wrapClass: 'purple',iconClass: 'fa-chart-column', valClass: 'green'  },
  damage:  { wrapClass: 'red',   iconClass: 'fa-xmark',        valClass: 'red'    }
};

function switchTab(tab) {
  document.querySelectorAll('.inv-tab-btn').forEach(b => {
    b.classList.remove('active');
    b.classList.add('inactive');
  });
  const btn = document.getElementById('tab-' + tab);
  btn.classList.remove('inactive');
  btn.classList.add('active');
  activeTab = tab;
  renderList();
}

function renderList() {
  const container = document.getElementById('prodList');
  const list = productsData[activeTab] || [];
  const cfg  = tabConfig[activeTab];

  if (list.length === 0) {
    container.innerHTML = `<div class="empty-state"><i class="fa-solid fa-box-open text-3xl mb-3 block"></i>No ${activeTab} records for this date.</div>`;
    return;
  }

  container.innerHTML = list.map(p => {
    const ppb   = p.pcs_per_box > 0 ? p.pcs_per_box : 1;
    const boxes = Math.floor(p.qty / ppb);
    const packs = p.qty % ppb;
    const val   = parseFloat(p.value).toLocaleString('en-BD', {maximumFractionDigits: 0});
    const prc   = parseFloat(p.trade_price).toFixed(0);

    let boxHtml = '';
    if (boxes > 0) boxHtml += `<span class="qty-box">${String(boxes).padStart(2,'0')}</span><span class="qty-label">Box</span>`;
    if (packs > 0 || boxes === 0) boxHtml += `<span class="qty-box">${String(packs).padStart(2,'0')}</span><span class="qty-label">Pack</span>`;

    return `
    <div class="prod-card">
      <div class="prod-icon-wrap ${cfg.wrapClass}">
        <div class="prod-icon-inner"><i class="fa-solid ${cfg.iconClass}"></i></div>
      </div>
      <div class="prod-info">
        <div class="prod-name">${p.name}</div>
        <div class="prod-qty">${boxHtml}</div>
      </div>
      <div class="prod-right">
        <div class="prod-val ${cfg.valClass}">Tk ${val}</div>
        <div class="prod-sub">Per pcs Tk ${prc}</div>
      </div>
    </div>`;
  }).join('');
}

function changeDate() {
  const date = document.getElementById('inventoryDate').value;
  window.location.href = `<?= url('dsr/van-stock') ?>?date=${encodeURIComponent(date)}`;
}

function printPage() { window.print(); }

renderList();
</script>
