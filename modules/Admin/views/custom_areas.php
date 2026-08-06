<?php
/**
 * Custom Area Map Management Page — Admin Panel
 */
?>

<!-- Leaflet CSS & Geoman (PM) CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
<link rel="stylesheet" href="https://unpkg.com/@geoman-io/leaflet-geoman-free@2.14.2/dist/leaflet-geoman.css" />

<style>
  #custom-map {
    width: 100%;
    height: calc(100vh - 145px);
    min-height: 560px;
    border-radius: 1rem;
    z-index: 1;
  }

  /* Custom Floating Left Tool Control matching reference screenshot UI */
  .map-toolbar-custom {
    position: absolute;
    top: 16px;
    left: 16px;
    z-index: 999;
    display: flex;
    flex-direction: column;
    gap: 4px;
    background: #ffffff;
    padding: 6px;
    border-radius: 12px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.12), 0 8px 10px -6px rgba(0, 0, 0, 0.08);
    border: 1px solid #e2e8f0;
  }

  .map-toolbar-custom button {
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    color: #475569;
    background: transparent;
    border: 1px solid transparent;
    transition: all 0.15s ease;
    font-size: 15px;
  }

  .map-toolbar-custom button:hover {
    background: #f1f5f9;
    color: #2563eb;
  }

  .map-toolbar-custom button.active {
    background: #eff6ff;
    color: #2563eb;
    border-color: #bfdbfe;
    font-weight: bold;
    box-shadow: inset 0 0 0 1px #3b82f6;
  }

  .map-toolbar-custom .divider {
    height: 1px;
    background: #f1f5f9;
    margin: 2px 4px;
  }

  /* Floating Map Style Switcher (Top Right) */
  .map-style-switcher {
    position: absolute;
    top: 16px;
    left: 70px;
    z-index: 999;
    background: #ffffff;
    padding: 4px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    border: 1px solid #e2e8f0;
    display: flex;
    gap: 2px;
  }

  .map-style-switcher button {
    padding: 4px 10px;
    font-size: 11px;
    font-weight: 600;
    border-radius: 6px;
    color: #64748b;
    transition: all 0.15s ease;
  }

  .map-style-switcher button.active {
    background: #2563eb;
    color: #ffffff;
  }

  /* Right floating Areas List Drawer */
  .area-drawer {
    position: absolute;
    top: 16px;
    right: 16px;
    z-index: 999;
    width: 320px;
    max-height: calc(100vh - 180px);
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(12px);
    border-radius: 1rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(226, 232, 240, 0.9);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: transform 0.25s ease, opacity 0.25s ease;
  }

  @media (max-width: 640px) {
    .area-drawer {
      width: calc(100% - 32px);
      max-height: 240px;
      top: auto;
      bottom: 16px;
      right: 16px;
      left: 16px;
    }
  }

  .leaflet-pm-toolbar {
    display: none !important; /* Disables default Geoman floating panel to render custom controls */
  }

  /* Custom Leaflet Popup styling */
  .leaflet-popup-content-wrapper {
    border-radius: 12px !important;
    padding: 2px !important;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15) !important;
    border: 1px solid #e2e8f0;
  }

  .leaflet-popup-content {
    margin: 12px 14px !important;
    font-family: 'Inter', sans-serif !important;
  }

  /* Color preset badges */
  .color-preset {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.15s ease;
  }
  .color-preset:hover {
    transform: scale(1.15);
  }
  .color-preset.selected {
    border-color: #0f172a;
    box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.9);
  }
</style>

