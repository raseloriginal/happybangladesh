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

$isNoDispatch = ($dispatchedValue <= 0);
$isLocked = $isSubmitted || $isNoDispatch;
$readonlyAttr = $isLocked ? 'readonly' : '';
?>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap');
  .font-siliguri {
    font-family: 'Hind Siliguri', 'Inter', sans-serif;
  }
</style>

<div class="p-3 sm:p-5 space-y-4 pb-28 max-w-5xl mx-auto font-siliguri text-slate-800 print:p-0 print:max-w-none">

  <!-- 1. Premium Minimal Header Card -->
  <div class="bg-white/95 backdrop-blur-md px-4 py-3 sm:px-6 sm:py-4 rounded-2xl border border-slate-200/60 shadow-2xs flex items-center justify-between gap-3 print:shadow-none print:border-none print:p-0">
    <div class="flex items-center gap-3">
      <a href="<?= url('dsr/dashboard') ?>" class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 hover:bg-slate-900 hover:text-white transition-all duration-200 flex items-center justify-center text-slate-600 shadow-2xs active:scale-95 print:hidden">
        <i class="fa-solid fa-arrow-left text-xs sm:text-sm"></i>
      </a>
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 leading-tight tracking-tight">হিসাব মিলাও</h1>
        <p class="text-xs text-slate-400 font-medium leading-tight mt-1">সারাদিনের ক্যাশ ও মালের হিসাব জমা • <span class="font-mono text-slate-500 font-bold"><?= date('d M Y', strtotime($selectedDate)) ?></span></p>
      </div>
    </div>

      <!-- Date Picker Form (Icon Only) -->
      <form method="GET" action="<?= url('dsr/settlement') ?>" id="dateForm" class="relative flex items-center">
        <button type="button" onclick="const inp=document.getElementById('dateInput'); if(inp.showPicker){inp.showPicker()}else{inp.click()}" class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition active:scale-95 shadow-2xs border border-slate-200/60" title="তারিখ পরিবর্তন করুন">
          <i class="fa-regular fa-calendar-days text-sm"></i>
        </button>
        <input type="date" id="dateInput" name="date" value="<?= h($selectedDate) ?>" onchange="document.getElementById('dateForm').submit()" class="absolute opacity-0 pointer-events-none inset-0 w-full h-full">
      </form>
    </div>
  </div>

  <form action="<?= url('dsr/settlement/submit') ?>" method="POST" id="settlementForm" class="space-y-4">
    <?= Helpers::csrfField() ?>
    <input type="hidden" name="dispatched_value" value="<?= $dispatchedValue ?>">
    <input type="hidden" name="returned_value" value="<?= $returnedValue ?>">
    <input type="hidden" name="should_pay" id="formShouldPay" value="0">
    <input type="hidden" name="counted_cash" id="formCountedCash" value="0">
    <input type="hidden" name="difference" id="formDifference" value="0">
    <input type="hidden" name="cash_breakdown" id="formCashBreakdown" value="{}">
    <input type="hidden" name="settlement_date" value="<?= htmlspecialchars($selectedDate) ?>">

    <!-- Status Alerts -->
    <?php if ($isSubmitted): ?>
      <div class="bg-blue-50/80 backdrop-blur-xs border border-blue-200 rounded-2xl p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
          <i class="fa-solid fa-lock text-sm"></i>
        </div>
        <div class="text-left">
          <div class="text-xs font-bold text-blue-900">হিসাব ইতিমধ্যে জমা দেওয়া হয়েছে</div>
          <div class="text-[11px] text-blue-700 mt-0.5">এই দিনের সেটেলমেন্ট ফাইনাল লক করা হয়েছে।</div>
        </div>
      </div>
    <?php endif; ?>

    <!-- 3. Account Breakdown Card (Excel Table Style) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden text-left">
      <div class="bg-slate-50 px-4 py-3 border-b border-slate-200 flex justify-between items-center text-xs">
        <span class="font-bold text-slate-800">হিসাবের বিবরণ (Breakdown)</span>
        <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">হিসাব</span>
      </div>

      <div class="divide-y divide-slate-100 text-xs">
        <div class="flex justify-between items-center p-3">
          <span class="font-semibold text-slate-600">মোট লোড করা মালের মূল্য</span>
          <span class="font-black text-slate-900 font-mono text-[13px]">৳ <?= number_format($dispatchedValue) ?></span>
        </div>
        <div class="flex justify-between items-center p-3 cursor-pointer hover:bg-slate-50/50 transition" onclick="openReturnModal()" title="বিস্তারিত দেখুন">
          <span class="font-semibold text-slate-600 flex items-center gap-1.5">ফেরত মালের মূল্য (-) <i class="fa-solid fa-circle-info text-[10px] text-slate-450"></i></span>
          <div class="flex items-center gap-1.5 text-rose-600">
            <span class="font-black font-mono text-[13px]">৳ <?= number_format($returnedValue) ?></span>
            <i class="fa-solid fa-chevron-right text-[10px] opacity-65"></i>
          </div>
        </div>
        <div class="flex justify-between items-center p-3">
          <span class="font-semibold text-slate-600">ড্যামেজ পণ্য (-)</span>
          <span class="font-black font-mono text-[13px] text-amber-600">৳ <?= number_format($savedDamage) ?></span>
        </div>
        <div class="flex justify-between items-center p-3">
          <span class="font-semibold text-slate-600">সারাদিনের খরচ (-)</span>
          <span class="font-black font-mono text-[13px] text-purple-600">৳ <?= number_format($savedExpense) ?></span>
        </div>
      </div>

      <!-- Net Payable Highlight (Excel Sum Vibe) -->
      <div class="bg-slate-50 border-t border-slate-200 p-3.5 flex justify-between items-center">
        <div>
          <span class="text-xs font-bold text-slate-600">ক্যাশারে জমা দেবার পরিমাণ (Net)</span>
          <span class="text-[9px] text-slate-400 block font-normal">(নিট নগদ জমা টাকা)</span>
        </div>
        <span class="text-lg font-black text-slate-900 font-mono" id="displayShouldPay">৳ 0</span>
      </div>
    </div>

    <!-- 4. Cash Note Denominations Grid (Excel Grid Vibe) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden text-left">
      <div class="bg-slate-50 px-4 py-3 border-b border-slate-200 text-xs font-bold text-slate-800">
        নোটের গণনা (Cash Counting)
      </div>

      <div class="p-3 bg-slate-50/30">
        <div class="grid grid-cols-2 gap-2">
          <?php 
            $denominations = [1000, 500, 200, 100, 50, 20, 10, 5, 2, 1];
            foreach ($denominations as $d): 
              $qty = $cashBreakdown[$d] ?? '';
          ?>
          <div class="flex items-center gap-1.5 bg-white p-2 border border-slate-200 rounded-xl shadow-3xs">
            <div class="w-12 font-black text-slate-600 text-xs font-mono text-center">৳ <?= $d ?></div>
            <div class="text-slate-350 text-xs font-bold select-none">×</div>
            <input type="number" min="0" class="w-full bg-slate-50/50 border border-slate-200/80 rounded-lg py-1 px-1.5 text-center font-black text-slate-900 outline-none focus:border-blue-500 text-xs font-mono denomination-input <?= $isLocked ? 'opacity-70' : '' ?>" data-val="<?= $d ?>" value="<?= $qty ?>" oninput="calculate()" <?= $readonlyAttr ?>>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Summary rows inside grid card -->
      <div class="divide-y divide-slate-100 border-t border-slate-200 text-xs">
        <!-- Total counted cash -->
        <div class="bg-slate-50 p-3.5 flex justify-between items-center">
          <span class="font-bold text-slate-700">মোট গণনাকৃত ক্যাশ:</span>
          <span class="text-base font-black text-slate-900 font-mono" id="displayCountedCash">৳ 0</span>
        </div>

        <!-- Difference & Live Status -->
        <div class="p-3.5 flex justify-between items-center">
          <div class="flex items-center gap-1.5">
            <span class="font-bold text-slate-600">কম / বেশি:</span>
            <span class="font-mono font-black text-slate-950 text-sm" id="displayDifference">৳ 0</span>
          </div>
          <div id="statusBadge" class="px-2.5 py-0.5 text-[9px] font-bold rounded-lg bg-slate-100 text-slate-600 uppercase tracking-wide border border-slate-200">
            পেন্ডিং
          </div>
        </div>
      </div>
    </div>

    <!-- 5. Optional Note -->
    <div class="bg-white p-4 border border-slate-200 rounded-2xl shadow-3xs space-y-2 text-left">
      <label class="text-xs font-bold text-slate-700">মন্তব্য বা নোট (অপশনাল)</label>
      <textarea name="note" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs text-slate-800 outline-none focus:border-blue-500 <?= $isLocked ? 'opacity-70' : '' ?>" rows="2" placeholder="ক্যাশ পার্থক্য বা কোনো মন্তব্য থাকলে লিখুন..." <?= $readonlyAttr ?>><?= htmlspecialchars($savedNote) ?></textarea>
    </div>

    <!-- Submit Button -->
    <?php if (!$isLocked): ?>
      <button type="submit" class="w-full py-3.5 font-bold text-white bg-blue-600 border border-blue-700 hover:bg-blue-700 rounded-xl active:scale-95 transition-all shadow-sm text-xs font-siliguri">
        হিসাব জমা দিন
      </button>
    <?php endif; ?>

  </form>

