<?php
// Reusable user list table (Managers, SRs, DSRs)
$role       = $role ?? 'manager';
$roleLabel  = $roleLabel ?? 'Manager';
$pageTitle  = $roleLabel . 's';
$createUrl  = url("admin/{$role}s/create");
?>
<div class="page-header">
  <div>
    <h1 class="page-title flex items-center gap-2">
      <i class="fa-solid fa-users-gear text-blue-600"></i> <?= $pageTitle ?> Sheet
    </h1>
    <div class="breadcrumb">Admin &rsaquo; <?= $pageTitle ?></div>
  </div>
  <a href="<?= $createUrl ?>" class="btn btn-primary">
    <i class="fa-solid fa-plus"></i> Add <?= $roleLabel ?>
  </a>
</div>

<div class="excel-container">
  <!-- Excel Ribbon Toolbar -->
  <div class="excel-ribbon">
    <div class="flex items-center gap-3">
      <div class="excel-ribbon-badge">
        <i class="fa-solid fa-file-excel text-blue-200"></i>
        <span><?= $pageTitle ?> Data Sheet</span>
      </div>
      <span class="text-xs text-blue-100 hidden sm:inline-block">Total <?= count($items) ?> Records</span>
    </div>

    <div class="flex items-center gap-2">
      <input type="text" placeholder="Search user..." data-table-search="users-table" 
             class="px-3 py-1.5 bg-white/20 text-white placeholder-blue-100 text-xs rounded-lg outline-none border border-white/30 focus:bg-white focus:text-gray-900 focus:placeholder-gray-400 transition">
      <button onclick="exportTableToCSV('users-table', '<?= $role ?>s_List.csv')" class="excel-action-btn">
        <i class="fa-solid fa-file-csv"></i> Export CSV
      </button>
      <button onclick="printTable('users-table', '<?= $pageTitle ?> Sheet')" class="excel-action-btn excel-action-btn-secondary">
        <i class="fa-solid fa-print"></i> Print
      </button>
    </div>
  </div>

  <!-- Formula Bar -->
  <div class="excel-formula-bar">
    <span class="fx-symbol">fx</span>
    <div class="excel-pill">
      <i class="fa-solid fa-calculator text-blue-600"></i>
      <span>TOTAL <?= strtoupper($pageTitle) ?>: <strong class="text-blue-700 font-mono"><?= count($items) ?></strong></span>
    </div>
  </div>

  <div class="overflow-x-auto">
    <table class="excel-table" id="users-table">
      <thead>
        <tr>
          <th class="excel-row-num">#</th>
          <th>Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th><?= $role === 'sr' ? 'Company' : 'Warehouse' ?></th>
          <?php if ($role === 'sr'): ?><th>Dealer</th><?php endif; ?>
          <th class="text-center">Status</th>
          <th class="text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $i => $u): ?>
        <tr>
          <td class="excel-row-num"><?= $i+1 ?></td>
          <td>
            <div class="flex items-center gap-2">
              <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">
                <?= Helpers::initials($u['name']) ?>
              </div>
              <span class="font-bold text-gray-900"><?= h($u['name']) ?></span>
            </div>
          </td>
          <td class="text-gray-500 font-mono"><?= h($u['email']) ?></td>
          <td class="font-mono text-gray-700"><?= h($u['phone'] ?? '—') ?></td>
          <td class="text-gray-700"><?= $role === 'sr' ? h($u['company_name'] ?? '—') : h($u['warehouse_name'] ?? '—') ?></td>
          <?php if ($role === 'sr'): ?>
          <td class="text-gray-600"><?= h($u['dealer_names'] ?? '—') ?></td>
          <?php endif; ?>
          <td class="text-center"><?= Helpers::statusBadge($u['status'] ? 'active' : 'inactive') ?></td>
          <td class="text-center">
            <div class="flex items-center justify-center gap-1">
              <?php if ($role === 'sr'): ?>
              <!-- Price Correction Toggle -->
              <button type="button" 
                      onclick="toggleSrPriceCorrection(<?= $u['id'] ?>, <?= $u['can_correct_price'] ?? 1 ?>)" 
                      class="btn btn-secondary btn-sm <?= ($u['can_correct_price'] ?? 1) ? 'text-blue-600 hover:text-blue-700 hover:bg-blue-50' : 'text-slate-400 hover:text-slate-500 hover:bg-slate-50' ?>" 
                      title="<?= ($u['can_correct_price'] ?? 1) ? 'Price Correction: Open (Click to Close)' : 'Price Correction: Closed (Click to Open)' ?>"
                      id="price-toggle-<?= $u['id'] ?>">
                <i class="fa-solid <?= ($u['can_correct_price'] ?? 1) ? 'fa-unlock' : 'fa-lock' ?>" id="price-icon-<?= $u['id'] ?>"></i>
              </button>
              <button type="button" onclick="openSrCutoffModal(<?= $u['id'] ?>, '<?= h(addslashes($u['name'])) ?>')" class="btn btn-secondary btn-sm text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50" title="Order Completion by Date">
                <i class="fa-solid fa-calendar-check"></i>
              </button>
              <?php endif; ?>
              <a href="<?= url("admin/{$role}s/edit/".$u['id']) ?>" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-pen"></i>
              </a>
              <form method="POST" action="<?= url("admin/{$role}s/delete/".$u['id']) ?>" data-confirm-form="Delete this user?">
                <?= Helpers::csrfField() ?>
                <button class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($items)): ?>
          <tr><td colspan="<?= $role === 'sr' ? '8' : '7' ?>" class="text-center py-8 text-gray-400">No <?= strtolower($pageTitle) ?> found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if ($role === 'sr'): ?>
