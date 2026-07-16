<?php $pageTitle = 'Settlements'; ?>
<div class="page-header">
  <div>
    <h1 class="page-title text-3xl font-black">Settlements</h1>
    <div class="breadcrumb text-sm text-gray-500">Manager &rsaquo; Settlements</div>
  </div>
</div>

<div class="grid grid-cols-1 gap-6">
  <?php if (empty($items)): ?>
  <div class="bg-white rounded-3xl p-10 text-center shadow-xl border border-gray-100">
    <div class="w-20 h-20 bg-gray-50 text-gray-300 rounded-full flex items-center justify-center mx-auto mb-4 text-4xl">
      <i class="fa-solid fa-folder-open"></i>
    </div>
    <h3 class="text-xl font-bold text-gray-800">No Settlements</h3>
    <p class="text-gray-500 mt-2">DSRs have not submitted any settlements yet.</p>
  </div>
  <?php else: ?>
  <?php foreach ($items as $s): 
      $isPending = $s['status'] === 'pending';
      $isApproved = $s['status'] === 'approved';
      $isRejected = $s['status'] === 'rejected';
      
      $statusColor = $isPending ? 'bg-amber-500 shadow-amber-500/50' : ($isApproved ? 'bg-emerald-500 shadow-emerald-500/50' : 'bg-red-500 shadow-red-500/50');
      $borderGlow = $isPending ? 'focus-within:ring-amber-500/30' : ($isApproved ? 'focus-within:ring-emerald-500/30' : 'focus-within:ring-red-500/30');

      $breakdown = json_decode($s['cash_breakdown'], true) ?? [];
      $dsrNote = $breakdown['note'] ?? '';
      $denominations = [1000, 500, 200, 100, 50, 20, 10, 5, 2, 1];
  ?>
  <div class="bg-white rounded-3xl p-6 shadow-xl border border-gray-100 transition-all duration-300 <?= $borderGlow ?> hover:shadow-2xl relative overflow-hidden" id="settlement-card-<?= $s['id'] ?>">
    
    <!-- Status Indicator Top Line -->
    <div class="absolute top-0 left-0 w-full h-1.5 <?= $statusColor ?> opacity-80"></div>
    
    <div class="flex flex-col md:flex-row md:items-start justify-between gap-6 mb-6 relative">
      <!-- Header Info -->
      <div class="flex-1">
        <div class="flex items-center gap-3 mb-2">
          <span class="px-3 py-1 text-[10px] font-black uppercase tracking-wider text-white rounded-full shadow-lg <?= $statusColor ?>">
            <?= $s['status'] ?>
          </span>
          <span class="text-sm font-bold text-gray-400"><i class="fa-regular fa-calendar mr-1"></i> <?= date('d M, Y', strtotime($s['date'])) ?></span>
        </div>
        <h2 class="text-2xl font-black text-gray-800 flex items-center gap-2">
          <i class="fa-solid fa-user-circle text-brand text-3xl"></i>
          <?= h($s['dsr_name']) ?>
        </h2>
      </div>

      <!-- Quick Summary -->
      <div class="flex gap-4">
        <div class="bg-gray-50 rounded-2xl p-4 min-w-[120px] text-center border border-gray-100">
          <div class="text-xs font-bold text-gray-400 mb-1">Should Pay</div>
          <div class="text-xl font-black text-gray-800" id="should-pay-<?= $s['id'] ?>">৳<?= number_format($s['should_pay'], 2) ?></div>
        </div>
        <div class="bg-gray-50 rounded-2xl p-4 min-w-[120px] text-center border border-gray-100">
          <div class="text-xs font-bold text-gray-400 mb-1">Counted Cash</div>
          <div class="text-xl font-black text-brand" id="counted-cash-<?= $s['id'] ?>">৳<?= number_format($s['counted_cash'], 2) ?></div>
        </div>
        <div class="rounded-2xl p-4 min-w-[120px] text-center border <?= $s['difference'] < 0 ? 'bg-red-50 border-red-100' : ($s['difference'] > 0 ? 'bg-green-50 border-green-100' : 'bg-blue-50 border-blue-100') ?>" id="diff-container-<?= $s['id'] ?>">
          <div class="text-xs font-bold text-gray-500 mb-1">Difference</div>
          <div class="text-xl font-black <?= $s['difference'] < 0 ? 'text-red-600' : ($s['difference'] > 0 ? 'text-green-600' : 'text-blue-600') ?>" id="diff-<?= $s['id'] ?>">
            <?= $s['difference'] > 0 ? '+' : '' ?>৳<?= number_format($s['difference'], 2) ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Details Accordion Toggle -->
    <button type="button" class="w-full py-3 bg-gray-50 text-gray-600 font-bold rounded-2xl hover:bg-gray-100 transition flex items-center justify-center gap-2 mb-4" onclick="document.getElementById('details-<?= $s['id'] ?>').classList.toggle('hidden')">
      <span>View & Edit Details</span>
      <i class="fa-solid fa-chevron-down text-xs"></i>
    </button>

    <!-- Expandable Details -->
    <div id="details-<?= $s['id'] ?>" class="hidden">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-6 border-t border-gray-100 pt-6">
        
        <!-- Account Logic -->
        <div class="space-y-4">
          <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest mb-4"><i class="fa-solid fa-calculator text-brand mr-2"></i> Account Values</h3>
          
          <div class="flex justify-between items-center bg-gray-50 p-3 rounded-xl">
            <span class="text-sm font-bold text-gray-500">Total Dispatched</span>
            <span class="text-sm font-black text-gray-800">৳<span id="orig-disp-<?= $s['id'] ?>"><?= number_format($s['total_dispatched'], 2, '.', '') ?></span></span>
          </div>
          <div class="flex justify-between items-center bg-gray-50 p-3 rounded-xl">
            <span class="text-sm font-bold text-gray-500">Total Returned</span>
            <span class="text-sm font-black text-gray-800 text-red-500">- ৳<span id="orig-ret-<?= $s['id'] ?>"><?= number_format($s['total_returned'], 2, '.', '') ?></span></span>
          </div>
          
          <div class="flex justify-between items-center bg-red-50/50 p-3 rounded-xl border border-red-100">
            <label class="text-sm font-bold text-red-700">Total Damage</label>
            <div class="relative w-32">
              <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-red-400 font-bold">৳</span>
              <input type="number" id="inp-dmg-<?= $s['id'] ?>" value="<?= number_format($s['total_damage'], 2, '.', '') ?>" class="w-full bg-white border border-red-200 rounded-lg py-2 pl-7 pr-3 text-right font-black text-red-700 outline-none focus:ring-2 focus:ring-red-400 <?= !$isPending ? 'opacity-70 cursor-not-allowed' : '' ?>" <?= !$isPending ? 'readonly' : '' ?> oninput="recalc(<?= $s['id'] ?>)">
            </div>
          </div>
          
          <div class="flex justify-between items-center bg-orange-50/50 p-3 rounded-xl border border-orange-100">
            <label class="text-sm font-bold text-orange-700">Total Expense</label>
            <div class="relative w-32">
              <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-orange-400 font-bold">৳</span>
              <input type="number" id="inp-exp-<?= $s['id'] ?>" value="<?= number_format($s['total_expense'], 2, '.', '') ?>" class="w-full bg-white border border-orange-200 rounded-lg py-2 pl-7 pr-3 text-right font-black text-orange-700 outline-none focus:ring-2 focus:ring-orange-400 <?= !$isPending ? 'opacity-70 cursor-not-allowed' : '' ?>" <?= !$isPending ? 'readonly' : '' ?> oninput="recalc(<?= $s['id'] ?>)">
            </div>
          </div>
          
          <div class="mt-4 pt-4 border-t border-gray-100">
            <label class="block text-xs font-bold text-gray-500 mb-2">DSR Note</label>
            <div class="p-3 bg-gray-50 text-gray-700 text-sm italic rounded-xl border border-gray-100 min-h-[60px]">
              <?= $dsrNote ? htmlspecialchars($dsrNote) : 'No note provided.' ?>
            </div>
          </div>
        </div>

        <!-- Cash Count -->
        <div>
          <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest mb-4"><i class="fa-solid fa-money-bill-wave text-emerald-500 mr-2"></i> Cash Denominations</h3>
          <div class="grid grid-cols-2 gap-2">
            <?php foreach($denominations as $d): $qty = $breakdown[$d] ?? ''; ?>
            <div class="flex items-center gap-2 bg-emerald-50/30 p-2 rounded-xl border border-emerald-100/50">
              <div class="w-10 text-center font-black text-emerald-700 text-xs">৳<?= $d ?></div>
              <div class="text-gray-300 text-xs">×</div>
              <input type="number" data-val="<?= $d ?>" value="<?= $qty ?>" min="0" class="flex-1 w-full bg-white border border-emerald-200 rounded-lg py-1 px-2 text-center font-bold text-gray-800 outline-none focus:ring-2 focus:ring-emerald-400 denom-<?= $s['id'] ?> <?= !$isPending ? 'opacity-70 cursor-not-allowed' : '' ?>" <?= !$isPending ? 'readonly' : '' ?> oninput="recalc(<?= $s['id'] ?>)">
            </div>
            <?php endforeach; ?>
          </div>

          <div class="mt-4">
            <label class="block text-xs font-bold text-gray-500 mb-2">Manager Note (Optional)</label>
            <textarea id="mgr-note-<?= $s['id'] ?>" class="w-full p-3 bg-white border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-brand <?= !$isPending ? 'opacity-70 cursor-not-allowed' : '' ?>" <?= !$isPending ? 'readonly' : '' ?> rows="2" placeholder="Add a note..."><?= htmlspecialchars($s['manager_notes'] ?? '') ?></textarea>
          </div>
        </div>

      </div>

      <!-- Action Buttons -->
      <?php if ($isPending): ?>
      <div class="flex gap-4 pt-4 border-t border-gray-100">
        <button type="button" class="flex-1 py-4 bg-emerald-500 text-white font-black text-lg rounded-2xl hover:bg-emerald-600 active:scale-95 transition-all shadow-[0_10px_20px_rgba(16,185,129,0.3)]" onclick="updateSettlement(<?= $s['id'] ?>, 'approved')">
          <i class="fa-solid fa-check-double mr-2"></i> Approve Settlement
        </button>
        <button type="button" class="px-8 py-4 bg-red-100 text-red-600 font-bold text-lg rounded-2xl hover:bg-red-200 active:scale-95 transition-all" onclick="updateSettlement(<?= $s['id'] ?>, 'rejected')">
          <i class="fa-solid fa-xmark mr-1"></i> Reject
        </button>
      </div>
      <?php endif; ?>

    </div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>

