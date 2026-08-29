<?php $pageTitle = 'Lots'; ?>
<div class="page-header">
  <div>
    <h1 class="page-title">Product Lots</h1>
    <div class="breadcrumb">Manager &rsaquo; Lots</div>
  </div>
  <button onclick="openNewLotModal()" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add Lot</button>
</div>

<div class="card">
  <div class="card-header">
    <h2 class="card-title">All Lots (<?= count($batches ?? []) ?>)</h2>
    <input type="text" placeholder="Search lots…" data-table-search="lots-table" class="form-input w-52 text-sm py-1.5">
  </div>
  <div class="overflow-x-auto">
    <table class="data-table w-full text-left" id="lots-table">
      <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold border-b border-gray-200">
        <tr>
          <th class="py-3 px-4">COMPANY</th>
          <th class="py-3 px-4">DATE</th>
          <th class="py-3 px-4">ITEMS</th>
          <th class="py-3 px-4 text-right">AMOUNT</th>
          <th class="py-3 px-4 text-center w-36">ACTIONS</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php foreach (($batches ?? []) as $b): ?>
        <tr class="hover:bg-gray-50/80 transition-colors">
          <td class="py-3.5 px-4 font-semibold text-gray-900"><?= h($b['company_name']) ?></td>
          <td class="py-3.5 px-4 text-gray-600 font-mono text-sm"><?= h($b['lot_date']) ?></td>
          <td class="py-3.5 px-4">
            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded border border-gray-200 text-gray-700 bg-gray-50">
              <?= $b['items_count'] ?> items
            </span>
          </td>
          <td class="py-3.5 px-4 text-right font-bold text-base <?= $b['total_amount'] < 0 ? 'text-rose-600' : 'text-gray-900' ?>">
            <?= ($b['total_amount'] < 0 ? '-' : '') . '৳' . number_format(abs($b['total_amount']), 2, '.', '') ?>
          </td>
          <td class="py-3.5 px-4">
            <div class="flex items-center justify-center gap-1.5">
              <!-- View Invoice Button -->
              <button type="button" onclick='viewBatchInvoice(<?= json_encode($b, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' class="w-8 h-8 rounded bg-indigo-600 hover:bg-indigo-700 text-white flex items-center justify-center transition-colors shadow-xs" title="View Invoice">
                <i class="fa-solid fa-file-invoice text-xs"></i>
              </button>
              <!-- Edit Batch Button -->
              <button type="button" onclick='editBatchLots(<?= json_encode($b, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' class="w-8 h-8 rounded bg-amber-500 hover:bg-amber-600 text-white flex items-center justify-center transition-colors shadow-xs" title="Edit Lot Batch (requires admin approval)">
                <i class="fa-solid fa-pen text-xs"></i>
              </button>
              <!-- Delete Batch Button -->
              <button type="button" onclick="deleteBatchLots(<?= (int)$b['company_id'] ?>, '<?= h($b['lot_date']) ?>', '<?= addslashes(h($b['company_name'])) ?>')" class="w-8 h-8 rounded bg-red-600 hover:bg-red-700 text-white flex items-center justify-center transition-colors shadow-xs" title="Delete Lot Batch">
                <i class="fa-solid fa-trash text-xs"></i>
              </button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($batches)): ?>
        <tr>
          <td colspan="5" class="text-center py-10 text-gray-400">No lots found.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<input type="hidden" id="csrf" value="<?= Helpers::csrfToken() ?>">

