<?php $pageTitle = 'Delivery'; ?>

<?php
// Only retailers whose products are physically on the van today
$retailers = $orderedRetailers ?? [];
$hasDeliveries = !empty($retailers);
?>

<div class="h-full flex flex-col relative bg-gray-100">

  <!-- ══════════════════════════════════════════════════════
       EMPTY STATE — No dispatches loaded on van yet
  ═══════════════════════════════════════════════════════ -->
  <?php if (!$hasDeliveries): ?>
  <div class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-white px-8 text-center">
    
    <!-- Empty State Date Picker -->
    <div class="absolute top-10 left-1/2 -translate-x-1/2 flex items-center gap-2 bg-gray-50 border border-gray-200 px-4 py-2 rounded-full shadow-sm z-30">
        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Date</span>
        <input type="date" value="<?= $selectedDate ?? date('Y-m-d') ?>" class="bg-transparent border-none text-brand text-sm font-black outline-none cursor-pointer" onchange="window.location.href='<?= url('dsr/delivery') ?>?date='+this.value">
    </div>

    <div class="w-24 h-24 bg-blue-50 rounded-full flex items-center justify-center mb-6 mt-12">
      <i class="fa-solid fa-truck text-4xl text-blue-300"></i>
    </div>
    <h2 class="text-xl font-black text-gray-800 mb-2">Van is Empty</h2>
    <?php if (isset($isCompleted) && $isCompleted): ?>
      <p class="text-sm text-gray-500 leading-relaxed mb-6">
        No retailer deliveries are assigned to your route today.<br>
        You can proceed with Ready Sales if you have van stock.
      </p>
    <?php else: ?>
      <p class="text-sm text-gray-500 leading-relaxed mb-6">
        No deliveries are loaded on your van today.<br>
        Please wait for your manager to complete the dispatch.
      </p>
      <a href="<?= url('dsr/van-stock') ?>" class="inline-flex items-center gap-2 px-6 py-3 bg-brand text-white font-bold rounded-2xl shadow-lg shadow-blue-500/30 active:scale-95 transition">
        <i class="fa-solid fa-boxes-stacked"></i> Go to Inventory
      </a>
    <?php endif; ?>
    <a href="<?= url('dsr/dashboard') ?>" class="mt-3 text-sm text-gray-400 font-medium">← Back to Dashboard</a>
  </div>
  <?php endif; ?>

  <!-- ══════════════════════════════════════════════════════
       MAP — shown only when there are deliveries
  ═══════════════════════════════════════════════════════ -->
  <div id="dsrMap" class="absolute inset-0 z-0 <?= !$hasDeliveries ? 'hidden' : '' ?>"></div>

  <?php if ($hasDeliveries): ?>

  <!-- Top Overlay -->
  <div class="absolute top-0 left-0 w-full z-10 px-4 pt-10 pb-2 bg-gradient-to-b from-black/60 to-transparent pointer-events-none">
    <div class="flex items-center gap-3 pointer-events-auto">
      <a href="<?= url('dsr/dashboard') ?>" class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-gray-800 shadow-md">
        <i class="fa-solid fa-arrow-left"></i>
      </a>
      <div class="flex-1">
        <div class="text-white text-xs font-semibold opacity-80 flex items-center gap-2">
            Deliveries for: 
            <input type="date" value="<?= $selectedDate ?? date('Y-m-d') ?>" class="bg-white/20 border-b border-white text-white text-xs outline-none px-1 py-0.5 rounded" onchange="window.location.href='<?= url('dsr/delivery') ?>?date='+this.value">
        </div>
        <div class="text-white text-lg font-black leading-tight"><?= count($retailers) ?> Retailer<?= count($retailers) !== 1 ? 's' : '' ?> on Van</div>
      </div>
      <button onclick="openRetailerListModal()" class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-gray-800 shadow-md active:scale-95 transition" style="margin-right: -4px;">
        <i class="fa-solid fa-list-ul"></i>
      </button>
      <button onclick="locateMe()" class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-blue-600 shadow-md active:scale-95 transition">
        <i class="fa-solid fa-location-crosshairs"></i>
      </button>
    </div>
  </div>

  <!-- ══════════════════════════════════════════════════════
       BOTTOM — Retailer List Panel + Sheet Overlay
  ═══════════════════════════════════════════════════════ -->

  <!-- Sheet Overlay (dim background) -->
  <div id="bottomSheetOverlay" class="bottom-sheet-overlay" onclick="closeBottomSheet()"></div>

  <!-- No retailerListPanel, map is full screen -->

  <!-- ══════════════════════════════════════════════════════
       BOTTOM SHEET — Retailer Delivery Detail
  ═══════════════════════════════════════════════════════ -->
  <div id="retailerSheet" class="bottom-sheet pb-[env(safe-area-inset-bottom)]">
    <div class="bottom-sheet-handle"></div>
    <div class="bottom-sheet-content no-scrollbar">

      <!-- Sheet Header -->
      <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
        <button onclick="closeBottomSheet()" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-gray-800">
          <i class="fa-solid fa-chevron-left text-lg"></i>
        </button>
        <span class="text-base font-black text-gray-800">Order Details</span>
        <button class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-gray-800">
          <i class="fa-solid fa-list-ul text-lg"></i>
        </button>
      </div>

      <!-- Retailer Info -->
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
          <img id="bsRetailerAvatar" src="" class="w-12 h-12 rounded-full object-cover border border-gray-100 shadow-sm" onerror="this.src='https://i.pravatar.cc/100?img=12'">
          <div>
            <h2 class="text-base font-black text-gray-800 leading-tight" id="bsRetailerName">Retailer Name</h2>
            <p class="text-xs text-gray-400 font-bold mt-0.5" id="bsRetailerSub">Address details</p>
          </div>
        </div>
        <!-- Action Buttons on Right -->
        <div class="flex gap-2">
          <a href="#" class="w-8 h-8 bg-green-50 text-green-500 rounded-full flex items-center justify-center text-sm shadow-sm active:scale-95 transition">
            <i class="fa-solid fa-circle-plus text-lg"></i>
          </a>
          <button onclick="openDamageModal()" id="damageBtn" class="w-8 h-8 bg-red-50 text-red-400 rounded-full flex items-center justify-center text-sm shadow-sm active:scale-95 transition" title="Report Damage">
            <i class="fa-solid fa-ban text-sm"></i>
          </button>
        </div>
      </div>

      <!-- Stats / Summary -->
      <div class="flex justify-between items-center mb-4">
        <div class="text-sm font-bold text-gray-800 flex items-center gap-1">
          অর্ডার সমূহ <span class="text-emerald-500 font-black text-base ml-1" id="bsTotalQty">50</span>
        </div>
        <div class="border border-blue-400 text-blue-500 bg-blue-50/10 font-bold px-4 py-1 rounded-full text-xs" id="bsOrderTotal">
          Tk 50.00
        </div>
      </div>

      <!-- Hidden elements to preserve JS bindings -->
      <div class="hidden">
        <div id="bsRetailerAddress"></div>
        <span id="bsGettingTotal">৳0</span>
        <span id="bsStatus">Pending</span>
        <div id="bsPartialInfo">
          <span id="bsPaidAmount">৳0.00</span>
          <span id="bsDueAmount">৳0.00</span>
        </div>
      </div>

      <!-- Company Tabs Container -->
      <div id="bsCompanyTabs" class="flex gap-2 mb-4 overflow-x-auto pb-1 no-scrollbar hidden">
          <!-- JS will populate company tabs here -->
      </div>

      <!-- Products List -->
      <div class="mb-4">
        <div id="bsProductsList" class="space-y-3 max-h-[45vh] overflow-y-auto pr-1 no-scrollbar">
          <!-- JS will populate this -->
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex gap-4 mt-4 pt-3 border-t border-gray-100" id="bsActionButtons">
        <button onclick="markDelivery('cancelled')" class="flex-1 py-3 rounded-full font-bold bg-[#ff3b30] text-white active:scale-[0.98] transition text-sm shadow-md">Cancel</button>
        <button onclick="markDelivery('delivered')" class="flex-1 py-3 rounded-full font-bold bg-[#007aff] text-white active:scale-[0.98] transition text-sm shadow-md">Paid</button>
      </div>

    </div>
  </div>

  <?php endif; // $hasDeliveries ?>

  <!-- ══════════════════════════════════════════════════════
       CUSTOM MODALS
  ═══════════════════════════════════════════════════════ -->

  <!-- Retailer List Modal -->
  <div id="retailerListModal" class="fixed inset-0 z-[500] hidden flex flex-col bg-gray-100 transition-opacity">
      <!-- Header -->
      <div class="bg-white px-5 pt-10 pb-4 shadow-sm flex items-center gap-4 sticky top-0 z-10">
          <button onclick="closeRetailerListModal()" class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center text-gray-600 active:scale-95 transition">
              <i class="fa-solid fa-arrow-left"></i>
          </button>
          <div class="flex-1">
              <h2 class="text-lg font-black text-gray-800">Retailers on Van</h2>
              <div class="text-xs text-gray-500 font-semibold"><?= count($retailers) ?> Retailers</div>
          </div>
      </div>
      <!-- Body -->
      <div class="flex-1 overflow-y-auto px-4 py-4 grid grid-cols-2 gap-3 pb-20 content-start">
          <?php foreach ($retailers as $idx => $r): 
              $hasDelivered = false;
              $hasPending = false;
              $hasPartial = false;
              $hasCancelled = false;
              foreach ($r['orders'] as $o) {
                  if ($o['status'] === 'in_transit') $hasPending = true;
                  if ($o['status'] === 'partial') $hasPartial = true;
                  if ($o['status'] === 'delivered') $hasDelivered = true;
                  if ($o['status'] === 'cancelled') $hasCancelled = true;
              }
              
              $statusBadge = '<span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-100 text-blue-700">Pending</span>';
              if ($hasPending) {
                  $statusBadge = '<span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-100 text-blue-700"><i class="fa-regular fa-clock mr-1"></i>Pending</span>';
              } elseif ($hasPartial) {
                  $statusBadge = '<span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-yellow-100 text-yellow-700"><i class="fa-solid fa-circle-half-stroke mr-1"></i>Partial</span>';
              } elseif ($hasDelivered && $hasCancelled) {
                  $statusBadge = '<span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-100 text-purple-700"><i class="fa-solid fa-shuffle mr-1"></i>Mixed</span>';
              } elseif ($hasCancelled) {
                  $statusBadge = '<span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-red-100 text-red-700"><i class="fa-solid fa-xmark mr-1"></i>Cancelled</span>';
              } elseif ($hasDelivered) {
                  $statusBadge = '<span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-green-100 text-green-700"><i class="fa-solid fa-check mr-1"></i>Delivered</span>';
              }
          ?>
            <div class="bg-white rounded-2xl p-3 shadow-sm active:scale-[0.98] transition cursor-pointer border border-gray-100 flex flex-col h-full" onclick="handleRetailerListClick(<?= $idx ?>)">
                <div class="mb-2">
                    <?= $statusBadge ?>
                </div>
                <div class="text-sm font-black text-gray-800 leading-tight mb-1 line-clamp-2"><?= h($r['retailer_name'] ?? $r['dealer_name'] ?? 'Unknown Retailer') ?></div>
                <div class="text-[10px] text-gray-400 line-clamp-2 mb-auto leading-tight"><i class="fa-solid fa-location-dot mr-1 text-gray-300"></i><?= h($r['address'] ?? 'No Address') ?></div>
                
                <div class="flex justify-between items-end mt-2 pt-2 border-t border-gray-50">
                    <?php if (count($r['orders']) > 1): ?>
                        <div class="text-[9px] font-bold text-brand bg-blue-50 px-1.5 py-0.5 rounded"><?= count($r['orders']) ?> Orders</div>
                    <?php else: ?>
                        <div></div>
                    <?php endif; ?>
                    <div class="w-6 h-6 rounded-full bg-gray-50 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-chevron-right text-gray-400 text-[10px]"></i>
                    </div>
                </div>
            </div>
          <?php endforeach; ?>
      </div>
  </div>

  <!-- Confirm Modal -->
  <div id="customConfirmModal" class="fixed inset-0 z-[200] hidden flex items-center justify-center p-4 bg-black/50 transition-opacity">
      <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-2xl transform transition-transform scale-95 opacity-0 duration-200" id="customConfirmContent">
          <div class="text-center">
              <div class="w-16 h-16 bg-orange-100 text-orange-500 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                  <i class="fa-solid fa-circle-question"></i>
              </div>
              <h3 class="text-lg font-black text-gray-800 mb-2">Confirmation</h3>
              <p class="text-sm text-gray-500 mb-6" id="confirmMessage">Are you sure?</p>
              <div class="flex gap-3">
                  <button id="confirmCancelBtn" class="flex-1 py-3 bg-gray-100 text-gray-600 font-bold rounded-xl active:bg-gray-200 transition">Cancel</button>
                  <button id="confirmOkBtn" class="flex-1 py-3 bg-brand text-white font-bold rounded-xl active:scale-[0.98] shadow-lg shadow-blue-500/30 transition">Yes, Proceed</button>
              </div>
          </div>
      </div>
  </div>

  <!-- Single Cancel Reason Modal -->
  <div id="singleCancelModal" class="fixed inset-0 z-[200] hidden flex items-center justify-center p-4 bg-black/50 transition-opacity">
      <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-2xl transform transition-transform scale-95 opacity-0 duration-200" id="singleCancelContent">
          <div class="text-center">
              <div class="w-16 h-16 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                  <i class="fa-solid fa-circle-xmark"></i>
              </div>
              <h3 class="text-lg font-black text-gray-800 mb-2">Cancel Order</h3>
              <p class="text-sm text-gray-500 mb-4">Please select a reason for cancellation:</p>
              
              <select id="cancelReasonSelect" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20 mb-6 transition font-semibold text-gray-700">
                  <option value="Retailer Refused">Retailer Refused</option>
                  <option value="Shop Closed">Shop Closed</option>
                  <option value="Out of Stock / Mismatch">Out of Stock / Mismatch</option>
                  <option value="Payment Issue">Payment Issue</option>
                  <option value="Price Mismatch">Price Mismatch</option>
                  <option value="Other">Other</option>
              </select>
              
              <div class="flex gap-3">
                  <button onclick="closeSingleCancelModal()" class="flex-1 py-3 bg-gray-100 text-gray-600 font-bold rounded-xl active:bg-gray-200 transition">Cancel</button>
                  <button onclick="submitSingleCancel()" class="flex-1 py-3 bg-red-600 text-white font-bold rounded-xl active:scale-[0.98] shadow-lg shadow-red-500/30 transition">Submit</button>
              </div>
          </div>
      </div>
  </div>

  <!-- Paid Payment Modal -->
  <div id="paidPaymentModal" class="fixed inset-0 z-[200] hidden flex items-center justify-center p-4 bg-black/50 transition-opacity">
      <div class="bg-white rounded-3xl p-6 w-full max-w-sm shadow-2xl transform transition-transform scale-95 opacity-0 duration-200" id="paidPaymentContent">
          <div class="text-center">
              <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                  <i class="fa-solid fa-hand-holding-dollar"></i>
              </div>
              <h3 class="text-lg font-black text-gray-800 mb-1">Receipt Amount</h3>
              
              <!-- Info block showing due status -->
              <div id="paymentDueInfo" class="text-sm font-semibold text-gray-500 mb-4 h-5">
                  Paid in Full
              </div>
              
              <div class="mb-6">
                  <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider text-left mb-1.5">Amount Paid (৳)</label>
                  <input type="number" id="paidPaymentInput" oninput="onPaidPaymentInput(this)" class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-4 py-3 text-center text-2xl font-black text-gray-800 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition">
              </div>
              
              <div class="flex gap-3">
                  <button onclick="closePaidPaymentModal()" class="flex-1 py-3 bg-gray-100 text-gray-600 font-bold rounded-xl active:bg-gray-200 transition">Cancel</button>
                  <button onclick="submitPaidPayment()" class="flex-1 py-3 bg-[#007aff] text-white font-bold rounded-xl active:scale-[0.98] shadow-lg shadow-blue-500/20 transition">Submit</button>
              </div>
          </div>
      </div>
  </div>

  <!-- Prompt Modal -->
  <div id="customPromptModal" class="fixed inset-0 z-[200] hidden flex items-center justify-center p-4 bg-black/50 transition-opacity">
      <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-2xl transform transition-transform scale-95 opacity-0 duration-200" id="customPromptContent">
          <div class="text-center">
              <div class="w-16 h-16 bg-blue-100 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                  <i class="fa-solid fa-hand-holding-dollar"></i>
              </div>
              <h3 class="text-lg font-black text-gray-800 mb-2">Partial Payment</h3>
              <p class="text-sm text-gray-500 mb-4" id="promptMessage">Enter the amount the retailer has paid:</p>
              <input type="number" id="promptInput" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-center text-lg font-bold text-gray-700 outline-none focus:border-brand focus:ring-2 focus:ring-brand/20 mb-6 transition" placeholder="৳0.00">
              <div class="flex gap-3">
                  <button id="promptCancelBtn" class="flex-1 py-3 bg-gray-100 text-gray-600 font-bold rounded-xl active:bg-gray-200 transition">Cancel</button>
                  <button id="promptOkBtn" class="flex-1 py-3 bg-brand text-white font-bold rounded-xl active:scale-[0.98] shadow-lg shadow-blue-500/30 transition">Submit</button>
              </div>
          </div>
      </div>
  </div>

  <!-- Cancel Multi-Order Modal -->
  <div id="customCancelModal" class="fixed inset-0 z-[200] hidden flex items-center justify-center p-4 bg-black/50 transition-opacity">
      <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-2xl transform transition-transform scale-95 opacity-0 duration-200" id="customCancelContent">
          <div class="text-center">
              <div class="w-16 h-16 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                  <i class="fa-solid fa-circle-xmark"></i>
              </div>
              <h3 class="text-lg font-black text-gray-800 mb-2">Cancel Company Orders</h3>
              <p class="text-sm text-gray-500 mb-4">Select which company orders you want to cancel:</p>
              
              <div id="cancelCheckboxesContainer" class="text-left space-y-2 mb-6 max-h-[20vh] overflow-y-auto px-2">
                  <!-- Dynamic check lists -->
              </div>
              
              <div class="flex gap-3">
                  <button id="cancelModalCloseBtn" class="flex-1 py-3 bg-gray-100 text-gray-600 font-bold rounded-xl active:bg-gray-200 transition">Cancel</button>
                  <button id="cancelModalOkBtn" class="flex-1 py-3 bg-red-600 text-white font-bold rounded-xl active:scale-[0.98] shadow-lg shadow-red-500/30 transition">Confirm Cancel</button>
              </div>
          </div>
      </div>
  </div>

  <!-- Complete Multi-Order Modal -->
  <div id="customCompleteModal" class="fixed inset-0 z-[200] hidden flex items-center justify-center p-4 bg-black/50 transition-opacity">
      <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-2xl transform transition-transform scale-95 opacity-0 duration-200" id="customCompleteContent">
          <div class="text-center">
              <div class="w-16 h-16 bg-green-100 text-green-500 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                  <i class="fa-solid fa-circle-check"></i>
              </div>
              <h3 class="text-lg font-black text-gray-800 mb-2">Complete Company Orders</h3>
              <p class="text-sm text-gray-500 mb-4">Select which company orders you want to complete:</p>
              
              <div id="completeCheckboxesContainer" class="text-left space-y-2 mb-6 max-h-[20vh] overflow-y-auto px-2">
                  <!-- Dynamic check lists -->
              </div>
              
              <div class="flex gap-3">
                  <button id="completeModalCloseBtn" class="flex-1 py-3 bg-gray-100 text-gray-600 font-bold rounded-xl active:bg-gray-200 transition">Cancel</button>
                  <button id="completeModalOkBtn" class="flex-1 py-3 bg-brand text-white font-bold rounded-xl active:scale-[0.98] shadow-lg shadow-blue-500/30 transition">Confirm Complete</button>
              </div>
          </div>
      </div>
  </div>

  <!-- Partial Multi-Order Modal -->
  <div id="customPartialModal" class="fixed inset-0 z-[200] hidden flex items-center justify-center p-4 bg-black/50 transition-opacity">
      <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-2xl transform transition-transform scale-95 opacity-0 duration-200" id="customPartialContent">
          <div class="text-center">
              <div class="w-16 h-16 bg-orange-100 text-orange-500 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                  <i class="fa-solid fa-circle-half-stroke"></i>
              </div>
              <h3 class="text-lg font-black text-gray-800 mb-2">Partial/Due Delivery</h3>
              <p class="text-sm text-gray-500 mb-4">Select which company orders are partial and input paid amount:</p>
              
              <div id="partialInputsContainer" class="text-left space-y-3 mb-6 max-h-[25vh] overflow-y-auto px-2">
                  <!-- Dynamic check lists with inputs -->
              </div>
              
              <div class="flex gap-3">
                  <button id="partialModalCloseBtn" class="flex-1 py-3 bg-gray-100 text-gray-600 font-bold rounded-xl active:bg-gray-200 transition">Cancel</button>
                  <button id="partialModalOkBtn" class="flex-1 py-3 bg-orange-500 text-white font-bold rounded-xl active:scale-[0.98] shadow-lg shadow-orange-500/30 transition">Confirm Partial</button>
              </div>
          </div>
      </div>
  </div>

  <!-- Partial Due Options Modal -->
  <div id="partialDueModal" class="fixed inset-0 z-[200] hidden flex items-center justify-center p-4 bg-black/50 transition-opacity">
      <div class="bg-white rounded-3xl p-6 w-full max-w-sm shadow-2xl transform transition-transform scale-95 opacity-0 duration-200" id="partialDueContent">
          <div class="text-center">
              <div class="w-16 h-16 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                  <i class="fa-solid fa-circle-exclamation"></i>
              </div>
              <h3 class="text-lg font-black text-gray-800 mb-1" id="partialDueTitle">Due Payment</h3>
              <p class="text-sm text-gray-500 mb-6" id="partialDueMessage">Remaining Due: ৳0.00</p>
              
              <div class="flex flex-col gap-3">
                  <button onclick="handleDuePaymentAction()" class="w-full py-3 bg-brand text-white font-bold rounded-xl active:scale-[0.98] shadow-lg shadow-blue-500/20 transition">Due Payment</button>
                  <button onclick="handleDueCancelAction()" class="w-full py-3 bg-red-600 text-white font-bold rounded-xl active:scale-[0.98] shadow-lg shadow-red-500/20 transition">Cancel Order</button>
                  <button onclick="handleDueDetailsAction()" class="w-full py-3 bg-gray-100 text-gray-600 font-bold rounded-xl active:bg-gray-200 transition">View Details</button>
              </div>
          </div>
      </div>
  </div>

  <!-- ══════════════════════════════════════════════════════
       DAMAGE MODAL
  ═══════════════════════════════════════════════════════ -->
  <div id="damageModal" class="fixed inset-0 z-[300] hidden flex items-end justify-center bg-black/50 transition-opacity">
    <div class="bg-white rounded-t-3xl w-full max-w-[480px] shadow-2xl transform transition-transform translate-y-full duration-300" id="damageModalContent">
      <!-- Handle -->
      <div class="flex justify-center pt-3 pb-1">
        <div class="w-10 h-1 bg-gray-200 rounded-full"></div>
      </div>
      <!-- Header -->
      <div class="flex items-center justify-between px-5 pt-2 pb-4 border-b border-gray-100">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 bg-red-100 rounded-full flex items-center justify-center">
            <i class="fa-solid fa-ban text-red-500"></i>
          </div>
          <div>
            <div class="text-base font-black text-gray-800">Report Damage</div>
            <div class="text-xs text-gray-400 font-medium" id="dmgRetailerLabel">Select retailer damage</div>
          </div>
        </div>
        <button onclick="closeDamageModal()" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-700">
          <i class="fa-solid fa-xmark text-lg"></i>
        </button>
      </div>
      <!-- Body -->
      <div class="px-5 pt-4 pb-2 max-h-[55vh] overflow-y-auto">
        <!-- Product selection trigger -->
        <button type="button" onclick="openDamageProductSelection()" class="w-full mb-4 py-2.5 px-4 rounded-xl border border-dashed border-red-300 hover:border-red-500 text-red-600 font-bold text-xs flex items-center justify-center gap-2 bg-red-50/20 transition active:scale-[0.98]">
          <i class="fa-solid fa-circle-plus"></i> Select Products
        </button>

        <!-- Selected products for inputting quantity -->
        <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Damaged Products List</div>
        <div id="dmgProductList" class="space-y-2 mb-4">
          <!-- Populated by JS -->
        </div>

        <!-- Total Delivery Value (Current Tab) -->
        <div class="flex justify-between items-center mb-2">
            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Delivered Value</div>
            <div id="dmgDeliveredValue" class="text-sm font-black text-blue-600">৳0.00</div>
        </div>

        <!-- Total Damage Amount -->
        <div class="flex justify-between items-center mb-2">
            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Damage Amount</div>
        </div>
        <input type="number" id="dmgTotalAmount" min="0" step="0.01" placeholder="0.00" oninput="onManualDamageAmountChange()"
          class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-4 py-3 text-center text-xl font-black text-red-500 outline-none focus:border-red-400 focus:ring-2 focus:ring-red-400/20 transition mb-4">

        <!-- Net Payable -->
        <div class="flex justify-between items-center mb-2">
            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Net Payable</div>
            <div id="dmgNetPayable" class="text-lg font-black text-gray-800">৳0.00</div>
        </div>

        <!-- Receipt Amount -->
        <div class="flex justify-between items-center mb-2 mt-4 border-t border-gray-100 pt-4">
            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Receipt Amount (৳)</div>
        </div>
        <input type="number" id="dmgReceiptAmount" placeholder="Enter amount..."
          class="w-full bg-white border border-gray-200 rounded-2xl px-4 py-3 text-center text-xl font-black text-gray-800 outline-none focus:border-brand focus:ring-2 focus:ring-brand/20 transition mb-4">
      </div>
      <!-- Footer -->
      <div class="px-5 pb-6 pt-3 border-t border-gray-100 flex gap-3">
        <button onclick="closeDamageModal()" class="flex-1 py-3 bg-gray-100 text-gray-600 font-bold rounded-2xl active:bg-gray-200 transition">Cancel</button>
        <button onclick="submitDamage()" class="flex-1 py-3 bg-red-500 text-white font-bold rounded-2xl shadow-lg shadow-red-500/30 active:scale-[0.98] transition">Submit Damage</button>
      </div>
    </div>
  </div>

  <!-- ══════════════════════════════════════════════════════
       DAMAGE PRODUCT SELECTION MODAL
  ═══════════════════════════════════════════════════════ -->
  <div id="damageProductSelectModal" class="fixed inset-0 z-[400] hidden flex items-end justify-center bg-black/60 transition-opacity">
    <div class="bg-white rounded-t-3xl w-full max-w-[480px] shadow-2xl transform transition-transform translate-y-full duration-300" id="damageProductSelectContent">
      <!-- Handle -->
      <div class="flex justify-center pt-3 pb-1">
        <div class="w-10 h-1 bg-gray-200 rounded-full"></div>
      </div>
      <!-- Header -->
      <div class="flex items-center justify-between px-5 pt-2 pb-4 border-b border-gray-100">
        <div>
          <div class="text-base font-black text-gray-800">Select Products</div>
          <div class="text-xs text-gray-400 font-medium">Choose from retailer's company products</div>
        </div>
        <button onclick="closeDamageProductSelection()" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-700">
          <i class="fa-solid fa-xmark text-lg"></i>
        </button>
      </div>
      <!-- Search Box -->
      <div class="px-5 pt-3 pb-2">
        <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 px-3 py-2 rounded-xl">
          <i class="fa-solid fa-magnifying-glass text-gray-400 text-sm"></i>
          <input type="text" id="dmgProductSearch" placeholder="Search products..." oninput="filterDmgProductSelection()" class="bg-transparent border-none text-sm outline-none w-full text-gray-700">
        </div>
      </div>
      <!-- Body -->
      <div class="px-5 pt-2 pb-2 max-h-[45vh] overflow-y-auto" id="dmgProductSelectItems">
        <!-- Products list with check buttons -->
      </div>
      <!-- Footer -->
      <div class="px-5 pb-6 pt-3 border-t border-gray-100 flex gap-3">
        <button onclick="closeDamageProductSelection()" class="flex-1 py-3 bg-gray-100 text-gray-600 font-bold rounded-2xl active:bg-gray-200 transition">Cancel</button>
        <button onclick="confirmDamageProductSelection()" class="flex-1 py-3 bg-red-500 text-white font-bold rounded-2xl shadow-lg shadow-red-500/30 active:scale-[0.98] transition">Add Selected</button>
      </div>
    </div>
  </div>