<div class="space-y-4">

  <!-- Header & Action Bar -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-blue-600/10 text-blue-600 flex items-center justify-center font-bold text-lg">
        <i class="fa-solid fa-draw-polygon"></i>
      </div>
      <div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Custom Area & Zone Management</h1>
        <p class="text-xs text-slate-500">Draw custom polygon areas, boundaries, and coverage zones on the map</p>
      </div>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
      <button onclick="zoomToBangladesh()" class="px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors flex items-center gap-1.5">
        <i class="fa-solid fa-compass text-slate-500"></i> Center Map
      </button>
      <button onclick="toggleAllAreasVisibility()" id="btn-toggle-all" class="px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors flex items-center gap-1.5">
        <i class="fa-solid fa-eye text-slate-500"></i> Hide/Show All
      </button>
      <button onclick="startDrawPolygon()" class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-500/20 transition-all flex items-center gap-2">
        <i class="fa-solid fa-plus"></i> Draw New Area
      </button>
    </div>
  </div>

  <!-- Main Map Container -->
  <div class="relative w-full rounded-2xl overflow-hidden border border-slate-200 shadow-sm bg-slate-100">

    <!-- The Map -->
    <div id="custom-map"></div>

    <!-- Map Style Switcher (Top left) -->
    <div class="map-style-switcher">
      <button id="tile-google_streets" onclick="switchMapStyle('google_streets')" class="active">Google Map</button>
      <button id="tile-google_hybrid" onclick="switchMapStyle('google_hybrid')">Google Hybrid</button>
      <button id="tile-google_terrain" onclick="switchMapStyle('google_terrain')">Terrain</button>
      <button id="tile-voyager" onclick="switchMapStyle('voyager')">Light</button>
    </div>

    <!-- Custom Floating Left Toolbar (Identical to reference screenshot controls) -->
    <div class="map-toolbar-custom">
      <button onclick="map.zoomIn()" title="Zoom In">
        <i class="fa-solid fa-plus"></i>
      </button>
      <button onclick="map.zoomOut()" title="Zoom Out">
        <i class="fa-solid fa-minus"></i>
      </button>
      
      <div class="divider"></div>

      <button id="btn-tool-polygon" onclick="startDrawPolygon()" title="Draw Custom Polygon Area">
        <i class="fa-solid fa-draw-polygon"></i>
      </button>

      <div class="divider"></div>

      <button id="btn-tool-edit" onclick="toggleEditMode()" title="Edit Saved Shapes">
        <i class="fa-solid fa-pen-to-square"></i>
      </button>
      <button id="btn-tool-delete" onclick="toggleDeleteMode()" title="Eraser / Delete Shape">
        <i class="fa-solid fa-eraser"></i>
      </button>
      
      <div class="divider"></div>

      <button onclick="resetMapView()" title="Reset Map View">
        <i class="fa-solid fa-rotate-right"></i>
      </button>
    </div>

    <!-- Right Floating Side Drawer (Area List & Stats) -->
    <div id="areaDrawer" class="area-drawer">
      <div class="p-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
        <div class="flex items-center gap-2">
          <span class="text-xs font-bold uppercase tracking-wider text-slate-800">Saved Areas</span>
          <span id="area-count-badge" class="bg-blue-100 text-blue-700 text-[11px] font-black px-2 py-0.5 rounded-full">0</span>
        </div>
        <button onclick="fetchCustomAreas()" class="text-xs text-slate-400 hover:text-slate-600 transition-colors" title="Reload Areas">
          <i class="fa-solid fa-rotate"></i>
        </button>
      </div>

      <div class="p-2 border-b border-slate-100">
        <div class="relative">
          <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-xs text-slate-400"></i>
          <input type="text" id="area-search" onkeyup="filterAreaList()" placeholder="Search custom areas..."
                 class="w-full pl-8 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500">
        </div>
      </div>

      <!-- Area Cards List -->
      <div id="area-list-container" class="flex-1 overflow-y-auto p-2 space-y-2 max-h-[420px]">
        <div class="text-center py-8 text-slate-400 text-xs">
          <i class="fa-solid fa-spinner fa-spin text-lg mb-2 text-blue-500"></i>
          <p>Loading custom areas...</p>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Modal for Area Creation / Edit -->
