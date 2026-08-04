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

/* Sub-table Excel overrides */
.sub-table th { 
  font-size: 0.725rem; 
  font-weight: 800;
  text-transform: uppercase; 
  letter-spacing: 0.05em;
  color: #1e293b; 
  background: #f1f5f9; 
  padding: 0.6rem 0.8rem;
  border-bottom: 2px solid #cbd5e1;
  border-right: 1px solid #cbd5e1;
}
.sub-table td { 
  padding: 0.6rem 0.8rem; 
  font-size: 0.825rem; 
  border-bottom: 1px solid #e2e8f0; 
  border-right: 1px solid #e2e8f0;
}
.sub-table tr:hover td {
  background: #eff6ff !important;
}

.status-badge { 
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 0.25rem 0.6rem; 
  border-radius: 9999px; 
  font-size: 0.725rem; 
  font-weight: 800; 
  text-transform: uppercase;
  letter-spacing: 0.025em;
}
.status-assigned { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
.status-organized { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
.status-dispatched { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
.status-returned { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

/* Print styling */
@media print {
  body * { visibility: hidden; }
  #viewExcelContainer, #viewExcelContainer * { visibility: visible; }
  #viewExcelContainer { position: absolute; left: 0; top: 0; width: 100%; }
  .excel-ribbon-actions, .no-print { display: none !important; }
}
</style>

<div class="page-header flex justify-between items-center mb-6">
  <div>
    <h1 class="page-title text-2xl font-bold text-gray-800">Dispatch Management</h1>
    <div class="breadcrumb text-sm text-gray-500">Manager &rsaquo; Dispatch</div>
  </div>
</div>

<!-- ============================================================================ -->
<!-- MODERN EXCEL SPREADSHEET DISPATCH VIEW CONTAINER                             -->
<!-- ============================================================================ -->
<div id="viewExcelContainer" class="space-y-4">
  <div class="excel-container">
    
    <!-- Excel Ribbon Toolbar -->
    <div class="excel-ribbon">
      <div class="flex items-center gap-3">
        <div class="excel-ribbon-badge">
          <i class="fa-solid fa-file-excel text-blue-200 text-lg"></i>
          <span>Dispatch Management Spreadsheet</span>
        </div>
        <span class="text-xs text-blue-100 hidden sm:inline-block">• Live Manager Dispatch Data Grid</span>
      </div>

      <div class="flex items-center gap-2 excel-ribbon-actions">
        <button onclick="exportDispatchCSV()" class="excel-action-btn">
          <i class="fa-solid fa-file-csv"></i> Export CSV / Excel
        </button>
        <button onclick="printDispatchSheet()" class="excel-action-btn excel-action-btn-secondary">
          <i class="fa-solid fa-print"></i> Print Sheet
        </button>
        <button onclick="openWireModal()" class="excel-action-btn bg-emerald-600 hover:bg-emerald-700 text-white border-emerald-500 shadow">
          <i class="fa-solid fa-bolt"></i> New Dispatch Assignment
        </button>
      </div>
    </div>

    <!-- Excel Formula & Summary Bar -->
    <div class="excel-formula-bar">
      <span class="fx-symbol">fx</span>
      <div class="excel-pill">
        <i class="fa-solid fa-calculator text-blue-600"></i>
        <span>SCHEDULES: <strong id="fxCount" class="text-blue-700 font-mono">0</strong></span>
      </div>
      <div class="excel-pill">
        <i class="fa-solid fa-bangladeshi-taka-sign text-blue-600"></i>
        <span>TOTAL ORDER: <strong id="fxSumOrder" class="text-blue-700 font-mono">৳0</strong></span>
      </div>
      <div class="excel-pill">
        <i class="fa-solid fa-truck text-indigo-600"></i>
        <span>DISPATCH VALUE: <strong id="fxSumDispatch" class="text-indigo-700 font-mono">৳0</strong></span>
      </div>
      <div class="excel-pill">
        <i class="fa-solid fa-circle-check text-emerald-600"></i>
        <span>TOTAL SALE: <strong id="fxSumSale" class="text-emerald-700 font-mono">৳0</strong></span>
      </div>
      <div class="excel-pill">
        <i class="fa-solid fa-arrow-rotate-left text-rose-600"></i>
        <span>RETURN / DAMAGE: <strong id="fxSumReturn" class="text-rose-700 font-mono">৳0</strong></span>
      </div>
    </div>

    <!-- Excel Grid Table -->
    <div class="overflow-x-auto max-h-[680px]">
      <table class="excel-table" id="dispatchExcelTable">
        <thead>
          <tr>
            <th class="excel-row-num">#</th>
            <th>Dates (Order & Delivery)</th>
            <th>DSR Name</th>
            <th class="text-right">Order Value</th>
            <th class="text-right">Dispatch Value</th>
            <th class="text-right">Return Value</th>
            <th class="text-right">Damage Value</th>
            <th class="text-right">Sale Value</th>
            <th class="text-center">Status</th>
            <th class="text-center no-print">Action</th>
          </tr>
        </thead>
        <tbody id="schedules-tbody">
          <!-- Rendered via JS -->
        </tbody>
      </table>
    </div>

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
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden">
    <div class="p-4 bg-amber-500 text-white flex justify-between items-center">
      <h2 class="text-lg font-bold flex items-center gap-2"><i class="fa-solid fa-box-open"></i> Organize Dispatch Items</h2>
      <button onclick="closeOrganizeModal()" class="text-white/80 hover:text-white"><i class="fa-solid fa-xmark text-xl"></i></button>
    </div>
    
    <div class="flex-1 overflow-y-auto p-4 bg-gray-50">
      <div class="excel-container">
        <table class="excel-table">
          <thead>
            <tr>
              <th class="excel-row-num">#</th>
              <th>Product</th>
              <th>Ordered Qty</th>
              <th>Dispatch Qty</th>
              <th class="text-center">Change (কম / বেশি)</th>
              <th class="text-center">Organized?</th>
            </tr>
          </thead>
          <tbody id="organize-tbody">
            <!-- Rows injected via JS -->
          </tbody>
        </table>
      </div>
    </div>
    
    <div class="p-4 border-t border-gray-200 flex justify-end gap-3 bg-white">
      <button onclick="closeOrganizeModal()" class="px-5 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 font-medium">Cancel</button>
      <button onclick="saveOrganize(event)" class="px-5 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-bold shadow-md">Save Organized</button>
    </div>
  </div>
</div>

<!-- ========================================== -->
<!-- 3. EDIT DSR MODAL                          -->
<!-- ========================================== -->
<div id="edit-dsr-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
  <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm overflow-hidden transform transition-all p-6">
    <h3 class="text-lg font-bold text-gray-800 mb-4"><i class="fa-solid fa-user-pen text-blue-500 mr-2"></i> Change Dispatch DSR</h3>
    <input type="hidden" id="edit-dsr-schedule-id">
    <div class="mb-5">
      <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Select New DSR</label>
      <select id="edit-dsr-select" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all font-semibold text-gray-700">
        <!-- populated via JS -->
      </select>
    </div>
    <div class="flex gap-3">
      <button onclick="closeEditDsrModal()" class="flex-1 py-3 bg-gray-100 text-gray-600 font-bold rounded-xl active:bg-gray-200 transition">Cancel</button>
      <button onclick="saveDsrChange()" class="flex-1 py-3 bg-blue-600 text-white font-bold rounded-xl active:scale-[0.98] shadow-lg shadow-blue-500/20 transition">Save Change</button>
    </div>
  </div>
</div>

<!-- ========================================== -->
<!-- 3.5. EDIT DELIVERY DATE MODAL              -->
<!-- ========================================== -->
<div id="edit-delivery-date-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
  <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm overflow-hidden transform transition-all p-6">
    <h3 class="text-lg font-bold text-gray-800 mb-4"><i class="fa-solid fa-calendar-days text-blue-500 mr-2"></i> Change Delivery Date</h3>
    <input type="hidden" id="edit-delivery-date-schedule-id">
    <div class="mb-5">
      <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Select New Delivery Date</label>
      <input type="date" id="edit-delivery-date-input" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all font-semibold text-gray-700">
    </div>
    <div class="flex gap-3">
      <button onclick="closeEditDeliveryDateModal()" class="flex-1 py-3 bg-gray-100 text-gray-600 font-bold rounded-xl active:bg-gray-200 transition">Cancel</button>
      <button onclick="saveDeliveryDateChange()" class="flex-1 py-3 bg-blue-600 text-white font-bold rounded-xl active:scale-[0.98] shadow-lg shadow-blue-500/20 transition">Save Date</button>
    </div>
  </div>
</div>

<!-- ========================================== -->
<!-- 4. RETURN MODAL                            -->
<!-- ========================================== -->
<div id="return-modal" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl w-full max-w-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
    <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
      <h3 class="font-bold text-gray-800 text-lg flex items-center gap-2"><i class="fa-solid fa-rotate-left text-gray-500"></i> Process Returns</h3>
      <button onclick="window.closeReturnModal()" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-xl"></i></button>
    </div>
    <div class="p-6 overflow-y-auto">
      <input type="hidden" id="return-schedule-id">
      <div id="return-modal-content"></div>
    </div>
    <div class="p-5 border-t border-gray-100 flex justify-end gap-3 bg-white">
      <button onclick="window.closeReturnModal()" class="px-5 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium">Cancel</button>
      <button onclick="window.submitReturn()" class="px-5 py-2 rounded-lg bg-gray-800 text-white font-medium hover:bg-gray-900 transition flex items-center gap-2">
        <i class="fa-solid fa-check"></i> Confirm Returns
      </button>
    </div>
  </div>
</div>

<script>
// ============================================================================
// MAIN SPREADSHEET LOGIC
// ============================================================================
let schedules = [];
let allDsrs = [];

async function fetchDsrs() {
  const res = await fetch('<?= url("manager/api/dispatch/new-popup-data") ?>');
  const data = await res.json();
  allDsrs = data.dsrs || [];
}

async function loadSchedules() {
  if (allDsrs.length === 0) {
    await fetchDsrs();
  }
  const res = await fetch('<?= url("manager/api/dispatch/data") ?>');
  schedules = await res.json();
  renderSchedules();
}

function renderSchedules() {
  const tbody = document.getElementById('schedules-tbody');
  tbody.innerHTML = '';
  
  if (schedules.length === 0) {
    tbody.innerHTML = `<tr><td colspan="10" class="p-12 text-center text-gray-400 bg-white font-medium">
      <i class="fa-solid fa-file-excel text-4xl text-gray-300 mb-3 block"></i>
      No dispatches found in spreadsheet sheet.
    </td></tr>`;
    updateFormulaBar(0, 0, 0, 0, 0);
    return;
  }

  let totalOrderVal = 0;
  let totalDispatchVal = 0;
  let totalSaleVal = 0;
  let totalReturnDmgVal = 0;
  
  schedules.forEach((sch, idx) => {
    const orderVal = parseFloat(sch.total_order_value || 0);
    const dispatchVal = parseFloat(sch.total_dispatch_value || 0);
    const returnVal = parseFloat(sch.total_return_value || 0);
    const damageVal = parseFloat(sch.total_damage_value || 0);
    const saleVal = parseFloat(sch.total_sale_value || 0);

    totalOrderVal += orderVal;
    if (sch.status === 'dispatched' || sch.status === 'returned') {
      totalDispatchVal += dispatchVal;
      totalSaleVal += saleVal;
      totalReturnDmgVal += (returnVal + damageVal);
    }

    // Determine buttons based on status
    let actionBtn = '';
    if (sch.status === 'assigned') {
      actionBtn = `<button onclick="openOrganizeModal(${sch.id})" class="text-amber-700 hover:bg-amber-100 px-2.5 py-1 rounded text-xs font-bold border border-amber-300 transition"><i class="fa-solid fa-box-open mr-1"></i> Organize</button>`;
    } else if (sch.status === 'organized') {
      actionBtn = `<button onclick="updateStatus(${sch.id}, 'dispatched')" class="text-emerald-700 hover:bg-emerald-100 px-2.5 py-1 rounded text-xs font-bold border border-emerald-300 transition"><i class="fa-solid fa-truck-fast mr-1"></i> Dispatch</button>`;
    } else if (sch.status === 'dispatched') {
      actionBtn = `<button type="button" onclick="window.openReturnModal(${sch.id}, ${sch.dsr_id}, '${sch.dispatch_date}')" class="text-gray-700 hover:bg-gray-100 px-2.5 py-1 rounded text-xs font-bold border border-gray-300 transition"><i class="fa-solid fa-rotate-left mr-1"></i> Return</button>`;
    }

    const tr = document.createElement('tr');
    tr.className = 'hover:bg-blue-50/50 transition-colors group';
    tr.innerHTML = `
      <td class="excel-row-num">${idx + 1}</td>
      <td class="whitespace-nowrap">
        <div class="text-xs font-bold text-gray-800">Order: ${sch.dispatch_date}</div>
        <div class="text-[11px] text-gray-500 font-medium mt-0.5 flex items-center gap-1">
          <span>Deliv: ${sch.delivery_date || sch.dispatch_date}</span>
          ${sch.status !== 'returned' ? `<button onclick="openEditDeliveryDateModal(${sch.id}, '${sch.delivery_date || sch.dispatch_date}')" class="text-blue-500 hover:text-blue-700 p-0.5 rounded hover:bg-blue-100 transition-colors opacity-0 group-hover:opacity-100 no-print" title="Change Delivery Date"><i class="fa-solid fa-pen text-[10px]"></i></button>` : ''}
        </div>
      </td>
      <td>
        <div class="flex items-center justify-between gap-2 w-full">
          <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs border border-blue-200">${sch.dsr_name.charAt(0)}</div>
            <span class="font-bold text-gray-800 text-xs">${sch.dsr_name}</span>
          </div>
          <button onclick="openEditDsrModal(${sch.id}, ${sch.dsr_id})" class="text-blue-500 hover:text-blue-700 p-1 rounded hover:bg-blue-100 transition-colors opacity-0 group-hover:opacity-100 no-print" title="Change DSR">
            <i class="fa-solid fa-pen text-xs"></i>
          </button>
        </div>
      </td>
      <td class="excel-money">
        ৳ ${orderVal.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}
        ${(()=>{ 
          const oc = parseFloat(sch.total_order_oc||0); 
          if(oc===0) return ''; 
          const sign=oc>0?'+':'-'; 
          const color=oc>0?'#10b981':'#ef4444'; 
          return `<div style="font-size:10px;font-weight:700;color:${color};margin-top:1px;">(${sign}৳${Math.abs(oc).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})})</div>`; 
        })()}
      </td>
      <td class="excel-money">
        ${(sch.status === 'dispatched' || sch.status === 'returned') ? `
          ৳ ${dispatchVal.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}
          ${(()=>{ 
            const oc = parseFloat(sch.total_dispatch_oc||0); 
            if(oc===0) return ''; 
            const sign=oc>0?'+':'-'; 
            const color=oc>0?'#10b981':'#ef4444'; 
            return `<div style="font-size:10px;font-weight:700;color:${color};margin-top:1px;">(${sign}৳${Math.abs(oc).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})})</div>`; 
          })()}
        ` : '-'}
      </td>
      <td class="excel-money text-rose-600">${(sch.status === 'returned') ? '৳ ' + returnVal.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) : '-'}</td>
      <td class="excel-money text-amber-600">${(sch.status === 'dispatched' || sch.status === 'returned') ? '৳ ' + damageVal.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) : '-'}</td>
      <td class="excel-money text-emerald-600">${(sch.status === 'dispatched' || sch.status === 'returned') ? '৳ ' + saleVal.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) : '-'}</td>
      <td class="text-center"><span class="status-badge status-${sch.status}">${sch.status.toUpperCase()}</span></td>
      <td class="text-center no-print">
        <div class="flex items-center justify-center gap-2">
          ${actionBtn}
          ${(sch.status === 'assigned' || sch.status === 'organized') ? `<button onclick="deleteSchedule(${sch.id})" class="text-rose-700 hover:bg-rose-100 px-2 py-1 rounded text-xs border border-rose-300 transition" title="Delete permanently"><i class="fa-solid fa-trash"></i></button>` : ''}
          <button onclick="toggleSrRow(${sch.id})" class="w-7 h-7 rounded hover:bg-slate-200 text-gray-600 flex items-center justify-center transition-colors border border-gray-200">
            <i class="fa-solid fa-chevron-down text-xs transform transition-transform" id="icon-sch-${sch.id}"></i>
          </button>
        </div>
      </td>
    `;
    tbody.appendChild(tr);

    // Expandable Row container
    const expTr = document.createElement('tr');
    expTr.id = `exp-sch-${sch.id}`;
    expTr.className = 'expand-row hidden';
    expTr.innerHTML = `<td colspan="10" class="p-0 border-b border-gray-300 bg-slate-100/70"><div id="sr-container-${sch.id}" class="p-3">Loading...</div></td>`;
    tbody.appendChild(expTr);
  });

  updateFormulaBar(schedules.length, totalOrderVal, totalDispatchVal, totalSaleVal, totalReturnDmgVal);
}

function updateFormulaBar(count, order, dispatch, sale, returnDmg) {
  document.getElementById('fxCount').innerText = `${count} schedules`;
  document.getElementById('fxSumOrder').innerText = `৳${order.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}`;
  document.getElementById('fxSumDispatch').innerText = `৳${dispatch.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}`;
  document.getElementById('fxSumSale').innerText = `৳${sale.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}`;
  document.getElementById('fxSumReturn').innerText = `৳${returnDmg.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}`;
}

async function toggleSrRow(schId) {
  const row = document.getElementById(`exp-sch-${schId}`);
  const icon = document.getElementById(`icon-sch-${schId}`);
  
  if (!row.classList.contains('hidden')) {
    row.classList.add('hidden');
    icon.classList.remove('rotate-180');
    return;
  }
  
  row.classList.remove('hidden');
  icon.classList.add('rotate-180');
  
  const container = document.getElementById(`sr-container-${schId}`);
  
  const sch = schedules.find(s => s.id == schId);
  const showValues = sch && (sch.status === 'dispatched' || sch.status === 'returned');
  
  // Fetch SR details
  const res = await fetch(`<?= url("manager/api/dispatch/sr-details/") ?>${schId}`);
  const srs = await res.json();
  
  if (srs.length === 0) {
    container.innerHTML = '<div class="text-xs text-gray-500 p-2 italic">No SRs assigned to this dispatch.</div>';
    return;
  }
  
  let html = `<div class="excel-container shadow-sm border border-slate-300">
    <table class="excel-table sub-table">
      <thead><tr>
        <th class="excel-row-num">#</th>
        <th>SR Name</th>
        <th class="text-right">Orders Value</th>
        <th class="text-right">Dispatch Items</th>
        <th class="text-right">Return Items</th>
        <th class="text-right">Damage</th>
        <th class="text-right">Sale Value</th>
        <th class="text-center no-print">Action</th>
      </tr></thead>
      <tbody>`;
      
  srs.forEach((sr, srIdx) => {
    const ordersVal = parseFloat(sr.orders_value || 0);
    const dispatchItemsVal = parseFloat(sr.dispatch_items_value || 0);
    const returnItemsVal = parseFloat(sr.return_items_value || 0);
    const damageVal = parseFloat(sr.damage_value || 0);
    const saleVal = parseFloat(sr.sale_value || 0);

    html += `<tr>
      <td class="excel-row-num">${srIdx + 1}</td>
      <td class="font-bold text-gray-800 text-xs">${sr.name}</td>
      <td class="excel-money">
        ৳ ${ordersVal.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}
        ${(()=>{ 
          const oc = parseFloat(sr.orders_oc||0); 
          if(oc===0) return ''; 
          const sign=oc>0?'+':'-'; 
          const color=oc>0?'#10b981':'#ef4444'; 
          return `<div style="font-size:10px;font-weight:700;color:${color};margin-top:1px;">(${sign}৳${Math.abs(oc).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})})</div>`; 
        })()}
      </td>
      <td class="excel-money">
        ${showValues ? `
          ৳ ${dispatchItemsVal.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}
          ${(()=>{ 
            const oc = parseFloat(sr.dispatch_items_oc||0); 
            if(oc===0) return ''; 
            const sign=oc>0?'+':'-'; 
            const color=oc>0?'#10b981':'#ef4444'; 
            return `<div style="font-size:10px;font-weight:700;color:${color};margin-top:1px;">(${sign}৳${Math.abs(oc).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})})</div>`; 
          })()}
        ` : '-'}
      </td>
      <td class="excel-money text-rose-600">${sch.status === 'returned' ? '৳ ' + returnItemsVal.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) : '-'}</td>
      <td class="excel-money text-amber-600">${showValues ? '৳ ' + damageVal.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) : '-'}</td>
      <td class="excel-money text-emerald-600 font-bold">${showValues ? '৳ ' + saleVal.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) : '-'}</td>
      <td class="text-center no-print">
        <button onclick="toggleProductRow(${schId}, ${sr.id})" class="text-xs text-gray-700 hover:text-blue-700 px-2 py-1 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded font-semibold transition">
          <i class="fa-solid fa-list mr-1 text-blue-600"></i> Products
        </button>
      </td>
    </tr>
    <tr id="exp-prod-${schId}-${sr.id}" class="hidden bg-white"><td colspan="8" class="p-0 border-b border-gray-300">
      <div id="prod-container-${schId}-${sr.id}" class="p-2"></div>
    </td></tr>`;
    
    // Store products for later rendering
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
    
    const sch = schedules.find(s => s.id == schId);
    const showValues = sch && (sch.status === 'dispatched' || sch.status === 'returned');
    
    if (products.length === 0) {
      container.innerHTML = '<div class="text-xs text-gray-400 p-2 italic">No products found.</div>';
      return;
    }
    
    let html = `<div class="excel-container shadow-none border border-slate-200"><table class="excel-table text-xs">
      <thead><tr>
        <th class="excel-row-num">#</th>
        <th>Product Name</th>
        <th class="text-center">Ordered Qty</th>
        <th class="text-center">Dispatched Qty</th>
        <th class="text-center">Returned Qty</th>
        <th class="text-right">Sale Value</th>
      </tr></thead><tbody>`;
      
    products.forEach((p, pIdx) => {
      const saleVal = parseFloat(p.sale_value || 0);
      html += `<tr>
        <td class="excel-row-num">${pIdx + 1}</td>
        <td class="font-bold text-gray-800">${p.name}</td>
        <td class="excel-qty">${p.ordered_qty}</td>
        <td class="excel-qty">${showValues ? p.dispatched_qty : '-'}</td>
        <td class="excel-qty text-rose-600">${sch.status === 'returned' ? p.returned_qty : '-'}</td>
        <td class="excel-money text-emerald-600 font-bold">
          ৳ ${saleVal.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}
          ${(()=>{ 
            const oc = parseFloat(p.order_oc||0); 
            if(oc===0) return ''; 
            const sign=oc>0?'+':'-'; 
            const color=oc>0?'#10b981':'#ef4444'; 
            return `<div style="font-size:10px;font-weight:700;color:${color};margin-top:1px;">(${sign}৳${Math.abs(oc).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})})</div>`; 
          })()}
        </td>
      </tr>`;
    });
    html += `</tbody></table></div>`;
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

async function deleteSchedule(id) {
  if(!confirm("Are you sure you want to permanently delete this dispatch schedule and all its related organized data? This action cannot be undone.")) return;
  const res = await fetch(`<?= url("manager/api/dispatch/delete/") ?>${id}`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' }
  });
  const data = await res.json();
  if (data.success) {
    loadSchedules();
  } else {
    alert("Error deleting: " + data.message);
  }
}

