<?php $pageTitle = 'Sales'; ?>


<!-- ── Fullscreen Map Page ──────────────────────────────────── -->
<div class="sr-map-page">
  <div id="srMap"></div>

  <!-- Search Bar & Filter Button Overlay -->
  <div class="sr-map-header-wrap">
    <a href="<?= url('sr/dashboard') ?>" class="w-[54px] h-[54px] bg-white text-slate-700 rounded-[14px] flex items-center justify-center shadow-[0_8px_30px_rgba(0,0,0,0.08)] active:scale-95 transition-all text-lg flex-shrink-0" title="Back">
      <i class="fa-solid fa-arrow-left"></i>
    </a>
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

<?php include __DIR__ . '/partials/_shop_v2.php'; ?>

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
  'linear-gradient(135deg,#2563eb,#3b82f6)',
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
    html: `<div style="width:18px;height:18px;border-radius:50%;background:#2563eb;border:3px solid #fff;box-shadow:0 2px 8px rgba(37,99,235,.5);"></div>`,
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

      // Auto open retailer from query parameter
      const urlParams = new URLSearchParams(window.location.search);
      const targetRetailerId = parseInt(urlParams.get('retailer_id'));
      if (targetRetailerId) {
        const targetRet = retailers.find(ret => ret.id === targetRetailerId);
        if (targetRet) {
          const isNearby = nearbyRetailers.some(ret => ret.id === targetRetailerId);
          if (!isNearby) {
            addRetailerPin(targetRet);
          }
          mainMap.setView([targetRet.lat, targetRet.lng], 17);
          setTimeout(() => {
            openRetailerCartSheet(targetRet);
          }, 350);
        }
      }
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