<!-- Invoice Details Modal -->
<div id="invoice-modal" class="modal-overlay hidden">
  <div class="modal-box p-0 overflow-hidden bg-white shadow-2xl rounded-xl" style="max-width: 680px; width: 95%;">
    <!-- Dark Modal Header Bar -->
    <div class="bg-[#334155] px-6 py-3.5 flex justify-between items-center text-white">
      <h3 class="text-base font-bold text-white tracking-wide">Invoice Details</h3>
      <button type="button" onclick="closeModal('invoice-modal')" class="text-gray-300 hover:text-white transition-colors text-2xl leading-none">
        &times;
      </button>
    </div>

    <!-- Printable Invoice Card -->
    <div id="invoice-printable" class="p-6 md:p-8 bg-white">
      <!-- Brand Logo & Header -->
      <div class="text-center mb-3">
        <img src="<?= asset('images/logo.png') ?>" alt="Logo" class="h-14 mx-auto mb-2 object-contain" onerror="this.outerHTML='<div class=&quot;text-3xl font-black text-gray-900 tracking-tight text-center mb-1&quot;>H</div>'">
        <div class="text-center text-[11px] md:text-xs text-gray-600 leading-relaxed font-normal">
          <p>Holding No: 01, Office No: 158-01, Charghat Bazar, Rajshahi, Bangladesh</p>
          <p>Licence No: 00158-01 &nbsp;|&nbsp; Licence ID: 05-021-00158-01</p>
          <p>Hotline: 01300-888811 &nbsp;|&nbsp; 01880-264444</p>
        </div>
      </div>

      <div class="border-t border-gray-200 my-4"></div>

      <!-- Invoice Reference -->
      <div class="text-center mb-5">
        <span class="text-xs md:text-sm font-semibold text-gray-600">Invoice: <span id="inv-lot-number" class="font-mono text-gray-900">#LOT130</span></span>
      </div>

      <!-- FROM & DATE Info Boxes -->
      <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="border border-gray-200 rounded-md p-3 bg-gray-50/40">
          <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-1">FROM:</div>
          <div class="text-sm md:text-base font-bold text-gray-900" id="inv-from-company">ডেকো ফুডস B</div>
        </div>
        <div class="border border-gray-200 rounded-md p-3 bg-gray-50/40">
          <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider mb-1">DATE:</div>
          <div class="text-sm md:text-base font-bold text-gray-900 font-mono" id="inv-lot-date">2026-07-21</div>
        </div>
      </div>

      <!-- Products Table -->
      <div class="mb-4 overflow-x-auto">
        <table class="w-full text-left text-xs md:text-sm border-collapse">
          <thead>
            <tr class="border-t-2 border-indigo-600 border-b border-gray-200 text-gray-800 font-bold uppercase text-[11px] md:text-xs bg-white">
              <th class="py-2.5 px-2 text-left">ITEM</th>
              <th class="py-2.5 px-2 text-center w-20">QTY</th>
              <th class="py-2.5 px-2 text-right w-24">PRICE</th>
              <th class="py-2.5 px-2 text-right w-28">TOTAL</th>
            </tr>
          </thead>
          <tbody id="inv-items-body" class="divide-y divide-gray-100 text-gray-800">
            <!-- Populated via JS -->
          </tbody>
        </table>
      </div>

      <!-- Grand Total Row -->
      <div class="flex justify-between items-center py-3 border-t border-b-2 border-gray-300 font-bold text-gray-900 text-base md:text-lg mb-2">
        <span>Total Amount:</span>
        <span id="inv-grand-total" class="font-mono">0.00</span>
      </div>
    </div>

    <!-- Modal Footer / Print Action -->
    <div class="px-6 py-3.5 bg-gray-50 border-t border-gray-200 flex justify-end">
      <button type="button" onclick="printInvoiceModal()" class="btn bg-[#334155] hover:bg-slate-800 text-white text-sm font-semibold px-5 py-2 rounded-lg flex items-center gap-2 shadow-sm transition-all">
        <i class="fa-solid fa-print"></i> Print Invoice
      </button>
    </div>
  </div>
</div>

