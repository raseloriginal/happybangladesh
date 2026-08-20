<?php 
$pageTitle = 'Operations: SR Orders'; 

// All products are passed from ManagerController operations()
$allProducts = $allProducts ?? [];
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
  .font-siliguri {
    font-family: 'Hind Siliguri', 'Inter', sans-serif;
  }
  input[type=number]::-webkit-inner-spin-button, 
  input[type=number]::-webkit-outer-spin-button { 
    -webkit-appearance: none; 
    margin: 0; 
  }
  input[type=number] {
    -moz-appearance: textfield;
  }
  .marker-pin-blue { color: #2563eb; }
  .marker-pin-green { color: #16a34a; }
  .marker-pin-orange { color: #ea580c; }
  .marker-pin-red { color: #dc2626; }
</style>

<div class="p-3 sm:p-5 space-y-4 pb-28 max-w-7xl mx-auto font-siliguri text-slate-800 print:p-0 print:max-w-none print:bg-white">

  <!-- Toast Notification Container -->
  <div id="toastContainer" class="fixed top-5 right-5 z-[100000] space-y-2 pointer-events-none"></div>

  <!-- Top Tab Navigation Switcher -->
  <div class="flex items-center gap-2 border-b border-slate-200/80 pb-2 mb-3 print:hidden">
    <button type="button" id="tabBtnSrOrders" onclick="switchOperationsTab('sr_orders')" 
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition-all shadow-2xs bg-blue-600 text-white shadow-blue-500/20 active:scale-95">
      <i class="fa-solid fa-cart-shopping text-sm"></i>
      <span>SR Orders (অর্ডার সারণী)</span>
    </button>
    <button type="button" id="tabBtnDsrDelivery" onclick="switchOperationsTab('dsr_delivery')" 
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition-all text-slate-600 hover:text-slate-900 bg-white border border-slate-200 active:scale-95">
      <i class="fa-solid fa-truck-ramp-box text-sm text-slate-500"></i>
      <span>DSR Delivery (ডিএসআর ডেলিভারি প্যানেল)</span>
    </button>
  </div>

  <!-- ═════════════════════════════════════════════════════════════
       TAB 1: SR ORDERS VIEW
  ══════════════════════════════════════════════════════════════ -->
  <div id="viewSrOrders" class="space-y-4">
    <!-- Premium Minimal Header Card -->
    <div class="bg-white/95 backdrop-blur-md px-4 py-3 sm:px-6 sm:py-4 rounded-2xl border border-slate-200/60 shadow-2xs flex flex-wrap items-center justify-between gap-3 print:shadow-none print:border-none print:p-0">
      <div class="flex items-center gap-3">
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 leading-tight tracking-tight">
          Operations: SR Orders
        </h1>
      </div>
    
    <!-- Filters (Date, SR, Search) -->
    <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto mt-3 sm:mt-0">
      
      <!-- Date Picker -->
      <div class="relative w-full sm:w-auto">
        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
          <i class="fa-regular fa-calendar text-slate-400 text-sm"></i>
        </div>
        <input type="date" id="filterDate" value="<?= date('Y-m-d') ?>" 
               onchange="loadOrders()" 
               class="bg-slate-50 border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 pr-3 py-2 cursor-pointer shadow-sm hover:border-slate-300 transition outline-none">
      </div>

      <!-- SR Filter Dropdown -->
      <div class="relative w-full sm:w-auto">
        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
          <i class="fa-solid fa-user-tie text-slate-400 text-sm"></i>
        </div>
        <select id="filterSr" onchange="loadOrders()" class="bg-slate-50 border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 pr-8 py-2 cursor-pointer shadow-sm hover:border-slate-300 transition outline-none appearance-none">
          <option value="">সকল SR</option>
          <!-- Populated via JS -->
        </select>
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
          <i class="fa-solid fa-chevron-down text-slate-400 text-xs"></i>
        </div>
      </div>

      <!-- Search Input -->
      <div class="relative w-full sm:w-64">
        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
          <i class="fa-solid fa-search text-slate-400 text-sm"></i>
        </div>
        <input type="text" id="filterSearch" onkeyup="filterOrdersClientSide()" placeholder="দোকান/SR খুঁজুন..." 
               class="bg-slate-50 border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 pr-3 py-2 shadow-sm hover:border-slate-300 transition outline-none">
      </div>
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
        <tr>
          <td colspan="3" class="p-12 text-center text-slate-400 bg-white font-siliguri">
            <i class="fa-solid fa-spinner fa-spin text-2xl mb-2"></i>
            <div class="text-xs font-medium">লোড হচ্ছে...</div>
          </td>
        </tr>
      </tbody>

      <!-- Footer Total -->
      <tfoot>
        <tr class="border-t border-slate-200 font-bold text-slate-800 text-xs bg-slate-50">
          <td class="p-3 border-r border-slate-200 bg-slate-50/80 font-siliguri font-bold text-slate-500">
            সর্বমোট (Subtotal):
          </td>
          <td class="p-3 text-right border-r border-slate-200 font-mono font-black text-slate-950 text-[13px]" id="grandTotalCell">
            ৳ 0.00
          </td>
          <td class="p-3 bg-slate-50/80"></td>
        </tr>
      </tfoot>
    </table>
  </div>
  </div> <!-- Close #viewSrOrders -->

  <!-- ═════════════════════════════════════════════════════════════
       TAB 2: DSR DELIVERY VIEW
  ══════════════════════════════════════════════════════════════ -->
  <div id="viewDsrDelivery" class="hidden space-y-4">
    <!-- Header Filter Card -->
    <div class="bg-white/95 backdrop-blur-md px-4 py-3 sm:px-6 sm:py-4 rounded-2xl border border-slate-200/60 shadow-2xs flex flex-wrap items-center justify-between gap-3">
      <div class="flex items-center gap-3">
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 leading-tight tracking-tight">
          DSR Delivery Map & List
        </h1>
      </div>

      <!-- Delivery Filters -->
      <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto mt-3 sm:mt-0">
        <!-- Date Picker -->
        <div class="relative w-full sm:w-auto">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="fa-regular fa-calendar text-slate-400 text-sm"></i>
          </div>
          <input type="date" id="dsrFilterDate" value="<?= date('Y-m-d') ?>" 
                 onchange="loadDsrDeliveries()" 
                 class="bg-slate-50 border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 pr-3 py-2 cursor-pointer shadow-sm hover:border-slate-300 transition outline-none">
        </div>

        <!-- DSR Select Dropdown -->
        <div class="relative w-full sm:w-auto">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="fa-solid fa-truck text-slate-400 text-sm"></i>
          </div>
          <select id="dsrFilterDsr" onchange="loadDsrDeliveries()" class="bg-slate-50 border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 pr-8 py-2 cursor-pointer shadow-sm hover:border-slate-300 transition outline-none appearance-none min-w-[140px]">
            <option value="">সকল DSR</option>
          </select>
          <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
            <i class="fa-solid fa-chevron-down text-slate-400 text-xs"></i>
          </div>
        </div>

        <!-- Retailer Search -->
        <div class="relative w-full sm:w-64">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <i class="fa-solid fa-search text-slate-400 text-sm"></i>
          </div>
          <input type="text" id="dsrFilterSearch" onkeyup="filterDsrDeliveriesClientSide()" placeholder="দোকান খুঁজুন..." 
                 class="bg-slate-50 border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-9 pr-3 py-2 shadow-sm hover:border-slate-300 transition outline-none">
        </div>
      </div>
    </div>

    <!-- Map & Summary Container -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <!-- Leaflet Map Canvas -->
      <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-3xs overflow-hidden relative h-[520px]" style="height: 520px;">
        <div id="dsrLeafletMap" class="w-full h-full z-0" style="height: 100%;"></div>
        <!-- Status Color Legend Overlay -->
        <div class="absolute bottom-3 left-3 z-[400] bg-white/90 backdrop-blur-md px-3 py-2 rounded-xl shadow-md border border-slate-200 flex flex-wrap items-center gap-3 text-[11px] font-bold">
          <div class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span> Pending</div>
          <div class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span> Delivered</div>
          <div class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-amber-600"></span> Partial/Due</div>
          <div class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-rose-600"></span> Canceled</div>
        </div>
      </div>

      <!-- Retailers List Card Sidebar -->
      <div class="bg-white rounded-2xl border border-slate-200/80 shadow-3xs p-4 flex flex-col h-[520px]">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-3 shrink-0">
          <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
            <i class="fa-solid fa-store text-blue-600"></i>
            <span>খুচরা বিক্রেতার তালিকা (<span id="dsrRetailerCount">0</span>)</span>
          </h3>
        </div>
        <div id="dsrRetailerList" class="flex-1 overflow-y-auto space-y-2 pr-1 custom-scrollbar">
          <!-- Populated via JS -->
        </div>
      </div>
    </div>
  </div>

<!-- ========================================================================= -->
<!-- ULTRA-PREMIUM SLIDE-UP EDIT DRAWER FOR MOBILE (SAME LINE DOR & QTY)        -->
<!-- ========================================================================= -->
<div id="editOrderModal" class="fixed inset-0 hidden opacity-0 transition-opacity duration-300 pointer-events-none flex items-end sm:items-center justify-center" style="z-index: 99999 !important;">
  
  <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity duration-300 pointer-events-auto" onclick="closeEditOrderModal()"></div>

  <div id="editOrderSheetContent" class="relative w-full max-w-lg sm:max-w-4xl bg-slate-50 rounded-t-3xl sm:rounded-2xl shadow-2xl transform translate-y-full sm:translate-y-8 sm:opacity-0 transition-all duration-300 ease-out border border-slate-200/90 max-h-[90vh] sm:max-h-[85vh] flex flex-col font-siliguri overflow-hidden text-slate-800 pointer-events-auto" style="padding-bottom: max(10px, env(safe-area-inset-bottom));">
    
    <div class="w-10 h-1 bg-slate-300 rounded-full mx-auto my-2 shrink-0 cursor-pointer sm:hidden" onclick="closeEditOrderModal()"></div>

    <div class="px-4 py-3 bg-slate-900 text-white flex items-center justify-between shrink-0 shadow-md">
      <div class="flex items-center gap-3 min-w-0">
        <div class="w-8 h-8 rounded-xl bg-blue-500/20 border border-blue-400/30 text-blue-400 flex items-center justify-center shrink-0">
          <i class="fa-solid fa-cart-shopping text-sm"></i>
        </div>
        <div class="min-w-0">
          <h3 class="font-extrabold text-white text-sm sm:text-base leading-snug truncate" id="editModalRetailerName">
            মিষ্টি ভ্যারাইটিজ স্টোর
          </h3>
          <span class="inline-block text-[10px] font-mono font-bold text-slate-300 bg-white/10 px-2 py-0.5 rounded-full mt-0.5" id="editModalOrderBadge">
            #ORD-0000
          </span>
        </div>
      </div>

      <button type="button" onclick="closeEditOrderModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white transition flex items-center justify-center active:scale-95 shrink-0">
        <i class="fa-solid fa-xmark text-sm"></i>
      </button>
    </div>

    <div class="flex-1 overflow-y-auto p-3 sm:p-5 space-y-4">
      
      <div id="editOrderItemsContainer" class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <!-- Populated dynamically via JS -->
      </div>

      <div class="pt-1">
        <div id="addProductSelectorArea" class="hidden space-y-2.5 bg-white p-3 rounded-2xl border border-slate-200 shadow-2xs">
          <div class="flex items-center justify-between text-xs font-bold text-slate-800">
            <span>নতুন পণ্য নির্বাচন করুন:</span>
            <button type="button" onclick="toggleAddProductDropdown()" class="text-slate-400 hover:text-slate-600 text-xs">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
          <div class="flex items-center gap-2">
            <select id="editAddProductSelect" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:outline-none focus:border-blue-500">
              <option value="">-- পণ্য সিলেক্ট করুন --</option>
            </select>
            <button type="button" onclick="confirmAddProduct()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shrink-0 transition active:scale-95 shadow-2xs">
              যোগ করুন
            </button>
          </div>
        </div>

        <button type="button" id="btnAddProductToggle" onclick="toggleAddProductDropdown()" class="w-full py-2.5 px-3 bg-white border border-dashed border-slate-300 hover:border-blue-500 rounded-2xl text-xs font-bold text-slate-700 hover:text-blue-600 hover:bg-blue-50/50 transition flex items-center justify-center gap-2 shadow-2xs active:scale-98">
          <i class="fa-solid fa-circle-plus text-sm text-blue-500"></i>
          <span>নতুন পণ্য যোগ করুন</span>
        </button>
      </div>

    </div>

    <!-- Bottom Red Summary Action Box -->
    <div class="p-3 sm:p-4 bg-white border-t border-slate-200 shrink-0 space-y-2">
      <!-- Edit Reason Input -->
      <div class="px-1">
        <input type="text" id="editReason" placeholder="পরিবর্তনের কারণ লিখুন (বাধ্যতামূলক)..." class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 focus:outline-none focus:border-rose-500 transition-colors">
      </div>

      <div class="rounded-2xl border-2 border-rose-500 bg-gradient-to-r from-rose-50/80 to-rose-100/40 p-3 sm:p-3.5 flex items-center justify-between gap-3 shadow-md">
        
        <div class="space-y-1 min-w-0">
          <div class="flex items-center gap-1.5">
            <span id="editModalOCBadge" class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-black bg-emerald-100 text-emerald-800 shadow-2xs">
              O/C +0
            </span>
          </div>
          <div class="text-sm sm:text-base font-black text-slate-950 font-sans tracking-tight leading-tight">
            Subtotal: <span class="font-mono text-slate-900" id="editModalSubtotal">Tk 0.00</span>
          </div>
        </div>

        <button type="button" 
                id="btnConfirmOrderEdit" 
                onclick="submitOrderEdit()" 
                class="px-5 py-3 rounded-full shadow-lg shadow-rose-500/30 transition-all duration-200 flex items-center gap-2 shrink-0 font-extrabold text-xs sm:text-sm active:scale-95"
                style="background: linear-gradient(135deg, #f43f5e 0%, #be123c 100%) !important; color: #ffffff !important;">
          <span id="btnConfirmText" style="color: #ffffff !important;">সেভ করুন</span>
          <i id="btnConfirmIcon" class="fa-solid fa-arrow-right text-xs text-white"></i>
          <svg id="btnConfirmSpinner" class="hidden animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
          </svg>
        </button>

      </div>
    </div>

  </div>
</div>

<!-- ========================================================================= -->
<!-- DSR RETAILER DELIVERY DETAIL MODAL                                        -->
<!-- ========================================================================= -->
<div id="dsrDeliveryModal" class="fixed inset-0 hidden opacity-0 transition-opacity duration-300 pointer-events-none flex items-end sm:items-center justify-center" style="z-index: 99999 !important;">
  <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity duration-300 pointer-events-auto" onclick="closeDsrDeliveryModal()"></div>

  <div class="relative w-full max-w-2xl bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl overflow-hidden pointer-events-auto transform transition-all duration-300 flex flex-col max-h-[90vh] font-siliguri">
    
    <!-- Modal Header Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white p-4 sm:p-5 flex items-center justify-between shrink-0">
      <div class="min-w-0 flex-1 pr-3">
        <div class="flex items-center gap-2 flex-wrap mb-1">
          <span id="dsrModalSaleType" class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">Ordered</span>
          <span id="dsrModalStatus" class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30">Pending</span>
        </div>
        <h3 id="dsrModalRetailerName" class="text-base sm:text-lg font-black truncate leading-tight">খুচরা বিক্রেতা</h3>
        <p id="dsrModalRetailerPhone" class="text-xs text-slate-300 mt-0.5"><i class="fa-solid fa-phone text-[10px] mr-1"></i> -</p>
      </div>

      <button type="button" onclick="closeDsrDeliveryModal()" class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition active:scale-95">
        <i class="fa-solid fa-xmark text-sm"></i>
      </button>
    </div>

    <!-- Modal Body: Company Grouped Products -->
    <div id="dsrModalProductBody" class="p-4 sm:p-5 overflow-y-auto flex-1 space-y-5 bg-slate-50/50 custom-scrollbar">
      <!-- Populated via JS -->
    </div>

    <!-- Modal Footer / Action Buttons -->
    <div id="dsrModalFooter" class="p-4 sm:p-5 bg-white border-t border-slate-100 flex items-center justify-between gap-3 shrink-0">
      <!-- Action buttons populated dynamically via JS -->
    </div>

  </div>
</div>

<!-- ========================================================================= -->
<!-- DSR PARTIAL PAYMENT POPUP MODAL                                           -->
<!-- ========================================================================= -->
<div id="dsrPaymentModal" class="fixed inset-0 hidden opacity-0 transition-opacity duration-300 pointer-events-none flex items-center justify-center" style="z-index: 100000 !important;">
  <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity duration-300 pointer-events-auto" onclick="closeDsrPaymentModal()"></div>

  <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden pointer-events-auto p-5 sm:p-6 font-siliguri space-y-4">
    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
      <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
        <i class="fa-solid fa-money-bill-wave text-emerald-600"></i>
        <span>টাকা সংগ্রহের পরিমাণ (Collect Amount)</span>
      </h3>
      <button type="button" onclick="closeDsrPaymentModal()" class="text-slate-400 hover:text-slate-600">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-3">
      <div class="flex justify-between items-center text-xs font-semibold text-slate-600">
        <span>মোট চালান মূল্য (Total Amount):</span>
        <span id="dsrPayModalTotal" class="font-mono font-black text-slate-900 text-sm">৳0.00</span>
      </div>
      <div class="flex justify-between items-center text-xs font-semibold text-slate-600">
        <span>সংগৃহীত টাকা (Paid):</span>
        <div class="relative">
          <span class="absolute left-2.5 top-1/2 -translate-y-1/2 font-bold text-slate-400">৳</span>
          <input type="number" id="dsrPayInput" step="0.01" min="0" value="0" oninput="recalcDsrPaymentDue()"
                 class="bg-white border border-slate-300 rounded-xl pl-6 pr-3 py-1.5 text-right font-mono font-bold text-slate-900 w-36 focus:border-blue-500 focus:outline-none">
        </div>
      </div>
      <div class="flex justify-between items-center text-xs font-bold border-t border-slate-200 pt-2 text-rose-600">
        <span>অবশিষ্ট বকেয়া (Due Amount):</span>
        <span id="dsrPayModalDue" class="font-mono font-black text-sm">৳0.00</span>
      </div>
    </div>

    <div class="flex items-center justify-end gap-2 pt-2">
      <button type="button" onclick="closeDsrPaymentModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
        বাতিল
      </button>
      <button type="button" onclick="submitDsrPaymentAction()" class="px-5 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition active:scale-95 flex items-center gap-2">
        <i class="fa-solid fa-check"></i>
        <span>সংরক্ষণ ও নিশ্চিত করুন</span>
      </button>
    </div>
  </div>
</div>


<!-- ========================================================================= -->
<!-- BEAUTIFUL RETAILER INVOICE MODAL                                         -->
<!-- ========================================================================= -->
<div id="invoiceModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden opacity-0 transition-opacity duration-200 flex items-center justify-center p-3 sm:p-4 overflow-y-auto pointer-events-none" style="z-index: 99990 !important;">
  
  <div id="invoiceModalContent" class="bg-white w-full max-w-lg rounded-2xl p-5 sm:p-6 shadow-xl space-y-4 transform scale-95 transition-transform duration-200 border border-slate-100 my-auto text-slate-800 font-siliguri pointer-events-auto">
    
    <div id="printableInvoiceArea" class="space-y-4 bg-white">
      
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

      <div class="grid grid-cols-2 gap-3 text-xs">
        <div>
          <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">কাস্টমার বিবরণ</div>
          <div class="font-bold text-slate-900 text-xs leading-tight" id="invRetailerName">Store</div>
          <div class="text-slate-500 mt-0.5 font-mono text-[11px]" id="invRetailerPhone">01700000000</div>
          <div class="text-slate-400 text-[10px] mt-0.5 leading-snug" id="invRetailerAddress">ঠিকানা দেওয়া নেই</div>
        </div>

        <div class="border-l border-slate-100 pl-3">
          <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">অর্ডার বিবরণ</div>
          <div class="flex items-center gap-1.5 mb-1 text-[11px]">
            <span class="text-slate-400">স্ট্যাটাস:</span>
            <span id="invStatusBadge" class="font-bold px-1.5 py-0.5 rounded text-[9px] bg-emerald-50 text-emerald-700">
              ডেলিভার্ড
            </span>
          </div>
          <div class="text-[10px] text-slate-600" id="invDealerName">ডিলার: General Dealer</div>
          <div class="text-[10px] text-slate-600" id="invSRName">SR: Name</div>
        </div>
      </div>

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
          </tbody>
        </table>
      </div>

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

<style>
@media print {
  body * { visibility: hidden; }
  #invoiceModal, #invoiceModal * { visibility: visible; }
  #invoiceModal { position: absolute; left: 0; top: 0; width: 100%; height: auto; background: white !important; padding: 0 !important; }
  #invoiceModalContent { box-shadow: none !important; border: none !important; max-width: 100% !important; width: 100% !important; margin: 0 !important; padding: 20px !important; }
  .print\:hidden { display: none !important; }
}
</style>

<script>
const ALL_SR_PRODUCTS = <?= json_encode($allProducts ?? []) ?>;
const ORDERS_MAP = {};
let editingOrder = null;

// ── DSR Delivery State ───────────────────────────────────────────────────
let currentTab = 'sr_orders';
let dsrDeliveriesMap = {};
let dsrMapInstance = null;
let dsrMarkersGroup = null;
let currentDsrDelivery = null;

document.addEventListener('DOMContentLoaded', () => {
    loadOrders();
});

function switchOperationsTab(tabName) {
    currentTab = tabName;
    const btnSr = document.getElementById('tabBtnSrOrders');
    const btnDsr = document.getElementById('tabBtnDsrDelivery');
    const viewSr = document.getElementById('viewSrOrders');
    const viewDsr = document.getElementById('viewDsrDelivery');

    if (tabName === 'sr_orders') {
        btnSr.className = "flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition-all shadow-2xs bg-blue-600 text-white shadow-blue-500/20 active:scale-95";
        btnDsr.className = "flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition-all text-slate-600 hover:text-slate-900 bg-white border border-slate-200 active:scale-95";
        viewSr.classList.remove('hidden');
        viewDsr.classList.add('hidden');
    } else {
        btnDsr.className = "flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition-all shadow-2xs bg-blue-600 text-white shadow-blue-500/20 active:scale-95";
        btnSr.className = "flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm transition-all text-slate-600 hover:text-slate-900 bg-white border border-slate-200 active:scale-95";
        viewSr.classList.add('hidden');
        viewDsr.classList.remove('hidden');

        initDsrMapIfNeeded();
        loadDsrDeliveries();
    }
}

function initDsrMapIfNeeded() {
    if (!dsrMapInstance && typeof L !== 'undefined') {
        dsrMapInstance = L.map('dsrLeafletMap').setView([23.8103, 90.4125], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(dsrMapInstance);
        dsrMarkersGroup = L.layerGroup().addTo(dsrMapInstance);
    }
    if (dsrMapInstance) {
        setTimeout(() => dsrMapInstance.invalidateSize(), 200);
    }
}

async function loadDsrDeliveries() {
    const date = document.getElementById('dsrFilterDate').value;
    const dsrId = document.getElementById('dsrFilterDsr').value;
    const listContainer = document.getElementById('dsrRetailerList');

    listContainer.innerHTML = `
        <div class="p-8 text-center text-slate-400 font-siliguri">
          <i class="fa-solid fa-spinner fa-spin text-2xl mb-2 text-blue-600"></i>
          <div class="text-xs font-medium">ডেলিভারি ডাটা লোড হচ্ছে...</div>
        </div>`;

    try {
        const res = await fetch(`<?= url('manager/api/operations/dsr-deliveries') ?>?date=${date}&dsr_id=${dsrId}&_t=${Date.now()}`, { cache: 'no-cache' });
        const data = await res.json();

        if (data.success) {
            // Populate DSR select dropdown if empty
            const dsrSelect = document.getElementById('dsrFilterDsr');
            if (dsrSelect.options.length <= 1) {
                let optionsHtml = '<option value="">সকল DSR</option>';
                (data.dsrs || []).forEach(dsr => {
                    optionsHtml += `<option value="${dsr.id}" ${dsr.id == dsrId ? 'selected' : ''}>${escapeHtml(dsr.name)}</option>`;
                });
                dsrSelect.innerHTML = optionsHtml;
            }

            // Map deliveries to map object
            dsrDeliveriesMap = {};
            (data.deliveries || []).forEach(d => {
                dsrDeliveriesMap[d.dispatch_id] = d;
            });

            renderDsrMapAndList();
        } else {
            showToast(data.message || 'Error loading DSR deliveries.', 'error');
        }
    } catch (e) {
        console.error(e);
        showToast('Network error loading DSR deliveries.', 'error');
    }
}

function renderDsrMapAndList() {
    const search = (document.getElementById('dsrFilterSearch').value || '').toLowerCase().trim();
    const deliveries = Object.values(dsrDeliveriesMap);
    const listContainer = document.getElementById('dsrRetailerList');
    const countEl = document.getElementById('dsrRetailerCount');

    if (dsrMarkersGroup) {
        dsrMarkersGroup.clearLayers();
    }

    const filtered = deliveries.filter(del => {
        const rName = (del.retailer_name || '').toLowerCase();
        const rPhone = (del.retailer_phone || '').toLowerCase();
        const dsrName = (del.dsr_name || '').toLowerCase();
        return !search || rName.includes(search) || rPhone.includes(search) || dsrName.includes(search);
    });

    countEl.innerText = filtered.length;

    if (filtered.length === 0) {
        listContainer.innerHTML = `
            <div class="p-8 text-center text-slate-400 font-siliguri">
              <i class="fa-solid fa-box-open text-2xl mb-2 text-slate-300"></i>
              <div class="text-xs font-medium">কোনো ডেলিভারি রেকর্ড পাওয়া যায়নি</div>
            </div>`;
        return;
    }

    listContainer.innerHTML = '';
    const bounds = [];

    filtered.forEach((del, idx) => {
        // Status Badge Style
        let statusBadge = '';
        if (del.status_code === 'canceled') {
            statusBadge = `<span class="px-2 py-0.5 rounded text-[9px] font-extrabold bg-rose-50 text-rose-600 border border-rose-200">বাতিল</span>`;
        } else if (del.status_code === 'delivered') {
            statusBadge = `<span class="px-2 py-0.5 rounded text-[9px] font-extrabold bg-emerald-50 text-emerald-600 border border-emerald-200">সম্পন্ন</span>`;
        } else if (del.status_code === 'partial') {
            statusBadge = `<span class="px-2 py-0.5 rounded text-[9px] font-extrabold bg-amber-50 text-amber-600 border border-amber-200">পার্শিয়াল / বকেয়া</span>`;
        } else {
            statusBadge = `<span class="px-2 py-0.5 rounded text-[9px] font-extrabold bg-blue-50 text-blue-600 border border-blue-200">অপেক্ষমান</span>`;
        }

        // Retailer List Card
        const card = document.createElement('div');
        card.className = "p-3 rounded-xl border border-slate-200 hover:border-blue-400 bg-white hover:bg-blue-50/20 transition cursor-pointer shadow-2xs space-y-1.5 font-siliguri";
        card.onclick = () => openDsrDeliveryModal(del.dispatch_id);
        card.innerHTML = `
            <div class="flex items-center justify-between min-w-0">
              <h4 class="font-bold text-slate-900 text-xs sm:text-sm truncate">${escapeHtml(del.retailer_name)}</h4>
              ${statusBadge}
            </div>
            <div class="text-[11px] text-slate-500 flex items-center justify-between">
              <span><i class="fa-solid fa-phone text-slate-400 text-[9px] mr-1"></i>${escapeHtml(del.retailer_phone || '-')}</span>
              <span class="font-mono font-black text-slate-900">৳${parseFloat(del.order_total || 0).toFixed(2)}</span>
            </div>
            <div class="text-[10px] text-blue-600 font-bold truncate">
              <i class="fa-solid fa-truck text-[9px] mr-1"></i> DSR: ${escapeHtml(del.dsr_name || 'Assigned')}
            </div>
        `;
        listContainer.appendChild(card);

        // Add Marker to Leaflet Map
        if (dsrMapInstance && dsrMarkersGroup) {
            let lat = parseFloat(del.latitude);
            let lng = parseFloat(del.longitude);

            if (isNaN(lat) || isNaN(lng) || (lat === 0 && lng === 0)) {
                // Fallback offset around center for demo coordinates
                lat = 23.8103 + (Math.sin(idx + 1) * 0.03);
                lng = 90.4125 + (Math.cos(idx + 1) * 0.03);
            }

            bounds.push([lat, lng]);

            const colorHex = del.color === 'green' ? '#16a34a' : (del.color === 'orange' ? '#ea580c' : (del.color === 'red' ? '#dc2626' : '#2563eb'));

            const customIcon = L.divIcon({
                className: 'custom-map-pin',
                html: `<div style="background-color: ${colorHex}; width: 24px; height: 24px; border-radius: 50%; border: 3px solid #ffffff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3); display: flex; items-center; justify-center; color: #fff; font-size: 10px; font-weight: bold;">
                        ${idx + 1}
                       </div>`,
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            });

            const marker = L.marker([lat, lng], { icon: customIcon }).addTo(dsrMarkersGroup);
            marker.bindPopup(`
                <div class="font-siliguri text-xs space-y-1">
                  <div class="font-bold text-slate-900">${escapeHtml(del.retailer_name)}</div>
                  <div class="text-slate-500">${escapeHtml(del.retailer_phone || '')}</div>
                  <div class="font-mono font-black text-blue-600">৳${parseFloat(del.order_total || 0).toFixed(2)}</div>
                  <button onclick="openDsrDeliveryModal(${del.dispatch_id})" class="mt-1 px-3 py-1 bg-blue-600 text-white rounded-lg text-[10px] font-bold w-full">অর্ডার বিবরণী</button>
                </div>
            `);
        }
    });

    if (dsrMapInstance && bounds.length > 0) {
        dsrMapInstance.fitBounds(bounds, { padding: [30, 30] });
    }
}

function filterDsrDeliveriesClientSide() {
    renderDsrMapAndList();
}

// ── DSR Retailer Delivery Modal Logic ────────────────────────────────────
function openDsrDeliveryModal(dispatchId) {
    const del = dsrDeliveriesMap[dispatchId];
    if (!del) return;

    currentDsrDelivery = JSON.parse(JSON.stringify(del)); // Deep clone

    // Set Header Info
    document.getElementById('dsrModalRetailerName').innerText = del.retailer_name || 'খুচরা বিক্রেতা';
    document.getElementById('dsrModalRetailerPhone').innerHTML = `<i class="fa-solid fa-phone text-[10px] mr-1"></i>${escapeHtml(del.retailer_phone || '-')}`;

    // Sale Type Badge
    const saleTypeEl = document.getElementById('dsrModalSaleType');
    if (parseInt(del.is_ready_sale || del.order_ready_sale || 0) === 1) {
        saleTypeEl.innerText = 'Ready Sale';
        saleTypeEl.className = "px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30";
    } else {
        saleTypeEl.innerText = 'Ordered';
        saleTypeEl.className = "px-2 py-0.5 rounded text-[10px] font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30";
    }

    // Status Badge
    const statusEl = document.getElementById('dsrModalStatus');
    statusEl.innerText = del.status_label || 'Pending';
    if (del.status_code === 'delivered') {
        statusEl.className = "px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30";
    } else if (del.status_code === 'partial') {
        statusEl.className = "px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30";
    } else if (del.status_code === 'canceled') {
        statusEl.className = "px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30";
    } else {
        statusEl.className = "px-2 py-0.5 rounded text-[10px] font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30";
    }

    renderDsrModalProducts();
    renderDsrModalFooter();

    const modal = document.getElementById('dsrDeliveryModal');
    modal.classList.remove('hidden');
    setTimeout(() => modal.classList.remove('opacity-0'), 10);
}

function closeDsrDeliveryModal() {
    const modal = document.getElementById('dsrDeliveryModal');
    modal.classList.add('opacity-0');
    setTimeout(() => modal.classList.add('hidden'), 300);
}

function renderDsrModalProducts() {
    const body = document.getElementById('dsrModalProductBody');
    if (!currentDsrDelivery || !currentDsrDelivery.items) {
        body.innerHTML = '<div class="text-center py-6 text-slate-400">No products found.</div>';
        return;
    }

    // Group items by company_name
    const groups = {};
    currentDsrDelivery.items.forEach(item => {
        const cName = item.company_name || 'সাধারণ কোম্পানি (Company)';
        if (!groups[cName]) groups[cName] = [];
        groups[cName].push(item);
    });

    const isEditable = (currentDsrDelivery.status_code !== 'canceled');

    let html = '';
    Object.keys(groups).forEach(cName => {
        html += `
            <div class="space-y-3">
              <div class="flex items-center gap-2 font-bold text-slate-700 text-xs sm:text-sm border-b border-slate-200 pb-1.5">
                <i class="fa-solid fa-building text-blue-600 text-xs"></i>
                <span class="tracking-wide uppercase">${escapeHtml(cName)}</span>
              </div>
              <div class="space-y-3">
        `;

        groups[cName].forEach(p => {
            const currentQty = (p.delivered_qty !== undefined ? p.delivered_qty : p.order_qty);
            const totalAmt = currentQty * parseFloat(p.unit_price || 0);

            html += `
                <div class="bg-white border border-slate-200 rounded-2xl p-3 sm:p-4 shadow-2xs space-y-2.5 font-siliguri">
                  <div class="font-bold text-slate-900 text-xs sm:text-sm leading-tight flex items-center justify-between">
                    <span>${escapeHtml(p.product_name)}</span>
                  </div>

                  <!-- Product Rates & Stock Grid -->
                  <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-[11px] bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                    <div>
                      <span class="text-slate-400 block text-[9px] font-semibold">মূল কেনা দর (Base)</span>
                      <span class="font-bold text-slate-700">৳${parseFloat(p.base_price || 0).toFixed(2)}</span>
                    </div>
                    <div>
                      <span class="text-slate-400 block text-[9px] font-semibold">O/C (পিস প্রতি)</span>
                      <span class="font-bold ${p.oc_per_unit >= 0 ? 'text-emerald-600' : 'text-rose-600'}">
                        ${p.oc_per_unit >= 0 ? '+' : ''}${parseFloat(p.oc_per_unit || 0).toFixed(2)}
                      </span>
                    </div>
                    <div>
                      <span class="text-slate-400 block text-[9px] font-semibold">অর্ডার পরিমাণ</span>
                      <span class="font-bold text-slate-800">${p.order_qty} পিস</span>
                    </div>
                    <div>
                      <span class="text-slate-400 block text-[9px] font-semibold">ভ্যান স্টক (Van Stock)</span>
                      <span class="font-bold text-blue-600">${p.van_stock} পিস</span>
                    </div>
                  </div>

                  <!-- Editable Quantity & Line Total -->
                  <div class="flex items-center justify-between gap-3 pt-1">
                    <div>
                      <span class="text-[9px] text-slate-400 font-semibold block">বিক্রয় মূল্য ও মোট</span>
                      <span class="text-xs font-black text-slate-900">
                        ৳${parseFloat(p.unit_price || 0).toFixed(2)} × 
                        <span id="dsrItemLineTotal_${p.id}" class="text-blue-600 font-mono">৳${totalAmt.toFixed(2)}</span>
                      </span>
                    </div>

                    <div class="flex items-center gap-1.5">
                      <button type="button" onclick="adjustDsrItemQty(${p.id}, -1)" ${isEditable ? '' : 'disabled'}
                              class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-black text-base flex items-center justify-center transition active:scale-95 disabled:opacity-40">
                        -
                      </button>
                      <input type="number" id="dsrItemQtyInput_${p.id}" value="${currentQty}" min="0" oninput="updateDsrItemQtyInput(${p.id}, this.value)" ${isEditable ? '' : 'disabled'}
                             class="w-16 text-center font-mono font-bold text-sm bg-slate-50 border border-slate-200 rounded-xl py-1 focus:border-blue-500 focus:outline-none disabled:bg-slate-100">
                      <button type="button" onclick="adjustDsrItemQty(${p.id}, 1)" ${isEditable ? '' : 'disabled'}
                              class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-black text-base flex items-center justify-center transition active:scale-95 disabled:opacity-40">
                        +
                      </button>
                    </div>
                  </div>
                </div>
            `;
        });

        html += `
              </div>
            </div>
        `;
    });

    body.innerHTML = html;
}

function adjustDsrItemQty(itemId, delta) {
    if (!currentDsrDelivery) return;
    const item = currentDsrDelivery.items.find(i => i.id == itemId);
    if (!item) return;

    let currentQty = (item.delivered_qty !== undefined ? item.delivered_qty : item.order_qty);
    currentQty = Math.max(0, currentQty + delta);
    item.delivered_qty = currentQty;

    const input = document.getElementById(`dsrItemQtyInput_${itemId}`);
    if (input) input.value = currentQty;

    const lineTotal = currentQty * parseFloat(item.unit_price || 0);
    const lineTotalEl = document.getElementById(`dsrItemLineTotal_${itemId}`);
    if (lineTotalEl) lineTotalEl.innerText = `৳${lineTotal.toFixed(2)}`;

    recalcDsrModalGrandTotal();
}

function updateDsrItemQtyInput(itemId, val) {
    if (!currentDsrDelivery) return;
    const item = currentDsrDelivery.items.find(i => i.id == itemId);
    if (!item) return;

    let currentQty = parseInt(val) || 0;
    if (currentQty < 0) currentQty = 0;
    item.delivered_qty = currentQty;

    const lineTotal = currentQty * parseFloat(item.unit_price || 0);
    const lineTotalEl = document.getElementById(`dsrItemLineTotal_${itemId}`);
    if (lineTotalEl) lineTotalEl.innerText = `৳${lineTotal.toFixed(2)}`;

    recalcDsrModalGrandTotal();
}

function recalcDsrModalGrandTotal() {
    if (!currentDsrDelivery || !currentDsrDelivery.items) return 0;
    let grandTotal = 0;
    currentDsrDelivery.items.forEach(p => {
        const qty = p.delivered_qty !== undefined ? p.delivered_qty : p.order_qty;
        grandTotal += (qty * parseFloat(p.unit_price || 0));
    });
    return grandTotal;
}

function renderDsrModalFooter() {
    const footer = document.getElementById('dsrModalFooter');
    if (!currentDsrDelivery) return;

    const statusCode = currentDsrDelivery.status_code;
    const grandTotal = recalcDsrModalGrandTotal();

    let buttonsHtml = '';

    if (statusCode === 'pending') {
        // Untouched / Pending: Cancel | Complete
        buttonsHtml = `
            <div class="font-mono font-bold text-xs text-slate-700">
              মোট: <span class="text-sm font-black text-slate-900">৳${grandTotal.toFixed(2)}</span>
            </div>
            <div class="flex items-center gap-2">
              <button type="button" onclick="submitDsrActionDirect('cancel')" class="px-4 py-2 rounded-xl text-xs font-extrabold bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 transition active:scale-95">
                <i class="fa-solid fa-ban mr-1"></i> Cancel (বাতিল)
              </button>
              <button type="button" onclick="submitDsrActionDirect('complete')" class="px-5 py-2 rounded-xl text-xs font-extrabold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition active:scale-95">
                <i class="fa-solid fa-check mr-1"></i> Complete (সম্পন্ন)
              </button>
            </div>
        `;
    } else if (statusCode === 'canceled') {
        // Canceled: Undo
        buttonsHtml = `
            <div class="font-mono font-bold text-xs text-slate-400 line-through">
              মোট: ৳${grandTotal.toFixed(2)}
            </div>
            <div class="flex items-center gap-2">
              <button type="button" onclick="submitDsrActionDirect('undo')" class="px-5 py-2 rounded-xl text-xs font-extrabold bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition active:scale-95">
                <i class="fa-solid fa-rotate-left mr-1"></i> Undo (পুনরায় চালু করুন)
              </button>
            </div>
        `;
    } else if (statusCode === 'partial') {
        // Partial / Due: Cancel | Continue
        buttonsHtml = `
            <div class="font-mono font-bold text-xs text-slate-700">
              মোট: <span class="text-sm font-black text-amber-600">৳${grandTotal.toFixed(2)}</span>
            </div>
            <div class="flex items-center gap-2">
              <button type="button" onclick="submitDsrActionDirect('cancel')" class="px-4 py-2 rounded-xl text-xs font-extrabold bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 transition active:scale-95">
                <i class="fa-solid fa-ban mr-1"></i> Cancel (বাতিল)
              </button>
              <button type="button" onclick="openDsrPaymentModal()" class="px-5 py-2 rounded-xl text-xs font-extrabold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition active:scale-95">
                Continue (চালিয়ে যান) <i class="fa-solid fa-arrow-right ml-1"></i>
              </button>
            </div>
        `;
    } else {
        // Delivered (Full): Modify | Cancel
        buttonsHtml = `
            <div class="font-mono font-bold text-xs text-slate-700">
              মোট: <span class="text-sm font-black text-emerald-600">৳${grandTotal.toFixed(2)}</span>
            </div>
            <div class="flex items-center gap-2">
              <button type="button" onclick="submitDsrActionDirect('cancel')" class="px-4 py-2 rounded-xl text-xs font-extrabold bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 transition active:scale-95">
                <i class="fa-solid fa-ban mr-1"></i> Cancel (বাতিল)
              </button>
              <button type="button" onclick="submitDsrActionDirect('modify')" class="px-5 py-2 rounded-xl text-xs font-extrabold bg-amber-500 hover:bg-amber-600 text-white shadow-sm transition active:scale-95">
                <i class="fa-solid fa-pen-to-square mr-1"></i> Modify (পরিবর্তন করুন)
              </button>
            </div>
        `;
    }

    footer.innerHTML = buttonsHtml;
}

// ── Partial Payment Modal Handlers ───────────────────────────────────────
function openDsrPaymentModal() {
    if (!currentDsrDelivery) return;
    const total = recalcDsrModalGrandTotal();
    const paid = parseFloat(currentDsrDelivery.paid_amount || 0);

    document.getElementById('dsrPayModalTotal').innerText = `৳${total.toFixed(2)}`;
    document.getElementById('dsrPayInput').value = paid > 0 ? paid : total;
    recalcDsrPaymentDue();

    const modal = document.getElementById('dsrPaymentModal');
    modal.classList.remove('hidden');
    setTimeout(() => modal.classList.remove('opacity-0'), 10);
}

function closeDsrPaymentModal() {
    const modal = document.getElementById('dsrPaymentModal');
    modal.classList.add('opacity-0');
    setTimeout(() => modal.classList.add('hidden'), 300);
}

function recalcDsrPaymentDue() {
    const total = recalcDsrModalGrandTotal();
    const paidVal = parseFloat(document.getElementById('dsrPayInput').value) || 0;
    const due = Math.max(0, total - paidVal);
    document.getElementById('dsrPayModalDue').innerText = `৳${due.toFixed(2)}`;
}

function submitDsrPaymentAction() {
    const paidVal = parseFloat(document.getElementById('dsrPayInput').value) || 0;
    closeDsrPaymentModal();
    submitDsrActionDirect('continue_partial', paidVal);
}

// ── Execute Action to Backend ────────────────────────────────────────────
async function submitDsrActionDirect(actionType, paidAmount = null) {
    if (!currentDsrDelivery) return;

    const payloadItems = (currentDsrDelivery.items || []).map(p => ({
        id: p.id,
        product_id: p.product_id,
        qty: p.delivered_qty !== undefined ? p.delivered_qty : p.order_qty,
        unit_price: p.unit_price
    }));

    const formData = new FormData();
    formData.append('dispatch_id', currentDsrDelivery.dispatch_id);
    formData.append('action', actionType);
    formData.append('items', JSON.stringify(payloadItems));
    if (paidAmount !== null) {
        formData.append('paid_amount', paidAmount);
    }

    try {
        const res = await fetch(`<?= url("manager/api/operations/dsr-delivery-action") ?>`, {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (data.success) {
            showToast(data.message || 'Action executed successfully.', 'success');
            closeDsrDeliveryModal();
            await loadDsrDeliveries();
            if (typeof loadOrders === 'function') loadOrders();
        } else {
            showToast(data.message || 'Error processing action.', 'error');
        }
    } catch (e) {
        console.error(e);
        showToast('Network error executing delivery action.', 'error');
    }
}

function truncateName(name) {
    const words = (name || '').trim().split(/\s+/);
    if (words.length > 2) {
        return { is_truncated: true, short: words.slice(0, 2).join(' ') + '..', full: name };
    }
    return { is_truncated: false, short: name, full: name };
}

function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return (unsafe + '').replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

async function loadOrders() {
    const date = document.getElementById('filterDate').value;
    const srId = document.getElementById('filterSr').value;
    
    document.getElementById('tableBody').innerHTML = `
        <tr>
          <td colspan="3" class="p-12 text-center text-slate-400 bg-white font-siliguri">
            <i class="fa-solid fa-spinner fa-spin text-2xl mb-2"></i>
            <div class="text-xs font-medium">লোড হচ্ছে...</div>
          </td>
        </tr>`;
        
    try {
        const res = await fetch(`<?= url('manager/api/operations/orders') ?>?date=${date}&sr_id=${srId}`);
        const data = await res.json();
        
        if (data.success) {
            // Update SR Dropdown options
            const srSelect = document.getElementById('filterSr');
            let optionsHtml = '<option value="">সকল SR</option>';
            (data.srs || []).forEach(sr => {
                optionsHtml += `<option value="${sr.id}" ${sr.id == srId ? 'selected' : ''}>${escapeHtml(sr.name)}</option>`;
            });
            srSelect.innerHTML = optionsHtml;
            
            // Map orders to ORDERS_MAP
            Object.keys(ORDERS_MAP).forEach(k => delete ORDERS_MAP[k]);
            data.data.forEach(ord => ORDERS_MAP[ord.id] = ord);
            
            renderTable();
        } else {
            document.getElementById('tableBody').innerHTML = `<tr><td colspan="3" class="p-8 text-center text-rose-500 text-sm font-bold">Failed to load orders</td></tr>`;
        }
    } catch (err) {
        console.error(err);
        document.getElementById('tableBody').innerHTML = `<tr><td colspan="3" class="p-8 text-center text-rose-500 text-sm font-bold">Network Error</td></tr>`;
    }
}

function filterOrdersClientSide() {
    renderTable();
}

function renderTable() {
    const search = document.getElementById('filterSearch').value.toLowerCase();
    const tbody = document.getElementById('tableBody');
    tbody.innerHTML = '';
    
    let grandTotalAmount = 0;
    let grandTotalOC = 0;
    
    const orders = Object.values(ORDERS_MAP);
    
    if (orders.length === 0) {
        tbody.innerHTML = `
          <tr id="emptyRow">
            <td colspan="3" class="p-12 text-center text-slate-400 bg-white font-siliguri">
              <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-300 flex items-center justify-center text-xl mx-auto mb-2"><i class="fa-solid fa-box-open"></i></div>
              <span class="text-xs font-medium">কোনো অর্ডারের তথ্য পাওয়া যায়নি।</span>
            </td>
          </tr>`;
        recalculateTableFooterTotals();
        return;
    }
    
    let visibleCount = 0;

    orders.forEach(ord => {
        const rName = ord.retailer_name || ord.dealer_name || 'সাধারণ কাস্টমার';
        const rPhone = ord.retailer_phone || 'N/A';
        const srName = ord.sr_name || 'Unknown SR';
        
        if (search && !rName.toLowerCase().includes(search) && !rPhone.toLowerCase().includes(search) && !srName.toLowerCase().includes(search)) {
            return;
        }
        
        visibleCount++;
        
        let orderOC = 0;
        if (ord.products && ord.products.length > 0) {
            ord.products.forEach(p => {
                orderOC += (parseFloat(p.unit_price || 0) - parseFloat(p.base_price || 0)) * parseInt(p.quantity || 0);
            });
        }
        
        grandTotalAmount += parseFloat(ord.total_amount || 0);
        grandTotalOC += orderOC;
        
        const nameHtml = `<div class="font-bold text-slate-800 text-xs sm:text-sm leading-snug break-words font-siliguri">${escapeHtml(rName)}</div>`;

        const rAddress = ord.retailer_address || '';
        let addressHtml = '';
        if (rAddress) {
            addressHtml = `
                <a href="https://maps.google.com/?q=${encodeURIComponent(rAddress)}" target="_blank" onclick="event.stopPropagation()" class="flex items-center gap-1 text-[9px] text-blue-500 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 px-1.5 py-0.5 rounded-md border border-blue-100 transition shrink-0 max-w-[120px] sm:max-w-[150px]" title="${escapeHtml(rAddress)}">
                    <i class="fa-solid fa-location-dot shrink-0"></i>
                    <span class="truncate">${escapeHtml(rAddress)}</span>
                </a>
            `;
        }

        const ocSign = orderOC > 0 ? '+' : '';
        const ocClass = orderOC > 0 ? 'text-emerald-500' : 'text-rose-500';

        const rawDate = ord.created_at || '';
        let displayDateTime = '';
        if (rawDate) {
            const d = new Date(rawDate);
            if (!isNaN(d)) {
                displayDateTime = d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' }) + ', ' + d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
            } else {
                displayDateTime = rawDate.substring(0, 16);
            }
        }

        const tr = document.createElement('tr');
        tr.className = "retailer-order-row hover:bg-slate-50/40 transition-colors";
        tr.id = `order-row-${ord.id}`;
        tr.innerHTML = `
          <td class="p-3 border-r border-slate-100 align-middle bg-white overflow-hidden">
            <div class="min-w-0">
              <div class="flex items-center gap-1 text-blue-600 font-bold text-[10px] mb-1">
                <i class="fa-solid fa-user-tie text-[9px]"></i>
                <span>${escapeHtml(srName)}</span>
              </div>
              ${nameHtml}
              <div class="text-[10px] text-slate-400 font-medium mt-1 flex flex-wrap items-center gap-2">
                <div class="flex items-center gap-1 shrink-0">
                  <i class="fa-solid fa-phone text-slate-300 text-[9px]"></i>
                  <span>${escapeHtml(rPhone)}</span>
                </div>
                ${addressHtml}
                <span class="bg-slate-50 border border-slate-200 text-slate-500 px-1.5 py-0.5 rounded-md text-[9px] font-bold tracking-wider shrink-0">#ORD-${ord.id}</span>
                <span class="bg-slate-50 border border-slate-200 text-slate-500 px-1.5 py-0.5 rounded-md text-[9px] font-bold tracking-wider shrink-0"><i class="fa-regular fa-clock mr-1"></i>${displayDateTime}</span>
              </div>
            </div>
          </td>
          <td class="p-3 text-right border-r border-slate-100 align-middle bg-white font-mono font-bold text-emerald-700 text-xs sm:text-sm" id="order-total-cell-${ord.id}">
            ৳ ${parseFloat(ord.total_amount || 0).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}
            <div class="text-[10px] font-bold mt-0.5 ${ocClass}" id="order-oc-badge-${ord.id}" style="${orderOC === 0 ? 'display:none;' : ''}">
              (${ocSign}৳${Math.abs(orderOC).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})})
            </div>
          </td>
          <td class="p-3 text-center align-middle bg-white" id="order-actions-cell-${ord.id}">
            <div class="flex items-center justify-center gap-1.5">
              <button type="button" onclick="openInvoiceModal(${ord.id})" class="w-8 h-8 rounded-lg bg-blue-50 border border-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white transition duration-200 flex items-center justify-center shadow-3xs active:scale-95" title="ইনভয়েস দেখুন">
                <i class="fa-solid fa-file-invoice text-xs"></i>
              </button>
              ${parseInt(ord.is_assigned || 0) === 0 ? `
              <button type="button" onclick="openEditOrderModal(${ord.id})" class="w-8 h-8 rounded-lg bg-rose-50 border border-rose-100 text-rose-600 hover:bg-rose-600 hover:text-white transition duration-200 flex items-center justify-center shadow-3xs active:scale-95" title="এডিট করুন">
                <i class="fa-solid fa-pen text-xs"></i>
              </button>
              <button type="button" onclick="deleteOrderClientSide(${ord.id})" class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-200 text-slate-500 hover:bg-red-500 hover:text-white hover:border-red-500 transition duration-200 flex items-center justify-center shadow-3xs active:scale-95" title="ডিলিট করুন">
                <i class="fa-solid fa-trash-can text-xs"></i>
              </button>
              ` : `
              <button type="button" disabled class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200 text-slate-300 flex items-center justify-center shadow-3xs cursor-not-allowed" title="SR অ্যাসাইন করা হয়েছে, এডিট সম্ভব নয়">
                <i class="fa-solid fa-pen text-xs"></i>
              </button>
              <button type="button" disabled class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200 text-slate-300 flex items-center justify-center shadow-3xs cursor-not-allowed" title="SR অ্যাসাইন করা হয়েছে, ডিলিট সম্ভব নয়">
                <i class="fa-solid fa-trash-can text-xs"></i>
              </button>
              `}
            </div>
          </td>
        `;
        tbody.appendChild(tr);
    });

    if (visibleCount === 0) {
        tbody.innerHTML = `<tr><td colspan="3" class="p-8 text-center text-slate-400 text-sm font-bold">কোনো ফলাফল পাওয়া যায়নি</td></tr>`;
    }

    recalculateTableFooterTotals();
}

function recalculateTableFooterTotals() {
    let grandTotal = 0;
    let grandOC = 0;

    const search = document.getElementById('filterSearch').value.toLowerCase();

    Object.values(ORDERS_MAP).forEach(ord => {
        const rName = ord.retailer_name || ord.dealer_name || '';
        const rPhone = ord.retailer_phone || '';
        const srName = ord.sr_name || '';
        if (search && !rName.toLowerCase().includes(search) && !rPhone.toLowerCase().includes(search) && !srName.toLowerCase().includes(search)) {
            return;
        }

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
        const ocSign = grandOC > 0 ? '+' : (grandOC < 0 ? '-' : '');
        const ocClass = grandOC > 0 ? 'text-emerald-500' : 'text-rose-500';

        grandCell.innerHTML = `
          ৳ ${grandTotal.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}
          ${grandOC !== 0 
            ? `<div class="text-[10px] font-bold mt-0.5 ${ocClass}">(${ocSign}৳${ocFormatted})</div>` 
            : `<div class="text-[10px] font-bold mt-0.5 text-slate-400" style="display:none;"></div>`
          }
        `;
    }
}

// ── Time Format Helper ───────────────────────────────────────────────
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

// ── Toast Helper ────────────────────────────────────────────────
function showToast(message, type = 'success') {
  const container = document.getElementById('toastContainer');
  const toast = document.createElement('div');
  const bgClass = type === 'success' ? 'bg-slate-900 text-white' : 'bg-rose-600 text-white';
  const icon = type === 'success' ? 'fa-circle-check text-emerald-400' : 'fa-circle-exclamation text-rose-200';
  
  toast.className = `${bgClass} px-4 py-3 rounded-2xl shadow-xl border border-white/10 text-xs font-bold flex items-center gap-2.5 pointer-events-auto transform translate-y-2 opacity-0 transition-all duration-300`;
  toast.innerHTML = `<i class="fa-solid ${icon} text-sm"></i><span>${message}</span>`;
  container.appendChild(toast);

  setTimeout(() => toast.classList.remove('translate-y-2', 'opacity-0'), 10);
  setTimeout(() => {
    toast.classList.add('opacity-0', '-translate-y-2');
    setTimeout(() => toast.remove(), 300);
  }, 3500);
}


// ── Invoice Modal ─────────────────────────────────────────
function openInvoiceModal(orderId) {
  const order = ORDERS_MAP[orderId];
  if (!order) return;

  const modal = document.getElementById('invoiceModal');
  const modalContent = document.getElementById('invoiceModalContent');

  const retailerName = order.retailer_name || order.dealer_name || 'সাধারণ কাস্টমার';
  const retailerPhone = order.retailer_phone || 'N/A';
  const retailerAddress = order.retailer_address || 'ঠিকানা দেওয়া নেই';

  document.getElementById('invOrderId').innerText = '#ORD-' + order.id;
  document.getElementById('invDate').innerText = formatDateTime12Hr(order.created_at);
  document.getElementById('invRetailerName').innerText = retailerName;
  document.getElementById('invRetailerPhone').innerText = retailerPhone;
  document.getElementById('invRetailerAddress').innerText = retailerAddress;
  document.getElementById('invDealerName').innerText = 'ডিলার: ' + (order.dealer_name || 'Direct');
  document.getElementById('invSRName').innerText = 'SR: ' + (order.sr_name || 'N/A');

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

      const unitPrice  = parseFloat(prod.unit_price  || 0);
      const basePrice  = parseFloat(prod.base_price  || 0);
      const itemOC     = (unitPrice - basePrice) * qty;
      const ocAbs      = Math.abs(itemOC).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
      const ocSign     = itemOC >= 0 ? '+' : '-';
      const ocColor    = itemOC >= 0 ? '#10b981' : '#f43f5e';
      const ocHtml     = itemOC !== 0 ? `<div style="font-size:9px;font-weight:700;color:${ocColor};margin-top:1px;">(${ocSign}৳${ocAbs})</div>` : '';

      const tr = document.createElement('tr');
      tr.className = 'bg-white hover:bg-slate-50/30 transition-colors';
      tr.innerHTML = `
        <td class="py-2 px-2.5 font-mono font-bold text-slate-400 text-[10px]">${index + 1}</td>
        <td class="py-2 px-2.5"><div class="font-bold text-slate-800 text-[11px] leading-tight break-words font-siliguri">${prod.product_name || 'পণ্য'}</div></td>
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

  document.getElementById('invTotalItems').innerText = totalItemsCount + 'টি';
  document.getElementById('invTotalQtyPcs').innerText = totalQtyPcs + ' পিস';
  document.getElementById('invGrandTotal').innerText = '৳ ' + parseFloat(order.total_amount || 0).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});

  if (modal.parentElement !== document.body) document.body.appendChild(modal);
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
  setTimeout(() => modal.classList.add('hidden', 'pointer-events-none'), 200);
}

function toggleRetailerName(element, fullName, shortName) {
  if (element.innerText.trim().endsWith('..')) {
    element.innerText = fullName;
  } else {
    element.innerText = shortName;
  }
}


// ── Open Edit Order Bottom Sheet Modal ───────────────────────────────────────
function openEditOrderModal(orderId) {
  const order = ORDERS_MAP[orderId];
  if (!order) return;

  const retailerName = order.retailer_name || order.dealer_name || 'সাধারণ কাস্টমার';
  document.getElementById('editModalRetailerName').innerText = retailerName;
  document.getElementById('editModalOrderBadge').innerText = `#ORD-${order.id}`;
  document.getElementById('editReason').value = '';

  editingOrder = {
    id: order.id,
    retailer_name: retailerName,
    items: (order.products || []).map(p => {
      const ppb = parseInt(p.pieces_per_box || 1) || 1;
      const totalQty = parseInt(p.quantity || 0);
      const boxes = ppb > 1 ? Math.floor(totalQty / ppb) : 0;
      const pcs = ppb > 1 ? (totalQty % ppb) : totalQty;
      const unitPrice = parseFloat(p.unit_price || 0);
      const basePrice = parseFloat(p.base_price || 0);

      return {
        product_id: parseInt(p.product_id || p.id),
        product_name: p.product_name || p.name || 'পণ্য',
        product_image: p.product_image || p.image || '',
        ppb: ppb,
        box_type: p.box_type || 'কার্টন',
        base_price: basePrice,
        unit_price: unitPrice,
        boxes: boxes,
        pcs: pcs,
        total_qty: totalQty,
        line_total: totalQty * unitPrice,
        item_oc: (unitPrice - basePrice) * totalQty
      };
    })
  };

  populateAddProductSelector();
  renderEditOrderItems();

  const modal = document.getElementById('editOrderModal');
  const sheet = document.getElementById('editOrderSheetContent');
  
  if (modal.parentElement !== document.body) document.body.appendChild(modal);
  document.body.classList.add('overflow-hidden');

  modal.classList.remove('hidden', 'pointer-events-none');
  setTimeout(() => {
    modal.classList.remove('opacity-0');
    sheet.classList.remove('translate-y-full', 'sm:translate-y-8', 'sm:opacity-0');
  }, 10);
}

function closeEditOrderModal() {
  const modal = document.getElementById('editOrderModal');
  const sheet = document.getElementById('editOrderSheetContent');

  sheet.classList.add('translate-y-full', 'sm:translate-y-8', 'sm:opacity-0');
  modal.classList.add('opacity-0');
  document.body.classList.remove('overflow-hidden');

  setTimeout(() => {
    modal.classList.add('hidden', 'pointer-events-none');
    document.getElementById('addProductSelectorArea').classList.add('hidden');
    document.getElementById('btnAddProductToggle').classList.remove('hidden');
  }, 300);
}

function renderEditOrderItems() {
  const container = document.getElementById('editOrderItemsContainer');
  container.innerHTML = '';

  if (!editingOrder || editingOrder.items.length === 0) {
    container.innerHTML = `
      <div class="bg-white rounded-2xl p-8 text-center border border-slate-200 shadow-2xs font-siliguri">
        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-xl mx-auto mb-2"><i class="fa-solid fa-box-open"></i></div>
        <div class="text-xs font-bold text-slate-500">কোনো পণ্য নেই। নতুন পণ্য যোগ করুন।</div>
      </div>`;
    updateEditOrderSummary();
    return;
  }

  editingOrder.items.forEach((item, idx) => {
    const diffPerUnit = item.unit_price - item.base_price;
    const ocSign = diffPerUnit >= 0 ? '+' : '';
    const ocFormatted = `${ocSign}${parseFloat(diffPerUnit.toFixed(2))} O/C`;
    const ocBadgeBg = diffPerUnit > 0 ? 'bg-emerald-100 text-emerald-800' : (diffPerUnit < 0 ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-600');
    const isSingleUnit = (item.ppb <= 1) || (item.box_type && (item.box_type.toLowerCase() === 'piece' || item.box_type === 'পিস' || item.box_type === 'pcs'));

    const card = document.createElement('div');
    card.className = 'bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-3 space-y-2 hover:border-slate-300 transition duration-150 font-siliguri';
    card.innerHTML = `
      <div class="flex items-center justify-between gap-2">
        <div class="flex items-center gap-2.5 min-w-0">
          <div class="w-8 h-8 rounded-xl bg-slate-100 border border-slate-200/80 shrink-0 overflow-hidden flex items-center justify-center text-slate-400 shadow-3xs">
            ${item.product_image ? `<img src="<?= url('') ?>${item.product_image}" class="w-full h-full object-cover" onerror="this.parentElement.innerHTML='<i class=\\\'fa-solid fa-box text-slate-400 text-xs\\\'></i>'">` : `<i class="fa-solid fa-box text-slate-400 text-xs"></i>`}
          </div>
          <div class="min-w-0">
            <div class="flex items-center gap-1.5 flex-wrap">
              <h4 class="font-bold text-slate-900 text-xs sm:text-sm leading-tight truncate">${item.product_name}</h4>
              <span id="item-oc-pill-${idx}" class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-black font-mono leading-none ${ocBadgeBg}">${ocFormatted}</span>
            </div>
            <div class="text-[10px] text-slate-400 font-mono mt-0.5">${item.box_type || 'প্যাকিং'} (${!isSingleUnit ? item.ppb + ' পিস/বক্স' : '১ পিস'})</div>
          </div>
        </div>
        <button type="button" onclick="deleteEditItem(${idx})" class="w-7 h-7 rounded-lg bg-rose-50 border border-rose-100 text-rose-500 hover:bg-rose-500 hover:text-white transition flex items-center justify-center shrink-0 active:scale-95 shadow-3xs"><i class="fa-solid fa-trash-can text-xs"></i></button>
      </div>

      <div class="bg-slate-50/90 rounded-xl p-2 border border-slate-200/70 flex items-center justify-between gap-2 flex-wrap sm:flex-nowrap">
        <div class="flex items-center gap-1">
          <span class="text-[11px] font-bold text-slate-600 select-none">দর:</span>
          <div class="flex items-center bg-white border border-slate-200 rounded-lg p-0.5 shadow-2xs">
            <button type="button" onclick="stepPrice(${idx}, -1)" class="w-6 h-6 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold flex items-center justify-center text-xs active:scale-95 transition">-</button>
            <div class="flex items-center px-1">
              <span class="text-[10px] font-bold text-slate-400 select-none">৳</span>
              <input type="number" step="any" min="0" id="item-price-input-${idx}" value="${item.unit_price}" oninput="onEditUnitPriceChange(${idx}, this.value)" class="w-12 text-center text-xs font-mono font-bold text-slate-900 bg-transparent outline-none px-0.5">
            </div>
            <button type="button" onclick="stepPrice(${idx}, 1)" class="w-6 h-6 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold flex items-center justify-center text-xs active:scale-95 transition">+</button>
          </div>
        </div>

        <div class="flex items-center gap-1.5">
          <span class="text-[11px] font-bold text-slate-600 select-none">পরিমাণ:</span>
          ${!isSingleUnit ? `
            <div class="flex items-center bg-white border border-slate-200 rounded-lg p-0.5 shadow-2xs">
              <button type="button" onclick="stepBox(${idx}, -1)" class="w-6 h-6 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold flex items-center justify-center text-xs active:scale-95 transition">-</button>
              <input type="number" min="0" id="item-box-input-${idx}" value="${item.boxes}" oninput="onEditBoxChange(${idx}, this.value)" class="w-7 text-center text-xs font-mono font-bold text-slate-900 bg-transparent outline-none px-0.5">
              <span class="text-[10px] font-bold text-slate-500 pr-1 select-none">B</span>
              <button type="button" onclick="stepBox(${idx}, 1)" class="w-6 h-6 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold flex items-center justify-center text-xs active:scale-95 transition">+</button>
            </div>
          ` : ''}
          <div class="flex items-center bg-white border border-slate-200 rounded-lg p-0.5 shadow-2xs">
            <button type="button" onclick="stepPcs(${idx}, -1)" class="w-6 h-6 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold flex items-center justify-center text-xs active:scale-95 transition">-</button>
            <input type="number" min="0" id="item-pcs-input-${idx}" value="${item.pcs}" oninput="onEditPcsChange(${idx}, this.value)" class="w-7 text-center text-xs font-mono font-bold text-slate-900 bg-transparent outline-none px-0.5">
            <span class="text-[10px] font-bold text-slate-500 pr-1 select-none">P</span>
            <button type="button" onclick="stepPcs(${idx}, 1)" class="w-6 h-6 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold flex items-center justify-center text-xs active:scale-95 transition">+</button>
          </div>
        </div>
      </div>

      <div class="flex items-center justify-between text-[11px] pt-0.5 px-1">
        <div class="text-slate-400 font-mono text-[10px]" id="item-total-pcs-${idx}">মোট: ${item.total_qty} পিস</div>
        <div class="font-bold text-slate-900">মোট দাম: <span class="font-mono text-xs text-slate-950 font-black" id="item-line-total-${idx}">Tk ${parseFloat(item.line_total).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}</span></div>
      </div>
    `;
    container.appendChild(card);
  });

  updateEditOrderSummary();
}

function stepPrice(idx, delta) {
  if (!editingOrder || !editingOrder.items[idx]) return;
  editingOrder.items[idx].unit_price = Math.max(0, parseFloat((editingOrder.items[idx].unit_price + delta).toFixed(2)));
  recalculateItem(idx);
}

function onEditBoxChange(idx, val) {
  if (!editingOrder || !editingOrder.items[idx]) return;
  editingOrder.items[idx].boxes = Math.max(0, parseInt(val) || 0);
  recalculateItem(idx);
}

function onEditPcsChange(idx, val) {
  if (!editingOrder || !editingOrder.items[idx]) return;
  editingOrder.items[idx].pcs = Math.max(0, parseInt(val) || 0);
  recalculateItem(idx);
}

function stepBox(idx, delta) {
  if (!editingOrder || !editingOrder.items[idx]) return;
  editingOrder.items[idx].boxes = Math.max(0, (editingOrder.items[idx].boxes || 0) + delta);
  recalculateItem(idx);
}

function stepPcs(idx, delta) {
  if (!editingOrder || !editingOrder.items[idx]) return;
  editingOrder.items[idx].pcs = Math.max(0, (editingOrder.items[idx].pcs || 0) + delta);
  recalculateItem(idx);
}

function onEditUnitPriceChange(idx, val) {
  if (!editingOrder || !editingOrder.items[idx]) return;
  editingOrder.items[idx].unit_price = Math.max(0, parseFloat(val) || 0);
  recalculateItem(idx);
}

function deleteEditItem(idx) {
  if (!editingOrder || !editingOrder.items[idx]) return;
  const item = editingOrder.items[idx];
  editingOrder.items.splice(idx, 1);
  renderEditOrderItems();
  populateAddProductSelector();
  showToast(`"${item.product_name}" সরানো হয়েছে।`, 'error');
}

function recalculateItem(idx) {
  const item = editingOrder.items[idx];
  if (!item) return;

  item.total_qty = (item.boxes * item.ppb) + item.pcs;
  item.line_total = item.total_qty * item.unit_price;
  item.item_oc = (item.unit_price - item.base_price) * item.total_qty;

  const boxInput = document.getElementById(`item-box-input-${idx}`);
  if (boxInput && parseInt(boxInput.value) !== item.boxes) boxInput.value = item.boxes;

  const pcsInput = document.getElementById(`item-pcs-input-${idx}`);
  if (pcsInput && parseInt(pcsInput.value) !== item.pcs) pcsInput.value = item.pcs;

  const priceInput = document.getElementById(`item-price-input-${idx}`);
  if (priceInput && parseFloat(priceInput.value) !== item.unit_price) priceInput.value = item.unit_price;

  const lineTotalEl = document.getElementById(`item-line-total-${idx}`);
  if (lineTotalEl) lineTotalEl.innerText = `Tk ${parseFloat(item.line_total).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}`;

  const totalPcsEl = document.getElementById(`item-total-pcs-${idx}`);
  if (totalPcsEl) totalPcsEl.innerText = `মোট: ${item.total_qty} পিস`;

  const pillEl = document.getElementById(`item-oc-pill-${idx}`);
  if (pillEl) {
    const diffPerUnit = item.unit_price - item.base_price;
    const ocSign = diffPerUnit >= 0 ? '+' : '';
    pillEl.innerText = `${ocSign}${parseFloat(diffPerUnit.toFixed(2))} O/C`;
    pillEl.className = `inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-black font-mono leading-none ${diffPerUnit > 0 ? 'bg-emerald-100 text-emerald-800' : (diffPerUnit < 0 ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-600')}`;
  }
  updateEditOrderSummary();
}

function updateEditOrderSummary() {
  if (!editingOrder) return;
  let grandSubtotal = 0, grandOC = 0;
  editingOrder.items.forEach(item => { grandSubtotal += item.line_total; grandOC += item.item_oc; });

  const subtotalEl = document.getElementById('editModalSubtotal');
  const ocBadgeEl = document.getElementById('editModalOCBadge');

  if (subtotalEl) subtotalEl.innerText = `Tk ${parseFloat(grandSubtotal).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}`;
  if (ocBadgeEl) {
    const ocSign = grandOC >= 0 ? '+' : '-';
    const ocAbs = Math.abs(grandOC).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
    ocBadgeEl.innerText = `O/C ${ocSign}৳${ocAbs}`;
    ocBadgeEl.className = `inline-flex items-center px-2 py-0.5 rounded-md text-xs font-black shadow-2xs ${grandOC > 0 ? 'bg-emerald-100 text-emerald-800' : (grandOC < 0 ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-700')}`;
  }
}

function toggleAddProductDropdown() {
  const area = document.getElementById('addProductSelectorArea');
  const btn = document.getElementById('btnAddProductToggle');
  area.classList.toggle('hidden');
  btn.classList.toggle('hidden');
}

function populateAddProductSelector() {
  const select = document.getElementById('editAddProductSelect');
  if (!select) return;
  select.innerHTML = '<option value="">-- পণ্য সিলেক্ট করুন --</option>';

  const currentIds = new Set((editingOrder?.items || []).map(i => i.product_id));
  (ALL_SR_PRODUCTS || []).forEach(prod => {
    if (!currentIds.has(parseInt(prod.id))) {
      const opt = document.createElement('option');
      opt.value = prod.id;
      opt.innerText = `${prod.name} (দর: ৳${parseFloat(prod.price || 0)})`;
      select.appendChild(opt);
    }
  });
}

function confirmAddProduct() {
  const select = document.getElementById('editAddProductSelect');
  const prodId = parseInt(select.value);
  if (!prodId) { alert('অনুগ্রহ করে একটি পণ্য সিলেক্ট করুন।'); return; }

  const prod = ALL_SR_PRODUCTS.find(p => parseInt(p.id) === prodId);
  if (!prod) return;

  const ppb = parseInt(prod.pieces_per_carton || prod.ppb || 1) || 1;
  const unitPrice = parseFloat(prod.price || 0);
  const basePrice = parseFloat(prod.buying_price || 0) + (parseFloat(prod.buying_price || 0) * (parseFloat(prod.dealer_percentage || 0) / 100)); // Manager products have buying_price and dealer_percentage

  editingOrder.items.push({
    product_id: parseInt(prod.id),
    product_name: prod.name,
    product_image: prod.image || '',
    ppb: ppb,
    box_type: prod.box_type || 'কার্টন',
    base_price: basePrice,
    unit_price: unitPrice,
    boxes: 1,
    pcs: 0,
    total_qty: ppb,
    line_total: ppb * unitPrice,
    item_oc: (unitPrice - basePrice) * ppb
  });

  renderEditOrderItems();
  populateAddProductSelector();
  toggleAddProductDropdown();
  showToast(`"${prod.name}" অর্ডারে যোগ করা হয়েছে।`, 'success');
}

async function submitOrderEdit() {
  if (!editingOrder) return;
  const reason = document.getElementById('editReason').value.trim();
  if (!reason) {
      alert('অর্ডার পরিবর্তনের কারণ লিখুন।');
      return;
  }

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
  btnIcon.classList.add('hidden');
  btnSpinner.classList.remove('hidden');

  try {
    const payloadItems = validItems.map(item => ({
        product_id: item.product_id,
        qty: item.total_qty,
        price: item.unit_price
    }));

    const formData = new FormData();
    formData.append('reason', reason);
    formData.append('items', JSON.stringify(payloadItems));

    const response = await fetch(`<?= url("manager/api/operations/edit-order/") ?>${editingOrder.id}`, {
      method: 'POST',
      body: formData
    });

    const result = await response.json();

    if (result.success && result.order) {
      ORDERS_MAP[editingOrder.id] = result.order;
      renderTable(); // Re-render table completely
      showToast(result.message || 'অর্ডার সফলভাবে আপডেট করা হয়েছে!', 'success');
      closeEditOrderModal();
    } else {
      alert(result.message || 'অর্ডার আপডেট করতে সমস্যা হয়েছে।');
    }
  } catch (err) {
    console.error('Order update error:', err);
    alert('সার্ভার এরর: অর্ডার আপডেট করা সম্ভব হয়নি।');
  } finally {
    btnConfirm.disabled = false;
    btnText.innerText = 'সেভ করুন';
    btnIcon.classList.remove('hidden');
    btnSpinner.classList.add('hidden');
  }
}

async function deleteOrderClientSide(id) {
    const reason = prompt("অর্ডারটি ডিলিট করার কারণ লিখুন (বাধ্যতামূলক):");
    if (reason === null) return; 
    if (reason.trim() === '') {
        alert("কারণ লেখা বাধ্যতামূলক!");
        return;
    }

    try {
        const formData = new FormData();
        formData.append('reason', reason);
        const res = await fetch(`<?= url("manager/api/operations/delete-order/") ?>${id}`, {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message || 'অর্ডার সফলভাবে ডিলিট হয়েছে।', 'success');
            delete ORDERS_MAP[id];
            renderTable();
        } else {
            alert(data.message || 'অর্ডার ডিলিট করতে সমস্যা হয়েছে।');
        }
    } catch (err) {
        console.error(err);
        alert('সার্ভার এরর');
    }
}
</script>
