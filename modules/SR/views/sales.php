<?php $pageTitle = 'Sales'; ?>

<style>
  /* Prevent Flash of Unstyled Content (FOUC) for overlays on page load */
  .sr-sheet-overlay,
  .sr-bottom-sheet,
  .sr-bottom-sheet-v2,
  .sr-fullmap-overlay,
  .sr-modal-overlay,
  .sr-retailer-popup-v2,
  .sr-success-overlay-v2 {
    visibility: hidden;
  }
  
  .sr-sheet-overlay.open,
  .sr-bottom-sheet.open,
  .sr-bottom-sheet-v2.open,
  .sr-modal-overlay.open,
  .sr-retailer-popup-v2.open,
  .sr-success-overlay-v2.open {
    visibility: visible !important;
  }
  
  .sr-fullmap-overlay:not(.hidden) {
    visibility: visible !important;
  }

  /* Custom Confirm Modal Styling - moved to top to prevent FOUC */
  .sr-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 3000;
    opacity: 0;
    transition: opacity 0.25s ease;
  }
  .sr-modal-overlay.open {
    opacity: 1;
  }
  .sr-confirm-modal {
    background: #ffffff;
    border-radius: 16px;
    width: 90%;
    max-width: 380px;
    padding: 24px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    transform: translateY(20px);
    transition: transform 0.25s ease;
  }
  .sr-modal-overlay.open .sr-confirm-modal {
    transform: translateY(0);
  }
  .sr-confirm-title {
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--sr-text);
    margin-bottom: 12px;
    display: flex;
    align-items: center;
  }
  .sr-confirm-body {
    font-size: 0.9rem;
    color: var(--sr-text-muted);
    line-height: 1.5;
    margin-bottom: 24px;
  }
  .sr-confirm-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
  }
  .sr-confirm-btn-no {
    background: #f1f5f9;
    color: #64748b;
    border: none;
    padding: 12px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    font-size: 0.9rem;
    transition: background 0.2s;
  }
  .sr-confirm-btn-no:hover {
    background: #e2e8f0;
  }
  .sr-confirm-btn-yes {
    background: var(--sr-primary);
    color: #ffffff;
    border: none;
    padding: 12px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    font-size: 0.9rem;
    transition: background 0.2s;
  }
  .sr-confirm-btn-yes:hover {
    background: #4338ca;
  }
</style>

<!-- ── Fullscreen Map Page ──────────────────────────────────── -->
<div class="sr-map-page">
  <div id="srMap"></div>

  <!-- Search Bar & Filter Button Overlay -->
  <div class="sr-map-header-wrap">
    <div class="sr-map-searchbar-new">
      <i class="fa-solid fa-magnifying-glass sr-map-search-icon"></i>
      <input type="text" id="mapSearchInput" placeholder="Search Retailer, Area…" autocomplete="off">
    </div>
    <div class="sr-search-suggestions" id="searchSuggestions"></div>
    <button class="sr-map-filter-btn" id="mapFilterBtn" title="Filter">
      <i class="fa-solid fa-sliders"></i>
    </button>
  </div>

  <!-- FAB Buttons (Float above bottom cards) -->
  <div class="sr-map-fabs-new">
    <button class="sr-map-fab-new sr-fab-locate-new" id="locateBtn" title="My Location">
      <i class="fa-solid fa-location-crosshairs"></i>
    </button>
    <button class="sr-map-fab-new sr-fab-add-new" id="addRetailerBtn" title="Add Retailer">
      <i class="fa-solid fa-plus"></i>
    </button>
  </div>

  <!-- Nearest Retailers Carousel Overlay -->
  <div class="sr-retailers-carousel-wrap" id="carouselWrap">
    <button class="sr-carousel-toggle-btn" id="carouselToggleBtn" title="Toggle Cards">
      <i class="fa-solid fa-chevron-down"></i>
    </button>
    <div class="sr-retailers-carousel" id="retailerCards">
      <!-- Dynamically filled with nearest retailer cards -->
    </div>
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

<!-- Custom Confirm Modal -->
<div class="sr-modal-overlay" id="confirmModalOverlay">
  <div class="sr-confirm-modal">
    <div class="sr-confirm-title">
      <i class="fa-solid fa-triangle-exclamation" style="color:var(--sr-primary);margin-right:8px;"></i>Modify Order?
    </div>
    <div class="sr-confirm-body" id="confirmModalBody">
      An order has already been placed for this retailer today. Do you want to modify this order?
    </div>
    <div class="sr-confirm-actions">
      <button class="sr-confirm-btn-no" id="confirmModalNoBtn">No, Cancel</button>
      <button class="sr-confirm-btn-yes" id="confirmModalYesBtn">Yes, Modify</button>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     RETAILER DETAIL FULLSCREEN POPUP
══════════════════════════════════════════════════════════════ -->
<!-- ══════════════════════════════════════════════════════════════
     RETAILER DETAIL FULLSCREEN POPUP
══════════════════════════════════════════════════════════════ -->
<div class="sr-retailer-popup-v2" id="retailerPopup">
  <!-- Topbar -->
  <div class="sr-popup-header-v2">
    <button class="sr-popup-back-btn-v2" id="retPopupBack">
      <i class="fa-solid fa-chevron-left"></i>
    </button>
    <div class="sr-popup-header-title-v2">Products</div>
    <button class="sr-popup-search-btn-v2">
      <i class="fa-solid fa-magnifying-glass"></i>
    </button>
  </div>

  <!-- Content Area -->
  <div class="sr-popup-content-v2">
    <!-- Retailer Profile Card -->
    <div class="sr-popup-profile-card-v2">
      <div class="sr-popup-profile-avatar-v2">
        <img id="retPopupAvatar" src="" alt="Avatar" onerror="this.src='https://i.pravatar.cc/100?img=12'">
      </div>
      <div class="sr-popup-profile-info-v2">
        <div class="sr-popup-profile-name-v2" id="retPopupName">—</div>
        <div class="sr-popup-profile-shop-v2">
          <i class="fa-solid fa-store" style="font-size:0.75rem; margin-right:4px; color:#64748b;"></i>
          <span id="retPopupShopName">—</span>
        </div>
      </div>
    </div>

    <!-- Category / Company Horizontal Scroll Bar -->
    <div class="sr-popup-categories-wrap-v2" id="popupCategoriesWrap">
      <!-- Populated dynamically based on companies -->
    </div>

    <!-- Section Title & Filters -->
    <div class="sr-popup-section-header-v2">
      <div class="sr-popup-section-title-v2">প্রোডাক্ট সমূহ</div>
      <div class="sr-popup-filters-v2">
        <span class="sr-filter-pill-v2 active">টপ সেলিং</span>
        <span class="sr-filter-pill-v2">ফ্রেশ</span>
        <span class="sr-filter-pill-v2">পুষ্টি</span>
        <span class="sr-filter-pill-v2">প্রাণ</span>
      </div>
    </div>

    <!-- Products Grid -->
    <div class="sr-products-grid-v2" id="productsGrid">
      <!-- Populated by JS -->
    </div>
  </div>

  <!-- Bottom Floating Cart Bar -->
  <div class="sr-popup-cart-bar-wrap-v2">
    <div class="sr-popup-cart-bar-v2">
      <div class="sr-cart-badge-container-v2">
        <div class="sr-cart-badge-btn-v2">
          <i class="fa-solid fa-cart-shopping"></i>
          <span class="sr-cart-badge-count-v2" id="cartCountBadge">0</span>
        </div>
      </div>
      <div class="sr-cart-item-thumbs-v2" id="cartItemThumbs">
        <!-- JS inserts thumbnails -->
      </div>
      <button class="sr-cart-checkout-btn-v2" onclick="openRetailerCartSheet(currentRetailer)">
        তালিকা দেখুন
      </button>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     PRODUCT ORDER BOTTOM SHEET
