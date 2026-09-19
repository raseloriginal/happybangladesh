<?php 
$pageTitle = 'অর্ডার ইতিহাস'; 

// Helper function to truncate retailer name to 2 words
$truncateName = function($name) {
    $words = preg_split('/\s+/', trim($name));
    if (count($words) > 2) {
        $truncated = implode(' ', array_slice($words, 0, 2)) . '..';
        return [
            'is_truncated' => true,
            'short' => $truncated,
            'full' => $name
        ];
    }
    return [
        'is_truncated' => false,
        'short' => $name,
        'full' => $name
    ];
};
?>

<style>
  .font-siliguri {
    font-family: 'Hind Siliguri', 'Inter', sans-serif;
  }
  /* Remove arrows from number inputs for clean pill look */
  input[type=number]::-webkit-inner-spin-button, 
  input[type=number]::-webkit-outer-spin-button { 
    -webkit-appearance: none; 
    margin: 0; 
  }
  input[type=number] {
    -moz-appearance: textfield;
  }
</style>

<div class="p-3 sm:p-5 space-y-4 pb-4 max-w-5xl mx-auto font-siliguri text-slate-800 print:p-0 print:max-w-none print:bg-white">

  <!-- Toast Notification Container (Over everything, top-left) -->
  <div id="toastContainer" class="fixed top-5 left-5 space-y-2 pointer-events-none" style="z-index: 999999 !important;"></div>

  <!-- Premium Minimal Header Card -->
  <div class="bg-white/95 backdrop-blur-md px-4 py-3 sm:px-6 sm:py-4 rounded-2xl border border-slate-200/60 shadow-2xs flex items-center justify-between gap-3 print:shadow-none print:border-none print:p-0">
    <div class="flex items-center gap-3">
      <a href="<?= url('sr/dashboard') ?>" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 transition-all duration-200 flex items-center justify-center text-slate-700 shadow-2xs active:scale-95 print:hidden">
        <i class="fa-solid fa-arrow-left text-sm"></i>
      </a>
      <h1 class="text-xl sm:text-2xl font-bold text-slate-900 leading-tight tracking-tight">
        অর্ডার ইতিহাস
      </h1>
    </div>
    
    <!-- Header Controls (Date Picker & Print Icon) -->
    <div class="flex items-center gap-2 print:hidden">
      <?php if ($period !== 'all'): ?>
        <a href="<?= url('sr/orders') ?>?period=all" class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center text-xs hover:bg-rose-100 transition active:scale-95" title="ফিল্টার মুছুন">
          <i class="fa-solid fa-xmark"></i>
        </a>
      <?php endif; ?>
      
      <form method="GET" action="<?= url('sr/orders') ?>" id="dateForm" class="relative flex items-center">
        <input type="hidden" name="period" value="custom">
        <label for="dateFromInput" class="cursor-pointer font-bold text-slate-700 hover:text-slate-900 hover:bg-slate-150 transition flex items-center justify-center bg-slate-100 rounded-xl w-9 h-9">
          <i class="fa-regular fa-calendar text-slate-600 text-sm"></i>
        </label>
        <input type="date" id="dateFromInput" name="from" value="<?= h($from ?? date('Y-m-d')) ?>" 
               onchange="document.getElementById('dateToInput').value = this.value; SRLoader.start(); document.getElementById('dateForm').submit();" 
               class="absolute opacity-0 pointer-events-auto inset-0 w-full h-full cursor-pointer">
        <input type="hidden" id="dateToInput" name="to" value="<?= h($to ?? date('Y-m-d')) ?>">
      </form>

      <button type="button" onclick="window.print()" class="w-9 h-9 rounded-xl bg-slate-900 text-white flex items-center justify-center hover:bg-slate-800 transition active:scale-95 shadow-sm" title="প্রিন্ট করুন">
        <i class="fa-solid fa-print text-sm"></i>
      </button>
    </div>
  </div>

  <!-- Minimal Table Container -->
  <div class="bg-white rounded-2xl border border-slate-200/80 shadow-3xs overflow-hidden print:border-slate-300">
    <table class="w-full text-left border-collapse table-fixed min-w-0 font-sans" id="ordersTable">
      <thead>
        <tr class="border-b border-slate-200 text-xs text-slate-800 font-bold tracking-tight bg-slate-50">
          <th class="p-3 bg-slate-50/80 border-r border-slate-200/50 w-[48%] font-siliguri">
            দোকান / কাস্টমার
          </th>
          <th class="p-3 bg-slate-50/80 text-right border-r border-slate-200/50 w-[27%] font-siliguri">
            মোট টাকা
          </th>
          <th class="p-3 bg-slate-50/80 text-center w-[25%] font-siliguri">
            অ্যাকশন
          </th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 font-sans" id="tableBody">
        <?php 
          $grandTotalAmount = 0;
          $grandTotalOC = 0;
          if (empty($items)): 
        ?>
          <tr id="emptyRow">
            <td colspan="3" class="p-12 text-center text-slate-400 bg-white font-siliguri">
              <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-300 flex items-center justify-center text-xl mx-auto mb-2"><i class="fa-solid fa-box-open"></i></div>
              <span class="text-xs font-medium">কোনো অর্ডারের তথ্য পাওয়া যায়নি।</span>
            </td>
          </tr>
        <?php else: ?>
          <?php 
            foreach ($items as $ord): 
              $grandTotalAmount += (float)$ord['total_amount'];
              // Calculate O/C for this order
              $orderOC = 0;
              if (!empty($ord['products'])) {
                foreach ($ord['products'] as $_p) {
                  $orderOC += ((float)($_p['unit_price'] ?? 0) - (float)($_p['base_price'] ?? 0)) * (int)($_p['quantity'] ?? 0);
                }
              }
              $grandTotalOC += $orderOC;
              $rName = !empty($ord['retailer_name']) ? $ord['retailer_name'] : (!empty($ord['dealer_name']) ? $ord['dealer_name'] : 'সাধারণ কাস্টমার');
              $rPhone = !empty($ord['retailer_phone']) ? $ord['retailer_phone'] : 'N/A';
            ?>
            <tr class="retailer-order-row hover:bg-slate-50/40 transition-colors" id="order-row-<?= $ord['id'] ?>">
              
              <!-- Retailer Name & Meta Info Cell -->
              <td class="p-3 border-r border-slate-100 align-middle bg-white overflow-hidden">
                <div class="min-w-0">
                  <?php 
                    $nameInfo = $truncateName($rName); 
                    if ($nameInfo['is_truncated']):
                  ?>
                    <div class="font-bold text-slate-800 text-xs sm:text-sm leading-snug cursor-pointer select-none break-words font-siliguri"
                         onclick="toggleRetailerName(this, '<?= htmlspecialchars($nameInfo['full'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($nameInfo['short'], ENT_QUOTES, 'UTF-8') ?>')">
                      <?= h($nameInfo['short']) ?>
                    </div>
                  <?php else: ?>
                    <div class="font-bold text-slate-800 text-xs sm:text-sm leading-snug break-words font-siliguri">
                      <?= h($rName) ?>
                    </div>
                  <?php endif; ?>
                  
                  <!-- Phone info subtext with icons & Real-time Status Badge -->
                  <div class="text-[10px] text-slate-400 font-medium mt-1 flex items-center gap-1.5 flex-wrap">
                    <div class="flex items-center gap-1">
                      <i class="fa-solid fa-phone text-slate-300 text-[9px]"></i>
                      <span><?= h($rPhone) ?></span>
                    </div>
                    <span class="text-slate-300">•</span>
                    <?php
                      $stMap = [
                        'pending'    => ['label' => 'প্যান্ডিং', 'cls' => 'bg-amber-50 text-amber-700 border-amber-200'],
                        'confirmed'  => ['label' => 'কনফার্মড', 'cls' => 'bg-blue-50 text-blue-700 border-blue-200'],
                        'dispatched' => ['label' => 'ডিসপ্যাচড', 'cls' => 'bg-indigo-50 text-indigo-700 border-indigo-200'],
                        'in_transit' => ['label' => 'অন দ্য ওয়ে', 'cls' => 'bg-indigo-50 text-indigo-700 border-indigo-200'],
                        'delivered'  => ['label' => 'ডেলিভার্ড', 'cls' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                        'partial'    => ['label' => 'আংশিক', 'cls' => 'bg-amber-50 text-amber-800 border-amber-300'],
                        'cancelled'  => ['label' => 'বাতিল', 'cls' => 'bg-rose-50 text-rose-700 border-rose-200'],
                      ];
                      $st = $stMap[$ord['status']] ?? ['label' => $ord['status'], 'cls' => 'bg-slate-50 text-slate-700 border-slate-200'];
                    ?>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold border <?= $st['cls'] ?>" id="order-row-status-<?= $ord['id'] ?>">
                      <?= $st['label'] ?>
                    </span>
                  </div>
                </div>
              </td>

              <!-- Total Amount Cell -->
              <td class="p-3 text-right border-r border-slate-100 align-middle bg-white font-mono font-bold text-emerald-700 text-xs sm:text-sm" id="order-total-cell-<?= $ord['id'] ?>">
                ৳ <?= number_format((float)$ord['total_amount'], 2) ?>
                <?php if ($orderOC != 0): ?>
                  <div class="text-[10px] font-bold mt-0.5 <?= $orderOC > 0 ? 'text-emerald-500' : 'text-rose-500' ?>" id="order-oc-badge-<?= $ord['id'] ?>">
                    (<?= $orderOC > 0 ? '+' : '' ?>৳<?= number_format($orderOC, 2) ?>)
                  </div>
                <?php else: ?>
                  <div class="text-[10px] font-bold mt-0.5 text-slate-400" id="order-oc-badge-<?= $ord['id'] ?>" style="display:none;"></div>
                <?php endif; ?>
              </td>

              <!-- Action Column (Invoice View) -->
              <td class="p-3 text-center align-middle bg-white" id="order-actions-cell-<?= $ord['id'] ?>">
                <div class="flex items-center justify-center gap-1.5">
                  <!-- Edit Button -->
                  <button type="button" 
                          id="btn-edit-order-<?= $ord['id'] ?>"
                          onclick='openEditOrderModal(ORDERS_MAP[<?= $ord['id'] ?>])'
                          class="w-8 h-8 rounded-lg bg-amber-50 border border-amber-200 text-amber-600 hover:bg-amber-600 hover:text-white transition duration-200 flex items-center justify-center shadow-3xs active:scale-95"
                          title="অর্ডার এডিট করুন">
                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                  </button>

                  <!-- Invoice Button -->
                  <button type="button" 
                          id="btn-invoice-order-<?= $ord['id'] ?>"
                          onclick='openInvoiceModal(ORDERS_MAP[<?= $ord['id'] ?>])'
                          class="w-8 h-8 rounded-lg bg-blue-50 border border-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white transition duration-200 flex items-center justify-center shadow-3xs active:scale-95"
                          title="ইনভয়েস দেখুন">
                    <i class="fa-solid fa-file-invoice text-xs"></i>
                  </button>
                  <!-- Delivery Memo Button -->
                  <button type="button" 
                          id="btn-delivery-order-<?= $ord['id'] ?>"
                          onclick='openDeliveryMemoModal(ORDERS_MAP[<?= $ord['id'] ?>])'
                          class="w-8 h-8 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-600 hover:bg-emerald-600 hover:text-white transition duration-200 flex items-center justify-center shadow-3xs active:scale-95"
                          title="ডেলিভারি মেমো (পেয়েছেন/ফেরত) দেখুন">
                    <i class="fa-solid fa-truck-ramp-box text-xs"></i>
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>

      <!-- Footer Total (Excel Summary Style) -->
      <tfoot>
        <tr class="border-t border-slate-200 font-bold text-slate-800 text-xs bg-slate-50">
          <td class="p-3 border-r border-slate-200 bg-slate-50/80 font-siliguri font-bold text-slate-500">
            সর্বমোট (Subtotal):
          </td>
          <td class="p-3 text-right border-r border-slate-200 font-mono font-black text-slate-950 text-[13px]" id="grandTotalCell">
            ৳ <?= number_format($grandTotalAmount, 2) ?>
            <?php if ($grandTotalOC != 0): ?>
              <div class="text-[10px] font-bold mt-0.5 <?= $grandTotalOC > 0 ? 'text-emerald-500' : 'text-rose-500' ?>" id="grandTotalOCCell">
                (<?= $grandTotalOC > 0 ? '+' : '' ?>৳<?= number_format($grandTotalOC, 2) ?>)
              </div>
            <?php else: ?>
              <div class="text-[10px] font-bold mt-0.5 text-slate-400" id="grandTotalOCCell" style="display:none;"></div>
            <?php endif; ?>
          </td>
          <td class="p-3 bg-slate-50/80"></td>
        </tr>
      </tfoot>
    </table>
  </div>

</div>

<!-- ========================================================================= -->
<!-- ========================================================================= -->
<!-- EDIT ORDER POPUP MODAL                                                    -->
<!-- ========================================================================= -->
<div id="editOrderModal" class="fixed inset-0 hidden opacity-0 transition-opacity duration-200 flex items-center justify-center p-3 sm:p-4 overflow-y-auto pointer-events-none" style="z-index: 99990 !important;">
  
  <!-- Backdrop Overlay -->
  <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity duration-200 pointer-events-auto" onclick="closeEditOrderModal()"></div>

  <!-- Modal Dialog Container -->
  <div id="editOrderModalContent" class="relative bg-white w-full max-w-2xl rounded-2xl p-4 sm:p-6 shadow-2xl space-y-4 transform scale-95 transition-transform duration-200 border border-slate-200 my-auto text-slate-800 font-siliguri pointer-events-auto z-10 max-h-[92vh] flex flex-col">
    
    <!-- Modal Header -->
    <div class="flex items-start justify-between border-b border-slate-100 pb-3 shrink-0">
      <div class="min-w-0 pr-2">
        <div class="flex items-center gap-2 flex-wrap">
          <h3 class="font-extrabold text-slate-900 text-base sm:text-lg leading-tight truncate" id="editModalRetailerName">
            দোকানের নাম
          </h3>
          <span class="inline-block text-[11px] font-mono font-bold text-blue-700 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded-full" id="editModalOrderBadge">
            #ORD-0000
          </span>
        </div>
        <div class="text-[11px] text-slate-400 font-medium mt-1 flex items-center gap-2">
          <span><i class="fa-solid fa-store text-[10px] text-slate-300 mr-1"></i>অর্ডার সম্পাদনা</span>
          <span class="text-slate-300">•</span>
          <span id="editModalOrderDate" class="font-mono text-slate-500"></span>
        </div>
      </div>

      <button type="button" onclick="closeEditOrderModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-400 hover:text-slate-700 transition flex items-center justify-center active:scale-95 shrink-0" title="বন্ধ করুন">
        <i class="fa-solid fa-xmark text-sm"></i>
      </button>
    </div>

    <!-- Scrollable Items Area: Table -->
    <div class="flex-1 overflow-y-auto space-y-3 pr-0.5">
      
      <!-- Interactive Table -->
      <div class="overflow-x-auto border border-slate-200 rounded-xl bg-white shadow-3xs">
        <table class="w-full text-left border-collapse text-xs font-sans">
          <thead>
            <tr class="bg-slate-50/90 border-b border-slate-200 font-siliguri text-slate-700 font-bold">
              <th class="p-2.5 min-w-[140px] text-left">পণ্যের নাম</th>
              <th class="p-2.5 text-center w-[85px] sm:w-[100px]">পরিমাণ (Qty)</th>
              <th class="p-2.5 text-right w-[95px] sm:w-[110px]">দর / রেট</th>
              <th class="p-2.5 text-right w-[100px] sm:w-[120px]">মোট টাকা</th>
              <th class="p-2.5 text-center w-[36px]"></th>
            </tr>
          </thead>
          <tbody id="editOrderTableBody" class="divide-y divide-slate-100 font-sans">
            <!-- Populated dynamically via JS -->
          </tbody>
          <tfoot>
            <tr class="bg-slate-50 font-bold text-slate-800 border-t border-slate-200 text-xs">
              <td class="p-2.5 font-siliguri text-slate-600">সর্বমোট (Subtotal):</td>
              <td class="p-2.5 text-center font-mono font-bold text-slate-800" id="editModalTotalQty">0</td>
              <td class="p-2.5 text-right">
                <span id="editModalOCBadge" class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-black font-mono bg-emerald-100 text-emerald-800">
                  O/C ৳0.00
                </span>
              </td>
              <td class="p-2.5 text-right font-mono font-black text-slate-950 text-xs sm:text-sm" id="editModalSubtotal">৳ 0.00</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- Add New Product Button (Triggers Product Picker Modal) -->
      <div>
        <button type="button" onclick="openProductPickerModal()" class="w-full py-2.5 px-3 bg-blue-50/70 hover:bg-blue-100/70 border border-dashed border-blue-300 hover:border-blue-400 rounded-xl text-xs font-bold text-blue-700 transition flex items-center justify-center gap-2 shadow-3xs active:scale-98">
          <i class="fa-solid fa-circle-plus text-sm text-blue-600"></i>
          <span>নতুন পণ্য যোগ করুন (Select Product)</span>
        </button>
      </div>

    </div>

    <!-- Bottom Actions Area -->
    <div class="pt-3 border-t border-slate-100 shrink-0">
      
      <!-- Default Actions: Delete on Left, Cancel & Save on Right -->
      <div id="editModalDefaultActions" class="flex items-center justify-between gap-2">
        <button type="button" id="btnTriggerDelete" onclick="startDeleteCountdown()" class="px-3.5 py-2.5 bg-rose-50 hover:bg-rose-100 active:scale-95 border border-rose-200 text-rose-600 font-bold rounded-xl text-xs sm:text-sm flex items-center gap-1.5 transition shadow-3xs" title="অর্ডার ডিলিট করুন">
          <i class="fa-solid fa-trash-can text-xs"></i>
          <span>ডিলিট (Delete)</span>
        </button>

        <div class="flex items-center gap-2">
          <button type="button" onclick="closeEditOrderModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-600 rounded-xl text-xs sm:text-sm font-bold transition">
            বাতিল
          </button>
          <button type="button" id="btnConfirmOrderEdit" onclick="submitOrderEdit()" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold rounded-xl text-xs sm:text-sm flex items-center gap-2 shadow-sm transition">
            <i id="btnConfirmIcon" class="fa-solid fa-floppy-disk text-xs"></i>
            <span id="btnConfirmText">সংরক্ষণ করুন (Save)</span>
            <svg id="btnConfirmSpinner" class="hidden animate-spin h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
          </button>
        </div>
      </div>

      <!-- 10-Second Countdown Delete Confirmation Area (Initially Hidden) -->
      <div id="editModalDeleteCountdownArea" class="hidden bg-gradient-to-r from-rose-50 to-rose-100/50 border border-rose-200 rounded-2xl p-3 sm:p-3.5 space-y-2.5">
        <div class="flex items-center gap-2 text-xs font-bold text-rose-800">
          <i class="fa-solid fa-triangle-exclamation text-rose-600 text-sm animate-pulse"></i>
          <span>সতর্কতা: অর্ডারটি স্থায়ীভাবে মুছে যাবে! নিশ্চিত করতে ১০ সেকেন্ড অপেক্ষা করুন।</span>
        </div>
        <div class="flex items-center justify-between gap-2 pt-1">
          <button type="button" id="btnCancelDelete" onclick="cancelDeleteCountdown()" class="px-4 py-2 bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 font-bold rounded-xl text-xs sm:text-sm transition active:scale-95 shadow-3xs flex items-center gap-1.5">
            <i class="fa-solid fa-xmark text-xs"></i>
            <span>বাতিল করুন</span>
          </button>

          <button type="button" id="btnConfirmDeleteOrder" onclick="executeOrderDelete()" disabled class="px-5 py-2 bg-rose-200 text-rose-400 cursor-not-allowed opacity-80 rounded-xl text-xs sm:text-sm font-bold flex items-center gap-2 transition duration-200">
            <i class="fa-solid fa-clock text-xs" id="deleteTimerIcon"></i>
            <span id="deleteTimerBtnText">ডিলিট নিশ্চিত করুন (10s)</span>
            <svg id="deleteTimerSpinner" class="hidden animate-spin h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
          </button>
        </div>
      </div>

    </div>

  </div>
</div>


<!-- ========================================================================= -->
<!-- PRODUCT PICKER BOX MODAL (ASSIGNED COMPANY PRODUCTS ONLY)                 -->
<!-- ========================================================================= -->
<div id="productPickerModal" class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm hidden opacity-0 transition-opacity duration-200 flex items-center justify-center p-3 sm:p-4 overflow-y-auto pointer-events-none" style="z-index: 99995 !important;">
  
  <div id="productPickerModalContent" class="bg-white w-full max-w-xl rounded-2xl shadow-2xl border border-slate-200 my-auto text-slate-800 font-siliguri pointer-events-auto transform scale-95 transition-transform duration-200 max-h-[85vh] flex flex-col overflow-hidden">
    
    <!-- Header -->
    <div class="px-4 py-3 bg-slate-900 text-white flex items-center justify-between shrink-0">
      <div>
        <h4 class="font-extrabold text-white text-sm sm:text-base leading-tight">
          পণ্য নির্বাচন করুন (Select Product)
        </h4>
        <div class="text-[10px] text-slate-300 mt-0.5">
          শুধুমাত্র আপনার নির্ধারিত কোম্পানির পণ্যসমূহ
        </div>
      </div>
      <button type="button" onclick="closeProductPickerModal()" class="w-7 h-7 rounded-full bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white transition flex items-center justify-center active:scale-95">
        <i class="fa-solid fa-xmark text-xs"></i>
      </button>
    </div>

    <!-- Search Input -->
    <div class="p-3 bg-slate-50 border-b border-slate-200 shrink-0">
      <div class="relative">
        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        <input type="text" id="productPickerSearchInput" oninput="filterProductPicker(this.value)" placeholder="পণ্য বা কোম্পানির নাম দিয়ে খুঁজুন..." class="w-full pl-8 pr-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-medium focus:border-blue-500 focus:outline-none shadow-3xs">
      </div>
    </div>

    <!-- Product Grid Container (Box Model) -->
    <div class="p-3 flex-1 overflow-y-auto">
      <div id="productPickerGrid" class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
        <!-- Injected via JS -->
      </div>
    </div>

    <!-- Footer -->
    <div class="p-3 bg-slate-50 border-t border-slate-200 flex justify-end shrink-0">
      <button type="button" onclick="closeProductPickerModal()" class="px-4 py-2 bg-white hover:bg-slate-100 border border-slate-300 rounded-xl text-xs font-bold text-slate-700 transition active:scale-95 shadow-3xs">
        বন্ধ করুন
      </button>
    </div>

  </div>
</div>


<!-- ========================================================================= -->
<!-- FREE ITEM PICKER MODAL (EDIT ORDER FREE PRODUCTS)                         -->
<!-- ========================================================================= -->
<div id="freeItemPickerModal" class="fixed inset-0 hidden opacity-0 transition-opacity duration-200 flex items-center justify-center p-3 sm:p-4 overflow-y-auto pointer-events-none" style="z-index: 99996 !important;">
  
  <!-- Backdrop Overlay -->
  <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity duration-200 pointer-events-auto" onclick="closeFreeItemModal()"></div>

  <div id="freeItemPickerModalContent" class="relative bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-slate-200 my-auto text-slate-800 font-siliguri pointer-events-auto z-10 transform scale-95 transition-transform duration-200 max-h-[85vh] flex flex-col overflow-hidden">
    
    <!-- Header -->
    <div class="px-4 py-3 bg-slate-900 text-white flex items-center justify-between shrink-0">
      <div class="min-w-0 pr-2">
        <div class="flex items-center gap-1.5 text-amber-400 font-bold text-xs sm:text-sm">
          <i class="fa-solid fa-gift text-amber-400"></i>
          <span>ফ্রি আইটেম নির্বাচন (Free Items)</span>
        </div>
        <div class="text-[11px] text-slate-300 truncate mt-0.5" id="freeItemModalParentName">
          পণ্য নির্বাচন
        </div>
      </div>
      <button type="button" onclick="closeFreeItemModal()" class="w-7 h-7 rounded-full bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white transition flex items-center justify-center active:scale-95 shrink-0">
        <i class="fa-solid fa-xmark text-xs"></i>
      </button>
    </div>

    <!-- Search Input -->
    <div class="p-3 bg-slate-50 border-b border-slate-200 shrink-0">
      <div class="relative">
        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        <input type="text" id="freeItemSearchInput" oninput="filterFreeItemProducts(this.value)" placeholder="ফ্রি পণ্য বা কোম্পানির নাম খুঁজুন..." class="w-full pl-8 pr-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-medium focus:border-amber-500 focus:outline-none shadow-3xs">
      </div>
    </div>

    <!-- Free Products List Container -->
    <div class="p-3 flex-1 overflow-y-auto">
      <div id="freeItemListContainer" class="space-y-2">
        <!-- Injected via JS -->
      </div>
    </div>

    <!-- Footer -->
    <div class="p-3 bg-slate-900 border-t border-slate-800 flex items-center justify-between shrink-0 rounded-b-2xl">
      <div class="text-xs text-slate-300 font-medium">
        মোট নির্বাচিত: <span id="freeItemTotalSelectedCount" class="font-bold text-amber-400 font-mono text-sm">0টি</span>
      </div>
      <div class="flex items-center gap-2">
        <button type="button" onclick="closeFreeItemModal()" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-white border border-slate-700 rounded-xl text-xs font-bold transition active:scale-95 shadow-xs">
          বাতিল
        </button>
        <button type="button" onclick="saveFreeItemSelection()" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 rounded-xl text-xs font-black transition active:scale-95 shadow-md flex items-center gap-1.5 cursor-pointer">
          <i class="fa-solid fa-check text-xs"></i>
          <span>সংরক্ষণ করুন</span>
        </button>
      </div>
    </div>

  </div>
</div>


<!-- ========================================================================= -->
<!-- BEAUTIFUL RETAILER INVOICE MODAL                                         -->
<!-- ========================================================================= -->
<div id="invoiceModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden opacity-0 transition-opacity duration-200 flex items-center justify-center p-3 sm:p-4 overflow-y-auto pointer-events-none" style="z-index: 99990 !important;">
  
  <div id="invoiceModalContent" class="bg-white w-full max-w-lg rounded-2xl p-5 sm:p-6 shadow-xl space-y-4 transform scale-95 transition-transform duration-200 border border-slate-100 my-auto text-slate-800 font-siliguri pointer-events-auto">
    
    <!-- Printable Invoice Container -->
    <div id="printableInvoiceArea" class="space-y-4 bg-white">
      
      <!-- Invoice Header Banner -->
      <div class="flex items-start justify-between border-b border-slate-100 pb-3">
        <div>
          <div class="flex items-center gap-1.5">
            <span class="text-base font-bold text-slate-900 tracking-tight">HappyBD DMS</span>
          </div>
          <p class="text-[9px] text-slate-400 font-semibold mt-0.5">Distribution Management System</p>
        </div>

        <div class="text-right">
          <div class="inline-block px-2 py-0.5 bg-slate-100 text-slate-700 font-bold text-[9px] uppercase tracking-wider rounded border border-slate-200/50">
            অর্ডার চালানি ইনভয়েস
          </div>
          <div class="text-xs font-bold font-mono text-slate-900 mt-1" id="invOrderId">#ORD-0000</div>
          <div class="text-[10px] text-slate-400 font-medium" id="invDate">00 Jan 2026, 1:50 PM</div>
        </div>
      </div>

      <!-- Retailer & SR Metadata Grid -->
      <div class="grid grid-cols-2 gap-3 text-xs">
        <!-- Customer Info -->
        <div>
          <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">কাস্টমার বিবরণ</div>
          <div class="font-bold text-slate-900 text-xs leading-tight" id="invRetailerName">Hunaima Store</div>
          <div class="text-slate-500 mt-0.5 font-mono text-[11px]" id="invRetailerPhone">01700000000</div>
          <div class="text-slate-400 text-[10px] mt-0.5 leading-snug" id="invRetailerAddress">ঠিকানা দেওয়া নেই</div>
        </div>

        <!-- Order & SR Info -->
        <div class="border-l border-slate-100 pl-3">
          <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">অর্ডার বিবরণ</div>
          <div class="flex items-center gap-1.5 mb-1 text-[11px]">
            <span class="text-slate-400">স্ট্যাটাস:</span>
            <span id="invStatusBadge" class="font-bold px-1.5 py-0.5 rounded text-[9px] bg-emerald-50 text-emerald-700">
              ডেলিভার্ড
            </span>
          </div>
          <div class="text-[10px] text-slate-600" id="invDealerName">ডিলার: General Dealer</div>
          <div class="text-[10px] text-slate-600" id="invSRName">SR: <?= h(Auth::name()) ?></div>
        </div>
      </div>

      <!-- Itemized Products Table -->
      <div class="overflow-x-auto border border-slate-100 rounded-xl">
        <table class="w-full text-left text-xs border-collapse table-fixed min-w-0">
          <thead>
            <tr class="bg-slate-50/80 text-slate-500 font-bold border-b border-slate-150 text-[10px]">
              <th class="py-2 px-2.5 w-[6%]">#</th>
              <th class="py-2 px-2.5 w-[40%]">পণ্যের বিবরণ</th>
              <th class="py-2 px-2.5 text-center w-[18%]">প্যাকিং</th>
              <th class="py-2 px-2.5 text-center w-[12%]">পিস</th>
              <th class="py-2 px-2.5 text-right w-[12%]">দর (৳)</th>
              <th class="py-2 px-2.5 text-right w-[12%]">মোট (৳)</th>
            </tr>
          </thead>
          <tbody id="invItemsTableBody" class="divide-y divide-slate-100 text-slate-800">
            <!-- JS will populate rows -->
          </tbody>
        </table>
      </div>

      <!-- Invoice Financial Summary Footer -->
      <div class="flex justify-end pt-1">
        <div class="w-full sm:w-60 space-y-1 text-xs text-slate-500 font-medium">
          <div class="flex justify-between">
            <span>মোট পণ্য:</span>
            <span class="font-bold text-slate-800" id="invTotalItems">0টি</span>
          </div>
          <div class="flex justify-between">
            <span>মোট পিস:</span>
            <span class="font-bold text-slate-800" id="invTotalQtyPcs">0 পিস</span>
          </div>
          <div class="border-t border-slate-100 pt-1.5 flex justify-between items-center text-sm font-bold text-slate-900">
            <span>সর্বমোট টাকা:</span>
            <span class="text-emerald-700 font-mono text-base font-black" id="invGrandTotal">৳ 0.00</span>
          </div>
        </div>
      </div>

      <!-- Auth Signatures / Footer text -->
      <div class="pt-5 border-t border-slate-100 flex justify-between items-end text-[9px] text-slate-400">
        <div>
          <div>HappyBD DMS-এর সাথে থাকার জন্য ধন্যবাদ।</div>
          <div>Computer Generated Invoice.</div>
        </div>
        <div class="text-center">
          <div class="border-b border-slate-200 w-24 mb-1"></div>
          <div class="font-bold text-slate-500">প্রতিনিধির স্বাক্ষর</div>
        </div>
      </div>

    </div>

    <!-- Modal Footer Actions (Non-printable) -->
    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 print:hidden">
      <button type="button" onclick="window.print()" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 active:scale-95 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-sm transition">
        <i class="fa-solid fa-print"></i>
        <span>প্রিন্ট করুন</span>
      </button>
      <button type="button" onclick="closeInvoiceModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-600 border border-slate-200 rounded-xl text-xs font-bold transition">
        বন্ধ করুন
      </button>
    </div>

  </div>
</div>


<!-- ========================================================================= -->
<!-- DELIVERY MEMO MODAL (Minimalist Clean Executive Theme)                    -->
<!-- ========================================================================= -->
<div id="deliveryMemoModal" class="fixed inset-0 bg-slate-950/70 backdrop-blur-md hidden opacity-0 transition-opacity duration-200 flex items-center justify-center p-3 sm:p-4 overflow-y-auto pointer-events-none" style="z-index: 99992 !important;">
  
  <div id="deliveryMemoModalContent" class="bg-white w-full max-w-[155mm] rounded-2xl shadow-2xl space-y-0 transform scale-95 transition-transform duration-200 border border-slate-200/80 my-auto text-slate-800 font-siliguri pointer-events-auto max-h-[92vh] flex flex-col overflow-hidden">
    
    <!-- Top Action Bar (Non-Printable) -->
    <div class="del-bar print:hidden flex items-center justify-between px-5 py-3 bg-slate-900 text-white shrink-0">
      <div class="flex items-center gap-2">
        <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xs">
          <i class="fa-solid fa-receipt"></i>
        </div>
        <div>
          <h4 class="font-bold text-xs sm:text-sm text-white leading-tight">ডেলিভারি মেমো বিবরণ</h4>
          <span class="text-[10px] text-slate-300">অর্ডার, ডেলিভারি ও ফেরত হিসাব</span>
        </div>
      </div>
      <button type="button" onclick="closeDeliveryMemoModal()" class="w-7 h-7 rounded-full bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white transition flex items-center justify-center active:scale-95">
        <i class="fa-solid fa-xmark text-xs"></i>
      </button>
    </div>

    <!-- Printable Delivery Sheet Area -->
    <div id="printableDeliveryArea" class="flex-1 overflow-y-auto p-4 sm:p-6 bg-white">
      
      <div class="del-sheet-wrap">
        
        <!-- Header Section -->
        <div class="del-head-sec">
          <div>
            <b id="delRetailerName">রহিম স্টোর</b>
            <p id="delRetailerSub">আব্দুর রহিম · চারঘাট বাজার</p>
          </div>
          <div class="rt">
            <div class="flex items-center justify-end gap-1.5 mb-1">
              <span id="delStatusBadge" class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold"></span>
            </div>
            <em id="delInvId">ইনভয়েস ১০২৪</em>
            <span id="delDate">১৪ সেপ্টেম্বর ২০২৬</span>
          </div>
        </div>

        <!-- HERO Section -->
        <div class="del-hero-sec">
          <div class="cap">
            <b>আজকের মাল</b>
            <small>নিচের প্রতিটা লাইন এই তিন ঘরে মিলবে</small>
          </div>
          <div class="box b-ord">
            <small>অর্ডার</small>
            <b id="delHOrd">১০০</b>
            <i>পিস</i>
          </div>
          <div class="box b-got">
            <small>পেয়েছেন</small>
            <b id="delHGot">৭০</b>
            <i>পিস</i>
          </div>
          <div class="box b-bak">
            <small>ফেরত</small>
            <b id="delHBak">৩০</b>
            <i>পিস</i>
          </div>
        </div>

        <div class="del-sumline-sec"><div></div><div></div><div></div><div></div></div>
        <div class="del-sumnote-sec" id="delEq">৭০ + ৩০ = ১০০ পিস — হিসাব মিলেছে</div>

        <!-- Rows List -->
        <div class="del-rows-sec" id="delRows"></div>

        <!-- Money Footer -->
        <div class="del-money-sec">
          <div class="lb">টাকার হিসাব</div>
          <div class="v" id="delMOrd">৳৫,০০০</div>
          <div class="v got" id="delMGot">৳৩,৫১০</div>
          <div class="v bak" id="delMBak">−৳১,৪৯০</div>
        </div>

        <!-- Pay -->
        <div class="del-pay-sec">
          <span>আজ দিতে হবে</span>
          <b id="delPay">৳৩,৫১০</b>
        </div>

        <!-- Skip Banner -->
        <div class="del-skip-sec">
          <b id="delS1">৩০ পিস</b> ফেরত গেছে। এই <b id="delS2">৳১,৪৯০</b> টাকা আজ <b>দেবেন না</b>।
        </div>
        <div class="del-later-sec">ফেরত যাওয়া মাল কালকের গাড়িতে চলে আসবে। ফোন দিতে হবে না।</div>

        <!-- Signatures -->
        <div class="del-sign-sec">
          <div>
            <b id="delSRSign">মোঃ হাসান</b>
            <small>মাল দিয়েছেন · ডেলিভারি ম্যান</small>
          </div>
          <div>
            <b id="delRetSign">আব্দুর রহিম</b>
            <small>গুনে বুঝে পেয়েছি · রহিম স্টোর</small>
          </div>
        </div>

        <!-- Footer Note -->
        <div class="del-foot-sec">
          <div>সমস্যা হলে ফোন <span id="delSup">০১৭০০-০০০০০০</span></div>
          <div class="pg">পৃষ্ঠা <span id="delPg">১/১</span></div>
        </div>

      </div>

    </div>

    <!-- Modal Footer Actions (Non-printable) -->
    <div class="px-5 py-3 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-2 print:hidden shrink-0">
      <button type="button" onclick="closeDeliveryMemoModal()" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition active:scale-95 shadow-xs">
        বন্ধ করুন
      </button>
    </div>

  </div>
</div>


<!-- Style for Delivery Sheet & Print View -->
<style>
:root {
  --del-ink: #111c28;
  --del-soft: #6b7b8c;
  --del-line: #dde5ee;
  --del-green: #06754f;
  --del-green-bg: #e6f4ee;
  --del-red: #c2261f;
  --del-red-bg: #fdecea;
  --del-cols: minmax(0, 1fr) 68px 68px 68px;
}

.del-sheet-wrap {
  font-family: 'Hind Siliguri', system-ui, sans-serif;
  color: var(--del-ink);
  font-size: 15px;
  line-height: 1.5;
  background: #fff;
  padding: 8px 4px;
}

/* Header */
.del-head-sec {
  display: flex;
  align-items: flex-end;
  gap: 12px;
  padding-bottom: 12px;
  border-bottom: 2px solid var(--del-ink);
}
.del-head-sec b {
  font-size: 22px;
  font-weight: 700;
  line-height: 1.15;
  display: block;
  color: var(--del-ink);
}
.del-head-sec p {
  font-size: 13.5px;
  color: var(--del-soft);
}
.del-head-sec .rt {
  margin-left: auto;
  text-align: right;
  font-size: 13px;
  color: var(--del-soft);
  line-height: 1.55;
}
.del-head-sec .rt em {
  font-style: normal;
  display: block;
  font-weight: 600;
  color: var(--del-ink);
  font-size: 14px;
}

/* HERO */
.del-hero-sec {
  display: grid;
  grid-template-columns: var(--del-cols);
  align-items: end;
  padding: 16px 0 0;
}
.del-hero-sec .cap {
  padding-bottom: 14px;
}
.del-hero-sec .cap b {
  display: block;
  font-size: 17px;
  font-weight: 600;
  line-height: 1.25;
  color: var(--del-ink);
}
.del-hero-sec .cap small {
  font-size: 13px;
  color: var(--del-soft);
}
.del-hero-sec .box {
  text-align: center;
  padding: 10px 4px 9px;
  border: 1.5px solid;
  margin-left: 4px;
}
.del-hero-sec .box small {
  display: block;
  font-size: 12.5px;
  font-weight: 600;
  line-height: 1.2;
}
.del-hero-sec .box b {
  display: block;
  font-size: 28px;
  font-weight: 700;
  line-height: 1.05;
  letter-spacing: -.02em;
  margin-top: 2px;
}
.del-hero-sec .box i {
  display: block;
  font-style: normal;
  font-size: 11.5px;
  font-weight: 500;
  opacity: .8;
}
.del-hero-sec .b-ord { border-color: var(--del-ink); background: #fff; color: var(--del-ink); }
.del-hero-sec .b-got { border-color: var(--del-green); background: var(--del-green-bg); color: var(--del-green); }
.del-hero-sec .b-bak { border-color: var(--del-red); background: var(--del-red-bg); color: var(--del-red); }

.del-sumline-sec {
  display: grid;
  grid-template-columns: var(--del-cols);
  margin-top: 6px;
}
.del-sumline-sec div {
  height: 9px;
  margin-left: 4px;
  border-left: 1px solid var(--del-line);
  border-right: 1px solid var(--del-line);
  border-bottom: 1px solid var(--del-line);
}
.del-sumline-sec div:first-child { border: 0; margin: 0; }
.del-sumnote-sec {
  font-size: 12.5px;
  color: var(--del-soft);
  text-align: right;
  padding: 4px 2px 0;
}

/* Rows */
.del-rows-sec {
  margin-top: 10px;
  border-top: 1px solid var(--del-line);
}
.del-row-item {
  display: grid;
  grid-template-columns: var(--del-cols);
  align-items: center;
  padding: 11px 0;
  border-bottom: 1px solid var(--del-line);
}
.del-row-item .nm b {
  display: block;
  font-size: 16.5px;
  font-weight: 600;
  line-height: 1.3;
  color: var(--del-ink);
}
.del-row-item .nm small {
  font-size: 12.5px;
  color: var(--del-soft);
}
.del-row-item .n {
  text-align: center;
  font-size: 22px;
  font-weight: 700;
  line-height: 1.1;
  margin-left: 4px;
}
.del-row-item .n.ord { color: var(--del-ink); }
.del-row-item .n.got { color: var(--del-green); }
.del-row-item .n.bak { color: var(--del-red); }
.del-row-item .n.bak.zero { color: #c3ced9; font-weight: 500; }
.del-row-item.done { background: #f6fbf8; }

/* Money Footer */
.del-money-sec {
  display: grid;
  grid-template-columns: var(--del-cols);
  margin-top: 2px;
  border-bottom: 2px solid var(--del-ink);
}
.del-money-sec .lb {
  font-size: 14px;
  color: var(--del-soft);
  padding: 11px 0;
  font-weight: 600;
}
.del-money-sec .v {
  text-align: center;
  padding: 11px 0;
  font-size: 15px;
  font-weight: 700;
  margin-left: 4px;
  color: var(--del-ink);
}
.del-money-sec .v.got { color: var(--del-green); }
.del-money-sec .v.bak { color: var(--del-red); }

.del-pay-sec {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 15px 0 4px;
}
.del-pay-sec span {
  font-size: 18px;
  font-weight: 600;
  color: var(--del-ink);
}
.del-pay-sec b {
  margin-left: auto;
  font-size: 32px;
  font-weight: 700;
  letter-spacing: -.02em;
  line-height: 1;
  color: var(--del-ink);
}
.del-skip-sec {
  margin-top: 10px;
  background: var(--del-red-bg);
  border-left: 4px solid var(--del-red);
  padding: 11px 13px;
  font-size: 15px;
  line-height: 1.55;
  color: var(--del-ink);
}
.del-skip-sec b { font-weight: 700; }
.del-later-sec {
  margin-top: 8px;
  font-size: 13.5px;
  color: var(--del-soft);
  line-height: 1.55;
}

.del-sign-sec {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-top: 24px;
}
.del-sign-sec div {
  padding-top: 34px;
  border-top: 1px solid var(--del-ink);
}
.del-sign-sec b {
  display: block;
  font-size: 15.5px;
  font-weight: 600;
  color: var(--del-ink);
}
.del-sign-sec small {
  font-size: 12.5px;
  color: var(--del-soft);
}
.del-foot-sec {
  margin-top: 16px;
  padding-top: 9px;
  border-top: 1px solid var(--del-line);
  font-size: 12.5px;
  color: var(--del-soft);
  display: flex;
  gap: 10px;
}
.del-foot-sec .pg {
  margin-left: auto;
  white-space: nowrap;
}

@media (max-width: 520px) {
  :root { --del-cols: minmax(0, 1fr) 54px 54px 54px; }
  .del-hero-sec .box b { font-size: 24px; }
  .del-hero-sec .box small { font-size: 11.5px; }
  .del-row-item .n { font-size: 19px; }
  .del-pay-sec b { font-size: 26px; }
  .del-sign-sec { grid-template-columns: 1fr; gap: 14px; }
}

@media print {
  body * {
    visibility: hidden;
  }
  #invoiceModal, #invoiceModal *,
  #deliveryMemoModal, #deliveryMemoModal * {
    visibility: visible;
  }
  #invoiceModal, #deliveryMemoModal {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: auto;
    background: white !important;
    padding: 0 !important;
  }
  #invoiceModalContent, #deliveryMemoModalContent {
    box-shadow: none !important;
    border: none !important;
    max-width: 100% !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 15px !important;
  }
  .print\:hidden, .del-bar {
    display: none !important;
  }
}
</style>

<script>
// Available SR Catalog Products passed from PHP
const ALL_SR_PRODUCTS = <?= json_encode($allProducts ?? []) ?>;

// In-Memory Orders Map for seamless synchronization
const ORDERS_MAP = {};
<?php foreach ($items as $ord): ?>
  ORDERS_MAP[<?= $ord['id'] ?>] = <?= json_encode($ord) ?>;
<?php endforeach; ?>

// Current Order Being Edited State
let editingOrder = null;

// ── 12-Hour Time Format Helper ───────────────────────────────────────────────
function formatDateTime12Hr(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr.replace(/-/g, '/'));
  if (isNaN(d.getTime())) return dateStr;
  
  const day = d.getDate();
  const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  const month = monthNames[d.getMonth()];
  const year = d.getFullYear();
  
  let hours = d.getHours();
  const minutes = String(d.getMinutes()).padStart(2, '0');
  const ampm = hours >= 12 ? 'PM' : 'AM';
  hours = hours % 12;
  hours = hours ? hours : 12;
  
  return `${day} ${month} ${year}, ${hours}:${minutes} ${ampm}`;
}

