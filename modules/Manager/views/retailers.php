<?php $pageTitle = 'Retailers'; ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
.ret-page { display: flex; flex-direction: column; height: calc(100vh - 130px); }
.ret-topbar { display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:2px solid #cbd5e1;flex-shrink:0;flex-wrap:wrap; }
.ret-topbar h1 { font-size:1.25rem;font-weight:800;color:#0f172a;margin:0; }
.ret-topbar .breadcrumb { font-size:0.75rem;color:#64748b;margin-left:4px; }
.ret-topbar-right { margin-left:auto;display:flex;gap:8px;align-items:center; }
.ret-tabs { display:flex;border-bottom:2px solid #cbd5e1;flex-shrink:0;margin-top:8px; }
.ret-tab { padding:8px 22px;font-size:0.82rem;font-weight:700;color:#475569;border:1px solid #cbd5e1;border-bottom:none;background:#f8fafc;cursor:pointer;user-select:none;margin-right:-1px;position:relative;top:1px;transition:background .1s,color .1s; }
.ret-tab:first-child { border-radius:4px 0 0 0; }
.ret-tab:last-child  { border-radius:0 4px 0 0; }
.ret-tab.active { background:#fff;color:#1e40af;border-bottom-color:#fff;z-index:2; }
.ret-tab i { margin-right:6px; }
.ret-panel { display:none;flex:1;min-height:0; }
.ret-panel.active { display:flex;flex-direction:column; }
#ret-map { flex:1;min-height:0;width:100%;border:2px solid #cbd5e1;border-top:none;background:#e2e8f0; }
.ret-list-bar { display:flex;gap:8px;align-items:center;padding:10px 0 8px;flex-shrink:0;flex-wrap:wrap;border-top:2px solid #e2e8f0; }
.ret-search { border:1px solid #cbd5e1;border-radius:0;padding:7px 12px;font-size:0.82rem;outline:none;flex:1;min-width:120px;background:#fff;color:#1e293b; }
.ret-search:focus { border-color:#3b82f6; }
.ret-count { font-size:0.78rem;color:#64748b;margin-left:6px; }
.ret-grid-wrap { flex:1;overflow-y:auto;border:2px solid #cbd5e1;border-top:none; }
.ret-table { width:100%;border-collapse:collapse; }
.ret-table thead th { position:sticky;top:0;background:#1e293b;color:#fff;font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;padding:9px 12px;border-right:1px solid #334155;white-space:nowrap;z-index:2;text-align:left; }
.ret-table tbody tr { cursor:pointer;transition:background .1s;border-bottom:1px solid #e2e8f0; }
.ret-table tbody tr:hover { background:#eff6ff; }
.ret-table tbody td { font-size:0.8rem;padding:8px 12px;color:#1e293b;border-right:1px solid #e2e8f0;white-space:nowrap; }
.ret-table .name-cell { font-weight:600;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
.ret-no-loc { color:#94a3b8;font-size:0.75rem; }
.ret-ratio { font-weight:700; }
.ret-ratio.good { color:#16a34a; }
.ret-ratio.avg  { color:#d97706; }
.ret-ratio.poor { color:#dc2626; }
.rbtn { display:inline-flex;align-items:center;gap:6px;padding:7px 14px;font-size:0.78rem;font-weight:700;border:1px solid transparent;border-radius:0;cursor:pointer;transition:background .1s;text-transform:uppercase;letter-spacing:.04em;background:none; }
.rbtn-primary { background:#1e40af;color:#fff;border-color:#1e3a8a; }
.rbtn-primary:hover { background:#1e3a8a; }
.rbtn-gray { background:#f1f5f9;color:#475569;border-color:#cbd5e1; }
.rbtn-gray:hover { background:#e2e8f0; }
.rbtn-disabled { background:#e2e8f0;color:#94a3b8;border-color:#cbd5e1;cursor:not-allowed; }
/* view toggle */
.view-toggle { display:flex;border:1px solid #cbd5e1;overflow:hidden; }
.view-btn { display:inline-flex;align-items:center;gap:5px;padding:6px 12px;font-size:0.76rem;font-weight:700;cursor:pointer;border:none;background:#f8fafc;color:#64748b;transition:background .1s,color .1s;text-transform:uppercase;letter-spacing:.04em; }
.view-btn.active { background:#1e40af;color:#fff; }
.view-btn:not(.active):hover { background:#e2e8f0; }
/* grid cards */
.ret-cards-wrap { flex:1;overflow-y:auto;border:2px solid #cbd5e1;border-top:none; }
.ret-cards-grid { display:grid;grid-template-columns:1fr 1fr;gap:12px;padding:12px; }
.ret-card { border:1px solid #cbd5e1;background:#fff;cursor:pointer;transition:box-shadow .15s,border-color .15s;display:flex;flex-direction:column;gap:0; }
.ret-card:hover { border-color:#3b82f6;box-shadow:0 4px 16px rgba(59,130,246,.15); }
.ret-card-hdr { background:#1e293b;color:#fff;padding:9px 12px;display:flex;align-items:center;justify-content:space-between; }
.ret-card-name { font-size:0.82rem;font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:calc(100% - 44px); }
.ret-card-id { font-size:0.68rem;color:#94a3b8;font-weight:600; }
.ret-card-body { padding:10px 12px;display:flex;flex-direction:column;gap:6px;flex:1; }
.ret-card-row { display:flex;align-items:center;gap:6px;font-size:0.76rem;color:#475569; }
.ret-card-row i { width:14px;text-align:center;color:#3b82f6;flex-shrink:0; }
.ret-card-row span { white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.ret-card-stats { display:grid;grid-template-columns:1fr 1fr;border-top:1px solid #e2e8f0;margin-top:4px; }
.ret-card-stat { padding:7px 10px;border-right:1px solid #e2e8f0; }
.ret-card-stat:last-child { border-right:none; }
.ret-card-stat .sl { font-size:.62rem;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;font-weight:700; }
.ret-card-stat .sv { font-size:.85rem;font-weight:800;color:#0f172a;margin-top:2px; }
.ret-card-footer { padding:7px 12px;border-top:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;background:#f8fafc; }
.ret-card-ratio { font-size:0.78rem;font-weight:800; }
.ret-card-ratio.good { color:#16a34a; }
.ret-card-ratio.avg  { color:#d97706; }
.ret-card-ratio.poor { color:#dc2626; }
.ret-card-loc { font-size:0.68rem;color:#3b82f6; }
.ret-card-noloc { font-size:0.68rem;color:#94a3b8; }
.rmodal-overlay { position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:9000;display:none;align-items:center;justify-content:center; }
.rmodal-overlay.open { display:flex; }
.rmodal { background:#fff;border:2px solid #1e293b;width:540px;max-width:95vw;max-height:90vh;overflow-y:auto;box-shadow:6px 6px 0 #0f172a; }
.rmodal-hdr { background:#1e293b;color:#fff;padding:14px 18px;font-size:.95rem;font-weight:800;display:flex;align-items:center;justify-content:space-between; }
.rmodal-close { background:none;border:none;color:#fff;font-size:1.25rem;cursor:pointer;line-height:1; }
.rmodal-body { padding:18px 20px; }
.rstats { display:grid;grid-template-columns:repeat(2,1fr);gap:8px;margin-bottom:16px; }
.rstat { border:1px solid #cbd5e1;padding:10px 14px;background:#f8fafc; }
.rstat .sl { font-size:.68rem;text-transform:uppercase;letter-spacing:.06em;color:#64748b;font-weight:700;margin-bottom:4px; }
.rstat .sv { font-size:1.05rem;font-weight:800;color:#0f172a; }
.rstat.good { background:#f0fdf4;border-color:#86efac; }
.rstat.good .sv { color:#16a34a; }
.rstat.avg  { background:#fffbeb;border-color:#fcd34d; }
.rstat.avg  .sv { color:#d97706; }
.rstat.poor { background:#fef2f2;border-color:#fca5a5; }
.rstat.poor .sv { color:#dc2626; }
.rinfo-row { display:flex;flex-direction:column;gap:8px;margin-bottom:14px; }
.rinfo-item { font-size:.82rem;color:#475569; }
.rinfo-item strong { color:#1e293b;font-weight:700;margin-right:4px; }
.rdivider { border:none;border-top:1px solid #e2e8f0;margin:14px 0; }
.rfg { margin-bottom:12px; }
.rfg label { display:block;font-size:.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px; }
.rfg input,.rfg textarea { width:100%;border:1px solid #cbd5e1;border-radius:0;padding:8px 10px;font-size:.82rem;color:#1e293b;background:#fff;outline:none;box-sizing:border-box; }
.rfg input:focus,.rfg textarea:focus { border-color:#3b82f6; }
.rfg textarea { resize:vertical;min-height:64px; }
.rfg-row { display:grid;grid-template-columns:1fr 1fr;gap:10px; }
.cs-badge { display:inline-block;background:#fef3c7;color:#92400e;border:1px solid #fde68a;font-size:.65rem;font-weight:700;padding:2px 7px;text-transform:uppercase;letter-spacing:.05em;vertical-align:middle;margin-left:4px; }
.rmodal-actions { display:flex;gap:8px;flex-wrap:wrap;padding:14px 20px;border-top:1px solid #e2e8f0;background:#f8fafc; }
/* canvas markers — pure canvas layer, no DOM per marker */

#rloading { position:absolute;inset:0;background:rgba(255,255,255,.85);display:flex;align-items:center;justify-content:center;z-index:999;font-size:.85rem;color:#475569;font-weight:700;gap:10px; }
@keyframes rspin { to { transform:rotate(360deg); } }
.rspinner { width:20px;height:20px;border:3px solid #e2e8f0;border-top-color:#3b82f6;border-radius:50%;animation:rspin .7s linear infinite; }
</style>

<div class="ret-page">
  <div class="ret-topbar">
    <div>
      <h1><i class="fa-solid fa-shop" style="color:#3b82f6;margin-right:8px;"></i>Retailers</h1>
      <span class="breadcrumb">Manager &rsaquo; Retailers</span>
    </div>
    <div class="ret-topbar-right">
      <span id="rtotal" style="font-size:.8rem;color:#475569;border:1px solid #cbd5e1;padding:5px 12px;background:#f8fafc;">Loading...</span>
    </div>
  </div>

  <div class="ret-tabs">
    <div class="ret-tab active" id="tab-map" onclick="rTab('map')"><i class="fa-solid fa-map-location-dot"></i>Map View</div>
    <div class="ret-tab" id="tab-list" onclick="rTab('list')"><i class="fa-solid fa-table-list"></i>List / Grid</div>
  </div>

  <!-- MAP -->
  <div class="ret-panel active" id="panel-map" style="position:relative;">
    <div id="rloading"><div class="rspinner"></div> Loading retailers...</div>
    <div id="ret-map"></div>
  </div>

  <!-- LIST -->
  <div class="ret-panel" id="panel-list">
    <div class="ret-list-bar">
      <input type="text" class="ret-search" id="rsearch" placeholder="Search name, phone, address..." oninput="rFilter()">
      <span class="ret-count" id="rcount"></span>
      <div class="view-toggle" style="margin-left:auto;">
        <button class="view-btn active" id="vbtn-list" onclick="rSetView('list')" title="List View"><i class="fa-solid fa-table-list"></i> List</button>
        <button class="view-btn" id="vbtn-grid" onclick="rSetView('grid')" title="Grid View"><i class="fa-solid fa-grip"></i> Grid</button>
      </div>
    </div>
    <!-- list view -->
    <div class="ret-grid-wrap" id="view-list">
      <table class="ret-table">
        <thead>
          <tr>
            <th>#</th><th>Name</th><th>Phone</th><th>Address</th><th>Location</th>
            <th>Orders</th><th>Ordered (&#2547;)</th><th>Delivered (&#2547;)</th><th>Success %</th>
          </tr>
        </thead>
        <tbody id="rtbody"></tbody>
      </table>
    </div>
    <!-- grid view -->
    <div class="ret-cards-wrap" id="view-grid" style="display:none;">
      <div class="ret-cards-grid" id="rcardsgrid"></div>
    </div>
  </div>
</div>

<!-- MODAL -->
<div class="rmodal-overlay" id="roverlay" onclick="rCloseOvl(event)">
  <div class="rmodal">
    <div class="rmodal-hdr">
      <span id="rmodal-title">Retailer</span>
      <button class="rmodal-close" onclick="rClose()">&times;</button>
    </div>
    <div class="rmodal-body">
      <div id="rview">
        <div class="rinfo-row" id="rinfo"></div>
        <hr class="rdivider">
        <div class="rstats" id="rstats"></div>
      </div>
      <div id="redit" style="display:none;">
        <div class="rfg"><label>Name *</label><input type="text" id="ename"></div>
        <div class="rfg"><label>Phone</label><input type="text" id="ephone"></div>
        <div class="rfg"><label>Address</label><textarea id="eaddr"></textarea></div>
        <div class="rfg-row">
          <div class="rfg"><label>Latitude</label><input type="number" step="any" id="elat"></div>
          <div class="rfg"><label>Longitude</label><input type="number" step="any" id="elng"></div>
        </div>
        <p style="font-size:.75rem;color:#64748b;margin:0 0 12px;"><i class="fa-solid fa-circle-info" style="color:#3b82f6;"></i> Enter coordinates manually or read from the map.</p>
      </div>
    </div>
    <div class="rmodal-actions" id="ract"></div>
  </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
var BURL = '<?= BASE_URL ?>';
var allR = [], filtR = [], rMap = null, curR = null;
var rViewMode = 'list'; /* 'list' | 'grid' */
var _dotLayer = null; /* our custom canvas layer */

document.addEventListener('DOMContentLoaded', function(){
  rInitMap();
  rLoad();
});

/* --- Tabs --- */
function rTab(t){
  document.querySelectorAll('.ret-tab').forEach(function(x){ x.classList.remove('active'); });
  document.querySelectorAll('.ret-panel').forEach(function(x){ x.classList.remove('active'); });
  document.getElementById('tab-'+t).classList.add('active');
  document.getElementById('panel-'+t).classList.add('active');
  if(t==='map'&&rMap) setTimeout(function(){ rMap.invalidateSize(); },60);
}

/* ═══════════════════════════════════════════════════════
   Custom Leaflet Canvas Layer
   Draws dots + name labels directly on <canvas>.
   Zero DOM elements per retailer — instant at any scale.
   Click detection: nearest point within CLICK_PX pixels.
   ═══════════════════════════════════════════════════════ */
var CLICK_PX = 14; /* click detection radius in pixels */

var RetailerLayer = L.Layer.extend({
  initialize: function(data){ this._data = data || []; },

  setData: function(data){
    this._data = data || [];
    if(this._canvas) this._draw();
  },

  onAdd: function(map){
    this._map = map;
    var pane = map.getPanes().overlayPane;
    this._canvas = document.createElement('canvas');
    var s = this._canvas.style;
    s.position = 'absolute'; s.top = '0'; s.left = '0';
    s.zIndex = 200;
    pane.appendChild(this._canvas);
    this._ctx = this._canvas.getContext('2d');
    map.on('moveend zoomend viewreset resize', this._reset, this);
    map.on('click', this._onClick, this);
    this._reset();
  },

  onRemove: function(map){
    this._canvas.parentNode.removeChild(this._canvas);
    map.off('moveend zoomend viewreset resize', this._reset, this);
    map.off('click', this._onClick, this);
  },

  _reset: function(){
    var map = this._map;
    var size = map.getSize();
    var tl   = map.containerPointToLayerPoint([0,0]);
    this._canvas.width  = size.x;
    this._canvas.height = size.y;
    L.DomUtil.setPosition(this._canvas, tl);
    this._draw();
  },

  _draw: function(){
    var map = this._map, ctx = this._ctx;
    var w = this._canvas.width, h = this._canvas.height;
    ctx.clearRect(0, 0, w, h);

    var zoom = map.getZoom();
    /* scale dot & font with zoom */
    var dotR   = zoom >= 16 ? 6 : zoom >= 14 ? 5 : zoom >= 12 ? 4 : 3;
    var fSize  = zoom >= 16 ? 12 : zoom >= 14 ? 11 : zoom >= 12 ? 10 : 9;
    var showLabel = true; /* always show */

    ctx.font = 'bold '+fSize+'px Inter,Arial,sans-serif';
    ctx.textBaseline = 'bottom';

    var data = this._data;
    for(var i=0; i<data.length; i++){
      var r = data[i];
      if(!r.lat || !r.lng || parseFloat(r.lat)==0) continue;

      var pt = map.latLngToContainerPoint([parseFloat(r.lat), parseFloat(r.lng)]);
      var x = pt.x, y = pt.y;

      /* dot */
      ctx.beginPath();
      ctx.arc(x, y, dotR, 0, Math.PI*2);
      ctx.fillStyle   = '#2563eb';
      ctx.fill();
      ctx.strokeStyle = '#1e3a8a';
      ctx.lineWidth   = 1.5;
      ctx.stroke();

      /* name label */
      if(showLabel && r.name){
        var name = r.name.length > 22 ? r.name.substring(0,20)+'…' : r.name;
        var tw = ctx.measureText(name).width;
        var lx = x + dotR + 3;
        var ly = y - 2;

        /* label background */
        ctx.fillStyle = 'rgba(15,23,42,0.82)';
        ctx.fillRect(lx - 2, ly - fSize - 1, tw + 6, fSize + 4);

        /* label text */
        ctx.fillStyle = '#ffffff';
        ctx.fillText(name, lx, ly);
      }
    }
  },

  _onClick: function(e){
    /* find nearest retailer within CLICK_PX */
    var pt  = e.containerPoint;
    var map = this._map;
    var best = null, bestD = CLICK_PX * CLICK_PX;
    var data = this._data;
    for(var i=0; i<data.length; i++){
      var r = data[i];
      if(!r.lat || !r.lng || parseFloat(r.lat)==0) continue;
      var rp = map.latLngToContainerPoint([parseFloat(r.lat), parseFloat(r.lng)]);
      var dx = pt.x - rp.x, dy = pt.y - rp.y;
      var d2 = dx*dx + dy*dy;
      if(d2 < bestD){ bestD = d2; best = r; }
    }
    if(best) rOpen(best.id);
  }
});

/* --- Map Init --- */
function rInitMap(){
  rMap = L.map('ret-map', { center:[23.8041,90.4152], zoom:12, preferCanvas:true });
  L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}',{
    attribution:'Google Maps', maxZoom:20
  }).addTo(rMap);
  _dotLayer = new RetailerLayer([]);
  _dotLayer.addTo(rMap);
}

/* draw / update markers on map */
function rDrawMarkers(list){
  if(_dotLayer) _dotLayer.setData(list);
}

/* --- Load --- */
function rLoad(){
  document.getElementById('rloading').style.display='flex';
  fetch(BURL+'/manager/api/retailers?limit=5000')
    .then(function(x){ return x.json(); })
    .then(function(d){
      document.getElementById('rloading').style.display='none';
      if(!d.success) return;
      allR=d.retailers||[]; filtR=allR;
      document.getElementById('rtotal').textContent='Total: '+d.total.toLocaleString()+' retailers';
      rDrawMarkers(allR);
      rRenderList(allR);
    }).catch(function(){ document.getElementById('rloading').style.display='none'; });
}

/* --- Filter --- */
function rFilter(){
  var q=document.getElementById('rsearch').value.trim().toLowerCase();
  filtR=q?allR.filter(function(r){
    return (r.name||'').toLowerCase().indexOf(q)>-1||(r.phone||'').toLowerCase().indexOf(q)>-1||(r.address||'').toLowerCase().indexOf(q)>-1;
  }):allR;
  rRenderList(filtR);
}

/* --- View Toggle --- */
function rSetView(v){
  rViewMode=v;
  document.getElementById('view-list').style.display=v==='list'?'':'none';
  document.getElementById('view-grid').style.display=v==='grid'?'':'none';
  document.getElementById('vbtn-list').classList.toggle('active',v==='list');
  document.getElementById('vbtn-grid').classList.toggle('active',v==='grid');
  rRenderList(filtR);
}

/* --- Render Grid Cards --- */
function rRenderGrid(list){
  var el=document.getElementById('rcardsgrid');
  if(!list.length){ el.innerHTML='<div style="grid-column:span 2;text-align:center;padding:24px;color:#94a3b8;">No retailers found.</div>'; return; }
  el.innerHTML=list.map(function(r,i){
    var rt=rRatio(r),rc=rt>=80?'good':rt>=50?'avg':'poor';
    var hasLoc=(r.lat&&r.lng&&parseFloat(r.lat)!=0);
    var locHtml=hasLoc
      ?'<span class="ret-card-loc"><i class="fa-solid fa-location-dot"></i> '+parseFloat(r.lat).toFixed(4)+', '+parseFloat(r.lng).toFixed(4)+'</span>'
      :'<span class="ret-card-noloc"><i class="fa-solid fa-location-xmark"></i> No GPS</span>';
    return '<div class="ret-card" onclick="rOpen('+r.id+')">'  
      +'<div class="ret-card-hdr"><span class="ret-card-name">'+rEsc(r.name)+'</span><span class="ret-card-id">#'+r.id+'</span></div>'
      +'<div class="ret-card-body">'
      +(r.phone?'<div class="ret-card-row"><i class="fa-solid fa-phone"></i><span>'+rEsc(r.phone)+'</span></div>':'')
      +(r.address?'<div class="ret-card-row"><i class="fa-solid fa-map-pin"></i><span>'+rEsc(r.address)+'</span></div>':'')
      +'</div>'
      +'<div class="ret-card-stats">'
      +'<div class="ret-card-stat"><div class="sl">Orders</div><div class="sv">'+(r.total_orders||0)+'</div></div>'
      +'<div class="ret-card-stat"><div class="sl">Delivered</div><div class="sv">'+(r.delivered_orders||0)+'</div></div>'
      +'</div>'
      +'<div class="ret-card-footer">'
      +'<span class="ret-card-ratio '+rc+'"><i class="fa-solid fa-chart-pie"></i> '+rt+'%</span>'
      +locHtml
      +'</div>'
      +'</div>';
  }).join('');
}

/* --- Render List --- */
function rRenderList(list){
  document.getElementById('rcount').textContent='Showing '+list.length.toLocaleString()+' of '+allR.length.toLocaleString();
  if(rViewMode==='grid'){ rRenderGrid(list); return; }
  var tb=document.getElementById('rtbody');
  if(!list.length){ tb.innerHTML='<tr><td colspan="9" style="text-align:center;padding:24px;color:#94a3b8;">No retailers found.</td></tr>'; return; }
  tb.innerHTML=list.map(function(r,i){
    var rt=rRatio(r),rc=rt>=80?'good':rt>=50?'avg':'poor';
    var loc=(r.lat&&r.lng&&parseFloat(r.lat)!=0)
      ?'<span style="font-size:.72rem;color:#3b82f6;"><i class="fa-solid fa-location-dot"></i> '+parseFloat(r.lat).toFixed(4)+', '+parseFloat(r.lng).toFixed(4)+'</span>'
      :'<span class="ret-no-loc"><i class="fa-solid fa-location-xmark"></i> No GPS</span>';
    return '<tr onclick="rOpen('+r.id+')">'
      +'<td style="color:#94a3b8;">'+(i+1)+'</td>'
      +'<td class="name-cell">'+rEsc(r.name)+'</td>'
      +'<td>'+rEsc(r.phone||'—')+'</td>'
      +'<td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;">'+rEsc(r.address||'—')+'</td>'
      +'<td>'+loc+'</td>'
      +'<td style="text-align:center;">'+(r.total_orders||0)+'</td>'
      +'<td>&#2547; '+rFmt(r.total_ordered_value)+'</td>'
      +'<td>&#2547; '+rFmt(r.total_delivered_value)+'</td>'
      +'<td><span class="ret-ratio '+rc+'">'+rt+'%</span></td>'
      +'</tr>';
  }).join('');
}

/* --- Modal Open --- */
function rOpen(id){
  var r=allR.filter(function(x){ return x.id==id; })[0];
  if(!r) return;
  curR=r;
  rShowView(r);
  document.getElementById('roverlay').classList.add('open');
}

/* --- View Mode --- */
function rShowView(r){
  document.getElementById('rmodal-title').textContent=r.name;
  var rt=rRatio(r),rc=rt>=80?'good':rt>=50?'avg':'poor';
  var loc=(r.lat&&r.lng&&parseFloat(r.lat)!=0)
    ?'<code style="font-size:.8rem;background:#f1f5f9;padding:1px 6px;border:1px solid #e2e8f0;">'+parseFloat(r.lat).toFixed(6)+', '+parseFloat(r.lng).toFixed(6)+'</code>'
    :'<span style="color:#94a3b8;">No coordinates</span>';
  document.getElementById('rview').style.display='';
  document.getElementById('redit').style.display='none';
  document.getElementById('rinfo').innerHTML=
    '<div class="rinfo-item"><strong>ID:</strong> #'+r.id+'</div>'+
    '<div class="rinfo-item"><strong>Phone:</strong> '+rEsc(r.phone||'—')+'</div>'+
    '<div class="rinfo-item"><strong>Address:</strong> '+rEsc(r.address||'—')+'</div>'+
    '<div class="rinfo-item"><strong>Location (Lat, Lng):</strong> '+loc+'</div>'+
    '<div class="rinfo-item"><strong>Registered:</strong> '+(r.created_at?r.created_at.substring(0,10):'—')+'</div>';
  document.getElementById('rstats').innerHTML=
    '<div class="rstat"><div class="sl"><i class="fa-solid fa-file-invoice"></i> Total Orders</div><div class="sv">'+(r.total_orders||0)+'</div></div>'+
    '<div class="rstat"><div class="sl"><i class="fa-solid fa-money-bill-wave"></i> Total Ordered Value</div><div class="sv">&#2547; '+rFmt(r.total_ordered_value)+'</div></div>'+
    '<div class="rstat"><div class="sl"><i class="fa-solid fa-truck"></i> Delivered Orders</div><div class="sv">'+(r.delivered_orders||0)+'</div></div>'+
    '<div class="rstat"><div class="sl"><i class="fa-solid fa-circle-check"></i> Delivered Value</div><div class="sv">&#2547; '+rFmt(r.total_delivered_value)+'</div></div>'+
    '<div class="rstat '+rc+'" style="grid-column:span 2"><div class="sl"><i class="fa-solid fa-chart-pie"></i> Success Ratio (Delivered / Total Orders)</div><div class="sv">'+rt+'%</div></div>';
  document.getElementById('ract').innerHTML=
    '<button class="rbtn rbtn-primary" onclick="rEnterEdit()"><i class="fa-solid fa-pen-to-square"></i> Edit Info</button>'+
    '<button class="rbtn rbtn-disabled" disabled><i class="fa-solid fa-code-merge"></i> Merge Retailer <span class="cs-badge">Coming Soon</span></button>'+
    '<button class="rbtn rbtn-gray" onclick="rClose()" style="margin-left:auto;"><i class="fa-solid fa-xmark"></i> Close</button>';
}

/* --- Edit Mode --- */
function rEnterEdit(){
  var r=curR;
  document.getElementById('rview').style.display='none';
  document.getElementById('redit').style.display='';
  document.getElementById('ename').value=r.name||'';
  document.getElementById('ephone').value=r.phone||'';
  document.getElementById('eaddr').value=r.address||'';
  document.getElementById('elat').value=r.lat||'';
  document.getElementById('elng').value=r.lng||'';
  document.getElementById('ract').innerHTML=
    '<button class="rbtn rbtn-primary" onclick="rSave()" id="rbsave"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>'+
    '<button class="rbtn rbtn-gray" onclick="rShowView(curR)"><i class="fa-solid fa-xmark"></i> Cancel</button>';
}

function rSave(){
  var id=curR.id,
      name=document.getElementById('ename').value.trim(),
      phone=document.getElementById('ephone').value.trim(),
      addr=document.getElementById('eaddr').value.trim(),
      lat=document.getElementById('elat').value.trim(),
      lng=document.getElementById('elng').value.trim();
  if(!name){ alert('Name is required.'); return; }
  var btn=document.getElementById('rbsave');
  btn.disabled=true; btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
  fetch(BURL+'/manager/api/retailers/update',{
    method:'POST',headers:{'Content-Type':'application/json'},
    body:JSON.stringify({id:id,name:name,phone:phone,address:addr,lat:lat,lng:lng})
  }).then(function(x){ return x.json(); }).then(function(d){
    if(d.success){
      var i=allR.findIndex(function(x){ return x.id==id; });
      if(i>=0){ allR[i]=Object.assign({},allR[i],{name:name,phone:phone,address:addr,lat:lat?parseFloat(lat):null,lng:lng?parseFloat(lng):null}); curR=allR[i]; }
      rShowView(curR); rRenderList(filtR); rDrawMarkers(allR);
      rToast('Retailer updated successfully.','success');
    } else {
      alert(d.message||'Failed to update.');
      btn.disabled=false; btn.innerHTML='<i class="fa-solid fa-floppy-disk"></i> Save Changes';
    }
  }).catch(function(){
    alert('Network error.'); btn.disabled=false; btn.innerHTML='<i class="fa-solid fa-floppy-disk"></i> Save Changes';
  });
}

function rClose(){ document.getElementById('roverlay').classList.remove('open'); curR=null; }
function rCloseOvl(e){ if(e.target===document.getElementById('roverlay')) rClose(); }

/* --- Helpers --- */
function rRatio(r){ var t=parseInt(r.total_orders)||0,d=parseInt(r.delivered_orders)||0; return t?Math.round(d/t*100):0; }
function rFmt(v){ return (parseFloat(v)||0).toLocaleString('en-IN',{maximumFractionDigits:0}); }
function rEsc(s){ return (s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function rToast(msg,type){
  var t=document.createElement('div');
  var bg=type==='success'?'#16a34a':'#dc2626', bd=type==='success'?'#15803d':'#b91c1c';
  t.style.cssText='position:fixed;bottom:24px;right:24px;z-index:99999;background:'+bg+';color:#fff;padding:10px 20px;font-size:.82rem;font-weight:700;border:2px solid '+bd+';box-shadow:4px 4px 0 rgba(0,0,0,.25);pointer-events:none;';
  t.textContent=msg; document.body.appendChild(t); setTimeout(function(){ t.remove(); },3000);
}
</script>
