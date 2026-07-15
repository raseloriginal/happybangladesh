<?php $pageTitle = 'Sales'; ?>

<!-- ── Fullscreen Map Page ──────────────────────────────────── -->
<div class="sr-map-page">
  <div id="srMap"></div>

  <!-- Search Bar Overlay -->
  <div class="sr-map-searchbar">
    <i class="fa-solid fa-magnifying-glass sr-map-search-icon"></i>
    <input type="text" id="mapSearchInput" placeholder="Search area or retailer…">
    <button id="mapSearchBtn" style="background:none;border:none;cursor:pointer;color:var(--sr-primary);font-size:0.9rem;">
      <i class="fa-solid fa-arrow-right"></i>
    </button>
  </div>

  <!-- FAB Buttons -->
  <div class="sr-map-fabs">
    <button class="sr-map-fab sr-fab-locate" id="locateBtn" title="My Location">
      <i class="fa-solid fa-location-crosshairs"></i>
    </button>
    <button class="sr-map-fab sr-fab-add" id="addRetailerBtn" title="Add Retailer">
      <i class="fa-solid fa-plus"></i>
    </button>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     ADD RETAILER BOTTOM SHEET
══════════════════════════════════════════════════════════════ -->
<div class="sr-sheet-overlay" id="addRetOverlay"></div>
<div class="sr-bottom-sheet" id="addRetSheet">
  <div class="sr-sheet-handle"></div>
  <div class="sr-sheet-header">
    <span class="sr-sheet-title"><i class="fa-solid fa-store" style="color:var(--sr-primary);margin-right:8px;"></i>Add New Retailer</span>
    <button class="sr-sheet-close" id="addRetClose"><i class="fa-solid fa-xmark"></i></button>
  </div>
  <div class="sr-sheet-body">
    <form id="addRetailerForm">
      <div class="sr-form-group">
        <label class="sr-form-label">Shop / Retailer Name <span style="color:#ef4444;">*</span></label>
        <input type="text" class="sr-form-input" id="retName" placeholder="e.g. Ahmed General Store" required>
      </div>
      <div class="sr-form-group">
        <label class="sr-form-label">Phone Number</label>
        <input type="tel" class="sr-form-input" id="retPhone" placeholder="01XXXXXXXXX">
      </div>
      <div class="sr-form-group">
        <label class="sr-form-label">Location <span style="color:#ef4444;">*</span></label>
        <div class="sr-mini-map-wrap">
          <div id="srMiniMap"></div>
          <button type="button" class="sr-mini-map-fullscreen" id="miniMapFullscreenBtn" title="Fullscreen map">
            <i class="fa-solid fa-expand"></i>
          </button>
          <div class="sr-mini-map-hint">Drag map to pin location</div>
        </div>
        <div id="selectedLocText" style="font-size:0.72rem;color:var(--sr-text-muted);margin-top:6px;text-align:center;">
          <i class="fa-solid fa-location-dot" style="color:var(--sr-primary);"></i> Detecting location…
        </div>
      </div>
      <button type="submit" class="sr-add-cart-btn" style="margin-top:4px;">
        <i class="fa-solid fa-floppy-disk"></i> Save Retailer
      </button>
    </form>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     FULLSCREEN PIN MAP OVERLAY
══════════════════════════════════════════════════════════════ -->
<div class="sr-fullmap-overlay hidden" id="fullMapOverlay">
  <div id="srFullMap"></div>
  <div class="sr-fullmap-crosshair">
    <i class="fa-solid fa-location-dot"></i>
  </div>
  <div class="sr-fullmap-topbar">
    <button class="sr-fullmap-back" id="fullMapBack"><i class="fa-solid fa-arrow-left"></i></button>
    <span class="sr-fullmap-title">Pin Retailer Location</span>
    <button class="sr-fullmap-confirm" id="fullMapConfirm">Confirm</button>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     RETAILER DETAIL FULLSCREEN POPUP