function openEditDsrModal(scheduleId, currentDsrId) {
  document.getElementById('edit-dsr-schedule-id').value = scheduleId;
  const select = document.getElementById('edit-dsr-select');
  select.innerHTML = '';
  
  allDsrs.forEach(dsr => {
    const opt = document.createElement('option');
    opt.value = dsr.id;
    opt.textContent = dsr.name;
    if (parseInt(dsr.id) === parseInt(currentDsrId)) {
      opt.selected = true;
    }
    select.appendChild(opt);
  });
  
  document.getElementById('edit-dsr-modal').classList.remove('hidden');
}

function closeEditDsrModal() {
  document.getElementById('edit-dsr-modal').classList.add('hidden');
}

async function saveDsrChange() {
  const scheduleId = document.getElementById('edit-dsr-schedule-id').value;
  const dsrId = document.getElementById('edit-dsr-select').value;
  
  if (!scheduleId || !dsrId) return;
  
  try {
    const res = await fetch('<?= url("manager/api/dispatch/update-dsr") ?>', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ schedule_id: scheduleId, dsr_id: dsrId })
    });
    
    const result = await res.json();
    if (result.success) {
      closeEditDsrModal();
      loadSchedules();
    } else {
      alert(result.message || 'Failed to update DSR');
    }
  } catch (err) {
    console.error(err);
    alert('An error occurred while updating DSR');
  }
}