</div>

<!-- ===== Return Detail Modal (Excel Style) ===== -->
<div id="returnModal" class="fixed inset-0 z-[100] hidden" aria-modal="true" role="dialog">
  <!-- Backdrop -->
  <div class="absolute inset-0 bg-black/60 backdrop-blur-xs" onclick="closeReturnModal()"></div>

  <!-- Sheet (Top Popup) -->
  <div class="absolute top-4 left-4 right-4 max-w-lg mx-auto bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[85vh] border border-slate-200" style="animation: slideDown .25s ease">
    <!-- Header -->
    <div class="flex items-center justify-between px-4 py-3 border-b border-slate-150 bg-slate-50">
      <div class="text-left">
        <h2 class="text-sm font-bold text-slate-900">ফেরত মালের বিস্তারিত</h2>
        <p class="text-[10px] text-slate-400 font-semibold" id="returnModalDate"></p>
      </div>
      <button onclick="closeReturnModal()" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 transition">
        <i class="fa-solid fa-xmark text-xs"></i>
      </button>
    </div>

    <!-- Body -->
    <div class="overflow-y-auto flex-1 p-4 space-y-4" id="returnModalBody">
      <!-- filled by JS -->
    </div>

    <!-- Footer Total -->
    <div class="px-4 py-3 border-t border-slate-150 flex justify-between items-center bg-rose-50/80">
      <span class="text-xs font-bold text-rose-800">মোট ফেরত মূল্য:</span>
      <span class="text-base font-black text-rose-700 font-mono" id="returnModalTotal">৳ 0</span>
    </div>
  </div>
