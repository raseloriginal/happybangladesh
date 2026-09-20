<?php 
$pageTitle = 'দোকান লিস্ট'; 

$formatDist = function($meters) {
    if ($meters === null || $meters === '') return null;
    $m = floatval($meters);
    if ($m < 1000) return round($m) . 'm';
    return number_format($m / 1000, 1) . 'km';
};
$cardPalettes = [
    '#1e40af', // Cobalt / Royal Blue
    '#047857', // Deep Emerald Green
    '#b91c1c', // Crimson Ruby
    '#6d28d9', // Royal Purple
    '#c2410c', // Terracotta / Burnt Orange
    '#0f766e', // Deep Teal
    '#be185d', // Rose Magenta
    '#4338ca', // Deep Indigo
    '#0369a1', // Deep Cerulean
    '#854d0e', // Bronze Amber
    '#701a75', // Deep Plum
    '#334155', // Charcoal Slate
];
?>

<style>
  .font-siliguri {
    font-family: 'Hind Siliguri', 'Inter', sans-serif;
  }
  .retailer-card {
    border-radius: 0;
    border-right: 1px solid rgba(255, 255, 255, 0.2);
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    padding: 10px 11px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 132px;
    color: #ffffff;
    cursor: pointer;
    user-select: none;
    transition: filter 0.15s ease;
    position: relative;
    overflow: hidden;
  }
  .retailer-card:hover {
    filter: brightness(1.06);
  }
  .retailer-card:active {
    filter: brightness(0.92);
  }
  .retailer-situation-icon {
    position: absolute;
    top: 9px;
    right: 10px;
    font-size: 32px;
    line-height: 1;
    pointer-events: none;
    z-index: 2;
    transition: transform 0.15s ease;
  }
  .retailer-situation-icon.status-ordered {
    color: #4ade80;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.35));
  }
  .retailer-situation-icon.status-pending {
    color: rgba(255, 255, 255, 0.65);
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.35));
  }
  .retailer-card:hover .retailer-situation-icon {
    transform: scale(1.1);
  }
  .retailer-card-title {
    font-size: 13px;
    font-weight: 700;
    line-height: 1.3;
    color: #ffffff;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-shadow: 0 1px 2px rgba(0,0,0,0.25);
  }
  .retailer-card-addr {
    font-size: 10px;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.85);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-top: 3px;
  }
  .retailer-bottom-row {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: auto;
    padding-top: 8px;
  }
  .dist-badge-solid {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 6px 4px;
    border-radius: 8px;
    font-size: 10px;
    font-weight: 700;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    color: #ffffff;
    background: rgba(0, 0, 0, 0.28);
    border: 1px solid rgba(255, 255, 255, 0.22);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .order-btn-solid {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 6px 4px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 800;
    color: #0f172a;
    background: #ffffff;
    border: 1px solid #ffffff;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.18);
    cursor: pointer;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: transform 0.1s ease, background-color 0.15s ease;
  }
  .order-btn-solid:hover {
    background-color: #f8fafc;
  }
  .order-btn-solid:active {
    transform: scale(0.95);
  }
  .order-btn-completed {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 6px 4px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 800;
    color: #047857;
    background: #ffffff;
    border: 1px solid #ffffff;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.18);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: transform 0.1s ease;
  }
  .order-btn-completed:active {
    transform: scale(0.95);
  }
</style>

<div class="max-w-2xl mx-auto font-siliguri text-slate-900 pb-4 print:p-0 print:max-w-none print:bg-white">

  <!-- Top App Bar (Header from sketch) -->
  <div class="bg-white px-3 py-3 border-b border-slate-300 flex items-center justify-between sticky top-0 z-30 select-none">
    <a href="<?= url('sr/dashboard') ?>" class="w-8 h-8 flex items-center justify-center text-slate-800 active:scale-90 transition print:hidden" title="পেছনে যান">
      <i class="fa-solid fa-arrow-left text-lg"></i>
    </a>
    
    <h1 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">
      দোকান লিস্ট
    </h1>
    
    <button type="button" id="toggleSearchBtn" onclick="toggleSearchBar()" class="w-8 h-8 flex items-center justify-center text-slate-800 active:scale-90 transition print:hidden cursor-pointer" title="সার্চ">
      <i class="fa-solid fa-magnifying-glass text-lg"></i>
    </button>
  </div>

  <!-- Search Row (Shown when Q clicked, as per sketch) -->
  <div id="searchRow" class="<?= $search !== '' ? '' : 'hidden' ?> bg-slate-50 px-3 py-2 border-b border-slate-300 print:hidden transition-all">
    <form id="retailerSearchForm" method="GET" action="<?= url('sr/retailers') ?>" class="flex gap-2">
      <div class="relative flex-1">
        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        <input type="text" id="retailerSearchInput" name="search" value="<?= h($search) ?>" placeholder="Search..." 
          class="w-full bg-white border border-slate-300 rounded px-8 py-1.5 text-xs font-bold text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-slate-800 transition" autocomplete="off">
        <a href="<?= url('sr/retailers') ?>" id="clearSearchBtn" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 text-xs <?= $search !== '' ? '' : 'hidden' ?>">
          <i class="fa-solid fa-circle-xmark"></i>
        </a>
      </div>
      <button type="submit" class="bg-slate-900 text-white font-bold text-xs px-3.5 py-1.5 rounded active:scale-95 transition">
        খুঁজুন
      </button>
    </form>
  </div>

  <!-- 2-Column Retailers Grid with Solid Color Cards (No Gap, No Border Radius) -->
  <div id="retailersContainer" class="grid grid-cols-2 border-t border-l border-white/20">
    <?php if (empty($retailers)): ?>
      <div id="emptyContainer" class="col-span-2 p-12 text-center text-slate-500 bg-white border-r border-b border-slate-300">
        <div class="w-10 h-10 rounded bg-slate-100 text-slate-400 flex items-center justify-center text-lg mx-auto mb-2">
          <i class="fa-solid fa-store"></i>
        </div>
        <p class="text-xs font-bold text-slate-800">কোনো দোকান পাওয়া যায়নি</p>
      </div>
    <?php else: ?>
      <?php foreach ($retailers as $idx => $r): ?>
        <?php 
          $hasOrder = !empty($r['has_order_today']);
          $distStr = isset($r['distance_meters']) ? $formatDist($r['distance_meters']) : null;
          $cleanAddress = (!empty($r['address']) && stripos($r['address'], 'imported dummy') === false) ? trim($r['address']) : '';
          $bgColor = $cardPalettes[$idx % count($cardPalettes)];
        ?>
        <div class="retailer-card"
             style="background-color: <?= $bgColor ?>;"
             onclick="openShop(<?= $r['id'] ?>, '<?= h(addslashes($r['name'])) ?>', '<?= h(addslashes($r['address'] ?? '')) ?>', <?= $hasOrder ? 'true' : 'false' ?>)"
             data-id="<?= $r['id'] ?>"
             data-lat="<?= $r['lat'] ?? '' ?>"
             data-lng="<?= $r['lng'] ?? '' ?>"
             data-dist="<?= $r['distance_meters'] ?? '' ?>">
          
          <!-- Big Situation Icon -->
          <div class="retailer-situation-icon <?= $hasOrder ? 'status-ordered' : 'status-pending' ?>" title="<?= $hasOrder ? 'আজকের অর্ডার সম্পন্ন' : 'নতুন অর্ডার' ?>">
            <i class="fa-solid <?= $hasOrder ? 'fa-circle-check' : 'fa-cart-shopping' ?>"></i>
          </div>

          <!-- Name Section (Line 1 & Line 2) -->
          <div class="min-w-0" style="padding-right: 38px;">
            <h3 class="retailer-card-title" title="<?= h($r['name']) ?>">
              <?= h($r['name']) ?>
            </h3>
            <?php if (!empty($cleanAddress)): ?>
              <p class="retailer-card-addr" title="<?= h($cleanAddress) ?>">
                <?= h($cleanAddress) ?>
              </p>
            <?php endif; ?>
          </div>

          <!-- Bottom Row: [ 📍 8m ] and [ Order Button ] -->
          <div class="retailer-bottom-row">
            <!-- Distance Badge [ 📍 8m ] -->
            <span class="dist-badge dist-badge-solid" title="দূরত্ব">
              <i class="fa-solid fa-location-dot" style="color:#fde047; font-size:9px; flex-shrink:0;"></i>
              <span class="dist-text truncate"><?= $distStr ?: '...' ?></span>
            </span>

            <!-- Status / Order Button -->
            <?php if ($hasOrder): ?>
              <span class="order-btn-completed" title="আজকের অর্ডার সম্পন্ন">
                <i class="fa-solid fa-circle-check" style="font-size:10px;"></i>
                <span>সম্পন্ন</span>
              </span>
            <?php else: ?>
              <span class="order-btn-solid" title="নতুন অর্ডার">
                <i class="fa-solid fa-cart-shopping" style="font-size:10px;"></i>
                <span>অর্ডার</span>
              </span>
            <?php endif; ?>
          </div>

        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
    <?php 
      $locQuery = ($lat != 0 && $lng != 0) ? "&lat={$lat}&lng={$lng}" : '';
    ?>
    <div id="paginationContainer" class="flex items-center justify-center gap-2 pt-4 print:hidden select-none">
      <?php if ($page > 1): ?>
        <a href="<?= url('sr/retailers') ?>?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?><?= $locQuery ?>" class="px-3 py-1 bg-white border border-slate-300 rounded text-xs font-bold text-slate-800 hover:bg-slate-50 active:scale-95 transition">
          <i class="fa-solid fa-angle-left"></i> আগে
        </a>
      <?php endif; ?>

      <span class="px-3 py-1 bg-slate-100 rounded text-xs font-bold text-slate-700 font-mono">
        <?= $page ?> / <?= $totalPages ?>
      </span>

      <?php if ($page < $totalPages): ?>
        <a href="<?= url('sr/retailers') ?>?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?><?= $locQuery ?>" class="px-3 py-1 bg-white border border-slate-300 rounded text-xs font-bold text-slate-800 hover:bg-slate-50 active:scale-95 transition">
          পরে <i class="fa-solid fa-angle-right"></i>
        </a>
      <?php endif; ?>
    </div>
  <?php endif; ?>

</div>

<?php include __DIR__ . '/partials/_shop_v2.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/fuse.js@7.0.0/dist/fuse.min.js" defer></script>
<script>
const BASE_URL = '<?= BASE_URL ?>';
const ALL_PRODUCTS_URL = `${BASE_URL}/sr/api/products`;
let ALL_PRODUCTS = [];

fetch(ALL_PRODUCTS_URL)
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      ALL_PRODUCTS = data.products || [];
    }
  })
  .catch(err => console.error('Failed to load products', err));