// ── Toast Notification Helper (Slide from Left, Stacks over Everything) ─────
function showToast(message, type = 'success') {
  let container = document.getElementById('toastContainer');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toastContainer';
    document.body.appendChild(container);
  } else if (container.parentElement !== document.body) {
    document.body.appendChild(container);
  }
  container.className = 'fixed top-4 left-4 space-y-2 pointer-events-none flex flex-col items-start';
  container.style.cssText = 'position: fixed !important; top: 1rem !important; left: 1rem !important; z-index: 999999 !important; max-width: calc(100vw - 2rem); pointer-events: none;';

  const toast = document.createElement('div');
  const isSuccess = type === 'success';
  const bg = isSuccess ? '#0f172a' : '#e11d48'; // slate-900 or rose-600
  const icon = isSuccess ? 'fa-circle-check' : 'fa-circle-exclamation';
  const iconColor = isSuccess ? '#34d399' : '#fecdd3';

  toast.className = 'pointer-events-auto flex items-center gap-2.5 font-siliguri font-bold text-xs text-white';
  toast.style.cssText = `
    background: ${bg};
    color: #ffffff;
    padding: 10px 18px;
    border-radius: 14px;
    box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.45), 0 4px 10px -2px rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.18);
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    font-weight: 700;
    pointer-events: auto;
    max-width: 90vw;
    word-break: break-word;
    transform: translateX(-120%);
    opacity: 0;
    transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.35s ease;
    z-index: 999999;
  `;

  toast.innerHTML = `<i class="fa-solid ${icon}" style="color: ${iconColor}; font-size: 15px; flex-shrink: 0;"></i><span>${message}</span>`;
  container.appendChild(toast);

  // Force reflow and slide in smoothly from left
  void toast.offsetWidth;
  setTimeout(() => {
    toast.style.transform = 'translateX(0)';
    toast.style.opacity = '1';
  }, 20);

  // Auto dismiss: slide out to left after 1 second then remove
  setTimeout(() => {
    toast.style.transform = 'translateX(-120%)';
    toast.style.opacity = '0';
    setTimeout(() => {
      toast.remove();
    }, 350);
  }, 1000);
}