<!-- SR 5 Days Order Completion Modal -->
<div id="srCutoffModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-200">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col border border-slate-100 transform scale-95 transition-all duration-200" id="srCutoffModalContainer">
    <!-- Header -->
    <div class="px-5 py-4 bg-slate-800 text-white flex items-center justify-between border-b border-slate-700">
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-sm">
          <i class="fa-solid fa-calendar-check"></i>
        </div>
        <div>
          <h3 class="font-bold text-sm leading-tight">Order Completion (Last 5 Days)</h3>
          <p class="text-xs text-slate-300 font-normal" id="srCutoffModalSubTitle">SR: Loading...</p>
        </div>
      </div>
      <button type="button" onclick="closeSrCutoffModal()" class="w-7 h-7 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-300 flex items-center justify-center transition">
        <i class="fa-solid fa-xmark text-sm"></i>
      </button>
    </div>

    <!-- Body -->
    <div class="p-5 overflow-y-auto max-h-[70vh]">
      <div id="srCutoffLoading" class="py-8 text-center text-slate-400">
        <i class="fa-solid fa-spinner fa-spin text-2xl text-blue-600 mb-2"></i>
        <p class="text-xs">Loading 5-day orders data...</p>
      </div>

      <div id="srCutoffContent" class="hidden space-y-3">
        <!-- List will be populated dynamically -->
      </div>
    </div>

    <!-- Footer -->
    <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
      <span>Toggle switch to change completion status</span>
      <button type="button" onclick="closeSrCutoffModal()" class="px-4 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold rounded-lg transition">Close</button>
    </div>
  </div>
</div>

<script>
let currentModalSrId = null;

async function openSrCutoffModal(srId, srName) {
  currentModalSrId = srId;
  const modal = document.getElementById('srCutoffModal');
  const container = document.getElementById('srCutoffModalContainer');
  const subtitle = document.getElementById('srCutoffModalSubTitle');
  const loading = document.getElementById('srCutoffLoading');
  const content = document.getElementById('srCutoffContent');

  subtitle.textContent = 'SR: ' + srName;
  loading.classList.remove('hidden');
  content.classList.add('hidden');
  content.innerHTML = '';

  modal.classList.remove('hidden');
  modal.classList.add('flex');
  setTimeout(() => {
    container.classList.remove('scale-95');
    container.classList.add('scale-100');
  }, 10);

  try {
    const res = await fetch(`<?= url('admin/api/sr-orders-cutoff') ?>?sr_id=${srId}`);
    const data = await res.json();

    loading.classList.add('hidden');
    if (data.success && data.days) {
      renderSrCutoffDays(srId, data.days);
      content.classList.remove('hidden');
    } else {
      content.innerHTML = `<div class="p-4 text-center text-red-500 text-xs">${data.message || 'Failed to load data.'}</div>`;
      content.classList.remove('hidden');
    }
  } catch (err) {
    loading.classList.add('hidden');
    content.innerHTML = `<div class="p-4 text-center text-red-500 text-xs">Error loading data.</div>`;
    content.classList.remove('hidden');
  }
}

function closeSrCutoffModal() {
  const modal = document.getElementById('srCutoffModal');
  const container = document.getElementById('srCutoffModalContainer');
  container.classList.remove('scale-100');
  container.classList.add('scale-95');
  setTimeout(() => {
    modal.classList.remove('flex');
    modal.classList.add('hidden');
  }, 150);
}