</div>

<style>
@keyframes slideDown {
  from { transform: translateY(-20px); opacity: 0; }
  to   { transform: translateY(0);     opacity: 1; }
}
</style>

<script>
const dispatched = <?= (float)$dispatchedValue ?>;
const returned = <?= (float)$returnedValue ?>;

function calculate() {
    const damage = parseFloat(document.getElementById('inputDamage')?.value || <?= (float)$savedDamage ?>) || 0;
    const expense = parseFloat(document.getElementById('inputExpense')?.value || <?= (float)$savedExpense ?>) || 0;
    
    // Should Pay
    const shouldPay = dispatched - returned - damage - expense;
    document.getElementById('displayShouldPay').innerText = '৳ ' + Math.round(shouldPay).toLocaleString('en-US');
    document.getElementById('formShouldPay').value = shouldPay.toFixed(2);

    // Cash Count
    let countedCash = 0;
    const cashBreakdown = {};
    document.querySelectorAll('.denomination-input').forEach(input => {
        const val = parseFloat(input.getAttribute('data-val'));
        const qty = parseInt(input.value) || 0;
        if(qty > 0) {
            countedCash += (val * qty);
            cashBreakdown[val] = qty;
        }
    });

    document.getElementById('displayCountedCash').innerText = '৳ ' + Math.round(countedCash).toLocaleString('en-US');
    document.getElementById('formCountedCash').value = countedCash.toFixed(2);
    document.getElementById('formCashBreakdown').value = JSON.stringify(cashBreakdown);

    // Difference
    const difference = countedCash - shouldPay;
    const diffDisplay = document.getElementById('displayDifference');
    diffDisplay.innerText = (difference > 0 ? '+' : '') + '৳ ' + Math.round(difference).toLocaleString('en-US');
    document.getElementById('formDifference').value = difference.toFixed(2);

    // Status Badge
    const badge = document.getElementById('statusBadge');
    if (shouldPay === 0 && countedCash === 0) {
        badge.className = 'px-2.5 py-0.5 text-[9px] font-bold rounded-lg bg-slate-100 text-slate-600 uppercase tracking-wide border border-slate-200';
        badge.innerText = 'পেন্ডিং';
        diffDisplay.className = 'font-mono font-black ml-1 text-sm text-slate-900';
    } else if (Math.abs(difference) < 0.01) {
        badge.className = 'px-2.5 py-0.5 text-[9px] font-bold rounded-lg bg-emerald-100 text-emerald-800 uppercase tracking-wide border border-emerald-200';
        badge.innerText = 'হিসাব মিলছে';
        diffDisplay.className = 'font-mono font-black ml-1 text-sm text-emerald-600';
    } else if (difference < 0) {
        badge.className = 'px-2.5 py-0.5 text-[9px] font-bold rounded-lg bg-rose-100 text-rose-800 uppercase tracking-wide border border-rose-200';
        badge.innerText = 'কম (শর্ট)';
        diffDisplay.className = 'font-mono font-black ml-1 text-sm text-rose-600';
    } else {
        badge.className = 'px-2.5 py-0.5 text-[9px] font-bold rounded-lg bg-blue-100 text-blue-800 uppercase tracking-wide border border-blue-200';
        badge.innerText = 'বেশি ক্যাশ';
        diffDisplay.className = 'font-mono font-black ml-1 text-sm text-blue-600';
    }
}