// ── Open Edit Order Popup Modal ──────────────────────────────────────────────
function openEditOrderModal(orderData) {
  const orderId = orderData.id;
  const order = ORDERS_MAP[orderId] || orderData;

  const retailerName = order.retailer_name || order.dealer_name || 'সাধারণ কাস্টমার';
  document.getElementById('editModalRetailerName').innerText = retailerName;
  document.getElementById('editModalOrderBadge').innerText = `#ORD-${order.id}`;
  const dateEl = document.getElementById('editModalOrderDate');
  if (dateEl) {
    dateEl.innerText = formatDateTime12Hr(order.created_at || '');
  }

  // Cancel any active delete countdown
  cancelDeleteCountdown();

  // Build mutable editing state
  editingOrder = {
    id: order.id,
    retailer_name: retailerName,
    created_at: order.created_at,
    items: (order.products || []).map(p => {
      const ppb = parseInt(p.pieces_per_box || 1) || 1;
      const totalQty = parseInt(p.quantity || 0);
      const unitPrice = parseFloat(p.unit_price || 0);
      const basePrice = parseFloat(p.base_price || 0);

      // Map free items into { free_product_id: { qty, name } }
      const freeItemsObj = {};
      if (Array.isArray(p.free_items)) {
        p.free_items.forEach(fi => {
          const fPid = parseInt(fi.free_product_id);
          const fQty = parseInt(fi.quantity) || 0;
          if (fPid > 0 && fQty > 0) {
            freeItemsObj[fPid] = {
              qty: fQty,
              name: fi.free_product_name || 'ফ্রি পণ্য'
            };
          }
        });
      }

      return {
        product_id: parseInt(p.product_id || p.id),
        product_name: p.product_name || p.name || 'পণ্য',
        product_image: p.product_image || p.image || '',
        ppb: ppb,
        box_type: p.box_type || 'কার্টন',
        base_price: basePrice,
        unit_price: unitPrice,
        total_qty: totalQty,
        line_total: totalQty * unitPrice,
        item_oc: (unitPrice - basePrice) * totalQty,
        free_items: freeItemsObj
      };
    })
  };

  renderEditOrderItems();

  const modal = document.getElementById('editOrderModal');
  const content = document.getElementById('editOrderModalContent');

  if (modal.parentElement !== document.body) {
    document.body.appendChild(modal);
  }
  const picker = document.getElementById('productPickerModal');
  if (picker && picker.parentElement !== document.body) {
    document.body.appendChild(picker);
  }

  document.body.classList.add('overflow-hidden');

  modal.classList.remove('hidden', 'pointer-events-none');
  setTimeout(() => {
    modal.classList.remove('opacity-0');
    if (content) {
      content.classList.remove('scale-95');
      content.classList.add('scale-100');
    }
  }, 10);
}

