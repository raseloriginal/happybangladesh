<?php $pageTitle = 'DSR Tracking'; ?>

<?php
// ── SR list for dropdowns ──────────────────────────────────────────
$dsrList = $dsrList ?? [];
?>

<style>
/* ── Tab system ─────────────────────────────────────────────── */
.track-tab-btn {
    display: flex; align-items: center; gap: 0.5rem;
    padding: 0.6rem 1.25rem; border-radius: 0.625rem;
    font-size: 0.8125rem; font-weight: 700; letter-spacing: 0.02em;
    cursor: pointer; transition: all 0.2s;
    border: none; background: transparent; color: #94a3b8;
}
.track-tab-btn.active {
    background: #fff; color: #2563eb;
    box-shadow: 0 1px 6px 0 rgba(37,99,235,.13);
}
.track-tab-btn:not(.active):hover { color: #1e40af; background: rgba(255,255,255,.5); }
.track-tab-panel { display: none; }
.track-tab-panel.active { display: block; }

/* ── Map container & styling ────────────────────────────────── */
.map-wrap {
    position: relative;
    border-radius: 0.875rem;
    overflow: hidden;
    box-shadow: 0 4px 20px 0 rgba(0,0,0,.06);
    border: 1px solid #e2e8f0;
}
.map-box { height: 550px; width: 100%; background: #f1f5f9; }

/* ── Map Custom Toggles ─────────────────────────────────────── */
.map-provider-toggle {
    position: absolute; top: 12px; right: 12px; z-index: 900;
    display: flex; gap: 2px; background: rgba(255, 255, 255, 0.88);
    backdrop-filter: blur(8px); padding: 3px; border-radius: 0.625rem;
    border: 1px solid rgba(226, 232, 240, 0.8);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}
.map-provider-btn {
    padding: 0.35rem 0.75rem; font-size: 0.6875rem; font-weight: 700;
    color: #64748b; border-radius: 0.5rem; border: none;
    background: transparent; cursor: pointer; transition: all 0.18s;
}
.map-provider-btn:hover { color: #1e293b; background: rgba(0, 0, 0, 0.04); }
.map-provider-btn.active { background: #fff; color: #2563eb; box-shadow: 0 1px 4px rgba(0,0,0,0.1); }

/* Marker Smooth Sliding Transition */
#liveMap:not(.map-moving) .leaflet-marker-icon {
    transition: transform 0.8s cubic-bezier(0.25, 0.8, 0.25, 1);
}

/* ── SR cards sidebar ────────────────────────────────────────── */
#liveDsrList::-webkit-scrollbar, .scrollbar-styled::-webkit-scrollbar {
    width: 6px; height: 6px;
}
#liveDsrList::-webkit-scrollbar-track, .scrollbar-styled::-webkit-scrollbar-track {
    background: transparent;
}
#liveDsrList::-webkit-scrollbar-thumb, .scrollbar-styled::-webkit-scrollbar-thumb {
    background: #cbd5e1; border-radius: 4px;
}
#liveDsrList::-webkit-scrollbar-thumb:hover, .scrollbar-styled::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.dsr-card {
    display: flex; align-items: flex-start; gap: 0.75rem;
    padding: 0.875rem 1rem; border-radius: 0.75rem;
    background: #fff; border: 1.5px solid #e2e8f0;
    transition: all 0.18s; cursor: pointer;
}
.dsr-card:hover, .dsr-card.selected { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.08); }
.dsr-card.selected { background: #f8fafc; }
.dsr-card .avatar {
    width: 2.25rem; height: 2.25rem; border-radius: 0.625rem;
    background: linear-gradient(135deg,#2563eb,#7c3aed);
    color: #fff; font-weight: 800; font-size: 0.75rem;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    box-shadow: 0 2px 4px rgba(37,99,235,.15);
}
.dsr-card .dsr-name  { font-size: 0.8125rem; font-weight: 700; color: #1e293b; }
.dsr-card .dsr-meta  { font-size: 0.7rem; color: #64748b; margin-top: 0.1rem; line-height: 1.4; }
.dsr-card .badge-online  { background: #d1fae5; color: #065f46; }
.dsr-card .badge-offline { background: #fee2e2; color: #991b1b; }
.dsr-card .badge { font-size: 0.6875rem; font-weight: 700; padding: 0.15rem 0.5rem; border-radius: 999px; }

/* ── Filter bar ─────────────────────────────────────────────── */
.filter-bar {
    display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;
    padding: 0.875rem 1rem; background: #f8fafc;
    border-bottom: 1px solid #e2e8f0; border-radius: 0.75rem 0.75rem 0 0;
}
.filter-bar select, .filter-bar input[type=date], .filter-bar input[type=time] {
    font-size: 0.8125rem; font-weight: 500; color: #1e293b;
    padding: 0.4rem 0.75rem; border: 1.5px solid #e2e8f0;
    border-radius: 0.5rem; background: #fff; outline: none;
    transition: border-color .15s;
}
.filter-bar select:focus, .filter-bar input:focus { border-color: #2563eb; }
.filter-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 0.25rem;
    padding: 0.45rem 1rem; border-radius: 0.5rem; font-size: 0.8125rem; font-weight: 700;
    cursor: pointer; border: none; transition: all .15s;
}
.filter-btn-primary { background: #2563eb; color: #fff; }
.filter-btn-primary:hover { background: #1d4ed8; }
.filter-btn-secondary { background: #e2e8f0; color: #475569; }
.filter-btn-secondary:hover { background: #cbd5e1; }

/* ── Info overlay on map ─────────────────────────────────────── */
#liveMapInfo, #historyMapInfo {
    position: absolute; top: 12px; left: 12px; z-index: 900;
    background: rgba(255,255,255,.9); backdrop-filter: blur(6px);
    border-radius: 0.625rem; padding: 0.6rem 0.875rem;
    font-size: 0.75rem; color: #1e293b; pointer-events: none;
    box-shadow: 0 4px 12px rgba(0,0,0,.08);
    border: 1px solid rgba(226,232,240,0.8);
    display: flex; align-items: center; gap: 0.35rem;
}

/* ── History route legend ────────────────────────────────────── */
.route-legend { display: flex; align-items: center; gap: 1.25rem; font-size: 0.725rem; color: #475569; }
.route-legend span { display: flex; align-items: center; gap: 0.35rem; }
.dot-start { width:10px;height:10px;border-radius:50%;background:#10b981;flex-shrink:0; border: 2px solid #fff; box-shadow: 0 0 0 2px rgba(16,185,129,0.3); }
.dot-end   { width:10px;height:10px;border-radius:50%;background:#ef4444;flex-shrink:0; border: 2px solid #fff; box-shadow: 0 0 0 2px rgba(239,68,68,0.3); }
.dot-gap   { width:20px;height:3px;border-top:2.5px dashed #94a3b8;flex-shrink:0; }
.dot-route { width:20px;height:3px;background:#2563eb;flex-shrink:0; border-radius:2px; }

/* ── History table ─────────────────────────────────────────── */
.hist-table { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
.hist-table th { background: #f1f5f9; padding: 0.6rem 0.75rem; text-align: left; font-weight: 700; color: #475569; font-size: 0.75rem; }
.hist-table td { padding: 0.55rem 0.75rem; border-bottom: 1px solid #f1f5f9; color: #1e293b; transition: background-color 0.15s; }
.hist-table tr:last-child td { border-bottom: none; }
.hist-table tbody tr { cursor: pointer; }
.hist-table tbody tr:hover td { background: #f8fafc; }

/* ── Custom Marker Initial styling ───────────────────────────── */
.sr-marker-pin {
    box-shadow: 0 3px 8px rgba(0,0,0,0.15);
    transform-origin: bottom center;
}
.sr-marker-pin:hover {
    transform: scale(1.1);
}
</style>

<!-- ── Page Header ────────────────────────────────────────────── -->
<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-2xl font-black text-gray-900 tracking-tight">DSR Tracking</h1>
    <p class="text-sm text-gray-500 mt-0.5">Real-time location monitoring &amp; movement history for Delivery Sales Representatives</p>
  </div>
  <div class="flex items-center gap-2 text-sm text-gray-500 bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm">
    <i class="fa-solid fa-satellite-dish text-blue-500 animate-pulse"></i>
    <span>Auto-refresh in <strong id="refreshCountdown" class="text-blue-600 font-bold font-mono">60</strong>s</span>
  </div>
</div>

<!-- ── Tab Switcher ───────────────────────────────────────────── -->
<div class="bg-slate-100 rounded-xl p-1 inline-flex gap-1 mb-5">
  <button id="tab-live-btn" class="track-tab-btn active" onclick="switchTab('live')">
    <i class="fa-solid fa-circle text-emerald-500 text-[9px] animate-pulse"></i> DSR Live Location
  </button>
  <button id="tab-hist-btn" class="track-tab-btn" onclick="switchTab('history')">
    <i class="fa-solid fa-clock-rotate-left text-[13px]"></i> DSR Location History
  </button>
</div>

<!-- ══════════════════════════════════════════════════════════════
     TAB 1: LIVE LOCATION
══════════════════════════════════════════════════════════════ -->
<div id="tab-live" class="track-tab-panel active">
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    <!-- SR list sidebar -->
    <div class="space-y-2 lg:max-h-[600px] lg:overflow-y-auto pr-1" id="liveDsrList">
      <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 px-1 flex items-center justify-between">
        <span>Active DSRs</span>
        <span class="bg-blue-50 text-blue-700 px-2 py-0.5 rounded text-[10px]" id="liveOnlineCount">—</span>
      </div>
      <div id="liveDsrCards" class="space-y-2"></div>
    </div>

    <!-- Map -->
    <div class="lg:col-span-2">
      <div class="map-wrap">
        <div id="liveMapInfo">
          <i class="fa-solid fa-satellite-dish text-blue-500 animate-pulse"></i>
          <span id="liveMapStatus" class="font-medium">Loading locations…</span>
        </div>
        
        <!-- Map Style Toggle Overlay -->
        <div class="map-provider-toggle" id="live-provider-toggle">
          <button class="map-provider-btn active" data-provider="osm" onclick="selectMapProvider('osm')">OSM</button>
          <button class="map-provider-btn" data-provider="google_road" onclick="selectMapProvider('google_road')">Google Road</button>
          <button class="map-provider-btn" data-provider="google_satellite" onclick="selectMapProvider('google_satellite')">Satellite</button>
        </div>

        <div id="liveMap" class="map-box"></div>
      </div>
      
      <!-- Selected SR detail -->
      <div id="liveDetailCard" class="hidden mt-3 bg-white rounded-xl border border-blue-100 p-4 shadow-sm transition-all duration-200">
        <div class="flex items-center justify-between mb-2">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-600 to-violet-600 text-white flex items-center justify-center font-black text-xs shadow-sm" id="dcAvatar"></div>
            <span class="font-bold text-gray-900 text-sm" id="dcName"></span>
          </div>
          <span id="dcStatusBadge" class="badge"></span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
          <div><div class="text-gray-400 mb-0.5">Coordinates</div><div class="font-mono text-gray-700 font-semibold" id="dcCoords"></div></div>
          <div><div class="text-gray-400 mb-0.5">Last Update</div><div class="font-semibold text-gray-700" id="dcTime"></div></div>
          <div class="col-span-2"><div class="text-gray-400 mb-0.5">Address</div><div class="font-semibold text-gray-700 truncate" id="dcAddress"></div></div>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     TAB 2: LOCATION HISTORY
══════════════════════════════════════════════════════════════ -->
<div id="tab-history" class="track-tab-panel">
  <!-- Filter bar -->
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm mb-4 overflow-hidden">
    <div class="filter-bar">
      <select id="histDsrFilter">
        <option value="">— Select DSR —</option>
        <?php foreach ($dsrList as $dsr): ?>
        <option value="<?= $dsr['id'] ?>"><?= h($dsr['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <input type="date" id="histDateFilter" value="<?= date('Y-m-d') ?>">
      <input type="time" id="histTimeFrom" value="00:00">
      <span class="text-gray-400 text-sm">to</span>
      <input type="time" id="histTimeTo" value="23:59">
      
      <button class="filter-btn filter-btn-primary" onclick="loadHistory()">
        <i class="fa-solid fa-magnifying-glass"></i> Load Route
      </button>
      <button class="filter-btn filter-btn-secondary" onclick="clearHistory()">
        <i class="fa-solid fa-xmark"></i> Clear
      </button>
      <button id="playRouteBtn" class="filter-btn text-white font-bold" style="display: none; background: #10b981;" onclick="toggleRouteAnimation()">
        <i class="fa-solid fa-play"></i> Play Route
      </button>
      
      <div class="ml-auto route-legend">
        <span><div class="dot-start"></div> Start</span>
        <span><div class="dot-end"></div> End</span>
        <span><div class="dot-route"></div> Route</span>
        <span><div class="dot-gap"></div> Gap (&gt;2km)</span>
      </div>
    </div>

    <!-- History map -->
    <div class="map-wrap" style="border-radius:0;border:none;border-top:1px solid #e2e8f0;">
      <div id="historyMapInfo" class="hidden">
        <i class="fa-solid fa-spinner fa-spin text-blue-500 mr-1"></i>
        <span id="histMapStatus"></span>
      </div>
      
      <!-- Map Style Toggle Overlay -->
      <div class="map-provider-toggle" id="hist-provider-toggle">
        <button class="map-provider-btn active" data-provider="osm" onclick="selectMapProvider('osm')">OSM</button>
        <button class="map-provider-btn" data-provider="google_road" onclick="selectMapProvider('google_road')">Google Road</button>
        <button class="map-provider-btn" data-provider="google_satellite" onclick="selectMapProvider('google_satellite')">Satellite</button>
      </div>

      <div id="histMap" class="map-box"></div>
    </div>
  </div>

  <!-- History table -->
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
      <div class="font-bold text-sm text-gray-800">
        <i class="fa-solid fa-list text-blue-500 mr-2"></i>Location Records
        <span class="text-gray-400 font-normal text-xs ml-1" id="histRecordCount"></span>
      </div>
    </div>
    <div class="overflow-x-auto max-h-72 overflow-y-auto scrollbar-styled" id="histTableContainer">
      <table class="hist-table">
        <thead class="sticky top-0 z-10">
          <tr>
            <th>#</th>
            <th>Time</th>
            <th>Latitude</th>
            <th>Longitude</th>
            <th>Address</th>
            <th>Accuracy</th>
          </tr>
        </thead>
        <tbody id="histTableBody">
          <tr><td colspan="6" class="text-center text-gray-400 py-6">Select a DSR and date to load history</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     SCRIPTS
══════════════════════════════════════════════════════════════ -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

<script>
// ── Config ────────────────────────────────────────────────────
const BASE = '<?= BASE_URL ?>';
const API_LIVE    = BASE + '/admin/api/dsr-tracking/live';
const API_HISTORY = BASE + '/admin/api/dsr-tracking/history';
const REFRESH_SEC = 60;

// ── Tab switching ─────────────────────────────────────────────
let activeTab = 'live';
function switchTab(tab) {
  activeTab = tab;
  document.querySelectorAll('.track-tab-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.track-tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-' + tab).classList.add('active');
  document.getElementById('tab-' + tab + '-btn').classList.add('active');
  
  // Re-calculate sizes immediately and with delay to prevent gray tile glitches in Leaflet
  if (tab === 'live') { liveMap.invalidateSize(); }
  if (tab === 'history') { histMap.invalidateSize(); }
  setTimeout(() => {
    if (tab === 'live') { liveMap.invalidateSize(); }
    if (tab === 'history') { histMap.invalidateSize(); }
  }, 200);
}

// ── Countdown ─────────────────────────────────────────────────
let countdown = REFRESH_SEC;
setInterval(() => {
  countdown--;
  document.getElementById('refreshCountdown').textContent = countdown;
  if (countdown <= 0) {
    countdown = REFRESH_SEC;
    fetchLive();
  }
}, 1000);

// ── Shared Leaflet Layer setup ────────────────────────────────
const liveTileLayers = {
  osm: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors', maxZoom: 19
  }),
  google_road: L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
    attribution: '© Google Maps', maxZoom: 20
  }),
  google_satellite: L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
    attribution: '© Google Maps', maxZoom: 20
  })
};

const histTileLayers = {
  osm: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors', maxZoom: 19
  }),
  google_road: L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
    attribution: '© Google Maps', maxZoom: 20
  }),
  google_satellite: L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
    attribution: '© Google Maps', maxZoom: 20
  })
};

// ── Initialization ────────────────────────────────────────────
const liveMap = L.map('liveMap', { zoomControl: true }).setView([23.8103, 90.4125], 11);
const histMap = L.map('histMap', { zoomControl: true }).setView([23.8103, 90.4125], 11);

// Robust ResizeObserver to fix Leaflet size issues on hidden tabs
const mapResizeObserver = new ResizeObserver(() => {
  liveMap.invalidateSize();
  histMap.invalidateSize();
});
mapResizeObserver.observe(document.getElementById('liveMap'));
mapResizeObserver.observe(document.getElementById('histMap'));

// Prevent transitions during map moves to ensure snappy zooms/pans
liveMap.on('movestart', () => { document.getElementById('liveMap').classList.add('map-moving'); });
liveMap.on('moveend',   () => { document.getElementById('liveMap').classList.remove('map-moving'); });

// Sync map provider from localStorage
const storedProvider = localStorage.getItem('dsr_tracking_map_provider') || 'osm';
setMapProvider('all', storedProvider);

function setMapProvider(mapType, providerKey) {
  localStorage.setItem('dsr_tracking_map_provider', providerKey);
  
  if (mapType === 'live' || mapType === 'all') {
    Object.values(liveTileLayers).forEach(layer => {
      if (liveMap.hasLayer(layer)) liveMap.removeLayer(layer);
    });
    liveTileLayers[providerKey].addTo(liveMap);
    
    // Update live toggle buttons style
    document.querySelectorAll('#live-provider-toggle .map-provider-btn').forEach(btn => {
      btn.classList.toggle('active', btn.dataset.provider === providerKey);
    });
  }
  
  if (mapType === 'history' || mapType === 'all') {
    Object.values(histTileLayers).forEach(layer => {
      if (histMap.hasLayer(layer)) histMap.removeLayer(layer);
    });
    histTileLayers[providerKey].addTo(histMap);
    
    // Update hist toggle buttons style
    document.querySelectorAll('#hist-provider-toggle .map-provider-btn').forEach(btn => {
      btn.classList.toggle('active', btn.dataset.provider === providerKey);
    });
  }
}

function selectMapProvider(providerKey) {
  setMapProvider('all', providerKey);
}

// ══════════════════════════════════════════════════════════════
//  TAB 1 — LIVE MAP
// ══════════════════════════════════════════════════════════════
const liveMarkers = {};    // keyed by dsr_id
let   selectedDsrId = null;

function dsrInitials(name) {
  return (name || '?').trim().split(/\s+/).map(w => w[0]).join('').toUpperCase().slice(0, 2);
}

function timeAgo(isoStr) {
  if (!isoStr) return '—';
  // Standardize space to ISO date format divider
  const dateStr = isoStr.replace(' ', 'T');
  let diff = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000);
  if (diff < 0) diff = 0;
  if (diff < 5) return 'Just now';
  if (diff < 60) return diff + 's ago';
  if (diff < 3600) return Math.floor(diff/60) + 'm ago';
  if (diff < 86400) return Math.floor(diff/3600) + 'h ago';
  return Math.floor(diff/86400) + 'd ago';
}

// Tick relative time pings locally every 10s to keep relative time up-to-date
setInterval(() => {
  document.querySelectorAll('.time-ago-text').forEach(el => {
    const timeStr = el.dataset.time;
    if (timeStr) {
      el.textContent = timeAgo(timeStr);
    }
  });
}, 10000);

function makeDsrIcon(isOnline, name) {
  const color = isOnline ? '#10b981' : '#64748b';
  const shadowColor = isOnline ? 'rgba(16,185,129,0.35)' : 'rgba(100,116,139,0.25)';
  const initials = dsrInitials(name);
  const html = `<div class="sr-marker-pin" style="position: relative; width: 32px; height: 32px; border-radius: 50%; background: ${color}; border: 2px solid #fff; box-shadow: 0 3px 8px ${shadowColor}; display: flex; align-items: center; justify-content: center; transition: background-color 0.3s;">
    <span style="color: #fff; font-size: 10px; font-weight: 800; font-family: 'Inter', sans-serif;">${initials}</span>
    <div style="position: absolute; bottom: -5px; left: 50%; transform: translateX(-50%); width: 0; height: 0; border-left: 5px solid transparent; border-right: 5px solid transparent; border-top: 6px solid ${color}; transition: border-top-color 0.3s;"></div>
  </div>`;
  return L.divIcon({
    html: html, className: '', iconSize: [32, 38], iconAnchor: [16, 38], popupAnchor: [0, -38]
  });
}

function fetchLive() {
  document.getElementById('liveMapStatus').innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i>Refreshing…';
  fetch(API_LIVE)
    .then(r => r.json())
    .then(data => {
      if (!data.success) return;
      renderLive(data.dsrs);
      document.getElementById('liveMapStatus').innerHTML =
        '<i class="fa-solid fa-circle text-emerald-500 text-[8px] mr-1"></i>Live: ' + new Date().toLocaleTimeString();
    })
    .catch(() => {
      document.getElementById('liveMapStatus').textContent = 'Connection error';
    });
}

function escHtml(str) {
  const d = document.createElement('div'); d.textContent = str; return d.innerHTML;
}

function updateCard(card, dsr) {
  const onl = dsr.is_online;
  const badge = card.querySelector('.badge');
  badge.className = `badge ${onl ? 'badge-online' : 'badge-offline'}`;
  badge.textContent = onl ? 'Online' : 'Offline';
  
  const meta = card.querySelector('.dsr-meta');
  const latLngText = dsr.lat ? `${dsr.lat.toFixed(5)}, ${dsr.lng.toFixed(5)}` : 'No location yet';
  const addressText = dsr.address ? (dsr.address.length > 40 ? escHtml(dsr.address.slice(0, 40)) + '…' : escHtml(dsr.address)) : '—';
  const agoText = dsr.recorded_at ? timeAgo(dsr.recorded_at) : 'Never';
  
  meta.innerHTML = `
    ${latLngText}<br>
    ${addressText}<br>
    <span class="text-gray-400 font-semibold time-ago-text" data-time="${dsr.recorded_at || ''}">${agoText}</span>
  `;
}

function renderLive(dsrs) {
  // Recalculate dimensions in case container was hidden during initialization
  liveMap.invalidateSize();

  const onlineCount = dsrs.filter(s => s.is_online).length;
  document.getElementById('liveOnlineCount').textContent = onlineCount + ' Online / ' + dsrs.length + ' Total';

  const container = document.getElementById('liveDsrCards');
  const currentIds = new Set(dsrs.map(s => s.id));
  
  // DOM Diffing - Clean up cards that no longer exist
  const existingCards = container.querySelectorAll('.dsr-card');
  existingCards.forEach(card => {
    const id = parseInt(card.dataset.id);
    if (!currentIds.has(id)) {
      card.remove();
    }
  });

  // Clean up markers that no longer exist
  Object.keys(liveMarkers).forEach(idStr => {
    const id = parseInt(idStr);
    if (!currentIds.has(id)) {
      liveMap.removeLayer(liveMarkers[id]);
      delete liveMarkers[id];
    }
  });

  // Create or Update elements
  dsrs.forEach(dsr => {
    const init = dsrInitials(dsr.name);
    const onl  = dsr.is_online;
    
    // 1. Get or create Card
    let card = container.querySelector(`.dsr-card[data-id="${dsr.id}"]`);
    if (!card) {
      card = document.createElement('div');
      card.className = 'dsr-card';
      card.dataset.id = dsr.id;
      card.innerHTML = `
        <div class="avatar">${init}</div>
        <div class="flex-1 min-w-0">
          <div class="dsr-name">${escHtml(dsr.name)}</div>
          <div class="dsr-meta"></div>
        </div>
        <span class="badge"></span>
      `;
      card.addEventListener('click', () => selectDsr(dsr));
      container.appendChild(card);
    }
    
    // Toggle active state without layout jump
    card.classList.toggle('selected', selectedDsrId === dsr.id);
    updateCard(card, dsr);

    // 2. Map Marker (Smooth transition on coordinate updates)
    if (dsr.lat && dsr.lng) {
      if (liveMarkers[dsr.id]) {
        liveMarkers[dsr.id].setLatLng([dsr.lat, dsr.lng]).setIcon(makeDsrIcon(onl, dsr.name));
      } else {
        liveMarkers[dsr.id] = L.marker([dsr.lat, dsr.lng], { icon: makeDsrIcon(onl, dsr.name) })
          .addTo(liveMap)
          .on('click', () => selectDsr(dsr));
      }
      liveMarkers[dsr.id].bindTooltip(dsr.name, { permanent: false, direction: 'top' });
    }
  });
}

function selectDsr(dsr) {
  selectedDsrId = dsr.id;
  document.querySelectorAll('.dsr-card').forEach(c => {
    c.classList.toggle('selected', parseInt(c.dataset.id) === dsr.id);
  });
  if (dsr.lat && dsr.lng) {
    liveMap.setView([dsr.lat, dsr.lng], 15, { animate: true });
    if (liveMarkers[dsr.id]) liveMarkers[dsr.id].openTooltip();
  }
  
  // Fill detail card
  const dc = document.getElementById('liveDetailCard');
  dc.classList.remove('hidden');
  document.getElementById('dcAvatar').textContent   = dsrInitials(dsr.name);
  document.getElementById('dcName').textContent     = dsr.name;
  document.getElementById('dcCoords').textContent   = dsr.lat ? dsr.lat.toFixed(6)+', '+dsr.lng.toFixed(6) : '—';
  document.getElementById('dcTime').textContent     = dsr.recorded_at ? new Date(dsr.recorded_at).toLocaleString() : 'Never';
  document.getElementById('dcAddress').textContent  = dsr.address || 'No address available';
  
  const badge = document.getElementById('dcStatusBadge');
  badge.className = 'badge ' + (dsr.is_online ? 'badge-online' : 'badge-offline');
  badge.textContent = dsr.is_online ? 'Online' : 'Offline';
}

// ══════════════════════════════════════════════════════════════
//  TAB 2 — HISTORY MAP & PLAYER
// ══════════════════════════════════════════════════════════════
const HIST_GAP_KM = 2.0; 
let histLayers     = [];
let histMarkers    = [];
let historyPoints  = [];
let animInterval   = null;
let animIndex      = 0;
let animMarker     = null;

function haversineKm(lat1, lng1, lat2, lng2) {
  const R = 6371;
  const dLat = (lat2-lat1) * Math.PI/180;
  const dLng = (lng2-lng1) * Math.PI/180;
  const a = Math.sin(dLat/2)**2 + Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLng/2)**2;
  return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}

function loadHistory() {
  const dsrId  = document.getElementById('histDsrFilter').value;
  const date   = document.getElementById('histDateFilter').value;
  const tFrom  = document.getElementById('histTimeFrom').value;
  const tTo    = document.getElementById('histTimeTo').value;

  if (!dsrId) { alert('Please select a DSR first.'); return; }
  if (!date) { alert('Please select a date.'); return; }

  stopRouteAnimation();
  const info = document.getElementById('historyMapInfo');
  info.classList.remove('hidden');
  document.getElementById('histMapStatus').innerHTML = 'Loading route…';

  const params = new URLSearchParams({ dsr_id: dsrId, date, time_from: tFrom, time_to: tTo });
  fetch(API_HISTORY + '?' + params.toString())
    .then(r => r.json())
    .then(data => {
      if (!data.success) {
        document.getElementById('histMapStatus').textContent = 'Error loading data';
        return;
      }
      renderHistory(data.points);
    })
    .catch(() => {
      document.getElementById('histMapStatus').textContent = 'Connection error';
    });
}

function clearHistory() {
  stopRouteAnimation();
  
  histLayers.forEach(l => histMap.removeLayer(l));
  histLayers = [];
  histMarkers = [];
  historyPoints = [];
  
  if (animMarker) {
    histMap.removeLayer(animMarker);
    animMarker = null;
  }
  
  document.getElementById('histTableBody').innerHTML =
    '<tr><td colspan="6" class="text-center text-gray-400 py-6">Select a DSR and date to load history</td></tr>';
  document.getElementById('histRecordCount').textContent = '';
  document.getElementById('historyMapInfo').classList.add('hidden');
  document.getElementById('playRouteBtn').style.display = 'none';
}

function renderHistory(points) {
  // Recalculate dimensions in case container was hidden during initialization
  histMap.invalidateSize();

  // Remove old layers
  histLayers.forEach(l => histMap.removeLayer(l));
  histLayers = [];
  histMarkers = [];
  historyPoints = points;
  
  if (animMarker) {
    histMap.removeLayer(animMarker);
    animMarker = null;
  }

  document.getElementById('histRecordCount').textContent = '(' + points.length + ' records)';

  if (!points.length) {
    document.getElementById('histMapStatus').textContent = 'No location data for this period';
    document.getElementById('histTableBody').innerHTML =
      '<tr><td colspan="6" class="text-center text-gray-400 py-6">No records found</td></tr>';
    document.getElementById('playRouteBtn').style.display = 'none';
    return;
  }

  document.getElementById('playRouteBtn').style.display = 'inline-flex';
  document.getElementById('playRouteBtn').innerHTML = '<i class="fa-solid fa-play"></i> Play Route';
  
  document.getElementById('histMapStatus').textContent =
    points.length + ' points — ' + new Date(points[0].recorded_at.replace(' ', 'T')).toLocaleTimeString() +
    ' → ' + new Date(points[points.length-1].recorded_at.replace(' ', 'T')).toLocaleTimeString();

  // Build segments on GPS gaps > HIST_GAP_KM
  const segments = [];
  let   seg = [points[0]];
  for (let i = 1; i < points.length; i++) {
    const prev = points[i-1], curr = points[i];
    const dist = haversineKm(parseFloat(prev.lat), parseFloat(prev.lng), parseFloat(curr.lat), parseFloat(curr.lng));
    if (dist > HIST_GAP_KM) {
      segments.push(seg);
      seg = [curr];
    } else {
      seg.push(curr);
    }
  }
  segments.push(seg);

  // Draw continuous route segments
  segments.forEach(s => {
    if (s.length < 2) return;
    const latlngs = s.map(p => [parseFloat(p.lat), parseFloat(p.lng)]);
    const poly = L.polyline(latlngs, { color: '#2563eb', weight: 4, opacity: 0.85 }).addTo(histMap);
    histLayers.push(poly);
  });

  // Draw grey dashed lines representing long GPS gaps
  for (let i = 0; i < segments.length - 1; i++) {
    const lastPt = segments[i][segments[i].length - 1];
    const firstPt = segments[i+1][0];
    const gapLatLngs = [
      [parseFloat(lastPt.lat), parseFloat(lastPt.lng)],
      [parseFloat(firstPt.lat), parseFloat(firstPt.lng)]
    ];
    const gapPoly = L.polyline(gapLatLngs, {
      color: '#94a3b8', weight: 2.5, dashArray: '5, 8', opacity: 0.7
    }).addTo(histMap);
    histLayers.push(gapPoly);
  }

  // Draw start, end, and regular points
  points.forEach((p, idx) => {
    const isFirst = idx === 0;
    const isLast  = idx === points.length - 1;
    const color   = isFirst ? '#10b981' : isLast ? '#ef4444' : '#2563eb';
    const radius  = (isFirst || isLast) ? 7 : 4.5;
    
    const circle  = L.circleMarker([parseFloat(p.lat), parseFloat(p.lng)], {
      radius, color: '#fff', weight: 2, fillColor: color, fillOpacity: 1
    }).addTo(histMap);
    
    circle.bindTooltip(
      `<strong>Ping #${idx+1}</strong> at ${new Date(p.recorded_at.replace(' ', 'T')).toLocaleTimeString()}` +
      (p.address ? `<br><span class="text-xs text-gray-400">${escHtml(p.address)}</span>` : ''),
      { sticky: true }
    );
    
    histLayers.push(circle);
    histMarkers.push(circle);
  });

  // Auto zoom map bounding box to fit the route
  const allCoords = points.map(p => [parseFloat(p.lat), parseFloat(p.lng)]);
  histMap.fitBounds(L.latLngBounds(allCoords), { padding: [40, 40] });

  // Draw history table
  const tbody = document.getElementById('histTableBody');
  tbody.innerHTML = '';
  points.forEach((p, i) => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="text-gray-400 font-semibold">${i+1}</td>
      <td class="font-mono text-xs font-semibold">${new Date(p.recorded_at.replace(' ', 'T')).toLocaleTimeString()}</td>
      <td class="font-mono text-slate-600">${parseFloat(p.lat).toFixed(6)}</td>
      <td class="font-mono text-slate-600">${parseFloat(p.lng).toFixed(6)}</td>
      <td class="text-gray-700 text-xs">${escHtml(p.address || '—')}</td>
      <td class="text-gray-500">${p.accuracy ? Math.round(p.accuracy)+'m' : '—'}</td>
    `;
    
    // Interaction: Clicking table row pans map & highlights row + marker
    tr.addEventListener('click', () => {
      stopRouteAnimation();
      
      // Reset animations/button style
      document.getElementById('playRouteBtn').innerHTML = '<i class="fa-solid fa-play"></i> Play Route';
      
      document.querySelectorAll('#histTableBody tr').forEach(r => r.classList.remove('bg-blue-50/70', 'font-semibold'));
      tr.classList.add('bg-blue-50/70', 'font-semibold');
      
      const lat = parseFloat(p.lat);
      const lng = parseFloat(p.lng);
      histMap.setView([lat, lng], 17, { animate: true });
      
      if (histMarkers[i]) {
        histMarkers[i].openTooltip();
      }
    });
    tbody.appendChild(tr);
  });
}

// ── Route Animator Player ─────────────────────────────────────
function toggleRouteAnimation() {
  const btn = document.getElementById('playRouteBtn');
  if (animInterval) {
    stopRouteAnimation();
    btn.innerHTML = '<i class="fa-solid fa-play"></i> Play Route';
  } else {
    if (historyPoints.length === 0) return;
    btn.innerHTML = '<i class="fa-solid fa-pause"></i> Pause';
    startRouteAnimation();
  }
}

function startRouteAnimation() {
  if (animIndex >= historyPoints.length) {
    animIndex = 0;
  }
  
  if (!animMarker) {
    const startPt = historyPoints[animIndex];
    const animIcon = L.divIcon({
      html: '<div class="w-5 h-5 rounded-full bg-blue-600 border-2 border-white shadow-md animate-ping"></div><div class="w-4 h-4 rounded-full bg-blue-500 border-2 border-white shadow-md absolute top-0.5 left-0.5"></div>',
      className: '', iconSize: [20, 20], iconAnchor: [10, 10]
    });
    animMarker = L.marker([parseFloat(startPt.lat), parseFloat(startPt.lng)], { icon: animIcon }).addTo(histMap);
  }
  
  animInterval = setInterval(() => {
    if (animIndex >= historyPoints.length) {
      stopRouteAnimation();
      document.getElementById('playRouteBtn').innerHTML = '<i class="fa-solid fa-play"></i> Play Route';
      animIndex = 0;
      return;
    }
    
    const pt = historyPoints[animIndex];
    const lat = parseFloat(pt.lat);
    const lng = parseFloat(pt.lng);
    
    animMarker.setLatLng([lat, lng]);
    
    // Pan map to follow the animation point if needed
    histMap.setView([lat, lng], histMap.getZoom(), { animate: true });
    
    // Highlight table rows
    const tbody = document.getElementById('histTableBody');
    const rows = tbody.querySelectorAll('tr');
    if (rows[animIndex]) {
      rows.forEach(r => r.classList.remove('bg-blue-50/70', 'font-semibold'));
      rows[animIndex].classList.add('bg-blue-50/70', 'font-semibold');
      rows[animIndex].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    
    if (histMarkers[animIndex]) {
      histMarkers[animIndex].openTooltip();
    }
    
    animIndex++;
  }, 500); // Step time index
}

function stopRouteAnimation() {
  clearInterval(animInterval);
  animInterval = null;
}

// ── Boot ──────────────────────────────────────────────────────
fetchLive();
</script>
