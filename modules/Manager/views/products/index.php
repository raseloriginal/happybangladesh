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
                                              <?php $isLowStock = ($totalPieces < 10); ?>
                                              <div class="inline-flex flex-col items-center justify-center px-2.5 py-1 rounded-lg text-xs font-medium <?= $isLowStock ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' ?>">
                                                   <div>
                                                       <?php
                                                       $boxType = trim($p['box_type'] ?? '');
                                                       $boxTypeLower = strtolower($boxType);
                                                       if ($boxTypeLower === 'pcs') {
                                                           echo $totalPieces . ' পিস';
                                                       } elseif ($boxType === 'পিস' || $boxType === 'পলি' || $boxType === 'জার') {
                                                           echo $totalPieces . ' ' . htmlspecialchars($boxType);
                                                       } else {
                                                           $boxLabel = !empty($boxType) ? $boxType : 'Box';
                                                           echo $displayBoxes . ' ' . htmlspecialchars($boxLabel) . ' - ' . $displayPieces . ' পিস';
                                                       }
                                                       ?>
                                                   </div>
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
                                                 <button onclick='openPriceHistoryModal(<?= (int)$p['id'] ?>, <?= htmlspecialchars(json_encode($p['name']), ENT_QUOTES, 'UTF-8') ?>)' class="bg-blue-50 text-blue-600 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors text-sm font-medium" title="Price Change History">
                                                     <i class="fas fa-history"></i>
                                                 </button>
                                                 <button onclick='openAdjustPriceModal(<?= htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8') ?>)' class="bg-amber-50 text-amber-600 hover:bg-amber-100 px-3 py-1.5 rounded-lg transition-colors text-sm font-medium" title="Adjust Buying Price">
                                                     <i class="fas fa-tag"></i>
                                                 </button>
                                                 <button onclick='editProduct(<?= htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8') ?>)' class="bg-indigo-50 text-indigo-600 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-colors text-sm font-medium" title="Edit Product">
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
                    <button type="button" onclick="downloadSampleCSV()" class="text-purple-600 hover:text-purple-800 font-medium text-sm flex items-center gap-2 bg-purple-50 px-3 py-1.5 rounded-lg border border-purple-200" title="Download Sample CSV">
                        <i class="fas fa-download"></i> Sample CSV
                    </button>
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
                    <option value="পলি">পলি</option>
                    <option value="কেস">কেস</option>
                    <option value="বান্ডিল">বান্ডিল</option>
                    <option value="বক্স">বক্স</option>
                    <option value="কার্টন">কার্টন</option>
                    <option value="পিস">পিস</option>
                    <option value="বস্তা">বস্তা</option>
                    <option value="জার">জার</option>
                    <option value="ড্রাম">ড্রাম</option>
                    <option value="কেজি">কেজি</option>
                    <option value="ডজন">ডজন</option>
                    <option value="কম্বো">কম্বো</option>
                    <option value="পাতা">পাতা</option>
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

