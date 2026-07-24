<?php $pageTitle = 'Products'; ?>

<div class="page-header">
  <div><h1 class="page-title">Products Inventory</h1><div class="breadcrumb">Manager &rsaquo; Products</div></div>
  <div class="flex gap-3">
      <button onclick="openModal('add-modal')" class="btn btn-primary flex items-center gap-2">
          <i class="fas fa-plus"></i> Bulk Add
      </button>
  </div>
</div>

<div class="card">
  <div class="card-header flex flex-wrap gap-4 items-center">
      <h2 class="card-title mr-auto">All Products</h2>
      <select id="filter-company" class="form-input text-sm w-auto">
          <option value="">All Companies</option>
          <?php foreach ($companies as $comp): ?>
              <option value="<?= $comp['id'] ?>"><?= htmlspecialchars($comp['name']) ?></option>
          <?php endforeach; ?>
      </select>
      
      <select id="filter-category" class="form-input text-sm w-auto">
          <option value="">All Categories</option>
          <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>"><?= htmlspecialchars(($cat['main_category_name'] ? $cat['main_category_name'] . ' > ' : '') . $cat['name']) ?></option>
          <?php endforeach; ?>
      </select>

      <select id="filter-stock" class="form-input text-sm w-auto">
          <option value="">Stock: All</option>
          <option value="in_stock">In Stock</option>
          <option value="out_of_stock">Out of Stock</option>
      </select>

      <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i class="fas fa-search text-gray-400"></i>
          </div>
          <input type="text" id="search-input" placeholder="Search by name or SKU..." class="form-input text-sm pl-10 w-64">
      </div>
  </div>

  <div class="overflow-x-auto">
      <table class="data-table whitespace-nowrap" id="products-table">
          <thead>
              <tr>
                                    <th class="py-4 px-6 text-left">Product</th>
                                    <th class="py-4 px-6 text-left">Company/Cat</th>
                                    <th class="py-4 px-6 text-center">Stock</th>
                                    <th class="py-4 px-6 text-right">Pricing</th>
                                    <th class="py-4 px-6 text-center">Packaging</th>
                                    <th class="py-4 px-6 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                 <?php foreach ($items as $p): 
                                     $ppb = max(1, (int)($p['pieces_per_box'] ?? 1));
                                     $totalPieces = ($p['stock_boxes'] * $ppb) + $p['stock_pieces'];
                                     $displayBoxes = floor($totalPieces / $ppb);
                                     $displayPieces = $totalPieces % $ppb;
                                 ?>
                                     <tr class="product-row" 
                                         data-company="<?= $p['company_id'] ?>" 
                                         data-category="<?= $p['category_id'] ?>"
                                         data-boxes="<?= $displayBoxes ?>"
                                         data-pieces="<?= $displayPieces ?>">
                                        <td class="py-4 px-6">
                                            <div class="flex items-center gap-3">
                                                <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden border border-gray-200 shrink-0">
                                                    <?php if($p['image']): ?>
                                                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($p['image']) ?>" class="w-full h-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                                                        <span style="display:none" class="w-full h-full items-center justify-center"><i class="fas fa-box text-gray-400"></i></span>
                                                    <?php else: ?>
                                                        <i class="fas fa-box text-gray-400 text-lg"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <div class="font-medium text-gray-900 product-name"><?= htmlspecialchars($p['name']) ?></div>
                                                    <div class="text-xs text-gray-500 product-sku">SKU: <?= htmlspecialchars($p['sku']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6">
                                            <div class="text-sm font-medium text-gray-800"><?= htmlspecialchars($p['company_name'] ?? 'General') ?></div>
                                            <div class="text-xs text-gray-500"><?= htmlspecialchars($p['category_name'] ?? 'Uncategorized') ?></div>
                                        </td>
                                        <td class="py-4 px-6 text-center">
                                             <?php $isLowStock = ($displayBoxes == 0 && $displayPieces < 10); ?>
                                             <div class="inline-flex flex-col items-center justify-center px-2.5 py-1 rounded-lg text-xs font-medium <?= $isLowStock ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' ?>">
                                                 <div><?= $displayBoxes ?> Box</div>
                                                 <div><?= $displayPieces ?> Pcs</div>
                                             </div>
                                        </td>
                                        <td class="py-4 px-6 text-right">
                                            <?php $sellPricePerBox = (float)$p['buying_price'] * (1 + (float)$p['dealer_percentage'] / 100); ?>
                                            <div class="text-sm font-bold text-gray-900">Sell: ৳<?= number_format($sellPricePerBox, 2) ?></div>
                                            <div class="text-xs text-gray-500">Buy: ৳<?= number_format($p['buying_price'], 2) ?></div>
                                            <div class="text-xs text-indigo-600 font-medium">Dealer: <?= $p['dealer_percentage'] ?>%</div>
                                        </td>
                                        <td class="py-4 px-6 text-center">
                                            <div class="text-sm text-gray-700"><?= htmlspecialchars($p['box_type'] ?? '') ?></div>
                                            <div class="text-xs text-gray-500"><?= $p['pieces_per_box'] ?> Pcs/Box</div>
                                        </td>
                                        <td class="py-4 px-6 text-right">
                                            <div class="flex justify-end gap-2">
                                                <button onclick='editProduct(<?= json_encode($p) ?>)' class="bg-indigo-50 text-indigo-600 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-colors text-sm font-medium" title="Edit Product">
                                                    <i class="fas fa-pen"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($items)): ?>
                                    <tr>
                                        <td colspan="6" class="py-12 text-center text-gray-500">
                                            <i class="fas fa-box-open text-4xl text-gray-300 mb-3 block"></i>
                                            No products found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>