<!-- Bulk Add/Edit Modal -->
<div id="add-modal" class="modal-overlay hidden">
    <div class="modal-box p-6" style="max-width: 1024px;">
        <div class="flex justify-between items-center mb-5">
            <div>
                <h3 class="text-2xl font-bold text-gray-900" id="modal-add-title">Add New Lot</h3>
                <p class="text-gray-500 text-sm" id="modal-add-subtitle">Record a new product batch received from company</p>
            </div>
            <button type="button" onclick="closeModal('add-modal')" class="btn btn-secondary bg-white border border-gray-300">
                <i class="fas fa-arrow-left mr-2"></i> Back to Lots
            </button>
        </div>

        <form id="bulk-add-form">
            <div class="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Company *</label>
                    <select id="bulk-company" class="form-input text-sm w-full" onchange="updateProductDropdowns()" required>
                        <option value="">Select Company</option>
                        <?php foreach (($companies ?? []) as $comp): ?>
                            <option value="<?= $comp['id'] ?>"><?= h($comp['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Lot Date *</label>
                    <input type="date" id="bulk-lot-date" class="form-input text-sm w-full" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>

            <div class="flex flex-wrap justify-between items-center mb-3 gap-2">
                <h4 class="text-lg font-bold text-gray-800">Products</h4>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="downloadLotCSVSample()" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1.5 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-md border border-indigo-200 transition-colors">
                        <i class="fas fa-download"></i> Sample CSV
                    </button>
                    <button type="button" onclick="document.getElementById('bulk-csv-input').click()" class="text-xs text-emerald-700 hover:text-emerald-900 font-medium flex items-center gap-1.5 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-md border border-emerald-200 transition-colors">
                        <i class="fas fa-file-csv"></i> Upload CSV
                    </button>
                    <input type="file" id="bulk-csv-input" accept=".csv,.txt" class="hidden" onchange="handleLotCSVUpload(this)">
                    <button type="button" onclick="addBulkRow()" class="btn btn-secondary btn-sm bg-white border border-gray-300">
                        <i class="fas fa-plus"></i> Add Row
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto mb-4 border border-gray-200 rounded">
                <table class="w-full text-left text-sm text-gray-700">
                    <thead class="bg-gray-50 text-gray-600 font-medium">
                        <tr>
                            <th class="p-3 border-b border-gray-200 w-1/3">Product</th>
                            <th class="p-3 border-b border-gray-200 w-36">Qty (Pieces)</th>
                            <th class="p-3 border-b border-gray-200 w-40">Expiry Date</th>
                            <th class="p-3 border-b border-gray-200 w-32">Buying Price (৳)</th>
                            <th class="p-3 border-b border-gray-200 w-32">Total (৳)</th>
                            <th class="p-3 border-b border-gray-200 w-12 text-center"></th>
                        </tr>
                    </thead>
                    <tbody id="bulk-rows" class="divide-y divide-gray-100">
                        <!-- Rows injected via JS -->
                    </tbody>
                </table>
            </div>
            
            <div class="flex justify-between items-end pt-4 border-t mt-4">
                <div>
                    <div class="text-sm text-gray-500 mb-1">Grand Total</div>
                    <div class="text-3xl font-bold text-blue-600" id="grand-total">৳0.00</div>
                </div>
                <div>
                    <button type="submit" id="btn-save-lot" class="btn btn-primary bg-indigo-600 hover:bg-indigo-700 border-none px-8 py-3 rounded-lg font-bold text-white shadow-sm">Save Lot</button>
                <p id="edit-approval-note" class="hidden text-xs text-amber-600 mt-2"><i class="fa-solid fa-triangle-exclamation mr-1"></i>Edit will be sent for admin approval before applying.</p>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Product Selector Modal -->
<div id="product-selector-modal" class="modal-overlay hidden">
    <div class="modal-box p-6" style="max-width: 800px; width: 90%;">
        <div class="flex justify-between items-center mb-5 border-b pb-3">
            <div>
                <h3 class="text-xl font-bold text-gray-900">Select Product</h3>
                <p class="text-xs text-gray-500 mt-1">Select a product to add to the lot</p>
            </div>
            <div class="flex items-center gap-4">
                <input type="text" id="modal-product-search" placeholder="Search products..." class="form-input text-sm py-1 px-3 w-48" oninput="filterModalProducts()">
                <button type="button" onclick="closeModal('product-selector-modal')" class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-gray-100 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <!-- Grid layout -->
        <div id="modal-products-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 max-h-[50vh] overflow-y-auto p-1">
            <!-- Dynamic product cards -->
        </div>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
const csrf = document.getElementById('csrf').value;

const productsList = <?= json_encode($products ?? []) ?>;

let activeProductSelectButton = null;
let isBatchEdit = false;
let originalBatchInfo = null;

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// ══════════════════════════════════════════════════════════
//  Invoice Modal Logic
// ══════════════════════════════════════════════════════════
function viewBatchInvoice(batch) {
    if (!batch) return;
    
    const lotNum = batch.min_lot_id ? batch.min_lot_id : (batch.company_id + '' + (batch.lot_date ? batch.lot_date.replace(/-/g, '') : ''));
    document.getElementById('inv-lot-number').textContent = `#LOT${lotNum}`;
    document.getElementById('inv-from-company').textContent = batch.company_name || 'N/A';
    document.getElementById('inv-lot-date').textContent = batch.lot_date || 'N/A';
    
    const tbody = document.getElementById('inv-items-body');
    tbody.innerHTML = '';
    
    let totalAmt = 0;
    (batch.items || []).forEach(item => {
        const tr = document.createElement('tr');
        tr.className = "hover:bg-gray-50/50";
        
        const qty = parseInt(item.qty_pieces) || 0;
        const ppb = Math.max(1, parseFloat(item.pieces_per_box) || 1);
        const buyingPrice = parseFloat(item.buying_price) || 0;
        
        // Unit price per piece = buying_price / pieces_per_box
        const unitPrice = buyingPrice / ppb;
        const rowTotal = (qty / ppb) * buyingPrice;
        totalAmt += rowTotal;
        
        const rowTotalFormatted = rowTotal < 0 ? '-৳' + Math.abs(rowTotal).toFixed(2) : '৳' + rowTotal.toFixed(2);
        
        tr.innerHTML = `
            <td class="py-2.5 px-2 font-medium text-gray-800">${escapeHtml(item.product_name)}</td>
            <td class="py-2.5 px-2 text-center font-semibold ${qty < 0 ? 'text-rose-600' : 'text-gray-900'}">${qty}</td>
            <td class="py-2.5 px-2 text-right text-gray-700">৳${unitPrice.toFixed(2)}</td>
            <td class="py-2.5 px-2 text-right font-semibold ${rowTotal < 0 ? 'text-rose-600' : 'text-gray-900'}">${rowTotalFormatted}</td>
        `;
        tbody.appendChild(tr);
    });
    
    const grandFormatted = totalAmt < 0 ? '-৳' + Math.abs(totalAmt).toFixed(2) : '৳' + totalAmt.toFixed(2);
    const grandEl = document.getElementById('inv-grand-total');
    grandEl.textContent = grandFormatted;
    if (totalAmt < 0) {
        grandEl.classList.add('text-rose-600');
    } else {
        grandEl.classList.remove('text-rose-600');
    }
    openModal('invoice-modal');
}

function printInvoiceModal() {
    const printContent = document.getElementById('invoice-printable').innerHTML;
    const printWindow = window.open('', '', 'width=800,height=900');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Invoice - ${document.getElementById('inv-lot-number').textContent}</title>
            <meta charset="utf-8">
            <script src="https://cdn.tailwindcss.com"><\/script>
            <style>
                @page { size: auto; margin: 12mm; }
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; color: #111827; background: #fff; }
                table { border-collapse: collapse; width: 100%; }
            </style>
        </head>
        <body class="p-6">
            ${printContent}
            <script>
                window.onload = function() {
                    window.focus();
                    window.print();
                    setTimeout(function() { window.close(); }, 500);
                };
            <\/script>
        </body>
        </html>
    `);
    printWindow.document.close();
}

// ══════════════════════════════════════════════════════════
//  Batch Edit & Delete Logic
// ══════════════════════════════════════════════════════════
function openNewLotModal() {
    isBatchEdit = false;
    originalBatchInfo = null;
    document.getElementById('modal-add-title').textContent = 'Add New Lot';
    document.getElementById('modal-add-subtitle').textContent = 'Record a new product batch received from company';
    document.getElementById('btn-save-lot').textContent = 'Save Lot';
    document.getElementById('edit-approval-note').classList.add('hidden');
    document.getElementById('bulk-company').value = '';
    document.getElementById('bulk-lot-date').value = new Date().toISOString().split('T')[0];
    document.getElementById('bulk-rows').innerHTML = '';
    addBulkRow();
    calculateTotals();
    openModal('add-modal');
}

function editBatchLots(batch) {
    if (!batch) return;
    isBatchEdit = true;
    originalBatchInfo = {
        company_id: batch.company_id,
        lot_date: batch.lot_date
    };
    
    document.getElementById('modal-add-title').textContent = 'Edit Lot Batch';
    document.getElementById('modal-add-subtitle').textContent = `Editing batch for ${batch.company_name} on ${batch.lot_date}`;
    document.getElementById('btn-save-lot').textContent = 'Submit Edit Request';
    document.getElementById('edit-approval-note').classList.remove('hidden');
    
    document.getElementById('bulk-company').value = batch.company_id || '';
    document.getElementById('bulk-lot-date').value = batch.lot_date || '';
    
    const tbody = document.getElementById('bulk-rows');
    tbody.innerHTML = '';
    
    if (batch.items && batch.items.length > 0) {
        batch.items.forEach(item => {
            addBulkRow({
                productId: item.product_id,
                qty: item.qty_pieces,
                expiry: item.expiry_date || '',
                price: item.buying_price
            });
        });
    } else {
        addBulkRow();
    }
    
    calculateTotals();
    openModal('add-modal');
}

async function deleteBatchLots(companyId, lotDate, companyName) {
    if (!confirm(`Are you sure you want to delete all lots for "${companyName}" on ${lotDate}? This will also revert the warehouse inventory.`)) {
        return;
    }
    
    try {
        const res = await fetch('<?= url('manager/api/lots/delete-batch') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
            body: JSON.stringify({ csrf_token: csrf, company_id: companyId, lot_date: lotDate })
        });
        const data = await res.json();
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Error deleting lot batch');
        }
    } catch (err) {
        alert('Request failed: ' + (err.message || err));
    }
}

// ══════════════════════════════════════════════════════════
//  Product Selector Modal Logic (Prevents duplicates)
// ══════════════════════════════════════════════════════════
function openProductSelector(btn) {
    activeProductSelectButton = btn;
    renderModalProducts();
    openModal('product-selector-modal');
    setTimeout(() => {
        const searchInput = document.getElementById('modal-product-search');
        if (searchInput) {
            searchInput.value = '';
            searchInput.focus();
        }
    }, 100);
}

function renderModalProducts() {
    const grid = document.getElementById('modal-products-grid');
    grid.innerHTML = '';
    
    const companyId = document.getElementById('bulk-company').value;
    
    // Collect all product IDs already selected in other rows to prevent duplicate products in lot
    const activeRow = activeProductSelectButton ? activeProductSelectButton.closest('tr') : null;
    const selectedProductIds = new Set();
    document.querySelectorAll('#bulk-rows tr').forEach(row => {
        if (activeRow && row === activeRow) return;
        const input = row.querySelector('.row-product');
        if (input && input.value) {
            selectedProductIds.add(String(input.value));
        }
    });

    const filtered = productsList.filter(p => {
        if (companyId && p.company_id != companyId) return false;
        if (selectedProductIds.has(String(p.id))) return false;
        return true;
    });
    
    if (filtered.length === 0) {
        const hasCompanyProducts = productsList.some(p => !companyId || p.company_id == companyId);
        const emptyMsg = hasCompanyProducts && selectedProductIds.size > 0
            ? 'All available products for this company have already been added to the lot.'
            : 'No products found for this company.';
        grid.innerHTML = `
            <div class="col-span-full py-8 text-center text-gray-400">
                <i class="fas fa-box-open text-3xl mb-2"></i>
                <p>${emptyMsg}</p>
            </div>
        `;
        return;
    }
    
    filtered.forEach(p => {
        const card = document.createElement('div');
        card.className = "border border-gray-200 rounded-lg p-3 hover:border-indigo-500 hover:shadow-md cursor-pointer transition-all flex gap-3 items-center modal-product-card bg-white";
        card.dataset.id = p.id;
        card.dataset.name = p.name.toLowerCase();
        card.dataset.sku = p.sku.toLowerCase();
        card.onclick = () => selectProductForActiveRow(p);
        
        let imgHtml = '';
        if (p.image) {
            imgHtml = `<img src="<?= BASE_URL ?>/${p.image}" class="w-12 h-12 object-cover rounded-md border border-gray-100 shrink-0" onerror="this.outerHTML='<div class=&quot;w-12 h-12 bg-gray-100 rounded-md flex items-center justify-center shrink-0 border border-gray-200&quot;><i class=&quot;fas fa-box text-gray-400&quot;></i></div>'">`;
        } else {
            imgHtml = `<div class="w-12 h-12 bg-gray-100 rounded-md flex items-center justify-center shrink-0 border border-gray-200"><i class="fas fa-box text-gray-400 text-lg"></i></div>`;
        }
        
        card.innerHTML = `
            ${imgHtml}
            <div class="flex-1 min-w-0">
                <h4 class="font-semibold text-sm text-gray-900 truncate" title="${p.name}">${p.name}</h4>
                <p class="text-xs text-gray-500">${p.pieces_per_box} Pcs / ${p.box_type || 'বক্স'}</p>
                <div class="mt-1 flex items-center gap-1.5">
                    <span class="text-[10px] font-medium px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-100">
                        Stock: ${p.stock_boxes} B, ${p.stock_pieces} P
                    </span>
                </div>
            </div>
        `;
        grid.appendChild(card);
    });
}

function filterModalProducts() {
    const query = document.getElementById('modal-product-search').value.toLowerCase();
    const cards = document.querySelectorAll('.modal-product-card');
    cards.forEach(card => {
        const name = card.dataset.name;
        const sku = card.dataset.sku;
        if (name.includes(query) || sku.includes(query)) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}

function selectProductForActiveRow(product) {
    if (!activeProductSelectButton) return;
    
    const container = activeProductSelectButton.closest('.product-select-container');
    const input = container.querySelector('.row-product');
    const nameSpan = container.querySelector('.selected-product-name');
    
    input.value = product.id;
    nameSpan.textContent = `${product.name} (${product.pieces_per_box} Pcs)`;
    nameSpan.classList.remove('text-gray-500');
    nameSpan.classList.add('text-gray-900', 'font-medium');
    
    closeModal('product-selector-modal');
    
    const row = container.closest('tr');
    const qtyInput = row.querySelector('.row-qty');
    if (qtyInput) {
        updatePiecesHelper(qtyInput);
    }
    
    const priceInput = row.querySelector('.row-price');
    if (priceInput && product.buying_price !== undefined && product.buying_price !== null) {
        priceInput.value = parseFloat(product.buying_price).toFixed(2);
        calculateTotals();
    }
}

function updatePiecesHelper(input) {
    const row = input.closest('tr');
    const helper = row.querySelector('.row-qty-helper');
    if (!helper) return;
    
    const productId = row.querySelector('.row-product').value;
    if (!productId) {
        helper.textContent = '';
        return;
    }
    
    const product = productsList.find(p => p.id == productId);
    if (!product) {
        helper.textContent = '';
        return;
    }
    
    const qty = parseInt(input.value) || 0;
    const ppb = parseInt(product.pieces_per_box) || 1;
    const boxType = product.box_type || 'Box';
    
    if (qty === 0) {
        helper.textContent = '';
        return;
    }
    
    const sign = qty < 0 ? '-' : '';
    const absQty = Math.abs(qty);
    const boxes = Math.floor(absQty / ppb);
    const pieces = absQty % ppb;
    
    let text = '';
    if (boxes > 0) {
        text += `${sign}${boxes} ${boxType}`;
    }
    if (pieces > 0) {
        if (text) text += ' / ';
        text += `${sign}${pieces} Pcs`;
    }
    if (!text && absQty > 0) {
        text = `${sign}0 ${boxType} / ${sign}0 Pcs`;
    }
    
    helper.textContent = text;
    if (qty < 0) {
        helper.classList.add('text-rose-500');
        helper.classList.remove('text-gray-500');
    } else {
        helper.classList.remove('text-rose-500');
        helper.classList.add('text-gray-500');
    }
}

function updateProductDropdowns() {
    const companyId = document.getElementById('bulk-company').value;
    const rows = document.querySelectorAll('#bulk-rows tr');
    rows.forEach(row => {
        const input = row.querySelector('.row-product');
        if (!input) return;
        const val = input.value;
        if (val) {
            const product = productsList.find(p => p.id == val);
            if (product && companyId && product.company_id != companyId) {
                input.value = '';
                const nameSpan = row.querySelector('.selected-product-name');
                nameSpan.textContent = 'Select Product...';
                nameSpan.classList.add('text-gray-500');
                nameSpan.classList.remove('text-gray-900', 'font-medium');
            }
        }
    });
}

function calculateTotals() {
    let grandTotal = 0;
    const rows = document.querySelectorAll('#bulk-rows tr');
    rows.forEach(row => {
        const qty = parseFloat(row.querySelector('.row-qty').value) || 0;
        const price = parseFloat(row.querySelector('.row-price').value) || 0;
        
        const productId = row.querySelector('.row-product').value;
        let ppb = 1;
        if (productId) {
            const product = productsList.find(p => p.id == productId);
            if (product) {
                ppb = parseFloat(product.pieces_per_box) || 1;
            }
        }
        
        const total = (qty / ppb) * price;
        const totalFormatted = total < 0 ? '-৳' + Math.abs(total).toFixed(2) : '৳' + total.toFixed(2);
        const rowTotalEl = row.querySelector('.row-total');
        rowTotalEl.innerText = totalFormatted;
        if (total < 0) {
            rowTotalEl.classList.add('text-rose-600');
            rowTotalEl.classList.remove('text-gray-800');
        } else {
            rowTotalEl.classList.remove('text-rose-600');
            rowTotalEl.classList.add('text-gray-800');
        }
        grandTotal += total;
    });
    const grandFormatted = grandTotal < 0 ? '-৳' + Math.abs(grandTotal).toFixed(2) : '৳' + grandTotal.toFixed(2);
    const grandEl = document.getElementById('grand-total');
    grandEl.innerText = grandFormatted;
    if (grandTotal < 0) {
        grandEl.classList.add('text-rose-600');
        grandEl.classList.remove('text-blue-600');
    } else {
        grandEl.classList.remove('text-rose-600');
        grandEl.classList.add('text-blue-600');
    }
}

function addBulkRow(data = null) {
    const tbody = document.getElementById('bulk-rows');
    const tr = document.createElement('tr');
    
    const productId = data ? (data.productId || '') : '';
    let labelText = 'Select Product...';
    let labelClass = 'selected-product-name text-gray-500';
    
    if (productId) {
        const product = productsList.find(p => p.id == productId);
        if (product) {
            labelText = `${product.name} (${product.pieces_per_box} Pcs)`;
            labelClass = 'selected-product-name text-gray-900 font-medium';
        }
    } else if (data && data.productName) {
        labelText = data.productName;
        labelClass = 'selected-product-name text-amber-700 font-medium';
    }
    
    const qty = data && data.qty !== undefined ? data.qty : 0;
    const expiry = data && data.expiry ? data.expiry : '';
    const price = data && data.price !== undefined ? data.price : 0;
    
    tr.innerHTML = `
        <td class="p-3 border-b border-gray-100">
            <div class="relative product-select-container">
                <button type="button" onclick="openProductSelector(this)" class="form-input text-sm w-full text-left flex justify-between items-center bg-white cursor-pointer select-btn border border-gray-300 rounded px-3 py-2 hover:border-indigo-500 transition-colors">
                    <span class="${labelClass}">${labelText}</span>
                    <i class="fas fa-chevron-down text-gray-400"></i>
                </button>
                <input type="hidden" class="row-product" value="${productId}" required>
            </div>
        </td>
        <td class="p-3 border-b border-gray-100">
            <input type="number" class="form-input text-sm w-full row-qty" value="${qty}" required oninput="calculateTotals(); updatePiecesHelper(this)">
            <div class="text-[10px] text-gray-500 mt-1 row-qty-helper font-medium"></div>
        </td>
        <td class="p-3 border-b border-gray-100"><input type="date" class="form-input text-sm w-full row-expiry" value="${expiry}"></td>
        <td class="p-3 border-b border-gray-100"><input type="number" step="0.01" class="form-input text-sm w-full row-price" value="${price}" min="0" oninput="calculateTotals()"></td>
        <td class="p-3 border-b border-gray-100 align-middle"><span class="row-total font-medium text-gray-800">৳0.00</span></td>
        <td class="p-3 border-b border-gray-100 text-center">
            <button type="button" onclick="this.closest('tr').remove(); calculateTotals();" class="text-red-500 hover:text-red-700 bg-red-50 p-2 rounded w-8 h-8 flex items-center justify-center">
                <i class="fas fa-times"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
    const qtyInput = tr.querySelector('.row-qty');
    if (qtyInput) updatePiecesHelper(qtyInput);
    calculateTotals();
}

function downloadLotCSVSample() {
    const csvContent = "\uFEFFProduct Name,Qty (Pieces),Buying Price,Expiry Date\n" +
        "Mini Mango Juice,100,450.00,2027-12-31\n" +
        "Choco Chocolate,50,300.00,2026-11-20\n";
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'Lot_Upload_Sample.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function parseCSVLine(str) {
    const arr = [];
    let quote = false;
    let col = '';
    for (let c = 0; c < str.length; c++) {
        const cc = str[c];
        const nc = str[c+1];
        if (cc === '"') {
            if (quote && nc === '"') {
                col += '"';
                c++;
            } else {
                quote = !quote;
            }
        } else if (cc === ',' && !quote) {
            arr.push(col.trim());
            col = '';
        } else {
            col += cc;
        }
    }
    arr.push(col.trim());
    return arr;
}

function normalizeStr(str) {
    if (!str) return '';
    return str.toString()
        .toLowerCase()
        .trim()
        .replace(/["'“”‘’]/g, '')
        .replace(/[\s\-_]+/g, ' ');
}

function formatDateString(str) {
    if (!str) return '';
    str = str.trim();
    if (/^\d{4}-\d{2}-\d{2}$/.test(str)) return str;
    const parts = str.split(/[\/\.-]/);
    if (parts.length === 3) {
        if (parts[0].length === 4) {
            return `${parts[0]}-${parts[1].padStart(2, '0')}-${parts[2].padStart(2, '0')}`;
        } else if (parts[2].length === 4) {
            return `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
        }
    }
    return '';
}

function handleLotCSVUpload(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    const reader = new FileReader();
    
    reader.onload = function(e) {
        const text = e.target.result;
        const lines = text.split(/\r?\n/);
        
        if (lines.length === 0) {
            alert('CSV file is empty.');
            return;
        }

        const selectedCompanyId = document.getElementById('bulk-company').value;
        
        let headerIndex = -1;
        let colMap = { product: 0, qty: 1, price: 2, expiry: 3 };

        for (let i = 0; i < Math.min(3, lines.length); i++) {
            const rowStr = lines[i].trim();
            if (!rowStr) continue;
            const cols = parseCSVLine(rowStr).map(c => c.toLowerCase());
            
            const prodIdx = cols.findIndex(c => c.includes('product') || c.includes('name') || c.includes('sku') || c.includes('item'));
            if (prodIdx !== -1) {
                headerIndex = i;
                colMap.product = prodIdx;
                
                const qtyIdx = cols.findIndex(c => c.includes('qty') || c.includes('piece') || c.includes('quantity') || c.includes('pcs'));
                if (qtyIdx !== -1) colMap.qty = qtyIdx;

                const priceIdx = cols.findIndex(c => c.includes('price') || c.includes('cost') || c.includes('buying') || c.includes('rate'));
                if (priceIdx !== -1) colMap.price = priceIdx;

                const expIdx = cols.findIndex(c => c.includes('expir') || c.includes('exp') || c.includes('date'));
                if (expIdx !== -1) colMap.expiry = expIdx;
                
                break;
            }
        }

        const rowsToProcess = [];
        const startLine = headerIndex >= 0 ? headerIndex + 1 : 0;
        
        for (let i = startLine; i < lines.length; i++) {
            const rowStr = lines[i].trim();
            if (!rowStr) continue;
            const cols = parseCSVLine(rowStr);
            if (cols.length === 0 || !cols.some(c => c.length > 0)) continue;
            
            const rawProd = cols[colMap.product] || '';
            const rawQty = cols[colMap.qty] || '1';
            const rawPrice = cols[colMap.price] || '0';
            const rawExpiry = cols[colMap.expiry] || '';
            
            if (rawProd) {
                rowsToProcess.push({
                    rawProd,
                    qty: parseInt(rawQty.replace(/[^0-9-]/g, '')) || 0,
                    price: parseFloat(rawPrice.replace(/[^0-9.]/g, '')) || 0,
                    expiry: formatDateString(rawExpiry)
                });
            }
        }

        if (rowsToProcess.length === 0) {
            alert('No valid product rows found in the CSV file.');
            input.value = '';
            return;
        }

        document.getElementById('bulk-rows').innerHTML = '';

        let matchedCount = 0;
        let unmatchedList = [];
        let matchedCompanyIds = new Set();

        rowsToProcess.forEach(item => {
            const searchRaw = item.rawProd.trim();
            const searchNorm = normalizeStr(searchRaw);
            
            let pool = selectedCompanyId 
                ? productsList.filter(p => p.company_id == selectedCompanyId)
                : productsList;

            // 1. Exact ID match
            let matchedProduct = pool.find(p => String(p.id) === searchRaw);
            
            // 2. Exact SKU match
            if (!matchedProduct) {
                matchedProduct = pool.find(p => p.sku && normalizeStr(p.sku) === searchNorm);
            }
            
            // 3. Exact Name match
            if (!matchedProduct) {
                matchedProduct = pool.find(p => normalizeStr(p.name) === searchNorm);
            }
            
            // 4. Normalized Substring match
            if (!matchedProduct) {
                matchedProduct = pool.find(p => {
                    const pNorm = normalizeStr(p.name);
                    return pNorm.length > 2 && (pNorm.includes(searchNorm) || searchNorm.includes(pNorm));
                });
            }

            // 5. Global fallback if company was selected but product was listed under standard pool
            if (!matchedProduct && selectedCompanyId) {
                matchedProduct = productsList.find(p => normalizeStr(p.name) === searchNorm || (p.sku && normalizeStr(p.sku) === searchNorm));
            }

            if (matchedProduct) {
                addBulkRow({
                    productId: matchedProduct.id,
                    qty: item.qty,
                    price: item.price,
                    expiry: item.expiry
                });
                matchedCount++;
                if (matchedProduct.company_id) matchedCompanyIds.add(matchedProduct.company_id);
            } else {
                addBulkRow({
                    productId: '',
                    productName: item.rawProd,
                    qty: item.qty,
                    price: item.price,
                    expiry: item.expiry
                });
                unmatchedList.push(item.rawProd);
            }
        });

        const companySelect = document.getElementById('bulk-company');
        if (!companySelect.value && matchedCompanyIds.size > 0) {
            companySelect.value = Array.from(matchedCompanyIds)[0];
            updateProductDropdowns();
        }

        calculateTotals();

        let msg = `CSV Import Summary:\n- Total Processed: ${rowsToProcess.length}\n- Matched Products: ${matchedCount}`;
        if (unmatchedList.length > 0) {
            msg += `\n- Unmatched (${unmatchedList.length}): ${unmatchedList.join(', ')}\n\nNote: Please click the unmatched product button to select the product manually.`;
        }
        alert(msg);
    };

    reader.readAsText(file);
    input.value = '';
}

// Initialize with one row
document.addEventListener('DOMContentLoaded', () => {
    addBulkRow();
});

// Add / Update Lots Form Submit
document.getElementById('bulk-add-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const rows = document.querySelectorAll('#bulk-rows tr');
    if(rows.length === 0) return alert('Please add at least one lot.');

    const lot_date = document.getElementById('bulk-lot-date').value;
    const company_id = document.getElementById('bulk-company').value;

    const lots = [];
    let valid = true;
    const seenProductIds = new Set();
    let hasDuplicate = false;

    rows.forEach(row => {
        const productId = row.querySelector('.row-product').value;
        const qty = row.querySelector('.row-qty').value;
        const expiry = row.querySelector('.row-expiry').value;
        const price = row.querySelector('.row-price').value;
        
        if(!productId) valid = false;
        if(productId) {
            if(seenProductIds.has(productId)) {
                hasDuplicate = true;
            }
            seenProductIds.add(productId);
        }
        
        lots.push({
            product_id: productId,
            qty_pieces: qty,
            expiry_date: expiry,
            buying_price: price
        });
    });

    if(!valid) return alert('Please fill in all required fields.');
    if(hasDuplicate) return alert('Duplicate products detected in the lot list. Each product can only be added once.');

    const btn = document.getElementById('btn-save-lot');
    const originalBtnText = btn.textContent;
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    
    const endpoint = (isBatchEdit && originalBatchInfo)
        ? '<?= url('manager/api/lots/request-edit') ?>'
        : '<?= url('manager/api/lots/store') ?>';
    
    const payload = {
        csrf_token: csrf,
        lot_date: lot_date,
        company_id: company_id,
        lots: lots
    };

    if (isBatchEdit && originalBatchInfo) {
        payload.original_company_id = originalBatchInfo.company_id;
        payload.original_lot_date   = originalBatchInfo.lot_date;
    }

    try {
        const res = await fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
            body: JSON.stringify(payload)
        });
        let data;
        try {
            data = await res.json();
        } catch(e) {
            const rawText = await res.text().catch(() => '');
            alert('Server error (' + res.status + '): ' + (rawText || res.statusText || 'Invalid response from server'));
            btn.disabled = false; btn.textContent = originalBtnText;
            return;
        }
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Error saving lots');
            btn.disabled = false; btn.textContent = originalBtnText;
        }
    } catch(err) {
        alert('Request failed: ' + (err.message || err));
        btn.disabled = false; btn.textContent = originalBtnText;
    }
});
</script>
