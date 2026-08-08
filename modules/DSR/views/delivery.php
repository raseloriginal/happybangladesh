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
  <div class="fixed inset-0 z-20 flex flex-col items-center justify-between p-4 sm:p-6 bg-slate-50 text-center font-siliguri overflow-y-auto">
    
    <!-- Top Date Bar Selector -->
    <div class="pt-2 pb-4">
      <div class="inline-flex items-center gap-2 bg-white border border-slate-200 px-4 py-2 rounded-full shadow-2xs">
        <i class="fa-regular fa-calendar-days text-blue-600 text-sm"></i>
        <input type="date" value="<?= $selectedDate ?? date('Y-m-d') ?>" class="bg-transparent border-none text-slate-900 text-xs sm:text-sm font-black font-mono outline-none cursor-pointer" onchange="window.location.href='<?= url('dsr/delivery') ?>?date='+this.value">
      </div>
    </div>

    <!-- Centered Card -->
    <div class="w-full max-w-sm bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80 my-auto space-y-5">
      <!-- Icon Container -->
      <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 text-2xl mx-auto shadow-inner border border-blue-100">
        <i class="fa-solid fa-truck-ramp-box"></i>
      </div>

      <!-- Text Header -->
      <div class="space-y-1.5">
        <h2 class="text-xl font-black text-slate-900 tracking-tight">ভ্যান খালি রয়েছে (Van is Empty)</h2>
        <?php if (isset($isCompleted) && $isCompleted): ?>
          <p class="text-xs text-slate-500 leading-relaxed font-medium">
            আজকের রুটে কোনো অর্ডার জমা নেই। আপনার কাছে মাল স্টক থাকলে রেডি সেল করতে পারেন।
          </p>
        <?php else: ?>
          <p class="text-xs text-slate-500 leading-relaxed font-medium">
            আজকের চালানে আপনার ভ্যানে কোনো পণ্য লোড করা হয়নি। ম্যানেজারের চালানের জন্য অপেক্ষা করুন।
          </p>
        <?php endif; ?>
      </div>

      <!-- Action Buttons -->
      <div class="space-y-2.5 pt-1">
        <?php if (!isset($isCompleted) || !$isCompleted): ?>
          <a href="<?= url('dsr/van-stock') ?>" class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs sm:text-sm rounded-2xl shadow-sm active:scale-95 transition flex items-center justify-center gap-2">
            <i class="fa-solid fa-boxes-stacked"></i>
            <span>Go to Inventory (ইনভেন্টরি)</span>
          </a>
        <?php endif; ?>

        <button onclick="openReadySaleModal()" class="w-full py-3 px-4 bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-xs sm:text-sm rounded-2xl shadow-sm active:scale-95 transition flex items-center justify-center gap-2" <?= isset($isReturned) && $isReturned ? 'disabled style="opacity: 0.5; cursor: not-allowed;" title="DSR has returned, Ready Sale is disabled"' : '' ?>>
          <i class="fa-solid fa-bolt"></i>
          <span>Ready Sale by DSR (রেডি সেল)</span>
        </button>
      </div>

      <!-- Back Link -->
      <div class="pt-2">
        <a href="<?= url('dsr/dashboard') ?>" class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-slate-700 font-bold transition">
          <i class="fa-solid fa-arrow-left text-[10px]"></i>
          <span>Back to Dashboard</span>
        </a>
      </div>
    </div>

    <!-- Bottom Spacing for Nav Bar -->
    <div class="pb-16"></div>

  </div>
  <?php endif; ?>

  <!-- ══════════════════════════════════════════════════════
       MAP — shown only when there are deliveries
  ═══════════════════════════════════════════════════════ -->
  <div id="dsrMap" class="absolute inset-0 z-0 <?= !$hasDeliveries ? 'hidden' : '' ?>"></div>

  <?php if ($hasDeliveries): ?>

  <!-- Top Overlay -->
  <div class="absolute top-0 left-0 w-full z-10 px-4 pt-10 pb-2 bg-gradient-to-b from-black/60 to-transparent pointer-events-none">
    <div class="flex items-center gap-2 pointer-events-auto">
      <a href="<?= url('dsr/dashboard') ?>" class="w-9 h-9 bg-white rounded-full flex items-center justify-center text-gray-800 shadow-md">
        <i class="fa-solid fa-arrow-left"></i>
      </a>
      <div class="flex-1 min-w-0">
        <div class="text-white text-[11px] font-semibold opacity-80 flex items-center gap-1.5 truncate">
            Deliveries for: 
            <input type="date" value="<?= $selectedDate ?? date('Y-m-d') ?>" class="bg-white/20 border-b border-white text-white text-[11px] outline-none px-1 py-0.5 rounded" onchange="window.location.href='<?= url('dsr/delivery') ?>?date='+this.value">
        </div>
        <div class="text-white text-base font-black leading-tight truncate"><?= count($retailers) ?> Retailer<?= count($retailers) !== 1 ? 's' : '' ?> on Van</div>
      </div>
      <button onclick="openReadySaleModal()" class="px-3 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold shadow-md flex items-center gap-1.5 active:scale-95 transition" <?= isset($isReturned) && $isReturned ? 'disabled style="opacity: 0.5; cursor: not-allowed;" title="DSR has returned, Ready Sale is disabled"' : '' ?>>
        <i class="fa-solid fa-bolt text-amber-100"></i> Ready Sale
      </button>
      <button onclick="openRetailerListModal()" class="w-9 h-9 bg-white rounded-full flex items-center justify-center text-gray-800 shadow-md active:scale-95 transition">
        <i class="fa-solid fa-list-ul"></i>
      </button>
      <button onclick="locateMe()" class="w-9 h-9 bg-white rounded-full flex items-center justify-center text-blue-600 shadow-md active:scale-95 transition">
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

      <!-- Sheet Header Banner (Royal Blue Brand Theme) -->
      <div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-200 bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-700" style="margin: -16px -16px 12px -16px; padding: 12px 16px; border-top-left-radius: 16px; border-top-right-radius: 16px;">
        <button onclick="closeBottomSheet()" class="w-8 h-8 flex items-center justify-center text-white/90 hover:text-white transition active:scale-90">
          <i class="fa-solid fa-chevron-left text-base"></i>
        </button>
        <span class="text-sm font-black text-white uppercase tracking-wider">অর্ডারের বিবরণ</span>
        <button onclick="openRetailerListModal()" class="w-8 h-8 flex items-center justify-center text-white/90 hover:text-white transition active:scale-90">
          <i class="fa-solid fa-list-ul text-base"></i>
        </button>
      </div>

      <!-- Retailer Card (Royal Blue Spreadsheet Style) -->
      <div class="bg-white border border-slate-200 rounded-xl p-3 shadow-sm mb-3">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2.5 min-w-0">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center text-white shrink-0 shadow-sm bg-gradient-to-tr from-blue-600 to-indigo-600">
              <i class="fa-solid fa-store text-sm"></i>
            </div>
            <div class="min-w-0">
              <h2 class="text-sm font-black text-slate-900 leading-tight truncate" id="bsRetailerName">খুচরা বিক্রেতার নাম</h2>
              <p class="text-[10px] text-slate-400 mt-0.5 truncate" id="bsRetailerSub">ঠিকানা বিবরণ</p>
            </div>
          </div>
          <div class="flex gap-1.5 shrink-0">
            <a href="#" class="w-8 h-8 rounded-lg flex items-center justify-center shadow-sm active:scale-95 transition text-blue-600 border border-blue-200 bg-blue-50">
              <i class="fa-solid fa-plus text-sm"></i>
            </a>
            <button onclick="openDamageModal()" id="damageBtn" class="w-8 h-8 rounded-lg flex items-center justify-center shadow-sm active:scale-95 transition text-red-600 border border-red-200 bg-red-50" title="ক্ষতিগ্রস্ত পণ্য" <?= isset($isReturned) && $isReturned ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '' ?>>
              <i class="fa-solid fa-ban text-xs"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Stats Summary (Royal Blue minimal grid cells) -->
      <div class="grid grid-cols-3 border border-slate-200 rounded-xl bg-white text-[10px] font-bold text-slate-500 mb-3 overflow-hidden divide-x divide-slate-100 shadow-[0_2px_10px_-3px_rgba(0,0,0,0.05)]">
        <!-- Items -->
        <div class="p-2 flex flex-col justify-between h-12 bg-white">
          <div class="text-[9px] text-slate-400 uppercase tracking-wider">মোট পণ্য</div>
          <div class="text-slate-800 font-black text-xs flex items-center gap-1.5 flex-wrap">
            <span id="bsTotalQty">0</span>
            <span id="bsStatus" class="px-1 py-0.5 rounded text-[8px] border font-bold" style="color: #2563eb; border-color: #93c5fd; background-color: #eff6ff;">অপেক্ষমান</span>
          </div>
        </div>
        <!-- Ordered Value -->
        <div class="p-2 flex flex-col justify-between h-12 bg-white">
          <div class="text-[9px] text-slate-400 uppercase tracking-wider">অর্ডার মূল্য</div>
          <div class="text-slate-400 line-through text-xs font-bold" id="bsOrderTotal">৳0</div>
        </div>
        <!-- Payable Value -->
        <div class="p-2 flex flex-col justify-between h-12 bg-blue-50/50">
          <div class="text-[9px] text-blue-700 uppercase tracking-wider">পরিশোধযোগ্য</div>
          <div class="text-blue-700 font-black text-xs" id="bsGettingTotal">৳0</div>
        </div>
      </div>

      <!-- Partial Info -->
      <div id="bsPartialInfo" class="hidden mb-4 bg-orange-50 border border-orange-100 rounded-lg p-3 flex justify-between items-center text-xs shadow-sm">
          <div class="text-orange-700 font-bold">Partial Status</div>
          <div class="flex gap-4">
              <div>Paid: <span id="bsPaidAmount" class="font-black text-green-600">৳0.00</span></div>
              <div>Due: <span id="bsDueAmount" class="font-black text-red-500">৳0.00</span></div>
          </div>
      </div>

      <!-- Hidden elements to preserve JS bindings -->
      <div class="hidden">
        <div id="bsRetailerAddress"></div>
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
      <div class="flex gap-4 mt-4 pt-3 border-t border-gray-150" id="bsActionButtons">
        <button onclick="markDelivery('cancelled')" class="flex-1 py-2.5 rounded-lg font-bold text-white active:scale-[0.98] transition text-sm shadow-md" style="background-color: #d83b01;">Cancel</button>
        <button onclick="markDelivery('delivered')" class="flex-1 py-2.5 rounded-lg font-bold text-white active:scale-[0.98] transition text-sm shadow-md" style="background-color: #1e73be;">Paid</button>
      </div>

    </div>
  </div>

  <?php endif; // $hasDeliveries ?>

  <!-- ══════════════════════════════════════════════════════
       CUSTOM MODALS
  ═══════════════════════════════════════════════════════ -->

  <!-- Retailer List Modal (Modern Excel Grid Style) -->
  <div id="retailerListModal" class="fixed inset-0 z-[500] hidden flex flex-col bg-slate-50 font-siliguri">
      <!-- Header -->
      <div class="bg-white px-4 py-3.5 sm:px-6 sm:py-4 shadow-sm border-b border-slate-200/80 flex items-center justify-between sticky top-0 z-10">
          <div class="flex items-center gap-3">
              <button type="button" onclick="closeRetailerListModal()" class="w-9 h-9 sm:w-10 sm:h-10 bg-slate-100 hover:bg-slate-900 hover:text-white rounded-xl flex items-center justify-center text-slate-700 active:scale-95 transition shadow-2xs cursor-pointer" title="ফিরে যান">
                  <i class="fa-solid fa-arrow-left text-sm"></i>
              </button>
              <div>
                  <h2 class="text-base sm:text-lg font-black text-slate-900 leading-tight">ভ্যানের খুচরা বিক্রেতা তালিকা</h2>
                  <div class="text-xs text-slate-500 font-medium">মোট <?= count($retailers) ?> জন রিটেইলার অর্ডার</div>
              </div>
          </div>

          <button type="button" onclick="closeRetailerListModal()" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition text-xs">
              <i class="fa-solid fa-xmark"></i>
          </button>
      </div>

      <!-- Body Grid (Modern Excel Card Grid) -->
      <div class="flex-1 overflow-y-auto p-3 sm:p-4 grid grid-cols-1 sm:grid-cols-2 gap-3 pb-24 content-start">
          <?php foreach ($retailers as $idx => $r): 
              $hasDelivered = false;
              $hasPending = false;
              $hasPartial = false;
              $hasCancelled = false;
              $actionedCount = 0;
              $totalVal = 0;

              foreach ($r['orders'] as $o) {
                  $totalVal += (float)($o['total_amount'] ?? 0);
                  if ($o['status'] === 'in_transit') {
                      $hasPending = true;
                  } else {
                      $actionedCount++;
                  }
                  if ($o['status'] === 'partial') $hasPartial = true;
                  if ($o['status'] === 'delivered') $hasDelivered = true;
                  if ($o['status'] === 'cancelled') $hasCancelled = true;
              }
              $totalOrders = count($r['orders']);
              
              // Determine status badge
              if ($hasPending && $actionedCount > 0) {
                  $statusBadge = '<span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-slate-800 text-white border border-slate-700"><i class="fa-solid fa-circle-exclamation mr-1"></i>আংশিক বাকি</span>';
              } elseif ($hasPending) {
                  $statusBadge = '<span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-blue-50 text-blue-700 border border-blue-200"><i class="fa-regular fa-clock mr-1"></i>অপেক্ষমাণ</span>';
              } elseif ($hasDelivered && !$hasPartial && !$hasCancelled) {
                  $statusBadge = '<span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200"><i class="fa-solid fa-check mr-1"></i>ডেলিভারড</span>';
              } elseif ($hasCancelled && !$hasDelivered && !$hasPartial) {
                  $statusBadge = '<span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-rose-50 text-rose-700 border border-rose-200"><i class="fa-solid fa-xmark mr-1"></i>বাতিল</span>';
              } elseif ($hasPartial && !$hasDelivered && !$hasCancelled) {
                  $statusBadge = '<span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-50 text-amber-700 border border-amber-200"><i class="fa-solid fa-circle-half-stroke mr-1"></i>পার্শিয়াল</span>';
              } else {
                  $statusBadge = '<span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-purple-50 text-purple-700 border border-purple-200"><i class="fa-solid fa-shuffle mr-1"></i>মিশ্রিত</span>';
              }
          ?>
            <div class="bg-white rounded-2xl p-3.5 shadow-2xs hover:shadow-md active:scale-[0.99] transition cursor-pointer border border-slate-200/90 flex flex-col justify-between space-y-3 group" onclick="handleRetailerListClick(<?= $idx ?>)">
                
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-black border border-blue-100">
                      <i class="fa-solid fa-store"></i>
                    </div>
                    <?= $statusBadge ?>
                  </div>
                  <span class="font-mono font-black text-xs text-slate-900 bg-slate-50 px-2 py-0.5 rounded-lg border border-slate-200">
                    ৳<?= number_format($totalVal) ?>
                  </span>
                </div>

                <div>
                  <div class="text-xs sm:text-sm font-black text-slate-900 leading-snug line-clamp-2 group-hover:text-blue-600 transition">
                    <?= h($r['retailer_name'] ?? $r['dealer_name'] ?? 'Unknown Retailer') ?>
                  </div>
                  <div class="text-[10.5px] text-slate-400 font-medium line-clamp-1 mt-0.5">
                    <i class="fa-solid fa-location-dot mr-1 text-slate-300"></i><?= h($r['address'] ?? 'No Address') ?>
                  </div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-[11px]">
                  <span class="font-bold text-slate-500">
                    <?= count($r['orders']) ?> টি অর্ডার
                  </span>
                  <span class="w-7 h-7 rounded-xl bg-slate-100 group-hover:bg-blue-600 group-hover:text-white flex items-center justify-center text-slate-400 text-xs transition duration-200">
                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                  </span>
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
  <div id="paidPaymentModal" class="fixed inset-0 z-[200] hidden flex items-center justify-center p-4 bg-black/60 transition-opacity">
      <div class="bg-white rounded-2xl w-full max-w-sm shadow-2xl transform transition-transform scale-95 opacity-0 duration-200 overflow-hidden" id="paidPaymentContent">
          <!-- Pop Header -->
          <div class="px-5 py-3.5 text-white flex items-center justify-between" style="background-color: #1e73be;">
              <div class="flex items-center gap-2">
                  <i class="fa-solid fa-money-bill-wave text-base"></i>
                  <span class="text-xs font-black uppercase tracking-wider">পেমেন্ট নিশ্চিত করুন</span>
              </div>
              <button onclick="closePaidPaymentModal()" class="w-6 h-6 flex items-center justify-center text-white/80 hover:text-white transition active:scale-90">
                  <i class="fa-solid fa-xmark text-base"></i>
              </button>
          </div>

          <!-- Pop Body -->
          <div class="p-5">
              <!-- Retailer Info -->
              <div class="mb-3 bg-gray-50 border border-gray-200 rounded-xl p-3">
                  <div class="text-[9px] text-gray-400 font-bold uppercase">খুচরা বিক্রেতা</div>
                  <div class="text-xs font-black text-gray-805 mt-0.5" id="paidRetailerName">খুচরা বিক্রেতার নাম</div>
              </div>

              <!-- Payment Summary Table -->
              <div class="border border-gray-200 rounded-xl overflow-hidden mb-3 bg-white text-xs">
                  <div class="grid grid-cols-2 divide-x divide-gray-150 border-b border-gray-150 bg-gray-50/50 p-2 font-bold text-gray-500 text-[10px] uppercase tracking-wider">
                      <div>বিবরণ</div>
                      <div class="text-right">টাকা</div>
                  </div>
                  <div class="divide-y divide-gray-150">
                      <div class="grid grid-cols-2 p-2.5">
                          <div class="text-gray-500 font-bold">মোট পরিশোধযোগ্য</div>
                          <div class="font-black text-gray-800 text-right" id="paidTotalPayable">৳০</div>
                      </div>
                      <div class="grid grid-cols-2 p-2.5">
                          <div class="text-gray-500 font-bold">ইতিমধ্যে পরিশোধিত</div>
                          <div class="font-black text-amber-600 text-right" id="paidAlreadyPaid">৳০</div>
                      </div>
                      <div class="grid grid-cols-2 p-2.5 bg-blue-50/10">
                          <div class="text-[#1e73be] font-black">বাকি পাওনা</div>
                          <div class="font-black text-[#1e73be] text-right" id="paidRemainingDue">৳০</div>
                      </div>
                  </div>
              </div>

              <!-- Input Amount -->
              <div class="mb-4">
                  <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">প্রাপ্ত টাকা লিখুন (৳)</label>
                  <div class="relative flex items-center">
                      <span class="absolute left-4 text-gray-400 font-bold text-lg">৳</span>
                      <input type="number" id="paidPaymentInput" oninput="onPaidPaymentInput(this)" class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-9 pr-4 py-2.5 text-lg font-black text-gray-800 outline-none focus:border-[#1e73be] focus:ring-4 focus:ring-blue-500/10 transition">
                  </div>
              </div>

              <!-- Info status text -->
              <div id="paymentDueInfo" class="text-xs font-bold text-center text-gray-500 mb-4 h-5">
                  সম্পূর্ণ পরিশোধিত
              </div>

              <!-- Action Buttons -->
              <div class="flex gap-3">
                  <button onclick="closePaidPaymentModal()" class="flex-1 py-2.5 bg-gray-100 text-gray-600 font-bold rounded-lg active:bg-gray-200 transition text-sm">বাতিল</button>
                  <button onclick="submitPaidPayment()" class="flex-1 py-2.5 text-white font-bold rounded-lg active:scale-[0.98] shadow-md shadow-blue-500/20 transition text-sm" style="background-color: #1e73be;">পেইড নিশ্চিত করুন</button>
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
                  <button onclick="handleDuePaymentAction()" class="w-full py-3 bg-brand text-white font-bold rounded-xl active:scale-[0.98] shadow-lg shadow-blue-500/20 transition">Due Complete</button>
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
        <!-- Add Row Button -->
        <button type="button" onclick="addDamageRow()" class="w-full mb-4 py-2.5 px-4 rounded-xl border border-dashed border-red-300 hover:border-red-500 text-red-600 font-bold text-xs flex items-center justify-center gap-2 bg-red-50/20 transition active:scale-[0.98]">
          <i class="fa-solid fa-plus"></i> Add Row
        </button>

        <!-- Rows Container -->
        <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Damage Entries</div>
        <div id="dmgRowsContainer" class="space-y-2.5 mb-4">
          <!-- Rows dynamically populated by JS -->
        </div>

        <!-- Total Delivery Value (Current Tab) -->
        <div class="flex justify-between items-center mb-2">
            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Delivered Value</div>
            <div id="dmgDeliveredValue" class="text-sm font-black text-blue-600">৳0.00</div>
        </div>

        <!-- Total Damage Amount -->
        <div class="flex justify-between items-center mb-2">
            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Damage Amount</div>
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

<script>
// ── Data from PHP ────────────────────────────────────────────
const orderedRetailers = <?= json_encode($retailers) ?>;
const vanStockMap = <?= json_encode($vanStockMap ?? new stdClass()) ?>;

let map, userMarker, radiusCircle = null;
let currentDispatchId = null;
let markers = [];

let currentPartialDueRetailer = null;
let currentPartialDueOrders = [];

function handleRetailerClick(ret, shouldWarn) {
    // If the retailer has a pending order (in_transit), bypass modals and open directly to it
    const pendingIndex = ret.orders.findIndex(o => o.status === 'in_transit');
    if (pendingIndex !== -1) {
        openRetailerSheet(ret, pendingIndex);
        return;
    }

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
        let actualTotal = 0;
        if (o.products && o.products.length > 0) {
            o.products.forEach(p => {
                const qty = parseInt(p.quantity);
                let deliveredQty = p.delivered_quantity !== null ? parseInt(p.delivered_quantity) : qty;
                actualTotal += (deliveredQty * parseFloat(p.price || 0));
            });
        } else {
            actualTotal = parseFloat(o.total_amount || 0);
        }
        totalDue += (actualTotal - parseFloat(o.paid_amount || 0));
    });
    
    document.getElementById('partialDueTitle').innerText = ret.name;
    document.getElementById('partialDueMessage').innerHTML = `This retailer has a pending due of <span class="text-amber-600 font-black">৳${totalDue.toFixed(0)}</span>.`;
    
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
const activeSrsList = <?= json_encode($srsList ?? []) ?>;
let damageRows = [];

