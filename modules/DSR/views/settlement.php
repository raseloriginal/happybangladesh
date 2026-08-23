<?php 
$pageTitle = 'Daily Settlement'; 
$isSubmitted = !empty($existingSettlement);
$savedDamage = $isSubmitted ? $existingSettlement['total_damage'] : $totalDamage;
$savedExpense = $isSubmitted ? $existingSettlement['total_expense'] : $totalExpense;
$savedDeliveryOc = $isSubmitted ? $existingSettlement['delivery_oc'] : $deliveryOc;
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

<!-- Daily Settlement Page (Modern Dashboard Style) -->
<div class="p-3 sm:p-5 space-y-4 pb-28 max-w-4xl mx-auto font-siliguri text-slate-800 print:p-0 print:max-w-none">

  <!-- Header Card (Modern Minimal) -->
  <div class="bg-white px-4 py-3.5 sm:px-6 sm:py-4 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center justify-between gap-3 print:shadow-none print:border-none print:p-0">
    <div class="flex items-center gap-3">
      <a href="<?= url('dsr/dashboard') ?>" class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition flex items-center justify-center shadow-2xs active:scale-95 print:hidden">
        <i class="fa-solid fa-arrow-left text-xs sm:text-sm"></i>
      </a>
      <div>
        <div class="flex items-center gap-2">
          <h1 class="text-xl sm:text-2xl font-black text-slate-900 leading-tight tracking-tight">
            দৈনিক হিসাব সেটেলমেন্ট
          </h1>
          <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200/60 print:hidden">
            <i class="fa-solid fa-calculator text-[9px] mr-1"></i> হিসাব মিলানো
          </span>
        </div>
        <p class="text-xs text-slate-500 font-medium leading-tight mt-0.5">দিনের ক্যাশ নোট গণনা ও নিট হিসাব সমর্পণ</p>
      </div>
    </div>
    
    <div class="flex items-center gap-2 print:hidden">
      <!-- Date Selector -->
      <form method="GET" action="<?= url('dsr/settlement') ?>" id="dateForm" class="relative flex items-center">
        <button type="button" onclick="const inp=document.getElementById('dateInput'); if(inp.showPicker){inp.showPicker()}else{inp.click()}" class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition active:scale-95 shadow-2xs border border-slate-200" title="তারিখ পরিবর্তন">
          <i class="fa-regular fa-calendar-days text-sm"></i>
        </button>
        <input type="date" id="dateInput" name="date" value="<?= h($selectedDate) ?>" onchange="document.getElementById('dateForm').submit()" class="absolute opacity-0 pointer-events-none inset-0 w-full h-full">
      </form>
    </div>
  </div>

  <!-- Quick Status Banners -->
  <?php if ($isSubmitted): ?>
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-3.5 flex items-center gap-3 text-xs text-blue-900 shadow-2xs">
      <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-sm shrink-0">
        <i class="fa-solid fa-lock"></i>
      </div>
      <div>
        <span class="font-black text-sm block">সেটেলমেন্ট জমা সম্পূর্ণ &amp; লকড</span>
        <span class="text-blue-700 font-medium">আজকের দিনের হিসাব ইতিপূর্বে সফলভাবে ক্যাশারে জমা দেওয়া হয়েছে।</span>
      </div>
    </div>
  <?php elseif (!$isReturned): ?>
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-3.5 flex items-center gap-3 text-xs text-amber-900 shadow-2xs">
      <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-sm shrink-0">
        <i class="fa-solid fa-triangle-exclamation"></i>
      </div>
      <div>
        <span class="font-black text-sm block">স্ট্যাটাস অপেক্ষমাণ</span>
        <span class="text-amber-800 font-medium">ম্যানেজার কর্তৃক ডেলিভারি স্ট্যাটাস 'Returned' সম্পন্ন হওয়ার পর ক্যাশ মেলানো যাবে।</span>
      </div>
    </div>
  <?php endif; ?>

  <!-- Summary KPI Overview Bar (4 Key Metrics) -->
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
    <div class="bg-white p-3 rounded-2xl border border-slate-200/80 shadow-2xs space-y-1">
      <span class="text-[11px] font-bold text-slate-500 block">মোট লোড (Dispatch)</span>
      <span class="font-black text-sm sm:text-base text-slate-900 font-mono tracking-tight block">৳<?= number_format($dispatchedValue) ?></span>
    </div>

    <div class="bg-white p-3 rounded-2xl border border-slate-200/80 shadow-2xs space-y-1">
      <span class="text-[11px] font-bold text-slate-500 block">ফেরত মাল (Return)</span>
      <span class="font-black text-sm sm:text-base text-rose-600 font-mono tracking-tight block">৳<?= number_format($returnedValue) ?></span>
    </div>

    <div class="bg-white p-3 rounded-2xl border border-slate-200/80 shadow-2xs space-y-1">
      <span class="text-[11px] font-bold text-slate-500 block">মোট বিক্রি (Sales)</span>
      <span class="font-black text-sm sm:text-base text-emerald-600 font-mono tracking-tight block">৳<?= number_format($salesAmount) ?></span>
    </div>

    <div class="bg-blue-600 text-white p-3 rounded-2xl border border-blue-700 shadow-xs space-y-1">
      <span class="text-[11px] font-bold text-blue-100 block">নিট জমা (Net Pay)</span>
      <span id="fxShouldPay" class="font-black text-sm sm:text-base text-white font-mono tracking-tight block">৳0</span>
    </div>
  </div>

  <form action="<?= url('dsr/settlement/submit') ?>" method="POST" id="settlementForm" class="space-y-4">
    <?= Helpers::csrfField() ?>
    <input type="hidden" name="dispatched_value" value="<?= $dispatchedValue ?>">
    <input type="hidden" name="returned_value" value="<?= $returnedValue ?>">
    <input type="hidden" name="damage_amount" value="<?= $savedDamage ?>">
    <input type="hidden" name="total_expense" value="<?= $savedExpense ?>">
    <input type="hidden" name="delivery_oc" id="formDeliveryOc" value="<?= $savedDeliveryOc ?>">
    <input type="hidden" name="should_pay" id="formShouldPay" value="0">
    <input type="hidden" name="counted_cash" id="formCountedCash" value="0">
    <input type="hidden" name="difference" id="formDifference" value="0">
    <input type="hidden" name="cash_breakdown" id="formCashBreakdown" value="{}">
    <input type="hidden" name="settlement_date" value="<?= htmlspecialchars($selectedDate) ?>">

    <!-- SECTION 1: ACCOUNT STATEMENT BREAKDOWN CARD -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
      <div class="bg-slate-50/80 px-4 py-3 border-b border-slate-200 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-black">১</span>
          <h2 class="font-black text-xs sm:text-sm text-slate-800 uppercase tracking-tight">হিসাব বিবরণী (Account Breakdown)</h2>
        </div>
      </div>

      <div class="divide-y divide-slate-100 text-xs sm:text-sm">
        <!-- Row 1: Total Dispatch -->
        <div class="p-3.5 flex items-center justify-between hover:bg-slate-50/50 transition">
          <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center text-xs">
              <i class="fa-solid fa-truck"></i>
            </div>
            <span class="font-bold text-slate-800">মোট লোড করা মাল</span>
          </div>
          <span class="font-black text-slate-900 font-mono">৳ <?= number_format($dispatchedValue) ?></span>
        </div>

        <!-- Row 1.5: Total Sales Amount -->
        <div class="p-3.5 flex items-center justify-between hover:bg-emerald-50/40 transition">
          <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs">
              <i class="fa-solid fa-cart-shopping"></i>
            </div>
            <span class="font-bold text-slate-800">মোট বিক্রি (বিক্রয় মূল্য)</span>
          </div>
          <span class="font-black text-emerald-600 font-mono">৳ <?= number_format($salesAmount ?? 0) ?></span>
        </div>

        <!-- Row 2: Returned Goods -->
        <div class="p-3.5 flex items-center justify-between hover:bg-rose-50/40 transition cursor-pointer" onclick="openReturnModal()" title="ফেরত মালের বিস্তারিত দেখুন">
          <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center text-xs">
              <i class="fa-solid fa-rotate-left"></i>
            </div>
            <div>
              <span class="font-bold text-slate-800 block leading-tight">ফেরত মালের মূল্য (-)</span>
              <span class="text-[10px] text-blue-600 font-bold underline">বিস্তারিত দেখুন</span>
            </div>
          </div>
          <span class="font-black text-rose-600 font-mono">- ৳ <?= number_format($returnedValue) ?></span>
        </div>

        <!-- Row 3: Damage Items -->
        <div class="p-3.5 flex items-center justify-between hover:bg-amber-50/40 transition">
          <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center text-xs">
              <i class="fa-solid fa-box-open"></i>
            </div>
            <span class="font-bold text-slate-800">ড্যামেজ পণ্য (-)</span>
          </div>
          <span class="font-black text-amber-600 font-mono">- ৳ <?= number_format($savedDamage) ?></span>
        </div>

        <!-- Row 4: Daily Expenses -->
        <div class="p-3.5 flex items-center justify-between hover:bg-purple-50/40 transition">
          <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center text-xs">
              <i class="fa-solid fa-receipt"></i>
            </div>
            <span class="font-bold text-slate-800">সারাদিনের খরচ (-)</span>
          </div>
          <span class="font-black text-purple-600 font-mono">- ৳ <?= number_format($savedExpense) ?></span>
        </div>

        <!-- Row 5: Delivery OC -->
        <div class="p-3.5 flex items-center justify-between hover:bg-blue-50/40 transition cursor-pointer" onclick="openOcModal()" title="O/C বিস্তারিত দেখুন">
          <div class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-xs">
              <i class="fa-solid fa-coins"></i>
            </div>
            <div>
              <span class="font-bold text-slate-800 block leading-tight">ডেলিভারি O/C (কমিশন/ওভার)</span>
              <span class="text-[10px] text-blue-600 font-bold underline">SR অনুযায়ী O/C দেখুন</span>
            </div>
          </div>
          <span class="font-black <?= $savedDeliveryOc >= 0 ? 'text-emerald-600' : 'text-rose-600' ?> font-mono">
            <?= $savedDeliveryOc >= 0 ? '+' : '' ?>৳ <?= number_format($savedDeliveryOc) ?>
          </span>
        </div>

        <!-- Net Total Header -->
        <div class="p-4 bg-blue-50/80 flex items-center justify-between border-t-2 border-slate-200">
          <span class="font-black text-slate-900 text-xs sm:text-sm">ক্যাশারে জমা (নিট দেয় মূল্য):</span>
          <span class="font-black text-blue-800 text-base sm:text-lg font-mono tracking-tight" id="displayShouldPay">৳ 0</span>
        </div>
      </div>
    </div>

    <!-- SECTION 2: CASH DENOMINATION COUNTER CARD -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden space-y-3">
      <div class="bg-slate-50/80 px-4 py-3 border-b border-slate-200 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-black">২</span>
          <h2 class="font-black text-xs sm:text-sm text-slate-800 uppercase tracking-tight">ক্যাশ নোট গণনাকারক (Cash Notes)</h2>
        </div>
        
        <?php if (!$isLocked): ?>
        <div class="flex items-center gap-1.5">
          <button type="button" onclick="autoFillCash()" class="px-2.5 py-1 text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-lg transition active:scale-95 shadow-2xs">
            <i class="fa-solid fa-wand-magic-sparkles text-emerald-600 mr-1"></i> অটো ফিল
          </button>
          <button type="button" onclick="clearCash()" class="px-2.5 py-1 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 border border-slate-200 rounded-lg transition active:scale-95 shadow-2xs">
            ক্লিয়ার
          </button>
        </div>
        <?php endif; ?>
      </div>

      <!-- Denominations Grid Cards (Clean Touch-Friendly Inputs) -->
      <div class="p-3 sm:p-4 grid grid-cols-1 sm:grid-cols-2 gap-2.5">
        <?php 
          $denominations = [1000, 500, 200, 100, 50, 20, 10, 5, 2, 1];
          foreach ($denominations as $d): 
            $qty = $cashBreakdown[$d] ?? '';
        ?>
        <div class="bg-slate-50/70 border border-slate-200/80 rounded-xl p-2.5 flex items-center justify-between gap-2 hover:bg-white hover:border-slate-300 transition shadow-2xs" id="denom-row-<?= $d ?>">
          <div class="flex items-center gap-2">
            <span class="w-12 text-center font-mono font-black text-xs bg-emerald-100/80 text-emerald-800 py-1 px-1.5 rounded-lg border border-emerald-200/80">৳ <?= $d ?></span>
          </div>

          <div class="flex items-center gap-1.5">
            <?php if (!$isLocked): ?>
            <button type="button" onclick="stepDenom(<?= $d ?>, -1)" class="w-7 h-7 rounded-lg bg-slate-200 text-slate-700 hover:bg-slate-300 font-bold text-sm flex items-center justify-center active:scale-90 transition">-</button>
            <?php endif; ?>

            <input type="number" min="0" id="denom-input-<?= $d ?>" class="w-12 sm:w-16 bg-white border border-slate-300 rounded-lg py-1 px-1 text-center font-mono font-black text-slate-900 outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-600 text-xs denomination-input shadow-2xs <?= $isLocked ? 'opacity-70' : '' ?>" data-val="<?= $d ?>" value="<?= $qty ?>" oninput="calculate()" <?= $readonlyAttr ?>>

            <?php if (!$isLocked): ?>
            <button type="button" onclick="stepDenom(<?= $d ?>, 1)" class="w-7 h-7 rounded-lg bg-blue-600 text-white hover:bg-blue-700 font-bold text-sm flex items-center justify-center active:scale-90 transition">+</button>
            <?php endif; ?>
          </div>

          <div class="w-16 text-right font-mono font-bold text-xs text-slate-800" id="subtotal-<?= $d ?>">৳ 0</div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Live Calculation & Difference Bar -->
      <div class="bg-slate-900 text-white p-3.5 space-y-2 text-xs font-siliguri border-t border-slate-800">
        <div class="flex justify-between items-center">
          <span class="text-slate-300 font-medium">গণনাকৃত মোট ক্যাশ:</span>
          <span class="text-base font-black text-white font-mono" id="displayCountedCash">৳ 0</span>
        </div>

        <div class="flex justify-between items-center pt-2 border-t border-slate-800">
          <div class="flex items-center gap-2">
            <span class="text-slate-300 font-medium">পার্থক্য (Difference):</span>
            <span class="font-mono font-black text-sm" id="displayDifference">৳ 0</span>
          </div>
          <div id="statusBadge" class="px-3 py-1 text-[10px] font-black rounded-full bg-slate-800 text-slate-300 uppercase tracking-wider border border-slate-700">
            পেন্ডিং
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION 3: REMARKS NOTE CARD -->
    <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-xs space-y-1.5 text-left">
      <label class="text-xs font-black text-slate-700 block uppercase tracking-wider">
        <i class="fa-solid fa-pen text-slate-400 mr-1"></i> মন্তব্য / নোট (Optional Remarks)
      </label>
      <textarea name="note" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-xs font-semibold text-slate-800 outline-none focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition <?= $isLocked ? 'opacity-70' : '' ?>" rows="2" placeholder="হিসাব সংক্রান্ত কোনো বিশেষ মন্তব্য থাকলে উল্লেখ করুন..." <?= $readonlyAttr ?>><?= htmlspecialchars($savedNote) ?></textarea>
    </div>

    <!-- SUBMIT ACTION BUTTON -->
    <?php if (!$isLocked): ?>
      <button type="submit" class="w-full py-3.5 font-extrabold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 rounded-2xl active:scale-[0.99] transition shadow-lg shadow-blue-500/20 text-sm sm:text-base font-siliguri flex items-center justify-center gap-2 border border-blue-500/30">
        <i class="fa-solid fa-check-circle"></i>
        <span>সেটেলমেন্ট হিসাব জমা দিন (Submit Settlement)</span>
      </button>
    <?php endif; ?>

  </form>

