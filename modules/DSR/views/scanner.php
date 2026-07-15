<?php $pageTitle = 'QR / Barcode Scanner'; ?>
<div class="page-header">
  <div><h1 class="page-title">Product Scanner</h1><div class="breadcrumb">DSR &rsaquo; Scanner</div></div>
</div>

<div class="max-w-lg mx-auto">
  <div class="card">
    <div class="card-header"><h2 class="card-title"><i class="fa-solid fa-qrcode text-blue-500 mr-2"></i>Scan Product / Lot</h2></div>
    <div class="card-body space-y-5">

      <!-- Camera scanner -->
      <div id="scanner-container" class="hidden rounded-xl overflow-hidden bg-black aspect-video relative">
        <video id="scanner-video" class="w-full h-full object-cover" autoplay muted playsinline></video>
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
          <div class="border-2 border-blue-400 rounded-lg w-48 h-32 opacity-70"></div>
        </div>
      </div>

      <!-- Camera controls -->
      <div class="flex gap-3">
        <button id="start-scan-btn" class="btn btn-primary flex-1">
          <i class="fa-solid fa-camera"></i> Start Camera
        </button>
        <button id="stop-scan-btn" class="btn btn-danger flex-1 hidden">
          <i class="fa-solid fa-stop"></i> Stop Camera
        </button>
      </div>

      <!-- Scan result display -->
      <div class="p-3 bg-gray-50 rounded-lg border">
        <p class="text-xs text-gray-500 mb-1">Scan result:</p>
        <p id="scan-result" class="text-sm text-gray-600 font-medium">Waiting for scan…</p>
      </div>

      <!-- Manual input -->
      <div>
        <label class="form-label">Manual Code Entry</label>
        <div class="flex gap-2">
          <input type="text" id="scan-input" class="form-input flex-1" placeholder="Type or paste barcode / SKU">
          <button id="lookup-btn" type="button" class="btn btn-primary">
            <i class="fa-solid fa-magnifying-glass"></i> Lookup
          </button>
        </div>
        <p class="text-xs text-gray-400 mt-1">You can also use a USB barcode scanner — it will auto-detect.</p>
      </div>

      <!-- Product result card -->
      <div id="product-result" class="hidden p-4 bg-green-50 border border-green-200 rounded-xl">
        <h3 class="font-semibold text-green-700 mb-2"><i class="fa-solid fa-circle-check mr-1"></i>Found</h3>
        <div id="product-info" class="text-sm text-gray-700 space-y-1"></div>
      </div>

      <div id="not-found-result" class="hidden p-4 bg-red-50 border border-red-200 rounded-xl">
        <h3 class="font-semibold text-red-700"><i class="fa-solid fa-circle-xmark mr-1"></i>Not Found</h3>
        <p id="not-found-msg" class="text-sm text-gray-600 mt-1"></p>
      </div>

    </div>
  </div>
</div>

<?php $extraScripts = <<<'JS'
<script>
const lookupBtn     = document.getElementById('lookup-btn');
const scanInput     = document.getElementById('scan-input');
const productResult = document.getElementById('product-result');
const productInfo   = document.getElementById('product-info');
const notFoundDiv   = document.getElementById('not-found-result');
const notFoundMsg   = document.getElementById('not-found-msg');

async function lookup(code) {
  if (!code) return;
  const res  = await fetch('<?= url('dsr/scanner/scan') ?>', {
    method:  'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body:    '_csrf_token=<?= Helpers::csrfToken() ?>&code=' + encodeURIComponent(code)
  });
  const json = await res.json();

  productResult.classList.add('hidden');
  notFoundDiv.classList.add('hidden');

  if (json.success) {
    productResult.classList.remove('hidden');
    const d = json.data;
    if (json.type === 'product') {
      productInfo.innerHTML = `
        <div><b>Name:</b> ${d.name}</div>
        <div><b>SKU:</b> ${d.sku}</div>
        <div><b>Company:</b> ${d.company_name || '—'}</div>
        <div><b>Unit:</b> ${d.unit}</div>
        <div><b>Price:</b> ৳ ${parseFloat(d.price).toFixed(2)}</div>
      `;
    } else {
      productInfo.innerHTML = `
        <div><b>Lot No:</b> ${d.lot_number}</div>
        <div><b>Product:</b> ${d.product_name}</div>
        <div><b>Quantity:</b> ${d.quantity}</div>
        <div><b>Expiry:</b> ${d.expiry_date || '—'}</div>
      `;
    }
  } else {
    notFoundDiv.classList.remove('hidden');
    notFoundMsg.textContent = json.message;
  }
}

lookupBtn.addEventListener('click', () => lookup(scanInput.value.trim()));
scanInput.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); lookup(scanInput.value.trim()); } });

// Hook into scan result from camera
const observer = new MutationObserver(() => {
  const val = scanInput.value.trim();
  if (val) lookup(val);
});
observer.observe(document.getElementById('scan-result'), { childList: true, subtree: true, characterData: true });
</script>
JS; ?>