══════════════════════════════════════════════════════════════ -->
<div class="sr-sheet-overlay" id="productSheetOverlay"></div>
<div class="sr-bottom-sheet-v2" id="productSheet">
  <div class="sr-sheet-handle-v2"></div>
  <div class="sr-sheet-header-v2">
    <span class="sr-sheet-title-v2">Add Product</span>
    <button class="sr-sheet-close-v2" id="productSheetClose"><i class="fa-solid fa-xmark"></i></button>
  </div>
  <div class="sr-sheet-body-v2">
    <!-- Image Wrapper -->
    <div class="sr-prod-sheet-img-wrap-v2">
      <div id="productSheetImgWrap"></div>
    </div>
    
    <!-- Product Name & Package Type -->
    <div class="sr-prod-sheet-name-v2" id="productSheetName">—</div>
    <div class="sr-prod-sheet-package-v2">
      প্যাকেজ টাইপ: <span style="color:#2563eb; font-weight:700;">বক্স ( <span id="productSheetPcsPerBox">—</span> পিস )</span>
    </div>
    <div class="sr-prod-sheet-baseprice-v2">
      মোট মূল্য <span style="color:#f43f5e; font-weight:700;" id="productSheetBasePrice">—</span>
    </div>
    
    <!-- Price setting override controls -->
    <div class="sr-prod-sheet-override-header-v2" style="justify-content: flex-end; min-height: 24px;">
      <span class="sr-prod-override-badge-v2" id="productSheetOcBadge" style="display:none;">Tk 0</span>
    </div>
    
    <!-- Big Middle counter showing Total Value -->
    <div class="sr-prod-total-counter-box-v2">
      <button class="sr-prod-total-cnt-btn-v2" onclick="changeTotalAmount(-10)">−</button>
      <div class="sr-prod-total-cnt-value-v2">
        Tk <input type="number" id="totalDisplayInput" value="0" min="0" step="1" oninput="calcTotal()" style="border:none; background:none; font-weight:700; width:100px; text-align:center; color:#0f172a; outline:none;">
      </div>
      <button class="sr-prod-total-cnt-btn-v2" onclick="changeTotalAmount(10)">+</button>
    </div>
    
    <!-- Box & Piece counters -->
    <div class="sr-prod-qty-counters-grid-v2">
      <!-- Box counter -->
      <div class="sr-prod-qty-counter-v2">
        <div class="sr-prod-qty-counter-label-v2">বক্স</div>
        <div class="sr-prod-qty-counter-row-v2">
          <button class="sr-qty-counter-btn-v2" onclick="changeQty('cartons',-1)">−</button>
          <input type="number" id="qtyCartons" value="0" min="0" oninput="calcTotal()" class="sr-qty-counter-input-v2">
          <button class="sr-qty-counter-btn-v2" onclick="changeQty('cartons',1)">+</button>
        </div>
      </div>
      <!-- Piece counter -->
      <div class="sr-prod-qty-counter-v2">
        <div class="sr-prod-qty-counter-label-v2">পিস</div>
        <div class="sr-prod-qty-counter-row-v2">
          <button class="sr-qty-counter-btn-v2" onclick="changeQty('pieces',-1)">−</button>
          <input type="number" id="qtyPieces" value="0" min="0" oninput="calcTotal()" class="sr-qty-counter-input-v2">
          <button class="sr-qty-counter-btn-v2" onclick="changeQty('pieces',1)">+</button>
        </div>
      </div>
    </div>
    
    <input type="hidden" id="baseUnitPrice" value="0">
    <input type="hidden" id="unitPrice" value="0">
    <div id="unitPriceDisplay" style="display:none;">৳ 0.00</div>

    <!-- Bottom blue Add to Cart CTA -->
    <button class="sr-prod-sheet-add-btn-v2" id="addToCartBtn" onclick="addToCart()">
      <span id="addToCartBtnText">Tk 0 • Add Now</span> <i class="fa-solid fa-cart-shopping" style="margin-left: 4px;"></i>
    </button>
  </div>
</div>



<!-- ══════════════════════════════════════════════════════════════
     RETAILER CART SHEET
══════════════════════════════════════════════════════════════ -->
<div class="sr-sheet-overlay" id="retCartOverlay"></div>
<div class="sr-bottom-sheet-v2" id="retCartSheet">
  <div class="sr-sheet-handle-v2"></div>
  <div class="sr-sheet-header-v2" style="border-bottom: 1px solid #f1f5f9;">
    <span class="sr-sheet-title-v2" id="retCartTitle">অর্ডার তালিকা দেখুন</span>
    <button class="sr-sheet-close-v2" onclick="closeSheet('retCartSheet','retCartOverlay')"><i class="fa-solid fa-xmark"></i></button>
  </div>
  <div class="sr-sheet-body-v2" style="padding: 16px;">
    <!-- Items scroll list -->
    <div id="retCartItemsList" style="margin-bottom:14px; max-height: 48vh; overflow-y: auto; display: flex; flex-direction: column; gap: 12px;">
      <!-- Populated by JS -->
    </div>
    
    <!-- Red bordered summary total container -->
    <div class="sr-cart-summary-box-v2">
      <div class="sr-cart-summary-left-v2">
        <div class="sr-cart-summary-oc-v2" id="retCartOcVal" style="display:none;">O/C 0</div>
        <div class="sr-cart-summary-subtotal-v2">Subtotal: <strong id="retCartGrandTotal" style="color:#0f172a;">Tk 0</strong></div>
      </div>
      <button class="sr-cart-summary-confirm-btn-v2" id="retCartConfirmBtn" onclick="confirmRetailerCart()">
        অর্ডার কনফার্ম করুন
      </button>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     CHECKOUT SUCCESS FULLSCREEN OVERLAY