function openDamageModal() {
    if (!currentRetailerObj) return;

    // Reset rows
    damageRows = [];

    // Set retailer label
    const name = currentRetailerObj.retailer_name || currentRetailerObj.dealer_name || currentRetailerObj.name || 'Retailer';
    document.getElementById('dmgRetailerLabel').innerText = name;

    // Add initial row by default
    addDamageRow();

    const modal = document.getElementById('damageModal');
    const content = document.getElementById('damageModalContent');
    modal.classList.remove('hidden');
    requestAnimationFrame(() => {
        content.classList.remove('translate-y-full');
        content.classList.add('translate-y-0');
    });
}

function addDamageRow() {
    damageRows.push({
        id: Date.now() + '_' + Math.random().toString(36).substr(2, 5),
        sr_id: '',
        amount: ''
    });
    renderDamageRows();
    calcDamageSummary();
}

function removeDamageRow(rowId) {
    damageRows = damageRows.filter(r => r.id !== rowId);
    if (damageRows.length === 0) {
        addDamageRow();
    } else {
        renderDamageRows();
        calcDamageSummary();
    }
}

function updateDamageRowSr(rowId, srId) {
    const row = damageRows.find(r => r.id === rowId);
    if (row) {
        row.sr_id = srId;
    }
}

function updateDamageRowAmount(rowId, amount) {
    const row = damageRows.find(r => r.id === rowId);
    if (row) {
        row.amount = amount;
        calcDamageSummary();
    }
}