<!-- Adjust Buying Price Modal -->
<div id="adjust-price-modal" class="modal-overlay hidden">
    <div class="modal-box p-6 max-w-md w-full">
        <div class="flex justify-between items-center mb-4 pb-3 border-b">
            <div>
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-tag text-amber-500"></i> Adjust Buying Price
                </h3>
                <p id="adjust-product-name" class="text-xs text-gray-500 font-medium mt-0.5"></p>
            </div>
            <button onclick="closeModal('adjust-price-modal')" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-full hover:bg-gray-100 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="adjust-price-form" class="space-y-4">
            <input type="hidden" id="adjust-product-id">
            <input type="hidden" id="adjust-dealer-pct-val" value="0">
            <input type="hidden" id="adjust-pcs-per-box-val" value="1">

            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3.5 space-y-2 text-xs text-amber-900">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 font-medium">Current Buying Price:</span>
                    <span id="adjust-curr-buy" class="font-bold text-gray-900 text-sm">৳0.00</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 font-medium">Dealer Commission:</span>
                    <span id="adjust-dealer-pct" class="font-bold text-indigo-700">0%</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 font-medium">Current Selling Price:</span>
                    <span id="adjust-curr-sell" class="font-bold text-emerald-700">৳0.00</span>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">New Buying Price (৳) *</label>
                <div class="relative flex items-center">
                    <span class="absolute left-3 text-gray-500 font-bold text-base pointer-events-none z-10 select-none">৳</span>
                    <input type="number" step="0.01" min="0" id="adjust-new-buy-price" class="form-input text-sm w-full py-2 font-semibold text-gray-900" style="padding-left: 2.25rem !important;" placeholder="0.00" required>
                </div>
            </div>

            <div class="bg-indigo-50/70 border border-indigo-100 rounded-xl p-3.5 text-xs text-indigo-950">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 font-medium">New Calculated Selling Price (per piece):</span>
                    <span id="calc-new-sell-piece" class="font-bold text-indigo-700 text-sm">৳0.00</span>
                </div>
            </div>

            <div class="flex gap-3 pt-3 border-t mt-4">
                <button type="button" onclick="closeModal('adjust-price-modal')" class="btn btn-secondary flex-1">Cancel</button>
                <button type="submit" id="btn-submit-adjust-price" class="btn btn-primary bg-amber-600 hover:bg-amber-700 border-amber-600 flex-1 flex items-center justify-center gap-2">
                    <i class="fas fa-check"></i> Update Price
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Product Price History Modal -->
<div id="price-history-modal" class="modal-overlay hidden">
    <div class="modal-box p-6 max-w-2xl w-full">
        <div class="flex justify-between items-center mb-4 pb-3 border-b">
            <div>
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-history text-blue-600"></i> Product Price History
                </h3>
                <p id="history-product-name" class="text-xs text-gray-500 font-medium mt-0.5"></p>
            </div>
            <button onclick="closeModal('price-history-modal')" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-full hover:bg-gray-100 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div id="history-loading" class="py-12 text-center text-gray-500">
            <i class="fas fa-circle-notch fa-spin text-3xl text-blue-500 mb-2"></i>
            <p class="text-sm">Loading price history...</p>
        </div>

        <div id="history-content" class="hidden space-y-4 max-h-[60vh] overflow-y-auto pr-1">
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-3 text-xs">
                    <div class="text-gray-500 font-medium">Current Buying Price</div>
                    <div id="history-curr-buy" class="text-base font-bold text-gray-900 mt-0.5">৳0.00</div>
                </div>
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 text-xs">
                    <div class="text-emerald-700 font-medium">Current Selling Price (Pcs)</div>
                    <div id="history-curr-sell" class="text-base font-bold text-emerald-800 mt-0.5">৳0.00</div>
                </div>
            </div>

            <div class="overflow-hidden border border-gray-200 rounded-xl">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-gray-100/75 text-gray-600 uppercase font-semibold text-[10px] tracking-wider border-b border-gray-200">
                        <tr>
                            <th class="py-2.5 px-3">Date & Time</th>
                            <th class="py-2.5 px-3">Type / By</th>
                            <th class="py-2.5 px-3 text-right">Buying Price</th>
                            <th class="py-2.5 px-3 text-right">Selling Price</th>
                            <th class="py-2.5 px-3">Reason</th>
                        </tr>
                    </thead>
                    <tbody id="history-table-body" class="divide-y divide-gray-100 bg-white">
                        <!-- Dynamic rows -->
                    </tbody>
                </table>
            </div>

            <div id="history-empty" class="hidden py-8 text-center text-gray-400">
                <i class="fas fa-file-invoice-dollar text-3xl mb-2 block text-gray-300"></i>
                No price history records found for this product.
            </div>
        </div>

        <div class="flex justify-end pt-3 border-t mt-4">
            <button type="button" onclick="closeModal('price-history-modal')" class="btn btn-secondary px-5">Close</button>
        </div>
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
        <option value="পলি">পলি</option>
        <option value="কেস">কেস</option>
        <option value="বান্ডিল">বান্ডিল</option>
        <option value="বক্স">বক্স</option>
        <option value="কার্টন">কার্টন</option>
        <option value="পিস">পিস</option>
        <option value="বস্তা">বস্তা</option>
        <option value="জার">জার</option>
        <option value="ড্রাম">ড্রাম</option>
        <option value="কেজি">কেজি</option>
        <option value="ডজন">ডজন</option>
        <option value="কম্বো">কম্বো</option>
        <option value="পাতা">পাতা</option>
    `;
    
    // Safely extract data
    const nameVal = data && data.name ? data.name.replace(/"/g, '&quot;') : '';
    const imgUrl = data && data.image_url ? data.image_url.replace(/"/g, '&quot;') : '';
    const priceVal = data && data.price ? data.price.toString().replace(/[^0-9.]/g, '') : '';
    const boxTypeVal = data && data.box_type ? data.box_type.replace(/"/g, '&quot;').trim() : '';
    const targetBoxType = boxTypeVal || 'পিস';
    
    const rawPcsBox = data && data.pcs_box !== undefined && data.pcs_box !== '' ? data.pcs_box : (data && data.pieces_per_box !== undefined && data.pieces_per_box !== '' ? data.pieces_per_box : '1');
    const pcsBoxVal = rawPcsBox ? (rawPcsBox.toString().replace(/[^0-9]/g, '') || '1') : '1';
    
    const rawDealerPct = data && data.dealer_pct !== undefined && data.dealer_pct !== '' ? data.dealer_pct : (data && data.dealer_percentage !== undefined && data.dealer_percentage !== '' ? data.dealer_percentage : '');
    const dealerPctVal = rawDealerPct ? rawDealerPct.toString().replace(/%/g, '').trim() : '';
    
    tr.innerHTML = `
        <td class="p-2"><select class="form-input text-sm p-1.5 bulk-cat">${categoriesOptions}</select></td>
        <td class="p-2"><input type="text" class="form-input text-sm p-1.5 bulk-name" placeholder="Name" value="${nameVal}" required></td>
        <td class="p-2"><select class="form-input text-sm p-1.5 bulk-boxtype">${boxTypes}</select></td>
        <td class="p-2"><input type="number" class="form-input text-sm p-1.5 bulk-pcsbox" value="${pcsBoxVal}" min="1" required></td>
        <td class="p-2"><input type="number" step="0.01" class="form-input text-sm p-1.5 bulk-dealerpct" placeholder="0" value="${dealerPctVal}"></td>
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

    const sel = tr.querySelector('.bulk-boxtype');
    if (sel) {
        const matched = Array.from(sel.options).find(opt => opt.value.trim().toLowerCase() === targetBoxType.toLowerCase() || opt.text.trim().toLowerCase() === targetBoxType.toLowerCase());
        if (matched) {
            sel.value = matched.value;
        }
    }
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
        
        let imgUrlVal = tr.querySelector('.bulk-image-url') ? tr.querySelector('.bulk-image-url').value.trim() : '';
        const previewEl = tr.querySelector('.bulk-img-preview');
        if (!imgUrlVal && previewEl && previewEl.src && !previewEl.classList.contains('hidden') && !previewEl.src.endsWith('/')) {
            imgUrlVal = previewEl.src;
        }

        const item = {
            category_id: tr.querySelector('.bulk-cat').value,
            name: name,
            box_type: tr.querySelector('.bulk-boxtype').value,
            pieces_per_box: tr.querySelector('.bulk-pcsbox').value,
            dealer_percentage: tr.querySelector('.bulk-dealerpct').value,
            image_url: imgUrlVal,
            price_piece: tr.querySelector('.bulk-price-piece').value
        };

        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('company_id', company_id);
        formData.append('items', JSON.stringify([item])); 

        // Attach image if uploaded manually or if blob URL
        const fileInput = tr.querySelector('.bulk-img-input');
        if (fileInput && fileInput.files[0]) {
            formData.append('row_indices[]', 0); // index 0 since items array has 1 element
            formData.append('images[0]', fileInput.files[0]);
        } else if (imgUrlVal.startsWith('blob:')) {
            try {
                const blobRes = await fetch(imgUrlVal);
                const blobData = await blobRes.blob();
                formData.append('row_indices[]', 0);
                formData.append('images[0]', blobData, 'product_image.jpg');
            } catch(e) {
                console.error("Failed to fetch blob image:", e);
            }
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

function downloadSampleCSV() {
    const csvContent = "\uFEFFProduct Name,Image URL,Price / Piece,Dealer Com,Box Type,Pcs / Box\n" +
                       "\"Sample Product 1\",\"https://via.placeholder.com/150\",\"100\",\"5.5\",\"বক্স\",\"10\"\n" +
                       "\"Sample Product 2\",\"\",\"50\",\"3.0\",\"পলি\",\"12\"";
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = "Product_Bulk_Upload_Sample.csv";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function parseCSVLine(text) {
    let result = [];
    let cur = '';
    let inQuotes = false;
    for (let i = 0; i < text.length; i++) {
        let c = text[i];
        if (c === '"') {
            if (inQuotes && text[i+1] === '"') {
                cur += '"';
                i++;
            } else {
                inQuotes = !inQuotes;
            }
        } else if (c === ',' && !inQuotes) {
            result.push(cur.trim());
            cur = '';
        } else {
            cur += c;
        }
    }
    result.push(cur.trim());
    return result;
}

function handleCSVUpload(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        const text = e.target.result;
        const rawLines = text.split(/\r?\n/);
        const lines = rawLines.map(l => l.trim()).filter(l => l.length > 0);
        
        if (lines.length === 0) {
            alert("CSV file is empty.");
            input.value = '';
            return;
        }
        
        document.getElementById('bulk-rows').innerHTML = ''; // Clear rows
        
        let startRow = 0;
        const firstLineCols = parseCSVLine(lines[0]);
        
        // Check if line 0 is header
        const isHeader = firstLineCols.some(c => {
            const l = c.toLowerCase();
            return l.includes('name') || l.includes('product') || l.includes('dealer') || l.includes('commission') || l.includes('commision') || l.includes('com') || l.includes('box') || l.includes('pcs') || l.includes('image') || l.includes('নাম') || l.includes('কমিশন');
        });
        
        let nameIdx = 0, imgIdx = 1, priceIdx = -1, dealerIdx = -1, boxTypeIdx = -1, pcsBoxIdx = -1;
        
        if (isHeader) {
            startRow = 1;
            firstLineCols.forEach((col, idx) => {
                const norm = col.toLowerCase().replace(/^"|"$/g, '').trim();
                if (norm.includes('pcs') || norm.includes('piece') || norm.includes('পিস')) pcsBoxIdx = idx;
                else if (norm.includes('name') || norm.includes('product') || norm.includes('নাম')) nameIdx = idx;
                else if (norm.includes('image') || norm.includes('img') || norm.includes('photo') || norm.includes('url') || norm.includes('ছবি')) imgIdx = idx;
                else if (norm.includes('dealer') || norm.includes('commission') || norm.includes('commision') || (norm.includes('com') && !norm.includes('company')) || norm.includes('ডিলার') || norm.includes('কমিশন')) dealerIdx = idx;
                else if (norm.includes('box') || norm.includes('boxtype') || norm.includes('pack') || norm.includes('বক্স')) boxTypeIdx = idx;
                else if (norm.includes('price') || norm.includes('buy') || norm.includes('মূল্য')) priceIdx = idx;
            });
        } else {
            // Positional fallback
            const colCount = firstLineCols.length;
            if (colCount >= 6) {
                // Name, Image, Price, Dealer %, Box Type, PCS per Box
                nameIdx = 0; imgIdx = 1; priceIdx = 2; dealerIdx = 3; boxTypeIdx = 4; pcsBoxIdx = 5;
            } else if (colCount === 5) {
                // Name, Image, Dealer %, Box Type, PCS per Box
                nameIdx = 0; imgIdx = 1; dealerIdx = 2; boxTypeIdx = 3; pcsBoxIdx = 4;
            } else if (colCount === 4) {
                // Name, Dealer %, Box Type, PCS per Box
                nameIdx = 0; imgIdx = -1; dealerIdx = 1; boxTypeIdx = 2; pcsBoxIdx = 3;
            } else if (colCount === 3) {
                // Name, Image, Price
                nameIdx = 0; imgIdx = 1; priceIdx = 2;
            }
        }

        let added = 0;
        for (let i = startRow; i < lines.length; i++) {
            const cols = parseCSVLine(lines[i]);
            if (!cols || cols.length === 0) continue;
            
            const name = nameIdx >= 0 && cols[nameIdx] ? cols[nameIdx].replace(/^"|"$/g, '').trim() : '';
            const image_url = imgIdx >= 0 && cols[imgIdx] ? cols[imgIdx].replace(/^"|"$/g, '').trim() : '';
            const price = priceIdx >= 0 && cols[priceIdx] ? cols[priceIdx].replace(/^"|"$/g, '').trim() : '';
            const dealer_pct = dealerIdx >= 0 && cols[dealerIdx] ? cols[dealerIdx].replace(/^"|"$/g, '').trim() : '';
            const box_type = boxTypeIdx >= 0 && cols[boxTypeIdx] ? cols[boxTypeIdx].replace(/^"|"$/g, '').trim() : '';
            const pcs_box = pcsBoxIdx >= 0 && cols[pcsBoxIdx] ? cols[pcsBoxIdx].replace(/^"|"$/g, '').trim() : '';
            
            if (name) {
                addBulkRow({ name, image_url, price, dealer_pct, box_type, pcs_box });
                added++;
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

// Adjust Buying Price Functions
function openAdjustPriceModal(p) {
    document.getElementById('adjust-product-id').value = p.id;
    document.getElementById('adjust-product-name').textContent = p.name + (p.sku ? ' (SKU: ' + p.sku + ')' : '');
    
    const buyPrice = parseFloat(p.buying_price || 0);
    const dealerPct = parseFloat(p.dealer_percentage || 0);
    const pcsPerBox = Math.max(1, parseFloat(p.pieces_per_box || 1));
    const currSellPriceBox = buyPrice * (1 + dealerPct / 100);

    document.getElementById('adjust-curr-buy').textContent = '৳' + buyPrice.toFixed(2);
    document.getElementById('adjust-dealer-pct').textContent = dealerPct + '%';
    document.getElementById('adjust-curr-sell').textContent = '৳' + currSellPriceBox.toFixed(2);

    document.getElementById('adjust-dealer-pct-val').value = dealerPct;
    document.getElementById('adjust-pcs-per-box-val').value = pcsPerBox;
    document.getElementById('adjust-new-buy-price').value = buyPrice ? buyPrice.toFixed(2) : '';

    calculateNewSellingPrice();
    openModal('adjust-price-modal');
}

function calculateNewSellingPrice() {
    const newBuy = parseFloat(document.getElementById('adjust-new-buy-price').value) || 0;
    const dealerPct = parseFloat(document.getElementById('adjust-dealer-pct-val').value) || 0;
    const pcsPerBox = Math.max(1, parseFloat(document.getElementById('adjust-pcs-per-box-val').value) || 1);

    const newSellBox = newBuy * (1 + dealerPct / 100);
    const newSellPiece = newSellBox / pcsPerBox;

    const elBox = document.getElementById('calc-new-sell-box');
    if (elBox) elBox.textContent = '৳' + newSellBox.toFixed(2);
    
    const elPiece = document.getElementById('calc-new-sell-piece');
    if (elPiece) elPiece.textContent = '৳' + newSellPiece.toFixed(2);
}

document.getElementById('adjust-new-buy-price').addEventListener('input', calculateNewSellingPrice);

document.getElementById('adjust-price-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const pid = document.getElementById('adjust-product-id').value;
    const newBuyPrice = document.getElementById('adjust-new-buy-price').value;
    const csrf = document.getElementById('csrf').value;

    if (!pid || !newBuyPrice) return alert('Please enter a valid buying price.');

    const btn = document.getElementById('btn-submit-adjust-price');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

    try {
        const res = await fetch('<?= BASE_URL ?>/manager/api/products/adjust-buying-price', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                csrf_token: csrf,
                product_id: pid,
                buying_price: newBuyPrice
            })
        });

        const data = await res.json();
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to update buying price'));
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (err) {
        alert('Network error: ' + err.message);
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
});

// Price History Handler
async function openPriceHistoryModal(productId, productName) {
    document.getElementById('history-product-name').textContent = productName || '';
    document.getElementById('history-loading').classList.remove('hidden');
    document.getElementById('history-content').classList.add('hidden');
    openModal('price-history-modal');

    const csrf = document.getElementById('csrf') ? document.getElementById('csrf').value : '';

    try {
        const res = await fetch(`<?= BASE_URL ?>/manager/api/products/price-history?product_id=${productId}&csrf_token=${csrf}`);
        const data = await res.json();

        document.getElementById('history-loading').classList.add('hidden');
        document.getElementById('history-content').classList.remove('hidden');

        if (data.success && data.product) {
            document.getElementById('history-curr-buy').textContent = '৳' + parseFloat(data.product.buying_price || 0).toFixed(2);
            document.getElementById('history-curr-sell').textContent = '৳' + parseFloat(data.product.price || 0).toFixed(2);

            const tbody = document.getElementById('history-table-body');
            tbody.innerHTML = '';

            const typeBadges = {
                'manual_adjust': '<span class="bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded text-[10px] font-semibold">Adjust</span>',
                'lot_entry': '<span class="bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded text-[10px] font-semibold">Lot Entry</span>',
                'lot_edit': '<span class="bg-indigo-100 text-indigo-800 px-1.5 py-0.5 rounded text-[10px] font-semibold">Lot Edit</span>',
                'admin_approval': '<span class="bg-purple-100 text-purple-800 px-1.5 py-0.5 rounded text-[10px] font-semibold">Admin</span>',
                'initial_creation': '<span class="bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded text-[10px] font-semibold">Created</span>'
            };

            if (data.history && data.history.length > 0) {
                document.getElementById('history-empty').classList.add('hidden');
                data.history.forEach(h => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-gray-50/50';

                    const oldBuy = h.old_buying_price !== null ? '৳' + parseFloat(h.old_buying_price).toFixed(2) : '-';
                    const newBuy = '৳' + parseFloat(h.new_buying_price).toFixed(2);
                    const oldSell = h.old_selling_price !== null ? '৳' + parseFloat(h.old_selling_price).toFixed(2) : '-';
                    const newSell = '৳' + parseFloat(h.new_selling_price).toFixed(2);

                    const typeBadge = typeBadges[h.change_type] || `<span class="bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded text-[10px]">${h.change_type}</span>`;
                    const userText = h.user_name ? `${h.user_name} <span class="text-gray-400 text-[10px]">(${h.role_name || 'User'})</span>` : '<span class="text-gray-400">System</span>';

                    tr.innerHTML = `
                        <td class="py-2.5 px-3 whitespace-nowrap text-gray-600 font-medium">
                            ${h.created_at}
                        </td>
                        <td class="py-2.5 px-3">
                            <div class="flex items-center gap-1.5">${typeBadge}</div>
                            <div class="text-[11px] text-gray-700 mt-0.5">${userText}</div>
                        </td>
                        <td class="py-2.5 px-3 text-right whitespace-nowrap">
                            <span class="text-gray-400 line-through text-[11px]">${oldBuy}</span>
                            <span class="font-bold text-gray-900 ml-1">${newBuy}</span>
                        </td>
                        <td class="py-2.5 px-3 text-right whitespace-nowrap">
                            <span class="text-gray-400 line-through text-[11px]">${oldSell}</span>
                            <span class="font-bold text-emerald-700 ml-1">${newSell}</span>
                        </td>
                        <td class="py-2.5 px-3 text-gray-500 text-[11px]">
                            ${h.reason || '-'}
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                document.getElementById('history-empty').classList.remove('hidden');
            }
        } else {
            alert('Failed to load history: ' + (data.message || 'Unknown error'));
        }
    } catch (err) {
        document.getElementById('history-loading').classList.add('hidden');
        alert('Network error: ' + err.message);
    }
}

</script>