══════════════════════════════════════════════════════════════ -->
<div class="sr-retailer-popup" id="retailerPopup">
  <div class="sr-retailer-topbar">
    <button class="sr-ret-back" id="retPopupBack"><i class="fa-solid fa-arrow-left"></i></button>
    <div class="sr-ret-name" id="retPopupName">—</div>
    <div class="sr-ret-phone" id="retPopupPhone"></div>
    <span class="sr-ret-dist" id="retPopupDist"><i class="fa-solid fa-location-dot"></i> —</span>
  </div>
  <div class="sr-products-section">
    <div class="sr-products-title">
      <i class="fa-solid fa-boxes-stacked" style="color:var(--sr-primary);margin-right:6px;"></i>Available Products
    </div>
    <div class="sr-products-grid" id="productsGrid">
      <!-- Populated by JS -->
    </div>
  </div>
  <div style="position:sticky; bottom:0; left:0; width:100%; padding:16px; background:#fff; border-top:1px solid var(--sr-border); display:flex; justify-content:space-between; align-items:center; box-shadow: 0 -4px 12px rgba(0,0,0,0.05); z-index:601;">
    <div id="popupCartInfo" style="font-weight:700; color:var(--sr-primary); font-size:1.1rem;">0 Items <span style="color:var(--sr-text-muted); font-size:0.85rem; margin-left:4px;">(৳0.00)</span></div>
    <button class="sr-cart-btn-add" style="padding: 10px 20px;" onclick="openRetailerCartSheet(currentRetailer)">Checkout</button>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     PRODUCT ORDER BOTTOM SHEET
══════════════════════════════════════════════════════════════ -->
<div class="sr-sheet-overlay" id="productSheetOverlay"></div>
<div class="sr-bottom-sheet" id="productSheet">
  <div class="sr-sheet-handle"></div>
  <div class="sr-sheet-header">
    <span class="sr-sheet-title" id="productSheetTitle">Product</span>
    <button class="sr-sheet-close" id="productSheetClose"><i class="fa-solid fa-xmark"></i></button>
  </div>
  <div class="sr-sheet-body">
    <div id="productSheetImgWrap"></div>
    <div class="sr-product-sheet-name" id="productSheetName"></div>
    <div class="sr-product-sheet-price" id="productSheetPriceInfo"></div>

    <div class="sr-divider"></div>

    <!-- Cartons -->
    <div class="sr-qty-row">
      <span class="sr-qty-label"><i class="fa-solid fa-box" style="color:var(--sr-primary);margin-right:4px;"></i>Cartons</span>
      <div class="sr-qty-controls">
        <button class="sr-qty-btn" onclick="changeQty('cartons',-1)">−</button>
        <input type="number" class="sr-qty-input" id="qtyCartons" value="0" min="0" oninput="calcTotal()">
        <button class="sr-qty-btn" onclick="changeQty('cartons',1)">+</button>
      </div>
    </div>

    <!-- Pieces -->
    <div class="sr-qty-row">
      <span class="sr-qty-label"><i class="fa-solid fa-cubes-stacked" style="color:var(--sr-accent);margin-right:4px;"></i>Pieces</span>
      <div class="sr-qty-controls">
        <button class="sr-qty-btn" onclick="changeQty('pieces',-1)">−</button>
        <input type="number" class="sr-qty-input" id="qtyPieces" value="0" min="0" oninput="calcTotal()">
        <button class="sr-qty-btn" onclick="changeQty('pieces',1)">+</button>
      </div>
    </div>

    <!-- Selling Price -->
    <div class="sr-price-row">
      <span class="sr-price-label"><i class="fa-solid fa-tag" style="color:var(--sr-success);margin-right:4px;"></i>Unit Price</span>
      <div class="sr-price-box" id="unitPriceDisplay">৳ 0.00</div>
      <input type="hidden" id="unitPrice" value="0">
    </div>

    <div class="sr-divider"></div>

    <!-- Total -->
    <div class="sr-price-row" style="margin-bottom:16px;">
      <span class="sr-price-label" style="font-size:1rem;"><strong>Total Amount</strong></span>
      <input type="number" class="sr-price-input" id="totalDisplayInput" value="0" min="0" step="0.01" oninput="calcUnitPriceFromTotal()" placeholder="Total">
    </div>

    <button class="sr-add-cart-btn" id="addToCartBtn" onclick="addToCart()">
      <i class="fa-solid fa-cart-plus"></i> Add to Cart
    </button>
  </div>
</div>



<!-- ══════════════════════════════════════════════════════════════
     RETAILER CART SHEET