══════════════════════════════════════════════════════════════ -->
<div class="sr-success-overlay-v2" id="successOverlay">
  <div class="sr-success-container-v2">
    <div class="sr-success-icon-box-v2">
      <i class="fa-solid fa-circle-check"></i>
    </div>
    
    <div class="sr-success-title-v2">অভিনন্দন!</div>
    <div class="sr-success-subtitle-v2">আপনার অর্ডার সফলভাবে সম্পন্ন হয়েছে!</div>
    
    <!-- Delivery Info -->
    <div class="sr-success-info-card-v2">
      <div class="sr-info-card-header-v2">ডেলিভারি তথ্য:</div>
      <div class="sr-info-row-v2">
        <span class="sr-info-label-v2">গ্রাহক:</span>
        <span class="sr-info-value-v2" id="successCustName">—</span>
      </div>
      <div class="sr-info-row-v2">
        <span class="sr-info-label-v2">ডেলিভারির ঠিকানা:</span>
        <span class="sr-info-value-v2" id="successAddress">—</span>
      </div>
    </div>
    
    <!-- Products List -->
    <div class="sr-success-products-card-v2">
      <div class="sr-success-card-header-v2">Products</div>
      <div class="sr-success-prod-list-v2" id="successProductList">
        <!-- JS filled -->
      </div>
      <div class="sr-success-subtotal-box-v2">
        <div class="sr-subtotal-oc-row-v2" id="successOcRow" style="display:none;">
          <span>O/C</span>
          <span id="successOcAmount">0</span>
        </div>
        <div class="sr-subtotal-row-v2">
          <span>Total:</span>
          <span id="successSubtotalVal">Tk 0</span>
        </div>
      </div>
    </div>
    
    <!-- Actions -->
    <div class="sr-success-actions-v2">
      <button class="sr-btn-home-back-v2" id="successHomeBtn">হোমে ফিরে যাই</button>
      <button class="sr-btn-store-back-v2" id="successStoreBtn">দোকানে ফিরে যাই</button>
    </div>
  </div>
</div>

<?php
// $allProducts is passed from SRController::sales()
// Fallback to empty array if not set
$allProducts = $allProducts ?? [];
?>

<script src="https://cdn.jsdelivr.net/npm/fuse.js@7.0.0/dist/fuse.min.js"></script>
<script>
// ══════════════════════════════════════════════════════════════
// SR SALES PAGE — Full JS Logic
// ══════════════════════════════════════════════════════════════

const BASE_URL = '<?= BASE_URL ?>';
const SR_ID    = <?= Auth::id() ?>;
const ALL_PRODUCTS = <?= json_encode($allProducts, JSON_UNESCAPED_UNICODE) ?>;

// ── State ─────────────────────────────────────────────────────
let mainMap, miniMap, fullMap;
// Load last known location from localStorage if available
let myLat = parseFloat(localStorage.getItem('sr_last_lat')) || 23.8103;
let myLng = parseFloat(localStorage.getItem('sr_last_lng')) || 90.4125;
let pinLat, pinLng;
let currentRetailer = null;
let currentProduct  = null;
let cartsByRetailer = {}; // Key: retailer ID, Value: array of items
let retailerMarkers = [];
let allRetailersData = [];
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

// ── Helpers ───────────────────────────────────────────────────
function normalizeBanglish(text) {
  if (!text) return '';
  text = text.toLowerCase();
  const b2e = {
    'অ': 'o', 'আ': 'a', 'ই': 'i', 'ঈ': 'i', 'উ': 'u', 'ঊ': 'u', 'ঋ': 'ri', 'এ': 'e', 'ঐ': 'oi', 'ও': 'o', 'ঔ': 'ou',
    'ক': 'k', 'খ': 'kh', 'গ': 'g', 'ঘ': 'gh', 'ঙ': 'ng', 'চ': 'ch', 'ছ': 'ch', 'জ': 'j', 'ঝ': 'jh', 'ঞ': 'n',
    'ট': 't', 'ঠ': 'th', 'ড': 'd', 'ঢ': 'dh', 'ণ': 'n', 'ত': 't', 'থ': 'th', 'দ': 'd', 'ধ': 'dh', 'ন': 'n',
    'প': 'p', 'ফ': 'f', 'ব': 'b', 'ভ': 'v', 'ম': 'm', 'য': 'j', 'র': 'r', 'ল': 'l', 'শ': 'sh', 'ষ': 'sh', 'স': 's',
    'হ': 'h', 'ড়': 'r', 'ঢ়': 'rh', 'য়': 'y', 'ৎ': 't', 'ং': 'ng', 'ঃ': 'h', 'ঁ': 'n',
    'া': 'a', 'ি': 'i', 'ী': 'i', 'ু': 'u', 'ূ': 'u', 'ৃ': 'ri', 'ে': 'e', 'ৈ': 'oi', 'ো': 'o', 'ৌ': 'ou', '্': ''
  };
  let res = '';
  for (let i = 0; i < text.length; i++) {
    res += b2e[text[i]] !== undefined ? b2e[text[i]] : text[i];
  }
  return res;
}

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
  mainMap = L.map('srMap', { zoomControl: false, attributionControl: false }).setView([myLat, myLng], 18);
  L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {maxZoom: 20, subdomains:['mt0','mt1','mt2','mt3']}).addTo(mainMap);
  L.control.zoom({ position: 'bottomleft' }).addTo(mainMap);
  
  // Show initial cached/default location and load pins immediately
  placeMyLocationMarker();
  loadRetailersOnMap();
  
  // Refine location in background
  detectLocation(false);
}

// ── Detect / Go-to My Location ────────────────────────────────
function detectLocation(animate = true) {
  if (!navigator.geolocation) return;
  
  const geoOptions = {
    enableHighAccuracy: true,
    timeout: 5000,
    maximumAge: 30000 // Use cached position if it's less than 30 seconds old
  };

  navigator.geolocation.getCurrentPosition(pos => {
    myLat = pos.coords.latitude;
    myLng = pos.coords.longitude;
    
    // Cache the location
    localStorage.setItem('sr_last_lat', myLat);
    localStorage.setItem('sr_last_lng', myLng);
    
    if (animate) mainMap.flyTo([myLat, myLng], 18, { duration: 1.2 });
    else mainMap.setView([myLat, myLng], 18);
    
    placeMyLocationMarker();
    loadRetailersOnMap();
  }, () => {
    // If geolocation fails or is denied, still make sure retailers are loaded for the current coordinates
    loadRetailersOnMap();
  }, geoOptions);
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
  
  // Add 100 meter radius circle around location
  myCircle = L.circle([myLat, myLng], {
    radius: 100,
    className: 'sr-radius-circle'
  }).addTo(mainMap);
}