</div>

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

<!-- ===== OC Detail Modal ===== -->
<div id="ocModal" class="fixed inset-0 z-[100] hidden" aria-modal="true" role="dialog">
  <div class="absolute inset-0 bg-black/60 backdrop-blur-xs" onclick="closeOcModal()"></div>

  <div class="absolute top-4 left-4 right-4 max-w-lg mx-auto bg-white rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[85vh] border border-slate-300" style="animation: slideDown .25s ease">
    
    <div class="flex items-center justify-between px-3 py-2 border-b border-slate-200 bg-slate-100">
      <div class="text-left">
        <h2 class="text-xs font-bold text-slate-900">SR অনুযায়ী O/C বিবরণ</h2>
        <p class="text-[10px] text-slate-500 font-mono" id="ocModalDate"></p>
      </div>
      <button onclick="closeOcModal()" class="w-6 h-6 rounded bg-white border border-slate-300 hover:bg-slate-200 flex items-center justify-center text-slate-600 transition">
        <i class="fa-solid fa-xmark text-xs"></i>
      </button>
    </div>

    <div class="overflow-y-auto flex-1 p-3 space-y-3" id="ocModalBody">
      <!-- filled by JS -->
    </div>

    <div class="px-3 py-2 border-t border-slate-200 flex justify-between items-center bg-blue-50/80">
      <span class="text-xs font-bold text-blue-800">মোট O/C:</span>
      <span class="text-sm font-black text-blue-700 font-mono" id="ocModalTotal">৳ 0</span>
    </div>

  </div>