══════════════════════════════════════════════════════════════ -->
<div class="sr-sheet-overlay" id="retCartOverlay"></div>
<div class="sr-bottom-sheet" id="retCartSheet">
  <div class="sr-sheet-handle"></div>
  <div class="sr-sheet-header">
    <span class="sr-sheet-title" id="retCartTitle">Cart</span>
    <button class="sr-sheet-close" onclick="closeSheet('retCartSheet','retCartOverlay')"><i class="fa-solid fa-xmark"></i></button>
  </div>
  <div class="sr-sheet-body">
    <div id="retCartItemsList" style="margin-bottom:14px; max-height: 40vh; overflow-y: auto;"></div>
    
    <div class="sr-divider"></div>
    <div class="sr-price-row" style="margin-bottom:16px;">
      <strong style="font-size:1rem;">Grand Total</strong>
      <div class="sr-price-box" id="retCartGrandTotal">৳ 0.00</div>
    </div>
    <div class="sr-form-group">
      <label class="sr-form-label">Notes (optional)</label>
      <input type="text" class="sr-form-input" id="retCartNotes" placeholder="Any delivery notes…">
    </div>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
      <button class="sr-cart-btn-cancel" onclick="closeSheet('retCartSheet','retCartOverlay')">Cancel</button>
      <button class="sr-cart-btn-add" onclick="openProductsForRetailer()"><i class="fa-solid fa-plus"></i> Add Product</button>
    </div>
    <button class="sr-add-cart-btn" id="retCartConfirmBtn" onclick="confirmRetailerCart()">
      <i class="fa-solid fa-paper-plane"></i> Confirm Order
    </button>
  </div>
</div>

<?php
// $allProducts is passed from SRController::sales()
// Fallback to empty array if not set
$allProducts = $allProducts ?? [];
?>

<script>
// ══════════════════════════════════════════════════════════════
// SR SALES PAGE — Full JS Logic
// ══════════════════════════════════════════════════════════════

const BASE_URL = '<?= BASE_URL ?>';
const SR_ID    = <?= Auth::id() ?>;
const ALL_PRODUCTS = <?= json_encode($allProducts, JSON_UNESCAPED_UNICODE) ?>;

// ── State ─────────────────────────────────────────────────────
let mainMap, miniMap, fullMap;
let myLat = 23.8103, myLng = 90.4125; // Default: Dhaka
let pinLat, pinLng;
let currentRetailer = null;
let currentProduct  = null;
let cartsByRetailer = {}; // Key: retailer ID, Value: array of items
let retailerMarkers = [];
let fullMapMarker   = null;
let myCircle        = null;
let isSubmitting    = false;

// ── Colour palette for product cards ──────────────────────────
const gradients = [
  'linear-gradient(135deg,#4f46e5,#6366f1)',
  'linear-gradient(135deg,#06b6d4,#0891b2)',
  'linear-gradient(135deg,#10b981,#059669)',
  'linear-gradient(135deg,#f59e0b,#d97706)',
  'linear-gradient(135deg,#8b5cf6,#7c3aed)',
  'linear-gradient(135deg,#ef4444,#dc2626)',
];
const emojis = ['📦','🛒','🏪','🎁','🧴','🍬','🧃','🍪'];

// ══════════════════════════════════════════════════════════════
// MAIN MAP INIT
// ══════════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => {
  initMainMap();
  initEventListeners();
});

window.addEventListener('beforeunload', function (e) {
  if (isSubmitting) return;
  const hasCarts = Object.values(cartsByRetailer).some(c => c.length > 0);
  if (hasCarts) {
    const msg = "You have items in your carts. If you leave, your carts will be lost.";
    e.returnValue = msg;
    return msg;
  }
});

function initMainMap() {
  mainMap = L.map('srMap', { zoomControl: false, attributionControl: false }).setView([myLat, myLng], 14);
  L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {maxZoom: 20, subdomains:['mt0','mt1','mt2','mt3']}).addTo(mainMap);
  L.control.zoom({ position: 'bottomleft' }).addTo(mainMap);
  detectLocation(false);
}

// ── Detect / Go-to My Location ────────────────────────────────
function detectLocation(animate = true) {
  if (!navigator.geolocation) return;
  navigator.geolocation.getCurrentPosition(pos => {
    myLat = pos.coords.latitude;
    myLng = pos.coords.longitude;
    if (animate) mainMap.flyTo([myLat, myLng], 16, { duration: 1.2 });
    else mainMap.setView([myLat, myLng], 16);
    placeMyLocationMarker();
    loadRetailersOnMap();
  }, () => {
    loadRetailersOnMap(); // still load with default
  });
}

