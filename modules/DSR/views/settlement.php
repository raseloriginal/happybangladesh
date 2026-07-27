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

<div class="p-3.5 sm:p-5 space-y-4 pb-32 max-w-lg mx-auto font-sans">

  <!-- 1. Header Bar -->
  <div class="flex items-center justify-between bg-white p-3.5 border border-slate-200/90 shadow-xs">
    <div class="flex items-center gap-3">
      <a href="<?= url('dsr/dashboard') ?>" class="w-9 h-9 bg-slate-100 border border-slate-200/80 flex items-center justify-center text-slate-700 hover:bg-slate-200 transition">
        <i class="fa-solid fa-arrow-left text-sm"></i>
      </a>
      <div>
        <h1 class="text-base font-black text-slate-900 leading-tight">হিসাব মিলাও (সেটেলমেন্ট)</h1>
        <p class="text-[11px] text-slate-500 font-medium">সারাদিনের ক্যাশ ও মালের হিসাব জমা</p>
      </div>
    </div>

    <!-- Date Badge -->
    <span class="bg-blue-50 text-blue-700 border border-blue-200/80 text-xs font-bold px-2.5 py-1">
      <?= date('d M Y', strtotime($selectedDate)) ?>
    </span>
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

    <!-- 2. Date Selection Strip -->
    <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-none snap-x">
      <?php 
        for ($i = 6; $i >= 0; $i--):
          $d = date('Y-m-d', strtotime("-$i days"));
          $dayName = date('D', strtotime($d));
          $dayNum = date('d', strtotime($d));
          $isSelected = ($d === $selectedDate);
          $pillClass = $isSelected ? 'bg-blue-600 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200/80 hover:bg-slate-50';
      ?>
      <a href="?date=<?= $d ?>" class="snap-start flex-shrink-0 w-14 h-16 flex flex-col items-center justify-center transition active:scale-95 text-center <?= $pillClass ?>">
        <span class="text-[10px] font-bold uppercase tracking-wider <?= $isSelected ? 'text-blue-100' : 'text-slate-400' ?>"><?= $dayName ?></span>
        <span class="text-lg font-black font-mono mt-0.5"><?= $dayNum ?></span>
      </a>
      <?php endfor; ?>
    </div>

    <!-- Status Alerts -->
    <?php if ($isSubmitted): ?>
      <div class="bg-blue-50 border border-blue-200 p-3.5 flex items-center gap-3">
        <div class="w-8 h-8 bg-blue-100 text-blue-700 flex items-center justify-center shrink-0">
          <i class="fa-solid fa-lock text-sm"></i>
        </div>
        <div>
          <div class="text-xs font-bold text-blue-900">হিসাব ইতিমধ্যে জমা দেওয়া হয়েছে</div>
          <div class="text-[11px] text-blue-700">এই দিনের সেটেলমেন্ট ফাইনাল লক করা হয়েছে।</div>
        </div>
      </div>
    <?php elseif ($isNoDispatch): ?>
      <div class="bg-slate-100 border border-slate-200 p-3.5 flex items-center gap-3">
        <div class="w-8 h-8 bg-slate-200 text-slate-600 flex items-center justify-center shrink-0">
          <i class="fa-solid fa-box-open text-sm"></i>
        </div>
        <div>
          <div class="text-xs font-bold text-slate-800">আজকে কোনো মাল লোড করা হয়নি</div>
          <div class="text-[11px] text-slate-500">এই তারিখের জন্য কোনো সেটেলমেন্ট প্রয়োজন নেই।</div>
        </div>
      </div>
    <?php endif; ?>

    <!-- 3. Account Breakdown Card -->
    <div class="bg-white p-4 border border-slate-200/90 shadow-xs space-y-3">
      <div class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center justify-between border-b border-slate-100 pb-2.5">
        <span>হিসাবের বিস্তারিত (Account Breakdown)</span>
        <span class="text-[10px] text-slate-400 font-normal capitalize">ক্যালকুলেশন</span>
      </div>

      <div class="space-y-2 text-xs">
        <div class="flex justify-between items-center py-1">
          <span class="font-medium text-slate-600">মোট লোড করা মালের মূল্য</span>
          <span class="font-black text-slate-900 font-mono">৳ <?= number_format($dispatchedValue, 2) ?></span>
        </div>
        <div class="flex justify-between items-center py-1 text-rose-600 cursor-pointer hover:bg-rose-50 active:bg-rose-100 transition -mx-1 px-1" onclick="openReturnModal()" title="বিস্তারিত দেখুন">
          <span class="font-medium flex items-center gap-1">ফেরত মালের মূল্য (-) <i class="fa-solid fa-circle-info text-[10px] opacity-60"></i></span>
          <div class="flex items-center gap-1.5">
            <span class="font-bold font-mono">৳ <?= number_format($returnedValue, 2) ?></span>
            <i class="fa-solid fa-chevron-right text-[10px] opacity-50"></i>
          </div>
        </div>
        <div class="flex justify-between items-center py-1 text-amber-700">
          <span class="font-medium">ড্যামেজ পণ্য (-)</span>
          <span class="font-bold font-mono">৳ <?= number_format($savedDamage, 2) ?></span>
        </div>
        <div class="flex justify-between items-center py-1 text-purple-700">
          <span class="font-medium">সারাদিনের খরচ (-)</span>
          <span class="font-bold font-mono">৳ <?= number_format($savedExpense, 2) ?></span>
        </div>
      </div>

      <!-- Net Payable Highlight -->
      <div class="bg-gradient-to-r from-slate-900 to-blue-950 text-white p-3.5 flex justify-between items-center shadow-xs">
        <div>
          <span class="text-xs font-bold text-blue-200">ক্যাশারে জমা দেবার পরিমাণ (Net)</span>
          <span class="text-[10px] text-slate-300 block font-normal">(নিট নগদ জমা টাকা)</span>
        </div>
        <span class="text-xl font-black text-amber-400 font-mono" id="displayShouldPay">৳ 0.00</span>
      </div>
    </div>

    <!-- 4. Cash Note Denominations Grid -->
    <div class="bg-white p-4 border border-slate-200/90 shadow-xs space-y-3">
      <div class="text-xs font-black text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2.5">
        নোটের গণনা (Cash Counting)
      </div>

      <div class="grid grid-cols-2 gap-2">
        <?php 
          $denominations = [1000, 500, 200, 100, 50, 20, 10, 5, 2, 1];
          foreach ($denominations as $d): 
            $qty = $cashBreakdown[$d] ?? '';
        ?>
        <div class="flex items-center gap-1.5 bg-slate-50 p-2 border border-slate-200/60">
          <div class="w-12 font-black text-blue-600 text-xs font-mono text-center">৳ <?= $d ?></div>
          <div class="text-slate-400 text-xs">×</div>
          <input type="number" min="0" class="w-full bg-white border border-slate-200 py-1 px-1.5 text-center font-black text-slate-900 outline-none focus:border-blue-500 text-xs font-mono denomination-input <?= $isLocked ? 'opacity-70' : '' ?>" data-val="<?= $d ?>" value="<?= $qty ?>" oninput="calculate()" <?= $readonlyAttr ?>>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Total Counted Cash -->
      <div class="bg-emerald-50 border border-emerald-200/80 p-3 flex justify-between items-center">
        <span class="text-xs font-bold text-emerald-800">মোট গণনাকৃত ক্যাশ</span>
        <span class="text-lg font-black text-emerald-700 font-mono" id="displayCountedCash">৳ 0.00</span>
      </div>

      <!-- Difference & Live Status -->
      <div class="flex justify-between items-center pt-2 border-t border-slate-100 text-xs">
        <div>
          <span class="font-bold text-slate-600">কম / বেশি:</span>
          <span class="font-mono font-black ml-1 text-sm text-slate-900" id="displayDifference">৳ 0.00</span>
        </div>
        <div id="statusBadge" class="px-3 py-1 text-[10px] font-black bg-slate-100 text-slate-600 uppercase tracking-wide">
          পেন্ডিং
        </div>
      </div>
    </div>

    <!-- 5. Optional Note -->
    <div class="bg-white p-3.5 border border-slate-200/90 shadow-xs space-y-1.5">
      <label class="text-xs font-bold text-slate-700">মন্তব্য বা নোট (অপশনাল)</label>
      <textarea name="note" class="w-full bg-slate-50 border border-slate-200 p-2.5 text-xs text-slate-800 outline-none focus:border-blue-500 <?= $isLocked ? 'opacity-70' : '' ?>" rows="2" placeholder="ক্যাশ পার্থক্য বা কোনো মন্তব্য থাকলে লিখুন..." <?= $readonlyAttr ?>><?= htmlspecialchars($savedNote) ?></textarea>
    </div>

    <!-- Submit Button -->
    <?php if (!$isLocked): ?>
      <button type="submit" class="w-full py-3.5 font-black text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 shadow-md shadow-blue-600/20 active:scale-95 transition">
        হিসাব জমা দিন
      </button>
    <?php endif; ?>

  </form>