let cartsByRetailer = {};
let currentRetailer = null;
let currentProduct  = null;
let isSubmitting    = false;

const CARD_PALETTES = [
  '#1e40af', // Cobalt / Royal Blue
  '#047857', // Deep Emerald Green
  '#b91c1c', // Crimson Ruby
  '#6d28d9', // Royal Purple
  '#c2410c', // Terracotta / Burnt Orange
  '#0f766e', // Deep Teal
  '#be185d', // Rose Magenta
  '#4338ca', // Deep Indigo
  '#0369a1', // Deep Cerulean
  '#854d0e', // Bronze Amber
  '#701a75', // Deep Plum
  '#334155'  // Charcoal Slate
];
const emojis = ['📦','🛒','🏪','🎁','🧴','🍬','🧃','🍪'];

function updateAllPins() {}

function openShop(id, name, address, hasOrderToday = false) {
  const ret = { id: id, name: name, address: address, has_order_today: hasOrderToday };

  fetch(`${BASE_URL}/sr/api/log-visit`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `retailer_id=${id}`
  }).catch(() => {});

  if (ret.has_order_today) {
    showConfirmModal(`"${ret.name}" দোকানে আজ একটি অর্ডার দেওয়া হয়েছে। আপনি কি এই অর্ডার পরিবর্তন করতে চান?`, () => {
      SRLoader.showOverlay('দোকানের পূর্বের অর্ডার লোড হচ্ছে...', 'অনুগ্রহ করে অপেক্ষা করুন...');
      fetch(`${BASE_URL}/sr/api/today-order?retailer_id=${ret.id}`)
        .then(res => res.json())
        .then(data => {
          SRLoader.hideOverlay();
          if (data.success) {
            if (data.is_dispatched) {
              showMiniToast('⚠️ অর্ডারটি ইতিমধ্যে ডিসপ্যাচ হয়ে গেছে, এটি পরিবর্তন করা যাবে না। নতুন অর্ডার করুন।', true);
              currentRetailer = ret;
              openProductsForRetailer();
              return;
            }
            cartsByRetailer[ret.id] = data.items;
            currentRetailer = ret;
            openProductsForRetailer();
          } else {
            showMiniToast('❌ ' + (data.message || 'অর্ডার আনতে সমস্যা হয়েছে'), true);
          }
        })
        .catch(() => {
          SRLoader.hideOverlay();
          showMiniToast('❌ নেটওয়ার্ক ত্রুটি', true);
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

// ── Toggle Search Bar (as in sketch: when click Q -> show this) ─
function toggleSearchBar() {
  const searchRow = document.getElementById('searchRow');
  const searchInput = document.getElementById('retailerSearchInput');
  if (!searchRow) return;

  if (searchRow.classList.contains('hidden')) {
    searchRow.classList.remove('hidden');
    if (searchInput) searchInput.focus();
  } else {
    searchRow.classList.add('hidden');
  }
}

// ── Client-side Retailers Data & Distance Calculation ──────────
const allRetailers = <?= isset($allRetailers) ? json_encode($allRetailers) : '[]' ?>;
let userLat = parseFloat(localStorage.getItem('sr_last_lat')) || <?= !empty($lat) ? (float)$lat : 'null' ?>;
let userLng = parseFloat(localStorage.getItem('sr_last_lng')) || <?= !empty($lng) ? (float)$lng : 'null' ?>;

function calculateDistance(lat1, lng1, lat2, lng2) {
  const R = 6371000;
  const dLat = (lat2 - lat1) * Math.PI / 180;
  const dLng = (lng2 - lng1) * Math.PI / 180;
  const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLng / 2) * Math.sin(dLng / 2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  return Math.round(R * c);
}

function formatDistanceJs(meters) {
  if (meters === null || meters === undefined || isNaN(meters)) {
    return '...';
  }
  if (meters < 1000) {
    return `${Math.round(meters)}m`;
  }
  return `${(meters / 1000).toFixed(1)}km`;
}

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

let fuse = null;
function initRetailersFuse() {
  allRetailers.forEach(r => {
    r.normalized_name = normalizeBanglish(r.name);
  });
  fuse = new Fuse(allRetailers, {
    keys: ['name', 'normalized_name', 'phone', 'address'],
    threshold: 0.4,
    ignoreLocation: true
  });
}

function updateRetailersDistancesAndSort() {
  if (!userLat || !userLng) return;

  allRetailers.forEach(r => {
    if (r.lat && r.lng && parseFloat(r.lat) !== 0 && parseFloat(r.lng) !== 0) {
      r.distance_meters = calculateDistance(userLat, userLng, parseFloat(r.lat), parseFloat(r.lng));
    } else {
      r.distance_meters = null;
    }
  });

  allRetailers.sort((a, b) => {
    if (a.distance_meters === null && b.distance_meters === null) return a.name.localeCompare(b.name);
    if (a.distance_meters === null) return 1;
    if (b.distance_meters === null) return -1;
    return a.distance_meters - b.distance_meters;
  });
}

function renderRetailerCardHtml(r, index = 0) {
  const hasOrder = r.has_order_today > 0;
  const distStr = formatDistanceJs(r.distance_meters);
  const cleanAddress = (r.address && !r.address.toLowerCase().includes('imported dummy')) ? r.address.trim() : '';
  const escName = escHtml(r.name);
  const escAddr = escHtml(cleanAddress);
  const bgColor = CARD_PALETTES[index % CARD_PALETTES.length];

  return `
    <div class="retailer-card"
         style="background-color: ${bgColor};"
         onclick="openShop(${r.id}, '${escName.replace(/'/g, "\\'")}', '${escAddr.replace(/'/g, "\\'")}', ${hasOrder ? 'true' : 'false'})"
         data-id="${r.id}"
         data-lat="${r.lat || ''}"
         data-lng="${r.lng || ''}"
         data-dist="${r.distance_meters !== null && r.distance_meters !== undefined ? r.distance_meters : ''}">
      
      <!-- Big Situation Icon -->
      <div class="retailer-situation-icon ${hasOrder ? 'status-ordered' : 'status-pending'}" title="${hasOrder ? 'আজকের অর্ডার সম্পন্ন' : 'নতুন অর্ডার'}">
        <i class="fa-solid ${hasOrder ? 'fa-circle-check' : 'fa-cart-shopping'}"></i>
      </div>

      <div class="min-w-0" style="padding-right: 38px;">
        <h3 class="retailer-card-title" title="${escName}">
          ${escName}
        </h3>
        ${cleanAddress ? `<p class="retailer-card-addr" title="${escAddr}">${escAddr}</p>` : ''}
      </div>

      <div class="retailer-bottom-row">
        <span class="dist-badge dist-badge-solid" title="দূরত্ব">
          <i class="fa-solid fa-location-dot" style="color:#fde047; font-size:9px; flex-shrink:0;"></i>
          <span class="dist-text truncate">${distStr}</span>
        </span>

        ${hasOrder 
          ? `<span class="order-btn-completed" title="আজকের অর্ডার সম্পন্ন">
              <i class="fa-solid fa-circle-check" style="font-size:10px;"></i>
              <span>সম্পন্ন</span>
            </span>`
          : `<span class="order-btn-solid" title="নতুন অর্ডার">
              <i class="fa-solid fa-cart-shopping" style="font-size:10px;"></i>
              <span>অর্ডার</span>
            </span>`
        }
      </div>

    </div>
  `;
}

function renderCardsList(list) {
  const container = document.getElementById('retailersContainer');
  if (!container) return;

  if (!list || list.length === 0) {
    container.innerHTML = `
      <div id="emptyContainer" class="col-span-2 p-12 text-center text-slate-500 bg-white border-r border-b border-slate-300">
        <div class="w-10 h-10 rounded bg-slate-100 text-slate-400 flex items-center justify-center text-lg mx-auto mb-2">
          <i class="fa-solid fa-store"></i>
        </div>
        <p class="text-xs font-bold text-slate-800">কোনো দোকান পাওয়া যায়নি</p>
      </div>
    `;
    return;
  }

  container.innerHTML = list.map((r, idx) => renderRetailerCardHtml(r, idx)).join('');
}

function updateExistingCardsDistances() {
  const container = document.getElementById('retailersContainer');
  if (!container || !userLat || !userLng) return;

  const cards = container.querySelectorAll('.retailer-card');
  cards.forEach(card => {
    const rLat = parseFloat(card.getAttribute('data-lat'));
    const rLng = parseFloat(card.getAttribute('data-lng'));
    const distSpan = card.querySelector('.dist-text');

    if (distSpan) {
      if (rLat && rLng && !isNaN(rLat) && !isNaN(rLng) && rLat !== 0 && rLng !== 0) {
        const dist = calculateDistance(userLat, userLng, rLat, rLng);
        card.setAttribute('data-dist', dist);
        distSpan.textContent = formatDistanceJs(dist);
      } else {
        distSpan.textContent = '...';
      }
    }
  });
}

function refreshLocation() {
  if (!navigator.geolocation) return;

  navigator.geolocation.getCurrentPosition(
    (pos) => {
      userLat = pos.coords.latitude;
      userLng = pos.coords.longitude;

      localStorage.setItem('sr_last_lat', userLat);
      localStorage.setItem('sr_last_lng', userLng);
      document.cookie = `sr_last_lat=${userLat}; path=/; max-age=86400; SameSite=Lax`;
      document.cookie = `sr_last_lng=${userLng}; path=/; max-age=86400; SameSite=Lax`;

      updateRetailersDistancesAndSort();

      const searchInput = document.getElementById('retailerSearchInput');
      if (!searchInput || !searchInput.value.trim()) {
        renderCardsList(allRetailers.slice(0, 32));
        const paginationContainer = document.getElementById('paginationContainer');
        if (paginationContainer) paginationContainer.style.display = 'flex';
      } else {
        updateExistingCardsDistances();
      }
    },
    (err) => {
      console.warn('Geolocation error:', err);
    },
    { enableHighAccuracy: true, timeout: 8000, maximumAge: 10000 }
  );
}

document.addEventListener('DOMContentLoaded', () => {
  const originalContainerHTML = document.getElementById('retailersContainer') ? document.getElementById('retailersContainer').innerHTML : '';
  const paginationContainer = document.getElementById('paginationContainer');
  const searchInput = document.getElementById('retailerSearchInput');
  const searchForm = document.getElementById('retailerSearchForm');
  const clearSearchBtn = document.getElementById('clearSearchBtn');

  if (userLat && userLng) {
    updateRetailersDistancesAndSort();

    const serverHadLocation = <?= (!empty($lat) && !empty($lng)) ? 'true' : 'false' ?>;
    if (!serverHadLocation && allRetailers.length > 0) {
      renderCardsList(allRetailers.slice(0, 32));
    } else {
      updateExistingCardsDistances();
    }
  }

  refreshLocation();

  function restoreOriginalCards() {
    if (userLat && userLng && allRetailers.length > 0) {
      renderCardsList(allRetailers.slice(0, 32));
    } else {
      const container = document.getElementById('retailersContainer');
      if (container) container.innerHTML = originalContainerHTML;
    }
    if (paginationContainer) paginationContainer.style.display = 'flex';
    if (clearSearchBtn) clearSearchBtn.classList.add('hidden');
  }

  if (searchForm) {
    searchForm.addEventListener('submit', (e) => {
      e.preventDefault();
    });
  }

  if (searchInput) {
    searchInput.addEventListener('input', () => {
      const q = searchInput.value.trim();
      if (!q) {
        restoreOriginalCards();
        return;
      }

      if (clearSearchBtn) clearSearchBtn.classList.remove('hidden');
      if (paginationContainer) paginationContainer.style.display = 'none';

      if (!fuse) {
        initRetailersFuse();
      }

      const normalizedQ = normalizeBanglish(q.toLowerCase());
      const results = fuse.search(normalizedQ);
      const matchedRetailers = results.map(res => res.item);

      matchedRetailers.sort((a, b) => {
        if (a.distance_meters === null && b.distance_meters === null) return 0;
        if (a.distance_meters === null) return 1;
        if (b.distance_meters === null) return -1;
        return a.distance_meters - b.distance_meters;
      });

      renderCardsList(matchedRetailers);
    });
  }

  if (clearSearchBtn) {
    clearSearchBtn.addEventListener('click', (e) => {
      e.preventDefault();
      if (searchInput) searchInput.value = '';
      restoreOriginalCards();
    });
  }
});
</script>