function placeMyLocationMarker() {
  // Remove old
  if (window._myMarker) mainMap.removeLayer(window._myMarker);
  if (myCircle) mainMap.removeLayer(myCircle);

  const icon = L.divIcon({
    className: '',
    html: `<div style="width:18px;height:18px;border-radius:50%;background:#4f46e5;border:3px solid #fff;box-shadow:0 2px 8px rgba(79,70,229,.5);"></div>`,
    iconSize: [18, 18], iconAnchor: [9, 9]
  });
  window._myMarker = L.marker([myLat, myLng], { icon }).addTo(mainMap);

  // 1000m radius circle
  myCircle = L.circle([myLat, myLng], {
    radius: 1000,
    className: 'sr-radius-circle'
  }).addTo(mainMap);
}

// ══════════════════════════════════════════════════════════════
// RETAILERS ON MAP
// ══════════════════════════════════════════════════════════════
function loadRetailersOnMap() {
  fetch(`${BASE_URL}/sr/api/retailers?lat=${myLat}&lng=${myLng}&radius=1000`)
    .then(r => r.json())
    .then(data => {
      retailerMarkers.forEach(m => mainMap.removeLayer(m));
      retailerMarkers = [];
      (data.retailers || []).forEach(ret => addRetailerPin(ret));
    })
    .catch(() => {
      // Silently fail — show demo pins
      showDemoPins();
    });
}

function showDemoPins() {
  const demos = [
    { id: 1, name: 'Ahmed Store', phone: '01711000001', lat: myLat + 0.0004, lng: myLng + 0.0003, dist: 45 },
    { id: 2, name: 'Rahim Shop',  phone: '01711000002', lat: myLat - 0.0003, lng: myLng + 0.0005, dist: 67 },
    { id: 3, name: 'Karim Bhai', phone: '01711000003', lat: myLat + 0.0006, lng: myLng - 0.0004, dist: 83 },
  ];
  demos.forEach(ret => addRetailerPin(ret));
}

function updateAllPins() {
  // Re-render pins so they get the .has-cart class if needed
  retailerMarkers.forEach(m => mainMap.removeLayer(m.marker));
  const oldMarkers = retailerMarkers;
  retailerMarkers = [];
  oldMarkers.forEach(m => addRetailerPin(m.ret));
}

function addRetailerPin(ret) {
  const hasCart = cartsByRetailer[ret.id] && cartsByRetailer[ret.id].length > 0;
  const icon = L.divIcon({
    className: '',
    html: `<div class="sr-retailer-marker ${hasCart ? 'has-cart' : ''}"><i class="fa-solid fa-store"></i>${escHtml(ret.name)}</div>`,
    iconSize: [0, 0],
    iconAnchor: [0, 0]
  });
  const marker = L.marker([ret.lat, ret.lng], { icon }).addTo(mainMap);
  marker.on('click', () => {
    if (cartsByRetailer[ret.id] && cartsByRetailer[ret.id].length > 0) {
      openRetailerCartSheet(ret);
    } else {
      currentRetailer = ret;
      if (!cartsByRetailer[ret.id]) cartsByRetailer[ret.id] = [];
      openProductsForRetailer();
    }
  });
  retailerMarkers.push({ marker, ret });
}

function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// ══════════════════════════════════════════════════════════════
// RETAILER CART SHEET & PRODUCT LIST
// ══════════════════════════════════════════════════════════════

function openRetailerCartSheet(ret) {
  currentRetailer = ret;
  if (!cartsByRetailer[ret.id]) {
    cartsByRetailer[ret.id] = [];
  }
  
  document.getElementById('retCartTitle').innerHTML = `<i class="fa-solid fa-cart-shopping" style="color:var(--sr-primary);margin-right:8px;"></i>${escHtml(ret.name)}`;
  document.getElementById('retCartNotes').value = '';
  
  renderRetailerCart();
  openSheet('retCartSheet', 'retCartOverlay');
}