</div><!-- /page root -->

<script>
// ── Data from PHP ────────────────────────────────────────────
const orderedRetailers = <?= json_encode($retailers) ?>;

let map, userMarker, radiusCircle = null;
let currentDispatchId = null;
let markers = [];

let currentPartialDueRetailer = null;
let currentPartialDueOrders = [];

function handleRetailerClick(ret, shouldWarn) {
    const partialOrders = ret.orders.filter(o => o.status === 'partial');
    if (partialOrders.length > 0) {
        showPartialDuePopup(ret, partialOrders);
    } else if (shouldWarn) {
        showConfirmPopup("This delivery was already processed. Do you want to redo/modify it?", () => {
            openRetailerSheet(ret);
        });
    } else {
        openRetailerSheet(ret);
    }
}

function showPartialDuePopup(ret, partialOrders) {
    currentPartialDueRetailer = ret;
    currentPartialDueOrders = partialOrders;
    
    let totalDue = 0;
    partialOrders.forEach(o => {
        totalDue += (parseFloat(o.total_amount) - parseFloat(o.paid_amount));
    });
    
    document.getElementById('partialDueTitle').innerText = ret.name;
    document.getElementById('partialDueMessage').innerHTML = `This retailer has a pending due of <span class="text-amber-600 font-black">৳${totalDue.toFixed(2)}</span>.`;
    
    const modal = document.getElementById('partialDueModal');
    const content = document.getElementById('partialDueContent');
    
    modal.classList.remove('hidden');
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closePartialDueModal() {
    const modal = document.getElementById('partialDueModal');
    const content = document.getElementById('partialDueContent');
    
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 200);
}

