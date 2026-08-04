<?php 
$pageTitle = 'Daily Settlement'; 
$isSubmitted = !empty($existingSettlement);
$savedDamage = $isSubmitted ? $existingSettlement['total_damage'] : $totalDamage;
$savedExpense = $isSubmitted ? $existingSettlement['total_expense'] : $totalExpense;
$cashBreakdown = $isSubmitted && !empty($existingSettlement['cash_breakdown']) ? json_decode($existingSettlement['cash_breakdown'], true) : [];
$savedNote = $cashBreakdown['note'] ?? '';

if ($isSubmitted) {
    $dispatchedValue = $existingSettlement['total_dispatched'];
    $returnedValue = $existingSettlement['total_returned'];
}

$scheduleStatus = $scheduleStatus ?? 'pending';
$isReturned = ($scheduleStatus === 'returned');
$isNoDispatch = ($dispatchedValue <= 0);
$isLocked = $isSubmitted || !$isReturned || $isNoDispatch;
$readonlyAttr = $isLocked ? 'readonly' : '';
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap');
.font-siliguri {
  font-family: 'Hind Siliguri', 'Inter', sans-serif;
}

.excel-table input[type="number"]::-webkit-inner-spin-button,
.excel-table input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
.excel-table input[type="number"] {
  -moz-appearance: textfield;
}

.qty-btn {
  width: 28px;
  height: 28px;
  border-radius: 6px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 14px;
  user-select: none;
  touch-action: manipulation;
  transition: all 0.1s ease;
}
.qty-btn:active {
  transform: scale(0.9);
}

.denom-row-active {
  background-color: #f0fdf4 !important;
  border-left: 3px solid #10b981 !important;
}

