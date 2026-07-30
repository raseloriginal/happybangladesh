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
</style>

<div class="p-3 sm:p-5 space-y-4 pb-28 max-w-5xl mx-auto font-siliguri text-slate-800 print:p-0 print:max-w-none print:bg-white">

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
               onchange="document.getElementById('dateToInput').value = this.value; document.getElementById('dateForm').submit();" 
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
          <th class="p-3 bg-slate-50/80 border-r border-slate-200/50 w-[55%] font-siliguri">
            দোকান / কাস্টমার
          </th>
          <th class="p-3 bg-slate-50/80 text-right border-r border-slate-200/50 w-[30%] font-siliguri">
            মোট টাকা
          </th>
          <th class="p-3 bg-slate-50/80 text-center w-[15%] font-siliguri">
            ইনভয়েস
          </th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 font-sans" id="tableBody">
        <?php 
          $grandTotalAmount = 0; 
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
              $rName = !empty($ord['retailer_name']) ? $ord['retailer_name'] : (!empty($ord['dealer_name']) ? $ord['dealer_name'] : 'সাধারণ কাস্টমার');
              $rPhone = !empty($ord['retailer_phone']) ? $ord['retailer_phone'] : 'N/A';
            ?>
            <tr class="retailer-order-row hover:bg-slate-50/40 transition-colors">
              
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
                  
                  <!-- Phone info subtext with icons -->
                  <div class="text-[10px] text-slate-400 font-medium mt-1 flex items-center gap-1.5 flex-wrap">
                    <div class="flex items-center gap-1">
                      <i class="fa-solid fa-phone text-slate-300 text-[9px]"></i>
                      <span><?= h($rPhone) ?></span>
                    </div>
                  </div>
                </div>
              </td>

              <!-- Total Amount Cell -->
              <td class="p-3 text-right border-r border-slate-100 align-middle bg-white font-mono font-bold text-emerald-700 text-xs sm:text-sm">
                ৳ <?= number_format((float)$ord['total_amount'], 2) ?>
              </td>

              <!-- Action Invoice Button -->
              <td class="p-3 text-center align-middle bg-white">
                <button type="button" 
                        onclick="openInvoiceModal(<?= htmlspecialchars(json_encode($ord), ENT_QUOTES, 'UTF-8') ?>)"
                        class="w-8 h-8 rounded-lg bg-blue-50 border border-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white transition duration-200 flex items-center justify-center shadow-3xs mx-auto"
                        title="ইনভয়েস দেখুন">
                  <i class="fa-solid fa-file-invoice text-xs"></i>
                </button>
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
          <td class="p-3 text-right border-r border-slate-200 font-mono font-black text-slate-950 text-[13px]">
            ৳ <?= number_format($grandTotalAmount, 2) ?>
          </td>
          <td class="p-3 bg-slate-50/80"></td>
        </tr>
      </tfoot>
    </table>
  </div>

</div>

<!-- ========================================================================= -->
<!-- BEAUTIFUL RETAILER INVOICE MODAL                                         -->
<!-- ========================================================================= -->
<div id="invoiceModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[110] hidden opacity-0 transition-opacity duration-200 flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
  
  <div id="invoiceModalContent" class="bg-white w-full max-w-lg rounded-2xl p-5 sm:p-6 shadow-xl space-y-4 transform scale-95 transition-transform duration-200 border border-slate-100 my-auto text-slate-800 font-siliguri">
    
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
          <div class="text-slate-555 mt-0.5 font-mono text-[11px]" id="invRetailerPhone">01700000000</div>
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


<!-- Style for Print View -->
<style>
@media print {
  body * {
    visibility: hidden;
  }
  #invoiceModal, #invoiceModal * {
    visibility: visible;
  }
  #invoiceModal {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: auto;
    background: white !important;
    padding: 0 !important;
  }
  #invoiceModalContent {
    box-shadow: none !important;
    border: none !important;
    max-width: 100% !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 20px !important;
  }
  .print\:hidden {
    display: none !important;
  }
}
</style>

<script>
// ── 12-Hour Time Format Helper (e.g. 23 Jul 2026, 1:50 PM) ───────────────────
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
  hours = hours ? hours : 12; // 0 becomes 12
  
  return `${day} ${month} ${year}, ${hours}:${minutes} ${ampm}`;
}

// ── Beautiful Retailer Invoice Modal ──────────────────────────────────
function openInvoiceModal(order) {
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
    'delivered': { label: 'ডেলিভার্ড', cls: 'bg-emerald-100 text-emerald-800' },
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

      const tr = document.createElement('tr');
      tr.className = 'bg-white hover:bg-slate-50/30 transition-colors';
      tr.innerHTML = `
        <td class="py-2 px-2.5 font-mono font-bold text-slate-400 text-[10px]">${index + 1}</td>
        <td class="py-2 px-2.5">
          <div class="font-bold text-slate-800 text-[11px] leading-tight break-words">${prod.product_name || 'পণ্য'}</div>
        </td>
        <td class="py-2 px-2.5 text-center font-semibold text-slate-600 text-[11px]">${packingStr}</td>
        <td class="py-2 px-2.5 text-center font-mono font-bold text-slate-700 text-[11px]">${qty} পিস</td>
        <td class="py-2 px-2.5 text-right font-mono text-slate-600 text-[11px]">৳ ${parseFloat(prod.unit_price || 0).toLocaleString()}</td>
        <td class="py-2 px-2.5 text-right font-mono font-bold text-slate-900 text-[11px]">৳ ${itemTotal.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
      `;
      tableBody.appendChild(tr);
    });
  } else {
    tableBody.innerHTML = '<tr><td colspan="6" class="py-4 text-center text-slate-400 text-xs font-bold">কোনো আইটেম পাওয়া যায়নি।</td></tr>';
  }

  // Summary Totals
  document.getElementById('invTotalItems').innerText = totalItemsCount + 'টি';
  document.getElementById('invTotalQtyPcs').innerText = totalQtyPcs + ' পিস';
  document.getElementById('invGrandTotal').innerText = '৳ ' + parseFloat(order.total_amount || 0).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});

  modal.classList.remove('hidden');
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
  setTimeout(() => {
    modal.classList.add('hidden');
  }, 200);
}

function toggleRetailerName(element, fullName, shortName) {
  if (element.innerText.trim().endsWith('..')) {
    element.innerText = fullName;
  } else {
    element.innerText = shortName;
  }
}
</script>