function renderDamageRows() {
    const container = document.getElementById('dmgRowsContainer');
    if (!container) return;

    container.innerHTML = damageRows.map(r => `
        <div class="flex items-center gap-2 bg-gray-50 rounded-2xl p-2.5 border border-gray-200 shadow-sm">
            <!-- SR Dropdown -->
            <div class="flex-1 min-w-0">
                <select onchange="updateDamageRowSr('${r.id}', this.value)"
                        class="w-full bg-white border border-gray-200 rounded-xl px-2.5 py-2 text-xs font-bold text-gray-700 outline-none focus:border-red-400 transition">
                    <option value="">-- Select SR --</option>
                    ${activeSrsList.map(sr => `<option value="${sr.id}" ${sr.id == r.sr_id ? 'selected' : ''}>${sr.name} (${sr.company_name})</option>`).join('')}
                </select>
            </div>

            <!-- Damage Amount Input -->
            <div class="w-32 min-w-0">
                <input type="number" min="0" step="0.01" value="${r.amount}" placeholder="Amount (৳)"
                       oninput="updateDamageRowAmount('${r.id}', this.value)"
                       class="w-full text-center text-xs font-black text-red-500 bg-white border border-gray-200 rounded-xl px-2 py-2 outline-none focus:border-red-400 transition">
            </div>

            <!-- Delete Row Button -->
            <button type="button" onclick="removeDamageRow('${r.id}')" 
                    class="w-8 h-8 flex items-center justify-center text-red-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition">
                <i class="fa-solid fa-trash-can text-sm"></i>
            </button>
        </div>
    `).join('');
}

function calcDamageSummary() {
    let totalDamage = 0;
    damageRows.forEach(r => {
        const val = parseFloat(r.amount);
        if (!isNaN(val) && val > 0) {
            totalDamage += val;
        }
    });

    const dmgTotalAmountInput = document.getElementById('dmgTotalAmount');
    dmgTotalAmountInput.value = totalDamage > 0 ? totalDamage.toFixed(2) : '';

    const deliveredValue = typeof getSelectedOrderGettingTotal === 'function' ? getSelectedOrderGettingTotal() : 0;
    document.getElementById('dmgDeliveredValue').innerText = '৳' + deliveredValue.toFixed(0);

    let netPayable = deliveredValue - totalDamage;
    if (netPayable < 0) netPayable = 0;

    document.getElementById('dmgNetPayable').innerText = '৳' + netPayable.toFixed(0);
    document.getElementById('dmgReceiptAmount').value = netPayable >= 0 ? netPayable.toFixed(0) : '';
}

function onManualDamageAmountChange() {
    const totalDamage = parseFloat(document.getElementById('dmgTotalAmount').value) || 0;
    const deliveredValue = typeof getSelectedOrderGettingTotal === 'function' ? getSelectedOrderGettingTotal() : 0;
    
    let netPayable = deliveredValue - totalDamage;
    if (netPayable < 0) netPayable = 0;

    document.getElementById('dmgNetPayable').innerText = '৳' + netPayable.toFixed(0);
    document.getElementById('dmgReceiptAmount').value = netPayable >= 0 ? netPayable.toFixed(0) : '';
}

function closeDamageModal() {
    const modal = document.getElementById('damageModal');
    const content = document.getElementById('damageModalContent');
    content.classList.remove('translate-y-0');
    content.classList.add('translate-y-full');
    setTimeout(() => { modal.classList.add('hidden'); }, 300);
}

function updateDamageBtnState(hasDamage) {
    const btn = document.getElementById('damageBtn');
    if (!btn) return;
    if (hasDamage) {
        btn.className = 'px-2.5 py-1 bg-red-500 text-white rounded-full flex items-center justify-center text-xs font-bold gap-1 shadow-sm active:scale-95 transition';
        btn.innerHTML = '<i class="fa-solid fa-[#ffffff] fa-check text-xs"></i><span>Damage Recorded</span>';
        btn.title = 'Damage Recorded';
    } else {
        btn.className = 'w-8 h-8 bg-red-50 text-red-400 rounded-full flex items-center justify-center text-sm shadow-sm active:scale-95 transition';
        btn.innerHTML = '<i class="fa-solid fa-ban text-sm"></i>';
        btn.title = 'Report Damage';
    }
}