// ── Damage Modal ──────────────────────────────────────────────
let selectedDamageProducts = [];
let allCompanyProducts = [];

function openDamageModal() {
    if (!currentRetailerObj) return;

    // Reset selected products
    selectedDamageProducts = [];
    allCompanyProducts = [];

    // Set retailer label
    const name = currentRetailerObj.retailer_name || currentRetailerObj.dealer_name || currentRetailerObj.name || 'Retailer';
    document.getElementById('dmgRetailerLabel').innerText = name;

    // Render empty / initial state
    renderSelectedDamageProducts();

    document.getElementById('dmgTotalAmount').value = '';
    
    const modal = document.getElementById('damageModal');
    const content = document.getElementById('damageModalContent');
    modal.classList.remove('hidden');
    requestAnimationFrame(() => {
        content.classList.remove('translate-y-full');
        content.classList.add('translate-y-0');
    });
    
    // Initial calculation
    calcDamageSummary();
}

function calcDamageSummary() {
    let totalDamage = 0;
    selectedDamageProducts.forEach(p => {
        totalDamage += (p.qty || 1) * (parseFloat(p.price) || 0);
    });

    const dmgTotalAmountInput = document.getElementById('dmgTotalAmount');
    dmgTotalAmountInput.value = totalDamage > 0 ? totalDamage.toFixed(2) : '';

    const deliveredValue = typeof getSelectedOrderGettingTotal === 'function' ? getSelectedOrderGettingTotal() : 0;
    document.getElementById('dmgDeliveredValue').innerText = '৳' + deliveredValue.toFixed(2);

    let netPayable = deliveredValue - totalDamage;
    if (netPayable < 0) netPayable = 0;

    document.getElementById('dmgNetPayable').innerText = '৳' + netPayable.toFixed(2);
    document.getElementById('dmgReceiptAmount').value = netPayable >= 0 ? netPayable.toFixed(2) : '';
}

function onManualDamageAmountChange() {
    const totalDamage = parseFloat(document.getElementById('dmgTotalAmount').value) || 0;
    const deliveredValue = typeof getSelectedOrderGettingTotal === 'function' ? getSelectedOrderGettingTotal() : 0;
    
    let netPayable = deliveredValue - totalDamage;
    if (netPayable < 0) netPayable = 0;

    document.getElementById('dmgNetPayable').innerText = '৳' + netPayable.toFixed(2);
    document.getElementById('dmgReceiptAmount').value = netPayable >= 0 ? netPayable.toFixed(2) : '';
}

