<?php $pageTitle = 'ম্যাপ'; ?>


<!-- ── Fullscreen Map Page ──────────────────────────────────── -->
<div class="sr-map-page" style="font-family: 'Hind Siliguri', sans-serif;">
  <div id="srMap"></div>

  <!-- Map Status Badge (GPS Locating & Retailer Loading Indicator) -->
  <div class="sr-map-loading-badge" id="srMapLoadingBadge">
    <i class="fa-solid fa-satellite-dish fa-spin"></i>
    <span id="srMapLoadingText">অবস্থান ও দোকান লোড হচ্ছে...</span>
  </div>

  <!-- Search Bar & Filter Button Overlay -->
  <div class="sr-map-header-wrap">
    <a href="<?= url('sr/dashboard') ?>" class="w-[54px] h-[54px] bg-white text-slate-700 rounded-[14px] flex items-center justify-center shadow-[0_8px_30px_rgba(0,0,0,0.08)] active:scale-95 transition-all text-lg flex-shrink-0" title="পিছনে">
      <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div class="sr-map-searchbar-new">
      <i class="fa-solid fa-magnifying-glass sr-map-search-icon"></i>
      <input type="text" id="mapSearchInput" placeholder="দোকান বা এলাকা খুঁজুন..." autocomplete="off">
      <i class="fa-solid fa-spinner fa-spin sr-search-loading-icon" id="mapSearchSpinner"></i>
    </div>
    <div class="sr-search-suggestions" id="searchSuggestions"></div>
    <button class="sr-map-filter-btn" id="mapFilterBtn" title="ফিল্টার">
      <i class="fa-solid fa-sliders"></i>
    </button>
  </div>

  <!-- FAB Buttons (Float above bottom cards) -->
  <div class="sr-map-fabs-new">
    <button class="sr-map-fab-new sr-fab-locate-new" id="locateBtn" title="আমার অবস্থান">
      <i class="fa-solid fa-location-crosshairs"></i>
    </button>
    <button class="sr-map-fab-new sr-fab-add-new" id="addRetailerBtn" title="নতুন দোকান">
      <i class="fa-solid fa-plus"></i>
    </button>
  </div>

  <!-- Nearest Retailers Carousel Overlay -->
  <div class="sr-retailers-carousel-wrap" id="carouselWrap">
    <button class="sr-carousel-toggle-btn" id="carouselToggleBtn" title="টগল কার্ড">
      <i class="fa-solid fa-chevron-down"></i>
    </button>
    <div class="sr-retailers-carousel" id="retailerCards">
      <!-- Dynamically filled with nearest retailer cards or skeletons -->
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     ADD RETAILER BOTTOM SHEET
══════════════════════════════════════════════════════════════ -->
<div class="sr-sheet-overlay" id="addRetOverlay"></div>
<div class="sr-bottom-sheet" id="addRetSheet" style="font-family: 'Hind Siliguri', sans-serif;">
  <div class="sr-sheet-handle"></div>
  <div class="sr-sheet-header">
    <span class="sr-sheet-title"><i class="fa-solid fa-store" style="color:var(--sr-primary);margin-right:8px;"></i>নতুন দোকান যুক্ত করুন</span>
    <button class="sr-sheet-close" id="addRetClose"><i class="fa-solid fa-xmark"></i></button>
  </div>
  <div class="sr-sheet-body">
    <form id="addRetailerForm">
      <div class="sr-form-group">
        <label class="sr-form-label">দোকানের নাম <span style="color:#ef4444;">*</span></label>
        <input type="text" class="sr-form-input" id="retName" placeholder="যেমন: আহমেদ জেনারেল স্টোর" required>
      </div>
      <div class="sr-form-group">
        <label class="sr-form-label">মোবাইল নাম্বার</label>
        <input type="tel" class="sr-form-input" id="retPhone" placeholder="01XXXXXXXXX">
      </div>
      <div class="sr-form-group">
        <label class="sr-form-label">অবস্থান <span style="color:#ef4444;">*</span></label>
        <div class="sr-mini-map-wrap">
          <div id="srMiniMap"></div>
          <button type="button" class="sr-mini-map-fullscreen" id="miniMapFullscreenBtn" title="পূর্ণ মানচিত্র">
            <i class="fa-solid fa-expand"></i>
          </button>
          <div class="sr-mini-map-hint">পিনের অবস্থান পরিবর্তন করতে মানচিত্রটি ড্র্যাগ করুন</div>
        </div>
        <div id="selectedLocText" style="font-size:0.72rem;color:var(--sr-text-muted);margin-top:6px;text-align:center;">
          <i class="fa-solid fa-location-dot" style="color:var(--sr-primary);"></i> অবস্থান সনাক্ত করা হচ্ছে…
        </div>
      </div>
      <button type="submit" class="sr-add-cart-btn" style="margin-top:4px; font-family: 'Hind Siliguri', sans-serif;">
        <i class="fa-solid fa-floppy-disk"></i> দোকান সংরক্ষণ করুন
      </button>
    </form>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     FULLSCREEN PIN MAP OVERLAY