</div>

<!-- ===== Return Detail Modal ===== -->
<div id="returnModal" class="fixed inset-0 z-50 hidden" aria-modal="true" role="dialog">
  <!-- Backdrop -->
  <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeReturnModal()"></div>

  <!-- Sheet (Top Popup) -->
  <div class="absolute top-4 left-4 right-4 max-w-lg mx-auto bg-white shadow-2xl overflow-hidden flex flex-col max-h-[85vh]" style="animation: slideDown .25s ease">
    <!-- Header -->
    <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100">
      <div>
        <h2 class="text-sm font-black text-slate-900">ফেরত মালের বিস্তারিত</h2>
        <p class="text-[11px] text-slate-500 font-medium" id="returnModalDate"></p>
      </div>
      <button onclick="closeReturnModal()" class="w-8 h-8 bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition">
        <i class="fa-solid fa-xmark text-xs"></i>
      </button>
    </div>

    <!-- Body -->
    <div class="overflow-y-auto flex-1 p-4 space-y-4" id="returnModalBody">
      <!-- filled by JS -->
    </div>

    <!-- Footer Total -->
    <div class="px-5 py-3.5 border-t border-slate-100 flex justify-between items-center bg-rose-50">
      <span class="text-xs font-bold text-rose-800">মোট ফেরত মূল্য</span>
      <span class="text-base font-black text-rose-700 font-mono" id="returnModalTotal">৳ 0.00</span>
    </div>
  </div>