function renderSelectedDamageProducts() {
    const list = document.getElementById('dmgProductList');
    if (selectedDamageProducts.length === 0) {
        list.innerHTML = `<div class="text-sm text-gray-400 text-center py-4">No products selected. Click "Select Products" to add.</div>`;
    } else {
        list.innerHTML = selectedDamageProducts.map(p => `
            <div class="flex items-center gap-3 bg-gray-50 rounded-2xl px-4 py-3 border border-red-100">
                <button type="button" onclick="removeDamageProduct(${p.id})" class="text-red-500 hover:text-red-700">
                    <i class="fa-solid fa-circle-minus text-lg"></i>
                </button>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-bold text-gray-800 truncate">${p.name}</div>
                    <div class="text-xs text-pink-500 font-bold">Tk ${parseFloat(p.price || 0).toFixed(0)}</div>
                </div>
                <input type="number" min="1" value="${p.qty || 1}" placeholder="Qty"
                       class="dmg-qty-input w-16 text-center text-sm font-bold bg-white border border-gray-200 rounded-xl py-1.5 outline-none focus:border-red-400 transition"
                       data-pid="${p.id}" oninput="updateDamageProductQty(${p.id}, this.value)">
            </div>
        `).join('');
    }
}

function removeDamageProduct(id) {
    selectedDamageProducts = selectedDamageProducts.filter(p => p.id !== id);
    renderSelectedDamageProducts();
    calcDamageSummary();
}

function updateDamageProductQty(id, qty) {
    const p = selectedDamageProducts.find(prod => prod.id === id);
    if (p) {
        p.qty = parseInt(qty) || 1;
        calcDamageSummary();
    }
}

// ── Damage Product Selection Modal ────────────────────────────
async function openDamageProductSelection() {
    if (!currentRetailerObj) return;

    let dispatchIds = [];
    if (currentRetailerObj.orders) {
        dispatchIds = currentRetailerObj.orders.map(o => o.dispatch_id).filter(id => id);
    }
    
    // Fallback if missing
    if (dispatchIds.length === 0 && currentDispatchId) {
        dispatchIds = [currentDispatchId];
    }

    // Show loading
    const listContainer = document.getElementById('dmgProductSelectItems');
    listContainer.innerHTML = `<div class="text-center py-8 text-gray-500 font-bold"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Loading products...</div>`;

    document.getElementById('dmgProductSearch').value = '';

    // Show selection modal
    const modal = document.getElementById('damageProductSelectModal');
    const content = document.getElementById('damageProductSelectContent');
    modal.classList.remove('hidden');
    requestAnimationFrame(() => {
        content.classList.remove('translate-y-full');
        content.classList.add('translate-y-0');
    });

    try {
        const res = await fetch(`<?= url("dsr/api/companies-products") ?>?dispatch_ids=${dispatchIds.join(',')}`);
        const data = await res.json();
        if (data.success && data.products) {
            allCompanyProducts = data.products;
            renderDmgProductSelectionList();
        } else {
            listContainer.innerHTML = `<div class="text-sm text-red-500 text-center py-4">${data.message || 'Failed to load products.'}</div>`;
        }
    } catch(err) {
        listContainer.innerHTML = `<div class="text-sm text-red-500 text-center py-4">Error loading products.</div>`;
    }
}

function renderDmgProductSelectionList() {
    const container = document.getElementById('dmgProductSelectItems');
    const query = document.getElementById('dmgProductSearch').value.toLowerCase().trim();
    
    const filtered = allCompanyProducts.filter(p => p.name.toLowerCase().includes(query));

    if (filtered.length === 0) {
        container.innerHTML = `<div class="text-sm text-gray-400 text-center py-8 font-semibold">No products match search.</div>`;
        return;
    }

    container.innerHTML = filtered.map(p => {
        const isChecked = selectedDamageProducts.some(prod => prod.id == p.id);
        return `
            <label class="flex items-center gap-3 bg-gray-50 rounded-2xl px-4 py-3 cursor-pointer select-none border border-transparent has-[:checked]:border-red-300 has-[:checked]:bg-red-50/40 transition mb-2">
                <input type="checkbox" class="dmg-select-cb w-4 h-4 accent-red-500 rounded" 
                       data-pid="${p.id}" data-name="${p.name}" data-price="${p.price}"
                       ${isChecked ? 'checked' : ''}>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-bold text-gray-800 truncate">${p.name}</div>
                    <div class="text-xs text-gray-400 font-bold">${p.company_name} | Tk ${parseFloat(p.price || 0).toFixed(0)}</div>
                </div>
            </label>
        `;
    }).join('');
}

function filterDmgProductSelection() {
    renderDmgProductSelectionList();
}

function closeDamageProductSelection() {
    const modal = document.getElementById('damageProductSelectModal');
    const content = document.getElementById('damageProductSelectContent');
    content.classList.remove('translate-y-0');
    content.classList.add('translate-y-full');
    setTimeout(() => { modal.classList.add('hidden'); }, 300);
}

function confirmDamageProductSelection() {
    const newSelection = [];
    document.querySelectorAll('.dmg-select-cb:checked').forEach(cb => {
        const id = parseInt(cb.dataset.pid);
        const name = cb.dataset.name;
        const price = parseFloat(cb.dataset.price);

        // Keep existing quantity if already selected before
        const existing = selectedDamageProducts.find(p => p.id === id);
        newSelection.push({
            id: id,
            name: name,
            price: price,
            qty: existing ? existing.qty : 1
        });
    });

    selectedDamageProducts = newSelection;
    renderSelectedDamageProducts();
    calcDamageSummary();
    closeDamageProductSelection();
}

function closeDamageModal() {
    const modal = document.getElementById('damageModal');
    const content = document.getElementById('damageModalContent');
    content.classList.remove('translate-y-0');
    content.classList.add('translate-y-full');
    setTimeout(() => { modal.classList.add('hidden'); }, 300);
}

async function submitDamage() {
    const totalAmount = parseFloat(document.getElementById('dmgTotalAmount').value || 0);
    if (totalAmount <= 0) {
        showToast('⚠️ Please enter the total damage amount.');
        return;
    }

    if (selectedDamageProducts.length === 0) {
        showToast('⚠️ Please select at least one product.');
        return;
    }

    const payloadProducts = selectedDamageProducts.map(p => ({
        product_id: p.id,
        qty: p.qty || 1
    }));

    const retailerId = currentRetailerObj.retailer_id || currentRetailerObj.dealer_id || 0;
    const date = document.querySelector('input[type="date"]')?.value || new Date().toISOString().split('T')[0];

    const submitBtn = document.querySelector('#damageModal button[onclick="submitDamage()"]');
    submitBtn.disabled = true;
    submitBtn.innerText = 'Saving...';

    try {
        const res = await fetch('<?= url("dsr/damage/store") ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `csrf_token=<?= Helpers::csrfToken() ?>&retailer_id=${retailerId}&date=${date}&total_amount=${totalAmount}&products=${encodeURIComponent(JSON.stringify(payloadProducts))}`
        });
        const data = await res.json();
        if (data.success) {
            closeDamageModal();
            showToast('✅ Damage report saved successfully!');
        } else {
            showToast('❌ ' + (data.message || 'Failed to save damage report.'));
        }
    } catch(err) {
        showToast('❌ Network error. Please try again.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerText = 'Submit Damage';
    }
}

function handleDueDetailsAction() {
    closePartialDueModal();
    openRetailerSheet(currentPartialDueRetailer);
}

function handleDueCancelAction() {
    closePartialDueModal();
    currentRetailerObj = currentPartialDueRetailer;
    if (currentPartialDueOrders.length === 1) {
        currentDispatchId = currentPartialDueOrders[0].dispatch_id;
        openSingleCancelModal();
    } else {
        showMultiCancelPopup(currentPartialDueOrders);
    }
}

async function submitDuePayment(dispatchId, newStatus, newPaidAmount, deliveredItems) {
    try {
        const res = await fetch('<?= url("dsr/delivery/update/") ?>' + dispatchId, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `csrf_token=<?= Helpers::csrfToken() ?>&status=${newStatus}&paid_amount=${newPaidAmount}&notes=&items=${encodeURIComponent(JSON.stringify(deliveredItems))}`
        });
        const data = await res.json();
        if(!data.success) {
            throw new Error(data.message || 'Error updating delivery');
        }
        return true;
    } catch (err) {
        showToast('❌ ' + (err.message || 'An error occurred.'));
        return false;
    }
}

function handleDuePaymentAction() {
    closePartialDueModal();
    currentRetailerObj = currentPartialDueRetailer;
    
    let totalDue = 0;
    currentPartialDueOrders.forEach(o => {
        totalDue += (parseFloat(o.total_amount) - parseFloat(o.paid_amount));
    });
    
    showPromptPopup(`Enter payment amount (Total Due: ৳${totalDue.toFixed(2)}):`, async (val) => {
        if (val <= 0) {
            showToast("⚠️ Payment amount must be greater than zero!");
            return;
        }
        
        let remainingPayment = val;
        
        const btns = document.querySelectorAll('button');
        btns.forEach(b => { b.disabled = true; });
        
        try {
            for (let i = 0; i < currentPartialDueOrders.length; i++) {
                const order = currentPartialDueOrders[i];
                const orderDue = parseFloat(order.total_amount) - parseFloat(order.paid_amount);
                
                if (remainingPayment <= 0) break;
                
                let paymentForThisOrder = Math.min(remainingPayment, orderDue);
                if (i === currentPartialDueOrders.length - 1) {
                    paymentForThisOrder = remainingPayment;
                }
                remainingPayment -= paymentForThisOrder;
                
                const newCumulativePaid = parseFloat(order.paid_amount) + paymentForThisOrder;
                
                let status = 'partial';
                if (newCumulativePaid >= parseFloat(order.total_amount)) {
                    status = 'delivered';
                }
                
                let deliveredItems = {};
                if (order.products) {
                    order.products.forEach(p => {
                        deliveredItems[p.product_id] = p.delivered_quantity !== null ? parseInt(p.delivered_quantity) : parseInt(p.quantity);
                    });
                }
                
                const success = await submitDuePayment(order.dispatch_id, status, newCumulativePaid, deliveredItems);
                if (success) {
                    order.status = status;
                    order.paid_amount = newCumulativePaid;
                }
            }
            
            showToast('✅ Due payment recorded!');
            
            if (document.getElementById('retailerSheet').classList.contains('active')) {
                openRetailerSheet(currentRetailerObj);
                selectCompanyOrder(currentOrderIndex);
            }
            if (typeof initMap === 'function') {
                redrawMapPins();
            }
        } finally {
            btns.forEach(b => { b.disabled = false; });
        }
    });
}

<?php if ($hasDeliveries): ?>

document.addEventListener('DOMContentLoaded', initMap);