══════════════════════════════════════════════════════════════ -->
<div class="sr-fullmap-overlay hidden" id="fullMapOverlay" style="font-family: 'Hind Siliguri', sans-serif;">
  <div id="srFullMap"></div>
  <div class="sr-fullmap-crosshair">
    <i class="fa-solid fa-location-dot"></i>
  </div>
  <div class="sr-fullmap-topbar">
    <button class="sr-fullmap-back" id="fullMapBack"><i class="fa-solid fa-arrow-left"></i></button>
    <span class="sr-fullmap-title">দোকানের অবস্থান সিলেক্ট করুন</span>
    <button class="sr-fullmap-confirm" id="fullMapConfirm">নিশ্চিত করুন</button>
  </div>
</div>

<?php include __DIR__ . '/partials/_shop_v2.php'; ?>

<?php
// $allProducts is passed from SRController::sales()
// Fallback to empty array if not set
$allProducts = $allProducts ?? [];
?>

<script src="https://cdn.jsdelivr.net/npm/fuse.js@7.0.0/dist/fuse.min.js" defer></script>
<script>
// ══════════════════════════════════════════════════════════════
// SR SALES PAGE — Full JS Logic
// ══════════════════════════════════════════════════════════════

const BASE_URL = '<?= BASE_URL ?>';
const SR_ID    = <?= Auth::id() ?>;
const ALL_PRODUCTS_URL = `${BASE_URL}/sr/api/products`;
let ALL_PRODUCTS = [];

// Fetch products asynchronously to avoid massive HTML payloads
fetch(ALL_PRODUCTS_URL)
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      ALL_PRODUCTS = data.products || [];
    }
  })
  .catch(err => console.error('Failed to load products', err));

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

let globalFuse = null;
function initOrUpdateFuse() {
  if (!allRetailersData) return;
  const retailers = allRetailersData.map(r => {
    if (!r.normalized_name) r.normalized_name = normalizeBanglish(r.name);
    return r;
  });
  globalFuse = new Fuse(retailers, {
    keys: ['name', 'normalized_name', 'phone'],
    threshold: 0.4,
    ignoreLocation: true
  });
}

// ══════════════════════════════════════════════════════════════
// MAIN MAP INIT
// ══════════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => {
  // Delay map and event initialization to unblock First Contentful Paint (FCP)
  setTimeout(() => {
    initMainMap();
    initEventListeners();
  }, 100);
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
  // Mobile Internet Optimization: Use HTML5 Canvas renderer for 60fps performance on mobile devices
  mainMap = L.map('srMap', { 
    zoomControl: false, 
    attributionControl: false,
    preferCanvas: true, // Render Leaflet markers on Canvas for mobile RAM optimization
    fadeAnimation: true,
    markerZoomAnimation: true
  }).setView([myLat, myLng], 18);

  // Fast HTTPS Google Tile Layer with buffer caching and idle updates to save mobile data
  L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
    maxZoom: 20,
    maxNativeZoom: 19,
    subdomains: ['mt0','mt1','mt2','mt3'],
    keepBuffer: 6,           // Buffer surrounding tiles to eliminate white squares on panning
    updateWhenIdle: true,    // Saves mobile data by postponing tile requests until pan stops
    updateWhenZooming: false
  }).addTo(mainMap);

  L.control.zoom({ position: 'bottomleft' }).addTo(mainMap);
  
  // Show initial cached/default location and load pins immediately
  placeMyLocationMarker();
  loadRetailersOnMap();
  
  // Refine location in background
  detectLocation(false);
}

