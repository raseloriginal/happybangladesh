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
    <div class="w-24 h-24 bg-blue-50 rounded-full flex items-center justify-center mb-6">
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
        <div class="text-white text-xs font-semibold opacity-80">Today's Deliveries</div>
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
          <div class="text-lg font-black text-brand" id="bsOrderTotal">৳0</div>
        </div>
        <div class="text-right">
          <div class="text-[10px] text-gray-500 font-bold uppercase">Status</div>
          <div class="text-sm font-bold text-blue-500" id="bsStatus">Pending</div>
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
      <div class="flex gap-3">
        <button onclick="closeBottomSheet()" class="flex-1 py-3 rounded-xl font-bold bg-gray-100 text-gray-600 active:bg-gray-200 transition">Cancel</button>
        <button onclick="markDelivery('partial')" class="flex-1 py-3 rounded-xl font-bold bg-orange-100 text-orange-600 active:bg-orange-200 transition">Partial/Due</button>
        <button onclick="markDelivery('delivered')" class="flex-1 py-3 rounded-xl font-bold bg-brand text-white active:scale-[0.98] transition shadow-lg shadow-blue-500/30">Complete</button>
      </div>

    </div>
  </div>

  <?php endif; // $hasDeliveries ?>

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
        let isDelivered = true;
        let isPending = false;
        let isPartial = false;
        
        ret.orders.forEach(o => {
            if (o.status === 'in_transit') isPending = true;
            if (o.status === 'partial') isPartial = true;
            if (o.status !== 'delivered') isDelivered = false;
        });

        let pinClass = 'pin-pending';
        let pinIcon = 'fa-clock';
        if (isPending) { pinClass = 'pin-pending'; pinIcon = 'fa-clock'; }
        else if (isPartial) { pinClass = 'pin-partial'; pinIcon = 'fa-circle-half-stroke'; }
        else if (isDelivered) { pinClass = 'pin-delivered'; pinIcon = 'fa-check'; }

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
        marker.on('click', () => openRetailerSheet(ret));
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
            console.log('Geolocation failed or denied.');
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

    if (retailer.orders && retailer.orders.length > 0) {
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

    const statusLabel = { 'in_transit': 'Pending Delivery', 'delivered': 'Delivered', 'partial': 'Partial/Due' };
    const statusColor = { 'in_transit': '#3b82f6', 'delivered': '#16a34a', 'partial': '#f97316' };
    const bsStatus = document.getElementById('bsStatus');
    bsStatus.innerText = statusLabel[order.status] || 'Pending';
    bsStatus.style.color = statusColor[order.status] || '#3b82f6';

    const list = document.getElementById('bsProductsList');
    list.innerHTML = '';

    if (!order.products || order.products.length === 0) {
        list.innerHTML = `<div class="text-center py-4 text-sm text-gray-400"><i class="fa-solid fa-box-open mb-2 text-xl"></i><br>No products found for this order.</div>`;
    } else {
        order.products.forEach((p, idx) => {
            const ppb = parseInt(p.pieces_per_box) || 1;
            const qty = parseInt(p.quantity); // pieces dispatched on van

            list.insertAdjacentHTML('beforeend', `
            <div class="bg-white rounded-xl border border-gray-100 p-3 shadow-sm">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center flex-shrink-0">
                        ${p.image
                            ? `<img src="<?= asset('uploads/products/') ?>${p.image}" class="w-full h-full object-cover">`
                            : `<i class="fa-solid fa-box text-gray-300"></i>`
                        }
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-bold text-gray-800 line-clamp-1">${p.name}</div>
                        <div class="text-[10px] text-gray-500">On Van: <span class="font-bold text-brand">${qty}</span> PCS &nbsp;·&nbsp; 1 Box = ${ppb} PCS</div>
                    </div>
                </div>

                <!-- Delivery Input -->
                <div class="flex gap-2 mb-2">
                    <div class="flex-1 bg-gray-50 rounded-lg p-2 border border-gray-200">
                        <div class="text-[9px] text-gray-400 font-bold uppercase mb-1">Boxes Delivered</div>
                        <input type="number" min="0" value="0"
                            class="w-full bg-transparent outline-none font-bold text-gray-700 delivery-input-box"
                            data-ppb="${ppb}" data-qty="${qty}" data-idx="${idx}"
                            oninput="calcProgress(this, ${idx})">
                    </div>
                    <div class="flex-1 bg-gray-50 rounded-lg p-2 border border-gray-200">
                        <div class="text-[9px] text-gray-400 font-bold uppercase mb-1">Extra PCS</div>
                        <input type="number" min="0" value="0"
                            class="w-full bg-transparent outline-none font-bold text-gray-700 delivery-input-pcs"
                            data-ppb="${ppb}" data-qty="${qty}" data-idx="${idx}"
                            oninput="calcProgress(this, ${idx})">
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="flex items-center justify-between text-[10px] font-bold mb-1">
                    <span class="text-gray-500">Delivered: <span id="delQty-${idx}">0</span> / ${qty} PCS</span>
                    <span id="delPercent-${idx}" class="text-brand">0%</span>
                </div>
                <div class="h-1.5 w-full bg-gray-100 rounded-full overflow-hidden">
                    <div id="delBar-${idx}" class="h-full bg-brand transition-all duration-300 w-0"></div>
                </div>
            </div>`);
        });
    }
}

function closeBottomSheet() {
    document.getElementById('bottomSheetOverlay').classList.remove('active');
    document.getElementById('retailerSheet').classList.remove('active');
    currentDispatchId = null;
}

function calcProgress(el, idx) {
    const parent = el.closest('.bg-white');
    const boxInput = parent.querySelector('.delivery-input-box');
    const pcsInput = parent.querySelector('.delivery-input-pcs');

    const boxes = parseInt(boxInput.value) || 0;
    const pcs   = parseInt(pcsInput.value) || 0;
    const ppb   = parseInt(boxInput.getAttribute('data-ppb')) || 1;
    const maxQty = parseInt(boxInput.getAttribute('data-qty')) || 1;

    const totalDelivered = (boxes * ppb) + pcs;

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
}

function markDelivery(status) {
    if (!currentDispatchId) return;

    const btn = event.currentTarget;
    btn.disabled = true;

    fetch('<?= url("dsr/delivery/update/") ?>' + currentDispatchId, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `csrf_token=<?= Helpers::csrfToken() ?>&status=${status}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Show brief success toast then reload
            showToast(status === 'delivered' ? '✅ Delivery marked complete!' : '🔶 Marked as Partial/Due');
            setTimeout(() => location.reload(), 900);
        } else {
            btn.disabled = false;
        }
    })
    .catch(() => { btn.disabled = false; });
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