function initMap() {
    map = L.map('dsrMap', { zoomControl: false }).setView([23.8103, 90.4125], 13);

    // Google Maps tiles
    L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['mt0','mt1','mt2','mt3']
    }).addTo(map);

    // ── Pin styles ──
    if (!document.getElementById('pin-styles')) {
        const s = document.createElement('style');
        s.id = 'pin-styles';
        s.textContent = `
            .map-pin-wrap { display:flex; flex-direction:column; align-items:center; }
            .map-pin-card {
                display: flex; align-items: center; gap: 5px;
                padding: 5px 10px 5px 7px; border-radius: 20px;
                white-space: nowrap; font-size: 11.5px; font-weight: 700;
                letter-spacing: 0.2px; box-shadow: 0 4px 14px rgba(0,0,0,0.22);
                border: 2px solid rgba(255,255,255,0.6);
                cursor: pointer; transition: transform 0.15s ease, box-shadow 0.15s ease;
                font-family: 'Segoe UI', sans-serif;
            }
            .map-pin-card:hover { transform: translateY(-2px) scale(1.04); box-shadow: 0 8px 20px rgba(0,0,0,0.28); }
            .map-pin-card .pin-icon {
                width: 22px; height: 22px; border-radius: 50%;
                display: flex; align-items: center; justify-content: center;
                font-size: 10px; flex-shrink: 0;
                background: rgba(255,255,255,0.25);
            }
            .map-pin-tail {
                width: 0; height: 0;
                border-left: 7px solid transparent;
                border-right: 7px solid transparent;
                margin-top: -1px;
            }
            /* Blue — in_transit (pending delivery) */
            .pin-pending .map-pin-card {
                background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 60%, #3b82f6 100%);
                color: #fff;
            }
            .pin-pending .map-pin-tail { border-top: 9px solid #1d4ed8; }
            /* Green — delivered */
            .pin-delivered .map-pin-card {
                background: linear-gradient(135deg, #15803d 0%, #16a34a 60%, #22c55e 100%);
                color: #fff;
            }
            .pin-delivered .map-pin-tail { border-top: 9px solid #15803d; }
            /* Yellow — partial */
            .pin-partial .map-pin-card {
                background: linear-gradient(135deg, #a16207 0%, #d97706 60%, #eab308 100%);
                color: #fff;
            }
            .pin-partial .map-pin-tail { border-top: 9px solid #a16207; }
            /* Red — cancelled */
            .pin-cancelled .map-pin-card {
                background: linear-gradient(135deg, #dc2626 0%, #ef4444 60%, #f87171 100%);
                color: #fff;
            }
            .pin-cancelled .map-pin-tail { border-top: 9px solid #dc2626; }
            /* Green and Red Gradient — Mixed (Delivered + Cancelled) */
            .pin-mixed .map-pin-card {
                background: linear-gradient(135deg, #15803d 0%, #16a34a 50%, #ef4444 50%, #dc2626 100%) !important;
                color: #fff;
            }
            .pin-mixed .map-pin-tail { border-top: 9px solid #16a34a; }
        `;
        document.head.appendChild(s);
    }

    const fallbackLat = 23.8103, fallbackLng = 90.4125;
    let firstValidLat = null, firstValidLng = null;

    // ── Plot only van-loaded retailers ──
    orderedRetailers.forEach((ret, i) => {
        ret.name = ret.dealer_name || ret.name || 'Retailer';

        // Use real coordinates if available, else spread around Dhaka
        if (!ret.lat || !ret.lng) {
            ret.lat = fallbackLat + (Math.random() - 0.5) * 0.05;
            ret.lng = fallbackLng + (Math.random() - 0.5) * 0.05;
        }

        if (!firstValidLat) { firstValidLat = parseFloat(ret.lat); firstValidLng = parseFloat(ret.lng); }

        // Determine aggregate status for pin color
        let hasDelivered = false;
        let hasPending = false;
        let hasPartial = false;
        let hasCancelled = false;
        
        ret.orders.forEach(o => {
            if (o.status === 'in_transit') hasPending = true;
            if (o.status === 'partial') hasPartial = true;
            if (o.status === 'delivered') hasDelivered = true;
            if (o.status === 'cancelled') hasCancelled = true;
        });

        let pinClass = 'pin-pending';
        let pinIcon = 'fa-clock';
        if (hasPending) { pinClass = 'pin-pending'; pinIcon = 'fa-clock'; }
        else if (hasPartial) { pinClass = 'pin-partial'; pinIcon = 'fa-circle-half-stroke'; }
        else if (hasDelivered && hasCancelled) { pinClass = 'pin-mixed'; pinIcon = 'fa-shuffle'; }
        else if (hasCancelled) { pinClass = 'pin-cancelled'; pinIcon = 'fa-circle-xmark'; }
        else if (hasDelivered) { pinClass = 'pin-delivered'; pinIcon = 'fa-check'; }

        let shouldWarn = true;
        ret.orders.forEach(o => {
            if (o.status !== 'delivered' && o.status !== 'cancelled') {
                shouldWarn = false;
            }
        });

        // Order count summary
        let orderSummary = '';
        if (ret.orders.length > 1) {
            orderSummary = `<div class="text-[9px] font-normal opacity-80 mt-[-2px]">${ret.orders.length} Orders</div>`;
        }

        const icon = L.divIcon({
            className: pinClass,
            html: `
                <div class="map-pin-wrap">
                    <div class="map-pin-card">
                        <div class="pin-icon"><i class="fa-solid ${pinIcon}"></i></div>
                        <div>
                            <div>${ret.name}</div>
                            ${orderSummary}
                        </div>
                    </div>
                    <div class="map-pin-tail"></div>
                </div>
            `,
            iconSize: [120, 45],
            iconAnchor: [60, 45]
        });
        const marker = L.marker([parseFloat(ret.lat), parseFloat(ret.lng)], { icon }).addTo(map);
        marker.on('click', () => {
            handleRetailerClick(ret, shouldWarn);
        });
        markers.push(marker);
    });

    // Center map on first retailer if coords exist, else locate DSR
    if (firstValidLat) {
        map.setView([firstValidLat, firstValidLng], 14);
    }

    locateMe();
}

function locateMe() {
    if (!map) return;
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            map.setView([lat, lng], 15);

            if (userMarker) map.removeLayer(userMarker);
            userMarker = L.marker([lat, lng], {
                icon: L.divIcon({
                    className: 'custom-div-icon',
                    html: `<div style="background-color:#3b82f6;width:16px;height:16px;border-radius:50%;border:3px solid white;box-shadow:0 0 0 4px rgba(59,130,246,0.3);"></div>`,
                    iconSize: [16, 16],
                    iconAnchor: [8, 8]
                })
            }).addTo(map);

            if (radiusCircle) map.removeLayer(radiusCircle);
            // Circle removed per user request
        }, () => {
            // Geolocation failed or denied
        });
    }
}

// ── Open bottom sheet for a specific retailer ──
let currentRetailerObj = null;
let currentOrderIndex = 0;

function openRetailerSheet(retailer) {
    currentRetailerObj = retailer;
    currentOrderIndex = 0;
    
    // Set Name & Subtitle & Avatar
    document.getElementById('bsRetailerName').innerText = retailer.retailer_name || retailer.dealer_name || retailer.name;
    document.getElementById('bsRetailerSub').innerText = retailer.retailer_name ? retailer.dealer_name : 'Retailer';
    document.getElementById('bsRetailerAvatar').src = 'https://i.pravatar.cc/100?img=' + ((parseInt(retailer.dealer_id) % 70) + 1);
    
    const tabsContainer = document.getElementById('bsCompanyTabs');
    tabsContainer.innerHTML = '';
    
    const list = document.getElementById('bsProductsList');
    list.innerHTML = '';
    
    if (retailer.orders && retailer.orders.length > 1) {
        tabsContainer.classList.remove('hidden');
        retailer.orders.forEach((order, idx) => {
            const isSelected = idx === 0;
            const count = order.products ? order.products.length : 0;
            const isCancelled = order.status === 'cancelled';
            let borderClass = 'border-gray-200 bg-white text-gray-500';
            if (isCancelled) {
                borderClass = isSelected ? 'border-red-600 bg-white text-red-600 font-bold' : 'border-red-200 bg-red-50/30 text-red-500';
            } else if (isSelected) {
                borderClass = 'border-blue-600 bg-white text-blue-600 font-bold';
            }
            
            tabsContainer.insertAdjacentHTML('beforeend', `
                <button onclick="selectCompanyOrder(${idx})" id="tab-order-${idx}"
                        class="whitespace-nowrap px-4 py-1.5 rounded-full text-xs font-semibold border transition ${borderClass}">
                    ${order.company_name} <span class="text-blue-500 ml-1 font-bold">${count}</span>
                </button>
            `);
        });
    } else {
        tabsContainer.classList.add('hidden');
    }

    // Render all orders
    if (retailer.orders && retailer.orders.length > 0) {
        retailer.orders.forEach((order, orderIdx) => {
            let orderHtml = `<div id="order-group-${orderIdx}" class="order-group-container hidden space-y-3">`;
            if (!order.products || order.products.length === 0) {
                orderHtml += `<div class="text-center py-4 text-sm text-gray-400"><i class="fa-solid fa-box-open mb-2 text-xl"></i><br>No products found for this order.</div>`;
            } else {
                order.products.forEach((p, idx) => {
                    const ppb = parseInt(p.pieces_per_box) || 1;
                    const qty = parseInt(p.quantity); // pieces dispatched on van

                    let initialDeliveredQty = p.delivered_quantity !== null ? parseInt(p.delivered_quantity) : qty;

                    const initialBoxes = Math.floor(initialDeliveredQty / ppb);
                    const initialPcs = initialDeliveredQty % ppb;

                    orderHtml += `
                    <div class="bg-white rounded-3xl border border-gray-150 p-4 shadow-sm product-item" data-price="${p.price || 0}">
                        <div class="flex items-center gap-4 mb-3">
                            <div class="w-16 h-16 bg-gray-50 rounded-2xl overflow-hidden flex items-center justify-center flex-shrink-0 p-1">
                                ${p.image
                                    ? `<img src="<?= asset('uploads/products/') ?>${p.image}" class="w-full h-full object-contain rounded-xl">`
                                    : `<i class="fa-solid fa-box text-gray-300 text-2xl"></i>`
                                }
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-black text-gray-800 line-clamp-2 leading-snug">${p.name}</div>
                                <div class="text-xs font-black text-pink-500 mt-1">Tk ${parseFloat(p.price || 0).toFixed(0)}</div>
                            </div>
                        </div>

                        <!-- Delivery Input Box & Pcs -->
                        <div class="flex gap-3 mt-3">
                            <!-- Box Input -->
                            <div class="flex items-center flex-1 border border-gray-250 rounded-xl overflow-hidden focus-within:border-blue-500 transition-colors">
                                <input type="number" min="0" value="${initialBoxes}"
                                    class="w-full text-center font-bold text-gray-700 py-2 outline-none delivery-input-box text-sm"
                                    data-ppb="${ppb}" data-qty="${qty}" data-idx="${orderIdx}-${idx}" data-pid="${p.product_id}" data-price="${p.price || 0}"
                                    oninput="calcProgress(this, '${orderIdx}-${idx}')">
                                <div class="bg-gray-100 text-gray-500 text-xs font-bold px-3 py-2.5 border-l border-gray-250 select-none">Box</div>
                            </div>
                            <!-- Pcs Input -->
                            <div class="flex items-center flex-1 border border-gray-250 rounded-xl overflow-hidden focus-within:border-blue-500 transition-colors">
                                <input type="number" min="0" value="${initialPcs}"
                                    class="w-full text-center font-bold text-gray-700 py-2 outline-none delivery-input-pcs text-sm"
                                    data-ppb="${ppb}" data-qty="${qty}" data-idx="${orderIdx}-${idx}" data-pid="${p.product_id}" data-price="${p.price || 0}"
                                    oninput="calcProgress(this, '${orderIdx}-${idx}')">
                                <div class="bg-gray-100 text-gray-500 text-xs font-bold px-3 py-2.5 border-l border-gray-250 select-none">Pcs</div>
                            </div>
                        </div>
                    </div>`;
                });
            }
            orderHtml += `</div>`;
            list.insertAdjacentHTML('beforeend', orderHtml);
        });
        
        selectCompanyOrder(0); // Load first order by default
    }

    document.getElementById('bottomSheetOverlay').classList.add('active');
    document.getElementById('retailerSheet').classList.add('active');

    // Pan map to this retailer
    if (retailer.lat && retailer.lng && map) {
        map.setView([parseFloat(retailer.lat), parseFloat(retailer.lng)], 16);
    }
}