function openEditDeliveryDateModal(scheduleId, currentDate) {
  document.getElementById('edit-delivery-date-schedule-id').value = scheduleId;
  document.getElementById('edit-delivery-date-input').value = currentDate;
  document.getElementById('edit-delivery-date-modal').classList.remove('hidden');
}

function closeEditDeliveryDateModal() {
  document.getElementById('edit-delivery-date-modal').classList.add('hidden');
}

async function saveDeliveryDateChange() {
  const scheduleId = document.getElementById('edit-delivery-date-schedule-id').value;
  const deliveryDate = document.getElementById('edit-delivery-date-input').value;
  
  if (!scheduleId || !deliveryDate) {
    alert('Please select a valid date.');
    return;
  }
  
  try {
    const res = await fetch('<?= url("manager/api/dispatch/update-delivery-date") ?>', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ schedule_id: scheduleId, delivery_date: deliveryDate })
    });
    
    const result = await res.json();
    if (result.success) {
      closeEditDeliveryDateModal();
      loadSchedules();
    } else {
      alert(result.message || 'Failed to update delivery date');
    }
  } catch (err) {
    console.error(err);
    alert('An error occurred while updating delivery date');
  }
}

// ============================================================================
// EXPORT & PRINT HELPERS
// ============================================================================
function exportDispatchCSV() {
  if (schedules.length === 0) {
    alert("No data available to export.");
    return;
  }

  let csvContent = "data:text/csv;charset=utf-8,";
  csvContent += "Index,Order Date,Delivery Date,DSR Name,Order Value,Dispatch Value,Return Value,Damage Value,Sale Value,Status\n";

  schedules.forEach((sch, idx) => {
    const row = [
      idx + 1,
      `"${sch.dispatch_date}"`,
      `"${sch.delivery_date || sch.dispatch_date}"`,
      `"${sch.dsr_name.replace(/"/g, '""')}"`,
      parseFloat(sch.total_order_value || 0).toFixed(2),
      parseFloat(sch.total_dispatch_value || 0).toFixed(2),
      parseFloat(sch.total_return_value || 0).toFixed(2),
      parseFloat(sch.total_damage_value || 0).toFixed(2),
      parseFloat(sch.total_sale_value || 0).toFixed(2),
      `"${sch.status}"`
    ];
    csvContent += row.join(",") + "\n";
  });

  const encodedUri = encodeURI(csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", `Dispatch_Management_Sheet_${new Date().toISOString().slice(0,10)}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

function printDispatchSheet() {
  window.print();
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
    tbody.innerHTML = '<tr><td colspan="6" class="text-center p-5 text-gray-400">No products found for these orders.</td></tr>';
  } else {
    products.forEach((p, pIdx) => {
      const img = p.image ? `<img src="<?= BASE_URL ?>/${p.image}" class="w-8 h-8 rounded object-cover border border-gray-200">` : `<div class="w-8 h-8 rounded bg-gray-100 flex items-center justify-center"><i class="fa-solid fa-box text-gray-300"></i></div>`;
      
      const ppb = Math.max(1, parseInt(p.pieces_per_box) || 1);
      const boxTypeStr = (p.box_type || '').toString().trim().toLowerCase();
      const pcsKeywords = ['pcs', 'pc', 'piece', 'pieces', 'পিস', 'পিছ'];
      const isPcs = pcsKeywords.includes(boxTypeStr) || (ppb <= 1);
      const boxLabel = p.box_type && p.box_type.trim() ? p.box_type.trim() : 'Box';

      const origPcs = parseInt(p.total_ordered_qty) || 0;
      const origBoxes = Math.floor(origPcs / ppb);
      const origRemPcs = origPcs % ppb;

      const origStr = isPcs 
        ? `${origPcs} pcs` 
        : `${origBoxes} ${boxLabel} | ${origRemPcs} pcs`;

      // Pre-fill editable inputs with existing dispatch quantity if extra/diff exists
      const extraPcs = (parseInt(p.extra_boxes || 0) * ppb) + parseInt(p.extra_pieces || 0);
      const initDispatchPcs = Math.max(0, origPcs + extraPcs);
      const initDispatchBoxes = Math.floor(initDispatchPcs / ppb);
      const initDispatchRemPcs = initDispatchPcs % ppb;

      const subtitleText = isPcs ? `1 Pcs` : `${ppb} Pcs / ${boxLabel}`;

      const inputControlsHtml = isPcs ? `
        <div class="flex items-center gap-2">
          <div class="flex items-center bg-white border border-gray-300 rounded-lg overflow-hidden focus-within:border-amber-500 focus-within:ring-1 focus-within:ring-amber-500 shadow-sm">
            <input type="hidden" value="0" class="org-dispatch-box">
            <input type="number" min="0" value="${initDispatchPcs}" oninput="updateOrgDiff(this)" class="org-dispatch-pcs w-16 text-xs py-1 px-2 text-center outline-none border-0 font-bold text-gray-800" placeholder="0">
            <span class="bg-gray-100 text-gray-500 text-[11px] px-2 py-1 border-l border-gray-200 font-semibold">Pcs</span>
          </div>
        </div>
      ` : `
        <div class="flex items-center gap-2">
          <div class="flex items-center bg-white border border-gray-300 rounded-lg overflow-hidden focus-within:border-amber-500 focus-within:ring-1 focus-within:ring-amber-500 shadow-sm">
            <input type="number" min="0" value="${initDispatchBoxes}" oninput="updateOrgDiff(this)" class="org-dispatch-box w-14 text-xs py-1 px-2 text-center outline-none border-0 font-bold text-gray-800" placeholder="0">
            <span class="bg-gray-100 text-gray-500 text-[11px] px-2 py-1 border-l border-r border-gray-200 font-semibold">${boxLabel}</span>
            <input type="number" min="0" value="${initDispatchRemPcs}" oninput="updateOrgDiff(this)" class="org-dispatch-pcs w-14 text-xs py-1 px-2 text-center outline-none border-0 font-semibold text-gray-800" placeholder="0">
            <span class="bg-gray-100 text-gray-500 text-[11px] px-2 py-1 border-l border-gray-200 font-semibold">Pcs</span>
          </div>
        </div>
      `;

      tbody.innerHTML += `
        <tr class="hover:bg-blue-50/50" data-pid="${p.product_id}" data-orig-pcs="${origPcs}" data-ppb="${ppb}" data-ispcs="${isPcs}" data-boxlabel="${boxLabel}">
          <td class="excel-row-num">${pIdx + 1}</td>
          <td class="p-3">
            <div class="flex items-center gap-3">
              ${img}
              <div>
                <div class="font-bold text-gray-800 text-xs">${p.name}</div>
                <div class="text-[11px] text-gray-400 font-medium">${subtitleText}</div>
              </div>
            </div>
          </td>
          <td class="p-3 whitespace-nowrap">
            <span class="bg-slate-100 text-gray-700 px-2 py-0.5 rounded text-xs font-bold border border-slate-200">${origStr}</span>
            <div class="text-[11px] text-gray-400 mt-0.5">Total: ${origPcs} pcs</div>
          </td>
          <td class="p-3">
            ${inputControlsHtml}
          </td>
          <td class="p-3 text-center whitespace-nowrap">
            <div class="org-diff-badge flex items-center justify-center"></div>
          </td>
          <td class="p-3 text-center">
            <input type="checkbox" class="org-check w-5 h-5 text-amber-500 rounded border-gray-300 focus:ring-amber-500 cursor-pointer">
          </td>
        </tr>
      `;
    });

    // Trigger difference updates for initial state
    document.querySelectorAll('#organize-tbody tr').forEach(tr => {
      const input = tr.querySelector('.org-dispatch-pcs') || tr.querySelector('.org-dispatch-box');
      if (input) updateOrgDiff(input);
    });
  }
  
  document.getElementById('organize-modal').classList.remove('hidden');
}

function updateOrgDiff(elem) {
  const tr = elem.closest('tr');
  const origPcs = parseInt(tr.getAttribute('data-orig-pcs')) || 0;
  const ppb = parseInt(tr.getAttribute('data-ppb')) || 1;
  const isPcs = tr.getAttribute('data-ispcs') === 'true';
  const boxLabel = tr.getAttribute('data-boxlabel') || 'Box';
  
  const boxVal = parseInt(tr.querySelector('.org-dispatch-box')?.value || 0);
  const pcsVal = parseInt(tr.querySelector('.org-dispatch-pcs')?.value || 0);
  
  const newTotalPcs = (boxVal * ppb) + pcsVal;
  const diffPcs = newTotalPcs - origPcs;
  const badgeContainer = tr.querySelector('.org-diff-badge');
  
  if (diffPcs === 0) {
    badgeContainer.innerHTML = '<span class="text-xs text-gray-500 font-bold px-2 py-0.5 rounded bg-gray-100 border border-gray-200">ঠিক আছে</span>';
    return;
  }
  
  const absDiff = Math.abs(diffPcs);
  let textStr = '';
  if (isPcs) {
    textStr = `${absDiff} pcs`;
  } else {
    const diffBox = Math.floor(absDiff / ppb);
    const diffRemPcs = absDiff % ppb;
    
    let parts = [];
    if (diffBox > 0) parts.push(`${diffBox} ${boxLabel}`);
    if (diffRemPcs > 0 || parts.length === 0) parts.push(`${diffRemPcs} pcs`);
    textStr = parts.join(' ');
  }
  
  if (diffPcs > 0) {
    badgeContainer.innerHTML = `<span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-full text-xs font-semibold border border-emerald-200"><i class="fa-solid fa-arrow-up text-[10px]"></i> +${textStr} বেশি</span>`;
  } else {
    badgeContainer.innerHTML = `<span class="inline-flex items-center gap-1 bg-rose-50 text-rose-700 px-2.5 py-1 rounded-full text-xs font-semibold border border-rose-200"><i class="fa-solid fa-arrow-down text-[10px]"></i> -${textStr} কম</span>`;
  }
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
    const origPcs = parseInt(tr.getAttribute('data-orig-pcs')) || 0;
    const ppb = parseInt(tr.getAttribute('data-ppb')) || 1;
    
    const boxVal = parseInt(tr.querySelector('.org-dispatch-box').value) || 0;
    const pcsVal = parseInt(tr.querySelector('.org-dispatch-pcs').value) || 0;
    
    const newTotalPcs = (boxVal * ppb) + pcsVal;
    const diffPcs = newTotalPcs - origPcs;
    
    if (diffPcs !== 0) {
      let diffBoxes = Math.trunc(diffPcs / ppb);
      let diffRemPcs = diffPcs - (diffBoxes * ppb);
      extras.push({ product_id: pid, boxes: diffBoxes, pcs: diffRemPcs });
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
      <div class="absolute left-0 top-1/2 transform -translate-y-1/2 -translate-x-1/2 w-4 h-4 rounded-full bg-gray-300 border-4 border-white dot" id="dsr-dot-${dsr.id}"></div>
      
      <div class="flex items-center gap-3 pointer-events-none w-1/2">
        <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold min-w-[40px]">${dsr.name.charAt(0)}</div>
        <div class="truncate">
          <div class="font-bold text-gray-800 truncate" title="${dsr.name}">${dsr.name}</div>
          <div class="text-xs text-gray-500">DSR</div>
        </div>
      </div>
      <div class="ml-auto pointer-events-auto flex flex-col items-end gap-1">
         <div class="flex items-center gap-1">
             <label class="text-[10px] text-gray-500 uppercase tracking-wider font-bold">Delivery:</label>
             <input type="date" id="dsr-date-${dsr.id}" class="form-input text-xs px-1 py-0.5 border-gray-300 rounded shadow-sm w-[110px]" value="${document.getElementById('wire-date').value}">
         </div>
         <span id="dsr-count-${dsr.id}" class="text-xs font-bold text-gray-400 bg-gray-100 px-2 py-1 rounded-lg">0 SRs</span>
      </div>
    `;
    div.onclick = (e) => { 
        if(e.target.tagName !== 'INPUT') handleDsrClick(dsr.id); 
    };
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
    connections[activeSrId] = id;
    document.getElementById(`sr-card-${activeSrId}`).classList.remove('active');
    activeSrId = null;
    updateVisuals();
  } else {
    document.querySelectorAll('#dsr-list .connector-card').forEach(el => el.classList.remove('active'));
    document.getElementById(`dsr-card-${id}`).classList.add('active');
    
    Object.keys(connections).forEach(sId => {
      if (connections[sId] === id) {
        const card = document.getElementById(`sr-card-${sId}`);
        card.onclick = () => {
          delete connections[sId];
          document.getElementById(`dsr-card-${id}`).classList.remove('active');
          
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
      
      const rectContainer = document.getElementById('wire-container').getBoundingClientRect();
      const rectS = sDot.getBoundingClientRect();
      const rectD = dDot.getBoundingClientRect();
      
      const startX = rectS.left - rectContainer.left + 8;
      const startY = rectS.top - rectContainer.top + 8;
      const endX = rectD.left - rectContainer.left + 8;
      const endY = rectD.top - rectContainer.top + 8;
      
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

window.addEventListener('resize', () => {
  if (!document.getElementById('wire-modal').classList.contains('hidden')) {
    updateVisuals();
  }
});
document.getElementById('sr-list').addEventListener('scroll', updateVisuals);
document.getElementById('dsr-list').addEventListener('scroll', updateVisuals);

async function saveWireAssignments() {
  const date = document.getElementById('wire-date').value;
  
  const assignments = {};
  Object.keys(connections).forEach(sId => {
    const dId = connections[sId];
    if (!assignments[dId]) {
      const dDate = document.getElementById(`dsr-date-${dId}`).value || date;
      assignments[dId] = { sr_ids: [], delivery_date: dDate };
    }
    assignments[dId].sr_ids.push(sId);
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

window.openReturnModal = async function(scheduleId, dsrId, date) {
  try {
    document.getElementById('return-schedule-id').value = scheduleId;
    const content = document.getElementById('return-modal-content');
    content.innerHTML = '<div class="text-center py-6 text-gray-500"><i class="fa-solid fa-spinner fa-spin text-2xl mb-2"></i><br>Loading van stock...</div>';
    document.getElementById('return-modal').classList.remove('hidden');

    const res = await fetch(`<?= url("manager/api/dispatch/van-stock/") ?>${dsrId}?date=${date}`);
    const data = await res.json();
    if(data.success) {
      let html = `<p class="mb-4 text-xs text-gray-600">The following quantities are currently in the van stock for this DSR on ${date}. Confirm to process as returns.</p>`;
      if(data.stock.length === 0) {
         html += `<div class="bg-amber-50 text-amber-800 p-3 rounded-xl text-xs font-semibold border border-amber-200"><i class="fa-solid fa-info-circle mr-1"></i> No van stock found to return.</div>`;
      } else {
         html += `<div class="excel-container shadow-sm"><table class="excel-table">
            <thead>
              <tr>
                <th class="excel-row-num">#</th>
                <th>Product</th>
                <th class="text-right w-28">Return Qty</th>
              </tr>
            </thead>
            <tbody>`;
         data.stock.forEach((item, itemIdx) => {
           html += `<tr>
             <td class="excel-row-num">${itemIdx + 1}</td>
             <td class="font-bold text-gray-800 text-xs">${item.product_name}</td>
             <td class="text-right">
               <input type="number" min="0" max="${item.qty}" class="return-qty-input w-full text-right border border-gray-300 rounded text-xs py-1 px-2 font-bold text-gray-800 outline-none focus:border-blue-500" data-pid="${item.product_id}" value="${item.qty}">
             </td>
           </tr>`;
         });
         html += `</tbody></table></div>`;
      }
      content.innerHTML = html;
    } else {
      content.innerHTML = `<div class="text-rose-500 p-4 text-xs font-bold">${data.message || 'Error loading stock'}</div>`;
    }
  } catch(e) {
    document.getElementById('return-modal-content').innerHTML = '<div class="text-rose-500 p-4 text-xs font-bold">Network error loading van stock.</div>';
  }
};

window.closeReturnModal = function() {
  document.getElementById('return-modal').classList.add('hidden');
};

window.submitReturn = async function() {
  const scheduleId = document.getElementById('return-schedule-id').value;
  const inputs = document.querySelectorAll('.return-qty-input');
  let products = [];
  inputs.forEach(inp => {
    let q = parseInt(inp.value) || 0;
    if(q > 0) {
      products.push({ id: inp.dataset.pid, qty: q });
    }
  });

  try {
    const res = await fetch(`<?= url("manager/api/dispatch/return-save/") ?>${scheduleId}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ products })
    });
    const data = await res.json();
    if (data.success) {
      window.closeReturnModal();
      if(typeof loadSchedules === 'function') loadSchedules();
    } else {
      alert(data.message || 'Error processing returns');
    }
  } catch(e) {
    alert('Network error processing returns');
  }
};
</script>
