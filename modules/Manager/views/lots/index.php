<?php $pageTitle = 'Lots'; ?>
<div class="page-header">
  <div><h1 class="page-title">Product Lots</h1><div class="breadcrumb">Manager &rsaquo; Lots</div></div>
  <button onclick="openModal('add-modal')" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add Lot</button>
</div>

<div class="card">
  <div class="card-header">
    <h2 class="card-title">All Lots (<?= count($items) ?>)</h2>
    <input type="text" placeholder="Search lots…" data-table-search="lots-table" class="form-input w-48 text-sm py-1.5">
  </div>
  <div class="overflow-x-auto">
    <table class="data-table whitespace-nowrap" id="lots-table">
      <thead>
        <tr><th>#</th><th>Lot Date</th><th>Product</th><th>Pieces</th><th>Buying Price</th><th>Total</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($items as $i => $l):
          $expired = !empty($l['expiry_date']) && strtotime($l['expiry_date']) < time();
          $expiring = !$expired && !empty($l['expiry_date']) && strtotime($l['expiry_date']) < strtotime('+30 days');
        ?>
        <tr>
          <td class="text-gray-400 text-xs"><?= $i+1 ?></td>
          <td class="font-mono font-semibold"><?= Helpers::date($l['lot_date'] ?? $l['created_at']) ?></td>
          <td>
            <?= h($l['product_name']) ?>
            <?php if ($expired): ?>
              <span class="badge bg-red-100 text-red-700 text-[10px] ml-1">EXPIRED</span>
            <?php elseif ($expiring): ?>
              <span class="badge bg-amber-100 text-amber-700 text-[10px] ml-1">EXPIRES SOON</span>
            <?php endif; ?>
          </td>
          <td><span class="font-semibold"><?= Helpers::number($l['qty_pieces']) ?></span></td>
          <td>৳<?= Helpers::number($l['buying_price'], 2) ?></td>
          <?php 
            $ppb = max(1, (float)($l['pieces_per_box'] ?? 1));
            $rowTotal = ($l['qty_pieces'] / $ppb) * $l['buying_price'];
          ?>
          <td class="font-semibold">৳<?= Helpers::number($rowTotal, 2) ?></td>
          <td>
            <button onclick='editLot(<?= json_encode($l) ?>)' class="btn btn-secondary btn-sm"><i class="fa-solid fa-pen"></i></button>
            <button onclick="deleteLot(<?= $l['id'] ?>)" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($items)): ?><tr><td colspan="7" class="text-center py-8 text-gray-400">No lots found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<input type="hidden" id="csrf" value="<?= Helpers::csrfToken() ?>">