function selectCompanyOrder(orderIndex) {
    if (!currentRetailerObj || !currentRetailerObj.orders) return;
    const order = currentRetailerObj.orders[orderIndex];
    if (!order) return;
    
    currentDispatchId = order.dispatch_id;
    currentOrderIndex = orderIndex;

    // Update tabs visual state
    if (currentRetailerObj.orders.length > 1) {
        document.querySelectorAll('[id^="tab-order-"]').forEach((btn, idx) => {
            const ord = currentRetailerObj.orders[idx];
            const count = ord.products ? ord.products.length : 0;
            const isCancelled = ord.status === 'cancelled';
            if (idx === orderIndex) {
                if (isCancelled) {
                    btn.className = 'whitespace-nowrap px-4 py-1.5 rounded-full text-xs font-semibold border border-red-600 bg-white text-red-600 font-bold';
                } else {
                    btn.className = 'whitespace-nowrap px-4 py-1.5 rounded-full text-xs font-semibold border border-blue-600 bg-white text-blue-600 font-bold';
                }
            } else {
                if (isCancelled) {
                    btn.className = 'whitespace-nowrap px-4 py-1.5 rounded-full text-xs font-semibold border border-red-200 bg-red-50/30 text-red-500';
                } else {
                    btn.className = 'whitespace-nowrap px-4 py-1.5 rounded-full text-xs font-semibold border border-gray-200 bg-white text-gray-500 active:bg-gray-50';
                }
            }
        });
    }

    document.getElementById('bsOrderTotal').innerText = 'Tk ' + parseFloat(order.total_amount || 0).toFixed(2);
    
    // Update order quantity stats
    const totalQty = order.products ? order.products.reduce((acc, p) => acc + parseInt(p.quantity), 0) : 0;
    document.getElementById('bsTotalQty').innerText = totalQty;

    const statusLabel = { 'in_transit': 'Pending Delivery', 'delivered': 'Delivered', 'partial': 'Partial/Due', 'cancelled': 'Cancelled' };
    const statusColor = { 'in_transit': '#3b82f6', 'delivered': '#16a34a', 'partial': '#f97316', 'cancelled': '#dc2626' };
    const bsStatus = document.getElementById('bsStatus');
    if (bsStatus) {
        bsStatus.innerText = statusLabel[order.status] || 'Pending';
        bsStatus.style.color = statusColor[order.status] || '#3b82f6';
    }

    const bsPartialInfo = document.getElementById('bsPartialInfo');
    if (bsPartialInfo) {
        if (order.status === 'partial') {
            bsPartialInfo.classList.remove('hidden');
            const paid = parseFloat(order.paid_amount || 0);
            const total = parseFloat(order.total_amount || 0);
            const due = total - paid;
            document.getElementById('bsPaidAmount').innerText = '৳' + paid.toFixed(2);
            document.getElementById('bsDueAmount').innerText = '৳' + (due > 0 ? due : 0).toFixed(2);
        } else {
            bsPartialInfo.classList.add('hidden');
        }
    }

    // Toggle visibility
    document.querySelectorAll('.order-group-container').forEach(div => div.classList.add('hidden'));
    const activeDiv = document.getElementById(`order-group-${orderIndex}`);
    if (activeDiv) {
        activeDiv.classList.remove('hidden');
        // Disable or enable inputs based on cancellation
        const inputs = activeDiv.querySelectorAll('input');
        inputs.forEach(input => {
            input.disabled = (order.status === 'cancelled');
        });
    }

    // Dynamic Action Buttons
    const actionContainer = document.getElementById('bsActionButtons');
    if (actionContainer) {
        if (order.status === 'cancelled') {
            actionContainer.innerHTML = `<button onclick="redoCancelledOrder(${orderIndex})" class="w-full py-3 rounded-full font-bold bg-amber-500 hover:bg-amber-600 text-white active:scale-[0.98] transition text-sm shadow-md flex items-center justify-center gap-2"><i class="fa-solid fa-rotate-left"></i> Redo</button>`;
        } else {
            actionContainer.innerHTML = `
                <button onclick="markDelivery('cancelled')" class="flex-1 py-3 rounded-full font-bold bg-[#ff3b30] text-white active:scale-[0.98] transition text-sm shadow-md">Cancel</button>
                <button onclick="markDelivery('delivered')" class="flex-1 py-3 rounded-full font-bold bg-[#007aff] text-white active:scale-[0.98] transition text-sm shadow-md">Paid</button>
            `;
        }
    }

    // Trigger initial calculation for this group
    const firstInput = activeDiv ? activeDiv.querySelector('.delivery-input-box') : null;
    if (firstInput) {
        calcProgress(firstInput, `${orderIndex}-0`);
    } else {
        document.getElementById('bsGettingTotal').innerText = '৳0.00';
    }
}

function closeBottomSheet() {
    document.getElementById('bottomSheetOverlay').classList.remove('active');
    document.getElementById('retailerSheet').classList.remove('active');
    currentDispatchId = null;
}

function calcProgress(el, idx) {
    const parent = el.closest('.product-item');
    const boxInput = parent.querySelector('.delivery-input-box');
    const pcsInput = parent.querySelector('.delivery-input-pcs');

    let boxes = parseInt(boxInput.value) || 0;
    let pcs   = parseInt(pcsInput.value) || 0;
    const ppb   = parseInt(boxInput.getAttribute('data-ppb')) || 1;
    const maxQty = parseInt(boxInput.getAttribute('data-qty')) || 1;

    let totalDelivered = (boxes * ppb) + pcs;

    if (totalDelivered > maxQty) {
        showToast("⚠️ Delivered quantity cannot exceed ordered quantity (" + maxQty + " PCS)!");
        boxes = Math.floor(maxQty / ppb);
        pcs = maxQty % ppb;
        boxInput.value = boxes;
        pcsInput.value = pcs;
        totalDelivered = maxQty;
    }

    const delQtyEl = document.getElementById(`delQty-${idx}`);
    if (delQtyEl) delQtyEl.innerText = totalDelivered;

    let percent = (totalDelivered / maxQty) * 100;
    if (percent > 100) percent = 100;

    const delPercentEl = document.getElementById(`delPercent-${idx}`);
    if (delPercentEl) delPercentEl.innerText = Math.round(percent) + '%';

    const bar = document.getElementById(`delBar-${idx}`);
    if (bar) {
        bar.style.width = percent + '%';
        if (percent >= 100) {
            bar.className = 'h-full transition-all duration-300 bg-green-500';
        } else if (percent > 0) {
            bar.className = 'h-full transition-all duration-300 bg-orange-400';
        } else {
            bar.className = 'h-full transition-all duration-300 bg-brand';
        }
    }
    
    // Recalculate getting total for the CURRENT active order group
    const orderGroup = el.closest('.order-group-container');
    let gettingTotal = 0;
    let anyInputFilled = false;
    orderGroup.querySelectorAll('.product-item').forEach(pItem => {
        const bInp = pItem.querySelector('.delivery-input-box');
        const pInp = pItem.querySelector('.delivery-input-pcs');
        if (bInp && pInp) {
            const b = parseInt(bInp.value) || 0;
            const p = parseInt(pInp.value) || 0;
            if (b > 0 || p > 0) {
                anyInputFilled = true;
            }
            const p_ppb = parseInt(bInp.getAttribute('data-ppb')) || 1;
            const tQty = (b * p_ppb) + p;
            const price = parseFloat(bInp.getAttribute('data-price')) || 0;
            gettingTotal += (tQty * price);
        }
    });
    
    const bsGettingTotal = document.getElementById('bsGettingTotal');
    if (bsGettingTotal) bsGettingTotal.innerText = '৳' + gettingTotal.toFixed(2);

    // Update due if partial info is visible
    const bsPartialInfo = document.getElementById('bsPartialInfo');
    if (bsPartialInfo && !bsPartialInfo.classList.contains('hidden') && currentRetailerObj && currentRetailerObj.orders) {
        const order = currentRetailerObj.orders.find(o => o.dispatch_id === currentDispatchId);
        if (order) {
            const paid = parseFloat(order.paid_amount || 0);
            let due = 0;
            if (anyInputFilled) {
                due = gettingTotal - paid;
            } else {
                due = parseFloat(order.total_amount || 0) - paid;
            }
            const bsDueAmount = document.getElementById('bsDueAmount');
            if (bsDueAmount) bsDueAmount.innerText = '৳' + (due > 0 ? due : 0).toFixed(2);
        }
    }
}

function getSelectedOrderGettingTotal() {
    const activeDiv = document.getElementById(`order-group-${currentOrderIndex}`);
    if (!activeDiv) return 0;
    let total = 0;
    activeDiv.querySelectorAll('.product-item').forEach(pItem => {
        const bInp = pItem.querySelector('.delivery-input-box');
        const pInp = pItem.querySelector('.delivery-input-pcs');
        if (bInp && pInp) {
            const b = parseInt(bInp.value) || 0;
            const p = parseInt(pInp.value) || 0;
            const p_ppb = parseInt(bInp.getAttribute('data-ppb')) || 1;
            const tQty = (b * p_ppb) + p;
            const price = parseFloat(bInp.getAttribute('data-price')) || 0;
            total += (tQty * price);
        }
    });
    return total;
}

function markDelivery(status) {
    if (!currentRetailerObj || !currentRetailerObj.orders) return;

    if (status === 'delivered') {
        openPaidPaymentModal();
    } else if (status === 'cancelled') {
        openSingleCancelModal();
    } else if (status === 'partial') {
        if (currentRetailerObj.orders.length > 1) {
            showMultiPartialPopup(currentRetailerObj.orders);
        } else {
            showPromptPopup("Enter the amount the retailer has paid:", (val) => {
                const targetDispatchIds = [currentRetailerObj.orders[0].dispatch_id];
                let paidAmounts = {};
                paidAmounts[currentRetailerObj.orders[0].dispatch_id] = val;
                submitSelectedDeliveries(status, targetDispatchIds, paidAmounts);
            });
        }
    }
}