// ── Skeleton Loader for Retailers Carousel ───────────────────
function renderRetailerSkeletons() {
  const container = document.getElementById('retailerCards');
  if (!container) return;
  container.innerHTML = [1, 2, 3].map(() => `
    <div class="sr-skeleton-retailer-card">
      <div style="display:flex; justify-content:space-between; align-items:center; gap:10px;">
        <div style="display:flex; align-items:center; gap:8px; flex:1;">
          <div class="sr-skeleton-circle" style="width:34px; height:34px; flex-shrink:0;"></div>
          <div class="sr-skeleton-line" style="width:65%; height:14px;"></div>
        </div>
        <div class="sr-skeleton-line" style="width:55px; height:24px; border-radius:8px;"></div>
      </div>
      <div class="sr-skeleton-line" style="width:85%; height:10px; margin: 8px 0 6px 0;"></div>
      <div style="display:flex; gap:6px;">
        <div class="sr-skeleton-line" style="width:50px; height:18px; border-radius:6px;"></div>
        <div class="sr-skeleton-line" style="width:65px; height:18px; border-radius:6px;"></div>
        <div class="sr-skeleton-line" style="width:55px; height:18px; border-radius:6px;"></div>
      </div>
    </div>
  `).join('');
}

// ── Detect / Go-to My Location ────────────────────────────────
function detectLocation(animate = true) {
  if (!navigator.geolocation) return;
  
  const locateBtn = document.getElementById('locateBtn');
  const loadingBadge = document.getElementById('srMapLoadingBadge');
  const loadingText = document.getElementById('srMapLoadingText');

  if (locateBtn) locateBtn.classList.add('sr-fab-locating');
  if (loadingBadge && loadingText) {
    loadingText.innerText = 'অবস্থান সনাক্ত করা হচ্ছে...';
    loadingBadge.classList.add('show');
  }
  
  const geoOptions = {
    enableHighAccuracy: true,
    timeout: 4000,           // Fast 4s timeout for mobile internet
    maximumAge: 60000        // Use cached position if less than 60s old
  };

  navigator.geolocation.getCurrentPosition(pos => {
    myLat = pos.coords.latitude;
    myLng = pos.coords.longitude;
    
    // Cache the location
    localStorage.setItem('sr_last_lat', myLat);
    localStorage.setItem('sr_last_lng', myLng);
    
    if (animate) mainMap.flyTo([myLat, myLng], 18, { duration: 1.0 });
    else mainMap.setView([myLat, myLng], 18);
    
    placeMyLocationMarker();
    loadRetailersOnMap();

    if (locateBtn) locateBtn.classList.remove('sr-fab-locating');
    if (loadingBadge) loadingBadge.classList.remove('show');
  }, () => {
    // If geolocation fails or is denied, load retailers using cached location
    loadRetailersOnMap();
    if (locateBtn) locateBtn.classList.remove('sr-fab-locating');
    if (loadingBadge) loadingBadge.classList.remove('show');
  }, geoOptions);
}

function placeMyLocationMarker() {
  if (window._myMarker) mainMap.removeLayer(window._myMarker);
  if (myCircle) mainMap.removeLayer(myCircle);

  const icon = L.divIcon({
    className: '',
    html: `<div style="width:18px;height:18px;border-radius:50%;background:#2563eb;border:3px solid #fff;box-shadow:0 2px 8px rgba(37,99,235,.5);"></div>`,
    iconSize: [18, 18], iconAnchor: [9, 9]
  });
  window._myMarker = L.marker([myLat, myLng], { icon }).addTo(mainMap);
  
  myCircle = L.circle([myLat, myLng], {
    radius: 100,
    className: 'sr-radius-circle'
  }).addTo(mainMap);
}

function calculateDistance(lat1, lng1, lat2, lng2) {
  return Math.round(6371000 * 2 * Math.asin(Math.sqrt(Math.pow(Math.sin((lat2 - lat1) * Math.PI / 360), 2) + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.pow(Math.sin((lng2 - lng1) * Math.PI / 360), 2))));
}