</div>

<style>
@keyframes slideDown {
  from { transform: translateY(-100%); opacity: 0; }
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
    document.getElementById('displayShouldPay').innerText = '৳ ' + shouldPay.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
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

    document.getElementById('displayCountedCash').innerText = '৳ ' + countedCash.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('formCountedCash').value = countedCash.toFixed(2);
    document.getElementById('formCashBreakdown').value = JSON.stringify(cashBreakdown);

    // Difference
    const difference = countedCash - shouldPay;
    const diffDisplay = document.getElementById('displayDifference');
    diffDisplay.innerText = (difference > 0 ? '+' : '') + '৳ ' + difference.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('formDifference').value = difference.toFixed(2);

    // Status Badge
    const badge = document.getElementById('statusBadge');
    if (shouldPay === 0 && countedCash === 0) {
        badge.className = 'px-3 py-1 text-[10px] font-black bg-slate-100 text-slate-600 uppercase tracking-wide';
        badge.innerText = 'পেন্ডিং';
        diffDisplay.className = 'font-mono font-black ml-1 text-sm text-slate-900';
    } else if (Math.abs(difference) < 0.01) {
        badge.className = 'px-3 py-1 text-[10px] font-black bg-emerald-100 text-emerald-800 uppercase tracking-wide';
        badge.innerText = 'হিসাব মিলছে';
        diffDisplay.className = 'font-mono font-black ml-1 text-sm text-emerald-600';
    } else if (difference < 0) {
        badge.className = 'px-3 py-1 text-[10px] font-black bg-rose-100 text-rose-800 uppercase tracking-wide';
        badge.innerText = 'কম (শর্ট)';
        diffDisplay.className = 'font-mono font-black ml-1 text-sm text-rose-600';
    } else {
        badge.className = 'px-3 py-1 text-[10px] font-black bg-blue-100 text-blue-800 uppercase tracking-wide';
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
            if (!data.success) { body.innerHTML = '<p class="text-center text-slate-500 text-xs py-8">ডেটা লোড করা যায়নি।</p>'; return; }

            let html = '';
            let grandTotal = 0;

            const buildSection = (title, color, bgColor, borderColor, items) => {
                if (!items.length) return '';
                let rows = '';
                items.forEach(item => {
                    const t = parseFloat(item.total);
                    grandTotal += t;
                    rows += `
                      <tr class="border-b border-slate-100 last:border-0">
                        <td class="py-2 pr-2 text-xs text-slate-700 font-medium">${item.product_name}</td>
                        <td class="py-2 text-center text-xs font-mono text-slate-600">${parseFloat(item.qty)}</td>
                        <td class="py-2 text-right text-xs font-mono text-slate-600">৳ ${parseFloat(item.price).toLocaleString('en-US',{minimumFractionDigits:2})}</td>
                        <td class="py-2 text-right text-xs font-mono font-bold ${color}">৳ ${t.toLocaleString('en-US',{minimumFractionDigits:2})}</td>
                      </tr>`;
                });
                return `
                  <div class="overflow-hidden border ${borderColor}">
                    <div class="${bgColor} px-3.5 py-2">
                      <span class="text-xs font-black ${color}">${title}</span>
                    </div>
                    <table class="w-full">
                      <thead>
                        <tr class="border-b border-slate-100">
                          <th class="text-left py-1.5 px-3.5 text-[10px] text-slate-400 font-semibold uppercase">পণ্য</th>
                          <th class="text-center py-1.5 text-[10px] text-slate-400 font-semibold uppercase">পরিমাণ</th>
                          <th class="text-right py-1.5 text-[10px] text-slate-400 font-semibold uppercase">মূল্য</th>
                          <th class="text-right py-1.5 px-3.5 text-[10px] text-slate-400 font-semibold uppercase">মোট</th>
                        </tr>
                      </thead>
                      <tbody class="px-3.5">${rows}</tbody>
                    </table>
                  </div>`;
            };

            html += buildSection('স্পট ফেরত (ডেলিভারি বাকি)', 'text-rose-700', 'bg-rose-50', 'border-rose-200', data.spot);
            html += buildSection('আনুষ্ঠানিক ফেরত', 'text-orange-700', 'bg-orange-50', 'border-orange-200', data.formal);

            if (!html) {
                html = '<p class="text-center text-slate-400 text-xs py-10">এই তারিখে কোনো ফেরত মাল নেই।</p>';
            }

            body.innerHTML = html;
            total.textContent = '৳ ' + grandTotal.toLocaleString('en-US', {minimumFractionDigits: 2});
        })
        .catch(() => { body.innerHTML = '<p class="text-center text-rose-500 text-xs py-8">নেটওয়ার্ক সমস্যা হয়েছে।</p>'; });
}

function closeReturnModal() {
    document.getElementById('returnModal').classList.add('hidden');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeReturnModal(); });
</script>