<input type="hidden" id="csrf" value="<?= Helpers::csrfToken() ?>">

<!-- Bulk Save Progress Overlay -->
<div id="bulk-progress-overlay" class="fixed inset-0 z-[100] hidden items-center justify-center bg-gray-900/80 backdrop-blur-sm transition-opacity duration-300 opacity-0">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4 text-center transform scale-95 transition-transform duration-300" id="bulk-progress-box">
        <!-- Spinner & Icon -->
        <div class="relative w-20 h-20 mx-auto mb-6">
            <svg class="animate-spin text-indigo-600 w-full h-full" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" id="progress-spinner">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <div id="progress-success-icon" class="absolute inset-0 hidden items-center justify-center text-emerald-500 bg-white rounded-full">
                <i class="fas fa-check-circle text-5xl animate-bounce"></i>
            </div>
        </div>

        <h3 class="text-2xl font-bold text-gray-900 mb-2" id="progress-title">Saving Products...</h3>
        <p class="text-gray-500 mb-6 text-sm h-10 overflow-hidden" id="progress-text">Preparing data...</p>

        <!-- Progress Bar -->
        <div class="w-full bg-gray-100 rounded-full h-3 mb-2 overflow-hidden">
            <div id="progress-bar-fill" class="bg-indigo-600 h-3 rounded-full transition-all duration-300 ease-out" style="width: 0%"></div>
        </div>
        <div class="flex justify-between text-xs font-medium text-gray-500">
            <span id="progress-percentage">0%</span>
            <span id="progress-count">0 / 0</span>
        </div>
    </div>
</div>