function openPaidPaymentModal() {
    const totalPayable = getSelectedOrderGettingTotal();
    document.getElementById('paidPaymentInput').value = totalPayable.toFixed(2);
    document.getElementById('paymentDueInfo').innerText = 'Paid in Full';
    document.getElementById('paymentDueInfo').className = 'text-sm font-semibold text-green-500 mb-4 h-5';
    
    document.getElementById('paidPaymentModal').classList.remove('hidden');
    setTimeout(() => {
        document.getElementById('paidPaymentContent').classList.remove('scale-95', 'opacity-0');
    }, 50);
}

function closePaidPaymentModal() {
    document.getElementById('paidPaymentContent').classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        document.getElementById('paidPaymentModal').classList.add('hidden');
    }, 200);
}

function onPaidPaymentInput(el) {
    const entered = parseFloat(el.value) || 0;
    const total = getSelectedOrderGettingTotal();
    const due = total - entered;
    
    const info = document.getElementById('paymentDueInfo');
    if (due > 0) {
        info.innerText = `Due: ৳${due.toFixed(2)} (Will set as Partial)`;
        info.className = 'text-sm font-bold text-red-500 mb-4 h-5';
    } else {
        info.innerText = 'Paid in Full';
        info.className = 'text-sm font-semibold text-green-500 mb-4 h-5';
    }
}

function submitPaidPayment() {
    const entered = parseFloat(document.getElementById('paidPaymentInput').value) || 0;
    const total = getSelectedOrderGettingTotal();
    
    let status = 'delivered';
    if (entered < total) {
        status = 'partial';
    }
    
    closePaidPaymentModal();
    
    let paidAmounts = {};
    paidAmounts[currentDispatchId] = entered;
    
    submitSelectedDeliveries(status, [currentDispatchId], paidAmounts);
}

function openSingleCancelModal() {
    document.getElementById('singleCancelModal').classList.remove('hidden');
    setTimeout(() => {
        document.getElementById('singleCancelContent').classList.remove('scale-95', 'opacity-0');
    }, 50);
}

function closeSingleCancelModal() {
    document.getElementById('singleCancelContent').classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        document.getElementById('singleCancelModal').classList.add('hidden');
    }, 200);
}

function submitSingleCancel() {
    const reason = document.getElementById('cancelReasonSelect').value;
    closeSingleCancelModal();
    submitSelectedDeliveries('cancelled', [currentDispatchId], {}, reason);
}

async function redoCancelledOrder(orderIndex) {
    const order = currentRetailerObj.orders[orderIndex];
    if (!order) return;
    
    const dispatchId = order.dispatch_id;
    const btns = document.querySelectorAll('#retailerSheet button');
    btns.forEach(b => { b.disabled = true; });

    try {
        const res = await fetch('<?= url("dsr/delivery/update/") ?>' + dispatchId, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `csrf_token=<?= Helpers::csrfToken() ?>&status=in_transit&paid_amount=0&notes=&items=${encodeURIComponent(JSON.stringify({}))}`
        });
        const data = await res.json();
        if(!data.success) {
            throw new Error(data.message || 'Error updating delivery');
        }

        // Update local object
        order.status = 'in_transit';
        order.notes = '';
        
        showToast('🔄 Order restored to pending!');
        
        // Re-render and refresh sheet
        openRetailerSheet(currentRetailerObj);
        selectCompanyOrder(orderIndex);

        // Redraw map pins
        if (typeof initMap === 'function' && map) {
            redrawMapPins();
        }

    } catch (err) {
        showToast('❌ ' + (err.message || 'An error occurred.'));
    } finally {
        btns.forEach(b => { b.disabled = false; });
    }
}

function redrawMapPins() {
    if (!map) return;
    markers.forEach(m => map.removeLayer(m));
    markers = [];
    
    orderedRetailers.forEach((ret, i) => {
        let hasDelivered = false;
        let hasPending = false;
        let hasPartial = false;
        let hasCancelled = false;
        ret.orders.forEach(o => {
            if (o.status === 'in_transit') hasPending = true;
            if (o.status === 'partial') hasPartial = true;
            if (o.status === 'delivered') hasDelivered = true;
            if (o.status === 'cancelled') hasCancelled = true;
        });
        let pinClass = 'pin-pending';
        let pinIcon = 'fa-clock';
        if (hasPending) { pinClass = 'pin-pending'; pinIcon = 'fa-clock'; }
        else if (hasPartial) { pinClass = 'pin-partial'; pinIcon = 'fa-circle-half-stroke'; }
        else if (hasDelivered && hasCancelled) { pinClass = 'pin-mixed'; pinIcon = 'fa-shuffle'; }
        else if (hasCancelled) { pinClass = 'pin-cancelled'; pinIcon = 'fa-circle-xmark'; }
        else if (hasDelivered) { pinClass = 'pin-delivered'; pinIcon = 'fa-check'; }

        let shouldWarn = true;
        ret.orders.forEach(o => {
            if (o.status !== 'delivered' && o.status !== 'cancelled') {
                shouldWarn = false;
            }
        });

        let orderSummary = '';
        if (ret.orders.length > 1) {
            orderSummary = `<div class="text-[9px] font-normal opacity-80 mt-[-2px]">${ret.orders.length} Orders</div>`;
        }

        const icon = L.divIcon({
            className: pinClass,
            html: `
                <div class="map-pin-wrap">
                    <div class="map-pin-card">
                        <div class="pin-icon"><i class="fa-solid ${pinIcon}"></i></div>
                        <div>
                            <div>${ret.name}</div>
                            ${orderSummary}
                        </div>
                    </div>
                    <div class="map-pin-tail"></div>
                </div>
            `,
            iconSize: [120, 45],
            iconAnchor: [60, 45]
        });
        const marker = L.marker([parseFloat(ret.lat), parseFloat(ret.lng)], { icon }).addTo(map);
        marker.on('click', () => {
            handleRetailerClick(ret, shouldWarn);
        });
        markers.push(marker);
    });
}

async function submitSelectedDeliveries(status, targetDispatchIds, paidAmounts = {}, reason = '') {
    const orders = currentRetailerObj.orders.filter(o => targetDispatchIds.map(String).includes(String(o.dispatch_id)));
    if (orders.length === 0) return;

    const btns = document.querySelectorAll('#retailerSheet button');
    btns.forEach(b => { b.disabled = true; });

    try {
        for (let i = 0; i < orders.length; i++) {
            const o = orders[i];
            const dispatchId = o.dispatch_id;
            const paidAmount = paidAmounts[dispatchId] || 0;

            // Gather items for this specific order group
            let deliveredItems = {};
            const origIdx = currentRetailerObj.orders.findIndex(orig => orig.dispatch_id === dispatchId);
            const orderGroup = document.getElementById(`order-group-${origIdx}`);
            if (orderGroup) {
                orderGroup.querySelectorAll('.product-item').forEach(pItem => {
                    const bInp = pItem.querySelector('.delivery-input-box');
                    const pInp = pItem.querySelector('.delivery-input-pcs');
                    if (bInp && pInp) {
                        const b = parseInt(bInp.value) || 0;
                        const p = parseInt(pInp.value) || 0;
                        const p_ppb = parseInt(bInp.getAttribute('data-ppb')) || 1;
                        const tQty = (b * p_ppb) + p;
                        const pid = bInp.getAttribute('data-pid');
                        if (pid) {
                            deliveredItems[pid] = tQty;
                        }
                    }
                });
            } else if (o.products) {
                o.products.forEach(p => {
                    deliveredItems[p.product_id] = p.delivered_quantity !== null ? parseInt(p.delivered_quantity) : parseInt(p.quantity);
                });
            }

            const res = await fetch('<?= url("dsr/delivery/update/") ?>' + dispatchId, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `csrf_token=<?= Helpers::csrfToken() ?>&status=${status}&paid_amount=${paidAmount}&notes=${encodeURIComponent(reason)}&items=${encodeURIComponent(JSON.stringify(deliveredItems))}`
            });
            const data = await res.json();
            if(!data.success) {
                throw new Error(data.message || 'Error updating delivery');
            }
        }

        let msg = '✅ Deliveries processed!';
        if (status === 'partial') msg = '🔶 Marked as Partial/Due';
        if (status === 'cancelled') msg = '❌ Orders Cancelled';
        showToast(msg);
        if (status === 'cancelled' || status === 'delivered' || status === 'partial') {
            orders.forEach(o => {
                o.status = status;
                o.paid_amount = paidAmounts[o.dispatch_id] || 0;
                o.notes = reason;
                
                const origIdx = currentRetailerObj.orders.findIndex(orig => orig.dispatch_id === o.dispatch_id);
                const orderGroup = document.getElementById(`order-group-${origIdx}`);
                if (orderGroup) {
                    orderGroup.querySelectorAll('.product-item').forEach(pItem => {
                        const bInp = pItem.querySelector('.delivery-input-box');
                        const pInp = pItem.querySelector('.delivery-input-pcs');
                        if (bInp && pInp) {
                            const b = parseInt(bInp.value) || 0;
                            const p = parseInt(pInp.value) || 0;
                            const p_ppb = parseInt(bInp.getAttribute('data-ppb')) || 1;
                            const tQty = (b * p_ppb) + p;
                            const pid = bInp.getAttribute('data-pid');
                            
                            const prod = o.products.find(pr => String(pr.product_id) === String(pid));
                            if (prod) {
                                prod.delivered_quantity = tQty;
                            }
                        }
                    });
                }
            });
            if (document.getElementById('retailerSheet').classList.contains('active')) {
                openRetailerSheet(currentRetailerObj);
                selectCompanyOrder(currentOrderIndex);
            }
            
            if (typeof initMap === 'function') {
                redrawMapPins();
            }
        } else {
            setTimeout(() => location.reload(), 900);
        }

    } catch (err) {
        showToast('❌ ' + (err.message || 'An error occurred.'));
    } finally {
        btns.forEach(b => { b.disabled = false; });
    }
}

