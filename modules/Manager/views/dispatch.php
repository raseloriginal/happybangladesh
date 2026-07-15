<?php $pageTitle = 'Dispatch Management'; ?>

<style>
/* Game-vibe styles for the wire connector */
#wire-canvas {
  position: absolute;
  top: 0; left: 0; width: 100%; height: 100%;
  pointer-events: none;
  z-index: 10;
}
.connector-card {
  cursor: pointer;
  user-select: none;
}
.connector-card.active {
  border-color: #3b82f6;
  background-color: #eff6ff;
  box-shadow: 0 0 0 2px #3b82f6;
}
.connector-card.connected {
  border-color: #10b981;
  background-color: #f0fdf4;
}

.wire-path {
  fill: none;
  stroke-width: 4;
  stroke-linecap: round;
  transition: stroke-dashoffset 0.5s ease;
}
.wire-path.glow {
  filter: drop-shadow(0 0 6px rgba(37, 99, 235, 0.8));
  stroke: #3b82f6;
}
.wire-path.connected {
  filter: drop-shadow(0 0 6px rgba(16, 185, 129, 0.8));
  stroke: #10b981;
}

/* Table expansions */
.expand-row { display: none; background: #f8fafc; }
.expand-row.open { display: table-row; }
.sub-table th { font-size: 0.75rem; text-transform: uppercase; color: #64748b; background: #f1f5f9; padding: 0.5rem; }
.sub-table td { padding: 0.5rem; font-size: 0.875rem; border-bottom: 1px solid #e2e8f0; }

.status-badge { padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; }
.status-assigned { background: #dbeafe; color: #1e40af; }
.status-organized { background: #fef3c7; color: #92400e; }
.status-dispatched { background: #d1fae5; color: #065f46; }
.status-returned { background: #fee2e2; color: #991b1b; }
</style>

<div class="page-header flex justify-between items-center mb-6">
  <div>
    <h1 class="page-title text-2xl font-bold text-gray-800">Dispatch Management</h1>
    <div class="breadcrumb text-sm text-gray-500">Manager &rsaquo; Dispatch</div>
  </div>
  <button onclick="openWireModal()" class="btn btn-primary bg-brand hover:bg-brand-dark text-white px-4 py-2 rounded-lg shadow-lg flex items-center gap-2 transition-all">
    <i class="fa-solid fa-bolt"></i> New Dispatch Assignment
  </button>
</div>

<div class="card bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
  <div class="card-header border-b border-gray-100 p-5 flex justify-between items-center">
    <h2 class="card-title text-lg font-semibold text-gray-800">Dispatch Schedules</h2>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-left border-collapse" id="main-table">
      <thead>
        <tr class="bg-gray-50 border-b border-gray-100">
          <th class="p-4 font-medium text-gray-500">Date</th>
          <th class="p-4 font-medium text-gray-500">DSR</th>
          <th class="p-4 font-medium text-gray-500">Dispatch Value</th>
          <th class="p-4 font-medium text-gray-500">Return Value</th>
          <th class="p-4 font-medium text-gray-500">Damage Value</th>
          <th class="p-4 font-medium text-gray-500">Status</th>
          <th class="p-4 font-medium text-gray-500 text-right">Action</th>
        </tr>
      </thead>
      <tbody id="schedules-tbody">
        <!-- Rendered via JS -->
      </tbody>
    </table>
  </div>
</div>

<!-- ========================================== -->
<!-- 1. WIRE CONNECTION MODAL (NEW DISPATCH)    -->
<!-- ========================================== -->
<div id="wire-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl h-[85vh] flex flex-col relative overflow-hidden">
    <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
      <div class="flex items-center gap-4">
        <h2 class="text-xl font-bold text-gray-800"><i class="fa-solid fa-network-wired text-brand mr-2"></i> Assign SRs to DSR</h2>
        <input type="date" id="wire-date" class="form-input rounded-md border-gray-300 text-sm" value="<?= date('Y-m-d') ?>" onchange="loadWireData()">
      </div>
      <button onclick="closeWireModal()" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-xl"></i></button>
    </div>
    
    <div class="flex-1 relative bg-slate-50 flex overflow-hidden p-6 gap-20" id="wire-container">
      <svg id="wire-canvas"></svg>
      
      <!-- Left Panel: SRs -->
      <div class="w-1/2 flex flex-col z-20">
        <h3 class="font-semibold text-gray-600 mb-4 uppercase tracking-wider text-sm flex items-center gap-2"><i class="fa-solid fa-users"></i> Available SRs</h3>
        <div id="sr-list" class="flex-1 overflow-y-auto space-y-3 pr-2 pb-4">
          <!-- SR Cards -->
        </div>
      </div>
      
      <!-- Right Panel: DSRs -->
      <div class="w-1/2 flex flex-col z-20">
        <h3 class="font-semibold text-gray-600 mb-4 uppercase tracking-wider text-sm flex items-center gap-2"><i class="fa-solid fa-truck"></i> Delivery DSRs</h3>
        <div id="dsr-list" class="flex-1 overflow-y-auto space-y-3 pl-2 pb-4">
          <!-- DSR Cards -->
        </div>
      </div>
    </div>
    
    <div class="p-4 border-t border-gray-100 bg-white flex justify-between items-center z-20">
      <div class="text-sm text-gray-500"><i class="fa-solid fa-circle-info text-blue-500"></i> Click an SR, then click a DSR to connect. Click a DSR, then connected SR to disconnect.</div>
      <button onclick="saveWireAssignments()" class="btn btn-primary bg-brand hover:bg-brand-dark text-white px-6 py-2.5 rounded-lg shadow font-medium">Save Assignments</button>
    </div>
  </div>
</div>

<!-- ========================================== -->
<!-- 2. ORGANIZE MODAL                          -->
<!-- ========================================== -->
<div id="organize-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col">
    <div class="p-5 border-b border-gray-100 flex justify-between items-center">
      <h2 class="text-xl font-bold text-gray-800"><i class="fa-solid fa-box-open text-amber-500 mr-2"></i> Organize Dispatch Items</h2>
      <button onclick="closeOrganizeModal()" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-xl"></i></button>
    </div>
    
    <div class="flex-1 overflow-y-auto p-5 bg-gray-50">
      <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-left">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="p-3 text-xs font-semibold text-gray-500 uppercase">Product</th>
              <th class="p-3 text-xs font-semibold text-gray-500 uppercase">Total Ordered</th>
              <th class="p-3 text-xs font-semibold text-gray-500 uppercase">Add Extra (Box | Pcs)</th>
              <th class="p-3 text-xs font-semibold text-gray-500 uppercase text-center">Organized?</th>
            </tr>
          </thead>
          <tbody id="organize-tbody" class="divide-y divide-gray-100">
            <!-- Rows injected via JS -->
          </tbody>
        </table>
      </div>
    </div>
    
    <div class="p-5 border-t border-gray-100 flex justify-end gap-3 bg-white">
      <button onclick="closeOrganizeModal()" class="px-5 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium">Cancel</button>
      <button onclick="saveOrganize(event)" class="px-5 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-medium shadow">Save Organized</button>
    </div>
  </div>
</div>

<script>
// ============================================================================
// MAIN TABLE LOGIC
// ============================================================================
let schedules = [];

async function loadSchedules() {
  const res = await fetch('<?= url("manager/api/dispatch/data") ?>');
  schedules = await res.json();
  renderSchedules();
}

function renderSchedules() {
  const tbody = document.getElementById('schedules-tbody');
  tbody.innerHTML = '';
  
  if (schedules.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7" class="p-8 text-center text-gray-400">No dispatches found.</td></tr>`;
    return;
  }
  
  schedules.forEach(sch => {
    // Determine buttons based on status
    let actionBtn = '';
    if (sch.status === 'assigned') {
      actionBtn = `<button onclick="openOrganizeModal(${sch.id})" class="text-amber-600 hover:bg-amber-50 px-2 py-1 rounded text-sm font-medium border border-amber-200"><i class="fa-solid fa-box-open mr-1"></i> Organize</button>`;
    } else if (sch.status === 'organized') {
      actionBtn = `<button onclick="updateStatus(${sch.id}, 'dispatched')" class="text-emerald-600 hover:bg-emerald-50 px-2 py-1 rounded text-sm font-medium border border-emerald-200"><i class="fa-solid fa-truck-fast mr-1"></i> Dispatch</button>`;
    } else if (sch.status === 'dispatched') {
      actionBtn = `<button onclick="updateStatus(${sch.id}, 'returned')" class="text-gray-600 hover:bg-gray-50 px-2 py-1 rounded text-sm font-medium border border-gray-200"><i class="fa-solid fa-rotate-left mr-1"></i> Return</button>`;
    }

    const tr = document.createElement('tr');
    tr.className = 'border-b border-gray-50 hover:bg-gray-50/50 transition-colors group';
    tr.innerHTML = `
      <td class="p-4 text-sm">${sch.dispatch_date}</td>
      <td class="p-4 font-medium text-gray-800">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">${sch.dsr_name.charAt(0)}</div>
          ${sch.dsr_name}
        </div>
      </td>
      <td class="p-4 text-sm font-medium">৳ ${parseFloat(sch.total_dispatch_value).toLocaleString()}</td>
      <td class="p-4 text-sm text-red-600">৳ ${parseFloat(sch.total_return_value).toLocaleString()}</td>
      <td class="p-4 text-sm text-orange-500">৳ ${parseFloat(sch.total_damage_value).toLocaleString()}</td>
      <td class="p-4"><span class="status-badge status-${sch.status}">${sch.status.toUpperCase()}</span></td>
      <td class="p-4 text-right">
        <div class="flex items-center justify-end gap-2">
          ${actionBtn}
          <button onclick="toggleSrRow(${sch.id})" class="w-8 h-8 rounded hover:bg-gray-200 text-gray-500 flex items-center justify-center transition-colors">
            <i class="fa-solid fa-chevron-down transform transition-transform" id="icon-sch-${sch.id}"></i>
          </button>
        </div>
      </td>
    `;
    tbody.appendChild(tr);

    // Expandable Row container
    const expTr = document.createElement('tr');
    expTr.id = `exp-sch-${sch.id}`;
    expTr.className = 'expand-row';
    expTr.innerHTML = `<td colspan="7" class="p-0 border-b border-gray-200"><div id="sr-container-${sch.id}" class="p-4 bg-gray-50/80 shadow-inner">Loading...</div></td>`;
    tbody.appendChild(expTr);
  });
}

async function toggleSrRow(schId) {
  const row = document.getElementById(`exp-sch-${schId}`);
  const icon = document.getElementById(`icon-sch-${schId}`);
  
  if (row.classList.contains('open')) {
    row.classList.remove('open');
    icon.classList.remove('rotate-180');
    return;
  }
  
  row.classList.add('open');
  icon.classList.add('rotate-180');
  
  const container = document.getElementById(`sr-container-${schId}`);
  
  // Fetch SR details
  const res = await fetch(`<?= url("manager/api/dispatch/sr-details/") ?>${schId}`);
  const srs = await res.json();
  
  if (srs.length === 0) {
    container.innerHTML = '<div class="text-sm text-gray-500 py-2">No SRs assigned to this dispatch.</div>';
    return;
  }
  
  let html = `<div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
    <table class="w-full text-left sub-table">
      <thead><tr>
        <th>SR Name</th><th>Orders Value</th><th>Dispatch Items</th><th>Return Items</th><th>Damage</th><th class="text-right">Action</th>
      </tr></thead>
      <tbody>`;
      
  srs.forEach(sr => {
    html += `<tr>
      <td class="font-medium text-gray-700">${sr.name}</td>
      <td class="text-blue-600 font-medium">৳ ${parseFloat(sr.orders_value).toLocaleString()}</td>
      <td>৳ ${parseFloat(sr.dispatch_items_value).toLocaleString()}</td>
      <td class="text-red-500">৳ ${parseFloat(sr.return_items_value).toLocaleString()}</td>
      <td class="text-orange-500">৳ ${parseFloat(sr.damage_value).toLocaleString()}</td>
      <td class="text-right">
        <button onclick="toggleProductRow(${schId}, ${sr.id})" class="text-xs text-gray-500 hover:text-brand px-2 py-1 bg-gray-100 rounded">
          <i class="fa-solid fa-list mr-1"></i> Products
        </button>
      </td>
    </tr>
    <tr id="exp-prod-${schId}-${sr.id}" class="hidden bg-slate-50"><td colspan="6" class="p-0 border-b border-gray-200">
      <div id="prod-container-${schId}-${sr.id}" class="p-3"></div>
    </td></tr>`;
    
    // Store products for later rendering to avoid another fetch if we already have them
    window[`prod_data_${schId}_${sr.id}`] = sr.products;
  });
  
  html += `</tbody></table></div>`;
  container.innerHTML = html;
}

function toggleProductRow(schId, srId) {
  const row = document.getElementById(`exp-prod-${schId}-${srId}`);
  if (row.classList.contains('hidden')) {
    row.classList.remove('hidden');
    const container = document.getElementById(`prod-container-${schId}-${srId}`);
    const products = window[`prod_data_${schId}_${srId}`] || [];
    
    if (products.length === 0) {
      container.innerHTML = '<div class="text-xs text-gray-400">No products found.</div>';
      return;
    }
    
    let html = `<table class="w-full text-xs text-left bg-white border border-gray-100 rounded">
      <thead class="bg-gray-50 text-gray-500"><tr>
        <th class="p-2">Product</th><th class="p-2">Ordered Qty</th><th class="p-2">Dispatched Qty</th>
        <th class="p-2">Returned Qty</th><th class="p-2">Sale Value</th>
      </tr></thead><tbody>`;
      
    products.forEach(p => {
      html += `<tr class="border-t border-gray-50 hover:bg-gray-50">
        <td class="p-2 font-medium">${p.name}</td>
        <td class="p-2">${p.ordered_qty}</td>
        <td class="p-2">${p.dispatched_qty}</td>
        <td class="p-2 text-red-500">${p.returned_qty}</td>
        <td class="p-2 font-medium text-emerald-600">৳ ${parseFloat(p.sale_value).toLocaleString()}</td>
      </tr>`;
    });
    html += `</tbody></table>`;
    container.innerHTML = html;
  } else {
    row.classList.add('hidden');
  }
}

async function updateStatus(id, status) {
  if(!confirm(`Mark this schedule as ${status.toUpperCase()}?`)) return;
  const res = await fetch(`<?= url("manager/api/dispatch/status-update/") ?>${id}`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ status })
  });
  const data = await res.json();
  if (data.success) {
    loadSchedules();
  } else {
    alert("Error updating status");
  }
}

// ============================================================================
// ORGANIZATION LOGIC
// ============================================================================
let currentOrgId = null;

async function openOrganizeModal(schId) {
  currentOrgId = schId;
  const res = await fetch(`<?= url("manager/api/dispatch/organize-data/") ?>${schId}`);
  const products = await res.json();
  
  const tbody = document.getElementById('organize-tbody');
  tbody.innerHTML = '';
  
  if (products.length === 0) {
    tbody.innerHTML = '<tr><td colspan="4" class="text-center p-5 text-gray-400">No products found for these orders.</td></tr>';
  } else {
    products.forEach(p => {
      const img = p.image ? `<img src="<?= BASE_URL ?>/${p.image}" class="w-10 h-10 rounded object-cover border border-gray-200">` : `<div class="w-10 h-10 rounded bg-gray-100 flex items-center justify-center"><i class="fa-solid fa-box text-gray-300"></i></div>`;
      
      const boxes = Math.floor(p.total_ordered_qty / Math.max(1, p.pieces_per_box));
      const pcs = p.total_ordered_qty % Math.max(1, p.pieces_per_box);
      const qtyStr = `${boxes} box | ${pcs} pcs`;

      tbody.innerHTML += `
        <tr class="hover:bg-gray-50/50" data-pid="${p.product_id}">
          <td class="p-3">
            <div class="flex items-center gap-3">
              ${img}
              <div class="font-medium text-gray-800">${p.name}</div>
            </div>
          </td>
          <td class="p-3">
            <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-sm font-medium border border-gray-200">${qtyStr}</span>
            <div class="text-xs text-gray-400 mt-1">Total: ${p.total_ordered_qty} pcs</div>
          </td>
          <td class="p-3">
            <div class="flex items-center gap-2">
              <input type="number" min="0" class="org-extra-box w-16 text-sm border-gray-300 rounded" placeholder="Box">
              <input type="number" min="0" class="org-extra-pcs w-16 text-sm border-gray-300 rounded" placeholder="Pcs">
            </div>
          </td>
          <td class="p-3 text-center">
            <input type="checkbox" class="org-check w-5 h-5 text-amber-500 rounded border-gray-300 focus:ring-amber-500">
          </td>
        </tr>
      `;
    });
  }
  
  document.getElementById('organize-modal').classList.remove('hidden');
}

function closeOrganizeModal() {
  document.getElementById('organize-modal').classList.add('hidden');
  currentOrgId = null;
}

async function saveOrganize(event) {
  if (!currentOrgId) return;
  
  // Validate if all are checked
  const unckecked = document.querySelectorAll('.org-check:not(:checked)');
  if (unckecked.length > 0) {
    if (!confirm(`You have ${unckecked.length} items not marked as organized. Are you sure you want to save?`)) {
      return;
    }
  }

  const extras = [];
  document.querySelectorAll('#organize-tbody tr').forEach(tr => {
    const pid = tr.getAttribute('data-pid');
    const box = parseInt(tr.querySelector('.org-extra-box').value) || 0;
    const pcs = parseInt(tr.querySelector('.org-extra-pcs').value) || 0;
    if (box > 0 || pcs > 0) {
      extras.push({ product_id: pid, boxes: box, pcs: pcs });
    }
  });

  const btn = event.target;
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

  const res = await fetch(`<?= url("manager/api/dispatch/organize-save/") ?>${currentOrgId}`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ extras })
  });

  const data = await res.json();
  if (data.success) {
    closeOrganizeModal();
    loadSchedules();
  } else {
    alert("Error saving: " + data.message);
  }
  
  btn.disabled = false;
  btn.innerHTML = 'Save Organized';
}

// ============================================================================
// WIRE CONNECTOR LOGIC
// ============================================================================
let activeSrId = null;
let activeDsrId = null;
// connections: sr_id -> dsr_id
let connections = {}; 
let srElements = {};
let dsrElements = {};

function openWireModal() {
  document.getElementById('wire-modal').classList.remove('hidden');
  loadWireData();
}

function closeWireModal() {
  document.getElementById('wire-modal').classList.add('hidden');
  clearWires();
}

async function loadWireData() {
  const date = document.getElementById('wire-date').value;
  const res = await fetch(`<?= url("manager/api/dispatch/new-popup-data") ?>?date=${date}`);
  const data = await res.json();
  
  renderSrList(data.srs);
  renderDsrList(data.dsrs);
  clearWires();
}

function renderSrList(srs) {
  const container = document.getElementById('sr-list');
  container.innerHTML = '';
  srElements = {};
  
  if (srs.length === 0) {
    container.innerHTML = '<div class="text-sm text-gray-500 italic p-4">No SRs have orders for this date, or all are already assigned.</div>';
    return;
  }
  
  srs.forEach(sr => {
    const div = document.createElement('div');
    div.className = 'connector-card bg-white p-4 rounded-xl border border-gray-200 flex items-center justify-between shadow-sm relative';
    div.id = `sr-card-${sr.id}`;
    div.innerHTML = `
      <div class="flex items-center gap-3 pointer-events-none">
        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold">${sr.name.charAt(0)}</div>
        <div>
          <div class="font-bold text-gray-800">${sr.name}</div>
          <div class="text-xs text-gray-500">ID: #${sr.id}</div>
        </div>
      </div>
      <div class="pointer-events-none">
        <span class="bg-amber-100 text-amber-700 text-xs px-2 py-1 rounded-lg font-bold">${sr.order_count} Orders</span>
      </div>
      <!-- Connection dot -->
      <div class="absolute right-0 top-1/2 transform -translate-y-1/2 translate-x-1/2 w-4 h-4 rounded-full bg-gray-300 border-4 border-white dot" id="sr-dot-${sr.id}"></div>
    `;
    div.onclick = () => handleSrClick(sr.id);
    container.appendChild(div);
    srElements[sr.id] = document.getElementById(`sr-dot-${sr.id}`);
  });
}

function renderDsrList(dsrs) {
  const container = document.getElementById('dsr-list');
  container.innerHTML = '';
  dsrElements = {};
  
  dsrs.forEach(dsr => {
    const div = document.createElement('div');
    div.className = 'connector-card bg-white p-4 rounded-xl border border-gray-200 flex items-center shadow-sm relative pl-8';
    div.id = `dsr-card-${dsr.id}`;
    div.innerHTML = `
      <!-- Connection dot -->
      <div class="absolute left-0 top-1/2 transform -translate-y-1/2 -translate-x-1/2 w-4 h-4 rounded-full bg-gray-300 border-4 border-white dot" id="dsr-dot-${dsr.id}"></div>
      
      <div class="flex items-center gap-3 pointer-events-none">
        <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold">${dsr.name.charAt(0)}</div>
        <div>
          <div class="font-bold text-gray-800">${dsr.name}</div>
          <div class="text-xs text-gray-500">DSR</div>
        </div>
      </div>
      <div class="ml-auto pointer-events-none">
         <span id="dsr-count-${dsr.id}" class="text-xs font-bold text-gray-400 bg-gray-100 px-2 py-1 rounded-lg">0 SRs</span>
      </div>
    `;
    div.onclick = () => handleDsrClick(dsr.id);
    container.appendChild(div);
    dsrElements[dsr.id] = document.getElementById(`dsr-dot-${dsr.id}`);
  });
}

function handleSrClick(id) {
  document.querySelectorAll('#sr-list .connector-card').forEach(el => el.classList.remove('active'));
  document.getElementById(`sr-card-${id}`).classList.add('active');
  activeSrId = id;
}

function handleDsrClick(id) {
  if (activeSrId) {
    // Connect!
    connections[activeSrId] = id;
    
    // Reset active SR
    document.getElementById(`sr-card-${activeSrId}`).classList.remove('active');
    activeSrId = null;
    
    updateVisuals();
  } else {
    // Highlight DSR and prepare to disconnect SRs clicked next
    document.querySelectorAll('#dsr-list .connector-card').forEach(el => el.classList.remove('active'));
    document.getElementById(`dsr-card-${id}`).classList.add('active');
    
    // Add temporary event to SRs connected to this DSR to disconnect
    Object.keys(connections).forEach(sId => {
      if (connections[sId] === id) {
        const card = document.getElementById(`sr-card-${sId}`);
        card.onclick = () => {
          delete connections[sId];
          document.getElementById(`dsr-card-${id}`).classList.remove('active');
          
          // Restore original click handler for all SRs
          Object.keys(connections).forEach(resSId => {
             const resCard = document.getElementById(`sr-card-${resSId}`);
             if(resCard) resCard.onclick = () => handleSrClick(parseInt(resSId));
          });
          document.querySelectorAll('#sr-list .connector-card').forEach(c => {
             const cId = parseInt(c.id.replace('sr-card-',''));
             c.onclick = () => handleSrClick(cId);
          });
          updateVisuals();
        };
      }
    });
  }
}

function clearWires() {
  connections = {};
  activeSrId = null;
  document.getElementById('wire-canvas').innerHTML = '';
  document.querySelectorAll('.connector-card').forEach(el => {
    el.classList.remove('active', 'connected');
  });
  document.querySelectorAll('.dot').forEach(el => {
    el.classList.remove('bg-brand', 'bg-emerald-500');
    el.classList.add('bg-gray-300');
  });
  document.querySelectorAll('[id^="dsr-count-"]').forEach(el => {
    el.innerText = '0 SRs';
    el.className = 'text-xs font-bold text-gray-400 bg-gray-100 px-2 py-1 rounded-lg';
  });
}

function updateVisuals() {
  const svg = document.getElementById('wire-canvas');
  svg.innerHTML = '';
  
  // Reset styles
  document.querySelectorAll('.connector-card').forEach(el => el.classList.remove('connected'));
  document.querySelectorAll('.dot').forEach(el => {
    el.classList.remove('bg-brand', 'bg-emerald-500');
    el.classList.add('bg-gray-300');
  });
  
  const dsrCounts = {};
  
  Object.keys(connections).forEach(sId => {
    const dId = connections[sId];
    dsrCounts[dId] = (dsrCounts[dId] || 0) + 1;
    
    const sCard = document.getElementById(`sr-card-${sId}`);
    const dCard = document.getElementById(`dsr-card-${dId}`);
    const sDot = document.getElementById(`sr-dot-${sId}`);
    const dDot = document.getElementById(`dsr-dot-${dId}`);
    
    if (sCard && dCard) {
      sCard.classList.add('connected');
      dCard.classList.add('connected');
      sDot.classList.replace('bg-gray-300', 'bg-emerald-500');
      dDot.classList.replace('bg-gray-300', 'bg-emerald-500');
      
      // Draw SVG path
      const rectContainer = document.getElementById('wire-container').getBoundingClientRect();
      const rectS = sDot.getBoundingClientRect();
      const rectD = dDot.getBoundingClientRect();
      
      const startX = rectS.left - rectContainer.left + 8; // 8 is half of dot width
      const startY = rectS.top - rectContainer.top + 8;
      const endX = rectD.left - rectContainer.left + 8;
      const endY = rectD.top - rectContainer.top + 8;
      
      // Bezier curve to make it look like a physical wire drooping slightly
      const cpX1 = startX + (endX - startX) / 2;
      const cpY1 = startY;
      const cpX2 = startX + (endX - startX) / 2;
      const cpY2 = endY;
      
      const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
      path.setAttribute("d", `M ${startX} ${startY} C ${cpX1} ${cpY1}, ${cpX2} ${cpY2}, ${endX} ${endY}`);
      path.setAttribute("class", "wire-path connected");
      svg.appendChild(path);
    }
  });
  
  // Update badges
  document.querySelectorAll('[id^="dsr-count-"]').forEach(el => {
    const id = parseInt(el.id.replace('dsr-count-', ''));
    const count = dsrCounts[id] || 0;
    el.innerText = `${count} SRs`;
    if (count > 0) {
      el.className = 'text-xs font-bold text-emerald-700 bg-emerald-100 px-2 py-1 rounded-lg';
    } else {
      el.className = 'text-xs font-bold text-gray-400 bg-gray-100 px-2 py-1 rounded-lg';
    }
  });
}

// Redraw lines on window resize or scroll inside lists
window.addEventListener('resize', () => {
  if (!document.getElementById('wire-modal').classList.contains('hidden')) {
    updateVisuals();
  }
});
document.getElementById('sr-list').addEventListener('scroll', updateVisuals);
document.getElementById('dsr-list').addEventListener('scroll', updateVisuals);

async function saveWireAssignments() {
  const date = document.getElementById('wire-date').value;
  
  // Pivot connections into: { dsr_id: [sr_id, ...] }
  const assignments = {};
  Object.keys(connections).forEach(sId => {
    const dId = connections[sId];
    if (!assignments[dId]) assignments[dId] = [];
    assignments[dId].push(sId);
  });
  
  if (Object.keys(assignments).length === 0) {
    alert("Please connect at least one SR to a DSR.");
    return;
  }
  
  const res = await fetch(`<?= url("manager/api/dispatch/assign") ?>`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ date, assignments })
  });
  
  const data = await res.json();
  if (data.success) {
    closeWireModal();
    loadSchedules();
  } else {
    alert("Error: " + data.message);
  }
}

// Initialization
document.addEventListener('DOMContentLoaded', loadSchedules);
</script>
