<?php $pageTitle = 'Orders & Retailer Map'; ?>

<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

<style>
  .custom-map-marker {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    color: white;
    font-size: 14px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    border: 2px solid #ffffff;
    transition: transform 0.2s;
  }
  .custom-map-marker:hover {
    transform: scale(1.15);
  }
  .marker-checked_out, .marker-delivered { background-color: #10b981; }
  .marker-dispatched { background-color: #3b82f6; }
  .marker-pending, .marker-confirmed, .marker-ordered { background-color: #f59e0b; }
  .marker-cancelled { background-color: #ef4444; }

  .badge-status {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 10px;
    border-radius: 9999px;
    font-size: 0.725rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.025em;
  }
  .badge-delivered, .badge-checked_out { background-color: #d1fae5; color: #065f46; }
  .badge-dispatched { background-color: #dbeafe; color: #1e40af; }
  .badge-pending, .badge-ordered { background-color: #fef3c7; color: #92400e; }
  .badge-confirmed { background-color: #e0e7ff; color: #3730a3; }
  .badge-cancelled { background-color: #fee2e2; color: #991b1b; }

  /* Modal animation */
  #orderModal.hidden {
    opacity: 0;
    pointer-events: none;
  }
  #orderModal:not(.hidden) {
    opacity: 1;
    pointer-events: auto;
  }
  .modal-content-box {
    transition: transform 0.25s ease-out;
  }
  #orderModal.hidden .modal-content-box {
    transform: scale(0.95) translateY(10px);
  }
  #orderModal:not(.hidden) .modal-content-box {
    transform: scale(1) translateY(0);
  }
</style>

<div class="space-y-6">

  <!-- ── Top Header & Date Selection ───────────────────────────── -->
  <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <div class="flex items-center gap-2 text-xs font-semibold text-blue-600 uppercase tracking-wider mb-1">
        <i class="fa-solid fa-store"></i> Retailer Order Management
      </div>
      <h1 class="text-2xl font-black text-gray-900 tracking-tight">Retailer Orders & Delivery Map</h1>
      <p class="text-xs text-gray-500 mt-1">View ordered retailers on interactive map & cards with detailed status breakdown</p>
    </div>

    <!-- Date selector -->
    <div class="flex items-center gap-3 bg-gray-50 border border-gray-200 px-4 py-2.5 rounded-xl">
      <div class="flex items-center gap-2">
        <i class="fa-regular fa-calendar-days text-brand text-base"></i>
        <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Order Date:</span>
      </div>
      <input type="date" id="filterDate" value="<?= h($selectedDate) ?>" 
             class="bg-white border border-gray-300 text-gray-900 text-sm font-bold rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-brand focus:border-brand outline-none cursor-pointer">
    </div>
  </div>

  <!-- ── Summary Counters Bar ──────────────────────────────────── -->
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-3">
      <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 text-xl font-bold flex-shrink-0">
        <i class="fa-solid fa-shop"></i>
      </div>
      <div>
        <div class="text-xs text-gray-500 font-medium">Ordered Retailers</div>
        <div class="text-xl font-black text-gray-900" id="cntTotalRetailers">0</div>
      </div>
    </div>

    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-3">
      <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 text-xl font-bold flex-shrink-0">
        <i class="fa-solid fa-bangladeshi-taka-sign"></i>
      </div>
      <div>
        <div class="text-xs text-gray-500 font-medium">Total Orders Value</div>
        <div class="text-xl font-black text-emerald-600" id="cntTotalAmount">৳0</div>
      </div>
    </div>

    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-3">
      <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 text-xl font-bold flex-shrink-0">
        <i class="fa-solid fa-clock-rotate-left"></i>
      </div>
      <div>
        <div class="text-xs text-gray-500 font-medium">Ordered / Open</div>
        <div class="text-xl font-black text-amber-600" id="cntOrdered">0</div>
      </div>
    </div>

    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-3">
      <div class="w-12 h-12 rounded-xl bg-teal-50 flex items-center justify-center text-teal-600 text-xl font-bold flex-shrink-0">
        <i class="fa-solid fa-circle-check"></i>
      </div>
      <div>
        <div class="text-xs text-gray-500 font-medium">Checked Out / Delivered</div>
        <div class="text-xl font-black text-teal-600" id="cntCheckedOut">0</div>
      </div>
    </div>
  </div>

  <!-- ── Navigation Tabs & Filters Bar ─────────────────────────── -->
  <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm space-y-4">
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pb-3 border-b border-gray-100">
      
      <!-- Tab Controls -->
      <div class="inline-flex bg-gray-100 p-1 rounded-xl">
        <button id="btnTabExcel" onclick="switchTab('excel')" 
                class="px-5 py-2 rounded-lg text-sm font-bold transition flex items-center gap-2 bg-emerald-600 text-white shadow-sm">
          <i class="fa-solid fa-file-excel text-base"></i> Modern Excel View
        </button>
        <button id="btnTabMap" onclick="switchTab('map')" 
                class="px-5 py-2 rounded-lg text-sm font-bold transition flex items-center gap-2 text-gray-600 hover:text-gray-900">
          <i class="fa-solid fa-map text-base"></i> Map View
        </button>
        <button id="btnTabList" onclick="switchTab('list')" 
                class="px-5 py-2 rounded-lg text-sm font-bold transition flex items-center gap-2 text-gray-600 hover:text-gray-900">
          <i class="fa-solid fa-table-list text-base"></i> Card List View
        </button>
      </div>

      <!-- Quick Search Input -->
      <div class="flex items-center gap-2 min-w-[260px]">
        <div class="relative w-full">
          <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
          <input type="text" id="filterSearch" placeholder="Search retailer name or phone..." 
                 class="w-full pl-9 pr-3 py-2 bg-gray-50 border border-gray-200 text-xs rounded-xl focus:bg-white focus:ring-2 focus:ring-brand focus:border-brand outline-none">
        </div>
        <button onclick="toggleAdvanceFilters()" class="px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl flex items-center gap-1.5 transition flex-shrink-0">
          <i class="fa-solid fa-sliders text-blue-600"></i> Filters
        </button>
      </div>
    </div>

    <!-- ── Advance Filters Panel ────────────────────────────────── -->
    <div id="advanceFilterPanel" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 pt-2">
      <!-- SR Filter -->
      <div>
        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Sales Rep (SR)</label>
        <select id="filterSr" class="w-full bg-gray-50 border border-gray-200 text-xs rounded-xl p-2.5 focus:bg-white focus:ring-2 focus:ring-brand outline-none">
          <option value="">All SRs</option>
          <?php foreach ($srs as $sr): ?>
            <option value="<?= $sr['id'] ?>"><?= h($sr['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- DSR Filter -->
      <div>
        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Delivery Rep (DSR)</label>
        <select id="filterDsr" class="w-full bg-gray-50 border border-gray-200 text-xs rounded-xl p-2.5 focus:bg-white focus:ring-2 focus:ring-brand outline-none">
          <option value="">All DSRs</option>
          <?php foreach ($dsrs as $dsr): ?>
            <option value="<?= $dsr['id'] ?>"><?= h($dsr['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Warehouse Filter -->
      <div>
        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Warehouse</label>
        <select id="filterWarehouse" class="w-full bg-gray-50 border border-gray-200 text-xs rounded-xl p-2.5 focus:bg-white focus:ring-2 focus:ring-brand outline-none">
          <option value="">All Warehouses</option>
          <?php foreach ($warehouses as $wh): ?>
            <option value="<?= $wh['id'] ?>"><?= h($wh['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Order Status Filter -->
      <div>
        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Order Status</label>
        <select id="filterStatus" class="w-full bg-gray-50 border border-gray-200 text-xs rounded-xl p-2.5 focus:bg-white focus:ring-2 focus:ring-brand outline-none">
          <option value="">All Statuses</option>
          <option value="pending">Pending</option>
          <option value="confirmed">Confirmed</option>
          <option value="dispatched">Dispatched</option>
          <option value="delivered">Delivered</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>

      <!-- O/C Status Filter -->
      <div>
        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">O/C Status</label>
        <select id="filterOcStatus" class="w-full bg-gray-50 border border-gray-200 text-xs rounded-xl p-2.5 focus:bg-white focus:ring-2 focus:ring-brand outline-none">
          <option value="">All O/C Status</option>
          <option value="ordered">Ordered / Open</option>
          <option value="checked_out">Checked Out / Delivered</option>
        </select>
      </div>

      <!-- Reset button -->
      <div class="sm:col-span-2 md:col-span-5 flex justify-end">
        <button onclick="resetFilters()" class="text-xs text-gray-500 hover:text-red-600 font-semibold flex items-center gap-1">
          <i class="fa-solid fa-rotate-left"></i> Reset All Filters
        </button>
      </div>
    </div>
  </div>

  <!-- ═════════════════════════════════════════════════════════════
       MODERN EXCEL SPREADSHEET VIEW TAB
  ══════════════════════════════════════════════════════════════ -->
  <div id="viewExcelContainer" class="space-y-4">
    <div class="excel-container">
      
      <!-- Excel Ribbon Toolbar -->
      <div class="excel-ribbon">
        <div class="flex items-center gap-3">
          <div class="excel-ribbon-badge">
            <i class="fa-solid fa-file-excel text-blue-200 text-lg"></i>
            <span>Orders Excel Spreadsheet</span>
          </div>
          <span class="text-xs text-blue-100 hidden sm:inline-block">• Live Retailer Orders Data Grid</span>
        </div>

        <div class="flex items-center gap-2">
          <button onclick="exportTableToCSV('excelOrdersTable', 'Retailer_Orders_Sheet.csv')" class="excel-action-btn">
            <i class="fa-solid fa-file-csv"></i> Export CSV / Excel
          </button>
          <button onclick="printTable('excelOrdersTable', 'Retailer Orders Excel Sheet')" class="excel-action-btn excel-action-btn-secondary">
            <i class="fa-solid fa-print"></i> Print Sheet
          </button>
        </div>
      </div>

      <!-- Excel Formula & Summary Bar -->
      <div class="excel-formula-bar">
        <span class="fx-symbol">fx</span>
        <div class="excel-pill">
          <i class="fa-solid fa-calculator text-blue-600"></i>
          <span>COUNT: <strong id="fxCount" class="text-blue-700 font-mono">0 rows</strong></span>
        </div>
        <div class="excel-pill">
          <i class="fa-solid fa-bangladeshi-taka-sign text-blue-600"></i>
          <span>SUM TOTAL: <strong id="fxSumTotal" class="text-blue-700 font-mono">৳0</strong></span>
        </div>
        <div class="excel-pill">
          <i class="fa-solid fa-circle-check text-emerald-600"></i>
          <span>DELIVERED: <strong id="fxSumDelivered" class="text-emerald-700 font-mono">৳0</strong></span>
        </div>
        <div class="excel-pill">
          <i class="fa-solid fa-clock-rotate-left text-amber-600"></i>
          <span>PENDING: <strong id="fxSumPending" class="text-amber-700 font-mono">৳0</strong></span>
        </div>
      </div>

      <!-- Excel Grid Table -->
      <div class="overflow-x-auto max-h-[620px]">
        <table class="excel-table" id="excelOrdersTable">
          <thead>
            <tr>
              <th class="excel-row-num">#</th>
              <th>Order No</th>
              <th>Retailer Name</th>
              <th>Phone</th>
              <th>SR Name</th>
              <th>DSR Name</th>
              <th>Warehouse</th>
              <th class="text-center">Items (Box/Pcs)</th>
              <th class="text-right">Total Amount</th>
              <th class="text-center">Order Status</th>
              <th class="text-center">O/C Status</th>
              <th class="text-center">Action</th>
            </tr>
          </thead>
          <tbody id="excelOrdersTableBody">
            <!-- Dynamic Excel Rows -->
          </tbody>
        </table>
      </div>

      <!-- Empty Excel State -->
      <div id="emptyExcelState" class="hidden py-12 text-center bg-white">
        <div class="w-16 h-16 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-3 text-2xl">
          <i class="fa-solid fa-file-excel"></i>
        </div>
        <h3 class="text-base font-bold text-gray-800">No Orders in Sheet</h3>
        <p class="text-xs text-gray-500 mt-1">Change date or filter parameters to display orders data.</p>
      </div>

    </div>
  </div>

  <!-- ═════════════════════════════════════════════════════════════
       MAP VIEW TAB
  ══════════════════════════════════════════════════════════════ -->
  <div id="viewMapContainer" class="hidden relative bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden p-2">
    <!-- Map Legend Bar -->
    <div class="p-3 bg-gray-50 rounded-xl mb-2 flex flex-wrap items-center justify-between gap-3 text-xs">
      <div class="flex items-center gap-4 font-semibold text-gray-700">
        <span class="text-gray-400">Marker Legend:</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span> Checked Out / Delivered</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span> Dispatched</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span> Pending / Ordered</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span> Cancelled</span>
      </div>
      <button onclick="recenterMap()" class="px-3 py-1 bg-white border border-gray-200 text-gray-700 rounded-lg text-xs font-bold hover:bg-gray-100 transition shadow-sm">
        <i class="fa-solid fa-compress"></i> Recenter Map
      </button>
    </div>

    <div id="leafletMap" class="w-full h-[540px] rounded-xl z-10"></div>
  </div>

  <!-- ═════════════════════════════════════════════════════════════
       CARD LIST VIEW TAB
  ══════════════════════════════════════════════════════════════ -->
  <div id="viewListContainer" class="hidden">
    <div id="cardsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
      <!-- Dynamic Retailer Cards render here -->
    </div>
    
    <!-- Empty State -->
    <div id="emptyListState" class="hidden py-16 text-center bg-white rounded-2xl border border-gray-100 shadow-sm">
      <div class="w-20 h-20 rounded-full bg-blue-50 text-blue-400 flex items-center justify-center mx-auto mb-4 text-3xl">
        <i class="fa-solid fa-store-slash"></i>
      </div>
      <h3 class="text-lg font-bold text-gray-800">No Ordered Retailers Found</h3>
      <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">There are no retailer orders matching your selected date and filter parameters.</p>
    </div>
  </div>

</div>

<!-- ═════════════════════════════════════════════════════════════
     RETAILER ORDER DETAIL POPUP MODAL
══════════════════════════════════════════════════════════════ -->
<div id="orderModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm transition-all duration-300">
  <div class="modal-content-box bg-white rounded-3xl shadow-2xl border border-gray-100 max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden">
    
    <!-- Modal Header -->
    <div class="px-6 py-5 bg-blue-600 text-white flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center text-lg font-bold border border-blue-400/30">
          <i class="fa-solid fa-store"></i>
        </div>
        <div>
          <h3 id="modalRetailerName" class="text-lg font-black leading-tight text-white">Retailer Name</h3>
          <div class="flex items-center gap-2 text-xs text-gray-300 mt-0.5">
            <span id="modalOrderNo" class="font-mono bg-white/10 px-2 py-0.5 rounded text-blue-200">ORD-00000</span>
            <span>•</span>
            <span id="modalOrderDate">Date</span>
          </div>
        </div>
      </div>
      <button onclick="closeOrderModal()" class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 text-gray-300 hover:text-white flex items-center justify-center transition">
        <i class="fa-solid fa-xmark text-lg"></i>
      </button>
    </div>

    <!-- Modal Body (Scrollable) -->
    <div class="p-6 overflow-y-auto space-y-5 flex-1">

      <!-- Status Badges Bar -->
      <div class="flex items-center justify-between bg-gray-50 p-3.5 rounded-2xl border border-gray-100">
        <div class="flex items-center gap-2">
          <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Order Status:</span>
          <span id="modalOrderStatusBadge" class="badge-status badge-pending">PENDING</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">O/C Status:</span>
          <span id="modalOcStatusBadge" class="badge-status badge-ordered">ORDERED</span>
        </div>
      </div>

      <!-- Retailer & Personnel Info Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
        <div class="bg-blue-50/50 p-3.5 rounded-2xl border border-blue-100/60 space-y-1.5">
          <div class="font-bold text-gray-700 flex items-center gap-1.5">
            <i class="fa-solid fa-phone text-blue-600"></i> Contact Info
          </div>
          <div id="modalPhone" class="text-gray-900 font-semibold">Phone</div>
          <div id="modalAddress" class="text-gray-500 leading-snug">Address</div>
        </div>

        <div class="bg-gray-50 p-3.5 rounded-2xl border border-gray-100 space-y-1.5">
          <div class="font-bold text-gray-700 flex items-center gap-1.5">
            <i class="fa-solid fa-user-shield text-gray-500"></i> Route & Personnel
          </div>
          <div class="text-gray-600">SR: <span id="modalSrName" class="font-bold text-gray-900">Name</span></div>
          <div class="text-gray-600">DSR: <span id="modalDsrName" class="font-bold text-gray-900">Name</span></div>
          <div class="text-gray-600">Warehouse: <span id="modalWarehouseName" class="font-bold text-gray-900">Warehouse</span></div>
        </div>
      </div>

      <!-- Overview Metrics Cards -->
      <div class="grid grid-cols-3 gap-3">
        <div class="bg-emerald-50/60 border border-emerald-100 p-3 rounded-2xl text-center">
          <div class="text-[11px] font-bold text-emerald-700 uppercase">Total Amount</div>
          <div id="modalTotalAmount" class="text-lg font-black text-emerald-700 mt-0.5">৳0</div>
        </div>
        <div class="bg-blue-50/60 border border-blue-100 p-3 rounded-2xl text-center">
          <div class="text-[11px] font-bold text-blue-700 uppercase">Total Boxes</div>
          <div id="modalTotalBoxes" class="text-lg font-black text-blue-700 mt-0.5">0</div>
        </div>
        <div class="bg-purple-50/60 border border-purple-100 p-3 rounded-2xl text-center">
          <div class="text-[11px] font-bold text-purple-700 uppercase">Total Pieces</div>
          <div id="modalTotalPieces" class="text-lg font-black text-purple-700 mt-0.5">0</div>
        </div>
      </div>

      <!-- Notes if any -->
      <div id="modalNotesBox" class="hidden bg-amber-50 border border-amber-200 text-amber-900 p-3 rounded-2xl text-xs">
        <span class="font-bold">Order Notes:</span> <span id="modalNotesText"></span>
      </div>

      <!-- Itemized Ordered Products Table -->
      <div>
        <h4 class="text-sm font-black text-gray-900 mb-2 flex items-center justify-between">
          <span><i class="fa-solid fa-boxes-stacked text-brand mr-1"></i> Ordered Products</span>
          <span id="modalItemCount" class="text-xs font-semibold text-gray-500">0 Items</span>
        </h4>
        <div class="border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
          <table class="w-full text-xs text-left">
            <thead class="bg-gray-100 text-gray-600 font-bold uppercase tracking-wider text-[10px]">
              <tr>
                <th class="py-2.5 px-3">Product</th>
                <th class="py-2.5 px-3 text-center">Qty (Box / Pcs)</th>
                <th class="py-2.5 px-3 text-right">Unit Price</th>
                <th class="py-2.5 px-3 text-right">Total (৳)</th>
              </tr>
            </thead>
            <tbody id="modalItemsTableBody" class="divide-y divide-gray-100">
              <!-- Rendered dynamically -->
            </tbody>
          </table>
        </div>
      </div>

    </div>

    <!-- Modal Footer -->
    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end">
      <button onclick="closeOrderModal()" class="px-6 py-2.5 bg-gray-900 hover:bg-black text-white text-xs font-bold rounded-xl transition shadow-md">
        Close Details
      </button>
    </div>

  </div>
</div>

<!-- ═════════════════════════════════════════════════════════════
     JAVASCRIPT LOGIC
══════════════════════════════════════════════════════════════ -->
<script>
  let currentTab = 'excel';
  let leafletMap = null;
  let mapMarkers = [];
  let fetchedOrders = [];

  // Initialize page on load
  document.addEventListener('DOMContentLoaded', () => {
    initMap();
    fetchOrdersData();

    // Event listeners for live filtering
    document.getElementById('filterDate').addEventListener('change', fetchOrdersData);
    document.getElementById('filterSr').addEventListener('change', fetchOrdersData);
    document.getElementById('filterDsr').addEventListener('change', fetchOrdersData);
    document.getElementById('filterWarehouse').addEventListener('change', fetchOrdersData);
    document.getElementById('filterStatus').addEventListener('change', fetchOrdersData);
    document.getElementById('filterOcStatus').addEventListener('change', fetchOrdersData);

    let searchTimer;
    document.getElementById('filterSearch').addEventListener('input', () => {
      clearTimeout(searchTimer);
      searchTimer = setTimeout(fetchOrdersData, 300);
    });

    // Close modal on Escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') closeOrderModal();
    });
  });

  // ── Switch Tabs ──────────────────────────────────────────────
  function switchTab(tab) {
    currentTab = tab;
    const btnExcel = document.getElementById('btnTabExcel');
    const btnMap = document.getElementById('btnTabMap');
    const btnList = document.getElementById('btnTabList');
    const viewExcel = document.getElementById('viewExcelContainer');
    const viewMap = document.getElementById('viewMapContainer');
    const viewList = document.getElementById('viewListContainer');

    const activeCls = "px-5 py-2 rounded-lg text-sm font-bold transition flex items-center gap-2 bg-emerald-600 text-white shadow-sm";
    const inactiveCls = "px-5 py-2 rounded-lg text-sm font-bold transition flex items-center gap-2 text-gray-600 hover:text-gray-900";

    btnExcel.className = (tab === 'excel') ? activeCls : inactiveCls;
    btnMap.className = (tab === 'map') ? activeCls : inactiveCls;
    btnList.className = (tab === 'list') ? activeCls : inactiveCls;

    viewExcel.classList.toggle('hidden', tab !== 'excel');
    viewMap.classList.toggle('hidden', tab !== 'map');
    viewList.classList.toggle('hidden', tab !== 'list');

    if (tab === 'map') {
      setTimeout(() => {
        if (leafletMap) leafletMap.invalidateSize();
      }, 200);
    }
  }

  function toggleAdvanceFilters() {
    const panel = document.getElementById('advanceFilterPanel');
    panel.classList.toggle('hidden');
  }

  function resetFilters() {
    document.getElementById('filterSr').value = '';
    document.getElementById('filterDsr').value = '';
    document.getElementById('filterWarehouse').value = '';
    document.getElementById('filterStatus').value = '';
    document.getElementById('filterOcStatus').value = '';
    document.getElementById('filterSearch').value = '';
    fetchOrdersData();
  }

  // ── Initialize Leaflet Map ───────────────────────────────────
  function initMap() {
    // Default center: Dhaka Bangladesh
    leafletMap = L.map('leafletMap').setView([23.8103, 90.4125], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '© OpenStreetMap contributors'
    }).addTo(leafletMap);
  }

  // ── Fetch Orders AJAX ────────────────────────────────────────
  function fetchOrdersData() {
    const date = document.getElementById('filterDate').value;
    const sr_id = document.getElementById('filterSr').value;
    const dsr_id = document.getElementById('filterDsr').value;
    const warehouse_id = document.getElementById('filterWarehouse').value;
    const status = document.getElementById('filterStatus').value;
    const oc_status = document.getElementById('filterOcStatus').value;
    const search = document.getElementById('filterSearch').value;

    const params = new URLSearchParams({
      date, sr_id, dsr_id, warehouse_id, status, oc_status, search
    });

    fetch(`<?= url('admin/api/orders') ?>?${params.toString()}`)
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          fetchedOrders = data.orders || [];
          updateSummaryCounters(data.summary || {});
          renderExcelGrid(fetchedOrders);
          renderMapMarkers(fetchedOrders);
          renderCardList(fetchedOrders);
        }
      })
      .catch(err => console.error('Error fetching orders:', err));
  }

  // ── Update Counter Cards ──────────────────────────────────────
  function updateSummaryCounters(summary) {
    document.getElementById('cntTotalRetailers').textContent = summary.total_retailers || 0;
    document.getElementById('cntTotalAmount').textContent = '৳' + (summary.total_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 2});
    document.getElementById('cntOrdered').textContent = summary.ordered_cnt || 0;
    document.getElementById('cntCheckedOut').textContent = summary.checked_out_cnt || 0;
  }

  // ── Render Modern Excel Spreadsheet Grid ──────────────────────
  function renderExcelGrid(orders) {
    const tbody = document.getElementById('excelOrdersTableBody');
    const emptyState = document.getElementById('emptyExcelState');
    tbody.innerHTML = '';

    if (orders.length === 0) {
      emptyState.classList.remove('hidden');
      document.getElementById('fxCount').textContent = '0 rows';
      document.getElementById('fxSumTotal').textContent = '৳0';
      document.getElementById('fxSumDelivered').textContent = '৳0';
      document.getElementById('fxSumPending').textContent = '৳0';
      return;
    }
    emptyState.classList.add('hidden');

    let sumTotal = 0;
    let sumDelivered = 0;
    let sumPending = 0;

    orders.forEach((ord, i) => {
      sumTotal += (parseFloat(ord.total_amount) || 0);
      if (ord.oc_status === 'checked_out' || ord.order_status === 'delivered') {
        sumDelivered += (parseFloat(ord.total_amount) || 0);
      } else {
        sumPending += (parseFloat(ord.total_amount) || 0);
      }

      const tr = document.createElement('tr');
      
      const ocBadgeClass = ord.oc_status === 'checked_out' ? 'badge-checked_out' : 'badge-ordered';
      const ocText = ord.oc_status === 'checked_out' ? 'Checked Out' : 'Ordered';
      const orderBadgeClass = 'badge-' + ord.order_status;

      tr.innerHTML = `
        <td class="excel-row-num">${i + 1}</td>
        <td class="excel-mono text-blue-600 font-bold">${escapeHtml(ord.order_no)}</td>
        <td class="font-bold text-gray-900">${escapeHtml(ord.retailer_name)}</td>
        <td class="text-gray-500 font-mono">${escapeHtml(ord.phone)}</td>
        <td class="font-medium text-gray-700">${escapeHtml(ord.sr_name)}</td>
        <td class="font-medium text-gray-700">${escapeHtml(ord.dsr_name)}</td>
        <td class="text-xs text-gray-500">${escapeHtml(ord.warehouse_name)}</td>
        <td class="excel-qty">${ord.items_count} <span class="text-xs text-gray-400">(${ord.total_boxes}B / ${ord.total_pieces}P)</span></td>
        <td class="excel-money">৳${(parseFloat(ord.total_amount) || 0).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 2})}</td>
        <td class="text-center"><span class="badge-status ${orderBadgeClass}">${ord.order_status}</span></td>
        <td class="text-center"><span class="badge-status ${ocBadgeClass}">${ocText}</span></td>
        <td class="text-center">
          <button onclick="openOrderModal(${ord.order_id})" 
                  class="px-2.5 py-1 bg-blue-50 hover:bg-blue-600 hover:text-white text-blue-700 text-xs font-bold rounded-lg transition border border-blue-200">
            <i class="fa-solid fa-eye mr-1"></i> Details
          </button>
        </td>
      `;
      tbody.appendChild(tr);
    });

    // Update Formula Bar stats
    document.getElementById('fxCount').textContent = orders.length + ' rows';
    document.getElementById('fxSumTotal').textContent = '৳' + sumTotal.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 2});
    document.getElementById('fxSumDelivered').textContent = '৳' + sumDelivered.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 2});
    document.getElementById('fxSumPending').textContent = '৳' + sumPending.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 2});
  }

  // ── Render Leaflet Map Markers ────────────────────────────────
  function renderMapMarkers(orders) {
    // Clear old markers
    mapMarkers.forEach(m => leafletMap.removeLayer(m));
    mapMarkers = [];

    const bounds = [];

    orders.forEach((ord, index) => {
      let lat = ord.lat;
      let lng = ord.lng;

      // Jitter if null coordinates so they can still be viewed or default to center
      if (!lat || !lng) {
        lat = 23.8103 + (Math.sin(index) * 0.04);
        lng = 90.4125 + (Math.cos(index) * 0.04);
      }

      bounds.push([lat, lng]);

      const statusClass = 'marker-' + (ord.oc_status === 'checked_out' ? 'checked_out' : ord.order_status);
      const iconHtml = `<div class="custom-map-marker ${statusClass}"><i class="fa-solid fa-shop"></i></div>`;
      
      const customIcon = L.divIcon({
        html: iconHtml,
        className: 'custom-leaflet-icon',
        iconSize: [36, 36],
        iconAnchor: [18, 18]
      });

      const marker = L.marker([lat, lng], { icon: customIcon }).addTo(leafletMap);

      // Tooltip
      const tooltipContent = `
        <div style="font-size:12px;font-family:sans-serif;padding:2px;">
          <strong style="font-size:13px;">${escapeHtml(ord.retailer_name)}</strong><br>
          <span style="color:#64748b;">${escapeHtml(ord.order_no)} • ৳${ord.total_amount}</span><br>
          <span style="font-weight:bold;color:${ord.oc_status === 'checked_out' ? '#10b981' : '#f59e0b'};">
            ${ord.oc_status === 'checked_out' ? 'Checked Out' : 'Ordered (' + ord.order_status + ')'}
          </span>
        </div>
      `;
      marker.bindTooltip(tooltipContent, { direction: 'top', offset: [0, -10] });

      marker.on('click', () => {
        openOrderModal(ord.order_id);
      });

      mapMarkers.push(marker);
    });

    if (bounds.length > 0) {
      leafletMap.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 });
    }
  }

  function recenterMap() {
    if (mapMarkers.length > 0) {
      const group = L.featureGroup(mapMarkers);
      leafletMap.fitBounds(group.getBounds(), { padding: [50, 50] });
    } else {
      leafletMap.setView([23.8103, 90.4125], 12);
    }
  }

  // ── Render Card List ─────────────────────────────────────────
  function renderCardList(orders) {
    const grid = document.getElementById('cardsGrid');
    const emptyState = document.getElementById('emptyListState');
    grid.innerHTML = '';

    if (orders.length === 0) {
      emptyState.classList.remove('hidden');
      return;
    }
    emptyState.classList.add('hidden');

    orders.forEach(ord => {
      const card = document.createElement('div');
      card.className = "bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition flex flex-col justify-between space-y-4 group";

      const ocBadgeClass = ord.oc_status === 'checked_out' ? 'badge-checked_out' : 'badge-ordered';
      const ocText = ord.oc_status === 'checked_out' ? 'Checked Out' : 'Ordered';
      
      const orderBadgeClass = 'badge-' + ord.order_status;

      card.innerHTML = `
        <div>
          <div class="flex items-start justify-between gap-2 mb-2">
            <div>
              <h3 class="font-black text-gray-900 text-base group-hover:text-brand transition leading-tight">
                ${escapeHtml(ord.retailer_name)}
              </h3>
              <div class="text-xs text-gray-400 mt-0.5">
                <i class="fa-solid fa-phone text-blue-500 mr-1"></i>${escapeHtml(ord.phone)}
              </div>
            </div>
            <span class="badge-status ${ocBadgeClass}">${ocText}</span>
          </div>

          <div class="text-xs text-gray-500 mb-3 flex items-center gap-1.5 truncate">
            <i class="fa-solid fa-location-dot text-red-400"></i>
            <span class="truncate">${escapeHtml(ord.address)}</span>
          </div>

          <div class="bg-gray-50 p-2.5 rounded-xl border border-gray-100 text-[11px] grid grid-cols-2 gap-2 mb-3">
            <div><span class="text-gray-400">SR:</span> <span class="font-bold text-gray-700">${escapeHtml(ord.sr_name)}</span></div>
            <div><span class="text-gray-400">DSR:</span> <span class="font-bold text-gray-700">${escapeHtml(ord.dsr_name)}</span></div>
          </div>

          <div class="flex items-center justify-between text-xs pt-1">
            <div>
              <span class="text-gray-400">Items:</span> <span class="font-bold text-gray-900">${ord.items_count} (${ord.total_boxes}B ${ord.total_pieces}P)</span>
            </div>
            <span class="badge-status ${orderBadgeClass}">${ord.order_status}</span>
          </div>
        </div>

        <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
          <div>
            <div class="text-[10px] text-gray-400 uppercase font-bold">Total Amount</div>
            <div class="text-lg font-black text-emerald-600">৳${ord.total_amount.toLocaleString()}</div>
          </div>
          <button onclick="openOrderModal(${ord.order_id})" 
                  class="px-4 py-2 bg-blue-50 hover:bg-brand hover:text-white text-brand text-xs font-bold rounded-xl transition flex items-center gap-1.5">
            View Details <i class="fa-solid fa-chevron-right text-[10px]"></i>
          </button>
        </div>
      `;
      grid.appendChild(card);
    });
  }

  // ── Open Order Detail Popup Modal ────────────────────────────
  function openOrderModal(orderId) {
    const ord = fetchedOrders.find(o => o.order_id == orderId);
    if (!ord) return;

    document.getElementById('modalRetailerName').textContent = ord.retailer_name;
    document.getElementById('modalOrderNo').textContent = ord.order_no;
    document.getElementById('modalOrderDate').textContent = ord.order_date;
    document.getElementById('modalPhone').textContent = ord.phone;
    document.getElementById('modalAddress').textContent = ord.address;
    document.getElementById('modalSrName').textContent = ord.sr_name;
    document.getElementById('modalDsrName').textContent = ord.dsr_name;
    document.getElementById('modalWarehouseName').textContent = ord.warehouse_name;
    
    document.getElementById('modalTotalAmount').textContent = '৳' + ord.total_amount.toLocaleString();
    document.getElementById('modalTotalBoxes').textContent = ord.total_boxes;
    document.getElementById('modalTotalPieces').textContent = ord.total_pieces;
    document.getElementById('modalItemCount').textContent = ord.items_count + ' Items';

    // Order Status Badge
    const statusBadge = document.getElementById('modalOrderStatusBadge');
    statusBadge.textContent = ord.order_status.toUpperCase();
    statusBadge.className = `badge-status badge-${ord.order_status}`;

    // OC Status Badge
    const ocBadge = document.getElementById('modalOcStatusBadge');
    const isCheckedOut = ord.oc_status === 'checked_out';
    ocBadge.textContent = isCheckedOut ? 'CHECKED OUT' : 'ORDERED / OPEN';
    ocBadge.className = `badge-status ${isCheckedOut ? 'badge-checked_out' : 'badge-ordered'}`;

    // Notes
    const notesBox = document.getElementById('modalNotesBox');
    if (ord.notes && ord.notes.trim() !== '') {
      document.getElementById('modalNotesText').textContent = ord.notes;
      notesBox.classList.remove('hidden');
    } else {
      notesBox.classList.add('hidden');
    }

    // Render Products Table
    const tbody = document.getElementById('modalItemsTableBody');
    tbody.innerHTML = '';

    ord.items.forEach(item => {
      const tr = document.createElement('tr');
      tr.className = "hover:bg-gray-50/80 transition";

      const qtyStr = (item.boxes > 0 ? `${item.boxes} Box ` : '') + (item.pieces > 0 || item.boxes === 0 ? `${item.pieces} Pcs` : '');

      tr.innerHTML = `
        <td class="py-2.5 px-3">
          <div class="font-bold text-gray-900">${escapeHtml(item.product_name)}</div>
          <div class="text-[10px] text-gray-400 font-mono">${escapeHtml(item.sku)} • ${item.pieces_per_box} pcs/box</div>
        </td>
        <td class="py-2.5 px-3 text-center">
          <span class="bg-gray-100 text-gray-800 font-bold px-2 py-1 rounded text-[11px]">${qtyStr}</span>
        </td>
        <td class="py-2.5 px-3 text-right text-gray-600 font-mono">৳${item.unit_price}</td>
        <td class="py-2.5 px-3 text-right font-bold text-gray-900 font-mono">৳${item.total_price.toLocaleString()}</td>
      `;
      tbody.appendChild(tr);
    });

    const modal = document.getElementById('orderModal');
    modal.classList.remove('hidden');
  }

  function closeOrderModal() {
    const modal = document.getElementById('orderModal');
    modal.classList.add('hidden');
  }

  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }
</script>
