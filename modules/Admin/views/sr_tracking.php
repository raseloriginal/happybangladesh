<?php $pageTitle = 'SR Tracking'; ?>

<?php
// ── SR list for dropdowns ──────────────────────────────────────────
$srList = $srList ?? [];
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

/* ── Map container ──────────────────────────────────────────── */
.map-wrap {
    position: relative;
    border-radius: 0.875rem;
    overflow: hidden;
    box-shadow: 0 2px 16px 0 rgba(0,0,0,.08);
    border: 1px solid #e2e8f0;
}
.map-box { height: 540px; width: 100%; background: #e5e7eb; }

/* ── SR cards ───────────────────────────────────────────────── */
.sr-card {
    display: flex; align-items: flex-start; gap: 0.75rem;
    padding: 0.875rem 1rem; border-radius: 0.75rem;
    background: #fff; border: 1.5px solid #e2e8f0;
    transition: all 0.18s; cursor: pointer;
}
.sr-card:hover, .sr-card.selected { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
.sr-card .avatar {
    width: 2.25rem; height: 2.25rem; border-radius: 0.625rem;
    background: linear-gradient(135deg,#2563eb,#7c3aed);
    color: #fff; font-weight: 800; font-size: 0.75rem;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.sr-card .sr-name  { font-size: 0.8125rem; font-weight: 700; color: #1e293b; }
.sr-card .sr-meta  { font-size: 0.7rem; color: #64748b; margin-top: 0.1rem; line-height: 1.4; }
.sr-card .badge-online  { background: #d1fae5; color: #065f46; }
.sr-card .badge-offline { background: #fee2e2; color: #991b1b; }
.sr-card .badge { font-size: 0.6875rem; font-weight: 700; padding: 0.15rem 0.5rem; border-radius: 999px; }

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
    padding: 0.45rem 1rem; border-radius: 0.5rem; font-size: 0.8125rem; font-weight: 700;
    cursor: pointer; border: none; transition: all .15s;
}
.filter-btn-primary { background: #2563eb; color: #fff; }
.filter-btn-primary:hover { background: #1d4ed8; }
.filter-btn-secondary { background: #e2e8f0; color: #475569; }
.filter-btn-secondary:hover { background: #cbd5e1; }

/* ── Info overlay on map ─────────────────────────────────────── */
#liveMapInfo {
    position: absolute; top: 12px; left: 12px; z-index: 900;
    background: rgba(255,255,255,.94); backdrop-filter: blur(6px);
    border-radius: 0.625rem; padding: 0.6rem 0.875rem;
    font-size: 0.75rem; color: #1e293b; pointer-events: none;
    box-shadow: 0 2px 8px rgba(0,0,0,.12);
}
#historyMapInfo {
    position: absolute; top: 12px; left: 12px; z-index: 900;
    background: rgba(255,255,255,.94); backdrop-filter: blur(6px);
    border-radius: 0.625rem; padding: 0.6rem 0.875rem;
    font-size: 0.75rem; color: #1e293b; pointer-events: none;
    box-shadow: 0 2px 8px rgba(0,0,0,.12);
}

/* ── History route legend ────────────────────────────────────── */
.route-legend { display: flex; align-items: center; gap: 1.5rem; font-size: 0.75rem; color: #475569; }
.route-legend span { display: flex; align-items: center; gap: 0.4rem; }
.dot-start { width:10px;height:10px;border-radius:50%;background:#16a34a;flex-shrink:0; }
.dot-end   { width:10px;height:10px;border-radius:50%;background:#dc2626;flex-shrink:0; }
.dot-gap   { width:20px;height:3px;border-top:2.5px dashed #94a3b8;flex-shrink:0; }
.dot-route { width:20px;height:3px;background:#2563eb;flex-shrink:0; border-radius:2px; }

/* ── Refresh countdown ─────────────────────────────────────── */
#refreshCountdown { font-variant-numeric: tabular-nums; }

/* ── History table ─────────────────────────────────────────── */
.hist-table { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
.hist-table th { background: #f1f5f9; padding: 0.5rem 0.75rem; text-align: left; font-weight: 700; color: #475569; font-size: 0.75rem; }
.hist-table td { padding: 0.45rem 0.75rem; border-bottom: 1px solid #f1f5f9; color: #1e293b; }
.hist-table tr:last-child td { border-bottom: none; }
.hist-table tr:hover td { background: #f8fafc; }
</style>

<!-- ── Page Header ────────────────────────────────────────────── -->
<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-2xl font-black text-gray-900 tracking-tight">SR Tracking</h1>
    <p class="text-sm text-gray-500 mt-0.5">Real-time location monitoring &amp; movement history for Sales Representatives</p>
  </div>
  <div class="flex items-center gap-2 text-sm text-gray-500">
    <i class="fa-solid fa-satellite-dish text-blue-500"></i>
    <span>Auto-refresh: <strong id="refreshCountdown" class="text-blue-700">60</strong>s</span>
  </div>
</div>

<!-- ── Tab Switcher ───────────────────────────────────────────── -->
<div class="bg-slate-100 rounded-xl p-1 inline-flex gap-1 mb-5">
  <button id="tab-live-btn" class="track-tab-btn active" onclick="switchTab('live')">
    <i class="fa-solid fa-circle text-green-500 text-[9px] animate-pulse"></i> SR Live Location
  </button>
  <button id="tab-hist-btn" class="track-tab-btn" onclick="switchTab('history')">
    <i class="fa-solid fa-clock-rotate-left text-[13px]"></i> SR Location History
  </button>
</div>

<!-- ══════════════════════════════════════════════════════════════
     TAB 1: LIVE LOCATION
══════════════════════════════════════════════════════════════ -->
<div id="tab-live" class="track-tab-panel active">
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    <!-- SR list sidebar -->
    <div class="space-y-2 lg:max-h-[600px] lg:overflow-y-auto pr-1" id="liveSrList">
      <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 px-1">
        Active SRs — <span id="liveOnlineCount">—</span> online
      </div>
      <div id="liveSrCards"></div>
    </div>

    <!-- Map -->
    <div class="lg:col-span-2">
      <div class="map-wrap">
        <div id="liveMapInfo">
          <i class="fa-solid fa-satellite-dish text-blue-500 mr-1"></i>
          <span id="liveMapStatus">Loading locations…</span>
        </div>
        <div id="liveMap" class="map-box"></div>
      </div>
      <!-- Selected SR detail -->
      <div id="liveDetailCard" class="hidden mt-3 bg-white rounded-xl border border-blue-100 p-4 shadow-sm">
        <div class="flex items-center justify-between mb-2">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-600 to-violet-600 text-white flex items-center justify-center font-black text-xs" id="dcAvatar"></div>
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
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm mb-4">
    <div class="filter-bar">
      <select id="histSrFilter">
        <option value="">— Select SR —</option>
        <?php foreach ($srList as $sr): ?>
        <option value="<?= $sr['id'] ?>"><?= h($sr['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <input type="date" id="histDateFilter" value="<?= date('Y-m-d') ?>">
      <input type="time" id="histTimeFrom" value="00:00">
      <span class="text-gray-400 text-sm">to</span>
      <input type="time" id="histTimeTo" value="23:59">
      <button class="filter-btn filter-btn-primary" onclick="loadHistory()">
        <i class="fa-solid fa-search mr-1"></i> Load Route
      </button>
      <button class="filter-btn filter-btn-secondary" onclick="clearHistory()">
        <i class="fa-solid fa-xmark mr-1"></i> Clear
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
        <span id="histMapStatus"></span>
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
    <div class="overflow-x-auto max-h-72 overflow-y-auto">
      <table class="hist-table">
        <thead class="sticky top-0">
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
          <tr><td colspan="6" class="text-center text-gray-400 py-6">Select an SR and date to load history</td></tr>
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
const API_LIVE    = BASE + '/admin/api/sr-tracking/live';
const API_HISTORY = BASE + '/admin/api/sr-tracking/history';
const REFRESH_SEC = 60;

// ── Tab switching ─────────────────────────────────────────────
let activeTab = 'live';
function switchTab(tab) {
  activeTab = tab;
  document.querySelectorAll('.track-tab-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.track-tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-' + tab).classList.add('active');
  document.getElementById('tab-' + tab + '-btn').classList.add('active');
  if (tab === 'live') { liveMap.invalidateSize(); }
  if (tab === 'history') { histMap.invalidateSize(); }
}

// ── Countdown ─────────────────────────────────────────────────
let countdown = REFRESH_SEC;
setInterval(() => {
  countdown--;
  document.getElementById('refreshCountdown').textContent = countdown;
  if (countdown <= 0) { countdown = REFRESH_SEC; fetchLive(); }
}, 1000);

// ══════════════════════════════════════════════════════════════
//  TAB 1 — LIVE MAP
// ══════════════════════════════════════════════════════════════
const liveMap = L.map('liveMap', { zoomControl: true }).setView([23.8103, 90.4125], 11);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '© OpenStreetMap contributors', maxZoom: 19
}).addTo(liveMap);

const liveMarkers = {};    // keyed by sr_id
let   selectedSrId = null;

function srInitials(name) {
  return (name || '?').trim().split(/\s+/).map(w => w[0]).join('').toUpperCase().slice(0,2);
}

function timeAgo(isoStr) {
  if (!isoStr) return '—';
  const diff = Math.floor((Date.now() - new Date(isoStr).getTime()) / 1000);
  if (diff < 60) return diff + 's ago';
  if (diff < 3600) return Math.floor(diff/60) + 'm ago';
  if (diff < 86400) return Math.floor(diff/3600) + 'h ago';
  return Math.floor(diff/86400) + 'd ago';
}

function makeSrIcon(isOnline) {
  const color = isOnline ? '#16a34a' : '#94a3b8';
  const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="28" height="36" viewBox="0 0 28 36">
    <path d="M14 0C6.268 0 0 6.268 0 14c0 9.333 14 22 14 22S28 23.333 28 14C28 6.268 21.732 0 14 0z" fill="${color}"/>
    <circle cx="14" cy="14" r="7" fill="white"/>
    <circle cx="14" cy="14" r="4" fill="${color}"/>
  </svg>`;
  return L.divIcon({
    html: svg, className: '', iconSize: [28, 36], iconAnchor: [14, 36], popupAnchor: [0, -36]
  });
}

function fetchLive() {
  document.getElementById('liveMapStatus').textContent = 'Refreshing…';
  fetch(API_LIVE)
    .then(r => r.json())
    .then(data => {
      if (!data.success) return;
      renderLive(data.srs);
      document.getElementById('liveMapStatus').textContent =
        'Updated ' + new Date().toLocaleTimeString();
    })
    .catch(() => { document.getElementById('liveMapStatus').textContent = 'Connection error'; });
}

function renderLive(srs) {
  const onlineCount = srs.filter(s => s.is_online).length;
  document.getElementById('liveOnlineCount').textContent = onlineCount + ' / ' + srs.length;

  // Build cards
  const container = document.getElementById('liveSrCards');
  container.innerHTML = '';
  srs.forEach(sr => {
    const init = srInitials(sr.name);
    const onl  = sr.is_online;
    const div  = document.createElement('div');
    div.className = 'sr-card' + (selectedSrId == sr.id ? ' selected' : '');
    div.dataset.id = sr.id;
    div.innerHTML = `
      <div class="avatar">${init}</div>
      <div class="flex-1 min-w-0">
        <div class="sr-name">${escHtml(sr.name)}</div>
        <div class="sr-meta">
          ${sr.lat ? sr.lat.toFixed(5)+', '+sr.lng.toFixed(5) : 'No location yet'}<br>
          ${sr.address ? escHtml(sr.address.slice(0,40)) + (sr.address.length>40?'…':'') : '—'}<br>
          <span class="text-gray-400">${sr.recorded_at ? timeAgo(sr.recorded_at) : 'Never'}</span>
        </div>
      </div>
      <span class="badge ${onl ? 'badge-online' : 'badge-offline'}">${onl ? 'Online' : 'Offline'}</span>
    `;
    div.addEventListener('click', () => selectSr(sr));
    container.appendChild(div);

    // Map marker
    if (sr.lat && sr.lng) {
      if (liveMarkers[sr.id]) {
        liveMarkers[sr.id].setLatLng([sr.lat, sr.lng]).setIcon(makeSrIcon(onl));
      } else {
        liveMarkers[sr.id] = L.marker([sr.lat, sr.lng], { icon: makeSrIcon(onl) })
          .addTo(liveMap)
          .on('click', () => selectSr(sr));
      }
      liveMarkers[sr.id].bindTooltip(sr.name, { permanent: false, direction: 'top' });
    }
  });
}

function selectSr(sr) {
  selectedSrId = sr.id;
  document.querySelectorAll('.sr-card').forEach(c => {
    c.classList.toggle('selected', parseInt(c.dataset.id) === sr.id);
  });
  if (sr.lat && sr.lng) {
    liveMap.setView([sr.lat, sr.lng], 15, { animate: true });
    if (liveMarkers[sr.id]) liveMarkers[sr.id].openTooltip();
  }
  // Fill detail card
  const dc = document.getElementById('liveDetailCard');
  dc.classList.remove('hidden');
  document.getElementById('dcAvatar').textContent   = srInitials(sr.name);
  document.getElementById('dcName').textContent     = sr.name;
  document.getElementById('dcCoords').textContent   = sr.lat ? sr.lat.toFixed(6)+', '+sr.lng.toFixed(6) : '—';
  document.getElementById('dcTime').textContent     = sr.recorded_at ? new Date(sr.recorded_at).toLocaleString() : 'Never';
  document.getElementById('dcAddress').textContent  = sr.address || 'No address available';
  const badge = document.getElementById('dcStatusBadge');
  badge.className = 'badge ' + (sr.is_online ? 'badge-online' : 'badge-offline');
  badge.textContent = sr.is_online ? 'Online' : 'Offline';
}

function escHtml(str) {
  const d = document.createElement('div'); d.textContent = str; return d.innerHTML;
}

// ══════════════════════════════════════════════════════════════
//  TAB 2 — HISTORY MAP
// ══════════════════════════════════════════════════════════════
const histMap = L.map('histMap', { zoomControl: true }).setView([23.8103, 90.4125], 11);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '© OpenStreetMap contributors', maxZoom: 19
}).addTo(histMap);

const HIST_GAP_KM = 2.0; // gap threshold in km
let histLayers = [];

function haversineKm(lat1, lng1, lat2, lng2) {
  const R = 6371;
  const dLat = (lat2-lat1) * Math.PI/180;
  const dLng = (lng2-lng1) * Math.PI/180;
  const a = Math.sin(dLat/2)**2 + Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLng/2)**2;
  return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}

function loadHistory() {
  const srId   = document.getElementById('histSrFilter').value;
  const date   = document.getElementById('histDateFilter').value;
  const tFrom  = document.getElementById('histTimeFrom').value;
  const tTo    = document.getElementById('histTimeTo').value;

  if (!srId) { alert('Please select an SR first.'); return; }
  if (!date) { alert('Please select a date.'); return; }

  const info = document.getElementById('historyMapInfo');
  info.classList.remove('hidden');
  document.getElementById('histMapStatus').innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i>Loading route…';

  const params = new URLSearchParams({ sr_id: srId, date, time_from: tFrom, time_to: tTo });
  fetch(API_HISTORY + '?' + params.toString())
    .then(r => r.json())
    .then(data => {
      if (!data.success) { document.getElementById('histMapStatus').textContent = 'Error loading data'; return; }
      renderHistory(data.points);
    })
    .catch(() => { document.getElementById('histMapStatus').textContent = 'Connection error'; });
}

function clearHistory() {
  histLayers.forEach(l => histMap.removeLayer(l));
  histLayers = [];
  document.getElementById('histTableBody').innerHTML =
    '<tr><td colspan="6" class="text-center text-gray-400 py-6">Select an SR and date to load history</td></tr>';
  document.getElementById('histRecordCount').textContent = '';
  document.getElementById('historyMapInfo').classList.add('hidden');
}

function renderHistory(points) {
  // Remove old layers
  histLayers.forEach(l => histMap.removeLayer(l));
  histLayers = [];

  document.getElementById('histRecordCount').textContent = '(' + points.length + ' records)';

  if (!points.length) {
    document.getElementById('histMapStatus').textContent = 'No location data for this period';
    document.getElementById('histTableBody').innerHTML =
      '<tr><td colspan="6" class="text-center text-gray-400 py-6">No records found</td></tr>';
    return;
  }

  document.getElementById('histMapStatus').textContent =
    points.length + ' points — ' + new Date(points[0].recorded_at).toLocaleTimeString() +
    ' → ' + new Date(points[points.length-1].recorded_at).toLocaleTimeString();

  // Build route segments (split on gap > HIST_GAP_KM)
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

  // Draw segments
  segments.forEach(s => {
    if (s.length < 2) return;
    const latlngs = s.map(p => [parseFloat(p.lat), parseFloat(p.lng)]);
    const poly = L.polyline(latlngs, { color: '#2563eb', weight: 4, opacity: 0.8 }).addTo(histMap);
    histLayers.push(poly);
  });

  // Small circle markers on each point
  points.forEach((p, idx) => {
    const isFirst = idx === 0;
    const isLast  = idx === points.length - 1;
    const color   = isFirst ? '#16a34a' : isLast ? '#dc2626' : '#2563eb';
    const radius  = (isFirst || isLast) ? 8 : 4;
    const circle  = L.circleMarker([parseFloat(p.lat), parseFloat(p.lng)], {
      radius, color: '#fff', weight: 2, fillColor: color, fillOpacity: 1
    }).addTo(histMap);
    circle.bindTooltip(
      new Date(p.recorded_at).toLocaleTimeString() +
      (p.address ? '<br>' + escHtml(p.address.slice(0,50)) : ''),
      { sticky: true }
    );
    histLayers.push(circle);
  });

  // Fit map to route
  const allCoords = points.map(p => [parseFloat(p.lat), parseFloat(p.lng)]);
  histMap.fitBounds(L.latLngBounds(allCoords), { padding: [30, 30] });

  // Render table
  const tbody = document.getElementById('histTableBody');
  tbody.innerHTML = '';
  points.forEach((p, i) => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="text-gray-400">${i+1}</td>
      <td class="font-mono text-xs">${new Date(p.recorded_at).toLocaleTimeString()}</td>
      <td class="font-mono">${parseFloat(p.lat).toFixed(6)}</td>
      <td class="font-mono">${parseFloat(p.lng).toFixed(6)}</td>
      <td class="text-gray-600">${escHtml(p.address || '—')}</td>
      <td class="text-gray-500">${p.accuracy ? Math.round(p.accuracy)+'m' : '—'}</td>
    `;
    tr.addEventListener('click', () => histMap.setView([parseFloat(p.lat), parseFloat(p.lng)], 16));
    tbody.appendChild(tr);
  });
}

// ── Boot ──────────────────────────────────────────────────────
fetchLive();
</script>