// Initial calculation
calculate();

// ---- Return Detail Modal ----
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
      <div class="flex justify-center py-8">
        <div class="w-7 h-7 border-2 border-rose-500 border-t-transparent animate-spin"></div>
      </div>`;

    fetch(`<?= url('dsr/api/settlement/returns') ?>?date=${selectedDate}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success || !data.items || !data.items.length) {
                body.innerHTML = '<p class="text-center text-slate-400 text-xs py-10 font-siliguri">এই তারিখে কোনো ফেরত মাল নেই।</p>';
                total.textContent = '৳ 0';
                return;
            }

            let grandTotal = 0;
            let rows = '';

            data.items.forEach(item => {
                const t = parseFloat(item.total);
                grandTotal += t;
                rows += `
                  <tr class="border-b border-slate-100 last:border-0 font-siliguri">
                    <td class="py-2.5 pr-2 text-xs text-slate-800 font-semibold text-left">${item.product_name}</td>
                    <td class="py-2.5 text-center text-xs font-mono text-slate-600">${parseFloat(item.qty)}</td>
                    <td class="py-2.5 text-right text-xs font-mono text-slate-600">৳ ${Math.round(parseFloat(item.price)).toLocaleString('en-US')}</td>
                    <td class="py-2.5 text-right text-xs font-mono font-bold text-rose-600">৳ ${Math.round(t).toLocaleString('en-US')}</td>
                  </tr>`;
            });

            body.innerHTML = `
              <div class="overflow-hidden border border-slate-200 rounded-xl">
                <table class="w-full">
                  <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                      <th class="text-left py-2 px-3 text-[10px] text-slate-500 font-bold uppercase font-siliguri">পণ্য</th>
                      <th class="text-center py-2 text-[10px] text-slate-500 font-bold uppercase font-siliguri">পরিমাণ</th>
                      <th class="text-right py-2 text-[10px] text-slate-500 font-bold uppercase font-siliguri">মূল্য</th>
                      <th class="text-right py-2 px-3 text-[10px] text-slate-500 font-bold uppercase font-siliguri">মোট</th>
                    </tr>
                  </thead>
                  <tbody class="px-3 text-xs">${rows}</tbody>
                </table>
              </div>`;

            total.textContent = '৳ ' + Math.round(grandTotal).toLocaleString('en-US');
        })
        .catch(() => { body.innerHTML = '<p class="text-center text-rose-500 text-xs py-8 font-siliguri">নেটওয়ার্ক সমস্যা হয়েছে।</p>'; });
}

function closeReturnModal() {
    document.getElementById('returnModal').classList.add('hidden');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeReturnModal(); });
</script>
