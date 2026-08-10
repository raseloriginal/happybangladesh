<?php 
/**
 * Admin Retailers List and Map View
 */
$pageTitle = 'Retailers'; 
?>
<!-- Leaflet CSS for Map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />

<style>
  /* Tabs UI */
  .tab-buttons {
    display: flex;
    gap: 1rem;
    border-bottom: 1px solid #e2e8f0;
    margin-bottom: 1.5rem;
  }
  .tab-btn {
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    color: #64748b;
    border-bottom: 2px solid transparent;
    transition: all 0.2s ease;
    background: transparent;
    border-top: none;
    border-left: none;
    border-right: none;
    cursor: pointer;
  }
  .tab-btn:hover {
    color: #2563eb;
  }
  .tab-btn.active {
    color: #2563eb;
    border-bottom-color: #2563eb;
  }
  .tab-content {
    display: none;
    animation: slideInFromLeft 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }
  .tab-content.active {
    display: block;
  }
  
  @keyframes slideInFromLeft {
    from { opacity: 0; transform: translateX(-20px); }
    to { opacity: 1; transform: translateX(0); }
  }

  /* Map Container */
  #retailer-map {
    width: 100%;
    height: calc(100vh - 220px);
    min-height: 500px;
    border-radius: 1rem;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    z-index: 1;
  }

  /* Custom Popup Styles */
  .leaflet-popup-content {
    margin: 12px 16px;
    line-height: 1.4;
  }
  .popup-title {
    font-weight: 700;
    color: #1e293b;
    font-size: 14px;
    margin-bottom: 4px;
  }
  .popup-info {
    color: #64748b;
    font-size: 12px;
  }
  .popup-info i {
    width: 14px;
    text-align: center;
    margin-right: 4px;
  }
</style>

<div class="page-header">
  <div>
    <h1 class="page-title flex items-center gap-2">
      <i class="fa-solid fa-store text-blue-600"></i> Retailers
    </h1>
    <div class="breadcrumb">Admin &rsaquo; Retailers</div>
  </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
  
  <!-- Tabs -->
  <div class="tab-buttons">
    <button class="tab-btn active" onclick="switchTab('list')">
      <i class="fa-solid fa-list mr-2"></i> List View
    </button>
    <button class="tab-btn" onclick="switchTab('map')">
      <i class="fa-solid fa-map-location-dot mr-2"></i> Map View
    </button>
  </div>

  <!-- TAB 1: LIST VIEW -->
  <div id="tab-list" class="tab-content active">
    
    <!-- Toolbar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
      <div class="text-sm text-slate-500 font-medium">
        Total <span class="text-blue-600 font-bold"><?= number_format($totalRows) ?></span> retailers found
      </div>
      <form method="GET" action="<?= url('admin/retailers') ?>" class="flex w-full sm:w-auto gap-2">
        <input type="text" name="search" value="<?= h($search) ?>" placeholder="Search by name, phone..." 
               class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm w-full sm:w-64 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
        <button type="submit" class="btn btn-primary px-4">
          <i class="fa-solid fa-magnifying-glass"></i>
        </button>
        <?php if ($search): ?>
          <a href="<?= url('admin/retailers') ?>" class="btn btn-secondary px-4 text-slate-500">
            <i class="fa-solid fa-times"></i>
          </a>
        <?php endif; ?>
      </form>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto rounded-xl border border-slate-200">
      <table class="w-full text-left border-collapse whitespace-nowrap">
        <thead>
          <tr class="bg-slate-50 border-b border-slate-200 text-xs uppercase tracking-wider text-slate-500">
            <th class="px-4 py-3 font-semibold">#</th>
            <th class="px-4 py-3 font-semibold">Retailer Name</th>
            <th class="px-4 py-3 font-semibold">Phone</th>
            <th class="px-4 py-3 font-semibold">Coordinates</th>
            <th class="px-4 py-3 font-semibold">Address</th>
            <th class="px-4 py-3 font-semibold">Created At</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php if (empty($items)): ?>
            <tr>
              <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                <i class="fa-solid fa-store-slash text-4xl mb-3 opacity-20"></i>
                <p>No retailers found.</p>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($items as $index => $item): ?>
            <tr class="hover:bg-slate-50 transition-colors">
              <td class="px-4 py-3 text-sm text-slate-500"><?= $offset + $index + 1 ?></td>
              <td class="px-4 py-3 text-sm font-bold text-slate-800">
                <?= h($item['name']) ?>
              </td>
              <td class="px-4 py-3 text-sm text-slate-600 font-mono">
                <?= h($item['phone'] ?? 'N/A') ?>
              </td>
              <td class="px-4 py-3 text-xs text-slate-500 font-mono">
                <?php if ($item['lat'] && $item['lng']): ?>
                  <a href="https://maps.google.com/?q=<?= $item['lat'] ?>,<?= $item['lng'] ?>" target="_blank" class="text-blue-500 hover:underline">
                    <i class="fa-solid fa-location-dot mr-1"></i><?= $item['lat'] ?>, <?= $item['lng'] ?>
                  </a>
                <?php else: ?>
                  <span class="text-slate-300">Not set</span>
                <?php endif; ?>
              </td>
              <td class="px-4 py-3 text-sm text-slate-500">
                <?= h($item['address'] ?? '—') ?>
              </td>
              <td class="px-4 py-3 text-xs text-slate-400 font-mono">
                <?= Helpers::date($item['created_at']) ?>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4 px-1">
      <div class="text-sm text-slate-500">
        Page <span class="font-bold text-slate-700"><?= $page ?></span> of <span class="font-bold text-slate-700"><?= $totalPages ?></span>
      </div>
      <div class="flex items-center gap-2">
        <?php 
          $prevQuery = $_GET; $prevQuery['page'] = max(1, $page - 1);
          $nextQuery = $_GET; $nextQuery['page'] = min($totalPages, $page + 1);
        ?>
        <a href="<?= url('admin/retailers?' . http_build_query($prevQuery)) ?>" 
           class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg transition-colors <?= $page <= 1 ? 'text-slate-400 bg-slate-50 pointer-events-none' : 'text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 hover:text-blue-600 shadow-sm' ?>">
          <i class="fa-solid fa-chevron-left text-[10px]"></i> Prev
        </a>
        <a href="<?= url('admin/retailers?' . http_build_query($nextQuery)) ?>" 
           class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg transition-colors <?= $page >= $totalPages ? 'text-slate-400 bg-slate-50 pointer-events-none' : 'text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 hover:text-blue-600 shadow-sm' ?>">
          Next <i class="fa-solid fa-chevron-right text-[10px]"></i>
        </a>
      </div>
    </div>
    <?php endif; ?>

  </div>

  <!-- TAB 2: MAP VIEW -->
  <div id="tab-map" class="tab-content relative">
    <!-- Overlay while map loads -->
    <div id="map-loader" class="absolute inset-0 z-10 bg-white/80 backdrop-blur-sm flex items-center justify-center rounded-xl">
      <div class="text-center">
        <i class="fa-solid fa-circle-notch fa-spin text-4xl text-blue-500 mb-3"></i>
        <div class="text-slate-500 font-medium text-sm">Loading map & retailers...</div>
      </div>
    </div>
    <div id="retailer-map"></div>
  </div>