<!-- Bulk Add Modal -->
<div id="add-modal" class="modal-overlay hidden">
    <div class="modal-box p-6" style="max-width: 1024px;">
        <div class="flex justify-between items-center mb-5">
            <div>
                <h3 class="text-2xl font-bold text-gray-900">Add New Lot</h3>
                <p class="text-gray-500 text-sm">Record a new product batch received from company</p>
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
                        <?php foreach ($companies as $comp): ?>
                            <option value="<?= $comp['id'] ?>"><?= h($comp['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Lot Date *</label>
                    <input type="date" id="bulk-lot-date" class="form-input text-sm w-full" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>

            <div class="flex justify-between items-center mb-2">
                <h4 class="text-lg font-bold text-gray-800">Products</h4>
                <button type="button" onclick="addBulkRow()" class="btn btn-secondary btn-sm bg-white border border-gray-300">
                    <i class="fas fa-plus"></i> Add Row
                </button>
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
                    <button type="submit" class="btn btn-primary bg-indigo-600 hover:bg-indigo-700 border-none px-8 py-3 rounded-lg font-bold text-white shadow-sm">Save Lot</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Lot Modal -->
<div id="edit-modal" class="modal-overlay hidden">
    <div class="modal-box p-6" style="max-width: 600px;">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-xl font-bold text-gray-900">Edit Lot</h3>
            <button type="button" onclick="closeModal('edit-modal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="edit-form">
            <input type="hidden" id="edit-id">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Product *</label>
                    <select id="edit-product" class="form-input text-sm w-full" required>
                        <?php foreach ($products as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= h($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Lot Number</label>
                    <input type="text" id="edit-lot-number" class="form-input text-sm w-full">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Qty (Pieces) *</label>
                    <input type="number" id="edit-pieces" class="form-input text-sm w-full" required min="0">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Buying Price (৳) *</label>
                    <input type="number" step="0.01" id="edit-buying-price" class="form-input text-sm w-full" required min="0">
                </div>
                <!-- qty_boxes is hidden/ignored as inventory relies on qty_pieces -->
                <input type="hidden" id="edit-boxes" value="0">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Mfg Date</label>
                    <input type="date" id="edit-mfg-date" class="form-input text-sm w-full">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Expiry Date</label>
                    <input type="date" id="edit-exp-date" class="form-input text-sm w-full">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Notes</label>
                    <textarea id="edit-notes" class="form-input text-sm w-full" rows="2"></textarea>
                </div>
            </div>
            
            <div class="flex justify-end gap-3 pt-4 border-t mt-4">
                <button type="button" onclick="closeModal('edit-modal')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
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

const productsList = <?= json_encode($products) ?>;

let activeProductSelectButton = null;

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
    
    const filtered = productsList.filter(p => !companyId || p.company_id == companyId);
    
    if (filtered.length === 0) {
        grid.innerHTML = `
            <div class="col-span-full py-8 text-center text-gray-400">
                <i class="fas fa-box-open text-3xl mb-2"></i>
                <p>No products found for this company.</p>
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
    
    if (qty <= 0) {
        helper.textContent = '';
        return;
    }
    
    const boxes = Math.floor(qty / ppb);
    const pieces = qty % ppb;
    
    let text = '';
    if (boxes > 0) {
        text += `${boxes} ${boxType}`;
    }
    if (pieces > 0) {
        if (text) text += ' / ';
        text += `${pieces} Pcs`;
    }
    if (!text && qty > 0) {
        text = `0 ${boxType} / 0 Pcs`;
    }
    
    helper.textContent = text;
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
        row.querySelector('.row-total').innerText = '৳' + total.toFixed(2);
        grandTotal += total;
    });
    document.getElementById('grand-total').innerText = '৳' + grandTotal.toFixed(2);
}

function addBulkRow() {
    const tbody = document.getElementById('bulk-rows');
    const tr = document.createElement('tr');
    
    tr.innerHTML = `
        <td class="p-3 border-b border-gray-100">
            <div class="relative product-select-container">
                <button type="button" onclick="openProductSelector(this)" class="form-input text-sm w-full text-left flex justify-between items-center bg-white cursor-pointer select-btn border border-gray-300 rounded px-3 py-2 hover:border-indigo-500 transition-colors">
                    <span class="selected-product-name text-gray-500">Select Product...</span>
                    <i class="fas fa-chevron-down text-gray-400"></i>
                </button>
                <input type="hidden" class="row-product" required>
            </div>
        </td>
        <td class="p-3 border-b border-gray-100">
            <input type="number" class="form-input text-sm w-full row-qty" value="1" min="1" required oninput="calculateTotals(); updatePiecesHelper(this)">
            <div class="text-[10px] text-gray-500 mt-1 row-qty-helper font-medium"></div>
        </td>
        <td class="p-3 border-b border-gray-100"><input type="date" class="form-input text-sm w-full row-expiry"></td>
        <td class="p-3 border-b border-gray-100"><input type="number" step="0.01" class="form-input text-sm w-full row-price" value="0" min="0" oninput="calculateTotals()"></td>
        <td class="p-3 border-b border-gray-100 align-middle"><span class="row-total font-medium text-gray-800">৳0.00</span></td>
        <td class="p-3 border-b border-gray-100 text-center">
            <button type="button" onclick="this.closest('tr').remove(); calculateTotals();" class="text-red-500 hover:text-red-700 bg-red-50 p-2 rounded w-8 h-8 flex items-center justify-center">
                <i class="fas fa-times"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
    calculateTotals();
}

// Initialize with one row
document.addEventListener('DOMContentLoaded', () => {
    addBulkRow();
});

// Add Bulk Lots
document.getElementById('bulk-add-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const rows = document.querySelectorAll('#bulk-rows tr');
    if(rows.length === 0) return alert('Please add at least one lot.');

    const lot_date = document.getElementById('bulk-lot-date').value;
    const company_id = document.getElementById('bulk-company').value;

    const lots = [];
    let valid = true;
    rows.forEach(row => {
        const productId = row.querySelector('.row-product').value;
        const qty = row.querySelector('.row-qty').value;
        const expiry = row.querySelector('.row-expiry').value;
        const price = row.querySelector('.row-price').value;
        
        if(!productId) valid = false;
        
        lots.push({
            product_id: productId,
            qty_pieces: qty,
            expiry_date: expiry,
            buying_price: price
        });
    });

    if(!valid) return alert('Please fill in all required fields.');

    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    
    try {
        const res = await fetch('<?= url('manager/api/lots/store') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
            body: JSON.stringify({ lot_date, company_id, lots })
        });
        const data = await res.json();
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Error saving lots');
            btn.disabled = false; btn.innerText = 'Save Lot';
        }
    } catch(err) {
        alert('Request failed');
        btn.disabled = false; btn.innerText = 'Save Lot';
    }
});

// Edit Lot
function editLot(lot) {
    document.getElementById('edit-id').value = lot.id;
    document.getElementById('edit-product').value = lot.product_id;
    document.getElementById('edit-lot-number').value = lot.lot_number;
    document.getElementById('edit-mfg-date').value = lot.manufacturing_date || '';
    document.getElementById('edit-exp-date').value = lot.expiry_date || '';
    document.getElementById('edit-boxes').value = lot.qty_boxes || 0;
    document.getElementById('edit-pieces').value = lot.qty_pieces || 0;
    document.getElementById('edit-buying-price').value = lot.buying_price || 0;
    document.getElementById('edit-notes').value = lot.notes || '';
    openModal('edit-modal');
}

document.getElementById('edit-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    
    const payload = {
        id: document.getElementById('edit-id').value,
        product_id: document.getElementById('edit-product').value,
        lot_number: document.getElementById('edit-lot-number').value,
        manufacturing_date: document.getElementById('edit-mfg-date').value,
        expiry_date: document.getElementById('edit-exp-date').value,
        qty_boxes: document.getElementById('edit-boxes').value,
        qty_pieces: document.getElementById('edit-pieces').value,
        buying_price: document.getElementById('edit-buying-price').value,
        notes: document.getElementById('edit-notes').value
    };

    try {
        const res = await fetch('<?= url('manager/api/lots/update') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Error updating lot');
            btn.disabled = false; btn.innerText = 'Save Changes';
        }
    } catch(err) {
        alert('Request failed');
        btn.disabled = false; btn.innerText = 'Save Changes';
    }
});

// Delete Lot
async function deleteLot(id) {
    if(!confirm('Are you sure you want to delete this lot?')) return;
    try {
        const res = await fetch('<?= url('manager/api/lots/delete') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
            body: JSON.stringify({ id: id })
        });
        const data = await res.json();
        if(data.success) window.location.reload();
        else alert('Error deleting lot');
    } catch(err) {
        alert('Request failed');
    }
}
</script>
