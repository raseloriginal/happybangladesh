<?php 
$pageTitle = 'Settlement'; 
$isSubmitted = !empty($existingSettlement);
$savedDamage = $isSubmitted ? $existingSettlement['total_damage'] : 0;
$savedExpense = $isSubmitted ? $existingSettlement['total_expense'] : 0;
$cashBreakdown = $isSubmitted && !empty($existingSettlement['cash_breakdown']) ? json_decode($existingSettlement['cash_breakdown'], true) : [];
$savedNote = $cashBreakdown['note'] ?? '';

if ($isSubmitted) {
    // Override with submitted values
    $dispatchedValue = $existingSettlement['total_dispatched'];
    $returnedValue = $existingSettlement['total_returned'];
}

$isNoDispatch = ($dispatchedValue <= 0);
$isLocked = $isSubmitted || $isNoDispatch;
$readonlyAttr = $isLocked ? 'readonly' : '';
?>
<div class="h-full flex flex-col bg-gray-50 pb-28 overflow-y-auto">
  
  <div class="bg-brand pt-10 pb-20 px-4 text-white relative rounded-b-[40px] shadow-lg">
    <div class="flex items-center gap-3 mb-6 relative z-10">
      <a href="<?= url('dsr/dashboard') ?>" class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-md active:bg-white/30 transition">
        <i class="fa-solid fa-arrow-left"></i>
      </a>
      <h1 class="text-xl font-bold">Daily Settlement</h1>
    </div>
  </div>

  <div class="px-4 -mt-14 relative z-20">
    
    <form action="<?= url('dsr/settlement/submit') ?>" method="POST" id="settlementForm">
      <?= Helpers::csrfField() ?>
      <input type="hidden" name="dispatched_value" value="<?= $dispatchedValue ?>">
      <input type="hidden" name="returned_value" value="<?= $returnedValue ?>">
      <input type="hidden" name="should_pay" id="formShouldPay" value="0">
      <input type="hidden" name="counted_cash" id="formCountedCash" value="0">
      <input type="hidden" name="difference" id="formDifference" value="0">
      <input type="hidden" name="cash_breakdown" id="formCashBreakdown" value="{}">
      <input type="hidden" name="settlement_date" value="<?= htmlspecialchars($selectedDate) ?>">

      <!-- Modern Date Selector -->
      <div class="mb-5">
        <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-hide snap-x" style="-webkit-overflow-scrolling: touch; scrollbar-width: none;">
          <?php 
            // Show last 7 days
            for($i = 6; $i >= 0; $i--):
                $d = date('Y-m-d', strtotime("-$i days"));
                $dayName = date('D', strtotime($d));
                $dayNum = date('d', strtotime($d));
                $isSelected = ($d === $selectedDate);
                $bgClass = $isSelected ? 'bg-brand text-white shadow-lg ring-2 ring-brand ring-offset-2 ring-offset-gray-50' : 'bg-white border border-gray-100 shadow-sm';
                $textClass = $isSelected ? 'text-white' : 'text-gray-800';
          ?>
          <a href="?date=<?= $d ?>" class="snap-start flex-shrink-0 w-[4.5rem] h-20 flex flex-col items-center justify-center rounded-3xl transition-all duration-200 active:scale-95 <?= $bgClass ?>">
            <span class="text-[11px] font-bold uppercase mb-1 tracking-wider <?= $isSelected ? 'text-white/80' : 'text-gray-400' ?>"><?= $dayName ?></span>
            <span class="text-2xl font-black <?= $textClass ?>"><?= $dayNum ?></span>
          </a>
          <?php endfor; ?>
        </div>
      </div>

      <?php if ($isSubmitted): ?>
      <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 mb-6 flex items-center gap-3">
          <div class="w-8 h-8 rounded-full bg-blue-200 text-blue-700 flex items-center justify-center shrink-0">
              <i class="fa-solid fa-lock"></i>
          </div>
          <div>
              <h4 class="text-sm font-bold text-blue-900">Settlement Submitted</h4>
              <p class="text-xs text-blue-700">This settlement has been submitted and cannot be edited.</p>
          </div>
      </div>
      <?php elseif ($isNoDispatch): ?>
      <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 mb-6 flex items-center gap-3">
          <div class="w-8 h-8 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center shrink-0">
              <i class="fa-solid fa-box-open"></i>
          </div>
          <div>
              <h4 class="text-sm font-bold text-gray-700">No Dispatches</h4>
              <p class="text-xs text-gray-500">There are no dispatched products for this date. Settlement is not required.</p>
          </div>
      </div>
      <?php endif; ?>

      <!-- Summary Card -->
      <div class="bg-white rounded-3xl p-5 shadow-xl mb-6 border border-gray-100">
        <div class="flex justify-between items-center mb-4">
          <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Account Summary</span>
          <span class="text-[10px] bg-blue-50 text-blue-600 px-2.5 py-1 rounded-md font-bold"><?= date('d M Y', strtotime($selectedDate)) ?></span>
        </div>
        
        <div class="space-y-3 border-b border-gray-100 pb-4 mb-4">
          <div class="flex justify-between items-center">
            <span class="text-sm font-semibold text-gray-600">Dispatched Value</span>
            <span class="text-sm font-bold text-gray-800">৳<?= number_format($dispatchedValue, 2) ?></span>
          </div>
          <div class="flex justify-between items-center text-red-500">
            <span class="text-sm font-semibold">Returned Value (-)</span>
            <span class="text-sm font-bold">৳<?= number_format($returnedValue, 2) ?></span>
          </div>
        </div>

        <div class="space-y-3 mb-4">
          <div class="flex items-center justify-between">
            <label class="text-sm font-semibold text-gray-600">Damage Amount (-)</label>
            <div class="relative w-32">
              <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 font-bold">৳</span>
              <input type="number" name="damage_amount" id="inputDamage" value="<?= $savedDamage ?>" min="0" step="0.01" class="w-full bg-gray-50 border border-gray-200 rounded-xl py-2 pl-7 pr-3 text-right font-bold text-gray-800 outline-none focus:ring-2 focus:ring-brand <?= $isLocked ? 'opacity-70' : '' ?>" oninput="calculate()" <?= $readonlyAttr ?>>
            </div>
          </div>
          <div class="flex items-center justify-between">
            <label class="text-sm font-semibold text-gray-600">Total Expenses (-)</label>
            <div class="relative w-32">
              <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 font-bold">৳</span>
              <input type="number" name="total_expense" id="inputExpense" value="<?= $savedExpense ?>" min="0" step="0.01" class="w-full bg-gray-50 border border-gray-200 rounded-xl py-2 pl-7 pr-3 text-right font-bold text-gray-800 outline-none focus:ring-2 focus:ring-brand <?= $isLocked ? 'opacity-70' : '' ?>" oninput="calculate()" <?= $readonlyAttr ?>>
            </div>
          </div>
        </div>

        <div class="bg-blue-50 rounded-2xl p-4 flex justify-between items-center">
          <span class="font-bold text-blue-800">Should Pay</span>
          <span class="text-xl font-black text-brand" id="displayShouldPay">৳0.00</span>
        </div>
      </div>

      <!-- Cash Counting -->
      <div class="bg-white rounded-3xl p-5 shadow-xl mb-6 border border-gray-100">
        <h3 class="text-sm font-bold text-gray-800 mb-4">Cash Counting</h3>
        
        <div class="grid grid-cols-2 gap-3 mb-6">
          <?php 
          $denominations = [1000, 500, 200, 100, 50, 20, 10, 5, 2, 1];
          foreach($denominations as $d): 
              $qty = $cashBreakdown[$d] ?? '';
          ?>
          <div class="flex items-center gap-2 bg-gray-50 p-2 rounded-xl border border-gray-100">
            <div class="w-10 text-center font-bold text-brand text-xs">৳<?= $d ?></div>
            <div class="text-gray-400 text-xs">×</div>
            <input type="number" min="0" class="flex-1 w-full bg-white border border-gray-200 rounded-lg py-1.5 px-2 text-center font-bold text-gray-800 outline-none focus:ring-2 focus:ring-brand denomination-input <?= $isLocked ? 'opacity-70' : '' ?>" data-val="<?= $d ?>" value="<?= $qty ?>" oninput="calculate()" <?= $readonlyAttr ?>>
          </div>
          <?php endforeach; ?>
        </div>
        
        <div class="bg-emerald-50 rounded-2xl p-4 flex justify-between items-center mb-4">
          <span class="font-bold text-emerald-800">Counted Cash</span>
          <span class="text-xl font-black text-emerald-600" id="displayCountedCash">৳0.00</span>
        </div>

        <div class="flex justify-between items-center py-2 border-t border-gray-100">
          <span class="text-sm font-semibold text-gray-600">Difference</span>
          <span class="text-lg font-black text-gray-800" id="displayDifference">৳0.00</span>
        </div>
      </div>

      <!-- Note Section -->
      <div class="bg-white rounded-3xl p-5 shadow-xl mb-6 border border-gray-100">
        <h3 class="text-sm font-bold text-gray-800 mb-2">Note (Optional)</h3>
        <textarea name="note" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm text-gray-800 outline-none focus:ring-2 focus:ring-brand <?= $isLocked ? 'opacity-70' : '' ?>" rows="2" placeholder="Add any comments or explanations here..." <?= $readonlyAttr ?>><?= htmlspecialchars($savedNote) ?></textarea>
      </div>

      <!-- Status Panel -->
      <div class="bg-white rounded-3xl p-5 shadow-xl mb-6 border border-gray-100 flex items-center justify-between">
        <span class="font-bold text-gray-700">Settlement Status</span>
        <div id="statusBadge" class="px-4 py-1.5 rounded-full text-xs font-bold bg-gray-100 text-gray-500">PENDING</div>
      </div>

      <?php if (!$isLocked): ?>
      <div class="fixed bottom-16 left-0 w-full p-4 bg-white/90 backdrop-blur-md border-t border-gray-100 z-30 pb-[calc(1rem+env(safe-area-inset-bottom))]">
        <button type="submit" class="w-full py-4 rounded-2xl font-bold text-white bg-brand shadow-[0_8px_20px_rgba(37,99,235,0.3)] active:scale-[0.98] transition-transform">
          Submit Settlement
        </button>
      </div>
      <?php endif; ?>
    </form>
    
  </div>