function renderRetailerCart() {
  const cart = cartsByRetailer[currentRetailer.id] || [];
  const list = document.getElementById('retCartItemsList');
  
  if (cart.length === 0) {
    list.innerHTML = `<div style="text-align:center;padding:24px;color:#94a3b8;">Cart is empty. Click "Add Product" to start.</div>`;
    document.getElementById('retCartGrandTotal').textContent = '৳ 0.00';
    return;
  }
  
  let totalVal = 0;
  list.innerHTML = cart.map((c, i) => {
    totalVal += c.total;
    // Calculate back boxes and pieces for display
    const pcsPerCarton = c.pcsPerCarton || 12;
    const boxes = Math.floor(c.qty / pcsPerCarton);
    const pcs = c.qty % pcsPerCarton;
    
    return `
    <div style="border: 1px solid var(--sr-border); border-radius: 12px; padding: 12px; margin-bottom: 12px; background: #fafafa;">
      <div style="font-weight: 600; color: var(--sr-text); margin-bottom: 8px;">${escHtml(c.name)}</div>
      
      <div style="display: flex; gap: 8px; margin-bottom: 8px;">
        <div style="flex: 1;">
          <label style="font-size:0.75rem; color:var(--sr-text-muted);">Cartons</label>
          <input type="number" value="${boxes}" min="0" class="sr-qty-input" style="width:100%; border:1px solid var(--sr-border); border-radius:8px;" oninput="updateCartItem(${i}, 'box', this.value)">
        </div>
        <div style="flex: 1;">
          <label style="font-size:0.75rem; color:var(--sr-text-muted);">Pieces</label>
          <input type="number" value="${pcs}" min="0" class="sr-qty-input" style="width:100%; border:1px solid var(--sr-border); border-radius:8px;" oninput="updateCartItem(${i}, 'pc', this.value)">
        </div>
      </div>
      
      <div style="display: flex; gap: 8px; align-items: center;">
        <div style="flex: 1;">
          <label style="font-size:0.75rem; color:var(--sr-text-muted);">Total ৳</label>
          <input type="number" value="${c.total.toFixed(2)}" min="0" step="0.01" class="sr-qty-input" style="width:100%; border:1px solid var(--sr-border); border-radius:8px; text-align:left; padding-left:8px;" oninput="updateCartItem(${i}, 'total', this.value)">
        </div>
        <button onclick="removeCartItem(${i})" style="background: #fee2e2; color: #ef4444; border: none; width: 36px; height: 36px; border-radius: 8px; cursor: pointer; margin-top:16px;"><i class="fa-solid fa-trash"></i></button>
      </div>
    </div>`;
  }).join('');
  
  document.getElementById('retCartGrandTotal').textContent = '৳ ' + totalVal.toFixed(2);
}

function updateCartItem(index, type, value) {
  const cart = cartsByRetailer[currentRetailer.id];
  const item = cart[index];
  const val = parseFloat(value) || 0;
  
  const pcsPerCarton = item.pcsPerCarton || 12;
  let currentBoxes = Math.floor(item.qty / pcsPerCarton);
  let currentPcs = item.qty % pcsPerCarton;
  
  if (type === 'box') {
    currentBoxes = Math.max(0, parseInt(val));
    item.qty = currentBoxes * pcsPerCarton + currentPcs;
    item.total = item.qty * item.price;
  } else if (type === 'pc') {
    currentPcs = Math.max(0, parseInt(val));
    item.qty = currentBoxes * pcsPerCarton + currentPcs;
    item.total = item.qty * item.price;
  } else if (type === 'total') {
    item.total = Math.max(0, val);
    if (item.qty > 0) item.price = item.total / item.qty;
  }
  
  renderRetailerCart();
}

function removeCartItem(index) {
  cartsByRetailer[currentRetailer.id].splice(index, 1);
  renderRetailerCart();
  updateAllPins();
}