<script>
function recalc(id) {
    const disp = parseFloat(document.getElementById(`orig-disp-${id}`).innerText) || 0;
    const ret = parseFloat(document.getElementById(`orig-ret-${id}`).innerText) || 0;
    const dmg = parseFloat(document.getElementById(`inp-dmg-${id}`).value) || 0;
    const exp = parseFloat(document.getElementById(`inp-exp-${id}`).value) || 0;
    
    const shouldPay = disp - ret - dmg - exp;
    
    let countedCash = 0;
    document.querySelectorAll(`.denom-${id}`).forEach(inp => {
        const val = parseFloat(inp.getAttribute('data-val'));
        const qty = parseInt(inp.value) || 0;
        countedCash += (val * qty);
    });

    const diff = countedCash - shouldPay;

    // Update displays
    document.getElementById(`should-pay-${id}`).innerText = '৳' + shouldPay.toFixed(2);
    document.getElementById(`counted-cash-${id}`).innerText = '৳' + countedCash.toFixed(2);
    
    const diffEl = document.getElementById(`diff-${id}`);
    const diffContainer = document.getElementById(`diff-container-${id}`);
    diffEl.innerText = (diff > 0 ? '+' : '') + '৳' + diff.toFixed(2);
    
    // Update colors based on difference
    diffContainer.className = 'rounded-2xl p-4 min-w-[120px] text-center border ' + 
        (diff < 0 ? 'bg-red-50 border-red-100' : (diff > 0 ? 'bg-green-50 border-green-100' : 'bg-blue-50 border-blue-100'));
    diffEl.className = 'text-xl font-black ' + 
        (diff < 0 ? 'text-red-600' : (diff > 0 ? 'text-green-600' : 'text-blue-600'));
}

async function updateSettlement(id, status) {
    if (!confirm(`Are you sure you want to mark this settlement as ${status.toUpperCase()}?`)) return;

    const dmg = parseFloat(document.getElementById(`inp-dmg-${id}`).value) || 0;
    const exp = parseFloat(document.getElementById(`inp-exp-${id}`).value) || 0;
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
            // Give a cool success effect and reload
            card.style.transform = 'scale(1.02)';
            card.style.boxShadow = '0 0 40px rgba(16,185,129,0.5)';
            setTimeout(() => location.reload(), 600);
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
</script>