function calculateDistance(lat1, lng1, lat2, lng2) {
  return Math.round(6371000 * 2 * Math.asin(Math.sqrt(Math.pow(Math.sin((lat2 - lat1) * Math.PI / 360), 2) + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.pow(Math.sin((lng2 - lng1) * Math.PI / 360), 2))));
}

// ══════════════════════════════════════════════════════════════
// RETAILERS ON MAP & DYNAMIC CAROUSEL
// ══════════════════════════════════════════════════════════════
function loadRetailersOnMap() {
  fetch(`${BASE_URL}/sr/api/retailers?lat=${myLat}&lng=${myLng}&radius=1000`)
    .then(r => r.json())
    .then(data => {
      retailerMarkers.forEach(m => mainMap.removeLayer(m.marker));
      retailerMarkers = [];
      const retailers = data.retailers || [];
      allRetailersData = retailers;
      
      const nearbyRetailers = retailers.filter(ret => {
        const dist = ret.dist !== undefined ? ret.dist : calculateDistance(myLat, myLng, ret.lat, ret.lng);
        return dist <= 100;
      });
      
      nearbyRetailers.forEach(ret => addRetailerPin(ret));
      renderRetailerCards(nearbyRetailers);
    })
    .catch(() => {
      // Silently fail — show demo pins
      showDemoPins();
    });
}

function showDemoPins() {
  const demos = [
    { id: 1, name: 'Ahmed Store', phone: '01711000001', lat: myLat + 0.0004, lng: myLng + 0.0003, dist: 45, address: 'Road 4, House 12, Banani, Dhaka' },
    { id: 2, name: 'Rahim Shop',  phone: '01711000002', lat: myLat - 0.0003, lng: myLng + 0.0005, dist: 67, address: 'Block C, Section 10, Mirpur, Dhaka' },
    { id: 3, name: 'Karim Bhai', phone: '01711000003', lat: myLat + 0.0006, lng: myLng - 0.0004, dist: 83, address: 'Sector 3, Uttara, Dhaka' },
  ];
  retailerMarkers.forEach(m => mainMap.removeLayer(m.marker));
  retailerMarkers = [];
  allRetailersData = demos;
  
  const nearbyDemos = demos.filter(ret => ret.dist <= 100);
  nearbyDemos.forEach(ret => addRetailerPin(ret));
  renderRetailerCards(nearbyDemos);
}

function updateAllPins() {
  const currentRetailers = retailerMarkers.map(m => m.ret);
  // Re-render pins so they get the .has-cart class if needed
  retailerMarkers.forEach(m => mainMap.removeLayer(m.marker));
  retailerMarkers = [];
  currentRetailers.forEach(ret => addRetailerPin(ret));
  renderRetailerCards(currentRetailers);
}

function addRetailerPin(ret) {
  const hasCart = cartsByRetailer[ret.id] && cartsByRetailer[ret.id].length > 0;
  const markerClass = hasCart ? 'has-cart' : (ret.has_order_today ? 'already-ordered' : '');
  const icon = L.divIcon({
    className: '',
    html: `<div class="sr-retailer-marker ${markerClass}"><i class="fa-solid fa-store"></i>${escHtml(ret.name)}</div>`,
    iconSize: [0, 0],
    iconAnchor: [0, 0]
  });
  const marker = L.marker([ret.lat, ret.lng], { icon }).addTo(mainMap);
  marker.on('click', () => {
    triggerRetailerAction(ret);
  });
  retailerMarkers.push({ marker, ret });
}

function triggerRetailerAction(ret) {
  if (ret.has_order_today) {
    showConfirmModal(`An order has already been placed for "${ret.name}" today. Are you sure you want to modify this order?`, () => {
      fetch(`${BASE_URL}/sr/api/today-order?retailer_id=${ret.id}`)
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            cartsByRetailer[ret.id] = data.items;
            ret.has_order_today = false; // allow editing
            openRetailerCartSheet(ret);
          } else {
            showMiniToast('❌ ' + (data.message || 'Error fetching order details'), true);
          }
        })
        .catch(() => showMiniToast('❌ Network error', true));
    });
    return;
  }

  if (cartsByRetailer[ret.id] && cartsByRetailer[ret.id].length > 0) {
    openRetailerCartSheet(ret);
  } else {
    currentRetailer = ret;
    if (!cartsByRetailer[ret.id]) cartsByRetailer[ret.id] = [];
    openProductsForRetailer();
  }
}

function handleCardClick(ret) {
  mainMap.flyTo([ret.lat, ret.lng], 16.5, { duration: 1.0 });
  
  // Highlight the card selected
  document.querySelectorAll('.sr-retailer-card-new').forEach(c => c.style.border = 'none');
  const card = document.getElementById(`retailer-card-${ret.id}`);
  if (card) {
    card.style.border = '2px solid #2563eb';
  }
}

function handleNavigationClick(ret) {
  mainMap.flyTo([ret.lat, ret.lng], 17, { duration: 0.8 });
  setTimeout(() => {
    triggerRetailerAction(ret);
  }, 800);
}