<div id="areaModal" class="fixed inset-0 hidden bg-slate-900/70 backdrop-blur-md flex items-center justify-center p-4" style="z-index: 999999 !important;">
  <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl border border-slate-100 overflow-hidden transform transition-all">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 bg-slate-50/80">
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-blue-600/10 text-blue-600 flex items-center justify-center font-bold text-sm">
          <i class="fa-solid fa-layer-group"></i>
        </div>
        <h3 id="modalTitle" class="text-base font-bold text-slate-900">Save Custom Area</h3>
      </div>
      <button onclick="closeAreaModal()" class="text-slate-400 hover:text-slate-600 text-lg">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <form id="areaForm" onsubmit="handleSaveArea(event)" class="p-5 space-y-4">
      <input type="hidden" id="area-id" value="">
      <input type="hidden" id="area-type" value="polygon">
      <input type="hidden" id="area-coordinates" value="">

      <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1">Area Name <span class="text-rose-500">*</span></label>
        <input type="text" id="area-name" required placeholder="e.g. Sardah Police Academy Zone"
               class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1">Description / Remarks</label>
        <textarea id="area-desc" rows="2" placeholder="Optional details about this zone..."
                  class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"></textarea>
      </div>

      <!-- Color Palette Selection -->
      <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Area Theme & Color</label>
        <div class="flex items-center gap-3">
          <div class="flex items-center gap-2">
            <div class="color-preset selected" style="background:#22c55e;" onclick="selectColorPreset('#22c55e', '#86efac', this)"></div>
            <div class="color-preset" style="background:#3b82f6;" onclick="selectColorPreset('#3b82f6', '#93c5fd', this)"></div>
            <div class="color-preset" style="background:#ef4444;" onclick="selectColorPreset('#ef4444', '#fca5a5', this)"></div>
            <div class="color-preset" style="background:#f97316;" onclick="selectColorPreset('#f97316', '#fdba74', this)"></div>
            <div class="color-preset" style="background:#8b5cf6;" onclick="selectColorPreset('#8b5cf6', '#c4b5fd', this)"></div>
          </div>
          <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
            <input type="color" id="area-stroke-color" value="#22c55e" class="w-8 h-8 rounded cursor-pointer border-0 p-0">
            <span class="text-[11px] text-slate-500 font-medium">Border</span>
          </div>
        </div>
        <input type="hidden" id="area-fill-color" value="#86efac">
      </div>

      <!-- Assignment Option -->
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Assign Type</label>
          <select id="area-assign-type" onchange="onAssignTypeChange()" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">None (General Zone)</option>
            <option value="sr">Sales Rep (SR)</option>
            <option value="dsr">Delivery SR (DSR)</option>
            <option value="warehouse">Warehouse</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Assignee</label>
          <select id="area-assign-id" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Select Assignee</option>
          </select>
        </div>
      </div>

      <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
        <button type="button" onclick="closeAreaModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
          Cancel
        </button>
        <button type="submit" id="btn-save-area" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-500/20 transition-all">
          Save Area
        </button>
      </div>
    </form>
  </div>
</div>

<!-- JS Libraries -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script src="https://unpkg.com/@geoman-io/leaflet-geoman-free@2.14.2/dist/leaflet-geoman.min.js"></script>