// ══════════════════════════════════════════════════════════════
// RETAILERS ON MAP WITH MOBILE INTERNET CACHING
// ══════════════════════════════════════════════════════════════
function loadRetailersOnMap() {
  const cacheKey = `sr_ret_cache_${myLat.toFixed(3)}_${myLng.toFixed(3)}`;
  const cachedData = sessionStorage.getItem(cacheKey);

  const loadingBadge = document.getElementById('srMapLoadingBadge');
  const loadingText = document.getElementById('srMapLoadingText');

  // Instant render from local cache if available (0ms load time for mobile)
  if (cachedData) {
    try {
      const parsed = JSON.parse(cachedData);
      processRetailerData(parsed);
    } catch (e) {}
  } else {
    renderRetailerSkeletons();
    if (loadingBadge && loadingText) {
      loadingText.innerText = 'নিকটবর্তী দোকান লোড হচ্ছে...';
      loadingBadge.classList.add('show');
    }
  }

  // Network Fetch
  fetch(`${BASE_URL}/sr/api/retailers?lat=${myLat}&lng=${myLng}&radius=1000`)
    .then(r => r.json())
    .then(data => {
      sessionStorage.setItem(cacheKey, JSON.stringify(data));
      processRetailerData(data);
      if (loadingBadge) loadingBadge.classList.remove('show');
    })
    .catch(() => {
      if (!cachedData) showDemoPins();
      if (loadingBadge) loadingBadge.classList.remove('show');
    });
}

