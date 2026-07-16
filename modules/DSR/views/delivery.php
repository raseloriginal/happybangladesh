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
        You have successfully collected your products, but there are no retailer deliveries assigned to your route today.<br>
        You can proceed with Ready Sales if you have van stock.
      </p>
    <?php else: ?>
      <p class="text-sm text-gray-500 leading-relaxed mb-6">
        No deliveries are loaded on your van today.<br>
        Please complete the <strong>Collection</strong> step first to load products onto your van.
      </p>
      <a href="<?= url('dsr/collection') ?>" class="inline-flex items-center gap-2 px-6 py-3 bg-brand text-white font-bold rounded-2xl shadow-lg shadow-blue-500/30 active:scale-95 transition">
        <i class="fa-solid fa-boxes-stacked"></i> Go to Collection
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
    <div class="bottom-sheet-content">

      <!-- Retailer Info -->
      <div class="flex items-center gap-3 mb-4">
        <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xl flex-shrink-0">
          <i class="fa-solid fa-store"></i>
        </div>
        <div class="flex-1 min-w-0">
          <h2 class="text-lg font-bold text-gray-800 truncate" id="bsRetailerName">Retailer Name</h2>
          <p class="text-xs text-gray-500 truncate" id="bsRetailerAddress">Address details</p>
        </div>
      </div>

      <!-- Company Tabs Container -->
      <div id="bsCompanyTabs" class="flex gap-2 mb-4 overflow-x-auto pb-1 no-scrollbar hidden">
          <!-- JS will populate company tabs here -->
      </div>

      <!-- Order Summary -->
      <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100 mb-4 flex justify-between items-center">
        <div>
          <div class="text-[10px] text-gray-500 font-bold uppercase">Order Value</div>
          <div class="text-lg font-black text-green-600" id="bsOrderTotal">৳0</div>
          <div class="text-[10px] text-gray-500 font-bold uppercase mt-1">Getting Value</div>
          <div class="text-lg font-black text-brand" id="bsGettingTotal">৳0</div>
        </div>
        <div class="text-right">
          <div class="text-[10px] text-gray-500 font-bold uppercase">Status</div>
          <div class="text-sm font-bold text-blue-500" id="bsStatus">Pending</div>
          <div id="bsPartialInfo" class="mt-1 text-right text-[11px] hidden leading-tight">
            <div class="text-gray-500">Paid: <span class="font-bold text-green-600" id="bsPaidAmount">৳0.00</span></div>
            <div class="text-gray-500">Due: <span class="font-bold text-red-600" id="bsDueAmount">৳0.00</span></div>
          </div>
        </div>
      </div>

      <!-- Products List (from dispatch_items — what's on the van) -->
      <div class="mb-4">
        <h3 class="text-sm font-bold text-gray-700 mb-2 flex items-center gap-2">
          <i class="fa-solid fa-boxes-stacked text-brand text-xs"></i>
          Products on Van
        </h3>
        <div id="bsProductsList" class="space-y-3 max-h-[35vh] overflow-y-auto pr-1">
          <!-- JS will populate this -->
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-col gap-2">
        <div class="flex gap-3">
          <button onclick="markDelivery('cancelled')" class="flex-1 py-3 rounded-xl font-bold bg-red-100 text-red-600 active:bg-red-200 transition text-sm">Cancel</button>
          <button onclick="markDelivery('partial')" class="flex-1 py-3 rounded-xl font-bold bg-orange-100 text-orange-600 active:bg-orange-200 transition text-sm">Partial/Due</button>
          <button onclick="markDelivery('delivered')" class="flex-1 py-3 rounded-xl font-bold bg-brand text-white active:scale-[0.98] transition shadow-lg shadow-blue-500/30 text-sm">Complete</button>
        </div>
        <button onclick="saveQuantitiesOnly()" class="w-full py-3 rounded-xl font-bold bg-emerald-600 text-white active:scale-[0.98] transition shadow-lg shadow-emerald-500/30 text-sm flex items-center justify-center gap-2"><i class="fa-solid fa-floppy-disk"></i> Save Quantities</button>
      </div>

    </div>
  </div>

  <?php endif; // $hasDeliveries ?>

  <!-- ══════════════════════════════════════════════════════
       CUSTOM MODALS
  ═══════════════════════════════════════════════════════ -->
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

</div><!-- /page root -->

<script>
// ── Data from PHP ────────────────────────────────────────────
const orderedRetailers = <?= json_encode($retailers) ?>;

let map, userMarker, radiusCircle = null;
let currentDispatchId = null;
let markers = [];

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
            /* Orange — partial */
            .pin-partial .map-pin-card {
                background: linear-gradient(135deg, #b45309 0%, #d97706 60%, #f59e0b 100%);
                color: #fff;
            }
            .pin-partial .map-pin-tail { border-top: 9px solid #b45309; }
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
            if (shouldWarn) {
                showConfirmPopup("This delivery was already processed. Do you want to redo/modify it?", () => {
                    openRetailerSheet(ret);
                });
            } else {
                openRetailerSheet(ret);
            }
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

function openRetailerSheet(retailer) {
    currentRetailerObj = retailer;
    document.getElementById('bsRetailerName').innerText = retailer.dealer_name || retailer.name;
    document.getElementById('bsRetailerAddress').innerText = retailer.address || 'No address provided';

    const tabsContainer = document.getElementById('bsCompanyTabs');
    tabsContainer.innerHTML = '';
    
    const list = document.getElementById('bsProductsList');
    list.innerHTML = '';
    
    if (retailer.orders && retailer.orders.length > 1) {
        tabsContainer.classList.remove('hidden');
        retailer.orders.forEach((order, idx) => {
            const isSelected = idx === 0;
            tabsContainer.insertAdjacentHTML('beforeend', `
                <button onclick="selectCompanyOrder(${idx})" id="tab-order-${idx}"
                        class="whitespace-nowrap px-4 py-2 rounded-full text-xs font-bold transition border ${isSelected ? 'bg-brand text-white border-brand' : 'bg-white text-gray-600 border-gray-200 active:bg-gray-50'}">
                    ${order.company_name}
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

                    let initialDeliveredQty = p.delivered_quantity !== null ? parseInt(p.delivered_quantity) : 0;
                    if (p.delivered_quantity === null && order.status === 'delivered') {
                        initialDeliveredQty = qty;
                    }

                    const initialBoxes = Math.floor(initialDeliveredQty / ppb);
                    const initialPcs = initialDeliveredQty % ppb;
                    const percent = Math.round((initialDeliveredQty / qty) * 100);
                    let barColorClass = 'bg-brand';
                    if (percent >= 100) barColorClass = 'bg-green-500';
                    else if (percent > 0) barColorClass = 'bg-orange-400';

                    orderHtml += `
                    <div class="bg-white rounded-xl border border-gray-100 p-3 shadow-sm product-item" data-price="${p.price || 0}">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center flex-shrink-0">
                                ${p.image
                                    ? `<img src="<?= asset('uploads/products/') ?>${p.image}" class="w-full h-full object-cover">`
                                    : `<i class="fa-solid fa-box text-gray-300"></i>`
                                }
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-bold text-gray-800 line-clamp-1">${p.name}</div>
                                <div class="text-[10px] text-gray-500">On Van: <span class="font-bold text-brand">${qty}</span> PCS &nbsp;·&nbsp; 1 Box = ${ppb} PCS &nbsp;·&nbsp; ৳${p.price}</div>
                            </div>
                        </div>

                        <!-- Delivery Input -->
                        <div class="flex gap-2 mb-2">
                            <div class="flex-1 bg-gray-50 rounded-lg p-2 border border-gray-200">
                                <div class="text-[9px] text-gray-400 font-bold uppercase mb-1">Boxes Delivered</div>
                                <input type="number" min="0" value="${initialBoxes}"
                                    class="w-full bg-transparent outline-none font-bold text-gray-700 delivery-input-box"
                                    data-ppb="${ppb}" data-qty="${qty}" data-idx="${orderIdx}-${idx}" data-pid="${p.product_id}" data-price="${p.price || 0}"
                                    oninput="calcProgress(this, '${orderIdx}-${idx}')">
                            </div>
                            <div class="flex-1 bg-gray-50 rounded-lg p-2 border border-gray-200">
                                <div class="text-[9px] text-gray-400 font-bold uppercase mb-1">Extra PCS</div>
                                <input type="number" min="0" value="${initialPcs}"
                                    class="w-full bg-transparent outline-none font-bold text-gray-700 delivery-input-pcs"
                                    data-ppb="${ppb}" data-qty="${qty}" data-idx="${orderIdx}-${idx}" data-pid="${p.product_id}" data-price="${p.price || 0}"
                                    oninput="calcProgress(this, '${orderIdx}-${idx}')">
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="flex items-center justify-between text-[10px] font-bold mb-1">
                            <span class="text-gray-500">Delivered: <span id="delQty-${orderIdx}-${idx}">${initialDeliveredQty}</span> / ${qty} PCS</span>
                            <span id="delPercent-${orderIdx}-${idx}" class="text-brand">${percent}%</span>
                        </div>
                        <div class="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                            <div id="delBar-${orderIdx}-${idx}" class="h-full transition-all duration-300 ${barColorClass}" style="width: ${percent}%"></div>
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

    // Update tabs visual state
    if (currentRetailerObj.orders.length > 1) {
        document.querySelectorAll('[id^="tab-order-"]').forEach(btn => {
            btn.className = 'whitespace-nowrap px-4 py-2 rounded-full text-xs font-bold transition border bg-white text-gray-600 border-gray-200 active:bg-gray-50';
        });
        const activeBtn = document.getElementById(`tab-order-${orderIndex}`);
        if (activeBtn) activeBtn.className = 'whitespace-nowrap px-4 py-2 rounded-full text-xs font-bold transition border bg-brand text-white border-brand';
    }

    document.getElementById('bsOrderTotal').innerText = '৳' + parseFloat(order.total_amount || 0).toFixed(2);

    const statusLabel = { 'in_transit': 'Pending Delivery', 'delivered': 'Delivered', 'partial': 'Partial/Due', 'cancelled': 'Cancelled' };
    const statusColor = { 'in_transit': '#3b82f6', 'delivered': '#16a34a', 'partial': '#f97316', 'cancelled': '#dc2626' };
    const bsStatus = document.getElementById('bsStatus');
    bsStatus.innerText = statusLabel[order.status] || 'Pending';
    bsStatus.style.color = statusColor[order.status] || '#3b82f6';

    const bsPartialInfo = document.getElementById('bsPartialInfo');
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

    // Toggle visibility
    document.querySelectorAll('.order-group-container').forEach(div => div.classList.add('hidden'));
    const activeDiv = document.getElementById(`order-group-${orderIndex}`);
    if (activeDiv) activeDiv.classList.remove('hidden');

    // Trigger initial calculation for this group
    const firstInput = activeDiv.querySelector('.delivery-input-box');
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

    document.getElementById(`delQty-${idx}`).innerText = totalDelivered;

    let percent = (totalDelivered / maxQty) * 100;
    if (percent > 100) percent = 100;

    document.getElementById(`delPercent-${idx}`).innerText = Math.round(percent) + '%';

    const bar = document.getElementById(`delBar-${idx}`);
    bar.style.width = percent + '%';
    if (percent >= 100) {
        bar.className = 'h-full transition-all duration-300 bg-green-500';
    } else if (percent > 0) {
        bar.className = 'h-full transition-all duration-300 bg-orange-400';
    } else {
        bar.className = 'h-full transition-all duration-300 bg-brand';
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
    
    document.getElementById('bsGettingTotal').innerText = '৳' + gettingTotal.toFixed(2);

    // Update due if partial info is visible
    const bsPartialInfo = document.getElementById('bsPartialInfo');
    if (!bsPartialInfo.classList.contains('hidden') && currentRetailerObj && currentRetailerObj.orders) {
        const order = currentRetailerObj.orders.find(o => o.dispatch_id === currentDispatchId);
        if (order) {
            const paid = parseFloat(order.paid_amount || 0);
            let due = 0;
            if (anyInputFilled) {
                due = gettingTotal - paid;
            } else {
                due = parseFloat(order.total_amount || 0) - paid;
            }
            document.getElementById('bsDueAmount').innerText = '৳' + (due > 0 ? due : 0).toFixed(2);
        }
    }
}

function markDelivery(status) {
    if (!currentRetailerObj || !currentRetailerObj.orders) return;

    if (status === 'delivered') {
        if (currentRetailerObj.orders.length > 1) {
            showMultiCompletePopup(currentRetailerObj.orders);
        } else {
            showConfirmPopup("Are you sure you want to mark this delivery as Complete?", () => {
                const targetDispatchIds = [currentRetailerObj.orders[0].dispatch_id];
                submitSelectedDeliveries(status, targetDispatchIds);
            });
        }
    } else if (status === 'cancelled') {
        if (currentRetailerObj.orders.length > 1) {
            showMultiCancelPopup(currentRetailerObj.orders);
        } else {
            showConfirmPopup("Are you sure you want to cancel this order?", () => {
                const targetDispatchIds = [currentRetailerObj.orders[0].dispatch_id];
                submitSelectedDeliveries(status, targetDispatchIds);
            });
        }
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

async function submitSelectedDeliveries(status, targetDispatchIds, paidAmounts = {}) {
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

        let msg = '✅ Deliveries processed!';
        if (status === 'partial') msg = '🔶 Marked as Partial/Due';
        if (status === 'cancelled') msg = '❌ Orders Cancelled';
        showToast(msg);
        setTimeout(() => location.reload(), 900);

    } catch (err) {
        showToast('❌ ' + (err.message || 'An error occurred.'));
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

<?php endif; // $hasDeliveries ?>
</script>

<style>
@keyframes fadeInUp {
    from { opacity: 0; transform: translate(-50%, 12px); }
    to   { opacity: 1; transform: translate(-50%, 0); }
}
</style>