</div>

<script>
const dispatched = <?= (float)$dispatchedValue ?>;
const returned = <?= (float)$returnedValue ?>;

function calculate() {
    const damage = parseFloat(document.getElementById('inputDamage').value) || 0;
    const expense = parseFloat(document.getElementById('inputExpense').value) || 0;
    
    // Should Pay
    const shouldPay = dispatched - returned - damage - expense;
    document.getElementById('displayShouldPay').innerText = '৳' + shouldPay.toFixed(2);
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

    document.getElementById('displayCountedCash').innerText = '৳' + countedCash.toFixed(2);
    document.getElementById('formCountedCash').value = countedCash.toFixed(2);
    document.getElementById('formCashBreakdown').value = JSON.stringify(cashBreakdown);

    // Difference
    const difference = countedCash - shouldPay;
    const diffDisplay = document.getElementById('displayDifference');
    diffDisplay.innerText = (difference > 0 ? '+' : '') + '৳' + difference.toFixed(2);
    document.getElementById('formDifference').value = difference.toFixed(2);

    // Status
    const badge = document.getElementById('statusBadge');
    if(shouldPay === 0 && countedCash === 0) {
        badge.className = 'px-4 py-1.5 rounded-full text-xs font-bold bg-gray-100 text-gray-500';
        badge.innerText = 'PENDING';
        diffDisplay.className = 'text-lg font-black text-gray-800';
    }
    else if(Math.abs(difference) < 0.01) {
        badge.className = 'px-4 py-1.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700';
        badge.innerText = 'PERFECT';
        diffDisplay.className = 'text-lg font-black text-blue-600';
    } else if(difference < 0) {
        badge.className = 'px-4 py-1.5 rounded-full text-xs font-bold bg-red-100 text-red-700';
        badge.innerText = 'SHORT';
        diffDisplay.className = 'text-lg font-black text-red-600';
    } else {
        badge.className = 'px-4 py-1.5 rounded-full text-xs font-bold bg-green-100 text-green-700';
        badge.innerText = 'OVER';
        diffDisplay.className = 'text-lg font-black text-green-600';
    }
}

// Initial calculation
calculate();
</script>