.progress-bar-fill {
  transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>

<div class="p-3 sm:p-4 space-y-3 pb-24 max-w-4xl mx-auto font-siliguri text-slate-800 print:p-0 print:max-w-none">

  <!-- EXCEL RIBBON & FORMULA HEADER -->
  <div class="excel-container shadow-sm border border-slate-300 overflow-hidden">
    
    <!-- Excel Ribbon Header -->
    <div class="excel-ribbon py-2 px-3 justify-between">
      <div class="flex items-center gap-2">
        <a href="<?= url('dsr/dashboard') ?>" class="w-7 h-7 rounded bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition text-xs">
          <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div class="excel-ribbon-badge py-0.5 px-2.5 text-xs font-bold">
          <i class="fa-solid fa-file-excel text-blue-200"></i>
          <span>হিসাব মিলাও</span>
        </div>
      </div>

      <!-- Date Selector -->
      <form method="GET" action="<?= url('dsr/settlement') ?>" id="dateForm" class="flex items-center gap-2">
        <input type="date" id="dateInput" name="date" value="<?= h($selectedDate) ?>" onchange="document.getElementById('dateForm').submit()" class="bg-white text-slate-900 font-mono text-xs font-bold px-2 py-0.5 rounded border border-white/40 outline-none cursor-pointer">
      </form>
    </div>

    <!-- Excel Formula (fx) Bar -->
    <div class="excel-formula-bar py-1.5 px-3 gap-2 text-xs bg-slate-50 border-b border-slate-300">
      <span class="fx-symbol text-xs">fx</span>
      <div class="excel-pill">
        <span class="text-slate-500">লোড:</span>
        <strong class="text-slate-900 font-mono">৳<?= number_format($dispatchedValue) ?></strong>
      </div>
      <div class="excel-pill">
        <span class="text-slate-500">ফেরত:</span>
        <strong class="text-rose-600 font-mono">৳<?= number_format($returnedValue) ?></strong>
      </div>
      <div class="excel-pill bg-blue-50 border-blue-200">
        <span class="text-blue-700 font-bold">নিট জমা:</span>
        <strong id="fxShouldPay" class="text-blue-800 font-mono font-bold">৳0</strong>
      </div>
    </div>

    <!-- Cash Progress Bar -->
    <div class="bg-slate-100 px-3 py-1 border-t border-slate-200 flex items-center justify-between text-[11px]">
      <div class="flex items-center gap-2 flex-1 max-w-xs">
        <span class="font-bold text-slate-600 shrink-0">গণনা:</span>
        <div class="w-full bg-slate-200 h-1.5 rounded-full overflow-hidden">
          <div id="cashProgressBar" class="bg-emerald-500 h-full progress-bar-fill w-0"></div>
        </div>
      </div>
      <span id="cashProgressText" class="font-mono font-bold text-slate-700 ml-2">0%</span>
    </div>
  </div>

  <form action="<?= url('dsr/settlement/submit') ?>" method="POST" id="settlementForm" class="space-y-3">
    <?= Helpers::csrfField() ?>
    <input type="hidden" name="dispatched_value" value="<?= $dispatchedValue ?>">
    <input type="hidden" name="returned_value" value="<?= $returnedValue ?>">
    <input type="hidden" name="should_pay" id="formShouldPay" value="0">
    <input type="hidden" name="counted_cash" id="formCountedCash" value="0">
    <input type="hidden" name="difference" id="formDifference" value="0">
    <input type="hidden" name="cash_breakdown" id="formCashBreakdown" value="{}">
    <input type="hidden" name="settlement_date" value="<?= htmlspecialchars($selectedDate) ?>">

    <?php if ($isSubmitted): ?>
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-2.5 flex items-center gap-2 text-xs">
        <i class="fa-solid fa-lock text-blue-600"></i>
        <span class="font-bold text-blue-900">এই দিনের সেটেলমেন্ট লক করা হয়েছে।</span>
      </div>
    <?php elseif (!$isReturned): ?>
      <div class="bg-amber-50 border border-amber-200 rounded-lg p-2.5 flex items-center gap-2 text-xs">
        <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
        <span class="font-bold text-amber-900">ম্যানেজার কর্তৃক ডেলিভারি স্ট্যাটাস 'Returned' হওয়ার পর হিসাব মিলানো ও জমা দেওয়া যাবে।</span>
      </div>
    <?php endif; ?>

    <!-- 1. ACCOUNT BREAKDOWN TABLE -->
    <div class="excel-container shadow-sm border border-slate-300">
      <div class="bg-slate-100 px-3 py-1.5 border-b border-slate-300 text-xs font-bold text-slate-800">
        ১. হিসাব বিবরণী
      </div>

      <div class="overflow-x-auto">
        <table class="excel-table text-xs">
          <thead>
            <tr>
              <th class="excel-row-num">#</th>
              <th>বিবরণ</th>
              <th class="text-right">টাকা (৳)</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="excel-row-num">1</td>
              <td class="font-bold text-slate-800">মোট লোড করা মাল</td>
              <td class="excel-money text-slate-900">৳ <?= number_format($dispatchedValue) ?></td>
            </tr>
            <tr class="hover:bg-blue-50/50 cursor-pointer" onclick="openReturnModal()" title="বিস্তারিত দেখুন">
              <td class="excel-row-num">2</td>
              <td class="font-bold text-slate-800 flex items-center gap-1.5">
                <span>ফেরত মালের মূল্য (-)</span>
                <span class="text-[10px] text-blue-600 font-bold bg-blue-50 px-1 py-0.2 rounded border border-blue-200">দেখুন</span>
              </td>
              <td class="excel-money text-rose-600 font-bold">
                - ৳ <?= number_format($returnedValue) ?>
              </td>
            </tr>
            <tr>
              <td class="excel-row-num">3</td>
              <td class="font-bold text-slate-800">ড্যামেজ পণ্য (-)</td>
              <td class="excel-money text-amber-600 font-bold">৳ <?= number_format($savedDamage) ?></td>
            </tr>
            <tr>
              <td class="excel-row-num">4</td>
              <td class="font-bold text-slate-800">সারাদিনের খরচ (-)</td>
              <td class="excel-money text-purple-600 font-bold">৳ <?= number_format($savedExpense) ?></td>
            </tr>
            <tr class="bg-blue-50/70 border-t-2 border-slate-300">
              <td class="excel-row-num text-blue-700 font-black">∑</td>
              <td class="font-black text-slate-900 text-xs">ক্যাশারে জমা (নিট)</td>
              <td class="excel-money text-blue-700 text-sm font-black" id="displayShouldPay">৳ 0</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 2. CASH NOTE COUNTING TABLE -->
    <div class="excel-container shadow-sm border border-slate-300">
      <div class="bg-slate-100 px-3 py-1.5 border-b border-slate-300 flex justify-between items-center text-xs font-bold text-slate-800">
        <span>২. নোট গণনাকারক</span>
        
        <?php if (!$isLocked): ?>
        <div class="flex items-center gap-1">
          <button type="button" onclick="autoFillCash()" class="px-2 py-0.5 text-[11px] font-bold text-emerald-700 bg-emerald-100 hover:bg-emerald-200 border border-emerald-300 rounded transition active:scale-95">
            <i class="fa-solid fa-wand-magic-sparkles text-emerald-600"></i> অটো ফিল
          </button>
          <button type="button" onclick="clearCash()" class="px-1.5 py-0.5 text-[11px] font-semibold text-slate-600 bg-white hover:bg-slate-200 border border-slate-300 rounded transition active:scale-95">
            ক্লিয়ার
          </button>
        </div>
        <?php endif; ?>
      </div>

      <div class="overflow-x-auto">
        <table class="excel-table text-xs">
          <thead>
            <tr>
              <th class="excel-row-num">#</th>
              <th>নোট</th>
              <th class="text-center w-28 sm:w-36">সংখ্যা</th>
              <th class="text-right">মোট (৳)</th>
            </tr>
          </thead>
          <tbody>
            <?php 
              $denominations = [1000, 500, 200, 100, 50, 20, 10, 5, 2, 1];
              foreach ($denominations as $rowIdx => $d): 
                $qty = $cashBreakdown[$d] ?? '';
            ?>
            <tr class="hover:bg-blue-50/40 transition-colors" id="denom-row-<?= $d ?>">
              <td class="excel-row-num"><?= $rowIdx + 1 ?></td>
              <td class="font-bold text-slate-800">
                <span class="inline-block w-12 text-center font-mono font-black bg-emerald-50 text-emerald-700 py-0.5 px-1 rounded border border-emerald-200">৳ <?= $d ?></span>
              </td>
              <td class="p-1">
                <div class="flex items-center justify-center gap-1 bg-white border border-slate-300 rounded p-0.5">
                  <?php if (!$isLocked): ?>
                  <button type="button" onclick="stepDenom(<?= $d ?>, -1)" class="qty-btn bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-300">-</button>
                  <?php endif; ?>
                  
                  <input type="number" min="0" id="denom-input-<?= $d ?>" class="w-10 sm:w-14 bg-slate-50 border border-slate-200 rounded py-1 px-1 text-center font-mono font-bold text-slate-900 outline-none focus:bg-white focus:border-blue-500 text-xs denomination-input <?= $isLocked ? 'opacity-70' : '' ?>" data-val="<?= $d ?>" value="<?= $qty ?>" oninput="calculate()" <?= $readonlyAttr ?>>
                  
                  <?php if (!$isLocked): ?>
                  <button type="button" onclick="stepDenom(<?= $d ?>, 1)" class="qty-btn bg-blue-600 text-white hover:bg-blue-700 border border-blue-600">+</button>
                  <?php endif; ?>
                </div>
              </td>
              <td class="excel-money text-slate-900 font-bold" id="subtotal-<?= $d ?>">৳ 0</td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Aggregate Footer -->
      <div class="bg-slate-50 border-t-2 border-slate-300 p-2.5 space-y-1.5 text-xs">
        <div class="flex justify-between items-center">
          <span class="font-bold text-slate-700">গণনাকৃত ক্যাশ:</span>
          <span class="text-sm font-black text-slate-900 font-mono" id="displayCountedCash">৳ 0</span>
        </div>

        <div class="flex justify-between items-center pt-1.5 border-t border-slate-200">
          <div class="flex items-center gap-2">
            <span class="font-bold text-slate-700">পার্থক্য:</span>
            <span class="font-mono font-black text-sm" id="displayDifference">৳ 0</span>
          </div>
          <div id="statusBadge" class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-slate-200 text-slate-700 uppercase tracking-wider border border-slate-300">
            পেন্ডিং
          </div>
        </div>
      </div>

    </div>

    <!-- 3. Optional Note -->
    <div class="excel-container p-2.5 border border-slate-300 bg-white shadow-sm space-y-1 text-left">
      <label class="text-xs font-bold text-slate-700 block">মন্তব্য (নোট)</label>
      <textarea name="note" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2 text-xs text-slate-800 outline-none focus:border-blue-500 <?= $isLocked ? 'opacity-70' : '' ?>" rows="2" placeholder="কোনো মন্তব্য থাকলে লিখুন..." <?= $readonlyAttr ?>><?= htmlspecialchars($savedNote) ?></textarea>
    </div>

    <!-- Submit Button -->
    <?php if (!$isLocked): ?>
      <button type="submit" class="w-full py-3 font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg active:scale-[0.99] transition shadow text-sm font-siliguri flex items-center justify-center gap-2">
        <i class="fa-solid fa-check-circle"></i>
        <span>হিসাব জমা দিন</span>
      </button>
    <?php endif; ?>

  </form>

</div>

<!-- ===== Return Detail Modal ===== -->
<div id="returnModal" class="fixed inset-0 z-[100] hidden" aria-modal="true" role="dialog">
  <div class="absolute inset-0 bg-black/60 backdrop-blur-xs" onclick="closeReturnModal()"></div>

  <div class="absolute top-4 left-4 right-4 max-w-lg mx-auto bg-white rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[85vh] border border-slate-300" style="animation: slideDown .25s ease">
    
    <div class="flex items-center justify-between px-3 py-2 border-b border-slate-200 bg-slate-100">
      <div class="text-left">
        <h2 class="text-xs font-bold text-slate-900">ফেরত মালের বিবরণ</h2>
        <p class="text-[10px] text-slate-500 font-mono" id="returnModalDate"></p>
      </div>
      <button onclick="closeReturnModal()" class="w-6 h-6 rounded bg-white border border-slate-300 hover:bg-slate-200 flex items-center justify-center text-slate-600 transition">
        <i class="fa-solid fa-xmark text-xs"></i>
      </button>
    </div>

    <div class="overflow-y-auto flex-1 p-3 space-y-3" id="returnModalBody">
      <!-- filled by JS -->
    </div>

    <div class="px-3 py-2 border-t border-slate-200 flex justify-between items-center bg-rose-50/80">
      <span class="text-xs font-bold text-rose-800">মোট ফেরত মূল্য:</span>
      <span class="text-sm font-black text-rose-700 font-mono" id="returnModalTotal">৳ 0</span>
    </div>

  </div>
</div>

<script>
const dispatched = <?= (float)$dispatchedValue ?>;
const returned = <?= (float)$returnedValue ?>;
const damageVal = <?= (float)$savedDamage ?>;
const expenseVal = <?= (float)$savedExpense ?>;

function stepDenom(denom, step) {
  const input = document.getElementById(`denom-input-${denom}`);
  if (!input || input.readOnly) return;
  let current = parseInt(input.value) || 0;
  let nextVal = Math.max(0, current + step);
  input.value = nextVal > 0 ? nextVal : '';
  calculate();
}

function autoFillCash() {
  const shouldPay = Math.round(dispatched - returned - damageVal - expenseVal);
  if (shouldPay <= 0) return;
  
  document.querySelectorAll('.denomination-input').forEach(inp => inp.value = '');
  
  let remaining = shouldPay;
  const denoms = [1000, 500, 200, 100, 50, 20, 10, 5, 2, 1];
  
  denoms.forEach(d => {
    if (remaining >= d) {
      const count = Math.floor(remaining / d);
      remaining %= d;
      const inp = document.getElementById(`denom-input-${d}`);
      if (inp) inp.value = count;
    }
  });
  
  calculate();
}

function clearCash() {
  document.querySelectorAll('.denomination-input').forEach(inp => inp.value = '');
  calculate();
}

function calculate() {
    const shouldPay = dispatched - returned - damageVal - expenseVal;
    const roundedShouldPay = Math.round(shouldPay);
    
    document.getElementById('displayShouldPay').innerText = '৳ ' + roundedShouldPay.toLocaleString('en-US');
    document.getElementById('fxShouldPay').innerText = '৳ ' + roundedShouldPay.toLocaleString('en-US');
    document.getElementById('formShouldPay').value = shouldPay.toFixed(2);

    let countedCash = 0;
    const cashBreakdown = {};

    document.querySelectorAll('.denomination-input').forEach(input => {
        const val = parseFloat(input.getAttribute('data-val'));
        const qty = parseInt(input.value) || 0;
        const subtotal = val * qty;
        const row = document.getElementById(`denom-row-${val}`);
        
        const subCell = document.getElementById(`subtotal-${val}`);
        if (subCell) {
          subCell.innerText = '৳ ' + subtotal.toLocaleString('en-US');
        }

        if (qty > 0) {
            countedCash += subtotal;
            cashBreakdown[val] = qty;
            if (row) row.classList.add('denom-row-active');
        } else {
            if (row) row.classList.remove('denom-row-active');
        }
    });

    const roundedCounted = Math.round(countedCash);
    document.getElementById('displayCountedCash').innerText = '৳ ' + roundedCounted.toLocaleString('en-US');
    document.getElementById('formCountedCash').value = countedCash.toFixed(2);
    document.getElementById('formCashBreakdown').value = JSON.stringify(cashBreakdown);

    let pct = roundedShouldPay > 0 ? Math.min(100, Math.round((roundedCounted / roundedShouldPay) * 100)) : 0;
    const pBar = document.getElementById('cashProgressBar');
    const pTxt = document.getElementById('cashProgressText');
    if (pBar) pBar.style.width = pct + '%';
    if (pTxt) pTxt.innerText = pct + '%';

    const difference = countedCash - shouldPay;
    const diffDisplay = document.getElementById('displayDifference');
    const roundedDiff = Math.round(difference);
    
    diffDisplay.innerText = (difference > 0 ? '+' : '') + '৳ ' + roundedDiff.toLocaleString('en-US');
    document.getElementById('formDifference').value = difference.toFixed(2);

    const badge = document.getElementById('statusBadge');
    if (shouldPay === 0 && countedCash === 0) {
        badge.className = 'px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-slate-200 text-slate-700 uppercase tracking-wider border border-slate-300';
        badge.innerText = 'পেন্ডিং';
        diffDisplay.className = 'font-mono font-black text-sm text-slate-900';
    } else if (Math.abs(difference) < 0.01) {
        badge.className = 'px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-emerald-500 text-white uppercase tracking-wider border border-emerald-600 shadow-sm animate-pulse';
        badge.innerText = 'হিসাব মিলছে ✓';
        diffDisplay.className = 'font-mono font-black text-sm text-emerald-600';
    } else if (difference < 0) {
        badge.className = 'px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-rose-100 text-rose-800 uppercase tracking-wider border border-rose-200';
        badge.innerText = 'কম (শর্ট)';
        diffDisplay.className = 'font-mono font-black text-sm text-rose-600';
    } else {
        badge.className = 'px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-blue-100 text-blue-800 uppercase tracking-wider border border-blue-200';
        badge.innerText = 'বেশি ক্যাশ';
        diffDisplay.className = 'font-mono font-black text-sm text-blue-600';
    }
}

calculate();

const selectedDate = '<?= $selectedDate ?>';

function openReturnModal() {
    const modal = document.getElementById('returnModal');
    const body  = document.getElementById('returnModalBody');
    const total = document.getElementById('returnModalTotal');
    const dateEl = document.getElementById('returnModalDate');

    dateEl.textContent = '<?= date('d M Y', strtotime($selectedDate)) ?>';
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    body.innerHTML = `
      <div class="flex justify-center py-6">
        <div class="w-5 h-5 border-2 border-rose-500 border-t-transparent animate-spin rounded-full"></div>
      </div>`;

    fetch(`<?= url('dsr/api/settlement/returns') ?>?date=${selectedDate}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success || !data.items || !data.items.length) {
                body.innerHTML = '<p class="text-center text-slate-400 text-xs py-6 font-siliguri">এই তারিখে কোনো ফেরত মাল নেই।</p>';
                total.textContent = '৳ 0';
                return;
            }

            let grandTotal = 0;
            let rows = '';

            data.items.forEach((item, idx) => {
                const t = parseFloat(item.total);
                grandTotal += t;
                rows += `
                  <tr class="font-siliguri">
                    <td class="excel-row-num">${idx + 1}</td>
                    <td class="font-bold text-slate-800 text-xs text-left">${item.product_name}</td>
                    <td class="text-center font-mono text-xs text-slate-700">${parseFloat(item.qty)}</td>
                    <td class="excel-money text-slate-600 font-normal">৳ ${Math.round(parseFloat(item.price)).toLocaleString('en-US')}</td>
                    <td class="excel-money text-rose-600 font-bold">৳ ${Math.round(t).toLocaleString('en-US')}</td>
                  </tr>`;
            });

            body.innerHTML = `
              <div class="excel-container shadow-2xs border border-slate-300">
                <table class="excel-table text-xs">
                  <thead>
                    <tr>
                      <th class="excel-row-num">#</th>
                      <th>পণ্য</th>
                      <th class="text-center">পরিমাণ</th>
                      <th class="text-right">মূল্য</th>
                      <th class="text-right">মোট</th>
                    </tr>
                  </thead>
                  <tbody>${rows}</tbody>
                </table>
              </div>`;

            total.textContent = '৳ ' + Math.round(grandTotal).toLocaleString('en-US');
        })
        .catch(() => { body.innerHTML = '<p class="text-center text-rose-500 text-xs py-6 font-siliguri">নেটওয়ার্ক সমস্যা হয়েছে।</p>'; });
}

function closeReturnModal() {
    document.getElementById('returnModal').classList.add('hidden');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeReturnModal(); });
</script>