function renderRetailerCards(retailers) {
  const container = document.getElementById('retailerCards');
  if (!container) return;
  
  const CARD_LIMIT = 1000; // 1km radius limit for cards display
  const filtered = (retailers || []).map(ret => {
    const distMeters = ret.dist || Math.round(6371000 * 2 * Math.asin(Math.sqrt(Math.pow(Math.sin((ret.lat - myLat) * Math.PI / 360), 2) + Math.cos(myLat * Math.PI / 180) * Math.cos(ret.lat * Math.PI / 180) * Math.pow(Math.sin((ret.lng - myLng) * Math.PI / 360), 2))));
    ret.calculated_dist = distMeters;
    return ret;
  }).filter(ret => ret.calculated_dist <= CARD_LIMIT);

  if (filtered.length === 0) {
    container.innerHTML = `<div style="width: 100%; text-align: center; background: #fff; padding: 20px; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); font-weight: 500; color: #94a3b8; pointer-events: auto;">No nearby retailers within 1km.</div>`;
    return;
  }
  
  container.innerHTML = filtered.map((ret, index) => {
    const distMeters = ret.calculated_dist;
    const imgUrl = `${BASE_URL}/public/assets/uploads/retailer_shop_${(index % 2) + 1}.png`;
    
    // Stable pseudo-random rating & reviews
    const ratingVal = (4.2 + ((ret.id * 7) % 9) / 10).toFixed(1);
    const reviewsCount = (ret.id * 17) % 180 + 15;
    
    // Highlight if has active cart
    const hasCart = cartsByRetailer[ret.id] && cartsByRetailer[ret.id].length > 0;
    const cardStyle = hasCart ? 'border: 2px dashed #eab308; background: #fffbeb;' : '';
    
    let starsHtml = '';
    for (let i = 1; i <= 5; i++) {
      if (i <= Math.round(parseFloat(ratingVal))) {
        starsHtml += '<i class="fa-solid fa-star"></i>';
      } else {
        starsHtml += '<i class="fa-regular fa-star"></i>';
      }
    }
    
    const distStr = distMeters > 1000 ? `${(distMeters / 1000).toFixed(1)} km` : `${distMeters} m`;
    const timeMins = Math.max(1, Math.round(distMeters / 80));
    const addressStr = ret.address || `Dhaka City Area, Retailer ID #${ret.id}`;

    return `
      <div class="sr-retailer-card-new" id="retailer-card-${ret.id}" style="${cardStyle}" onclick="handleCardClick(${JSON.stringify(ret).replace(/"/g, '&quot;')})">
        <div class="sr-retailer-card-img-wrap">
          <img src="${imgUrl}" class="sr-retailer-card-img" alt="${escHtml(ret.name)}">
        </div>
        <div class="sr-retailer-card-body">
          <div class="sr-retailer-card-header">
            <div class="sr-retailer-card-title-group">
              <div class="sr-retailer-card-title">${escHtml(ret.name)}</div>
              <div class="sr-retailer-card-rating">
                <span>${ratingVal}</span>
                <div class="sr-retailer-card-stars">${starsHtml}</div>
                <span class="sr-retailer-card-reviews">(${reviewsCount} Reviews)</span>
              </div>
            </div>
            <button class="sr-retailer-card-nav-btn" onclick="event.stopPropagation(); handleNavigationClick(${JSON.stringify(ret).replace(/"/g, '&quot;')})" title="Order Page">
              <i class="fa-solid fa-paper-plane"></i>
            </button>
          </div>
          <div class="sr-retailer-card-location">
            <i class="fa-solid fa-location-dot"></i>
            <span>${escHtml(addressStr)}</span>
          </div>
          <div class="sr-retailer-card-divider"></div>
          <div class="sr-retailer-card-footer">
            <div class="sr-retailer-card-meta-item">
              <i class="fa-solid fa-person-running"></i>
              <span>${distStr} / ${timeMins} min</span>
            </div>
            <div class="sr-retailer-card-meta-item">
              <i class="fa-solid fa-store"></i>
              <span>Grocery Store</span>
            </div>
          </div>
        </div>
      </div>
    `;
  }).join('');
}


function showConfirmModal(text, onYes) {
  document.getElementById('confirmModalBody').innerText = text;
  const overlay = document.getElementById('confirmModalOverlay');
  overlay.classList.add('open');
  
  const yesBtn = document.getElementById('confirmModalYesBtn');
  const noBtn = document.getElementById('confirmModalNoBtn');
  
  const newYesBtn = yesBtn.cloneNode(true);
  const newNoBtn = noBtn.cloneNode(true);
  
  yesBtn.parentNode.replaceChild(newYesBtn, yesBtn);
  noBtn.parentNode.replaceChild(newNoBtn, noBtn);
  
  newYesBtn.addEventListener('click', () => {
    overlay.classList.remove('open');
    onYes();
  });
  
  newNoBtn.addEventListener('click', () => {
    overlay.classList.remove('open');
  });
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
  
  renderRetailerCart();
  openSheet('retCartSheet', 'retCartOverlay');
}

function renderRetailerCart() {
  const cart = cartsByRetailer[currentRetailer.id] || [];
  const list = document.getElementById('retCartItemsList');
  
  if (cart.length === 0) {
    list.innerHTML = `<div style="text-align:center;padding:24px;color:#94a3b8;">Cart is empty. Select products from the shop to start.</div>`;
    document.getElementById('retCartGrandTotal').textContent = 'Tk 0';
    document.getElementById('retCartOcVal').style.display = 'none';
    return;
  }
  
  let totalVal = 0;
  list.innerHTML = cart.map((c, i) => {
    totalVal += c.total;
    const pcsPerCarton = c.pcsPerCarton || 12;
    const boxes = Math.floor(c.qty / pcsPerCarton);
    const pcs = c.qty % pcsPerCarton;
    
    const prod = ALL_PRODUCTS.find(p => p.id === c.id);
    const imgHtml = prod && prod.image
      ? `<img src="${BASE_URL}/${escHtml(prod.image)}" class="sr-cart-item-image-v2" alt="">`
      : `<div class="sr-cart-item-image-placeholder-v2">📦</div>`;
      
      // O/C status
      const ocHtml = c.oc !== 0 && c.oc !== undefined ? `<span class="sr-cart-item-oc-badge-v2 ${c.oc < 0 ? 'neg' : 'pos'}">${c.oc > 0 ? '+' : ''}${Math.round(c.oc)} O/C</span>` : '';
        
      return `
      <div class="sr-cart-item-card-v2">
        <div class="sr-cart-item-image-wrap-v2">
          ${imgHtml}
        </div>
        <div class="sr-cart-item-info-v2">
          <div class="sr-cart-item-name-v2">${escHtml(c.name)}</div>
          <div class="sr-cart-item-tags-v2">
            <span class="sr-cart-item-tag-v2"><strong>${boxes.toString().padStart(2, '0')}</strong> Box</span>
            <span class="sr-cart-item-tag-v2"><strong>${pcs.toString().padStart(2, '0')}</strong> Pack</span>
          </div>
          <div class="sr-cart-item-price-row-v2">
            <span class="sr-cart-item-price-v2">Tk ${Math.round(c.total)}</span>
            ${ocHtml}
          </div>
        </div>
        <button class="sr-cart-item-delete-btn-v2" onclick="removeCartItem(${i})" title="Remove item">
          <i class="fa-solid fa-trash-can"></i>
        </button>
      </div>`;
    }).join('');
    
    document.getElementById('retCartGrandTotal').textContent = 'Tk ' + Math.round(totalVal);
    
    // Total O/C display
    const totalOc = cart.reduce((sum, item) => sum + (item.oc || 0), 0);
    const retCartOcVal = document.getElementById('retCartOcVal');
    if (totalOc !== 0) {
      retCartOcVal.style.display = 'block';
      retCartOcVal.textContent = `O/C ${totalOc > 0 ? '+' : ''}${Math.round(totalOc)}`;
      retCartOcVal.className = `sr-cart-summary-oc-v2 ${totalOc < 0 ? 'neg' : 'pos'}`;
    } else {
      retCartOcVal.style.display = 'none';
    }
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
  updatePopupCartInfo();
  renderProductsGrid();
}

function removeCartItem(index) {
  cartsByRetailer[currentRetailer.id].splice(index, 1);
  renderRetailerCart();
  updateAllPins();
  updatePopupCartInfo();
  renderProductsGrid();
}