function openProductsForRetailer() {
  closeSheet('retCartSheet', 'retCartOverlay');
  
  document.getElementById('retPopupName').textContent  = currentRetailer.name;
  document.getElementById('retPopupPhone').textContent = currentRetailer.phone || '';
  document.getElementById('retPopupDist').innerHTML =
    `<i class="fa-solid fa-location-dot"></i> ${currentRetailer.dist ? currentRetailer.dist + 'm away' : 'Nearby'}`;

  renderProductsGrid();
  updatePopupCartInfo();
  document.getElementById('retailerPopup').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function updatePopupCartInfo() {
  const cart = cartsByRetailer[currentRetailer.id] || [];
  const totalItems = cart.length; // Or you can sum up qty if you want total pieces
  const totalVal = cart.reduce((sum, item) => sum + item.total, 0);
  
  const infoDiv = document.getElementById('popupCartInfo');
  if (infoDiv) {
    infoDiv.innerHTML = `${totalItems} Item${totalItems !== 1 ? 's' : ''} <span style="color:var(--sr-text-muted); font-size:0.85rem; margin-left:4px;">(৳${totalVal.toFixed(2)})</span>`;
  }
}

function renderProductsGrid() {
  const grid = document.getElementById('productsGrid');
  if (!ALL_PRODUCTS.length) {
    grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:24px;color:#94a3b8;">No products available.</div>`;
    return;
  }
  grid.innerHTML = ALL_PRODUCTS.map((p, i) => {
    const grad  = gradients[i % gradients.length];
    const emoji = emojis[i % emojis.length];
    const imgHtml = p.image
      ? `<img src="${BASE_URL}/public/${escHtml(p.image)}" class="sr-product-img" alt="${escHtml(p.name)}">`
      : `<div class="sr-product-img-placeholder" style="background:${grad};">${emoji}</div>`;

    const ppb = parseInt(p.pieces_per_carton || p.pieces_per_box || 1);
    const totalPcs = parseInt(p.stock || 0);
    const boxes = Math.floor(totalPcs / ppb);
    const pieces = totalPcs % ppb;
    const stockStr = `${boxes} Box, ${pieces} Pcs`;

    return `
    <div class="sr-product-card" onclick="openProductSheet(${i})">
      ${imgHtml}
      <div class="sr-product-info">
        <div class="sr-product-name">${escHtml(p.name)}</div>
        <div class="sr-product-stock"><i class="fa-solid fa-cubes" style="color:#94a3b8;margin-right:3px;font-size:0.65rem;"></i>Stock: ${stockStr}</div>
        <div class="sr-product-price">৳ ${parseFloat(p.selling_price || p.price || 0).toFixed(2)}</div>
      </div>
    </div>`;
  }).join('');
}

// ══════════════════════════════════════════════════════════════
// PRODUCT BOTTOM SHEET
// ══════════════════════════════════════════════════════════════
function openProductSheet(idx) {
  currentProduct = ALL_PRODUCTS[idx];
  const p = currentProduct;
  const grad  = gradients[idx % gradients.length];
  const emoji = emojis[idx % emojis.length];

  const ppb = parseInt(p.pieces_per_carton || p.pieces_per_box || 1);
  const totalPcs = parseInt(p.stock || 0);
  const boxes = Math.floor(totalPcs / ppb);
  const pieces = totalPcs % ppb;
  const stockStr = `${boxes} Box, ${pieces} Pcs`;

  document.getElementById('productSheetTitle').textContent = p.name;
  document.getElementById('productSheetName').textContent  = p.name;
  document.getElementById('productSheetPriceInfo').textContent =
    `${p.company_name || ''} · Stock: ${stockStr}`;

  const imgWrap = document.getElementById('productSheetImgWrap');
  if (p.image) {
    imgWrap.innerHTML = `<img src="${BASE_URL}/public/${escHtml(p.image)}" class="sr-product-sheet-img" alt="${escHtml(p.name)}">`;
  } else {
    imgWrap.innerHTML = `<div class="sr-product-sheet-placeholder" style="background:${grad};">${emoji}</div>`;
  }

  document.getElementById('qtyCartons').value = 0;
  document.getElementById('qtyPieces').value  = 0;
  const basePrice = parseFloat(p.selling_price || p.price || 0).toFixed(2);
  document.getElementById('unitPrice').value = basePrice;
  document.getElementById('unitPriceDisplay').textContent = '৳ ' + basePrice;
  calcTotal();

  openSheet('productSheet','productSheetOverlay');
}

function changeQty(type, delta) {
  const el = document.getElementById(type === 'cartons' ? 'qtyCartons' : 'qtyPieces');
  el.value = Math.max(0, parseInt(el.value || 0) + delta);
  calcTotal();
}

function calcTotal() {
  const cartons = parseInt(document.getElementById('qtyCartons').value) || 0;
  const pieces  = parseInt(document.getElementById('qtyPieces').value)  || 0;
  const price   = parseFloat(document.getElementById('unitPrice').value) || 0;
  const pcsPerCarton = currentProduct?.pieces_per_carton || 12;
  const totalPcs = cartons * pcsPerCarton + pieces;
  const total = totalPcs * price;
  document.getElementById('totalDisplayInput').value = total.toFixed(2);
}

function calcUnitPriceFromTotal() {
  const cartons = parseInt(document.getElementById('qtyCartons').value) || 0;
  const pieces  = parseInt(document.getElementById('qtyPieces').value)  || 0;
  const total   = parseFloat(document.getElementById('totalDisplayInput').value) || 0;
  const pcsPerCarton = currentProduct?.pieces_per_carton || 12;
  const totalPcs = cartons * pcsPerCarton + pieces;
  
  if (totalPcs > 0) {
    const price = total / totalPcs;
    document.getElementById('unitPrice').value = price.toFixed(2);
    document.getElementById('unitPriceDisplay').textContent = '৳ ' + price.toFixed(2);
  }
}

function addToCart() {
  const p = currentProduct;
  const cartons = parseInt(document.getElementById('qtyCartons').value) || 0;
  const pieces  = parseInt(document.getElementById('qtyPieces').value)  || 0;
  const total   = parseFloat(document.getElementById('totalDisplayInput').value) || 0;
  const pcsPerCarton = p?.pieces_per_carton || 12;
  const totalPcs = cartons * pcsPerCarton + pieces;

  if (totalPcs <= 0) { shakeElement('addToCartBtn'); return; }

  const price = total / totalPcs;
  const cart = cartsByRetailer[currentRetailer.id];
  
  const existing = cart.find(c => c.id === p.id);
  if (existing) {
    existing.qty   += totalPcs;
    existing.total += total;
    existing.price = existing.total / existing.qty;
  } else {
    cart.push({ id: p.id, name: p.name, qty: totalPcs, price, total, pcsPerCarton });
  }

  closeSheet('productSheet','productSheetOverlay');
  
  updateAllPins(); // Ensure pin turns yellow
  updatePopupCartInfo(); // Update the bottom bar in the popup
  
  showMiniToast(`✓ ${p.name} added to cart`);
}

// ── Checkout / Confirm Order ──────────────────────────────────
function confirmRetailerCart() {
  const cart = cartsByRetailer[currentRetailer.id] || [];
  if (!cart.length) {
    shakeElement('retCartConfirmBtn');
    showMiniToast('Cart is empty!');
    return; 
  }
  
  const notes = document.getElementById('retCartNotes').value;

  const form = document.createElement('form');
  form.method = 'POST';
  form.action = `${BASE_URL}/sr/orders/store`;

  const csrf = document.querySelector('meta[name="csrf"]');
  if (csrf) addInput(form, '_csrf', csrf.content);

  // You may need to pass retailer ID if the backend supports it, otherwise it relies on session/default
  addInput(form, 'retailer_id', currentRetailer.id);
  addInput(form, 'notes', notes);
  
  cart.forEach((c, i) => {
    addInput(form, `product_id[${i}]`, c.id);
    addInput(form, `quantity[${i}]`, c.qty);
    addInput(form, `unit_price[${i}]`, c.price);
  });

  isSubmitting = true;
  document.body.appendChild(form);
  form.submit();
}

function addInput(form, name, value) {
  const el = document.createElement('input');
  el.type  = 'hidden';
  el.name  = name;
  el.value = value;
  form.appendChild(el);
}

// ══════════════════════════════════════════════════════════════
// ADD RETAILER
// ══════════════════════════════════════════════════════════════
let miniMapInitialized = false;
pinLat = myLat; pinLng = myLng;

function openAddRetailerSheet() {
  openSheet('addRetSheet','addRetOverlay');
  setTimeout(() => {
    if (!miniMapInitialized) {
      miniMap = L.map('srMiniMap', { zoomControl: false, attributionControl: false })
        .setView([myLat, myLng], 15);
      L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {maxZoom: 20, subdomains:['mt0','mt1','mt2','mt3']}).addTo(miniMap);
      miniMapInitialized = true;
    } else {
      miniMap.setView([myLat, myLng], 15);
    }
    miniMap.invalidateSize();
    updatePinFromMiniMap();
    miniMap.on('move', updatePinFromMiniMap);
  }, 350);
}