// ── Close Edit Order Popup Modal ─────────────────────────────────────────────
function closeEditOrderModal() {
  cancelDeleteCountdown();
  closeProductPickerModal();

  const modal = document.getElementById('editOrderModal');
  const content = document.getElementById('editOrderModalContent');

  if (content) {
    content.classList.remove('scale-100');
    content.classList.add('scale-95');
  }
  modal.classList.add('opacity-0');

  document.body.classList.remove('overflow-hidden');

  setTimeout(() => {
    modal.classList.add('hidden', 'pointer-events-none');
  }, 200);
}

// ── Render Items Table in Edit Modal ─────────────────────────────────────────
function renderEditOrderItems() {
  const tbody = document.getElementById('editOrderTableBody');
  if (!tbody) return;
  tbody.innerHTML = '';

  if (!editingOrder || editingOrder.items.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="5" class="p-8 text-center text-slate-400 bg-white font-siliguri">
          <div class="w-10 h-10 rounded-xl bg-slate-50 text-slate-300 flex items-center justify-center text-lg mx-auto mb-1.5">
            <i class="fa-solid fa-box-open"></i>
          </div>
          <span class="text-xs font-medium">কোনো পণ্য যোগ করা হয়নি। নিচের বাটনে ক্লিক করে পণ্য যোগ করুন।</span>
        </td>
      </tr>
    `;
    updateEditOrderSummary();
    return;
  }

  editingOrder.items.forEach((item, idx) => {
    const tr = document.createElement('tr');
    tr.className = 'hover:bg-slate-50/60 transition-colors';
    tr.id = `edit-item-row-${idx}`;

    const diffPerUnit = item.unit_price - item.base_price;
    const ocSign = item.item_oc >= 0 ? '+' : '-';
    const ocClass = diffPerUnit > 0 
      ? 'bg-emerald-50 text-emerald-700 border-emerald-200' 
      : (diffPerUnit < 0 ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-slate-50 text-slate-500 border-slate-200');

    tr.innerHTML = `
      <!-- Product Column -->
      <td class="p-2.5 align-middle">
        <div class="flex items-start gap-2">
          ${item.product_image ? `
            <img src="<?= url('') ?>${item.product_image}" class="w-8 h-8 rounded-lg object-cover border border-slate-200 shrink-0 mt-0.5" onerror="this.style.display='none'">
          ` : `
            <div class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200 text-slate-400 flex items-center justify-center text-xs shrink-0 mt-0.5">
              <i class="fa-solid fa-box"></i>
            </div>
          `}
          <div class="min-w-0 flex-1">
            <div class="font-bold text-slate-900 truncate leading-tight font-siliguri text-xs">
              ${item.product_name}
            </div>
            <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
              <span class="text-[10px] text-slate-400 font-mono">${item.box_type || 'কার্টন'} (${item.ppb} পিস)</span>
              <span id="row-oc-${idx}" class="text-[9px] font-bold font-mono px-1 py-0.2 rounded border ${ocClass}">
                ${ocSign}৳${Math.abs(item.item_oc).toFixed(1)} O/C
              </span>
            </div>

            <!-- Free Items Badges & Edit Button -->
            <div class="mt-1.5 flex items-center gap-1.5 flex-wrap">
              ${(() => {
                const freeMap = item.free_items || {};
                const activeFree = Object.entries(freeMap).filter(([_, f]) => (typeof f === 'object' ? f.qty : parseInt(f)) > 0);
                const count = activeFree.reduce((sum, [_, f]) => sum + (typeof f === 'object' ? f.qty : parseInt(f)), 0);

                let tags = activeFree.map(([fPid, fData]) => {
                  const qty = typeof fData === 'object' ? fData.qty : parseInt(fData);
                  const name = typeof fData === 'object' ? fData.name : (ALL_SR_PRODUCTS.find(p => p.id == fPid)?.name || 'ফ্রি');
                  return `<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-200 text-[9px] font-semibold">
                    <i class="fa-solid fa-gift text-amber-600 text-[8px]"></i> ${name}: <b>${qty}টি</b>
                  </span>`;
                }).join(' ');

                return `
                  ${tags}
                  <button type="button" onclick="openFreeItemModal(${idx})" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold transition active:scale-95 border ${count > 0 ? 'bg-amber-50 hover:bg-amber-100 text-amber-700 border-amber-300' : 'bg-slate-50 hover:bg-slate-100 text-slate-600 border-slate-200'}">
                    <i class="fa-solid fa-gift text-[9px] ${count > 0 ? 'text-amber-600' : 'text-slate-400'}"></i>
                    <span>${count > 0 ? `ফ্রি আইটেম (${count})` : '+ ফ্রি আইটেম'}</span>
                  </button>
                `;
              })()}
            </div>
          </div>
        </div>
      </td>

      <!-- Ordered Quantity (editable) -->
      <td class="p-2 align-middle text-center">
        <input type="number" min="1" step="1" id="item-qty-${idx}" value="${item.total_qty}" 
               oninput="onEditQtyChange(${idx}, this.value)" 
               class="w-full text-center font-mono font-bold text-xs bg-slate-50 border border-slate-200 rounded-lg py-1 px-1 focus:bg-white focus:border-blue-500 focus:outline-none shadow-3xs">
      </td>

      <!-- Per-Unit / Box Price (editable) -->
      <td class="p-2 align-middle text-right">
        <div class="relative">
          <span class="absolute left-1.5 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 font-bold select-none">৳</span>
          <input type="number" min="0" step="any" id="item-price-${idx}" value="${item.unit_price}" 
                 oninput="onEditPriceChange(${idx}, this.value)" 
                 class="w-full text-right font-mono font-bold text-xs bg-slate-50 border border-slate-200 rounded-lg py-1 pl-4 pr-1 focus:bg-white focus:border-blue-500 focus:outline-none shadow-3xs">
        </div>
      </td>

      <!-- Total Amount (editable) -->
      <td class="p-2 align-middle text-right">
        <div class="relative">
          <span class="absolute left-1.5 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 font-bold select-none">৳</span>
          <input type="number" min="0" step="any" id="item-total-${idx}" value="${item.line_total.toFixed(2)}" 
                 oninput="onEditTotalChange(${idx}, this.value)" 
                 class="w-full text-right font-mono font-bold text-xs bg-slate-50 border border-slate-200 rounded-lg py-1 pl-4 pr-1 focus:bg-white focus:border-blue-500 focus:outline-none shadow-3xs">
        </div>
      </td>

      <!-- Remove Action -->
      <td class="p-2 align-middle text-center">
        <button type="button" onclick="deleteEditItem(${idx})" class="w-6 h-6 rounded-md text-slate-400 hover:text-white hover:bg-rose-500 flex items-center justify-center transition active:scale-95" title="পণ্য সরান">
          <i class="fa-solid fa-trash-can text-[11px]"></i>
        </button>
      </td>
    `;
    tbody.appendChild(tr);
  });

  updateEditOrderSummary();
}

// ── Real-Time Bi-Directional Quantity, Price, Total, and O/C Handlers ───────
function onEditQtyChange(idx, val) {
  if (!editingOrder || !editingOrder.items[idx]) return;
  const item = editingOrder.items[idx];
  const qty = Math.max(0, parseInt(val) || 0);
  item.total_qty = qty;
  item.line_total = qty * item.unit_price;
  item.item_oc = (item.unit_price - item.base_price) * qty;

  const totalInput = document.getElementById(`item-total-${idx}`);
  if (totalInput) {
    totalInput.value = item.line_total.toFixed(2);
  }
  updateRowOC(idx);
  updateEditOrderSummary();
}

function onEditPriceChange(idx, val) {
  if (!editingOrder || !editingOrder.items[idx]) return;
  const item = editingOrder.items[idx];
  const price = Math.max(0, parseFloat(val) || 0);
  item.unit_price = price;
  item.line_total = item.total_qty * price;
  item.item_oc = (price - item.base_price) * item.total_qty;

  const totalInput = document.getElementById(`item-total-${idx}`);
  if (totalInput) {
    totalInput.value = item.line_total.toFixed(2);
  }
  updateRowOC(idx);
  updateEditOrderSummary();
}

function onEditTotalChange(idx, val) {
  if (!editingOrder || !editingOrder.items[idx]) return;
  const item = editingOrder.items[idx];
  const total = Math.max(0, parseFloat(val) || 0);
  item.line_total = total;

  if (item.total_qty > 0) {
    item.unit_price = parseFloat((total / item.total_qty).toFixed(2));
    const priceInput = document.getElementById(`item-price-${idx}`);
    if (priceInput) {
      priceInput.value = item.unit_price;
    }
  }
  item.item_oc = (item.unit_price - item.base_price) * item.total_qty;

  updateRowOC(idx);
  updateEditOrderSummary();
}

function updateRowOC(idx) {
  const item = editingOrder.items[idx];
  if (!item) return;

  const badge = document.getElementById(`row-oc-${idx}`);
  if (!badge) return;

  const diffPerUnit = item.unit_price - item.base_price;
  const ocSign = item.item_oc >= 0 ? '+' : '-';
  const ocClass = diffPerUnit > 0 
    ? 'bg-emerald-50 text-emerald-700 border-emerald-200' 
    : (diffPerUnit < 0 ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-slate-50 text-slate-500 border-slate-200');

  badge.className = `text-[9px] font-bold font-mono px-1 py-0.2 rounded border ${ocClass}`;
  badge.innerText = `${ocSign}৳${Math.abs(item.item_oc).toFixed(1)} O/C`;
}

function deleteEditItem(idx) {
  if (!editingOrder || !editingOrder.items[idx]) return;
  const item = editingOrder.items[idx];
  editingOrder.items.splice(idx, 1);
  renderEditOrderItems();
  showToast(`"${item.product_name}" সরানো হয়েছে।`, 'error');
}

function updateEditOrderSummary() {
  if (!editingOrder) return;

  let grandSubtotal = 0;
  let grandTotalQty = 0;
  let grandOC = 0;

  editingOrder.items.forEach(item => {
    grandSubtotal += item.line_total;
    grandTotalQty += item.total_qty;
    grandOC += item.item_oc;
  });

  const subtotalEl = document.getElementById('editModalSubtotal');
  const qtyEl = document.getElementById('editModalTotalQty');
  const ocBadgeEl = document.getElementById('editModalOCBadge');

  if (subtotalEl) {
    subtotalEl.innerText = `৳ ${parseFloat(grandSubtotal).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}`;
  }
  if (qtyEl) {
    qtyEl.innerText = grandTotalQty;
  }
  if (ocBadgeEl) {
    const ocSign = grandOC >= 0 ? '+' : '-';
    const ocAbs = Math.abs(grandOC).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
    ocBadgeEl.innerText = `O/C ${ocSign}৳${ocAbs}`;
    ocBadgeEl.className = `inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-black font-mono ${
      grandOC > 0 ? 'bg-emerald-100 text-emerald-800' : (grandOC < 0 ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-700')
    }`;
  }
}

// ── Product Picker Box Modal (Only SR's Assigned Company Products) ───────────
function openProductPickerModal() {
  const modal = document.getElementById('productPickerModal');
  const content = document.getElementById('productPickerModalContent');
  const searchInput = document.getElementById('productPickerSearchInput');
  if (searchInput) searchInput.value = '';

  renderProductPickerGrid('');

  modal.classList.remove('hidden', 'pointer-events-none');
  setTimeout(() => {
    modal.classList.remove('opacity-0');
    if (content) {
      content.classList.remove('scale-95');
      content.classList.add('scale-100');
    }
    if (searchInput) searchInput.focus();
  }, 10);
}

function closeProductPickerModal() {
  const modal = document.getElementById('productPickerModal');
  const content = document.getElementById('productPickerModalContent');

  if (content) {
    content.classList.remove('scale-100');
    content.classList.add('scale-95');
  }
  modal.classList.add('opacity-0');

  setTimeout(() => {
    modal.classList.add('hidden', 'pointer-events-none');
  }, 200);
}

function filterProductPicker(query) {
  renderProductPickerGrid(query);
}

function renderProductPickerGrid(filterText) {
  const grid = document.getElementById('productPickerGrid');
  if (!grid) return;
  grid.innerHTML = '';

  const q = (filterText || '').toLowerCase().trim();
  const currentProductIds = new Set((editingOrder?.items || []).map(i => i.product_id));

  // ALL_SR_PRODUCTS is already filtered to only products of the SR's assigned company
  const filtered = (ALL_SR_PRODUCTS || []).filter(p => {
    if (!q) return true;
    const name = (p.name || '').toLowerCase();
    const comp = (p.company_name || '').toLowerCase();
    return name.includes(q) || comp.includes(q);
  });

  if (filtered.length === 0) {
    grid.innerHTML = `
      <div class="col-span-full py-8 text-center text-slate-400 font-siliguri">
        <i class="fa-solid fa-box-open text-2xl mb-1 text-slate-300"></i>
        <div class="text-xs font-bold">কোনো পণ্য পাওয়া যায়নি।</div>
      </div>
    `;
    return;
  }

  filtered.forEach(p => {
    const isAlreadyAdded = currentProductIds.has(parseInt(p.id));
    const stock = parseInt(p.stock || 0);
    const stockClass = stock > 0 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-600 border-rose-200';
    const ppb = parseInt(p.pieces_per_box || p.pieces_per_carton || 1) || 1;
    const price = parseFloat(p.price || 0);

    const card = document.createElement('div');
    card.className = `p-2.5 rounded-xl border transition-all duration-150 flex items-center gap-2.5 cursor-pointer select-none ${
      isAlreadyAdded 
        ? 'bg-blue-50/50 border-blue-200 opacity-90' 
        : 'bg-white hover:bg-slate-50 border-slate-200 hover:border-blue-400 hover:shadow-2xs active:scale-98'
    }`;
    card.onclick = () => addProductFromPicker(parseInt(p.id));

    card.innerHTML = `
      <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200/80 overflow-hidden flex items-center justify-center text-slate-400 shrink-0">
        ${p.image 
          ? `<img src="<?= url('') ?>${p.image}" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML='<i class=\\\'fa-solid fa-box text-slate-400 text-xs\\\'></i>'">` 
          : `<i class="fa-solid fa-box text-slate-400 text-xs"></i>`
        }
      </div>

      <div class="min-w-0 flex-1">
        <div class="flex items-center justify-between gap-1">
          <h5 class="font-bold text-slate-900 text-xs leading-snug truncate">
            ${p.name}
          </h5>
          ${isAlreadyAdded ? `<span class="text-[9px] font-bold text-blue-600 bg-blue-100/70 px-1.5 py-0.2 rounded shrink-0">যুক্ত আছে</span>` : ''}
        </div>

        <div class="text-[10px] text-slate-400 font-mono mt-0.5">
          ${p.company_name || 'কোম্পানি'} • ${p.box_type || 'কার্টন'} (${ppb} পিস)
        </div>

        <div class="flex items-center justify-between gap-2 mt-1">
          <span class="inline-flex items-center px-1.5 py-0.2 rounded border text-[9px] font-bold font-mono ${stockClass}">
            স্টক: ${stock} পিস
          </span>
          <span class="font-mono font-black text-slate-800 text-xs">
            ৳ ${price.toFixed(2)}
          </span>
        </div>
      </div>
    `;

    grid.appendChild(card);
  });
}

function addProductFromPicker(prodId) {
  if (!editingOrder) return;
  const prod = (ALL_SR_PRODUCTS || []).find(p => parseInt(p.id) === prodId);
  if (!prod) return;

  const existingIdx = editingOrder.items.findIndex(i => i.product_id === prodId);
  if (existingIdx !== -1) {
    editingOrder.items[existingIdx].total_qty += 1;
    editingOrder.items[existingIdx].line_total = editingOrder.items[existingIdx].total_qty * editingOrder.items[existingIdx].unit_price;
    editingOrder.items[existingIdx].item_oc = (editingOrder.items[existingIdx].unit_price - editingOrder.items[existingIdx].base_price) * editingOrder.items[existingIdx].total_qty;
    showToast(`"${prod.name}" এর পরিমাণ বাড়ানো হয়েছে।`, 'success');
  } else {
    const ppb = parseInt(prod.pieces_per_box || prod.pieces_per_carton || 1) || 1;
    const unitPrice = parseFloat(prod.price || 0);
    const basePrice = parseFloat(prod.price || 0);

    editingOrder.items.push({
      product_id: parseInt(prod.id),
      product_name: prod.name,
      product_image: prod.image || '',
      ppb: ppb,
      box_type: prod.box_type || 'কার্টন',
      base_price: basePrice,
      unit_price: unitPrice,
      total_qty: 1,
      line_total: 1 * unitPrice,
      item_oc: 0,
      free_items: {}
    });
    showToast(`"${prod.name}" অর্ডারে যোগ করা হয়েছে।`, 'success');
  }

  renderEditOrderItems();
  closeProductPickerModal();
}

// ── Free Items Picker Modal Logic ──────────────────────────────────────────
let currentFreeItemParentIdx = null;
let tempFreeItems = {}; // { [free_product_id]: { qty, name } }

function openFreeItemModal(itemIdx) {
  if (!editingOrder || !editingOrder.items[itemIdx]) return;
  currentFreeItemParentIdx = itemIdx;
  const parentItem = editingOrder.items[itemIdx];

  // Deep clone existing free items
  tempFreeItems = {};
  if (parentItem.free_items) {
    Object.entries(parentItem.free_items).forEach(([fPid, fData]) => {
      const qty = typeof fData === 'object' ? fData.qty : parseInt(fData);
      const name = typeof fData === 'object' ? fData.name : (ALL_SR_PRODUCTS.find(p => p.id == fPid)?.name || 'ফ্রি');
      if (qty > 0) {
        tempFreeItems[parseInt(fPid)] = { qty, name };
      }
    });
  }

  const parentNameEl = document.getElementById('freeItemModalParentName');
  if (parentNameEl) {
    parentNameEl.textContent = `${parentItem.product_name} (${parentItem.box_type || 'কার্টন'}) - এর ফ্রি আইটেম`;
  }

  const searchInput = document.getElementById('freeItemSearchInput');
  if (searchInput) searchInput.value = '';

  renderFreeItemsList('');
  updateFreeItemTotalCounter();

  const modal = document.getElementById('freeItemPickerModal');
  const content = document.getElementById('freeItemPickerModalContent');

  if (modal.parentElement !== document.body) {
    document.body.appendChild(modal);
  }

  modal.classList.remove('hidden', 'pointer-events-none');
  setTimeout(() => {
    modal.classList.remove('opacity-0');
    if (content) {
      content.classList.remove('scale-95');
      content.classList.add('scale-100');
    }
    if (searchInput) searchInput.focus();
  }, 10);
}

function closeFreeItemModal() {
  const modal = document.getElementById('freeItemPickerModal');
  const content = document.getElementById('freeItemPickerModalContent');

  if (content) {
    content.classList.remove('scale-100');
    content.classList.add('scale-95');
  }
  modal.classList.add('opacity-0');

  setTimeout(() => {
    modal.classList.add('hidden', 'pointer-events-none');
    currentFreeItemParentIdx = null;
    tempFreeItems = {};
  }, 200);
}

function filterFreeItemProducts(query) {
  renderFreeItemsList(query);
}

function renderFreeItemsList(filterText) {
  const container = document.getElementById('freeItemListContainer');
  if (!container) return;
  container.innerHTML = '';

  const q = (filterText || '').toLowerCase().trim();

  // Show selected items first, then others
  const allList = (ALL_SR_PRODUCTS || []).filter(p => {
    if (!q) return true;
    const name = (p.name || '').toLowerCase();
    const comp = (p.company_name || '').toLowerCase();
    return name.includes(q) || comp.includes(q);
  }).sort((a, b) => {
    const aSelected = (tempFreeItems[a.id]?.qty || 0) > 0 ? 1 : 0;
    const bSelected = (tempFreeItems[b.id]?.qty || 0) > 0 ? 1 : 0;
    return bSelected - aSelected;
  });

  if (allList.length === 0) {
    container.innerHTML = `
      <div class="py-8 text-center text-slate-400 font-siliguri">
        <i class="fa-solid fa-gift text-2xl mb-1 text-slate-300"></i>
        <div class="text-xs font-bold">কোনো ফ্রি পণ্য পাওয়া যায়নি।</div>
      </div>
    `;
    return;
  }

  allList.forEach(p => {
    const pid = parseInt(p.id);
    const curQty = tempFreeItems[pid]?.qty || 0;
    const isSelected = curQty > 0;

    const row = document.createElement('div');
    row.className = `p-2.5 rounded-xl border transition flex items-center justify-between gap-2.5 ${
      isSelected ? 'bg-amber-50/60 border-amber-300 shadow-2xs' : 'bg-white border-slate-200 hover:bg-slate-50'
    }`;

    row.innerHTML = `
      <div class="flex items-center gap-2.5 min-w-0 flex-1">
        <div class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center text-slate-400 shrink-0">
          ${p.image ? `
            <img src="<?= url('') ?>${p.image}" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML='<i class=\\\'fa-solid fa-gift text-amber-500 text-xs\\\'></i>'">
          ` : `
            <i class="fa-solid fa-gift text-amber-500 text-xs"></i>
          `}
        </div>
        <div class="min-w-0 flex-1">
          <div class="font-bold text-slate-900 text-xs leading-snug truncate font-siliguri">
            ${p.name}
          </div>
          <div class="text-[10px] text-slate-400 font-mono mt-0.5">
            ${p.company_name || 'কোম্পানি'} • স্টক: <span class="font-bold ${parseInt(p.stock || 0) > 0 ? 'text-emerald-600' : 'text-rose-500'}">${parseInt(p.stock || 0)}টি</span>
          </div>
        </div>
      </div>

      <!-- Quantity Adjuster -->
      <div class="flex items-center gap-1 shrink-0">
        <button type="button" onclick="changeFreeItemQty(${pid}, -1)" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-rose-100 text-slate-600 hover:text-rose-600 transition flex items-center justify-center font-bold text-xs active:scale-95 border border-slate-200">
          <i class="fa-solid fa-minus text-[10px]"></i>
        </button>
        <input type="number" min="0" value="${curQty}" id="free-qty-input-${pid}" onchange="setFreeItemQty(${pid}, this.value)" class="w-12 text-center font-mono font-bold text-xs bg-white border border-slate-200 rounded-lg py-1 px-1 focus:border-amber-500 focus:outline-none shadow-3xs">
        <button type="button" onclick="changeFreeItemQty(${pid}, 1)" class="w-7 h-7 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 transition flex items-center justify-center font-bold text-xs active:scale-95 border border-amber-300">
          <i class="fa-solid fa-plus text-[10px]"></i>
        </button>
      </div>
    `;

    container.appendChild(row);
  });
}

function changeFreeItemQty(prodId, delta) {
  const prod = (ALL_SR_PRODUCTS || []).find(p => parseInt(p.id) === prodId);
  const curQty = tempFreeItems[prodId]?.qty || 0;
  const newQty = Math.max(0, curQty + delta);

  if (newQty === 0) {
    delete tempFreeItems[prodId];
  } else {
    tempFreeItems[prodId] = {
      qty: newQty,
      name: prod?.name || 'ফ্রি পণ্য'
    };
  }

  const input = document.getElementById(`free-qty-input-${prodId}`);
  if (input) input.value = newQty;

  updateFreeItemTotalCounter();
}

function setFreeItemQty(prodId, val) {
  const prod = (ALL_SR_PRODUCTS || []).find(p => parseInt(p.id) === prodId);
  const newQty = Math.max(0, parseInt(val) || 0);

  if (newQty === 0) {
    delete tempFreeItems[prodId];
  } else {
    tempFreeItems[prodId] = {
      qty: newQty,
      name: prod?.name || 'ফ্রি পণ্য'
    };
  }

  const input = document.getElementById(`free-qty-input-${prodId}`);
  if (input) input.value = newQty;

  updateFreeItemTotalCounter();
}

function updateFreeItemTotalCounter() {
  const total = Object.values(tempFreeItems).reduce((sum, f) => sum + (f.qty || 0), 0);
  const countEl = document.getElementById('freeItemTotalSelectedCount');
  if (countEl) countEl.innerText = `${total}টি`;
}

function saveFreeItemSelection() {
  if (currentFreeItemParentIdx === null || !editingOrder || !editingOrder.items[currentFreeItemParentIdx]) {
    closeFreeItemModal();
    return;
  }

  const parentItem = editingOrder.items[currentFreeItemParentIdx];
  parentItem.free_items = { ...tempFreeItems };

  renderEditOrderItems();
  closeFreeItemModal();
  showToast(`"${parentItem.product_name}" এর ফ্রি আইটেম আপডেট করা হয়েছে।`, 'success');
}

// ── Delete Order with 10-Second Countdown Safety Lock ────────────────────────
let deleteCountdownTimer = null;
let deleteCountdownSeconds = 10;

function startDeleteCountdown() {
  document.getElementById('editModalDefaultActions').classList.add('hidden');
  document.getElementById('editModalDeleteCountdownArea').classList.remove('hidden');

  deleteCountdownSeconds = 10;
  const btnConfirm = document.getElementById('btnConfirmDeleteOrder');
  const btnText = document.getElementById('deleteTimerBtnText');
  const icon = document.getElementById('deleteTimerIcon');

  btnConfirm.disabled = true;
  btnConfirm.className = 'px-5 py-2 bg-rose-200 text-rose-400 cursor-not-allowed opacity-80 rounded-xl text-xs sm:text-sm font-bold flex items-center gap-2 transition duration-200';
  icon.className = 'fa-solid fa-clock text-xs';
  btnText.innerText = `ডিলিট নিশ্চিত করুন (10s)`;

  if (deleteCountdownTimer) clearInterval(deleteCountdownTimer);

  deleteCountdownTimer = setInterval(() => {
    deleteCountdownSeconds--;
    if (deleteCountdownSeconds > 0) {
      btnText.innerText = `ডিলিট নিশ্চিত করুন (${deleteCountdownSeconds}s)`;
    } else {
      clearInterval(deleteCountdownTimer);
      deleteCountdownTimer = null;
      btnConfirm.disabled = false;
      btnConfirm.className = 'px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white shadow-md active:scale-95 cursor-pointer rounded-xl text-xs sm:text-sm font-bold flex items-center gap-2 transition duration-200';
      icon.className = 'fa-solid fa-trash-can text-xs';
      btnText.innerText = 'হ্যাঁ, ডিলিট করুন';
    }
  }, 1000);
}

function cancelDeleteCountdown() {
  if (deleteCountdownTimer) {
    clearInterval(deleteCountdownTimer);
    deleteCountdownTimer = null;
  }
  const confirmArea = document.getElementById('editModalDeleteCountdownArea');
  const defaultActions = document.getElementById('editModalDefaultActions');
  if (confirmArea) confirmArea.classList.add('hidden');
  if (defaultActions) defaultActions.classList.remove('hidden');
}

async function executeOrderDelete() {
  if (!editingOrder) return;
  const orderId = editingOrder.id;

  const btnConfirm = document.getElementById('btnConfirmDeleteOrder');
  const btnText = document.getElementById('deleteTimerBtnText');
  const spinner = document.getElementById('deleteTimerSpinner');
  const icon = document.getElementById('deleteTimerIcon');

  btnConfirm.disabled = true;
  btnText.innerText = 'ডিলিট হচ্ছে...';
  if (icon) icon.classList.add('hidden');
  if (spinner) spinner.classList.remove('hidden');

  try {
    const formData = new FormData();
    formData.append('order_id', orderId);

    const response = await fetch('<?= url("sr/orders/delete") ?>', {
      method: 'POST',
      body: formData
    });

    const result = await response.json();

    if (result.success) {
      // Remove from ORDERS_MAP
      delete ORDERS_MAP[orderId];

      // Remove row from table with animation
      const row = document.getElementById(`order-row-${orderId}`);
      if (row) {
        row.style.transition = 'all 0.3s ease';
        row.style.opacity = '0';
        row.style.transform = 'scale(0.95)';
        setTimeout(() => {
          row.remove();
          // Check if table empty
          const tbody = document.getElementById('tableBody');
          if (tbody && Object.keys(ORDERS_MAP).length === 0) {
            tbody.innerHTML = `
              <tr id="emptyRow">
                <td colspan="3" class="p-12 text-center text-slate-400 bg-white font-siliguri">
                  <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-300 flex items-center justify-center text-xl mx-auto mb-2"><i class="fa-solid fa-box-open"></i></div>
                  <span class="text-xs font-medium">কোনো অর্ডারের তথ্য পাওয়া যায়নি।</span>
                </td>
              </tr>
            `;
          }
        }, 300);
      }

      // Recalculate table footer
      recalculateTableFooterTotals();

      showToast(result.message || 'অর্ডার সফলভাবে মুছে ফেলা হয়েছে!', 'success');
      closeEditOrderModal();
    } else {
      alert(result.message || 'অর্ডার ডিলিট করতে সমস্যা হয়েছে।');
      btnConfirm.disabled = false;
      btnText.innerText = 'হ্যাঁ, ডিলিট করুন';
      if (icon) icon.classList.remove('hidden');
      if (spinner) spinner.classList.add('hidden');
    }
  } catch (err) {
    console.error('Order delete error:', err);
    alert('সার্ভার এরর: অর্ডার ডিলিট করা সম্ভব হয়নি।');
    btnConfirm.disabled = false;
    btnText.innerText = 'হ্যাঁ, ডিলিট করুন';
    if (icon) icon.classList.remove('hidden');
    if (spinner) spinner.classList.add('hidden');
  }
}

// ── Submit Order Edit via AJAX (In-Place JS Sync) ─────────────────────────────
async function submitOrderEdit() {
  if (!editingOrder) return;

  const validItems = editingOrder.items.filter(item => item.total_qty > 0);
  if (validItems.length === 0) {
    alert('অর্ডারে অন্তত একটি পণ্যের পরিমাণ থাকতে হবে।');
    return;
  }

  const btnConfirm = document.getElementById('btnConfirmOrderEdit');
  const btnText = document.getElementById('btnConfirmText');
  const btnIcon = document.getElementById('btnConfirmIcon');
  const btnSpinner = document.getElementById('btnConfirmSpinner');

  btnConfirm.disabled = true;
  btnText.innerText = 'সংরক্ষণ হচ্ছে...';
  if (btnIcon) btnIcon.classList.add('hidden');
  if (btnSpinner) btnSpinner.classList.remove('hidden');
  SRLoader.showOverlay('অর্ডার আপডেট করা হচ্ছে...', 'পরিবর্তন সংরক্ষণ হচ্ছে...');

  try {
    const formData = new FormData();
    formData.append('order_id', editingOrder.id);
    
    validItems.forEach(item => {
      formData.append('product_id[]', item.product_id);
      formData.append('quantity[]', item.total_qty);
      formData.append('unit_price[]', item.unit_price);
    });

    // Collect free items across all valid order products
    const freeItemsPayload = [];
    validItems.forEach(item => {
      if (item.free_items) {
        Object.entries(item.free_items).forEach(([freePid, fData]) => {
          const qty = typeof fData === 'object' ? parseInt(fData.qty) : parseInt(fData);
          if (qty > 0) {
            freeItemsPayload.push({
              product_id: item.product_id,
              free_product_id: parseInt(freePid),
              quantity: qty
            });
          }
        });
      }
    });
    formData.append('free_items', JSON.stringify(freeItemsPayload));

    const response = await fetch('<?= url("sr/orders/update") ?>', {
      method: 'POST',
      body: formData
    });

    const result = await response.json();

    if (result.success && result.order) {
      ORDERS_MAP[editingOrder.id] = result.order;
      updateOrderTableRow(result.order);
      recalculateTableFooterTotals();
      showToast(result.message || 'অর্ডার সফলভাবে আপডেট করা হয়েছে!', 'success');
      closeEditOrderModal();
    } else {
      alert(result.message || 'অর্ডার আপডেট করতে সমস্যা হয়েছে।');
    }
  } catch (err) {
    console.error('Order update error:', err);
    alert('সার্ভার এরর: অর্ডার আপডেট করা সম্ভব হয়নি।');
  } finally {
    SRLoader.hideOverlay();
    btnConfirm.disabled = false;
    btnText.innerText = 'সংরক্ষণ করুন (Save)';
    if (btnIcon) btnIcon.classList.remove('hidden');
    if (btnSpinner) btnSpinner.classList.add('hidden');
  }
}

// ── Update Order Table Row in Place (Real-Time JS DOM Sync) ───────────────────
function updateOrderTableRow(order) {
  const orderId = order.id;

  // Calculate order O/C
  let orderOC = 0;
  if (order.products && order.products.length > 0) {
    order.products.forEach(p => {
      orderOC += (parseFloat(p.unit_price || 0) - parseFloat(p.base_price || 0)) * parseInt(p.quantity || 0);
    });
  }

  // Update Total Cell
  const totalCell = document.getElementById(`order-total-cell-${orderId}`);
  if (totalCell) {
    const ocFormatted = Math.abs(orderOC).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
    const ocSign = orderOC >= 0 ? '+' : '-';
    const ocClass = orderOC > 0 ? 'text-emerald-500' : 'text-rose-500';
    
    totalCell.innerHTML = `
      ৳ ${parseFloat(order.total_amount || 0).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}
      ${orderOC !== 0 
        ? `<div class="text-[10px] font-bold mt-0.5 ${ocClass}" id="order-oc-badge-${orderId}">(${ocSign}৳${ocFormatted})</div>` 
        : `<div class="text-[10px] font-bold mt-0.5 text-slate-400" id="order-oc-badge-${orderId}" style="display:none;"></div>`
      }
    `;
  }

  // Update Row Status Badge
  const rowStBadge = document.getElementById(`order-row-status-${orderId}`);
  if (rowStBadge) {
    const stMap = {
      'pending': { label: 'প্যান্ডিং', cls: 'bg-amber-50 text-amber-700 border-amber-200' },
      'confirmed': { label: 'কনফার্মড', cls: 'bg-blue-50 text-blue-700 border-blue-200' },
      'dispatched': { label: 'ডিসপ্যাচড', cls: 'bg-indigo-50 text-indigo-700 border-indigo-200' },
      'in_transit': { label: 'অন দ্য ওয়ে', cls: 'bg-indigo-50 text-indigo-700 border-indigo-200' },
      'delivered': { label: 'ডেলিভার্ড', cls: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
      'partial': { label: 'আংশিক', cls: 'bg-amber-50 text-amber-800 border-amber-300' },
      'cancelled': { label: 'বাতিল', cls: 'bg-rose-50 text-rose-700 border-rose-200' }
    };
    const st = stMap[order.status] || { label: order.status, cls: 'bg-slate-50 text-slate-700 border-slate-200' };
    rowStBadge.className = `inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold border ${st.cls}`;
    rowStBadge.innerText = st.label;
  }

  // Update action buttons with refreshed order JSON
  const btnInvoice = document.getElementById(`btn-invoice-order-${orderId}`);
  if (btnInvoice) {
    btnInvoice.setAttribute('onclick', `openInvoiceModal(ORDERS_MAP[${orderId}])`);
  }
  const btnDelivery = document.getElementById(`btn-delivery-order-${orderId}`);
  if (btnDelivery) {
    btnDelivery.setAttribute('onclick', `openDeliveryMemoModal(ORDERS_MAP[${orderId}])`);
  }
  const btnEdit = document.getElementById(`btn-edit-order-${orderId}`);
  if (btnEdit) {
    btnEdit.setAttribute('onclick', `openEditOrderModal(ORDERS_MAP[${orderId}])`);
  }
}

// ── Recalculate Table Footer Grand Total (Real-Time JS Sync) ──────────────────
function recalculateTableFooterTotals() {
  let grandTotal = 0;
  let grandOC = 0;

  Object.values(ORDERS_MAP).forEach(ord => {
    grandTotal += parseFloat(ord.total_amount || 0);
    if (ord.products && ord.products.length > 0) {
      ord.products.forEach(p => {
        grandOC += (parseFloat(p.unit_price || 0) - parseFloat(p.base_price || 0)) * parseInt(p.quantity || 0);
      });
    }
  });

  const grandCell = document.getElementById('grandTotalCell');
  if (grandCell) {
    const ocFormatted = Math.abs(grandOC).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
    const ocSign = grandOC >= 0 ? '+' : '-';
    const ocClass = grandOC > 0 ? 'text-emerald-500' : 'text-rose-500';

    grandCell.innerHTML = `
      ৳ ${grandTotal.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}
      ${grandOC !== 0 
        ? `<div class="text-[10px] font-bold mt-0.5 ${ocClass}" id="grandTotalOCCell">(${ocSign}৳${ocFormatted})</div>` 
        : `<div class="text-[10px] font-bold mt-0.5 text-slate-400" id="grandTotalOCCell" style="display:none;"></div>`
      }
    `;
  }
}

// ── Beautiful Retailer Invoice Modal ─────────────────────────────────────────
function openInvoiceModal(orderData) {
  const orderId = orderData.id;
  const order = ORDERS_MAP[orderId] || orderData;

  const modal = document.getElementById('invoiceModal');
  const modalContent = document.getElementById('invoiceModalContent');

  // Order & Retailer Header Data
  const retailerName = order.retailer_name || order.dealer_name || 'সাধারণ কাস্টমার';
  const retailerPhone = order.retailer_phone || 'N/A';
  const retailerAddress = order.retailer_address || 'ঠিকানা দেওয়া নেই';

  document.getElementById('invOrderId').innerText = '#ORD-' + order.id;
  document.getElementById('invDate').innerText = formatDateTime12Hr(order.created_at);
  document.getElementById('invRetailerName').innerText = retailerName;
  document.getElementById('invRetailerPhone').innerText = retailerPhone;
  document.getElementById('invRetailerAddress').innerText = retailerAddress;
  document.getElementById('invDealerName').innerText = 'ডিলার: ' + (order.dealer_name || 'Direct');

  // Status Badge
  const stBadge = document.getElementById('invStatusBadge');
  const statusMap = {
    'pending': { label: 'প্যান্ডিং', cls: 'bg-amber-100 text-amber-800' },
    'confirmed': { label: 'কনফার্মড', cls: 'bg-blue-100 text-blue-800' },
    'dispatched': { label: 'ডিসপ্যাচড', cls: 'bg-indigo-100 text-indigo-800' },
    'in_transit': { label: 'অন দ্য ওয়ে', cls: 'bg-indigo-100 text-indigo-800' },
    'delivered': { label: 'ডেলিভার্ড', cls: 'bg-emerald-100 text-emerald-800' },
    'partial': { label: 'আংশিক', cls: 'bg-amber-100 text-amber-800' },
    'cancelled': { label: 'বাতিল', cls: 'bg-rose-100 text-rose-800' }
  };
  const stInfo = statusMap[order.status] || { label: order.status, cls: 'bg-slate-100 text-slate-800' };
  stBadge.innerText = stInfo.label;
  stBadge.className = 'font-extrabold px-2.5 py-0.5 rounded-md text-[10px] ' + stInfo.cls;

  // Populate Table Rows
  const tableBody = document.getElementById('invItemsTableBody');
  tableBody.innerHTML = '';

  let totalQtyPcs = 0;
  let totalItemsCount = 0;

  if (order.products && order.products.length > 0) {
    totalItemsCount = order.products.length;
    order.products.forEach((prod, index) => {
      const qty = parseInt(prod.quantity || 0);
      const ppb = parseInt(prod.pieces_per_box || 1) || 1;
      totalQtyPcs += qty;

      const boxes = Math.floor(qty / ppb);
      const pcs = qty % ppb;
      const packingStr = (boxes > 0 ? boxes + ' কার্টন ' : '') + (pcs > 0 || boxes === 0 ? pcs + ' পিস' : '');
      const itemTotal = parseFloat(prod.total_price || (qty * parseFloat(prod.unit_price || 0)));

      // O/C = (unit_price - base_price) × qty
      const unitPrice  = parseFloat(prod.unit_price  || 0);
      const basePrice  = parseFloat(prod.base_price  || 0);
      const itemOC     = (unitPrice - basePrice) * qty;
      const ocAbs      = Math.abs(itemOC).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
      const ocSign     = itemOC >= 0 ? '+' : '-';
      const ocColor    = itemOC >= 0 ? '#10b981' : '#f43f5e';
      const ocHtml     = itemOC !== 0
        ? `<div style="font-size:9px;font-weight:700;color:${ocColor};margin-top:1px;">(${ocSign}৳${ocAbs})</div>`
        : '';

      let freeBadgeHtml = '';
      if (prod.free_items && prod.free_items.length > 0) {
        const freeBadges = prod.free_items.map(fi => {
          return `<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-200 text-[9px] font-semibold"><i class="fa-solid fa-gift text-amber-600 text-[8px]"></i> ${fi.free_product_name || 'ফ্রি'}: <b>${fi.quantity}</b>টি</span>`;
        }).join(' ');
        freeBadgeHtml = `<div class="mt-1 flex flex-wrap gap-1">${freeBadges}</div>`;
      }

      const tr = document.createElement('tr');
      tr.className = 'bg-white hover:bg-slate-50/30 transition-colors';
      tr.innerHTML = `
        <td class="py-2 px-2.5 font-mono font-bold text-slate-400 text-[10px]">${index + 1}</td>
        <td class="py-2 px-2.5">
          <div class="font-bold text-slate-800 text-[11px] leading-tight break-words font-siliguri">${prod.product_name || 'পণ্য'}</div>
          ${freeBadgeHtml}
        </td>
        <td class="py-2 px-2.5 text-center font-semibold text-slate-600 text-[11px]">${packingStr}</td>
        <td class="py-2 px-2.5 text-center font-mono font-bold text-slate-700 text-[11px]">${qty} পিস</td>
        <td class="py-2 px-2.5 text-right font-mono text-slate-600 text-[11px]">৳ ${parseFloat(prod.unit_price || 0).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
        <td class="py-2 px-2.5 text-right font-mono font-bold text-slate-900 text-[11px]">
          ৳ ${itemTotal.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}
          ${ocHtml}
        </td>
      `;
      tableBody.appendChild(tr);
    });
  } else {
    tableBody.innerHTML = '<tr><td colspan="6" class="py-4 text-center text-slate-400 text-xs font-bold font-siliguri">কোনো আইটেম পাওয়া যায়নি।</td></tr>';
  }

  // Summary Totals
  document.getElementById('invTotalItems').innerText = totalItemsCount + 'টি';
  document.getElementById('invTotalQtyPcs').innerText = totalQtyPcs + ' পিস';
  document.getElementById('invGrandTotal').innerText = '৳ ' + parseFloat(order.total_amount || 0).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});

  // Portal to body to guarantee on-top rendering
  if (modal.parentElement !== document.body) {
    document.body.appendChild(modal);
  }

  document.body.classList.add('overflow-hidden');
  modal.classList.remove('hidden', 'pointer-events-none');
  setTimeout(() => {
    modal.classList.remove('opacity-0');
    modalContent.classList.remove('scale-95');
    modalContent.classList.add('scale-100');
  }, 10);
}