function processRetailerData(data) {
  retailerMarkers.forEach(m => mainMap.removeLayer(m.marker));
  retailerMarkers = [];
  const retailers = data.retailers || [];
  allRetailersData = retailers;
  
  if (typeof initOrUpdateFuse === 'function') {
    initOrUpdateFuse();
  }
  
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
  
  if (typeof initOrUpdateFuse === 'function') {
    initOrUpdateFuse();
  }
  
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
      SRLoader.showOverlay('দোকানের পূর্বের অর্ডার লোড হচ্ছে...', 'অনুগ্রহ করে অপেক্ষা করুন...');
      fetch(`${BASE_URL}/sr/api/today-order?retailer_id=${ret.id}`)
        .then(res => res.json())
        .then(data => {
          SRLoader.hideOverlay();
          if (data.success) {
            cartsByRetailer[ret.id] = data.items;
            ret.has_order_today = false; // allow editing
            openRetailerCartSheet(ret);
          } else {
            showMiniToast('❌ ' + (data.message || 'Error fetching order details'), true);
          }
        })
        .catch(() => {
          SRLoader.hideOverlay();
          showMiniToast('❌ Network error', true);
        });
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
  // DOM Chunking: Limit to 15 cards to prevent TBT (Total Blocking Time) overload
  const displayRetailers = filtered.slice(0, 15);
  
  container.innerHTML = displayRetailers.map((ret, index) => {
    const distMeters = ret.calculated_dist;
    const cleanAddress = (ret.address && !ret.address.toLowerCase().includes('imported dummy')) ? ret.address.trim() : '';
    
    // Highlight if has active cart
    const hasCart = cartsByRetailer[ret.id] && cartsByRetailer[ret.id].length > 0;
    const cardClass = hasCart ? 'has-cart-card' : (ret.has_order_today ? 'visited-card' : '');
    
    const distStr = distMeters > 1000 ? `${(distMeters / 1000).toFixed(1)} km` : `${distMeters} m`;

    return `
      <div class="sr-retailer-card-new ${cardClass}" id="retailer-card-${ret.id}" onclick="handleCardClick(${JSON.stringify(ret).replace(/"/g, '&quot;')})">
        <div class="sr-card-header-row">
          <div class="flex items-center gap-2.5 min-w-0 flex-1">
            <div class="sr-card-icon-box">
              <i class="fa-solid fa-store"></i>
            </div>
            <h4 class="sr-card-name" title="${escHtml(ret.name)}">${escHtml(ret.name)}</h4>
          </div>
          <button class="sr-card-action-btn" onclick="event.stopPropagation(); handleNavigationClick(${JSON.stringify(ret).replace(/"/g, '&quot;')})" title="Order Page">
            <i class="fa-solid fa-paper-plane"></i> Order
          </button>
        </div>
        <div class="sr-card-address-row">
          <i class="fa-solid fa-location-dot"></i>
          <span>${escHtml(cleanAddress || (ret.lat && ret.lng ? `${parseFloat(ret.lat).toFixed(4)}, ${parseFloat(ret.lng).toFixed(4)}` : 'Location unavailable'))}</span>
        </div>
        <div class="sr-card-tags-row">
          <span class="sr-card-tag"><i class="fa-solid fa-person-running"></i> ${distStr}</span>
          ${ret.phone ? `<span class="sr-card-tag"><i class="fa-solid fa-phone"></i> ${escHtml(ret.phone)}</span>` : ''}
          <span class="sr-card-tag ${ret.has_order_today ? 'tag-success' : 'tag-pending'}">
            <i class="fa-solid ${ret.has_order_today ? 'fa-circle-check' : 'fa-clock'}"></i>
            ${ret.has_order_today ? 'Visited' : 'Pending'}
          </span>
        </div>
      </div>
    `;
  }).join('');

  if (filtered.length > 15) {
    container.innerHTML += `<div style="width: 100%; text-align: center; background: #fff; padding: 12px; border-radius: 12px; margin-top:8px; color: #64748b; font-size: 0.85rem; pointer-events: auto;">Showing nearest 15 of ${filtered.length}. Use map to see all.</div>`;
  }
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
      miniMap = L.map('srMiniMap', { zoomControl: false, attributionControl: false, preferCanvas: true })
        .setView([myLat, myLng], 15);
      L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
        maxZoom: 20, maxNativeZoom: 19, subdomains: ['mt0','mt1','mt2','mt3'], keepBuffer: 4, updateWhenIdle: true
      }).addTo(miniMap);
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
      fullMap = L.map('srFullMap', { zoomControl: true, attributionControl: false, preferCanvas: true })
        .setView([pinLat, pinLng], 16);
      L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
        maxZoom: 20, maxNativeZoom: 19, subdomains: ['mt0','mt1','mt2','mt3'], keepBuffer: 4, updateWhenIdle: true
      }).addTo(fullMap);
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

    const submitBtn = this.querySelector('button[type="submit"]');
    SRLoader.buttonLoading(submitBtn, 'সংরক্ষণ হচ্ছে...');

    fetch(`${BASE_URL}/sr/api/retailers/store`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name, phone, lat: pinLat, lng: pinLng })
    })
    .then(r => r.json())
    .then(d => {
      SRLoader.buttonReset(submitBtn);
      if (d.success) {
        closeSheet('addRetSheet','addRetOverlay');
        document.getElementById('addRetailerForm').reset();
        loadRetailersOnMap();
        showMiniToast(`✓ Retailer "${name}" added!`);
      } else {
        showMiniToast('❌ ' + (d.message || 'Failed to save'), true);
      }
    })
    .catch(() => {
      SRLoader.buttonReset(submitBtn);
      showMiniToast('❌ Network error', true);
    });
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
  const searchSpinner = document.getElementById('mapSearchSpinner');
  const suggestionsBox = document.getElementById('searchSuggestions');
  let searchTimeout = null;

  if (searchInput && suggestionsBox) {
    searchInput.addEventListener('input', () => {
      const q = searchInput.value.trim();
      if (q.length < 2) {
        suggestionsBox.innerHTML = '';
        suggestionsBox.classList.remove('open');
        if (searchSpinner) searchSpinner.classList.remove('show');
        return;
      }

      if (searchSpinner) searchSpinner.classList.add('show');
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        const normalizedQ = normalizeBanglish(q.toLowerCase());
        
        if (!globalFuse && allRetailersData && allRetailersData.length > 0) {
            initOrUpdateFuse();
        }

        // 1. Try Fuse.js local search first
        let localMatches = [];
        if (globalFuse) {
            localMatches = globalFuse.search(normalizedQ).map(res => res.item);
        }

        // Sort local matches by distance (nearest first)
        localMatches.forEach(ret => {
          if (ret.dist === undefined) {
            ret.dist = calculateDistance(myLat, myLng, parseFloat(ret.lat || 0), parseFloat(ret.lng || 0));
          }
        });
        localMatches.sort((a, b) => a.dist - b.dist);

        const renderMatches = (matches) => {
          if (matches.length > 0) {
            suggestionsBox.innerHTML = matches.map(ret => {
              const hasAddress = ret.address && ret.address.trim() !== '';
              return `
                <div class="sr-suggestion-item" onclick="handleSuggestionSelect(${JSON.stringify(ret).replace(/"/g, '&quot;')})">
                  <span class="sr-suggestion-title"><i class="fa-solid fa-store" style="color:#2563eb; margin-right:6px; font-size:0.8rem;"></i>${escHtml(ret.name)}</span>
                  ${hasAddress ? `<span class="sr-suggestion-desc">${escHtml(ret.address)}</span>` : ''}
                </div>
              `;
            }).join('');
          } else {
            suggestionsBox.innerHTML = `<div style="padding: 12px; color: #94a3b8; font-size: 0.82rem; text-align: center;">No matching retailers</div>`;
          }
          suggestionsBox.classList.add('open');
        };

        // Render local matches immediately to keep it fast
        renderMatches(localMatches.slice(0, 15));

        // 2. Concurrently fetch complete results from server and merge them
        fetch(`${BASE_URL}/sr/api/retailers/search?q=${encodeURIComponent(q)}`)
          .then(res => res.json())
          .then(data => {
            if (data.success && data.results) {
              const merged = [...localMatches];
              data.results.forEach(serverRet => {
                if (!merged.some(r => r.id === serverRet.id)) {
                  merged.push(serverRet);
                }
              });

              // Rank merged results based on nearest retailer first
              merged.forEach(ret => {
                if (ret.dist === undefined) {
                  ret.dist = calculateDistance(myLat, myLng, parseFloat(ret.lat || 0), parseFloat(ret.lng || 0));
                }
              });
              merged.sort((a, b) => a.dist - b.dist);

              renderMatches(merged.slice(0, 15));
            }
          })
          .catch(() => {
            if (localMatches.length === 0) {
              suggestionsBox.innerHTML = `<div style="padding: 12px; color: #ef4444; font-size: 0.82rem; text-align: center;">Search failed</div>`;
              suggestionsBox.classList.add('open');
            }
          })
          .finally(() => {
            if (searchSpinner) searchSpinner.classList.remove('show');
          });
      }, 200);
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
  
  // Directly use Nominatim for map geocoding if the user presses Enter
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

/* ── Sleek custom slate theme filter for Leaflet maps ── */
#srMap .leaflet-tile, #srMiniMap .leaflet-tile, #srFullMap .leaflet-tile {
  filter: grayscale(1) invert(0.08) contrast(1.15) brightness(0.96) saturate(0.85) !important;
}