function updatePinFromMiniMap() {
  const c = miniMap.getCenter();
  pinLat = c.lat; pinLng = c.lng;
  document.getElementById('selectedLocText').innerHTML =
    `<i class="fa-solid fa-location-dot" style="color:var(--sr-primary);"></i> ${pinLat.toFixed(5)}, ${pinLng.toFixed(5)}`;
}

// Fullscreen map for pin
let fullMapInitialized = false;
function openFullMap() {
  document.getElementById('fullMapOverlay').classList.remove('hidden');
  setTimeout(() => {
    if (!fullMapInitialized) {
      fullMap = L.map('srFullMap', { zoomControl: true, attributionControl: false })
        .setView([pinLat, pinLng], 16);
      L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {maxZoom: 20, subdomains:['mt0','mt1','mt2','mt3']}).addTo(fullMap);
      fullMapInitialized = true;
    } else {
      fullMap.setView([pinLat, pinLng], 16);
    }
    fullMap.invalidateSize();
  }, 100);
}

function confirmFullMap() {
  const c = fullMap.getCenter();
  pinLat = c.lat; pinLng = c.lng;
  // Sync to mini map
  if (miniMapInitialized) miniMap.setView([pinLat, pinLng], 15);
  updatePinFromMiniMap();
  document.getElementById('fullMapOverlay').classList.add('hidden');
}