</div>

<script>
const dispatched = <?= (float)$dispatchedValue ?>;
const returned = <?= (float)$returnedValue ?>;
const damageVal = <?= (float)$savedDamage ?>;
const expenseVal = <?= (float)$savedExpense ?>;
const deliveryOcVal = <?= (float)$savedDeliveryOc ?>;

function stepDenom(denom, step) {
  const input = document.getElementById(`denom-input-${denom}`);
  if (!input || input.readOnly) return;
  let current = parseInt(input.value) || 0;
  let nextVal = Math.max(0, current + step);
  input.value = nextVal > 0 ? nextVal : '';
  calculate();
}

function autoFillCash() {
  const shouldPay = Math.round(dispatched - returned - damageVal - expenseVal + deliveryOcVal);
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
    const shouldPay = dispatched - returned - damageVal - expenseVal + deliveryOcVal;
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

function openOcModal() {
    const modal = document.getElementById('ocModal');
    const body  = document.getElementById('ocModalBody');
    const total = document.getElementById('ocModalTotal');
    const dateEl = document.getElementById('ocModalDate');

    dateEl.textContent = '<?= date('d M Y', strtotime($selectedDate)) ?>';
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    body.innerHTML = `
      <div class="flex justify-center py-6">
        <div class="w-5 h-5 border-2 border-blue-500 border-t-transparent animate-spin rounded-full"></div>
      </div>`;

    fetch(`<?= url('dsr/api/settlement/oc') ?>?date=${selectedDate}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success || !data.items || !data.items.length) {
                body.innerHTML = '<p class="text-center text-slate-400 text-xs py-6 font-siliguri">এই তারিখে কোনো O/C নেই।</p>';
                total.textContent = '৳ 0';
                return;
            }

            let grandTotal = 0;
            let rows = '';

            data.items.forEach((item, idx) => {
                const t = parseFloat(item.sr_oc);
                grandTotal += t;
                rows += `
                  <tr class="font-siliguri">
                    <td class="excel-row-num">${idx + 1}</td>
                    <td class="font-bold text-slate-800 text-xs text-left">${item.sr_name}</td>
                    <td class="excel-money ${t >= 0 ? 'text-emerald-600' : 'text-rose-600'} font-bold">
                      ${t >= 0 ? '+' : ''}৳ ${Math.round(t).toLocaleString('en-US')}
                    </td>
                  </tr>`;
            });

            body.innerHTML = `
              <div class="excel-container shadow-2xs border border-slate-300">
                <table class="excel-table text-xs">
                  <thead>
                    <tr>
                      <th class="excel-row-num">#</th>
                      <th>SR নাম</th>
                      <th class="text-right">O/C মোট</th>
                    </tr>
                  </thead>
                  <tbody>${rows}</tbody>
                </table>
              </div>`;

            total.textContent = (grandTotal >= 0 ? '+' : '') + '৳ ' + Math.round(grandTotal).toLocaleString('en-US');
        })
        .catch(() => { body.innerHTML = '<p class="text-center text-rose-500 text-xs py-6 font-siliguri">নেটওয়ার্ক সমস্যা হয়েছে।</p>'; });
}

function closeOcModal() {
    document.getElementById('ocModal').classList.add('hidden');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', e => { 
    if (e.key === 'Escape') {
        closeReturnModal();
        closeOcModal();
    }
});
</script>