<!-- Bulk Add Modal -->
<div id="add-modal" class="modal-overlay hidden">
    <div class="modal-box p-6" style="max-width: 1024px;">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-xl font-bold text-gray-900">Bulk Add Products</h3>
                <div class="flex items-center gap-3">
                    <button type="button" onclick="document.getElementById('bulk-csv-upload').click()" class="text-emerald-600 hover:text-emerald-800 font-medium text-sm flex items-center gap-2 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200">
                        <i class="fas fa-file-csv"></i> Upload CSV
                    </button>
                    <input type="file" id="bulk-csv-upload" accept=".csv" class="hidden" onchange="handleCSVUpload(this)">
                    <button type="button" onclick="addBulkRow()" class="text-blue-600 hover:text-blue-800 font-medium text-sm flex items-center gap-2 bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-200">
                        <i class="fas fa-plus-circle"></i> Add Row
                    </button>
                    <button type="button" onclick="closeModal('add-modal')" class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-gray-100 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <div class="bg-blue-50 p-4 rounded mb-4 border border-blue-100">
                <label class="block text-sm font-medium text-blue-900 mb-1">Company (Applies to all rows)</label>
                <select id="bulk-company" class="form-input text-sm w-full md:w-1/3">
                    <option value="">Select Company</option>
                    <?php foreach ($companies as $comp): ?>
                        <option value="<?= $comp['id'] ?>"><?= htmlspecialchars($comp['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <form id="bulk-add-form">
                <div class="overflow-x-auto mb-4">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Name *</th>
                                <th>Box Type</th>
                                <th class="w-24">Pcs/Box</th>
                                <th class="w-24">Dealer %</th>
                                <th class="w-28">Image</th>
                                <th class="text-center w-12"><i class="fas fa-trash text-gray-400"></i></th>
                            </tr>
                        </thead>
                        <tbody id="bulk-rows">
                            <!-- Rows injected via JS -->
                        </tbody>
                    </table>
                </div>
                
                <div class="flex justify-end items-center pt-4 border-t mt-4 gap-3">
                    <button type="button" onclick="closeModal('add-modal')" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save All Products</button>
                </div>
            </form>
        </div>
</div>

<!-- Edit Modal -->
<div id="edit-modal" class="modal-overlay hidden">
    <div class="modal-box p-6">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-xl font-bold text-gray-900">Edit Product</h3>
            <button onclick="closeModal('edit-modal')" class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-gray-100 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="edit-form" class="space-y-4">
            <input type="hidden" id="edit-id">
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                    <select id="edit-company" class="form-input text-sm w-full">
                        <option value="">General</option>
                        <?php foreach ($companies as $comp): ?>
                            <option value="<?= $comp['id'] ?>"><?= htmlspecialchars($comp['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select id="edit-category" class="form-input text-sm w-full">
                        <option value="">Uncategorized</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars(($cat['main_category_name'] ? $cat['main_category_name'] . ' > ' : '') . $cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                <input type="text" id="edit-name" class="form-input text-sm w-full" required>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Box Type</label>
                <select id="edit-box-type" class="form-input text-sm w-full">
                    <option value="বক্স">বক্স</option>
                    <option value="পলি">পলি</option>
                    <option value="কার্টুন">কার্টুন</option>
                    <option value="পিস">পিস</option>
                    <option value="বস্তা">বস্তা</option>
                    <option value="জার">জার</option>
                    <option value="কেজি">কেজি</option>
                    <option value="ডজন">ডজন</option>
                    <option value="কম্বো">কম্বো</option>
                </select>
            </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pcs / Box *</label>
                    <input type="number" id="edit-pcs-box" class="form-input text-sm w-full" required min="1">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dealer %</label>
                    <input type="number" step="0.01" id="edit-dealer-pct" class="form-input text-sm w-full" placeholder="0.00">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Product Image</label>
                <div class="flex items-center gap-4">
                    <div class="w-20 h-20 rounded-lg bg-gray-100 border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden shrink-0" id="edit-img-preview-wrap">
                        <img id="edit-img-preview" class="w-full h-full object-cover hidden">
                        <i id="edit-img-icon" class="fas fa-image text-2xl text-gray-300"></i>
                    </div>
                    <div class="flex-1">
                        <input type="file" id="edit-image" accept="image/*" class="form-input text-sm w-full" onchange="previewEditImage(this)">
                        <p class="text-xs text-gray-400 mt-1">Leave empty to keep existing image</p>
                    </div>
                </div>
            </div>

            <div class="flex gap-3 pt-4 border-t mt-4">
                <button type="button" onclick="closeModal('edit-modal')" class="btn btn-secondary flex-1">Cancel</button>
                <button type="submit" class="btn btn-primary flex-1">Save Changes</button>
            </div>
        </form>
    </div>
</div>



<script>
// UI State
const categories = <?= json_encode($categories) ?>;
const categoriesOptions = '<option value="">Sel Cat</option>' + categories.map(c => `<option value="${c.id}">${c.main_category_name ? c.main_category_name + ' > ' : ''}${c.name}</option>`).join('');

function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

// Filters
function applyFilters() {
    const comp = document.getElementById('filter-company').value;
    const cat = document.getElementById('filter-category').value;
    const stock = document.getElementById('filter-stock').value;
    const search = document.getElementById('search-input').value.toLowerCase();

    document.querySelectorAll('.product-row').forEach(row => {
        let show = true;
        if (comp && row.dataset.company != comp) show = false;
        if (cat && row.dataset.category != cat) show = false;
        
        const boxes = parseInt(row.dataset.boxes);
        const pcs = parseInt(row.dataset.pieces);
        if (stock === 'in_stock' && (boxes === 0 && pcs === 0)) show = false;
        if (stock === 'out_of_stock' && (boxes > 0 || pcs > 0)) show = false;

        const text = (row.querySelector('.product-name').innerText + ' ' + row.querySelector('.product-sku').innerText).toLowerCase();
        if (search && !text.includes(search)) show = false;

        row.style.display = show ? '' : 'none';
    });
}
document.getElementById('filter-company').addEventListener('change', applyFilters);
document.getElementById('filter-category').addEventListener('change', applyFilters);
document.getElementById('filter-stock').addEventListener('change', applyFilters);
document.getElementById('search-input').addEventListener('input', applyFilters);

// Bulk Add
let bulkRowIndex = 0;

function addBulkRow(data = null) {
    const tr = document.createElement('tr');
    tr.className = "bulk-row";
    const idx = bulkRowIndex++;
    const boxTypes = `
        <option value="বক্স">বক্স</option>
        <option value="পলি">পলি</option>
        <option value="কার্টুন">কার্টুন</option>
        <option value="পিস">পিস</option>
        <option value="বস্তা">বস্তা</option>
        <option value="জার">জার</option>
        <option value="কেজি">কেজি</option>
        <option value="ডজন">ডজন</option>
        <option value="কম্বো">কম্বো</option>
    `;
    
    // Safely extract data
    const nameVal = data && data.name ? data.name.replace(/"/g, '&quot;') : '';
    const imgUrl = data && data.image_url ? data.image_url.replace(/"/g, '&quot;') : '';
    const priceVal = data && data.price ? data.price.replace(/"/g, '&quot;') : '';
    
    tr.innerHTML = `
        <td class="p-2"><select class="form-input text-sm p-1.5 bulk-cat">${categoriesOptions}</select></td>
        <td class="p-2"><input type="text" class="form-input text-sm p-1.5 bulk-name" placeholder="Name" value="${nameVal}" required></td>
        <td class="p-2"><select class="form-input text-sm p-1.5 bulk-boxtype">${boxTypes}</select></td>
        <td class="p-2"><input type="number" class="form-input text-sm p-1.5 bulk-pcsbox" value="1" min="1" required></td>
        <td class="p-2"><input type="number" step="0.01" class="form-input text-sm p-1.5 bulk-dealerpct" placeholder="0"></td>
        <td class="p-2">
            <label class="flex flex-col items-center justify-center w-20 h-16 border-2 border-dashed border-gray-300 rounded cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-colors group relative overflow-hidden" title="Upload Image">
                <img class="bulk-img-preview absolute inset-0 w-full h-full object-cover rounded ${imgUrl ? '' : 'hidden'}" src="${imgUrl}">
                <div class="bulk-img-placeholder flex flex-col items-center ${imgUrl ? 'hidden' : ''}">
                    <i class="fas fa-image text-gray-300 text-xl group-hover:text-blue-400"></i>
                    <span class="text-[10px] text-gray-400 mt-0.5">Upload</span>
                </div>
                <input type="file" name="images[${idx}]" accept="image/*" class="bulk-img-input sr-only" onchange="previewBulkImage(this)">
                <input type="hidden" name="row_indices[]" value="${idx}">
                <input type="hidden" class="bulk-image-url" value="${imgUrl}">
                <input type="hidden" class="bulk-price-piece" value="${priceVal}">
            </label>
        </td>
        <td class="p-2 text-center"><button type="button" onclick="this.closest('tr').remove()" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button></td>
    `;
    document.getElementById('bulk-rows').appendChild(tr);
}

function previewBulkImage(input) {
    if (!input.files || !input.files[0]) return;
    const label = input.closest('label');
    const preview = label.querySelector('.bulk-img-preview');
    const placeholder = label.querySelector('.bulk-img-placeholder');
    const reader = new FileReader();
    reader.onload = (e) => {
        preview.src = e.target.result;
        preview.classList.remove('hidden');
        placeholder.classList.add('hidden');
    };
    reader.readAsDataURL(input.files[0]);
}

// Initialize with 1 row
for(let i=0; i<1; i++) addBulkRow();

document.getElementById('bulk-add-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const rows = Array.from(document.querySelectorAll('.bulk-row')).filter(tr => tr.querySelector('.bulk-name').value);
    
    if (!rows.length) { alert('No valid products to save.'); return; }

    const company_id = document.getElementById('bulk-company').value;
    const csrfToken = document.getElementById('csrf').value;
    
    const overlay = document.getElementById('bulk-progress-overlay');
    const overlayBox = document.getElementById('bulk-progress-box');
    const titleEl = document.getElementById('progress-title');
    const textEl = document.getElementById('progress-text');
    const barEl = document.getElementById('progress-bar-fill');
    const pctEl = document.getElementById('progress-percentage');
    const countEl = document.getElementById('progress-count');
    const spinner = document.getElementById('progress-spinner');
    const successIcon = document.getElementById('progress-success-icon');

    // Close Bulk Add Modal
    closeModal('add-modal');

    // Show Overlay
    overlay.classList.remove('hidden');
    overlay.classList.add('flex');
    setTimeout(() => {
        overlay.classList.remove('opacity-0');
        overlayBox.classList.remove('scale-95');
    }, 10);

    titleEl.innerText = "Saving Products...";
    titleEl.className = "text-2xl font-bold text-gray-900 mb-2";
    spinner.classList.remove('hidden');
    successIcon.classList.add('hidden');
    successIcon.classList.remove('flex');
    barEl.style.width = "0%";
    barEl.className = "bg-indigo-600 h-3 rounded-full transition-all duration-300 ease-out";
    pctEl.innerText = "0%";
    countEl.innerText = `0 / ${rows.length}`;

    let successCount = 0;
    let failCount = 0;

    for (let i = 0; i < rows.length; i++) {
        const tr = rows[i];
        const name = tr.querySelector('.bulk-name').value;
        textEl.innerHTML = `Saving <strong>${name}</strong>...<br><span class="text-xs text-gray-400">Downloading image & saving data</span>`;
        
        const item = {
            category_id: tr.querySelector('.bulk-cat').value,
            name: name,
            box_type: tr.querySelector('.bulk-boxtype').value,
            pieces_per_box: tr.querySelector('.bulk-pcsbox').value,
            dealer_percentage: tr.querySelector('.bulk-dealerpct').value,
            image_url: tr.querySelector('.bulk-image-url').value,
            price_piece: tr.querySelector('.bulk-price-piece').value
        };

        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('company_id', company_id);
        formData.append('items', JSON.stringify([item])); 

        // Attach image if uploaded manually
        const fileInput = tr.querySelector('.bulk-img-input');
        if (fileInput && fileInput.files[0]) {
            formData.append('row_indices[]', 0); // index 0 since items array has 1 element
            formData.append('images[0]', fileInput.files[0]);
        }

        try {
            const res = await fetch('<?= BASE_URL ?>/manager/api/products', {
                method: 'POST',
                body: formData
            });
            const text = await res.text();
            try { 
                const data = JSON.parse(text); 
                if (data.success) {
                    successCount++;
                } else {
                    failCount++;
                    console.error("Failed to save:", name, data.message);
                }
            } catch(e) {
                failCount++;
                console.error("Server error for:", name, text.substring(0, 200));
            }
        } catch(err) {
            failCount++;
            console.error("Network error for:", name, err.message);
        }

        // Update progress
        const pct = Math.round(((i + 1) / rows.length) * 100);
        barEl.style.width = pct + "%";
        pctEl.innerText = pct + "%";
        countEl.innerText = `${i + 1} / ${rows.length}`;
    }

    // Finished
    spinner.classList.add('hidden');
    successIcon.classList.remove('hidden');
    successIcon.classList.add('flex');
    barEl.className = "bg-emerald-500 h-3 rounded-full transition-all duration-300 ease-out";
    
    if (failCount > 0) {
        titleEl.innerText = "Completed with Errors";
        titleEl.classList.add("text-amber-600");
        textEl.innerHTML = `${successCount} saved successfully.<br><span class="text-red-500">${failCount} failed. Check console for details.</span>`;
    } else {
        titleEl.innerText = "Success!";
        titleEl.classList.add("text-emerald-600");
        textEl.innerText = `All ${successCount} products saved successfully.`;
    }

    // Reload after a short delay
    setTimeout(() => {
        location.reload();
    }, 2000);
});

function handleCSVUpload(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        const text = e.target.result;
        const rows = text.split(/\r?\n/);
        
        document.getElementById('bulk-rows').innerHTML = ''; // Clear rows
        
        let added = 0;
        for (let i = 0; i < rows.length; i++) {
            const rowStr = rows[i].trim();
            if (!rowStr) continue;
            
            // Simple split by comma. 
            const cols = rowStr.split(',');
            // Skip header
            if (i === 0 && cols[0].toLowerCase().includes('name')) continue;
            
            if (cols.length >= 1) {
                const name = cols[0] ? cols[0].replace(/^"|"$/g, '').trim() : '';
                const image_url = cols[1] ? cols[1].replace(/^"|"$/g, '').trim() : '';
                const price = cols[2] ? cols[2].replace(/^"|"$/g, '').trim() : '';
                
                if (name) {
                    addBulkRow({ name, image_url, price });
                    added++;
                }
            }
        }
        
        if (added === 0) {
            addBulkRow(); 
            alert("No valid products found in CSV.");
        }
    };
    reader.readAsText(input.files[0]);
    input.value = '';
}

// Edit
function previewEditImage(input) {
    if (!input.files || !input.files[0]) return;
    const preview = document.getElementById('edit-img-preview');
    const icon = document.getElementById('edit-img-icon');
    const reader = new FileReader();
    reader.onload = (e) => {
        preview.src = e.target.result;
        preview.classList.remove('hidden');
        if (icon) icon.classList.add('hidden');
    };
    reader.readAsDataURL(input.files[0]);
}

function editProduct(p) {
    document.getElementById('edit-id').value = p.id;
    document.getElementById('edit-company').value = p.company_id || '';
    document.getElementById('edit-category').value = p.category_id || '';
    document.getElementById('edit-name').value = p.name;
    document.getElementById('edit-box-type').value = p.box_type || 'বক্স';
    document.getElementById('edit-pcs-box').value = p.pieces_per_box;
    document.getElementById('edit-dealer-pct').value = p.dealer_percentage;
    
    // Show existing image preview
    const preview = document.getElementById('edit-img-preview');
    const icon = document.getElementById('edit-img-icon');
    if (p.image) {
        preview.src = '<?= BASE_URL ?>/' + p.image;
        preview.classList.remove('hidden');
        if (icon) icon.classList.add('hidden');
    } else {
        preview.classList.add('hidden');
        preview.src = '';
        if (icon) icon.classList.remove('hidden');
    }
    // Reset file input
    document.getElementById('edit-image').value = '';
    
    openModal('edit-modal');
}

document.getElementById('edit-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData();
    formData.append('csrf_token', document.getElementById('csrf').value);
    formData.append('id', document.getElementById('edit-id').value);
    formData.append('company_id', document.getElementById('edit-company').value);
    formData.append('category_id', document.getElementById('edit-category').value);
    formData.append('name', document.getElementById('edit-name').value);
    formData.append('box_type', document.getElementById('edit-box-type').value);
    formData.append('pieces_per_box', document.getElementById('edit-pcs-box').value);
    formData.append('dealer_percentage', document.getElementById('edit-dealer-pct').value);
    
    const imgInput = document.getElementById('edit-image');
    if (imgInput.files.length > 0) {
        formData.append('image', imgInput.files[0]);
    }

    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';

    try {
        const res = await fetch('<?= BASE_URL ?>/manager/api/products/update', {
            method: 'POST',
            body: formData
        });
        const text = await res.text();
        let data;
        try { 
            data = JSON.parse(text); 
        } catch(e) {
            alert('Server error:\n' + text.substring(0, 400));
            btn.disabled = false; btn.innerHTML = 'Save Changes';
            return;
        }
        if (data.success) location.reload();
        else { alert('Error: ' + (data.message || 'Failed to update')); btn.disabled = false; btn.innerHTML = 'Save Changes'; }
    } catch(err) { alert('Network error: ' + err.message); btn.disabled = false; btn.innerHTML = 'Save Changes'; }
});

// Paste clipboard image to hovered bulk row
let hoveredBulkRow = null;
document.getElementById('bulk-rows').addEventListener('mouseover', (e) => {
    hoveredBulkRow = e.target.closest('.bulk-row');
});
document.getElementById('bulk-rows').addEventListener('mouseout', (e) => {
    hoveredBulkRow = null;
});

window.addEventListener('paste', (e) => {
    const items = (e.clipboardData || e.originalEvent.clipboardData).items;
    let imageFile = null;
    for (let i = 0; i < items.length; i++) {
        if (items[i].type.indexOf('image') === 0) {
            imageFile = items[i].getAsFile();
            break;
        }
    }
    if (!imageFile) return;

    if (hoveredBulkRow) {
        const fileInput = hoveredBulkRow.querySelector('.bulk-img-input');
        if (fileInput) {
            const dataTransfer = new DataTransfer();
            const file = new File([imageFile], `paste_${Date.now()}.png`, { type: imageFile.type });
            dataTransfer.items.add(file);
            fileInput.files = dataTransfer.files;
            previewBulkImage(fileInput);
        }
    }
});

</script>