function renderSrCutoffDays(srId, days) {
  const content = document.getElementById('srCutoffContent');
  content.innerHTML = '';

  days.forEach(day => {
    const row = document.createElement('div');
    row.className = `flex items-center justify-between p-3.5 rounded-xl border transition-all ${day.is_completed ? 'bg-emerald-50/60 border-emerald-200' : 'bg-slate-50 border-slate-200'}`;

    const isToday = day.date === new Date().toISOString().split('T')[0];
    const dateLabel = isToday ? `${day.formatted_date} <span class="ml-1 text-[10px] bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded-full font-semibold">Today</span>` : day.formatted_date;

    row.innerHTML = `
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg ${day.is_completed ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600'} flex items-center justify-center text-sm font-bold flex-shrink-0">
          <i class="fa-solid ${day.is_completed ? 'fa-circle-check text-emerald-600' : 'fa-circle-xmark text-slate-400'}"></i>
        </div>
        <div>
          <div class="font-bold text-xs text-slate-800 flex items-center">${dateLabel}</div>
          <div class="text-[11px] text-slate-500 mt-0.5">
            Total Orders: <span class="font-bold text-slate-700">${day.order_count}</span>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <span class="text-[11px] font-semibold ${day.is_completed ? 'text-emerald-700' : 'text-slate-400'}" id="status-text-${day.date}">
          ${day.is_completed ? 'Complete' : 'Not Complete'}
        </span>
        <button type="button" 
                id="toggle-btn-${day.date}"
                onclick="toggleSrCutoffDate(${srId}, '${day.date}', ${day.is_completed})" 
                class="w-12 h-6 rounded-full p-0.5 transition-colors duration-200 ease-in-out focus:outline-none flex items-center ${day.is_completed ? 'bg-emerald-500' : 'bg-slate-300'}"
                title="${day.is_completed ? 'Click to mark Not Complete' : 'Click to mark Complete'}">
          <div class="w-5 h-5 rounded-full bg-white shadow-md transform transition-transform duration-200 ease-in-out ${day.is_completed ? 'translate-x-6' : 'translate-x-0'} flex items-center justify-center">
            <i class="fa-solid ${day.is_completed ? 'fa-check text-[10px] text-emerald-600' : 'fa-xmark text-[10px] text-slate-400'}" id="toggle-icon-${day.date}"></i>
          </div>
        </button>
      </div>
    `;
    content.appendChild(row);
  });
}

async function toggleSrCutoffDate(srId, date, currentStatus) {
  const btn = document.getElementById(`toggle-btn-${date}`);
  const statusText = document.getElementById(`status-text-${date}`);
  const icon = document.getElementById(`toggle-icon-${date}`);
  if (!btn) return;

  const newStatus = !currentStatus;

  // Optimistic UI update
  btn.className = `w-12 h-6 rounded-full p-0.5 transition-colors duration-200 ease-in-out focus:outline-none flex items-center ${newStatus ? 'bg-emerald-500' : 'bg-slate-300'}`;
  btn.querySelector('div').className = `w-5 h-5 rounded-full bg-white shadow-md transform transition-transform duration-200 ease-in-out ${newStatus ? 'translate-x-6' : 'translate-x-0'} flex items-center justify-center`;
  if (statusText) {
    statusText.textContent = newStatus ? 'Complete' : 'Not Complete';
    statusText.className = `text-[11px] font-semibold ${newStatus ? 'text-emerald-700' : 'text-slate-400'}`;
  }
  if (icon) {
    icon.className = `fa-solid ${newStatus ? 'fa-check text-[10px] text-emerald-600' : 'fa-xmark text-[10px] text-slate-400'}`;
  }
  btn.setAttribute('onclick', `toggleSrCutoffDate(${srId}, '${date}', ${newStatus})`);

  try {
    const res = await fetch(`<?= url('admin/api/sr-orders-cutoff/toggle') ?>`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ sr_id: srId, date: date, completed: newStatus })
    });
    const result = await res.json();
    if (!result.success) {
      alert(result.message || 'Failed to update order status');
      toggleSrCutoffDate(srId, date, newStatus);
    }
  } catch (err) {
    alert('Network error updating status');
    toggleSrCutoffDate(srId, date, newStatus);
  }
}
async function toggleSrPriceCorrection(srId, currentStatus) {
  const btn = document.getElementById(`price-toggle-${srId}`);
  const icon = document.getElementById(`price-icon-${srId}`);
  if (!btn) return;

  const newStatus = currentStatus ? 0 : 1;
  
  // Optimistic UI update
  btn.className = `btn btn-secondary btn-sm ${newStatus ? 'text-blue-600 hover:text-blue-700 hover:bg-blue-50' : 'text-slate-400 hover:text-slate-500 hover:bg-slate-50'}`;
  btn.title = newStatus ? 'Price Correction: Open (Click to Close)' : 'Price Correction: Closed (Click to Open)';
  icon.className = `fa-solid ${newStatus ? 'fa-unlock' : 'fa-lock'}`;
  btn.setAttribute('onclick', `toggleSrPriceCorrection(${srId}, ${newStatus})`);

  try {
    const res = await fetch(`<?= url('admin/api/sr-price-correction/toggle') ?>`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ sr_id: srId, can_correct: newStatus })
    });
    const result = await res.json();
    if (!result.success) {
      alert(result.message || 'Failed to update price correction access');
      // Revert on failure
      toggleSrPriceCorrection(srId, newStatus); 
    }
  } catch (err) {
    alert('Network error updating status');
    // Revert on failure
    toggleSrPriceCorrection(srId, newStatus);
  }
}
</script>
<?php endif; ?>