function closeInvoiceModal() {
  const modal = document.getElementById('invoiceModal');
  const modalContent = document.getElementById('invoiceModalContent');

  modalContent.classList.remove('scale-100');
  modalContent.classList.add('scale-95');
  modal.classList.add('opacity-0');
  document.body.classList.remove('overflow-hidden');

  setTimeout(() => {
    modal.classList.add('hidden', 'pointer-events-none');
  }, 200);
}

function toggleRetailerName(element, fullName, shortName) {
  if (element.innerText.trim().endsWith('..')) {
    element.innerText = fullName;
  } else {
    element.innerText = shortName;
  }
}

// ── Delivery Memo Modal Logic ────────────────────────────────────────────────
function openDeliveryMemoModal(orderData) {
  const orderId = orderData.id;
  const order = ORDERS_MAP[orderId] || orderData;

  const modal = document.getElementById('deliveryMemoModal');
  const modalContent = document.getElementById('deliveryMemoModalContent');

  const retailerName = order.retailer_name || order.dealer_name || 'সাধারণ কাস্টমার';
  const retailerSub = (order.retailer_phone ? order.retailer_phone : '') + (order.retailer_address ? ' · ' + order.retailer_address : '');

  document.getElementById('delRetailerName').innerText = retailerName;
  // Set Real-Time Status Badge
  const statusBadge = document.getElementById('delStatusBadge');
  if (statusBadge) {
    const statusMap = {
      'pending': { label: 'প্যান্ডিং (Pending)', cls: 'bg-amber-100 text-amber-800 border border-amber-200' },
      'confirmed': { label: 'কনফার্মড (Confirmed)', cls: 'bg-blue-100 text-blue-800 border border-blue-200' },
      'dispatched': { label: 'ডিসপ্যাচড / অন দ্য ওয়ে', cls: 'bg-indigo-100 text-indigo-800 border border-indigo-200' },
      'in_transit': { label: 'অন দ্য ওয়ে (In Transit)', cls: 'bg-indigo-100 text-indigo-800 border border-indigo-200' },
      'delivered': { label: 'ডেলিভার্ড / সম্পূর্ণ (Delivered)', cls: 'bg-emerald-100 text-emerald-800 border border-emerald-200' },
      'partial': { label: 'আংশিক ডেলিভারি (Partial)', cls: 'bg-amber-100 text-amber-800 border border-amber-200' },
      'cancelled': { label: 'বাতিল (Cancelled)', cls: 'bg-rose-100 text-rose-800 border border-rose-200' }
    };
    const st = statusMap[order.status] || { label: order.status || 'Unknown', cls: 'bg-slate-100 text-slate-700 border border-slate-200' };
    statusBadge.className = `inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold ${st.cls}`;
    statusBadge.innerText = st.label;
  }
  
  const createdDate = order.created_at ? new Date(order.created_at.replace(/-/g, '/')) : new Date();
  const day = bnNum(createdDate.getDate());
  const monthNames = ['জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর'];
  const month = monthNames[createdDate.getMonth()] || '';
  const year = bnNum(createdDate.getFullYear());
  document.getElementById('delDate').innerText = `${day} ${month} ${year}`;

  const isCancelled = order.status === 'cancelled';
  const isDelivered = order.status === 'delivered';
  const isPartial = order.status === 'partial';

  const items = (order.products || []).map(p => {
    const ordQty = parseInt(p.quantity || 0);
    let gotQty;
    if (isCancelled) {
      gotQty = 0;
    } else if (p.delivered_quantity !== undefined && p.delivered_quantity !== null) {
      gotQty = parseInt(p.delivered_quantity);
    } else if (p.delivered_qty !== undefined && p.delivered_qty !== null) {
      gotQty = parseInt(p.delivered_qty);
    } else if (isDelivered) {
      gotQty = ordQty;
    } else {
      gotQty = ordQty;
    }
    const unitPrice = parseFloat(p.unit_price || p.price || 0);
    return {
      name: p.product_name || p.name || 'পণ্য',
      pack: (p.box_type || 'কার্টন') + ' (' + (p.pieces_per_box || 1) + ' পিস)',
      ord: ordQty,
      got: gotQty,
      price: unitPrice
    };
  });

  const totalOrd = items.reduce((a, i) => a + i.ord, 0);
  const totalGot = items.reduce((a, i) => a + i.got, 0);
  const totalBak = Math.max(0, totalOrd - totalGot);

  const valOrd = items.reduce((a, i) => a + (i.ord * i.price), 0);
  const valGot = items.reduce((a, i) => a + (i.got * i.price), 0);
  const valBak = Math.max(0, valOrd - valGot);

  document.getElementById('delHOrd').innerText = bnNum(totalOrd);
  document.getElementById('delHGot').innerText = bnNum(totalGot);
  document.getElementById('delHBak').innerText = bnNum(totalBak);

  if (isCancelled) {
    document.getElementById('delEq').innerText = `০ + ${bnNum(totalOrd)} = ${bnNum(totalOrd)} পিস — সম্পূর্ণ অর্ডার বাতিল`;
  } else if (totalBak === 0) {
    document.getElementById('delEq').innerText = `${bnNum(totalGot)} + ০ = ${bnNum(totalOrd)} পিস — সম্পূর্ণ মাল ডেলিভারি হয়েছে`;
  } else {
    document.getElementById('delEq').innerText = `${bnNum(totalGot)} + ${bnNum(totalBak)} = ${bnNum(totalOrd)} পিস — হিসাব মিলেছে`;
  }

  document.getElementById('delMOrd').innerText = tkFormat(valOrd);
  document.getElementById('delMGot').innerText = tkFormat(valGot);
  document.getElementById('delMBak').innerText = '−' + tkFormat(valBak);

  document.getElementById('delPay').innerText = tkFormat(valGot);
  document.getElementById('delS1').innerText = bnNum(totalBak) + ' পিস';
  document.getElementById('delS2').innerText = tkFormat(valBak);

  const skipSec = document.querySelector('.del-skip-sec');
  const laterSec = document.querySelector('.del-later-sec');
  if (skipSec && laterSec) {
    if (isCancelled) {
      skipSec.style.display = 'block';
      laterSec.style.display = 'none';
      skipSec.innerHTML = `পুরো অর্ডারটি <b>বাতিল</b> করা হয়েছে। কোনো টাকা <b>দেবেন না</b>।`;
    } else if (totalBak > 0) {
      skipSec.style.display = 'block';
      laterSec.style.display = 'block';
      skipSec.innerHTML = `<b id="delS1">${bnNum(totalBak)} পিস</b> ফেরত গেছে। এই <b id="delS2">${tkFormat(valBak)}</b> টাকা আজ <b>দেবেন না</b>।`;
    } else {
      skipSec.style.display = 'none';
      laterSec.style.display = 'none';
    }
  }

  document.getElementById('delSRSign').innerText = '<?= h(Auth::name()) ?>';
  document.getElementById('delRetSign').innerText = retailerName;
  document.getElementById('delSup').innerText = bnNum('01700-000000');
  document.getElementById('delPg').innerText = bnNum('1') + '/' + bnNum('1');

  const rowsContainer = document.getElementById('delRows');
  rowsContainer.innerHTML = items.map(it => {
    const b = Math.max(0, it.ord - it.got);
    return `<div class="del-row-item ${b ? '' : 'done'}">
      <div class="nm pr-1">
        <b>${it.name}</b>
        <small>${it.pack} · ${tkFormat(it.price)} দরে</small>
      </div>
      <div class="n ord">${bnNum(it.ord)}</div>
      <div class="n got">${bnNum(it.got)}</div>
      <div class="n bak ${b ? '' : 'zero'}">${b ? bnNum(b) : '—'}</div>
    </div>`;
  }).join('');

  if (modal.parentElement !== document.body) {
    document.body.appendChild(modal);
  }

  document.body.classList.add('overflow-hidden');
  modal.classList.remove('hidden', 'pointer-events-none');
  setTimeout(() => {
    modal.classList.remove('opacity-0');
    modalContent.classList.remove('scale-95');
    modalContent.classList.add('scale-100');
  }, 10);
}

function closeDeliveryMemoModal() {
  const modal = document.getElementById('deliveryMemoModal');
  const modalContent = document.getElementById('deliveryMemoModalContent');

  modalContent.classList.remove('scale-100');
  modalContent.classList.add('scale-95');
  modal.classList.add('opacity-0');
  document.body.classList.remove('overflow-hidden');

  setTimeout(() => {
    modal.classList.add('hidden', 'pointer-events-none');
  }, 200);
}

function printDeliveryMemo() {
  window.print();
}

function bnNum(v) {
  return String(v).replace(/[0-9]/g, d => '০১২৩৪৫৬৭৮৯'[d]);
}

function tkFormat(v) {
  return '৳' + bnNum(Math.round(v).toLocaleString('en-US'));
}
</script>
