<?php $pageTitle = 'Settlements'; ?>

<style>
/* Modern Excel Table Grid Overrides for Settlements */
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

.status-badge-settlement { 
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
.status-pending { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
.status-approved { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
.status-rejected { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

@media print {
  body * { visibility: hidden; }
  #viewExcelContainer, #viewExcelContainer * { visibility: visible; }
  #viewExcelContainer { position: absolute; left: 0; top: 0; width: 100%; }
  .excel-ribbon-actions, .no-print { display: none !important; }
}
</style>

<div class="page-header flex justify-between items-center mb-6">
  <div>
    <h1 class="page-title text-2xl font-bold text-gray-800">DSR Settlements</h1>
    <div class="breadcrumb text-sm text-gray-500">Manager &rsaquo; Settlements</div>
  </div>
</div>

<?php 
  $totalCount = count($items ?? []);
  $totalShouldPay = 0;
  $totalCountedCash = 0;
  $totalDifference = 0;
  $pendingCount = 0;

  if (!empty($items)) {
    foreach ($items as $item) {
      $totalShouldPay += floatval($item['should_pay'] ?? 0);
      $totalCountedCash += floatval($item['counted_cash'] ?? 0);
      $totalDifference += floatval($item['difference'] ?? 0);
      if ($item['status'] === 'pending') {
        $pendingCount++;
      }
    }
  }
?>

<!-- ============================================================================ -->
<!-- MODERN EXCEL SPREADSHEET SETTLEMENTS VIEW CONTAINER                         -->
<!-- ============================================================================ -->
<div id="viewExcelContainer" class="space-y-4">
  <div class="excel-container">
    
    <!-- Excel Ribbon Toolbar -->
    <div class="excel-ribbon">
      <div class="flex items-center gap-3">
        <div class="excel-ribbon-badge">
          <i class="fa-solid fa-file-excel text-blue-200 text-lg"></i>
          <span>DSR Settlements Spreadsheet</span>
        </div>
        <span class="text-xs text-blue-100 hidden sm:inline-block">• Live Cash Collection & Settlement Data Grid</span>
      </div>

      <div class="flex items-center gap-2 excel-ribbon-actions">
        <button onclick="exportSettlementsCSV()" class="excel-action-btn">
          <i class="fa-solid fa-file-csv"></i> Export CSV / Excel
        </button>
        <button onclick="printSettlementsSheet()" class="excel-action-btn excel-action-btn-secondary">
          <i class="fa-solid fa-print"></i> Print Sheet
        </button>
      </div>
    </div>

    <!-- Excel Formula & Summary Bar -->
    <div class="excel-formula-bar">
      <span class="fx-symbol">fx</span>
      <div class="excel-pill">
        <i class="fa-solid fa-calculator text-blue-600"></i>
        <span>SETTLEMENTS: <strong class="text-blue-700 font-mono"><?= $totalCount ?></strong></span>
      </div>
      <div class="excel-pill">
        <i class="fa-solid fa-bangladeshi-taka-sign text-blue-600"></i>
        <span>SHOULD PAY: <strong class="text-blue-700 font-mono">৳<?= number_format($totalShouldPay, 2) ?></strong></span>
      </div>
      <div class="excel-pill">
        <i class="fa-solid fa-money-bill-wave text-emerald-600"></i>
        <span>COUNTED CASH: <strong class="text-emerald-700 font-mono">৳<?= number_format($totalCountedCash, 2) ?></strong></span>
      </div>
      <div class="excel-pill">
        <i class="fa-solid fa-scale-unbalanced text-purple-600"></i>
        <span>NET DIFFERENCE: <strong class="<?= $totalDifference < 0 ? 'text-rose-700' : ($totalDifference > 0 ? 'text-emerald-700' : 'text-blue-700') ?> font-mono"><?= $totalDifference > 0 ? '+' : '' ?>৳<?= number_format($totalDifference, 2) ?></strong></span>
      </div>
      <div class="excel-pill">
        <i class="fa-solid fa-clock-rotate-left text-amber-600"></i>
        <span>PENDING: <strong class="text-amber-700 font-mono"><?= $pendingCount ?></strong></span>
      </div>
    </div>

    <!-- Excel Grid Table -->
    <div class="overflow-x-auto max-h-[680px]">
      <table class="excel-table" id="settlementsExcelTable">
        <thead>
          <tr>
            <th class="excel-row-num">#</th>
            <th>Date</th>
            <th>DSR Name</th>
            <th class="text-right">Total Dispatched</th>
            <th class="text-right">Total Returned</th>
            <th class="text-right">Damage</th>
            <th class="text-right">Expense</th>
            <th class="text-right">Delivery O/C</th>
            <th class="text-right">Should Pay</th>
            <th class="text-right">Counted Cash</th>
            <th class="text-right">Difference</th>
            <th class="text-center">Status</th>
            <th class="text-center no-print">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($items)): ?>
          <tr>
            <td colspan="13" class="p-12 text-center text-gray-400 bg-white font-medium">
              <i class="fa-solid fa-folder-open text-4xl text-gray-300 mb-3 block"></i>
              No DSR settlements submitted yet.
            </td>
          </tr>
          <?php else: ?>
          <?php foreach ($items as $idx => $s): 
              $isPending = $s['status'] === 'pending';
              $isApproved = $s['status'] === 'approved';
              $isRejected = $s['status'] === 'rejected';
              
              $breakdown = json_decode($s['cash_breakdown'], true) ?? [];
              $dsrNote = $breakdown['note'] ?? '';
              $denominations = [1000, 500, 200, 100, 50, 20, 10, 5, 2, 1];

              $dmg = $isPending ? (floatval($s['live_damage']) > 0 ? $s['live_damage'] : $s['total_damage']) : $s['total_damage'];
              $exp = $isPending ? (floatval($s['live_expense']) > 0 ? $s['live_expense'] : $s['total_expense']) : $s['total_expense'];
              $oc  = $isPending ? (floatval($s['live_delivery_oc']) != 0 ? $s['live_delivery_oc'] : $s['delivery_oc']) : $s['delivery_oc'];
              
              $sp = $isPending ? ($s['total_dispatched'] - $s['total_returned'] - $dmg - $exp + $oc) : $s['should_pay'];
              $diff = $isPending ? ($s['counted_cash'] - $sp) : floatval($s['difference']);
          ?>
          <tr class="hover:bg-blue-50/50 transition-colors group" id="settlement-card-<?= $s['id'] ?>">
            <td class="excel-row-num"><?= $idx + 1 ?></td>
            <td class="whitespace-nowrap font-bold text-gray-800 text-xs">
              <?= date('d M, Y', strtotime($s['date'])) ?>
            </td>
            <td>
              <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs border border-blue-200"><?= h(substr($s['dsr_name'], 0, 1)) ?></div>
                <span class="font-bold text-gray-800 text-xs"><?= h($s['dsr_name']) ?></span>
              </div>
            </td>
            <td class="excel-money">৳ <?= number_format($s['total_dispatched'], 2) ?></td>
            <td class="excel-money text-rose-600">- ৳ <?= number_format($s['total_returned'], 2) ?></td>
            <td class="excel-money text-amber-600">৳ <?= number_format($dmg, 2) ?></td>
            <td class="excel-money text-orange-600">৳ <?= number_format($exp, 2) ?></td>
            <td class="excel-money <?= $oc >= 0 ? 'text-emerald-600' : 'text-rose-600' ?>">
              <?= $oc >= 0 ? '+' : '' ?>৳ <?= number_format($oc, 2) ?>
            </td>
            <td class="excel-money text-gray-900 font-bold" id="should-pay-<?= $s['id'] ?>">৳ <?= number_format($sp, 2) ?></td>
            <td class="excel-money text-blue-700 font-bold" id="counted-cash-<?= $s['id'] ?>">৳ <?= number_format($s['counted_cash'], 2) ?></td>
            <td class="excel-money <?= $diff < 0 ? 'text-rose-600' : ($diff > 0 ? 'text-emerald-600' : 'text-blue-600') ?>" id="diff-<?= $s['id'] ?>">
              <?= $diff > 0 ? '+' : '' ?>৳ <?= number_format($diff, 2) ?>
            </td>
            <td class="text-center">
              <span class="status-badge-settlement status-<?= strtolower($s['status']) ?>"><?= strtoupper($s['status']) ?></span>
            </td>
            <td class="text-center no-print">
              <button type="button" onclick="toggleSettlementDetails(<?= $s['id'] ?>)" class="px-2.5 py-1 text-xs font-bold text-gray-700 hover:text-blue-700 bg-gray-100 hover:bg-blue-100 border border-gray-300 rounded transition flex items-center justify-center gap-1 mx-auto">
                <span>Details</span>
                <i class="fa-solid fa-chevron-down text-[10px] transform transition-transform" id="icon-set-<?= $s['id'] ?>"></i>
              </button>
            </td>
          </tr>

          <!-- Expanded Details Row -->
          <tr id="details-<?= $s['id'] ?>" class="hidden bg-slate-100/70">
            <td colspan="13" class="p-4 border-b border-gray-300">
              <div class="excel-container p-5 bg-white shadow-sm border border-slate-300">
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                  
                  <!-- Account Values Breakdown -->
                  <div class="space-y-4">
                    <h3 class="text-xs font-black text-gray-800 uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-gray-200">
                      <i class="fa-solid fa-calculator text-blue-600"></i> Account Values & Adjustments
                    </h3>
                    
                    <div class="flex justify-between items-center bg-gray-50 p-2.5 rounded-lg border border-gray-200">
                      <span class="text-xs font-bold text-gray-600">Total Dispatched</span>
                      <span class="text-xs font-bold text-gray-900 font-mono">৳ <span id="orig-disp-<?= $s['id'] ?>"><?= number_format($s['total_dispatched'], 2, '.', '') ?></span></span>
                    </div>
 
                    <div class="flex justify-between items-center bg-gray-50 p-2.5 rounded-lg border border-gray-200">
                      <span class="text-xs font-bold text-gray-600">Total Returned</span>
                      <span class="text-xs font-bold text-rose-600 font-mono">- ৳ <span id="orig-ret-<?= $s['id'] ?>"><?= number_format($s['total_returned'], 2, '.', '') ?></span></span>
                    </div>
 
                    <div class="flex justify-between items-center bg-amber-50/60 p-2.5 rounded-lg border border-amber-200">
                      <label class="text-xs font-bold text-amber-800">Total Damage</label>
                      <div class="relative w-36">
                        <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-amber-500 font-bold text-xs">৳</span>
                        <input type="number" step="0.01" id="inp-dmg-<?= $s['id'] ?>" value="<?= number_format($dmg, 2, '.', '') ?>" class="w-full bg-white border border-amber-300 rounded-md py-1 pl-6 pr-2 text-right font-mono font-bold text-xs text-amber-800 outline-none focus:ring-1 focus:ring-amber-500 <?= !$isPending ? 'opacity-70 cursor-not-allowed' : '' ?>" <?= !$isPending ? 'readonly' : '' ?> oninput="recalc(<?= $s['id'] ?>)">
                      </div>
                    </div>
 
                    <div class="flex justify-between items-center bg-orange-50/60 p-2.5 rounded-lg border border-orange-200">
                      <label class="text-xs font-bold text-orange-800">Total Expense</label>
                      <div class="relative w-36">
                        <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-orange-500 font-bold text-xs">৳</span>
                        <input type="number" step="0.01" id="inp-exp-<?= $s['id'] ?>" value="<?= number_format($exp, 2, '.', '') ?>" class="w-full bg-white border border-orange-300 rounded-md py-1 pl-6 pr-2 text-right font-mono font-bold text-xs text-orange-800 outline-none focus:ring-1 focus:ring-orange-500 <?= !$isPending ? 'opacity-70 cursor-not-allowed' : '' ?>" <?= !$isPending ? 'readonly' : '' ?> oninput="recalc(<?= $s['id'] ?>)">
                      </div>
                    </div>

                    <div class="flex justify-between items-center bg-blue-50/60 p-2.5 rounded-lg border border-blue-200">
                      <label class="text-xs font-bold text-blue-800">Delivery O/C (কমিশন/ওভার)</label>
                      <div class="relative w-36">
                        <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-blue-500 font-bold text-xs">৳</span>
                        <input type="number" step="0.01" id="inp-oc-<?= $s['id'] ?>" value="<?= number_format($oc, 2, '.', '') ?>" class="w-full bg-white border border-blue-300 rounded-md py-1 pl-6 pr-2 text-right font-mono font-bold text-xs text-blue-800 outline-none focus:ring-1 focus:ring-blue-500 <?= !$isPending ? 'opacity-70 cursor-not-allowed' : '' ?>" <?= !$isPending ? 'readonly' : '' ?> oninput="recalc(<?= $s['id'] ?>)">
                      </div>
                    </div>

                    <div class="pt-2">
                      <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">DSR Note</label>
                      <div class="p-2.5 bg-gray-50 text-gray-700 text-xs italic rounded-lg border border-gray-200 min-h-[50px]">
                        <?= $dsrNote ? htmlspecialchars($dsrNote) : 'No note provided by DSR.' ?>
                      </div>
                    </div>
                  </div>

                  <!-- Cash Denomination Matrix -->
                  <div class="space-y-4">
                    <h3 class="text-xs font-black text-gray-800 uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-gray-200">
                      <i class="fa-solid fa-money-bill-wave text-emerald-600"></i> Cash Denomination Matrix
                    </h3>
                    
                    <div class="grid grid-cols-2 gap-2">
                      <?php foreach($denominations as $d): $qty = $breakdown[$d] ?? ''; ?>
                      <div class="flex items-center gap-2 bg-emerald-50/40 p-1.5 rounded-lg border border-emerald-100">
                        <div class="w-12 text-center font-mono font-bold text-emerald-800 text-xs">৳<?= $d ?></div>
                        <div class="text-gray-300 text-xs">×</div>
                        <input type="number" data-val="<?= $d ?>" value="<?= $qty ?>" min="0" class="flex-1 w-full bg-white border border-emerald-300 rounded py-1 px-2 text-center font-mono font-bold text-xs text-gray-800 outline-none focus:ring-1 focus:ring-emerald-500 denom-<?= $s['id'] ?> <?= !$isPending ? 'opacity-70 cursor-not-allowed' : '' ?>" <?= !$isPending ? 'readonly' : '' ?> oninput="recalc(<?= $s['id'] ?>)">
                      </div>
                      <?php endforeach; ?>
                    </div>

                    <div>
                      <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Manager Note (Optional)</label>
                      <textarea id="mgr-note-<?= $s['id'] ?>" class="w-full p-2 bg-white border border-gray-300 rounded-lg text-xs outline-none focus:ring-1 focus:ring-blue-500 <?= !$isPending ? 'opacity-70 cursor-not-allowed' : '' ?>" <?= !$isPending ? 'readonly' : '' ?> rows="2" placeholder="Add a note..."><?= htmlspecialchars($s['manager_notes'] ?? '') ?></textarea>
                    </div>
                  </div>

                </div>

                <!-- Action Bar -->
                <?php if ($isPending): ?>
                <div class="flex gap-4 pt-5 mt-5 border-t border-gray-200">
                  <button type="button" class="flex-1 py-2.5 bg-emerald-600 text-white font-bold text-sm rounded-lg hover:bg-emerald-700 active:scale-[0.99] transition shadow" onclick="updateSettlement(<?= $s['id'] ?>, 'approved')">
                    <i class="fa-solid fa-check-double mr-1.5"></i> Approve Settlement
                  </button>
                  <button type="button" class="px-6 py-2.5 bg-rose-100 text-rose-700 font-bold text-sm rounded-lg hover:bg-rose-200 active:scale-[0.99] transition border border-rose-200" onclick="updateSettlement(<?= $s['id'] ?>, 'rejected')">
                    <i class="fa-solid fa-xmark mr-1"></i> Reject
                  </button>
                </div>
                <?php endif; ?>

              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<script>
function toggleSettlementDetails(id) {
  const row = document.getElementById(`details-${id}`);
  const icon = document.getElementById(`icon-set-${id}`);
  if (row.classList.contains('hidden')) {
    row.classList.remove('hidden');
    if (icon) icon.classList.add('rotate-180');
  } else {
    row.classList.add('hidden');
    if (icon) icon.classList.remove('rotate-180');
  }
}

function recalc(id) {
    const disp = parseFloat(document.getElementById(`orig-disp-${id}`).innerText) || 0;
    const ret = parseFloat(document.getElementById(`orig-ret-${id}`).innerText) || 0;
    const dmg = parseFloat(document.getElementById(`inp-dmg-${id}`).value) || 0;
    const exp = parseFloat(document.getElementById(`inp-exp-${id}`).value) || 0;
    const oc = parseFloat(document.getElementById(`inp-oc-${id}`).value) || 0;
    
    const shouldPay = disp - ret - dmg - exp + oc;
    
    let countedCash = 0;
    document.querySelectorAll(`.denom-${id}`).forEach(inp => {
        const val = parseFloat(inp.getAttribute('data-val'));
        const qty = parseInt(inp.value) || 0;
        countedCash += (val * qty);
    });

    const diff = countedCash - shouldPay;

    // Update displays
    document.getElementById(`should-pay-${id}`).innerText = '৳ ' + shouldPay.toFixed(2);
    document.getElementById(`counted-cash-${id}`).innerText = '৳ ' + countedCash.toFixed(2);
    
    const diffEl = document.getElementById(`diff-${id}`);
    diffEl.innerText = (diff > 0 ? '+' : '') + '৳ ' + diff.toFixed(2);
    
    diffEl.className = 'excel-money ' + 
        (diff < 0 ? 'text-rose-600' : (diff > 0 ? 'text-emerald-600' : 'text-blue-600'));
}

async function updateSettlement(id, status) {
    if (!confirm(`Are you sure you want to mark this settlement as ${status.toUpperCase()}?`)) return;

    const dmg = parseFloat(document.getElementById(`inp-dmg-${id}`).value) || 0;
    const exp = parseFloat(document.getElementById(`inp-exp-${id}`).value) || 0;
    const oc = parseFloat(document.getElementById(`inp-oc-${id}`).value) || 0;
    const mgrNote = document.getElementById(`mgr-note-${id}`).value;

    let cashBreakdown = {};
    let countedCash = 0;
    document.querySelectorAll(`.denom-${id}`).forEach(inp => {
        const val = parseFloat(inp.getAttribute('data-val'));
        const qty = parseInt(inp.value) || 0;
        if(qty > 0) {
            cashBreakdown[val] = qty;
            countedCash += (val * qty);
        }
    });

    const payload = {
        csrf_token: '<?= Helpers::csrfToken() ?>',
        status: status,
        total_damage: dmg,
        total_expense: exp,
        delivery_oc: oc,
        counted_cash: countedCash,
        cash_breakdown: JSON.stringify(cashBreakdown),
        manager_notes: mgrNote
    };

    try {
        const card = document.getElementById(`settlement-card-${id}`);
        card.style.opacity = '0.5';
        card.style.pointerEvents = 'none';

        const res = await fetch(`<?= url('manager/api/settlements/update/') ?>${id}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        
        if (data.success) {
            card.style.opacity = '1';
            location.reload();
        } else {
            alert(data.message || 'Error updating settlement');
            card.style.opacity = '1';
            card.style.pointerEvents = 'auto';
        }
    } catch (e) {
        alert('Request failed');
        const card = document.getElementById(`settlement-card-${id}`);
        card.style.opacity = '1';
        card.style.pointerEvents = 'auto';
    }
}

function exportSettlementsCSV() {
  const table = document.getElementById("settlementsExcelTable");
  if (!table) return;

  let csv = [];
  const rows = table.querySelectorAll("tr");
  
  for (let i = 0; i < rows.length; i++) {
    // Exclude detail child rows
    if (rows[i].id && rows[i].id.startsWith("details-")) continue;
    
    let row = [], cols = rows[i].querySelectorAll("td, th");
    for (let j = 0; j < cols.length - 1; j++) { // Skip action col
      let text = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, " ").replace(/\s+/g, " ").trim();
      row.push('"' + text.replace(/"/g, '""') + '"');
    }
    csv.push(row.join(","));
  }

  const csvContent = "data:text/csv;charset=utf-8," + csv.join("\n");
  const encodedUri = encodeURI(csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", `DSR_Settlements_Sheet_${new Date().toISOString().slice(0,10)}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

function printSettlementsSheet() {
  window.print();
}
</script>