/* ── Advanced Level Retailer Marker Design ── */
.sr-retailer-marker {
  font-family: 'Hind Siliguri', sans-serif !important;
  font-size: 0.8rem !important;
  font-weight: 700 !important;
  padding: 6px 12px !important;
  border-radius: 20px !important;
  box-shadow: 0 4px 15px rgba(15, 23, 42, 0.15) !important;
  border: 1.5px solid #2563eb !important;
  background: #ffffff !important;
  color: #0f172a !important;
}
.sr-retailer-marker i {
  color: #2563eb !important;
  margin-right: 4px;
}
.sr-retailer-marker::after {
  border-top-color: #2563eb !important;
}
.sr-retailer-marker:hover {
  background: #2563eb !important;
  color: #ffffff !important;
  border-color: #2563eb !important;
}
.sr-retailer-marker:hover i {
  color: #ffffff !important;
}

/* Active Cart Marker (Yellow Highlight) */
.sr-retailer-marker.has-cart {
  background: #fffbeb !important;
  border-color: #eab308 !important;
  color: #713f12 !important;
  box-shadow: 0 4px 20px rgba(234, 179, 8, 0.25) !important;
}
.sr-retailer-marker.has-cart i {
  color: #eab308 !important;
}
.sr-retailer-marker.has-cart::after {
  border-top-color: #eab308 !important;
}
.sr-retailer-marker.has-cart:hover {
  background: #eab308 !important;
  color: #ffffff !important;
}
.sr-retailer-marker.has-cart:hover i {
  color: #ffffff !important;
}