function openProductsForRetailer() {
  closeSheet('retCartSheet', 'retCartOverlay');
  
  document.getElementById('retPopupName').textContent  = currentRetailer.name;
  document.getElementById('retPopupShopName').textContent = currentRetailer.name;
  
  // Set profile avatar with a stable index based on retailer ID
  const avatarId = (currentRetailer.id % 70) + 1;
  document.getElementById('retPopupAvatar').src = `https://i.pravatar.cc/100?img=${avatarId}`;

  // Dynamically render categories (companies) from ALL_PRODUCTS
  const companies = [...new Set(ALL_PRODUCTS.map(p => p.company_name).filter(Boolean))];
  const catWrap = document.getElementById('popupCategoriesWrap');
  if (catWrap) {
    if (companies.length > 0) {
      catWrap.innerHTML = companies.map((cName, idx) => `
        <div class="sr-popup-category-card-v2 ${idx === 0 ? 'active' : ''}">
          <div class="sr-cat-icon-box-v2">🏢</div>
          <div class="sr-cat-label-v2">${escHtml(cName)}</div>
        </div>
      `).join('');
    } else {
      catWrap.innerHTML = '';
    }
  }

  renderProductsGrid();
  updatePopupCartInfo();
  document.getElementById('retailerPopup').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function updatePopupCartInfo() {
  const cart = cartsByRetailer[currentRetailer.id] || [];
  const totalQty = cart.reduce((sum, item) => sum + item.qty, 0);
  
  // Update floating cart badge and count
  const badgeCount = document.getElementById('cartCountBadge');
  if (badgeCount) {
    badgeCount.textContent = totalQty;
    badgeCount.style.display = totalQty > 0 ? 'flex' : 'none';
  }

  // Render product thumbnails in floating bar
  const thumbsContainer = document.getElementById('cartItemThumbs');
  if (thumbsContainer) {
    if (cart.length === 0) {
      thumbsContainer.innerHTML = '';
    } else {
      const maxThumbs = 3;
      let thumbsHtml = '';
      cart.slice(0, maxThumbs).forEach(item => {
        const prod = ALL_PRODUCTS.find(p => p.id === item.id);
        if (prod && prod.image) {
          thumbsHtml += `<div class="sr-cart-thumb-img-v2"><img src="${BASE_URL}/${escHtml(prod.image)}" alt=""></div>`;
        } else {
          thumbsHtml += `<div class="sr-cart-thumb-img-placeholder-v2">📦</div>`;
        }
      });
      
      if (cart.length > maxThumbs) {
        thumbsHtml += `<div class="sr-cart-thumb-more-v2">+${cart.length - maxThumbs}</div>`;
      }
      thumbsContainer.innerHTML = thumbsHtml;
    }
  }
}

function renderProductsGrid() {
  const grid = document.getElementById('productsGrid');
  if (!ALL_PRODUCTS.length) {
    grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:24px;color:#94a3b8;">No products available.</div>`;
    return;
  }
  
  const cart = cartsByRetailer[currentRetailer?.id] || [];
  
  grid.innerHTML = ALL_PRODUCTS.map((p, i) => {
    const grad  = gradients[i % gradients.length];
    const emoji = emojis[i % emojis.length];
    const imgHtml = p.image
      ? `<img src="${BASE_URL}/${escHtml(p.image)}" class="sr-product-card-image-v2" alt="${escHtml(p.name)}">`
      : `<div class="sr-product-card-image-placeholder-v2" style="background:${grad};">${emoji}</div>`;

    const isInCart = cart.some(item => item.id === p.id);
    const btnHtml = isInCart 
      ? `<button class="sr-prod-card-btn-v2 added" onclick="event.stopPropagation(); openProductSheet(${i})">যোগ হয়েছে</button>`
      : `<button class="sr-prod-card-btn-v2" onclick="event.stopPropagation(); openProductSheet(${i})">যোগ করুন <i class="fa-solid fa-plus" style="font-size: 0.65rem; margin-left: 2px;"></i></button>`;

    return `
    <div class="sr-product-card-v2" onclick="openProductSheet(${i})">
      <div class="sr-product-card-image-wrap-v2">
        ${imgHtml}
      </div>
      <div class="sr-product-card-info-v2">
        <div class="sr-product-card-name-v2">${escHtml(p.name)}</div>
        <div class="sr-product-card-price-v2">Tk ${parseFloat(p.selling_price || p.price || 0)}</div>
        <div class="sr-product-card-action-v2">
          ${btnHtml}
        </div>
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

  const ppb = parseInt(p.pieces_per_carton || p.pieces_per_box || 12);
  document.getElementById('productSheetName').textContent = p.name;
  document.getElementById('productSheetPcsPerBox').textContent = ppb;
  
  // Pre-fill quantities from cart if item already exists
  const cart = cartsByRetailer[currentRetailer.id] || [];
  const existing = cart.find(c => c.id === p.id);
  
  const baseProductPrice = parseFloat(p.selling_price || p.price || 0);
  document.getElementById('baseUnitPrice').value = baseProductPrice;

  // Carton base price estimation
  const cartonBasePrice = baseProductPrice * ppb;
  document.getElementById('productSheetBasePrice').textContent = `Tk 0`;
  
  // Set the big input default to the carton base price
  document.getElementById('totalDisplayInput').value = cartonBasePrice.toFixed(2);

  const imgWrap = document.getElementById('productSheetImgWrap');
  if (p.image) {
    imgWrap.innerHTML = `<img src="${BASE_URL}/${escHtml(p.image)}" class="sr-product-sheet-img-v2" alt="${escHtml(p.name)}">`;
  } else {
    imgWrap.innerHTML = `<div class="sr-product-sheet-placeholder-v2" style="background:${grad};">${emoji}</div>`;
  }
  
  let cartons = 0;
  let pieces = 0;
  let currentPiecePrice = baseProductPrice;
  
  if (existing) {
    cartons = Math.floor(existing.qty / ppb);
    pieces = existing.qty % ppb;
    currentPiecePrice = existing.price;
    document.getElementById('totalDisplayInput').value = (currentPiecePrice * ppb).toFixed(2);
  }

  document.getElementById('qtyCartons').value = cartons;
  document.getElementById('qtyPieces').value  = pieces;
  document.getElementById('unitPrice').value = currentPiecePrice;
  
  calcTotal();

  openSheet('productSheet','productSheetOverlay');
}

function changeQty(type, delta) {
  const el = document.getElementById(type === 'cartons' ? 'qtyCartons' : 'qtyPieces');
  el.value = Math.max(0, parseInt(el.value || 0) + delta);
  calcTotal();
}

function changeTotalAmount(amount) {
  const input = document.getElementById('totalDisplayInput');
  let currentBoxPrice = parseFloat(input.value) || 0;
  input.value = Math.max(0, currentBoxPrice + amount).toFixed(2);
  calcTotal();
}

function updateOcDisplay(totalPcs, actualTotal) {
  const basePrice = parseFloat(document.getElementById('baseUnitPrice').value) || 0;
  const expectedTotal = totalPcs * basePrice;
  const oc = actualTotal - expectedTotal;
  
  const badge = document.getElementById('productSheetOcBadge');
  if (Math.round(oc) === 0 || totalPcs === 0) {
    badge.style.display = 'none';
  } else {
    badge.style.display = 'inline-block';
    badge.textContent = `Tk ${oc > 0 ? '+' : ''}${Math.round(oc)}`;
    badge.className = `sr-prod-override-badge-v2 ${oc < 0 ? 'neg' : 'pos'}`;
  }
}

function calcTotal() {
  const cartons = parseInt(document.getElementById('qtyCartons').value) || 0;
  const pieces  = parseInt(document.getElementById('qtyPieces').value)  || 0;
  
  const currentBoxPrice = parseFloat(document.getElementById('totalDisplayInput').value) || 0;
  const pcsPerCarton = currentProduct?.pieces_per_carton || currentProduct?.pieces_per_box || 12;
  const currentPiecePrice = pcsPerCarton > 0 ? (currentBoxPrice / pcsPerCarton) : currentBoxPrice;
  
  const totalPcs = cartons * pcsPerCarton + pieces;
  const actualTotal = totalPcs * currentPiecePrice;
  
  document.getElementById('unitPrice').value = currentPiecePrice;
  
  // update মোট মূল্য
  document.getElementById('productSheetBasePrice').textContent = `Tk ${Math.round(actualTotal)}`;
  
  const btnText = document.getElementById('addToCartBtnText');
  if (btnText) {
    btnText.textContent = `Tk ${Math.round(actualTotal)} • Add Now`;
  }
  updateOcDisplay(totalPcs, actualTotal);
}

function addToCart() {
  const p = currentProduct;
  const cartons = parseInt(document.getElementById('qtyCartons').value) || 0;
  const pieces  = parseInt(document.getElementById('qtyPieces').value)  || 0;
  
  const currentBoxPrice = parseFloat(document.getElementById('totalDisplayInput').value) || 0;
  const pcsPerCarton = p?.pieces_per_carton || p?.pieces_per_box || 12;
  const currentPiecePrice = pcsPerCarton > 0 ? (currentBoxPrice / pcsPerCarton) : currentBoxPrice;
  
  const totalPcs = cartons * pcsPerCarton + pieces;

  if (totalPcs <= 0) { shakeElement('addToCartBtn'); return; }

  const actualTotal = totalPcs * currentPiecePrice;
  const basePrice = parseFloat(document.getElementById('baseUnitPrice').value) || 0;
  const oc = actualTotal - (totalPcs * basePrice);
  
  const cart = cartsByRetailer[currentRetailer.id];
  
  const existing = cart.find(c => c.id === p.id);
  if (existing) {
    existing.qty   = totalPcs;
    existing.total = actualTotal;
    existing.price = currentPiecePrice;
    existing.oc    = oc;
  } else {
    cart.push({ id: p.id, name: p.name, qty: totalPcs, price: currentPiecePrice, total: actualTotal, pcsPerCarton, oc });
  }

  closeSheet('productSheet','productSheetOverlay');
  
  updateAllPins(); // Ensure pin turns yellow
  updatePopupCartInfo(); // Update the bottom bar in the popup
  renderProductsGrid(); // Update button states in the products grid
  
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
  
  const notes = '';

  const form = document.createElement('form');
  form.method = 'POST';
  form.action = `${BASE_URL}/sr/orders/store`;

  const csrf = document.querySelector('meta[name="csrf"]');
  if (csrf) addInput(form, '_csrf', csrf.content);

  addInput(form, 'retailer_id', currentRetailer.id);
  addInput(form, 'notes', notes);
  addInput(form, 'ajax', '1');
  
  cart.forEach((c, i) => {
    addInput(form, `product_id[${i}]`, c.id);
    addInput(form, `quantity[${i}]`, c.qty);
    addInput(form, `unit_price[${i}]`, c.price);
  });

  isSubmitting = true;
  const confirmBtn = document.getElementById('retCartConfirmBtn');
  const originalBtnHtml = confirmBtn.innerHTML;
  confirmBtn.disabled = true;
  confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Placing Order...';

  const formData = new FormData(form);

  fetch(`${BASE_URL}/sr/orders/store`, {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(d => {
    isSubmitting = false;
    confirmBtn.disabled = false;
    confirmBtn.innerHTML = originalBtnHtml;

    if (d.success) {
      // 1. Populating the success screen before clearing the cart
      document.getElementById('successCustName').textContent = currentRetailer.name;
      document.getElementById('successAddress').textContent = currentRetailer.address || 'Detected Location';
      
      const prodList = document.getElementById('successProductList');
      let grandTotal = 0;
      
      prodList.innerHTML = cart.map((item, idx) => {
        grandTotal += item.total;
        const pcsPerCarton = item.pcsPerCarton || 12;
        const boxes = Math.floor(item.qty / pcsPerCarton);
        const pcs = item.qty % pcsPerCarton;
        
        return `
        <div class="sr-success-prod-item-v2">
          <div class="sr-success-prod-index-v2">${idx + 1}</div>
          <div class="sr-success-prod-details-v2">
            <div class="sr-success-prod-name-v2">${escHtml(item.name)}</div>
            <div class="sr-success-prod-tags-v2">
              <span class="sr-success-tag-v2"><strong>${boxes.toString().padStart(2, '0')}</strong> Box</span>
              <span class="sr-success-tag-v2"><strong>${pcs.toString().padStart(2, '0')}</strong> Pack</span>
            </div>
          </div>
        </div>`;
      }).join('');
      
      // Total O/C computation
      const totalOc = cart.reduce((sum, item) => sum + (item.oc || 0), 0);
      if (totalOc !== 0) {
        document.getElementById('successOcRow').style.display = 'flex';
        document.getElementById('successOcRow').className = `sr-subtotal-oc-row-v2 ${totalOc < 0 ? 'neg' : 'pos'}`;
        document.getElementById('successOcAmount').textContent = `${totalOc > 0 ? '+' : ''}${Math.round(totalOc)}`;
      } else {
        document.getElementById('successOcRow').style.display = 'none';
      }
      
      document.getElementById('successSubtotalVal').textContent = `Tk ${Math.round(grandTotal)}`;

      // Clear cart for this retailer
      cartsByRetailer[currentRetailer.id] = [];
      
      // Close sheets and popups
      closeSheet('retCartSheet', 'retCartOverlay');
      
      // Open Success Screen overlay
      document.getElementById('successOverlay').classList.add('open');

      // Play success notification sound
      try {
        const audio = new Audio(`${BASE_URL}/public/assets/dragon-studio-notification-sound-effect-372475.mp3.mpeg`);
        audio.play().catch(e => console.log('Audio playback blocked or failed:', e));
      } catch (err) {
        console.error('Audio error:', err);
      }
      
      // Update map pins (so yellow cart indicator is removed)
      updateAllPins();
    } else {
      showMiniToast('❌ ' + (d.message || 'Failed to place order'), true);
    }
  })
  .catch(err => {
    isSubmitting = false;
    confirmBtn.disabled = false;
    confirmBtn.innerHTML = originalBtnHtml;
    showMiniToast('❌ Network error', true);
    console.error(err);
  });
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

  document.getElementById('successHomeBtn').addEventListener('click', () => {
    document.getElementById('successOverlay').classList.remove('open');
    document.getElementById('retailerPopup').classList.remove('open');
    document.body.style.overflow = '';
  });
  
  document.getElementById('successStoreBtn').addEventListener('click', () => {
    document.getElementById('successOverlay').classList.remove('open');
    renderProductsGrid();
    updatePopupCartInfo();
  });

  // Carousel Toggle Button
  const carouselToggleBtn = document.getElementById('carouselToggleBtn');
  const carouselWrap = document.getElementById('carouselWrap');
  const mapFabs = document.querySelector('.sr-map-fabs-new');
  if (carouselToggleBtn && carouselWrap) {
    carouselToggleBtn.addEventListener('click', () => {
      carouselWrap.classList.toggle('collapsed');
      const isCollapsed = carouselWrap.classList.contains('collapsed');
      carouselToggleBtn.innerHTML = isCollapsed 
        ? '<i class="fa-solid fa-chevron-up"></i>' 
        : '<i class="fa-solid fa-chevron-down"></i>';
      if (mapFabs) {
        if (isCollapsed) {
          mapFabs.classList.add('lowered');
        } else {
          mapFabs.classList.remove('lowered');
        }
      }
    });
  }

  // Filter Button
  const filterBtn = document.getElementById('mapFilterBtn');
  if (filterBtn) {
    filterBtn.addEventListener('click', () => {
      showMiniToast('ℹ️ Filter settings are fully optimized for nearest retailers');
    });
  }

  // Search Input & Suggestions
  const searchInput = document.getElementById('mapSearchInput');
  const suggestionsBox = document.getElementById('searchSuggestions');
  if (searchInput && suggestionsBox) {
    searchInput.addEventListener('input', () => {
      const q = searchInput.value.trim().toLowerCase();
      if (!q) {
        suggestionsBox.innerHTML = '';
        suggestionsBox.classList.remove('open');
        return;
      }

      const normalizedQ = normalizeBanglish(q);

      // Prepare data for Fuse.js
      const retailers = allRetailersData.map(r => {
        if (!r.normalized_name) r.normalized_name = normalizeBanglish(r.name);
        return r;
      });

      const fuse = new Fuse(retailers, {
        keys: ['name', 'normalized_name', 'phone'],
        threshold: 0.4,
        ignoreLocation: true
      });

      const results = fuse.search(normalizedQ);
      const matches = results.map(result => result.item).slice(0, 15); // Limit to top 15 matches

      if (matches.length === 0) {
        suggestionsBox.innerHTML = `<div style="padding: 12px; color: #94a3b8; font-size: 0.82rem; text-align: center;">No matching retailers</div>`;
        suggestionsBox.classList.add('open');
        return;
      }

      suggestionsBox.innerHTML = matches.map(ret => {
        const addressStr = ret.address || `Dhaka City Area, Retailer ID #${ret.id}`;
        return `
          <div class="sr-suggestion-item" onclick="handleSuggestionSelect(${JSON.stringify(ret).replace(/"/g, '&quot;')})">
            <span class="sr-suggestion-title"><i class="fa-solid fa-store" style="color:#2563eb; margin-right:6px; font-size:0.8rem;"></i>${escHtml(ret.name)}</span>
            <span class="sr-suggestion-desc">${escHtml(addressStr)}</span>
          </div>
        `;
      }).join('');
      suggestionsBox.classList.add('open');
    });

    searchInput.addEventListener('keypress', e => {
      if (e.key === 'Enter') {
        suggestionsBox.classList.remove('open');
        doMapSearch();
      }
    });

    // Close when clicking outside
    document.addEventListener('click', e => {
      if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
        suggestionsBox.classList.remove('open');
      }
    });
  }
}