async function submitDamage() {
    const totalAmount = parseFloat(document.getElementById('dmgTotalAmount').value || 0);
    if (totalAmount <= 0) {
        showToast('⚠️ Please enter a valid damage amount.');
        return;
    }

    const validRows = damageRows.filter(r => parseFloat(r.amount) > 0);
    if (validRows.length === 0) {
        showToast('⚠️ Please enter damage amount for at least one row.');
        return;
    }

    const payloadRows = validRows.map(r => ({
        sr_id: parseInt(r.sr_id) || 0,
        amount: parseFloat(r.amount) || 0
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
            body: `csrf_token=<?= Helpers::csrfToken() ?>&retailer_id=${retailerId}&date=${date}&total_amount=${totalAmount}&rows=${encodeURIComponent(JSON.stringify(payloadRows))}`
        });
        const data = await res.json();
        if (data.success) {
            if (currentRetailerObj) {
                currentRetailerObj.has_damage = true;
            }
            updateDamageBtnState(true);
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
        let actualTotal = 0;
        if (o.products && o.products.length > 0) {
            o.products.forEach(p => {
                const qty = parseInt(p.quantity);
                let deliveredQty = p.delivered_quantity !== null ? parseInt(p.delivered_quantity) : qty;
                actualTotal += (deliveredQty * parseFloat(p.price || 0));
            });
        } else {
            actualTotal = parseFloat(o.total_amount || 0);
        }
        totalDue += (actualTotal - parseFloat(o.paid_amount || 0));
    });
    
    showConfirmPopup(`Mark all due orders as fully paid? (Total Due: ৳${totalDue.toFixed(0)})`, async () => {
        const btns = document.querySelectorAll('button');
        btns.forEach(b => { b.disabled = true; });
        
        try {
            for (let i = 0; i < currentPartialDueOrders.length; i++) {
                const order = currentPartialDueOrders[i];
                
                let actualTotal = 0;
                if (order.products && order.products.length > 0) {
                    order.products.forEach(p => {
                        const qty = parseInt(p.quantity);
                        let deliveredQty = p.delivered_quantity !== null ? parseInt(p.delivered_quantity) : qty;
                        actualTotal += (deliveredQty * parseFloat(p.price || 0));
                    });
                } else {
                    actualTotal = parseFloat(order.total_amount || 0);
                }
                
                // Set paid_amount = actualTotal (fully paid)
                const fullPaid = actualTotal;
                
                let deliveredItems = {};
                if (order.products) {
                    order.products.forEach(p => {
                        deliveredItems[p.product_id] = p.delivered_quantity !== null ? parseInt(p.delivered_quantity) : parseInt(p.quantity);
                    });
                }
                
                const success = await submitDuePayment(order.dispatch_id, 'delivered', fullPaid, deliveredItems);
                if (success) {
                    order.status = 'delivered';
                    order.paid_amount = fullPaid;
                }
            }
            
            showToast('✅ All dues marked as complete!');
            
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
    map = L.map('dsrMap', { 
        zoomControl: false,
        attributionControl: false,
        preferCanvas: true, // HTML5 Canvas rendering for 60fps mobile map performance
        fadeAnimation: true
    }).setView([23.8103, 90.4125], 13);

    // Fast HTTPS Google Maps tiles with tile buffering and data saver
    L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        maxNativeZoom: 19,
        subdomains: ['mt0','mt1','mt2','mt3'],
        keepBuffer: 6,
        updateWhenIdle: true,
        updateWhenZooming: false
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
            /* Blue — in_transit (all pending delivery) */
            .pin-pending .map-pin-card {
                background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 60%, #3b82f6 100%);
                color: #fff;
            }
            .pin-pending .map-pin-tail { border-top: 9px solid #1d4ed8; }
            /* Green — all delivered / complete */
            .pin-delivered .map-pin-card {
                background: linear-gradient(135deg, #15803d 0%, #16a34a 60%, #22c55e 100%) !important;
                color: #fff;
            }
            .pin-delivered .map-pin-tail { border-top: 9px solid #15803d !important; }
            /* Yellow — all partial / due */
            .pin-partial .map-pin-card {
                background: linear-gradient(135deg, #b45309 0%, #d97706 60%, #eab308 100%) !important;
                color: #fff;
            }
            .pin-partial .map-pin-tail { border-top: 9px solid #b45309 !important; }
            /* Red — all cancelled */
            .pin-cancelled .map-pin-card {
                background: linear-gradient(135deg, #dc2626 0%, #ef4444 60%, #f87171 100%) !important;
                color: #fff;
            }
            .pin-cancelled .map-pin-tail { border-top: 9px solid #dc2626 !important; }
            /* Black — incomplete (some actioned, some still pending) */
            .pin-incomplete .map-pin-card {
                background: linear-gradient(135deg, #1a1a1a 0%, #374151 60%, #4b5563 100%) !important;
                color: #fff;
            }
            .pin-incomplete .map-pin-tail { border-top: 9px solid #1a1a1a !important; }
            /* Green + Red split — Delivered + Cancelled */
            .pin-delivered-cancelled .map-pin-card {
                background: linear-gradient(135deg, #15803d 0%, #16a34a 48%, #ef4444 52%, #dc2626 100%) !important;
                color: #fff;
            }
            .pin-delivered-cancelled .map-pin-tail { border-top: 9px solid #16a34a; }
            /* Green + Yellow split — Delivered + Partial */
            .pin-delivered-partial .map-pin-card {
                background: linear-gradient(135deg, #15803d 0%, #16a34a 48%, #d97706 52%, #eab308 100%) !important;
                color: #fff;
            }
            .pin-delivered-partial .map-pin-tail { border-top: 9px solid #16a34a; }
            /* Yellow + Red split — Partial + Cancelled */
            .pin-partial-cancelled .map-pin-card {
                background: linear-gradient(135deg, #b45309 0%, #eab308 48%, #ef4444 52%, #dc2626 100%) !important;
                color: #fff;
            }
            .pin-partial-cancelled .map-pin-tail { border-top: 9px solid #b45309; }
            /* Green + Yellow + Red — All three mixed */
            .pin-mixed-all .map-pin-card {
                background: linear-gradient(135deg, #15803d 0%, #16a34a 30%, #eab308 50%, #ef4444 70%, #dc2626 100%) !important;
                color: #fff;
            }
            .pin-mixed-all .map-pin-tail { border-top: 9px solid #15803d; }
        `;
        document.head.appendChild(s);
    }

// getRetailerPinInfo moved outside

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
        const pinInfo = getRetailerPinInfo(ret);
        const pinClass = pinInfo.pinClass;
        const pinIcon = pinInfo.pinIcon;

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

function getRetailerPinInfo(ret) {
    let deliveredCount = 0;
    let partialCount = 0;
    let cancelledCount = 0;
    let pendingCount = 0;
    const totalOrders = ret.orders ? ret.orders.length : 0;

    ret.orders.forEach(o => {
        if (o.status === 'delivered') {
            deliveredCount++;
        } else if (o.status === 'partial') {
            partialCount++;
        } else if (o.status === 'cancelled') {
            cancelledCount++;
        } else {
            pendingCount++;
        }
    });

    const actionedCount = deliveredCount + partialCount + cancelledCount;
    if (pendingCount > 0 && actionedCount > 0) return { pinClass: 'pin-incomplete', pinIcon: 'fa-circle-exclamation' };
    if (pendingCount === totalOrders) return { pinClass: 'pin-pending', pinIcon: 'fa-clock' };
    if (deliveredCount === totalOrders) return { pinClass: 'pin-delivered', pinIcon: 'fa-check' };
    if (cancelledCount === totalOrders) return { pinClass: 'pin-cancelled', pinIcon: 'fa-circle-xmark' };
    if (partialCount === totalOrders) return { pinClass: 'pin-partial', pinIcon: 'fa-circle-half-stroke' };

    const hasDelivered = deliveredCount > 0;
    const hasPartial = partialCount > 0;
    const hasCancelled = cancelledCount > 0;

    if (hasDelivered && hasPartial && hasCancelled) return { pinClass: 'pin-mixed-all', pinIcon: 'fa-shuffle' };
    if (hasDelivered && hasCancelled) return { pinClass: 'pin-delivered-cancelled', pinIcon: 'fa-shuffle' };
    if (hasDelivered && hasPartial) return { pinClass: 'pin-delivered-partial', pinIcon: 'fa-shuffle' };
    if (hasPartial && hasCancelled) return { pinClass: 'pin-partial-cancelled', pinIcon: 'fa-shuffle' };

    return { pinClass: 'pin-pending', pinIcon: 'fa-clock' };
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

function changeQty(btn, amount, type, idx) {
    const parent = btn.closest('.product-item');
    if (!parent) return;
    const input = parent.querySelector(type === 'box' ? '.delivery-input-box' : '.delivery-input-pcs');
    if (!input || input.disabled) return;
    let val = parseInt(input.value) || 0;
    val = Math.max(0, val + amount);
    input.value = val;
    calcProgress(input, idx);
}

function openRetailerSheet(retailer, defaultIndex = 0) {
    currentRetailerObj = retailer;
    currentOrderIndex = defaultIndex;
    
    // Set Name & Subtitle
    document.getElementById('bsRetailerName').innerText = retailer.retailer_name || retailer.dealer_name || retailer.name;
    document.getElementById('bsRetailerSub').innerText = retailer.retailer_name ? retailer.dealer_name : 'খুচরা বিক্রেতা';
    
    // Update damage button icon & styling based on whether damage is recorded for this retailer
    updateDamageBtnState(retailer.has_damage);

    const tabsContainer = document.getElementById('bsCompanyTabs');
    tabsContainer.innerHTML = '';
    tabsContainer.className = "flex gap-6 overflow-x-auto pb-0 no-scrollbar border-b border-gray-100 px-4 pt-2 mb-4";
    
    const list = document.getElementById('bsProductsList');
    list.innerHTML = '';
    
    if (retailer.orders && retailer.orders.length > 1) {
        tabsContainer.classList.remove('hidden');
        retailer.orders.forEach((order, idx) => {
            const isSelected = idx === 0;
            const count = order.products ? order.products.length : 0;
            const isCancelled = order.status === 'cancelled';
            let tabClass = 'text-gray-500 pb-2 border-b-2 border-transparent transition hover:text-gray-700';
            if (isCancelled) {
                tabClass = isSelected ? 'text-red-600 pb-2 border-b-2 border-red-600 font-extrabold' : 'text-red-400 pb-2 border-b-2 border-transparent transition hover:text-red-500';
            } else if (isSelected) {
                tabClass = 'text-[#217346] pb-2 border-b-2 border-[#217346] font-extrabold';
            }
            
            tabsContainer.insertAdjacentHTML('beforeend', `
                <button onclick="selectCompanyOrder(${idx})" id="tab-order-${idx}"
                        class="whitespace-nowrap px-1 text-xs font-semibold ${tabClass}">
                    ${order.company_name} <span class="text-gray-400 ml-1 text-[10px]">(${count})</span>
                </button>
            `);
        });
    } else {
        tabsContainer.classList.add('hidden');
    }

    // Render all orders
    if (retailer.orders && retailer.orders.length > 0) {
        retailer.orders.forEach((order, orderIdx) => {
            let orderHtml = `<div id="order-group-${orderIdx}" class="order-group-container hidden">`;
            if (!order.products || order.products.length === 0) {
                orderHtml += `<div class="text-center py-6 text-sm text-gray-400 bg-gray-50 rounded-xl border border-gray-100"><i class="fa-solid fa-box-open mb-2 text-2xl text-gray-300"></i><br>এই অর্ডারে কোনো পণ্য পাওয়া যায়নি।</div>`;
            } else {
                orderHtml += `
                <div class="border border-gray-150 rounded-xl bg-white overflow-hidden max-h-[50vh] overflow-y-auto shadow-[0_2px_10px_-3px_rgba(0,0,0,0.05)]">
                    <!-- Clean Minimal Column Header -->
                    <div class="flex items-center text-[9px] text-gray-400 font-extrabold bg-[#fcfcfc] border-b border-gray-150 uppercase tracking-wider select-none sticky top-0 z-10">
                        <div class="flex-1 py-2 px-3">পণ্যের বিবরণ ও স্টক</div>
                        <div class="w-[165px] py-2 text-center shrink-0 border-l border-gray-100">ডেলিভারি পরিমাণ</div>
                    </div>
                    <div class="divide-y divide-gray-100 bg-white">
                `;
                
                order.products.forEach((p, idx) => {
                    const ppb = parseInt(p.pieces_per_box) || 1;
                    const boxTypeStr = (p.box_type || '').toString().trim().toLowerCase();
                    const pcsKeywords = ['pcs', 'pc', 'piece', 'pieces', 'পিস', 'পিছ'];
                    const isPcs = pcsKeywords.includes(boxTypeStr) || (ppb <= 1);

                    const qty = parseInt(p.quantity); // pieces dispatched on van

                    let initialDeliveredQty = p.delivered_quantity !== null ? parseInt(p.delivered_quantity) : qty;

                    const initialBoxes = Math.floor(initialDeliveredQty / ppb);
                    const initialPcs = initialDeliveredQty % ppb;

                    const vanStock = parseInt(vanStockMap[p.product_id]) || 0;
                    const isStockOk = vanStock >= qty;

                    orderHtml += `
                    <div class="product-item flex items-stretch divide-x divide-gray-100 text-xs animate-fadeIn border-b border-gray-100 last:border-b-0" data-price="${p.price || 0}" data-baseprice="${p.base_price || 0}">
                        <!-- Product & Stock Cell -->
                        <div class="flex-1 p-3 flex flex-col justify-center min-w-0 bg-white">
                            <div class="font-black text-gray-800 text-[11px] leading-snug break-words" title="${p.name}">${p.name}</div>
                            
                            <!-- Badges -->
                            <div class="text-[9px] mt-2 flex flex-wrap gap-1.5 items-center">
                                <span class="bg-gray-100 border border-gray-200 text-gray-600 px-1.5 py-0.5 rounded text-[10px] font-bold">৳${parseFloat(p.price || 0).toFixed(0)}</span>
                                <span class="${isStockOk ? 'bg-emerald-50 border border-emerald-100 text-emerald-700' : 'bg-red-50 border border-red-100 text-red-700'} px-1.5 py-0.5 rounded font-bold">স্টক: ${vanStock}</span>
                            </div>

                            <!-- Totals & OC -->
                            <div class="flex items-center gap-1.5 mt-2.5">
                                <span class="text-pink-600 font-black text-[11px]" id="itemPrice-${orderIdx}-${idx}">৳${(parseFloat(p.price || 0) * initialDeliveredQty).toFixed(0)}</span>
                                <span id="itemOc-${orderIdx}-${idx}" class="hidden"></span>
                            </div>
                        </div>

                        <!-- Delivered Input Cell -->
                        <div class="w-[165px] flex flex-col justify-center shrink-0 p-2 bg-white gap-2">
                            ${isPcs ? `
                                <input type="hidden" value="0" class="delivery-input-box"
                                    data-ppb="${ppb}" data-qty="${qty}" data-idx="${orderIdx}-${idx}" data-pid="${p.product_id}" data-price="${p.price || 0}">
                            ` : `
                                <div class="flex items-center justify-between gap-1 text-[10px] text-gray-500 font-bold">
                                    <span>বক্স</span>
                                    <div class="flex items-center border border-gray-200 bg-white rounded-md overflow-hidden shadow-sm">
                                        <button type="button" onclick="changeQty(this, -1, 'box', '${orderIdx}-${idx}')" class="qty-btn w-9 h-9 hover:bg-gray-100 active:bg-gray-200 flex items-center justify-center text-gray-600 font-black text-sm shrink-0 select-none border-r border-gray-200 transition-colors bg-transparent">-</button>
                                        <input type="number" min="0" value="${initialBoxes}"
                                            class="w-12 text-center font-black text-gray-800 outline-none bg-transparent delivery-input-box text-base"
                                            data-ppb="${ppb}" data-qty="${qty}" data-idx="${orderIdx}-${idx}" data-pid="${p.product_id}" data-price="${p.price || 0}"
                                            oninput="calcProgress(this, '${orderIdx}-${idx}')">
                                        <button type="button" onclick="changeQty(this, 1, 'box', '${orderIdx}-${idx}')" class="qty-btn w-9 h-9 flex items-center justify-center font-black text-sm shrink-0 select-none border-l border-[#217346] text-white active:scale-95 transition-all" style="background-color: #217346;">+</button>
                                    </div>
                                </div>
                            `}
                            
                            <div class="flex items-center justify-between gap-1 text-[10px] text-gray-500 font-bold">
                                <span>পিস</span>
                                <div class="flex items-center border border-gray-200 bg-white rounded-md overflow-hidden shadow-sm">
                                    <button type="button" onclick="changeQty(this, -1, 'pcs', '${orderIdx}-${idx}')" class="qty-btn w-9 h-9 hover:bg-gray-100 active:bg-gray-200 flex items-center justify-center text-gray-600 font-black text-sm shrink-0 select-none border-r border-gray-200 transition-colors bg-transparent">-</button>
                                    <input type="number" min="0" value="${isPcs ? initialDeliveredQty : initialPcs}"
                                        class="w-12 text-center font-black text-gray-800 outline-none bg-transparent delivery-input-pcs text-base"
                                        data-ppb="${ppb}" data-qty="${qty}" data-idx="${orderIdx}-${idx}" data-pid="${p.product_id}" data-price="${p.price || 0}"
                                        oninput="calcProgress(this, '${orderIdx}-${idx}')">
                                    <button type="button" onclick="changeQty(this, 1, 'pcs', '${orderIdx}-${idx}')" class="qty-btn w-9 h-9 flex items-center justify-center font-black text-sm shrink-0 select-none border-l border-[#217346] text-white active:scale-95 transition-all" style="background-color: #217346;">+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    `;
                });
                
                orderHtml += `
                    </div>
                </div>
                `;
            }
            orderHtml += `</div>`;
            list.insertAdjacentHTML('beforeend', orderHtml);
        });
        
        selectCompanyOrder(currentOrderIndex); // Load default order
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
                    btn.className = 'whitespace-nowrap px-4 py-2 text-xs font-bold bg-white text-red-655 border-t-2 border-t-red-600 border-x border-x-gray-300 rounded-t-md relative z-10 -mb-[1px]';
                } else {
                    btn.className = 'whitespace-nowrap px-4 py-2 text-xs font-bold bg-white text-[#217346] border-t-2 border-t-[#217346] border-x border-x-gray-300 rounded-t-md relative z-10 -mb-[1px]';
                }
            } else {
                if (isCancelled) {
                    btn.className = 'whitespace-nowrap px-4 py-2 text-xs font-semibold bg-[#f3f2f1] text-red-500 border-b border-b-gray-300 border-x border-x-gray-200 rounded-t-md opacity-80';
                } else {
                    btn.className = 'whitespace-nowrap px-4 py-2 text-xs font-semibold bg-[#f3f2f1] text-gray-600 border-b border-b-gray-300 border-x border-x-gray-200 rounded-t-md hover:bg-gray-100';
                }
            }
        });
    }

    document.getElementById('bsOrderTotal').innerText = 'Tk ' + parseFloat(order.total_amount || 0).toFixed(0);
    
    // Update order quantity stats
    const totalQty = order.products ? order.products.reduce((acc, p) => acc + parseInt(p.quantity), 0) : 0;
    document.getElementById('bsTotalQty').innerText = totalQty;

    const statusLabel = { 'in_transit': 'অপেক্ষমান', 'delivered': 'পরিশোধিত', 'partial': 'আংশিক/বাকি', 'cancelled': 'বাতিল' };
    const statusColor = { 'in_transit': '#3b82f6', 'delivered': '#16a34a', 'partial': '#f97316', 'cancelled': '#dc2626' };
    const bsStatus = document.getElementById('bsStatus');
    if (bsStatus) {
        bsStatus.innerText = statusLabel[order.status] || 'অপেক্ষমান';
        bsStatus.style.color = statusColor[order.status] || '#3b82f6';
    }

    const bsPartialInfo = document.getElementById('bsPartialInfo');
    if (bsPartialInfo) {
        if (order.status === 'partial') {
            bsPartialInfo.classList.remove('hidden');
            const paid = parseFloat(order.paid_amount || 0);
            
            // Calculate the actual total based on products' delivered quantity
            let actualTotal = 0;
            if (order.products) {
                order.products.forEach(p => {
                    const qty = parseInt(p.quantity);
                    let deliveredQty = p.delivered_quantity !== null ? parseInt(p.delivered_quantity) : qty;
                    actualTotal += (deliveredQty * parseFloat(p.price || 0));
                });
            } else {
                actualTotal = parseFloat(order.total_amount || 0);
            }
            
            const due = actualTotal - paid;
            document.getElementById('bsPaidAmount').innerText = '৳' + paid.toFixed(0);
            document.getElementById('bsDueAmount').innerText = '৳' + due.toFixed(0);
        } else {
            bsPartialInfo.classList.add('hidden');
        }
    }

    // Toggle visibility
    document.querySelectorAll('.order-group-container').forEach(div => div.classList.add('hidden'));
    const activeDiv = document.getElementById(`order-group-${orderIndex}`);
    if (activeDiv) {
        activeDiv.classList.remove('hidden');
        // Disable or enable inputs based on cancellation or delivery
        const inputs = activeDiv.querySelectorAll('input');
        inputs.forEach(input => {
            input.disabled = (order.status === 'cancelled' || order.status === 'delivered');
        });
    }

    // Dynamic Action Buttons
    const actionContainer = document.getElementById('bsActionButtons');
    if (actionContainer) {
        if (order.status === 'cancelled') {
            actionContainer.innerHTML = `<button onclick="redoCancelledOrder(${orderIndex})" class="w-full py-2.5 rounded-lg font-bold bg-amber-500 hover:bg-amber-600 text-white active:scale-[0.98] transition text-sm shadow-md flex items-center justify-center gap-2" <?= isset($isReturned) && $isReturned ? 'disabled style="opacity: 0.5; cursor: not-allowed;" title="DSR has returned, Action disabled"' : '' ?>><i class="fa-solid fa-rotate-left"></i> আবার চেষ্টা করুন</button>`;
        } else if (order.status === 'delivered') {
            actionContainer.innerHTML = '';
        } else if (order.status === 'partial') {
            actionContainer.innerHTML = `
                <button onclick="markDelivery('cancelled')" class="flex-1 py-2.5 rounded-lg font-bold text-white active:scale-[0.98] transition text-sm shadow-md" style="background-color: #d83b01;">বাতিল করুন</button>
                <button id="pay-btn" onclick="markDelivery('delivered')" class="flex-1 py-2.5 rounded-lg font-bold text-white active:scale-[0.98] transition text-sm shadow-md" style="background-color: #1e73be;">পরিশোধ করুন</button>
            `;
        } else {
            actionContainer.innerHTML = `
                <button onclick="markDelivery('cancelled')" class="flex-1 py-2.5 rounded-lg font-bold text-white active:scale-[0.98] transition text-sm shadow-md" style="background-color: #d83b01;">বাতিল করুন</button>
                <button id="pay-btn" onclick="markDelivery('delivered')" class="flex-1 py-2.5 rounded-lg font-bold text-white active:scale-[0.98] transition text-sm shadow-md" style="background-color: #1e73be;">পরিশোধ করুন</button>
            `;
        }
    }

    // Trigger initial calculation for ALL items in this group
    if (activeDiv) {
        const productItems = activeDiv.querySelectorAll('.product-item');
        productItems.forEach((pItem, pIdx) => {
            const bInput = pItem.querySelector('.delivery-input-box');
            if (bInput) {
                calcProgress(bInput, `${orderIndex}-${pIdx}`);
            }
        });
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

    const delQtyEl = document.getElementById(`delQty-${idx}`);
    if (delQtyEl) delQtyEl.innerText = totalDelivered;

    // Update item price display (total cost = qty * unit price)
    const itemPriceEl = document.getElementById(`itemPrice-${idx}`);
    const itemOcEl = document.getElementById(`itemOc-${idx}`);
    if (itemPriceEl) {
        const unitPrice = parseFloat(parent.getAttribute('data-price')) || 0;
        const basePrice = parseFloat(parent.getAttribute('data-baseprice')) || 0;
        itemPriceEl.innerText = 'Tk ' + (totalDelivered * unitPrice).toFixed(0);
        
        if (itemOcEl) {
            const oc = (unitPrice - basePrice) * totalDelivered;
            if (Math.round(oc) !== 0 && totalDelivered > 0) {
                itemOcEl.className = `text-[10px] font-bold px-1.5 py-0.5 rounded-md ${oc > 0 ? 'text-green-700 bg-green-100' : 'text-red-700 bg-red-100'}`;
                itemOcEl.innerText = `${oc > 0 ? '+' : ''}${Math.round(oc)}`;
                itemOcEl.classList.remove('hidden');
            } else {
                itemOcEl.classList.add('hidden');
            }
        }
    }

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
    let exceedsStock = false;
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
            
            const pid = bInp.getAttribute('data-pid') || pInp.getAttribute('data-pid');
            if (pid) {
                const vanStock = parseInt(vanStockMap[pid]) || 0;
                if (tQty > vanStock) {
                    exceedsStock = true;
                }
            }
        }
    });
    
    const bsGettingTotal = document.getElementById('bsGettingTotal');
    if (bsGettingTotal) bsGettingTotal.innerText = '৳' + gettingTotal.toFixed(0);

    const payBtn = document.getElementById('pay-btn');
    if (payBtn) {
        if (exceedsStock) {
            payBtn.disabled = true;
            payBtn.classList.add('opacity-50', 'cursor-not-allowed');
            payBtn.title = 'পর্যাপ্ত ভ্যান স্টক নেই';
        } else {
            payBtn.disabled = false;
            payBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            payBtn.removeAttribute('title');
        }
    }

    // Update due if partial info is visible
    const bsPartialInfo = document.getElementById('bsPartialInfo');
    if (bsPartialInfo && !bsPartialInfo.classList.contains('hidden') && currentRetailerObj && currentRetailerObj.orders) {
        const order = currentRetailerObj.orders.find(o => o.dispatch_id === currentDispatchId);
        if (order) {
            const paid = parseFloat(order.paid_amount || 0);
            let due = gettingTotal - paid;
            const bsDueAmount = document.getElementById('bsDueAmount');
            if (bsDueAmount) bsDueAmount.innerText = '৳' + due.toFixed(0);
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
    
    // Check if this order already has some paid amount
    let existingPaid = 0;
    if (currentRetailerObj && currentRetailerObj.orders) {
        const order = currentRetailerObj.orders.find(o => o.dispatch_id === currentDispatchId);
        if (order) existingPaid = parseFloat(order.paid_amount || 0);
    }
    const remainingDue = totalPayable - existingPaid;
    
    // Populate new structural info elements
    if (document.getElementById('paidRetailerName') && currentRetailerObj) {
        document.getElementById('paidRetailerName').innerText = currentRetailerObj.retailer_name || currentRetailerObj.dealer_name || currentRetailerObj.name;
        document.getElementById('paidTotalPayable').innerText = '৳' + totalPayable.toFixed(0);
        document.getElementById('paidAlreadyPaid').innerText = '৳' + existingPaid.toFixed(0);
        document.getElementById('paidRemainingDue').innerText = '৳' + remainingDue.toFixed(0);
    }
    
    document.getElementById('paidPaymentInput').value = remainingDue.toFixed(0);
    
    if (existingPaid > 0) {
        document.getElementById('paymentDueInfo').innerHTML = `ইতিমধ্যে পরিশোধিত: ৳${existingPaid.toFixed(0)} | বাকি পাওনা: ৳${remainingDue.toFixed(0)}`;
        document.getElementById('paymentDueInfo').className = 'text-xs font-bold text-amber-600 mb-4 h-5';
    } else {
        document.getElementById('paymentDueInfo').innerText = 'সম্পূর্ণ পরিশোধিত';
        document.getElementById('paymentDueInfo').className = 'text-xs font-bold text-green-500 mb-4 h-5';
    }
    
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
    let entered = parseFloat(el.value) || 0;
    const total = getSelectedOrderGettingTotal();
    
    let existingPaid = 0;
    if (currentRetailerObj && currentRetailerObj.orders) {
        const order = currentRetailerObj.orders.find(o => o.dispatch_id === currentDispatchId);
        if (order) existingPaid = parseFloat(order.paid_amount || 0);
    }
    const maxPayable = total - existingPaid;
    
    if (entered > maxPayable) {
        entered = maxPayable;
        el.value = entered.toFixed(0);
    }
    
    const due = maxPayable - entered;
    
    if (document.getElementById('paidRemainingDue')) {
        document.getElementById('paidRemainingDue').innerText = '৳' + due.toFixed(0);
    }
    
    const info = document.getElementById('paymentDueInfo');
    if (Math.round(due) > 0) {
        info.innerText = `বাকি পাওনা: ৳${Math.round(due)} (আংশিক পরিশোধ হবে)`;
        info.className = 'text-xs font-bold text-red-500 mb-4 h-5';
    } else if (Math.round(due) < 0) {
        info.innerText = `অতিরিক্ত পরিশোধ: ৳${Math.abs(Math.round(due))} (সমন্বয় করুন)`;
        info.className = 'text-xs font-bold text-orange-500 mb-4 h-5';
    } else {
        info.innerText = 'সম্পূর্ণ পরিশোধিত';
        info.className = 'text-xs font-bold text-green-500 mb-4 h-5';
    }
}

function submitPaidPayment() {
    const entered = parseFloat(document.getElementById('paidPaymentInput').value) || 0;
    const total = getSelectedOrderGettingTotal();
    
    // Get existing paid amount for this order (for cumulative calculation)
    let existingPaid = 0;
    if (currentRetailerObj && currentRetailerObj.orders) {
        const order = currentRetailerObj.orders.find(o => o.dispatch_id === currentDispatchId);
        if (order) existingPaid = parseFloat(order.paid_amount || 0);
    }
    const cumulativePaid = existingPaid + entered;
    
    let status = 'delivered';
    if (Math.round(cumulativePaid) < Math.round(total)) {
        status = 'partial';
    }
    
    closePaidPaymentModal();
    
    let paidAmounts = {};
    paidAmounts[currentDispatchId] = cumulativePaid;
    
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
        const pinInfo = getRetailerPinInfo(ret);
        const pinClass = pinInfo.pinClass;
        const pinIcon = pinInfo.pinIcon;

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

    // Validation: Check if van stock is sufficient before completing delivery (delivered or partial)
    if (status === 'delivered' || status === 'partial') {
        for (let i = 0; i < orders.length; i++) {
            const o = orders[i];
            const dispatchId = o.dispatch_id;
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

            for (let pid in deliveredItems) {
                const tQty = deliveredItems[pid];
                const vanStock = parseInt(vanStockMap[pid]) || 0;
                if (tQty > vanStock) {
                    const prod = o.products.find(pr => String(pr.product_id) === String(pid));
                    const prodName = prod ? prod.name : 'Product';
                    alert(`⚠️ Delivery cannot be completed!\nVan stock for "${prodName}" is ${vanStock}, but the retailer ordered/requested quantity is ${tQty}.`);
                    return;
                }
            }
        }
    }

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
                let hasPending = false;
                if (currentRetailerObj && currentRetailerObj.orders) {
                    hasPending = currentRetailerObj.orders.some(o => o.status === 'in_transit');
                }
                
                if (hasPending) {
                    openRetailerSheet(currentRetailerObj);
                    selectCompanyOrder(currentOrderIndex);
                } else {
                    closeBottomSheet();
                }
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
                    <div class="text-xs text-gray-500">Value: ৳${parseFloat(o.total_amount).toFixed(0)}</div>
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
                    <div class="text-xs text-gray-500">Value: ৳${parseFloat(o.total_amount).toFixed(0)}</div>
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
                        <div class="text-xs text-gray-500">Value: ৳${parseFloat(o.total_amount).toFixed(0)}</div>
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
    if (modal) modal.classList.remove('hidden');
}

function closeRetailerListModal() {
    const modal = document.getElementById('retailerListModal');
    if (modal) modal.classList.add('hidden');
}

function handleRetailerListClick(idx) {
    closeRetailerListModal();
    if (typeof orderedRetailers === 'undefined' || !orderedRetailers[idx]) return;
    const ret = orderedRetailers[idx];
    
    let shouldWarn = true;
    if (ret.orders && Array.isArray(ret.orders)) {
        ret.orders.forEach(o => {
            if (o.status !== 'delivered' && o.status !== 'cancelled') {
                shouldWarn = false;
            }
        });
    }
    
    if (typeof handleRetailerClick === 'function') {
        handleRetailerClick(ret, shouldWarn);
    }
}

<?php endif; // $hasDeliveries ?>
</script>

<!-- ══════════════════════════════════════════════════════
     READY SALE MODALS & NEARBY MAP (ALWAYS AVAILABLE)
═══════════════════════════════════════════════════════ -->

<!-- Ready Sale Modal (Excel Grid Style - Blue Brand Theme) -->
<div id="readySaleModal" class="fixed inset-0 flex items-center justify-center p-2 sm:p-4 bg-slate-950/75 backdrop-blur-md hidden transition-all duration-300" style="z-index: 99980 !important;">
  <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl flex flex-col max-h-[94vh] overflow-hidden border border-slate-200 animate-in fade-in zoom-in-95 duration-200">
    
    <!-- Modal Header (Blue Brand Gradient) -->
    <div class="bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-700 px-4 py-3.5 sm:px-5 flex items-center justify-between text-white shadow-md relative overflow-hidden">
      <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-white/10 rounded-full blur-xl pointer-events-none"></div>

      <div class="flex items-center gap-3 relative z-10">
        <div class="w-9 h-9 rounded-xl bg-white/20 backdrop-blur-xs text-white flex items-center justify-center text-lg border border-white/20 shadow-inner">
          <i class="fa-solid fa-bolt text-blue-100"></i>
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h3 class="font-extrabold text-sm sm:text-base tracking-tight leading-none text-white font-siliguri">Ready Sale Order Grid</h3>
            <span class="text-[9.5px] uppercase font-black px-2 py-0.5 rounded-full bg-blue-500/30 text-blue-100 border border-blue-300/30">Van Stock</span>
          </div>
          <p class="text-[11px] text-blue-100/90 font-medium mt-0.5 font-siliguri">স্প্রেডশীট গ্রিড স্টাইলে সরাসরি ভ্যান স্টক থেকে বিক্রয়</p>
        </div>
      </div>

      <button onclick="closeReadySaleModal()" class="w-8 h-8 rounded-full bg-white/15 hover:bg-white/25 flex items-center justify-center text-white transition active:scale-95 border border-white/20 relative z-10">
        <i class="fa-solid fa-xmark text-sm"></i>
      </button>
    </div>

    <!-- Modal Body -->
    <div class="p-3 sm:p-4 overflow-y-auto space-y-3 bg-slate-50 font-siliguri text-slate-800">
      
      <!-- Hidden Date & CSRF -->
      <input type="hidden" id="rs_csrf_token" value="<?= Helpers::csrfToken() ?>">
      <input type="hidden" id="rs_date" value="<?= $selectedDate ?? date('Y-m-d') ?>">

      <!-- Retailer Toolbar (Excel Header Form Bar) -->
      <div class="bg-white border border-slate-200 rounded-xl p-3 shadow-xs space-y-2">
        <div class="flex items-center justify-between">
          <label class="text-[11px] font-black text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-solid fa-store text-blue-600"></i> রিটেলার নির্বাচন <span class="text-rose-500">*</span>
          </label>
          <div class="flex items-center gap-1.5">
            <button type="button" onclick="openNearbyRetailerMapModal(20)" class="px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-xs font-bold flex items-center gap-1.5 border border-blue-200/80 active:scale-95 transition shadow-2xs whitespace-nowrap" title="২০ মিটার রিটেলার ম্যাপ">
              <i class="fa-solid fa-location-crosshairs text-blue-600"></i> ২০মি: ম্যাপ
            </button>
            <button type="button" onclick="openQuickAddRetailerModal()" class="px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold flex items-center gap-1 border border-emerald-200/80 active:scale-95 transition shadow-2xs whitespace-nowrap">
              <i class="fa-solid fa-plus text-emerald-600"></i> নতুন রিটেলার
            </button>
          </div>
        </div>

        <div class="relative">
          <select id="rs_retailer_id" class="w-full bg-slate-50/80 hover:bg-white border border-slate-300 rounded-xl px-3 py-2 text-xs sm:text-sm font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-600 transition shadow-2xs appearance-none">
            <option value="">-- রিটেলার পছন্দ করুন --</option>
            <?php foreach ($allRetailers ?? [] as $ret): ?>
              <option value="<?= $ret['id'] ?>" data-lat="<?= $ret['lat'] ?? '' ?>" data-lng="<?= $ret['lng'] ?? '' ?>"><?= htmlspecialchars($ret['name']) ?> (<?= htmlspecialchars($ret['phone'] ?? 'No Phone') ?>)</option>
            <?php endforeach; ?>
          </select>
          <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
            <i class="fa-solid fa-chevron-down text-xs"></i>
          </div>
        </div>
      </div>

      <!-- Excel Style Items Sheet Table -->
      <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
        <!-- Sheet Header Bar -->
        <div class="bg-slate-100/90 border-b border-slate-200 px-3 py-2 flex items-center justify-between">
          <span class="text-xs font-black text-slate-700 uppercase tracking-tight flex items-center gap-1.5">
            <i class="fa-solid fa-table-cells text-blue-600"></i> অর্ডারের আইটেম তালিকা (Order Grid)
          </span>
          <button type="button" onclick="addReadySaleRow()" class="text-xs font-extrabold text-blue-700 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1 rounded-lg border border-blue-200 active:scale-95 transition flex items-center gap-1.5 shadow-2xs">
            <i class="fa-solid fa-plus text-xs"></i> রো যোগ করুন (Add Row)
          </button>
        </div>

        <!-- Excel Table Grid -->
        <div class="overflow-x-auto max-h-[310px]">
          <table class="w-full text-left border-collapse min-w-[520px]" id="rs_excel_table">
            <thead>
              <tr class="bg-slate-100/70 text-[11px] font-black text-slate-700 uppercase tracking-tight border-b border-slate-200">
                <th class="p-2.5 border-r border-slate-200 w-[42%]">পণ্য / আইটেম (Product)</th>
                <th class="p-2.5 border-r border-slate-200 text-center w-[15%]">পরিমাণ (Qty)</th>
                <th class="p-2.5 border-r border-slate-200 text-right w-[16%]">মূল দাম (Trade)</th>
                <th class="p-2.5 border-r border-slate-200 text-right w-[17%]">বিক্রি মূল্য (Unit)</th>
                <th class="p-2.5 text-center w-[10%]">মুছুন</th>
              </tr>
            </thead>
            <tbody id="rs_products_container" class="divide-y divide-slate-200 text-xs">
              <!-- Excel Grid Dynamic Rows Inserted Here -->
            </tbody>
          </table>
        </div>
      </div>

      <!-- Excel Style Bottom Totals Toolbar -->
      <div class="bg-slate-900 text-white rounded-xl p-3 shadow-md border border-slate-800 font-mono text-xs">
        <div class="grid grid-cols-3 gap-2 text-center border-b border-slate-800 pb-2 mb-2 font-sans">
          <div>
            <span class="text-[10px] text-slate-400 uppercase font-medium block">মোট রো count</span>
            <span class="font-extrabold text-slate-200" id="rs_summary_items_count">0 টি</span>
          </div>
          <div>
            <span class="text-[10px] text-slate-400 uppercase font-medium block">লাইভ O/C</span>
            <span class="font-bold px-2 py-0.5 rounded bg-slate-800 text-slate-300 text-[11px] inline-block font-mono" id="rs_summary_oc">৳0.00</span>
          </div>
          <div>
            <span class="text-[10px] text-slate-400 uppercase font-medium block">পেমেন্ট স্টেটাস</span>
            <span class="font-bold text-emerald-400 text-[11px]">অন-দ্য-স্পট ক্যাশ</span>
          </div>
        </div>

        <div class="flex items-center justify-between px-1">
          <span class="text-slate-300 font-bold font-sans text-xs sm:text-sm">সর্বমোট মূল্য (Grand Total):</span>
          <span class="font-black text-blue-400 text-lg sm:text-xl font-mono tracking-tight" id="rs_summary_total">৳0.00</span>
        </div>
      </div>

    </div>

    <!-- Modal Footer (Blue Action Button) -->
    <div class="bg-slate-50 border-t border-slate-200 px-4 py-3 flex items-center justify-end gap-2 font-siliguri">
      <button type="button" onclick="closeReadySaleModal()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-lg text-xs transition">
        বাতিল
      </button>
      <button type="button" id="rs_submit_btn" onclick="submitReadySale()" class="px-5 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-extrabold rounded-lg text-xs sm:text-sm shadow-md shadow-blue-500/20 flex items-center gap-1.5 active:scale-95 transition border border-blue-500/30">
        <i class="fa-solid fa-check"></i> অর্ডার কনফার্ম (Save Order)
      </button>
    </div>

  </div>
</div>

<!-- Quick Add Retailer Modal -->
<div id="quickAddRetailerModal" class="fixed inset-0 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-md hidden transition-all duration-300" style="z-index: 99990 !important;">
  <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden border border-slate-200 p-4 space-y-3 animate-in fade-in zoom-in-95 duration-200 font-siliguri">
    <div class="flex items-center justify-between border-b border-slate-200 pb-2.5">
      <div class="flex items-center gap-2">
        <div class="w-7 h-7 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-xs">
          <i class="fa-solid fa-user-plus"></i>
        </div>
        <h4 class="font-extrabold text-slate-800 text-sm">নতুন রিটেলার যোগ করুন</h4>
      </div>
      <button type="button" onclick="closeQuickAddRetailerModal()" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition"><i class="fa-solid fa-xmark"></i></button>
    </div>
    
    <div class="space-y-2.5 text-left text-xs">
      <div>
        <label class="block font-bold text-slate-700 mb-1">রিটেলারের নাম <span class="text-rose-500">*</span></label>
        <input type="text" id="qr_name" class="w-full bg-slate-50/80 focus:bg-white border border-slate-300 rounded-lg px-3 py-2 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-600 transition" placeholder="দোকানের/মালিকের নাম">
      </div>
      <div>
        <label class="block font-bold text-slate-700 mb-1">ফোন নাম্বার</label>
        <input type="text" id="qr_phone" class="w-full bg-slate-50/80 focus:bg-white border border-slate-300 rounded-lg px-3 py-2 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-600 transition" placeholder="017xxxxxxxx">
      </div>
    </div>
    
    <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
      <button type="button" onclick="closeQuickAddRetailerModal()" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition">বাতিল</button>
      <button type="button" id="qr_save_btn" onclick="submitQuickAddRetailer()" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-extrabold rounded-lg shadow-sm transition">সেভ করুন</button>
    </div>
  </div>
</div>

<!-- Nearby Retailer Map Modal (20m Radius) -->
<div id="nearbyRetailerMapModal" class="fixed inset-0 flex items-center justify-center p-3 sm:p-4 bg-slate-950/75 backdrop-blur-md hidden transition-all duration-300" style="z-index: 99999 !important;">

  <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl flex flex-col h-[85vh] max-h-[650px] overflow-hidden border border-slate-200 animate-in fade-in zoom-in-95 duration-200 font-siliguri">
    
    <!-- Header -->
    <div class="bg-slate-900 px-4 py-3 flex items-center justify-between text-white shadow-md">
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center text-sm border border-blue-500/30">
          <i class="fa-solid fa-location-crosshairs"></i>
        </div>
        <div>
          <h4 class="font-extrabold text-sm leading-tight text-white">কাছের রিটেলার ম্যাপ</h4>
          <p class="text-[11px] text-slate-300 font-medium mt-0.5" id="nearbyRadiusLabel">আপনার অবস্থানের ২০ মিটার ব্যাসার্ধের মধ্যে</p>
        </div>
      </div>
      <button onclick="closeNearbyRetailerMapModal()" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 flex items-center justify-center text-slate-300 transition active:scale-95">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <!-- Radius Control Bar -->
    <div class="bg-slate-100 border-b border-slate-200 px-4 py-2 flex items-center justify-between gap-2 text-xs">
      <span class="font-bold text-slate-600">দূরত্ব ফিল্টার:</span>
      <div class="flex items-center gap-1">
        <button type="button" onclick="setNearbyRadius(20)" id="radiusBtn20" class="px-2.5 py-1 rounded-lg font-extrabold transition bg-blue-600 text-white shadow-xs">২০মি:</button>
        <button type="button" onclick="setNearbyRadius(50)" id="radiusBtn50" class="px-2.5 py-1 rounded-lg font-bold transition bg-white text-slate-700 border border-slate-200 hover:bg-slate-50">৫০মি:</button>
        <button type="button" onclick="setNearbyRadius(100)" id="radiusBtn100" class="px-2.5 py-1 rounded-lg font-bold transition bg-white text-slate-700 border border-slate-200 hover:bg-slate-50">১০০মি:</button>
        <button type="button" onclick="setNearbyRadius(999999)" id="radiusBtnAll" class="px-2.5 py-1 rounded-lg font-bold transition bg-white text-slate-700 border border-slate-200 hover:bg-slate-50">সব</button>
      </div>
    </div>

    <!-- Map Container -->
    <div class="relative flex-1 bg-slate-200 min-h-[220px]">
      <div id="nearbyMapCanvas" class="w-full h-full"></div>
      <div id="nearbyMapLoader" class="absolute inset-0 bg-white/85 backdrop-blur-xs z-10 flex flex-col items-center justify-center text-slate-600 text-xs font-bold gap-2">
        <i class="fa-solid fa-circle-notch fa-spin text-2xl text-blue-600"></i>
        <span>আপনার বর্তমান লোকেশন পাওয়ার চেষ্টা চলছে...</span>
      </div>
    </div>

    <!-- Retailer List Sheet at bottom of map modal -->
    <div class="bg-white border-t border-slate-200 p-3 max-h-[200px] overflow-y-auto" id="nearbyRetailersList">
      <!-- Dynamic list of nearby retailers -->
    </div>

  </div>
</div>

<script>
// ── Ready Sale by DSR Global Logic ───────────────────────────
let vanStockProducts = [];

async function openReadySaleModal() {
    const modal = document.getElementById('readySaleModal');
    const container = document.getElementById('rs_products_container');
    container.innerHTML = '<tr><td colspan="5" class="text-center py-8 text-slate-400 text-xs font-medium"><i class="fa-solid fa-circle-notch fa-spin mr-2 text-blue-600"></i>ভ্যান স্টক থেকে প্রোডাক্ট লোড হচ্ছে...</td></tr>';
    
    modal.classList.remove('hidden');

    try {
        const date = document.getElementById('rs_date').value || '<?= $selectedDate ?? date('Y-m-d') ?>';
        const res = await fetch('<?= url('dsr/api/van-stock') ?>?date=' + date);
        const data = await res.json();
        
        if (data.success && data.items) {
            vanStockProducts = data.items;
        } else {
            vanStockProducts = [];
        }

        container.innerHTML = '';
        if (vanStockProducts.length === 0) {
            container.innerHTML = '<tr><td colspan="5" class="p-4 bg-rose-50 text-rose-700 text-center text-xs font-bold"><i class="fa-solid fa-triangle-exclamation mr-1.5"></i>আপনার ভ্যানে বর্তমানে কোনো অবশিষ্টাংশ প্রোডাক্ট নেই।</td></tr>';
        } else {
            addReadySaleRow();
        }
        calculateRSTotals();
    } catch (err) {
        container.innerHTML = '<tr><td colspan="5" class="p-4 bg-rose-50 text-rose-700 text-center text-xs font-bold">ভ্যান স্টক লোড করতে ব্যর্থ হয়েছে।</td></tr>';
    }
}

function closeReadySaleModal() {
    document.getElementById('readySaleModal').classList.add('hidden');
}

function addReadySaleRow() {
    const container = document.getElementById('rs_products_container');
    const rowId = 'rs_row_' + Date.now() + '_' + Math.random().toString(36).substr(2, 4);

    let productOptionsHtml = '<option value="">-- প্রোডাক্ট নির্বাচন করুন --</option>';
    vanStockProducts.forEach(p => {
        productOptionsHtml += `<option value="${p.product_id}" data-baseprice="${p.base_price}" data-avail="${p.available_qty}" data-ppb="${p.pieces_per_box}" data-name="${p.product_name}" data-lotid="${p.lot_id || ''}">${p.product_name} (Avail: ${p.available_qty} Pcs | Base: ৳${p.base_price})</option>`;
    });

    const rowHtml = `
        <tr id="${rowId}" class="rs-product-row hover:bg-blue-50/30 transition-colors border-b border-slate-200">
          <!-- Product Selector Cell -->
          <td class="p-2.5 border-r border-slate-200 align-middle">
            <div class="relative">
              <select class="rs-prod-select w-full bg-slate-50/90 hover:bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs font-bold text-slate-800 outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-600 transition appearance-none" onchange="onRSProductChange(this)">
                ${productOptionsHtml}
              </select>
              <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                <i class="fa-solid fa-chevron-down text-[9px]"></i>
              </div>
            </div>
            <div class="mt-1 flex items-center justify-between px-0.5">
              <span class="rs-avail-badge text-[9.5px] font-bold text-slate-500">Avail: -</span>
              <span class="rs-oc-badge font-mono text-[10px] font-bold text-slate-500">O/C: ৳0.00</span>
            </div>
          </td>

          <!-- Quantity Cell -->
          <td class="p-2.5 border-r border-slate-200 align-middle text-center">
            <input type="number" min="1" value="1" class="rs-qty w-full max-w-[70px] bg-white border border-slate-300 focus:border-blue-600 rounded-lg px-2 py-1 text-center font-bold text-slate-800 outline-none focus:ring-2 focus:ring-blue-500/30 transition shadow-2xs" oninput="calculateRSTotals()">
          </td>

          <!-- Base Trade Price Cell -->
          <td class="p-2.5 border-r border-slate-200 align-middle text-right font-mono font-bold text-slate-700 bg-slate-50/50">
            <span class="rs-base-price-label">৳0.00</span>
          </td>

          <!-- Unit Selling Price Cell -->
          <td class="p-2.5 border-r border-slate-200 align-middle text-right">
            <input type="number" step="0.01" min="0" value="0.00" class="rs-unit-price w-full max-w-[85px] bg-blue-50/60 focus:bg-white border border-blue-300 focus:border-blue-600 rounded-lg px-2 py-1 text-right font-mono font-bold text-blue-900 outline-none focus:ring-2 focus:ring-blue-500/30 transition shadow-2xs" oninput="calculateRSTotals()">
            <div class="text-[10px] text-slate-500 font-sans mt-0.5">টোটাল: <span class="rs-line-total font-mono font-bold text-blue-700">৳0.00</span></div>
          </td>

          <!-- Action Cell -->
          <td class="p-2.5 align-middle text-center">
            <button type="button" onclick="removeRSRow('${rowId}')" class="w-7 h-7 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg flex items-center justify-center text-xs active:scale-95 transition border border-rose-200 mx-auto shadow-2xs" title="রো মুছে ফেলুন">
              <i class="fa-solid fa-trash-can"></i>
            </button>
          </td>
        </tr>
    `;

    container.insertAdjacentHTML('beforeend', rowHtml);
}

function removeRSRow(rowId) {
    const el = document.getElementById(rowId);
    if (el) el.remove();
    calculateRSTotals();
}

function onRSProductChange(selectEl) {
    const row = selectEl.closest('.rs-product-row');
    const selectedOpt = selectEl.options[selectEl.selectedIndex];
    const availBadge = row.querySelector('.rs-avail-badge');
    
    if (!selectedOpt || !selectedOpt.value) {
        row.querySelector('.rs-base-price-label').innerText = '৳0.00';
        row.querySelector('.rs-base-price-label').dataset.val = '0';
        row.querySelector('.rs-unit-price').value = '0.00';
        row.querySelector('.rs-qty').max = '';
        if (availBadge) {
            availBadge.innerText = 'Avail: -';
            availBadge.className = 'rs-avail-badge text-[9.5px] font-bold text-slate-500';
        }
        calculateRSTotals();
        return;
    }

    const basePrice = parseFloat(selectedOpt.dataset.baseprice || 0);
    const availQty = parseInt(selectedOpt.dataset.avail || 0);

    row.querySelector('.rs-base-price-label').innerText = '৳' + basePrice.toFixed(2);
    row.querySelector('.rs-base-price-label').dataset.val = basePrice;
    row.querySelector('.rs-unit-price').value = basePrice.toFixed(2);
    row.querySelector('.rs-qty').max = availQty;
    
    if (availBadge) {
        availBadge.innerText = `Avail: ${availQty} Pcs`;
        if (availQty <= 5) {
            availBadge.className = 'rs-avail-badge text-[9.5px] font-bold text-amber-600';
        } else {
            availBadge.className = 'rs-avail-badge text-[9.5px] font-bold text-emerald-600';
        }
    }

    if (parseInt(row.querySelector('.rs-qty').value) > availQty) {
        row.querySelector('.rs-qty').value = availQty;
    }

    calculateRSTotals();
}

function calculateRSTotals() {
    const rows = document.querySelectorAll('.rs-product-row');
    let grandTotal = 0;
    let grandOC = 0;
    let selectedItemCount = 0;

    rows.forEach(row => {
        const select = row.querySelector('.rs-prod-select');
        const selectedOpt = select ? select.options[select.selectedIndex] : null;
        if (!selectedOpt || !selectedOpt.value) return;

        selectedItemCount++;
        const qtyInput = row.querySelector('.rs-qty');
        const maxAvail = parseInt(selectedOpt.dataset.avail || 99999);
        let qty = parseInt(qtyInput.value) || 0;
        if (qty > maxAvail) {
            qty = maxAvail;
            qtyInput.value = qty;
        }

        const basePrice = parseFloat(selectedOpt.dataset.baseprice || 0);
        const unitPrice = parseFloat(row.querySelector('.rs-unit-price').value) || 0;

        const lineTotal = qty * unitPrice;
        const lineOC = (unitPrice - basePrice) * qty;

        grandTotal += lineTotal;
        grandOC += lineOC;

        row.querySelector('.rs-line-total').innerText = '৳' + lineTotal.toFixed(2);
        
        const ocBadge = row.querySelector('.rs-oc-badge');
        const sign = lineOC > 0 ? '+' : '';
        ocBadge.innerText = `O/C: ${sign}৳` + lineOC.toFixed(2);

        if (lineOC > 0) {
            ocBadge.className = 'rs-oc-badge font-mono text-[10px] font-bold text-emerald-700';
        } else if (lineOC < 0) {
            ocBadge.className = 'rs-oc-badge font-mono text-[10px] font-bold text-rose-700';
        } else {
            ocBadge.className = 'rs-oc-badge font-mono text-[10px] font-bold text-slate-500';
        }
    });

    const itemsCountEl = document.getElementById('rs_summary_items_count');
    if (itemsCountEl) itemsCountEl.innerText = selectedItemCount + ' টি';

    document.getElementById('rs_summary_total').innerText = '৳' + grandTotal.toFixed(2);
    
    const ocSummaryEl = document.getElementById('rs_summary_oc');
    const signSum = grandOC > 0 ? '+' : '';
    ocSummaryEl.innerText = `O/C: ${signSum}৳` + grandOC.toFixed(2);

    if (grandOC > 0) {
        ocSummaryEl.className = 'font-bold px-1.5 py-0.5 rounded bg-emerald-900/80 text-emerald-300 text-[11px] inline-block font-mono border border-emerald-700';
    } else if (grandOC < 0) {
        ocSummaryEl.className = 'font-bold px-1.5 py-0.5 rounded bg-rose-900/80 text-rose-300 text-[11px] inline-block font-mono border border-rose-700';
    } else {
        ocSummaryEl.className = 'font-bold px-1.5 py-0.5 rounded bg-slate-800 text-slate-300 text-[11px] inline-block font-mono';
    }
}

async function submitReadySale() {
    const retailerId = document.getElementById('rs_retailer_id').value;
    if (!retailerId) {
        showToast('⚠️ রিটেলার সিলেক্ট করুন!');
        return;
    }

    const rows = document.querySelectorAll('.rs-product-row');
    const items = [];

    rows.forEach(row => {
        const select = row.querySelector('.rs-prod-select');
        const pid = select ? select.value : 0;
        if (!pid) return;

        const selectedOpt = select.options[select.selectedIndex];
        const lotId = selectedOpt ? selectedOpt.dataset.lotid : null;
        const qty = parseInt(row.querySelector('.rs-qty').value) || 0;
        const unitPrice = parseFloat(row.querySelector('.rs-unit-price').value) || 0;

        if (qty > 0 && unitPrice >= 0) {
            items.push({
                product_id: parseInt(pid),
                lot_id: lotId ? parseInt(lotId) : null,
                qty: qty,
                unit_price: unitPrice
            });
        }
    });

    if (items.length === 0) {
        showToast('⚠️ অন্তত একটি সঠিক প্রোডাক্ট সিলেক্ট করুন!');
        return;
    }

    const submitBtn = document.getElementById('rs_submit_btn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> প্রসেসিং হচ্ছে...';

    try {
        const csrfToken = document.getElementById('rs_csrf_token').value;
        const date = document.getElementById('rs_date').value || '<?= $selectedDate ?? date('Y-m-d') ?>';

        const formData = new URLSearchParams();
        formData.append('csrf_token', csrfToken);
        formData.append('date', date);
        formData.append('retailer_id', retailerId);
        formData.append('items', JSON.stringify(items));

        const res = await fetch('<?= url('dsr/ready-sale/store') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        });

        const data = await res.json();
        if (data.success) {
            showToast('✅ ' + (data.message || 'রেডি সেল সফলভাবে সম্পন্ন হয়েছে!'));
            closeReadySaleModal();
            setTimeout(() => location.reload(), 900);
        } else {
            showToast('❌ ' + (data.message || 'রেডি সেল জমা দিতে ব্যর্থ হয়েছে!'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-check"></i> সেভ করুন (Submit)';
        }
    } catch (err) {
        showToast('❌ সার্ভিস কানেকশন সমস্যা।');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fa-solid fa-check"></i> সেভ করুন (Submit)';
    }
}

// Quick Add Retailer Handlers
function openQuickAddRetailerModal() {
    document.getElementById('quickAddRetailerModal').classList.remove('hidden');
    document.getElementById('qr_name').focus();
}

function closeQuickAddRetailerModal() {
    document.getElementById('quickAddRetailerModal').classList.add('hidden');
}

async function submitQuickAddRetailer() {
    const name = document.getElementById('qr_name').value.trim();
    const phone = document.getElementById('qr_phone').value.trim();

    if (!name) {
        showToast('⚠️ রিটেলারের নাম দিন!');
        return;
    }

    const saveBtn = document.getElementById('qr_save_btn');
    saveBtn.disabled = true;
    saveBtn.innerText = 'সেভ হচ্ছে...';

    try {
        const res = await fetch('<?= url('dsr/api/retailers/store') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name: name, phone: phone })
        });
        const data = await res.json();
        if (data.success && data.id) {
            showToast('✅ রিটেলার যুক্ত হয়েছে!');
            const select = document.getElementById('rs_retailer_id');
            const newOpt = document.createElement('option');
            newOpt.value = data.id;
            newOpt.text = name + (phone ? ` (${phone})` : '');
            newOpt.selected = true;
            select.appendChild(newOpt);
            closeQuickAddRetailerModal();
            document.getElementById('qr_name').value = '';
            document.getElementById('qr_phone').value = '';
        } else {
            showToast('❌ ' + (data.message || 'রিটেলার যুক্ত করতে ব্যর্থ।'));
        }
    } catch (err) {
        showToast('❌ সার্ভিস সমস্যা।');
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerText = 'সেভ করুন';
    }
}

// ── Nearby Retailer Map (20m Radius) ─────────────────────────
const allRetailersList = <?= json_encode($allRetailers ?? []) ?>;
let nearbyMap = null;
let nearbyUserMarker = null;
let nearbyCircle = null;
let nearbyMarkers = [];
let currentNearbyRadius = 20;
let currentDsrLat = null;
let currentDsrLng = null;

function openNearbyRetailerMapModal(radius = 20) {
    currentNearbyRadius = radius;
    const modal = document.getElementById('nearbyRetailerMapModal');
    const loader = document.getElementById('nearbyMapLoader');
    loader.classList.remove('hidden');
    modal.classList.remove('hidden');

    updateRadiusButtonsUI(radius);

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                currentDsrLat = pos.coords.latitude;
                currentDsrLng = pos.coords.longitude;
                loader.classList.add('hidden');
                initNearbyMap(currentDsrLat, currentDsrLng, currentNearbyRadius);
            },
            (err) => {
                loader.classList.add('hidden');
                currentDsrLat = 23.8103;
                currentDsrLng = 90.4125;
                if (allRetailersList.length > 0 && allRetailersList[0].lat && allRetailersList[0].lng) {
                    currentDsrLat = parseFloat(allRetailersList[0].lat);
                    currentDsrLng = parseFloat(allRetailersList[0].lng);
                }
                showToast('⚠️ আপনার লোকেশন অ্যাক্সেস করা যায়নি, ডিফল্ট লোকেশন ব্যবহার করা হচ্ছে।');
                initNearbyMap(currentDsrLat, currentDsrLng, currentNearbyRadius);
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    } else {
        loader.classList.add('hidden');
        showToast('⚠️ ব্রাউজারে Geolocation সাপোর্ট নেই।');
    }
}

function closeNearbyRetailerMapModal() {
    document.getElementById('nearbyRetailerMapModal').classList.add('hidden');
}

function updateRadiusButtonsUI(radius) {
    const btns = { 20: 'radiusBtn20', 50: 'radiusBtn50', 100: 'radiusBtn100', 999999: 'radiusBtnAll' };
    Object.keys(btns).forEach(r => {
        const btn = document.getElementById(btns[r]);
        if (btn) {
            if (parseInt(r) === radius) {
                btn.className = 'px-2.5 py-1 rounded-lg font-bold transition bg-blue-600 text-white shadow-sm';
            } else {
                btn.className = 'px-2.5 py-1 rounded-lg font-bold transition bg-white text-gray-700 border border-gray-200';
            }
        }
    });

    const label = document.getElementById('nearbyRadiusLabel');
    if (label) {
        if (radius >= 999999) {
            label.innerText = 'সকল নিবন্ধিত রিটেলার';
        } else {
            label.innerText = `আপনার অবস্থানের ${radius} মিটার ব্যাসার্ধের মধ্যে`;
        }
    }
}

function setNearbyRadius(radius) {
    currentNearbyRadius = radius;
    updateRadiusButtonsUI(radius);
    if (currentDsrLat && currentDsrLng) {
        renderNearbyMapItems(currentDsrLat, currentDsrLng, currentNearbyRadius);
    }
}

function getDistanceInMeters(lat1, lon1, lat2, lon2) {
    if (!lat1 || !lon1 || !lat2 || !lon2) return Infinity;
    const R = 6371000;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

function initNearbyMap(userLat, userLng, radius) {
    const canvas = document.getElementById('nearbyMapCanvas');
    if (!canvas) return;

    if (!nearbyMap) {
        nearbyMap = L.map(canvas, { zoomControl: false }).setView([userLat, userLng], 19);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 21,
            attribution: 'Happy Bangladesh'
        }).addTo(nearbyMap);
    } else {
        nearbyMap.setView([userLat, userLng], 19);
        setTimeout(() => nearbyMap.invalidateSize(), 200);
    }

    renderNearbyMapItems(userLat, userLng, radius);
}

function renderNearbyMapItems(userLat, userLng, radius) {
    if (!nearbyMap) return;

    nearbyMarkers.forEach(m => nearbyMap.removeLayer(m));
    nearbyMarkers = [];
    if (nearbyUserMarker) nearbyMap.removeLayer(nearbyUserMarker);
    if (nearbyCircle) nearbyMap.removeLayer(nearbyCircle);

    const userIcon = L.divIcon({
        className: 'nearby-user-pin',
        html: `<div class="w-6 h-6 bg-blue-600 border-2 border-white rounded-full shadow-lg flex items-center justify-center text-white text-xs animate-pulse"><i class="fa-solid fa-person-walking"></i></div>`,
        iconSize: [24, 24],
        iconAnchor: [12, 12]
    });
    nearbyUserMarker = L.marker([userLat, userLng], { icon: userIcon }).addTo(nearbyMap);

    if (radius < 999999) {
        nearbyCircle = L.circle([userLat, userLng], {
            radius: radius,
            color: '#2563eb',
            fillColor: '#3b82f6',
            fillOpacity: 0.15,
            weight: 2,
            dashArray: '4, 4'
        }).addTo(nearbyMap);
    }

    const nearbyList = [];
    allRetailersList.forEach(r => {
        const rLat = parseFloat(r.lat);
        const rLng = parseFloat(r.lng);
        if (isNaN(rLat) || isNaN(rLng) || rLat === 0 || rLng === 0) return;

        const dist = getDistanceInMeters(userLat, userLng, rLat, rLng);
        if (dist <= radius) {
            nearbyList.push({ ...r, distMeters: dist });

            const pinIcon = L.divIcon({
                className: 'nearby-ret-pin',
                html: `<div class="bg-amber-500 hover:bg-amber-600 text-white font-bold text-[10px] px-2 py-1 rounded-full shadow-md border border-white whitespace-nowrap flex items-center gap-1 cursor-pointer"><i class="fa-solid fa-store"></i> ${r.name} (${Math.round(dist)}m)</div>`,
                iconSize: [100, 24],
                iconAnchor: [50, 12]
            });
            const m = L.marker([rLat, rLng], { icon: pinIcon }).addTo(nearbyMap);
            m.on('click', () => selectNearbyRetailer(r.id));
            nearbyMarkers.push(m);
        }
    });

    nearbyList.sort((a, b) => a.distMeters - b.distMeters);

    const listContainer = document.getElementById('nearbyRetailersList');
    if (nearbyList.length === 0) {
        listContainer.innerHTML = `
            <div class="text-center py-4 text-gray-500 text-xs font-medium">
                <i class="fa-solid fa-circle-info text-amber-500 mr-1"></i> 
                ${radius < 999999 ? `${radius} মিটারের মধ্যে কোনো নিবন্ধিত রিটেলার পাওয়া যায়নি।` : 'কোনো রিটেলার পাওয়া যায়নি।'}
                <button type="button" onclick="setNearbyRadius(50)" class="block mx-auto mt-2 text-blue-600 font-bold underline">৫০ মিটারে চেক করুন</button>
            </div>
        `;
    } else {
        let listHtml = `<div class="text-[11px] font-bold text-gray-500 mb-2 uppercase tracking-wider">পাওয়ার রিটেলারসমূহ (${nearbyList.length}):</div><div class="space-y-1.5">`;
        nearbyList.forEach(r => {
            listHtml += `
                <div onclick="selectNearbyRetailer('${r.id}')" class="flex items-center justify-between p-2.5 bg-gray-50 hover:bg-amber-50 rounded-xl border border-gray-200 hover:border-amber-300 cursor-pointer transition">
                  <div class="flex items-center gap-2 min-w-0">
                    <div class="w-7 h-7 rounded-lg bg-amber-500 text-white flex items-center justify-center text-xs flex-shrink-0">
                      <i class="fa-solid fa-store"></i>
                    </div>
                    <div class="truncate">
                      <div class="text-xs font-bold text-gray-800 truncate">${r.name}</div>
                      <div class="text-[10px] text-gray-500 truncate">${r.phone || 'No Phone'} ${r.address ? '• ' + r.address : ''}</div>
                    </div>
                  </div>
                  <div class="flex items-center gap-2 flex-shrink-0">
                    <span class="text-[11px] font-mono font-bold text-blue-600 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded-lg">${Math.round(r.distMeters)}m দূরে</span>
                    <i class="fa-solid fa-circle-check text-amber-500 text-sm"></i>
                  </div>
                </div>
            `;
        });
        listHtml += `</div>`;
        listContainer.innerHTML = listHtml;
    }
}

function selectNearbyRetailer(retailerId) {
    const select = document.getElementById('rs_retailer_id');
    if (select) {
        select.value = retailerId;
    }
    closeNearbyRetailerMapModal();
    showToast('✅ রিটেলার সিলেক্ট করা হয়েছে!');
}
</script>

<style>
@keyframes fadeInUp {
    from { opacity: 0; transform: translate(-50%, 12px); }
    to   { opacity: 1; transform: translate(-50%, 0); }
}
</style>