// Save retailer
document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('addRetailerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const name  = document.getElementById('retName').value.trim();
    const phone = document.getElementById('retPhone').value.trim();
    if (!name) return;

    fetch(`${BASE_URL}/sr/api/retailers/store`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name, phone, lat: pinLat, lng: pinLng })
    })
    .then(r => r.json())
    .then(d => {
      if (d.success) {
        closeSheet('addRetSheet','addRetOverlay');
        document.getElementById('addRetailerForm').reset();
        loadRetailersOnMap();
        showMiniToast(`✓ Retailer "${name}" added!`);
      } else {
        showMiniToast('❌ ' + (d.message || 'Failed to save'), true);
      }
    })
    .catch(() => showMiniToast('❌ Network error', true));
  });
});

// ══════════════════════════════════════════════════════════════
// EVENT LISTENERS
// ══════════════════════════════════════════════════════════════
function initEventListeners() {
  document.getElementById('locateBtn').addEventListener('click', () => detectLocation(true));
  document.getElementById('addRetailerBtn').addEventListener('click', openAddRetailerSheet);
  document.getElementById('addRetOverlay').addEventListener('click', () => closeSheet('addRetSheet','addRetOverlay'));
  document.getElementById('addRetClose').addEventListener('click', () => closeSheet('addRetSheet','addRetOverlay'));
  document.getElementById('miniMapFullscreenBtn').addEventListener('click', openFullMap);
  document.getElementById('fullMapBack').addEventListener('click', () => document.getElementById('fullMapOverlay').classList.add('hidden'));
  document.getElementById('fullMapConfirm').addEventListener('click', confirmFullMap);
  document.getElementById('retPopupBack').addEventListener('click', () => {
    document.getElementById('retailerPopup').classList.remove('open');
    document.body.style.overflow = '';
  });
  document.getElementById('productSheetClose').addEventListener('click', () => closeSheet('productSheet','productSheetOverlay'));
  document.getElementById('productSheetOverlay').addEventListener('click', () => closeSheet('productSheet','productSheetOverlay'));

  // Search
  document.getElementById('mapSearchBtn').addEventListener('click', doMapSearch);
  document.getElementById('mapSearchInput').addEventListener('keypress', e => { if(e.key==='Enter') doMapSearch(); });
}

function doMapSearch() {
  const q = document.getElementById('mapSearchInput').value.trim();
  if (!q) return;
  fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(q)}&format=json&limit=1`)
    .then(r => r.json())
    .then(d => {
      if (d.length) {
        mainMap.flyTo([d[0].lat, d[0].lon], 15, { duration: 1 });
      }
    });
}

// ══════════════════════════════════════════════════════════════
// SHEET HELPERS
// ══════════════════════════════════════════════════════════════
function openSheet(sheetId, overlayId) {
  document.getElementById(overlayId).classList.add('open');
  document.getElementById(sheetId).classList.add('open');
}
function closeSheet(sheetId, overlayId) {
  document.getElementById(overlayId).classList.remove('open');
  document.getElementById(sheetId).classList.remove('open');
}

function shakeElement(id) {
  const el = document.getElementById(id);
  el.style.animation = 'none';
  el.offsetHeight;
  el.style.animation = 'shake 0.3s ease';
  setTimeout(() => el.style.animation = '', 400);
}

function showMiniToast(msg, isError = false) {
  const t = document.createElement('div');
  t.className = 'sr-flash sr-flash-' + (isError ? 'error' : 'success');
  t.style.cssText = 'position:fixed;top:20px;left:50%;transform:translateX(-50%);z-index:2000;transition:opacity 0.4s;';
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(() => { t.style.opacity='0'; setTimeout(()=>t.remove(),400); }, 2500);
}
</script>

<style>
@keyframes shake {
  0%,100%{transform:translateX(0)}
  25%{transform:translateX(-6px)}
  75%{transform:translateX(6px)}
}
</style>