function showMultiCancelPopup(orders) {
    const modal = document.getElementById('customCancelModal');
    const content = document.getElementById('customCancelContent');
    const container = document.getElementById('cancelCheckboxesContainer');
    
    container.innerHTML = '';
    orders.forEach(o => {
        container.insertAdjacentHTML('beforeend', `
            <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-100 cursor-pointer active:bg-gray-100 transition">
                <input type="checkbox" name="cancel_dispatch" value="${o.dispatch_id}" checked class="w-5 h-5 text-red-600 rounded focus:ring-red-500">
                <div class="flex-1">
                    <div class="text-sm font-bold text-gray-800">${o.company_name}</div>
                    <div class="text-xs text-gray-500">Value: ৳${parseFloat(o.total_amount).toFixed(2)}</div>
                </div>
            </label>
        `);
    });

    modal.classList.remove('hidden');
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);

    const closeBtn = document.getElementById('cancelModalCloseBtn');
    const okBtn = document.getElementById('cancelModalOkBtn');

    // Clean previous events
    const newCloseBtn = closeBtn.cloneNode(true);
    closeBtn.parentNode.replaceChild(newCloseBtn, closeBtn);
    const newOkBtn = okBtn.cloneNode(true);
    okBtn.parentNode.replaceChild(newOkBtn, okBtn);

    const close = () => {
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(() => modal.classList.add('hidden'), 200);
    };

    newCloseBtn.addEventListener('click', close);
    newOkBtn.addEventListener('click', () => {
        const checkedBoxes = container.querySelectorAll('input[name="cancel_dispatch"]:checked');
        const targetDispatchIds = Array.from(checkedBoxes).map(cb => cb.value);
        if (targetDispatchIds.length === 0) {
            showToast("⚠️ Please select at least one order to cancel!");
            return;
        }
        close();
        submitSelectedDeliveries('cancelled', targetDispatchIds);
    });
}

function showMultiCompletePopup(orders) {
    const modal = document.getElementById('customCompleteModal');
    const content = document.getElementById('customCompleteContent');
    const container = document.getElementById('completeCheckboxesContainer');
    
    container.innerHTML = '';
    orders.forEach(o => {
        container.insertAdjacentHTML('beforeend', `
            <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-100 cursor-pointer active:bg-gray-100 transition">
                <input type="checkbox" name="complete_dispatch" value="${o.dispatch_id}" checked class="w-5 h-5 text-green-600 rounded focus:ring-green-500">
                <div class="flex-1">
                    <div class="text-sm font-bold text-gray-800">${o.company_name}</div>
                    <div class="text-xs text-gray-500">Value: ৳${parseFloat(o.total_amount).toFixed(2)}</div>
                </div>
            </label>
        `);
    });

    modal.classList.remove('hidden');
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);

    const closeBtn = document.getElementById('completeModalCloseBtn');
    const okBtn = document.getElementById('completeModalOkBtn');

    // Clean previous events
    const newCloseBtn = closeBtn.cloneNode(true);
    closeBtn.parentNode.replaceChild(newCloseBtn, closeBtn);
    const newOkBtn = okBtn.cloneNode(true);
    okBtn.parentNode.replaceChild(newOkBtn, okBtn);

    const close = () => {
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(() => modal.classList.add('hidden'), 200);
    };

    newCloseBtn.addEventListener('click', close);
    newOkBtn.addEventListener('click', () => {
        const checkedBoxes = container.querySelectorAll('input[name="complete_dispatch"]:checked');
        const targetDispatchIds = Array.from(checkedBoxes).map(cb => cb.value);
        if (targetDispatchIds.length === 0) {
            showToast("⚠️ Please select at least one order to complete!");
            return;
        }
        close();
        submitSelectedDeliveries('delivered', targetDispatchIds);
    });
}

function showMultiPartialPopup(orders) {
    const modal = document.getElementById('customPartialModal');
    const content = document.getElementById('customPartialContent');
    const container = document.getElementById('partialInputsContainer');
    
    container.innerHTML = '';
    orders.forEach(o => {
        container.insertAdjacentHTML('beforeend', `
            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 space-y-2">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="partial_dispatch" value="${o.dispatch_id}" checked class="w-5 h-5 text-orange-500 rounded focus:ring-orange-500" onchange="togglePartialInput(this)">
                    <div class="flex-1">
                        <div class="text-sm font-bold text-gray-800">${o.company_name}</div>
                        <div class="text-xs text-gray-500">Value: ৳${parseFloat(o.total_amount).toFixed(2)}</div>
                    </div>
                </label>
                <div class="flex items-center gap-2 pl-8" id="partial-input-wrapper-${o.dispatch_id}">
                    <span class="text-xs font-bold text-gray-400">Paid:</span>
                    <input type="number" name="partial_amount_${o.dispatch_id}" class="w-full bg-white border border-gray-200 rounded-lg px-2 py-1 text-sm font-bold text-gray-700 outline-none focus:border-orange-500" placeholder="৳0.00" value="${o.paid_amount || ''}">
                </div>
            </div>
        `);
    });

    modal.classList.remove('hidden');
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);

    const closeBtn = document.getElementById('partialModalCloseBtn');
    const okBtn = document.getElementById('partialModalOkBtn');

    // Clean previous events
    const newCloseBtn = closeBtn.cloneNode(true);
    closeBtn.parentNode.replaceChild(newCloseBtn, closeBtn);
    const newOkBtn = okBtn.cloneNode(true);
    okBtn.parentNode.replaceChild(newOkBtn, okBtn);

    const close = () => {
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(() => modal.classList.add('hidden'), 200);
    };

    newCloseBtn.addEventListener('click', close);
    newOkBtn.addEventListener('click', () => {
        const checkedBoxes = container.querySelectorAll('input[name="partial_dispatch"]:checked');
        const targetDispatchIds = Array.from(checkedBoxes).map(cb => cb.value);
        if (targetDispatchIds.length === 0) {
            showToast("⚠️ Please select at least one order!");
            return;
        }
        
        let paidAmounts = {};
        targetDispatchIds.forEach(id => {
            const inp = container.querySelector(`input[name="partial_amount_${id}"]`);
            paidAmounts[id] = parseFloat(inp.value) || 0;
        });
        
        close();
        submitSelectedDeliveries('partial', targetDispatchIds, paidAmounts);
    });
}

function togglePartialInput(cb) {
    const wrapper = document.getElementById(`partial-input-wrapper-${cb.value}`);
    if (wrapper) {
        if (cb.checked) {
            wrapper.classList.remove('hidden');
        } else {
            wrapper.classList.add('hidden');
        }
    }
}

async function saveQuantitiesOnly() {
    if (!currentRetailerObj || !currentRetailerObj.orders) return;
    const orders = currentRetailerObj.orders;

    const btns = document.querySelectorAll('#retailerSheet button');
    btns.forEach(b => { b.disabled = true; });

    try {
        for (let i = 0; i < orders.length; i++) {
            const o = orders[i];
            const dispatchId = o.dispatch_id;
            const paidAmount = parseFloat(o.paid_amount || 0);
            const status = o.status; // Keep original status (e.g. 'in_transit', 'partial')

            // Gather items for this specific order group
            let deliveredItems = {};
            const orderGroup = document.getElementById(`order-group-${i}`);
            if (orderGroup) {
                orderGroup.querySelectorAll('.product-item').forEach(pItem => {
                    const bInp = pItem.querySelector('.delivery-input-box');
                    const pInp = pItem.querySelector('.delivery-input-pcs');
                    if (bInp && pInp) {
                        const b = parseInt(bInp.value) || 0;
                        const p = parseInt(pInp.value) || 0;
                        const p_ppb = parseInt(bInp.getAttribute('data-ppb')) || 1;
                        const tQty = (b * p_ppb) + p;
                        const pid = bInp.getAttribute('data-pid');
                        if (pid) {
                            deliveredItems[pid] = tQty;
                        }
                    }
                });
            }

            const res = await fetch('<?= url("dsr/delivery/update/") ?>' + dispatchId, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `csrf_token=<?= Helpers::csrfToken() ?>&status=${status}&paid_amount=${paidAmount}&items=${encodeURIComponent(JSON.stringify(deliveredItems))}`
            });
            const data = await res.json();
            if(!data.success) {
                throw new Error(data.message || 'Error updating delivery');
            }
        }

        showToast('💾 Quantities saved successfully!');
        setTimeout(() => location.reload(), 900);

    } catch (err) {
        showToast('❌ ' + (err.message || 'An error occurred.'));
        btns.forEach(b => { b.disabled = false; });
    }
}

// --- Modal Handlers ---
function showConfirmPopup(message, onConfirm) {
    const modal = document.getElementById('customConfirmModal');
    const content = document.getElementById('customConfirmContent');
    document.getElementById('confirmMessage').innerText = message;
    
    modal.classList.remove('hidden');
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);

    const closeBtn = document.getElementById('confirmCancelBtn');
    const okBtn = document.getElementById('confirmOkBtn');
    
    // Clean previous events
    const newCloseBtn = closeBtn.cloneNode(true);
    closeBtn.parentNode.replaceChild(newCloseBtn, closeBtn);
    const newOkBtn = okBtn.cloneNode(true);
    okBtn.parentNode.replaceChild(newOkBtn, okBtn);
    
    const close = () => {
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(() => modal.classList.add('hidden'), 200);
    };

    newCloseBtn.addEventListener('click', close);
    newOkBtn.addEventListener('click', () => {
        close();
        if(onConfirm) onConfirm();
    });
}

function showPromptPopup(message, onConfirm) {
    const modal = document.getElementById('customPromptModal');
    const content = document.getElementById('customPromptContent');
    const input = document.getElementById('promptInput');
    
    document.getElementById('promptMessage').innerText = message;
    input.value = '';
    
    modal.classList.remove('hidden');
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
        input.focus();
    }, 10);

    const closeBtn = document.getElementById('promptCancelBtn');
    const okBtn = document.getElementById('promptOkBtn');
    
    // Clean previous events
    const newCloseBtn = closeBtn.cloneNode(true);
    closeBtn.parentNode.replaceChild(newCloseBtn, closeBtn);
    const newOkBtn = okBtn.cloneNode(true);
    okBtn.parentNode.replaceChild(newOkBtn, okBtn);
    
    const close = () => {
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(() => modal.classList.add('hidden'), 200);
    };

    newCloseBtn.addEventListener('click', close);
    newOkBtn.addEventListener('click', () => {
        const val = parseFloat(input.value) || 0;
        close();
        if(onConfirm) onConfirm(val);
    });
}

function showToast(msg) {
    const t = document.createElement('div');
    t.className = 'fixed top-20 left-1/2 -translate-x-1/2 z-[200] bg-gray-900 text-white text-sm font-bold px-5 py-3 rounded-2xl shadow-2xl transition-all';
    t.style.cssText = 'animation: fadeInUp 0.3s ease';
    t.innerText = msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 1800);
}
function openRetailerListModal() {
    const modal = document.getElementById('retailerListModal');
    modal.classList.remove('hidden');
    // Animate opacity if needed, but since it's full screen, just show it
}

function closeRetailerListModal() {
    document.getElementById('retailerListModal').classList.add('hidden');
}

function handleRetailerListClick(idx) {
    closeRetailerListModal();
    const ret = orderedRetailers[idx];
    if (!ret) return;
    
    let shouldWarn = true;
    ret.orders.forEach(o => {
        if (o.status !== 'delivered' && o.status !== 'cancelled') {
            shouldWarn = false;
        }
    });
    
    handleRetailerClick(ret, shouldWarn);
}

<?php endif; // $hasDeliveries ?>
</script>

<style>
@keyframes fadeInUp {
    from { opacity: 0; transform: translate(-50%, 12px); }
    to   { opacity: 1; transform: translate(-50%, 0); }
}
</style>