/* Visited Marker (Green Highlight) */
.sr-retailer-marker.already-ordered {
  background: #f0fdf4 !important;
  border-color: #10b981 !important;
  color: #14532d !important;
  box-shadow: 0 4px 20px rgba(16, 185, 129, 0.25) !important;
}
.sr-retailer-marker.already-ordered i {
  color: #10b981 !important;
}
.sr-retailer-marker.already-ordered::after {
  border-top-color: #10b981 !important;
}
.sr-retailer-marker.already-ordered:hover {
  background: #10b981 !important;
  color: #ffffff !important;
}
.sr-retailer-marker.already-ordered:hover i {
  color: #ffffff !important;
}

/* ── Advanced Level Retailer Card Design ── */
.sr-retailer-card-new {
  flex: 0 0 310px !important;
  width: 310px !important;
  background: #ffffff !important;
  border-radius: 16px !important;
  box-shadow: 0 10px 25px rgba(15, 23, 42, 0.05) !important;
  border: 1px solid #e2e8f0 !important;
  border-left: 5px solid #2563eb !important; /* Indicator bar */
  padding: 12px 14px !important;
  display: flex !important;
  flex-direction: column !important;
  align-items: stretch !important;
  gap: 8px !important;
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
}

.sr-retailer-card-new:hover {
  transform: translateY(-4px) !important;
  box-shadow: 0 16px 35px rgba(15, 23, 42, 0.12) !important;
}

/* Indicator bars for different statuses */
.sr-retailer-card-new.has-cart-card {
  border-left: 5px solid #eab308 !important;
  background: #fffbeb !important;
}
.sr-retailer-card-new.visited-card {
  border-left: 5px solid #10b981 !important;
}

/* Icon / Header Row in Card */
.sr-card-header-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

.sr-card-icon-box {
  width: 32px !important;
  height: 32px !important;
  border-radius: 8px !important;
  background: #f1f5f9 !important;
  color: #64748b !important;
  box-shadow: none !important;
  font-size: 0.95rem !important;
}
.has-cart-card .sr-card-icon-box {
  background: #fef9c3 !important;
  color: #ca8a04 !important;
}
.visited-card .sr-card-icon-box {
  background: #dcfce7 !important;
  color: #15803d !important;
}

.sr-card-name {
  font-size: 0.95rem !important;
  font-weight: 800 !important;
  color: #0f172a !important;
  font-family: 'Hind Siliguri', sans-serif !important;
}

/* Action button inside card */
.sr-card-action-btn {
  background: #2563eb !important;
  color: #ffffff !important;
  font-size: 0.75rem !important;
  font-weight: 700 !important;
  padding: 6px 12px !important;
  border-radius: 8px !important;
  box-shadow: 0 4px 10px rgba(37, 99, 235, 0.18) !important;
  transition: all 0.2s !important;
}
.sr-card-action-btn:hover {
  background: #1d4ed8 !important;
  box-shadow: 0 6px 15px rgba(37, 99, 235, 0.25) !important;
}

/* Address styling */
.sr-card-address-row {
  font-size: 0.75rem !important;
  color: #64748b !important;
  margin-top: 1px;
}
.sr-card-address-row i {
  color: #94a3b8 !important;
}

/* Tags spacing and style */
.sr-card-tags-row {
  margin-top: 4px !important;
  gap: 6px !important;
}
.sr-card-tag {
  background: #f8fafc !important;
  border: 1px solid #e2e8f0 !important;
  color: #64748b !important;
  border-radius: 6px !important;
  padding: 3px 8px !important;
  font-weight: 700 !important;
}
.sr-card-tag i {
  font-size: 0.65rem;
}
.sr-card-tag.tag-success {
  background: #ecfdf5 !important;
  color: #047857 !important;
  border-color: #a7f3d0 !important;
}
.sr-card-tag.tag-pending {
  background: #f1f5f9 !important;
  color: #475569 !important;
  border-color: #cbd5e1 !important;
}
</style>
  border-color: #cbd5e1 !important;
}
</style>