function doMapSearch() {
  const q = document.getElementById('mapSearchInput').value.trim();
  if (!q) return;
  
  // Try searching locally first
  const normalizedQ = normalizeBanglish(q);
  const retailers = allRetailersData.map(r => {
    if (!r.normalized_name) r.normalized_name = normalizeBanglish(r.name);
    return r;
  });

  const fuse = new Fuse(retailers, {
    keys: ['name', 'normalized_name', 'phone'],
    threshold: 0.4,
    ignoreLocation: true
  });

  const results = fuse.search(normalizedQ);
  const match = results.length > 0 ? results[0].item : null;
  
  if (match) {
    handleSuggestionSelect(match);
    return;
  }

  fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(q)}&format=json&limit=1`)
    .then(r => r.json())
    .then(d => {
      if (d.length) {
        mainMap.flyTo([d[0].lat, d[0].lon], 15, { duration: 1 });
      } else {
        showMiniToast('❌ Location not found', true);
      }
    })
    .catch(() => showMiniToast('❌ Search service unavailable', true));
}

function handleSuggestionSelect(ret) {
  const suggestionsBox = document.getElementById('searchSuggestions');
  const searchInput = document.getElementById('mapSearchInput');
  if (suggestionsBox) suggestionsBox.classList.remove('open');
  if (searchInput) searchInput.value = ret.name;

  // Make sure the marker is on the map. If it's not already in retailerMarkers, add it temporarily
  const exists = retailerMarkers.some(m => m.ret.id === ret.id);
  if (!exists) {
    addRetailerPin(ret);
    // Re-render carousel cards
    const currentCards = retailerMarkers.map(m => m.ret);
    renderRetailerCards(currentCards);
  }

  handleCardClick(ret);

  const card = document.getElementById(`retailer-card-${ret.id}`);
  if (card) {
    card.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
  }
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
.sr-retailer-marker.already-ordered {
  background: #ffedd5 !important;
  border-color: #ea580c !important;
  color: #c2410c !important;
}
.sr-retailer-marker.already-ordered::after {
  border-top-color: #ea580c !important;
}
.sr-retailer-marker.already-ordered i {
  color: #ea580c !important;
}
.sr-retailer-marker.already-ordered:hover {
  background: #f97316 !important;
  color: #ffffff !important;
}
.sr-retailer-marker.already-ordered:hover i {
  color: #ffffff !important;
}
.sr-retailer-marker.already-ordered:hover::after {
  border-top-color: #f97316 !important;
}


</style>