</div>

<!-- Leaflet JS & MarkerCluster -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
<script>
  let mapInitialized = false;
  let map = null;

  // Retailer Data injected from PHP
  const retailerData = <?= json_encode($mapData ?? []) ?>;

  function switchTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
    
    // Set active tab
    event.currentTarget.classList.add('active');
    document.getElementById('tab-' + tab).classList.add('active');

    // Initialize map if it's the first time viewing the map tab
    if (tab === 'map' && !mapInitialized) {
      initMap();
    }
  }

  function initMap() {
    mapInitialized = true;
    
    // Default center to Bangladesh coordinates
    map = L.map('retailer-map').setView([23.8103, 90.4125], 7);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
      attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
      subdomains: 'abcd',
      maxZoom: 20
    }).addTo(map);

    const bounds = [];
    
    // Custom Icon
    const storeIcon = L.divIcon({
      className: 'custom-div-icon',
      html: `<div style="background-color:#2563eb; width:24px; height:24px; border-radius:50%; display:flex; align-items:center; justify-content:center; border:2px solid white; box-shadow:0 2px 5px rgba(0,0,0,0.3); color:white; font-size:12px;">
              <i class="fa-solid fa-store"></i>
             </div>`,
      iconSize: [24, 24],
      iconAnchor: [12, 12]
    });

    if (retailerData.length > 0) {
      const markers = L.markerClusterGroup({
        chunkedLoading: true,
        maxClusterRadius: 50
      });

      retailerData.forEach(retailer => {
        const lat = parseFloat(retailer.lat);
        const lng = parseFloat(retailer.lng);
        
        if (!isNaN(lat) && !isNaN(lng)) {
          const marker = L.marker([lat, lng], { icon: storeIcon });
          
          let popupContent = `<div class="popup-title">${retailer.name}</div>`;
          if(retailer.phone) popupContent += `<div class="popup-info"><i class="fa-solid fa-phone"></i> ${retailer.phone}</div>`;
          if(retailer.address) popupContent += `<div class="popup-info"><i class="fa-solid fa-map-pin"></i> ${retailer.address}</div>`;
          
          marker.bindPopup(popupContent);
          markers.addLayer(marker);
          bounds.push([lat, lng]);
        }
      });
      
      map.addLayer(markers);
      
      // Fit map to show all retailers if any exist
      if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [50, 50] });
      }
    }

    // Hide loader
    document.getElementById('map-loader').style.display = 'none';
  }

  // Drag to scroll for the table
  const slider = document.querySelector('.overflow-x-auto');
  let isDown = false;
  let startX;
  let scrollLeft;

  slider.addEventListener('mousedown', (e) => {
    isDown = true;
    slider.style.cursor = 'grabbing';
    startX = e.pageX - slider.offsetLeft;
    scrollLeft = slider.scrollLeft;
  });
  slider.addEventListener('mouseleave', () => {
    isDown = false;
    slider.style.cursor = 'auto';
  });
  slider.addEventListener('mouseup', () => {
    isDown = false;
    slider.style.cursor = 'auto';
  });
  slider.addEventListener('mousemove', (e) => {
    if (!isDown) return;
    e.preventDefault();
    const x = e.pageX - slider.offsetLeft;
    const walk = (x - startX) * 2; // Scroll-fast multiplier
    slider.scrollLeft = scrollLeft - walk;
  });
</script>