<script>
  const BASE_URL = '<?= BASE_URL ?>';

  // Data passed from controller
  const SRS_DATA = <?= json_encode($srs ?? []) ?>;
  const DSRS_DATA = <?= json_encode($dsrs ?? []) ?>;
  const WAREHOUSES_DATA = <?= json_encode($warehouses ?? []) ?>;

  let map;
  let tileLayers = {};
  let drawnItemsGroup;
  let areaLayersMap = {}; // id -> L.Layer
  let areaDataList = [];
  let currentTempLayer = null;
  let isEditingMode = false;
  let isDeleteMode = false;
  let isAllVisible = true;

  document.addEventListener('DOMContentLoaded', () => {
    initMap();
    fetchCustomAreas();
  });

  function initMap() {
    // Center of Rajshahi/Charghat/Sardah region as seen in user reference image
    const defaultCenter = [24.2865, 88.7510];
    map = L.map('custom-map', {
      zoomControl: false
    }).setView(defaultCenter, 13);

    // Google Maps Tiles (Ultra-fast CDN tile server mt0..mt3)
    tileLayers.google_streets = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
      maxZoom: 20,
      subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
      updateWhenIdle: false,
      updateWhenZooming: true,
      keepBuffer: 3,
      attribution: '&copy; Google Maps'
    });

    tileLayers.google_hybrid = L.tileLayer('https://{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
      maxZoom: 20,
      subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
      updateWhenIdle: false,
      updateWhenZooming: true,
      keepBuffer: 3,
      attribution: '&copy; Google Maps'
    });

    tileLayers.google_terrain = L.tileLayer('https://{s}.google.com/vt/lyrs=p&x={x}&y={y}&z={z}', {
      maxZoom: 20,
      subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
      updateWhenIdle: false,
      updateWhenZooming: true,
      keepBuffer: 3,
      attribution: '&copy; Google Maps'
    });

    tileLayers.voyager = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap &copy; CARTO'
    });

    // Default tile layer: Google Maps Streets
    tileLayers.google_streets.addTo(map);

    drawnItemsGroup = L.featureGroup().addTo(map);

    // Init Geoman (Leaflet PM) controls
    map.pm.setGlobalOptions({
      allowSelfIntersection: false,
      snapping: true,
      snapDistance: 20
    });

    // Listen for creation of shapes
    map.on('pm:create', (e) => {
      const { shape, layer } = e;
      currentTempLayer = layer;
      
      const geojson = layer.toGeoJSON();
      const typeMap = {
        'Polygon': 'polygon',
        'Rectangle': 'rectangle',
        'Line': 'polyline',
        'Marker': 'marker',
        'Circle': 'circle'
      };

      const shapeType = typeMap[shape] || 'polygon';
      
      openAreaModal({
        type: shapeType,
        coordinates: geojson.geometry
      });
    });

    // Listen for edits on shapes
    map.on('pm:globaleditmodetoggled', (e) => {
      isEditingMode = e.enabled;
      document.getElementById('btn-tool-edit').classList.toggle('active', isEditingMode);
      if (!isEditingMode) {
        syncEditedShapes();
      }
    });

    // Listen for shape removal
    map.on('pm:remove', (e) => {
      const layer = e.layer;
      if (layer._areaId) {
        deleteAreaById(layer._areaId);
      }
    });
  }

  function switchMapStyle(type) {
    Object.values(tileLayers).forEach(layer => map.removeLayer(layer));
    if (tileLayers[type]) {
      tileLayers[type].addTo(map);
    }
    ['google_streets', 'google_hybrid', 'google_terrain', 'voyager'].forEach(t => {
      const btn = document.getElementById('tile-' + t);
      if (btn) btn.classList.toggle('active', t === type);
    });
  }

  function resetToolButtons() {
    const tools = ['btn-tool-polygon', 'btn-tool-edit', 'btn-tool-delete'];
    tools.forEach(id => {
      const btn = document.getElementById(id);
      if (btn) btn.classList.remove('active');
    });
  }

  function startDrawPolygon() {
    resetToolButtons();
    document.getElementById('btn-tool-polygon').classList.add('active');
    map.pm.enableDraw('Polygon', {
      snapping: true,
      pathOptions: { color: '#22c55e', fillColor: '#86efac', fillOpacity: 0.4 }
    });
  }

  function startDrawRectangle() {
    resetToolButtons();
    document.getElementById('btn-tool-rectangle').classList.add('active');
    map.pm.enableDraw('Rectangle', {
      pathOptions: { color: '#3b82f6', fillColor: '#93c5fd', fillOpacity: 0.4 }
    });
  }

  function startDrawLine() {
    resetToolButtons();
    document.getElementById('btn-tool-line').classList.add('active');
    map.pm.enableDraw('Line', {
      pathOptions: { color: '#ef4444', weight: 4 }
    });
  }

  function startDrawMarker() {
    resetToolButtons();
    document.getElementById('btn-tool-marker').classList.add('active');
    map.pm.enableDraw('Marker');
  }

  function toggleEditMode() {
    resetToolButtons();
    map.pm.toggleGlobalEditMode();
  }

  function toggleDeleteMode() {
    resetToolButtons();
    map.pm.toggleGlobalRemovalMode();
    isDeleteMode = map.pm.globalRemovalEnabled();
    document.getElementById('btn-tool-delete').classList.toggle('active', isDeleteMode);
  }

  function resetMapView() {
    map.pm.disableDraw();
    map.pm.disableGlobalEditMode();
    map.pm.disableGlobalRemovalMode();
    resetToolButtons();
    if (drawnItemsGroup.getLayers().length > 0) {
      map.fitBounds(drawnItemsGroup.getBounds(), { padding: [40, 40] });
    } else {
      zoomToBangladesh();
    }
  }

  function zoomToBangladesh() {
    map.setView([24.2865, 88.7510], 13);
  }

  // Fetch areas from DB API
  async function fetchCustomAreas() {
    const listEl = document.getElementById('area-list-container');
    try {
      const res = await fetch(BASE_URL + '/admin/api/custom-areas');
      const data = await res.json();

      if (data.success) {
        areaDataList = data.data;
        renderAreaList(areaDataList);
        renderAreasOnMap(areaDataList);
      } else {
        listEl.innerHTML = `<div class="p-4 text-xs text-rose-500 text-center">Failed to load custom areas</div>`;
      }
    } catch (err) {
      console.error(err);
      listEl.innerHTML = `<div class="p-4 text-xs text-rose-500 text-center">Error connecting to server</div>`;
    }
  }

  function renderAreasOnMap(areas) {
    drawnItemsGroup.clearLayers();
    areaLayersMap = {};

    areas.forEach(area => {
      if (!area.coordinates) return;

      try {
        let geojsonFeature = {
          type: "Feature",
          geometry: area.coordinates,
          properties: {
            id: area.id,
            name: area.name
          }
        };

        const layer = L.geoJSON(geojsonFeature, {
          style: {
            color: area.stroke_color || '#3b82f6',
            fillColor: area.fill_color || '#93c5fd',
            fillOpacity: parseFloat(area.fill_opacity) || 0.35,
            weight: 2.5
          },
          pointToLayer: (feature, latlng) => {
            return L.marker(latlng);
          }
        }).getLayers()[0];

        if (layer) {
          layer._areaId = area.id;
          
          const popupContent = `
            <div class="p-1 min-w-[160px]">
              <div class="font-bold text-sm text-slate-900">${escapeHtml(area.name)}</div>
              ${area.description ? `<div class="text-xs text-slate-500 mt-1">${escapeHtml(area.description)}</div>` : ''}
              ${area.assigned_type ? `<div class="mt-2 text-[10px] uppercase font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded inline-block">Assigned: ${area.assigned_type}</div>` : ''}
            </div>
          `;
          layer.bindPopup(popupContent);
          
          drawnItemsGroup.addLayer(layer);
          areaLayersMap[area.id] = layer;
        }
      } catch (err) {
        console.error("Error rendering area", area.id, err);
      }
    });

    document.getElementById('area-count-badge').innerText = areas.length;
  }

  function renderAreaList(areas) {
    const container = document.getElementById('area-list-container');
    if (areas.length === 0) {
      container.innerHTML = `
        <div class="text-center py-8 text-slate-400 text-xs">
          <i class="fa-solid fa-shapes text-2xl mb-2 text-slate-300"></i>
          <p>No custom areas created yet.</p>
          <button onclick="startDrawPolygon()" class="mt-2 text-blue-600 font-semibold hover:underline">Draw area now</button>
        </div>`;
      return;
    }

    container.innerHTML = areas.map(area => `
      <div id="area-item-${area.id}" class="group p-2.5 rounded-xl border border-slate-200/80 bg-white hover:border-blue-300 hover:shadow-sm transition-all flex items-center justify-between gap-2">
        <div class="flex items-center gap-2.5 min-w-0 flex-1">
          <span class="w-3.5 h-3.5 rounded-full flex-shrink-0 border border-black/10" style="background:${area.stroke_color}"></span>
          <div class="min-w-0 flex-1">
            <div class="text-xs font-bold text-slate-800 truncate">${escapeHtml(area.name)}</div>
            <div class="text-[10px] text-slate-400 flex items-center gap-1 mt-0.5">
              <span class="capitalize">${area.type}</span>
              ${area.assigned_type ? `• <span class="text-blue-600 font-medium">${area.assigned_type}</span>` : ''}
            </div>
          </div>
        </div>

        <div class="flex items-center gap-1">
          <button onclick="focusAreaOnMap(${area.id})" class="p-1.5 text-slate-400 hover:text-blue-600 rounded-lg hover:bg-slate-50 transition-colors" title="Zoom to Area">
            <i class="fa-solid fa-crosshairs text-xs"></i>
          </button>
          <button onclick="editAreaDetails(${area.id})" class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-slate-50 transition-colors" title="Edit Properties">
            <i class="fa-solid fa-pen text-xs"></i>
          </button>
          <button onclick="deleteAreaById(${area.id})" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition-colors" title="Delete Area">
            <i class="fa-solid fa-trash-can text-xs"></i>
          </button>
        </div>
      </div>
    `).join('');
  }

  function filterAreaList() {
    const q = document.getElementById('area-search').value.toLowerCase();
    const filtered = areaDataList.filter(a => a.name.toLowerCase().includes(q) || (a.description && a.description.toLowerCase().includes(q)));
    renderAreaList(filtered);
  }

  function focusAreaOnMap(id) {
    const layer = areaLayersMap[id];
    if (layer) {
      if (layer.getBounds) {
        map.fitBounds(layer.getBounds(), { padding: [60, 60] });
      } else if (layer.getLatLng) {
        map.setView(layer.getLatLng(), 15);
      }
      layer.openPopup();
    }
  }

  function toggleAllAreasVisibility() {
    isAllVisible = !isAllVisible;
    if (isAllVisible) {
      map.addLayer(drawnItemsGroup);
    } else {
      map.removeLayer(drawnItemsGroup);
    }
    document.getElementById('btn-toggle-all').classList.toggle('bg-blue-50', !isAllVisible);
  }

  // Modal Handlers
  function openAreaModal(data = {}) {
    const modal = document.getElementById('areaModal');
    if (modal && modal.parentElement !== document.body) {
      document.body.appendChild(modal);
    }
    document.getElementById('area-id').value = data.id || '';
    document.getElementById('area-type').value = data.type || 'polygon';
    document.getElementById('area-coordinates').value = JSON.stringify(data.coordinates || {});
    document.getElementById('area-name').value = data.name || '';
    document.getElementById('area-desc').value = data.description || '';
    
    if (data.stroke_color) document.getElementById('area-stroke-color').value = data.stroke_color;
    if (data.fill_color) document.getElementById('area-fill-color').value = data.fill_color;

    document.getElementById('area-assign-type').value = data.assigned_type || '';
    onAssignTypeChange();
    if (data.assigned_id) document.getElementById('area-assign-id').value = data.assigned_id;

    document.getElementById('modalTitle').innerText = data.id ? 'Edit Custom Area' : 'Save Custom Area';
    modal.classList.remove('hidden');
  }

  function closeAreaModal() {
    document.getElementById('areaModal').classList.add('hidden');
    if (currentTempLayer && !document.getElementById('area-id').value) {
      map.removeLayer(currentTempLayer);
      currentTempLayer = null;
    }
    resetToolButtons();
  }

  function selectColorPreset(stroke, fill, el) {
    document.querySelectorAll('.color-preset').forEach(b => b.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('area-stroke-color').value = stroke;
    document.getElementById('area-fill-color').value = fill;
  }

  function onAssignTypeChange() {
    const type = document.getElementById('area-assign-type').value;
    const select = document.getElementById('area-assign-id');
    select.innerHTML = '<option value="">Select Assignee</option>';

    let list = [];
    if (type === 'sr') list = SRS_DATA;
    else if (type === 'dsr') list = DSRS_DATA;
    else if (type === 'warehouse') list = WAREHOUSES_DATA;

    list.forEach(item => {
      const opt = document.createElement('option');
      opt.value = item.id;
      opt.textContent = item.name;
      select.appendChild(opt);
    });
  }

  async function handleSaveArea(e) {
    e.preventDefault();

    const id = document.getElementById('area-id').value;
    const payload = {
      name: document.getElementById('area-name').value.trim(),
      description: document.getElementById('area-desc').value.trim(),
      type: document.getElementById('area-type').value,
      coordinates: JSON.parse(document.getElementById('area-coordinates').value || '{}'),
      stroke_color: document.getElementById('area-stroke-color').value,
      fill_color: document.getElementById('area-fill-color').value,
      assigned_type: document.getElementById('area-assign-type').value,
      assigned_id: document.getElementById('area-assign-id').value
    };

    const url = id ? `${BASE_URL}/admin/api/custom-areas/update/${id}` : `${BASE_URL}/admin/api/custom-areas/store`;

    try {
      const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();

      if (data.success) {
        currentTempLayer = null;
        closeAreaModal();
        fetchCustomAreas();
      } else {
        alert(data.message || 'Failed to save custom area');
      }
    } catch (err) {
      console.error(err);
      alert('Error saving area. Please try again.');
    }
  }

  function editAreaDetails(id) {
    const area = areaDataList.find(a => a.id == id);
    if (area) {
      openAreaModal(area);
    }
  }

  async function deleteAreaById(id) {
    if (!confirm('Are you sure you want to delete this custom area?')) return;

    try {
      const res = await fetch(`${BASE_URL}/admin/api/custom-areas/delete/${id}`, { method: 'POST' });
      const data = await res.json();
      if (data.success) {
        fetchCustomAreas();
      } else {
        alert('Failed to delete area.');
      }
    } catch (err) {
      console.error(err);
    }
  }

  async function syncEditedShapes() {
    for (let id in areaLayersMap) {
      const layer = areaLayersMap[id];
      const areaObj = areaDataList.find(a => a.id == id);
      if (layer && areaObj) {
        const newGeojson = layer.toGeoJSON();
        const payload = {
          name: areaObj.name,
          description: areaObj.description,
          type: areaObj.type,
          coordinates: newGeojson.geometry,
          stroke_color: areaObj.stroke_color,
          fill_color: areaObj.fill_color,
          assigned_type: areaObj.assigned_type,
          assigned_id: areaObj.assigned_id
        };

        await fetch(`${BASE_URL}/admin/api/custom-areas/update/${id}`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
      }
    }
  }

  function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
  }
</script>
