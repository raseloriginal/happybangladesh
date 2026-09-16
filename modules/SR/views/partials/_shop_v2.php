<style>
  @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap');

  .sr-retailer-popup-v2,
  .sr-bottom-sheet-v2,
  .sr-modal-overlay,
  .sr-success-overlay-v2,
  .sr-confirm-modal {
    font-family: 'Hind Siliguri', 'Inter', sans-serif !important;
  }
  .sr-line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  /* Redesign the floating bottom cart bar */
  .sr-popup-cart-bar-v2 {
    background: #0f172a !important; /* Dark Slate theme */
    border: 1px solid #1e293b !important;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.3) !important;
    border-radius: 20px !important;
    height: 60px !important;
  }
  .sr-cart-badge-btn-v2 {
    background: #1e293b !important;
    color: #cbd5e1 !important;
  }
  .sr-cart-checkout-btn-v2 {
    background: #2563eb !important;
    border-radius: 12px !important;
    font-weight: 800 !important;
    padding: 0 20px !important;
    height: 44px !important;
    transition: all 0.2s !important;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2) !important;
  }
  .sr-cart-checkout-btn-v2:hover {
    background: #1d4ed8 !important;
  }
  .sr-cart-checkout-btn-v2:active {
    transform: scale(0.97) !important;
  }
  .sr-cart-thumb-img-v2, .sr-cart-thumb-more-v2 {
    border: 1.5px solid #1e293b !important;
    background: #1e293b !important;
    color: #cbd5e1 !important;
  }

  /* Prevent Flash of Unstyled Content (FOUC) for overlays on page load */
  .sr-sheet-overlay,
  .sr-bottom-sheet,
  .sr-bottom-sheet-v2,
  .sr-fullmap-overlay,
  .sr-modal-overlay,
  .sr-retailer-popup-v2,
  .sr-success-overlay-v2 {
    visibility: hidden;
  }
  
  .sr-sheet-overlay.open,
  .sr-bottom-sheet.open,
  .sr-bottom-sheet-v2.open,
  .sr-modal-overlay.open,
  .sr-retailer-popup-v2.open,
  .sr-success-overlay-v2.open {
    visibility: visible !important;
  }
  
  .sr-fullmap-overlay:not(.hidden) {
    visibility: visible !important;
  }

  /* Custom Confirm Modal Styling - moved to top to prevent FOUC */
  .sr-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 3000;
    opacity: 0;
    transition: opacity 0.25s ease;
  }
  .sr-modal-overlay.open {
    opacity: 1;
  }
  .sr-confirm-modal {
    background: #ffffff;
    border-radius: 16px;
    width: 90%;
    max-width: 380px;
    padding: 24px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    transform: translateY(20px);
    transition: transform 0.25s ease;
  }
  .sr-modal-overlay.open .sr-confirm-modal {
    transform: translateY(0);
  }
  .sr-confirm-title {
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--sr-text);
    margin-bottom: 12px;
    display: flex;
    align-items: center;
  }
  .sr-confirm-body {
    font-size: 0.9rem;
    color: var(--sr-text-muted);
    line-height: 1.5;
    margin-bottom: 24px;
  }
  .sr-confirm-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
  }
  .sr-confirm-btn-no {
    background: #f1f5f9;
    color: #64748b;
    border: none;
    padding: 12px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    font-size: 0.9rem;
    transition: background 0.2s;
  }
  .sr-confirm-btn-no:hover {
    background: #e2e8f0;
  }
  .sr-confirm-btn-yes {
    background: var(--sr-primary);
    color: #ffffff;
    border: none;
    padding: 12px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    font-size: 0.9rem;
    transition: background 0.2s;
  }
  .sr-confirm-btn-yes:hover {
    background: #4338ca;
  }
</style>

<!-- Custom Confirm Modal -->
<div class="sr-modal-overlay" id="confirmModalOverlay">
  <div class="sr-confirm-modal">
    <div class="sr-confirm-title">
      <i class="fa-solid fa-triangle-exclamation" style="color:var(--sr-primary);margin-right:8px;"></i>Modify Order?
    </div>
    <div class="sr-confirm-body" id="confirmModalBody">
      An order has already been placed for this retailer today. Do you want to modify this order?
    </div>
    <div class="sr-confirm-actions">
      <button class="sr-confirm-btn-no" id="confirmModalNoBtn">No, Cancel</button>
      <button class="sr-confirm-btn-yes" id="confirmModalYesBtn">Yes, Modify</button>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     RETAILER DETAIL FULLSCREEN POPUP
══════════════════════════════════════════════════════════════ -->
<!-- ══════════════════════════════════════════════════════════════
     RETAILER DETAIL FULLSCREEN POPUP
══════════════════════════════════════════════════════════════ -->
<div class="sr-retailer-popup-v2" id="retailerPopup">
  <!-- Topbar -->
  <div class="sr-popup-header-v2">
    <button class="sr-popup-back-btn-v2" id="retPopupBack">
      <i class="fa-solid fa-chevron-left"></i>
    </button>
    <div class="sr-popup-header-title-v2" id="retPopupHeaderTitle">Products</div>
    
    <!-- Search Input (Hidden by default) -->
    <div class="hidden flex-1 mx-2" id="retPopupSearchContainer">
      <input type="text" id="retPopupSearchInput" placeholder="প্রোডাক্ট খুঁজুন..." class="w-full bg-slate-100 border border-slate-200 text-slate-800 text-xs rounded-xl px-3 py-1.5 focus:outline-none focus:border-blue-500 font-sans" oninput="filterProductsTable()">
    </div>

    <button class="sr-popup-search-btn-v2" id="retPopupSearchBtn" onclick="toggleSearchInput()">
      <i class="fa-solid fa-magnifying-glass"></i>
    </button>
  </div>

  <!-- Content Area -->
  <div class="sr-popup-content-v2">



    <!-- View Switcher (List vs 2-Column Grid) -->
    <div class="flex items-center justify-between mb-3 px-0.5 select-none">
      <div class="text-xs font-bold text-slate-500 flex items-center gap-1.5">
        <i class="fa-solid fa-boxes-stacked text-blue-600 text-xs"></i>
        <span id="prodCountBadge">প্রোডাক্ট</span>
      </div>
      <div class="inline-flex rounded-xl border border-slate-200 bg-slate-100 p-1 shadow-2xs">
        <button type="button" id="srViewListBtn" onclick="setShopViewMode('list')" class="flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-lg transition-all shadow-xs bg-white text-blue-600 cursor-pointer">
          <i class="fa-solid fa-list text-xs"></i> <span>লিস্ট</span>
        </button>
        <button type="button" id="srViewGridBtn" onclick="setShopViewMode('grid')" class="flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-lg transition-all text-slate-600 hover:text-slate-900 cursor-pointer">
          <i class="fa-solid fa-grip text-xs"></i> <span>গ্রিড</span>
        </button>
      </div>
    </div>

    <!-- Products Container -->
    <div id="productsGrid">
      <!-- Populated by JS -->
    </div>
  </div>

  <!-- Bottom Floating Cart Bar -->
  <div class="sr-popup-cart-bar-wrap-v2">
    <div class="sr-popup-cart-bar-v2">
      <div class="sr-cart-badge-container-v2">
        <div class="sr-cart-badge-btn-v2">
          <i class="fa-solid fa-cart-shopping"></i>
          <span class="sr-cart-badge-count-v2" id="cartCountBadge">0</span>
        </div>
      </div>
      <div class="sr-cart-item-thumbs-v2" id="cartItemThumbs">
        <!-- JS inserts thumbnails -->
      </div>
      <button class="sr-cart-checkout-btn-v2" onclick="openRetailerCartSheet(currentRetailer)">
        তালিকা দেখুন
      </button>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     PRODUCT ORDER BOTTOM SHEET
══════════════════════════════════════════════════════════════ -->
<div class="sr-sheet-overlay" id="productSheetOverlay"></div>
<div class="sr-bottom-sheet-v2" id="productSheet">
  <div class="sr-sheet-handle-v2"></div>
  <div class="sr-sheet-header-v2 border-b border-slate-100 pb-3">
    <span class="sr-sheet-title-v2 font-black text-slate-900 text-base">প্রোডাক্ট যোগ করুন</span>
    <button class="sr-sheet-close-v2 w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition" id="productSheetClose"><i class="fa-solid fa-xmark"></i></button>
  </div>
  <div class="sr-sheet-body-v2 space-y-4 pt-2">
    <!-- Image Wrapper -->
    <div class="sr-prod-sheet-img-wrap-v2 rounded-2xl overflow-hidden border border-slate-100 shadow-2xs">
      <div id="productSheetImgWrap"></div>
    </div>
    
    <!-- Product Name & Free Item Button (Red Area) -->
    <div class="flex items-center justify-between gap-2.5">
      <div class="sr-prod-sheet-name-v2 font-sans font-black text-slate-900 text-lg leading-snug truncate" id="productSheetName">—</div>
      <button type="button" id="srProductFreeItemBtn" onclick="openSrFreeItemModal()" class="shrink-0 text-xs font-bold text-amber-800 bg-amber-50 hover:bg-amber-100 active:scale-95 border border-amber-300 px-3 py-1.5 rounded-xl flex items-center gap-1.5 shadow-2xs transition-all select-none" title="ফ্রি আইটেম যুক্ত করুন">
        <i class="fa-solid fa-gift text-amber-600 text-sm"></i>
        <span>ফ্রি আইটেম</span>
        <span id="srProductFreeItemBadge" class="hidden ml-0.5 px-1.5 py-0.2 bg-amber-500 text-white rounded-full text-[10px] font-black leading-tight">0</span>
      </button>
    </div>
    
    <!-- Product Info Table (Excel Style) -->
    <div class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white text-xs select-none shadow-2xs">
      <table class="w-full text-left border-collapse">
        <tbody class="divide-y divide-slate-100 text-slate-700">
          <tr>
            <td class="p-3 border-r border-slate-100 bg-slate-50/70 font-bold text-slate-500 w-1/3 font-sans">প্যাকেজ টাইপ</td>
            <td class="p-3 text-slate-900 font-bold font-sans" id="productSheetPackageWrap">
              <span id="productSheetPackageInner">বক্স ( <span id="productSheetPcsPerBox">—</span> পিস )</span>
            </td>
          </tr>
          <tr>
            <td class="p-3 border-r border-slate-100 bg-slate-50/70 font-bold text-slate-500 font-sans">প্রতি পিস মূল্য</td>
            <td class="p-3 text-slate-900 font-extrabold font-mono text-xs" id="productSheetPerPiecePriceWrap">
              Tk <span id="productSheetPerPiecePriceVal">0.00</span>
              <span id="productSheetPerPiecePriceFormula" class="text-[10px] text-slate-400 font-sans ml-1"></span>
            </td>
          </tr>
          <tr class="bg-blue-50/70">
            <td class="p-3 border-r border-blue-200/80 bg-blue-100/60 font-black text-blue-900 font-sans text-xs">মোট মূল্য</td>
            <td class="p-2.5 text-blue-950 font-black font-mono text-sm cursor-pointer" onclick="document.getElementById('productSheetBasePriceInput').focus()">
              <div class="inline-flex items-center gap-1.5 bg-white border-2 border-blue-400 rounded-xl px-2.5 py-1 shadow-sm">
                <span class="text-blue-600 font-black">Tk</span>
                <input type="number" id="productSheetBasePriceInput" value="0.00" min="0" step="any" oninput="calcFromTotal()" style="border:none; background:none; font-weight:900; width:100px; outline:none; padding:0; font-family:monospace;" class="text-slate-900 text-sm font-black focus:ring-0">
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <!-- Price setting override controls -->
    <div class="sr-prod-sheet-override-header-v2" style="justify-content: flex-end; min-height: 20px;">
      <span class="sr-prod-override-badge-v2" id="productSheetOcBadge" style="display:none;">Tk 0</span>
    </div>
    
    <!-- Big Middle counter showing Total Value -->
    <div class="sr-prod-total-counter-box-v2 rounded-2xl border-2 border-blue-100 bg-gradient-to-r from-blue-50/50 via-white to-blue-50/50 p-2 shadow-2xs" onclick="if(event.target.tagName !== 'BUTTON') document.getElementById('totalDisplayInput').focus()" style="cursor:text;">
      <button class="sr-prod-total-cnt-btn-v2" onclick="changeTotalAmount(-1)">−</button>
      <div class="sr-prod-total-cnt-value-v2 font-black">
        Tk <input type="number" id="totalDisplayInput" value="0.00" min="0" step="0.01" oninput="calcTotal()" style="border:none; background:none; font-weight:900; width:120px; text-align:center; color:#0f172a; outline:none;" class="font-mono text-lg">
      </div>
      <button class="sr-prod-total-cnt-btn-v2" onclick="changeTotalAmount(1)">+</button>
    </div>
    
    <!-- Box & Piece counters -->
    <div class="sr-prod-qty-counters-grid-v2" id="qtyCountersGrid">
      <!-- Box counter -->
      <div class="sr-prod-qty-counter-v2" id="boxCounterGroup">
        <div class="sr-prod-qty-counter-label-v2 font-sans font-bold text-slate-700">বক্স</div>
        <div class="sr-prod-qty-counter-row-v2 rounded-xl border border-slate-200 bg-white" onclick="if(event.target.tagName !== 'BUTTON') document.getElementById('qtyCartons').focus()" style="cursor:text;">
          <button class="sr-qty-counter-btn-v2" onclick="changeQty('cartons',-1)">−</button>
          <input type="number" id="qtyCartons" value="0" min="0" oninput="calcTotal()" class="sr-qty-counter-input-v2 font-black text-slate-900">
          <button class="sr-qty-counter-btn-v2" onclick="changeQty('cartons',1)">+</button>
        </div>
      </div>
      <!-- Piece counter -->
      <div class="sr-prod-qty-counter-v2" id="pieceCounterGroup">
        <div class="sr-prod-qty-counter-label-v2 font-sans">পিস</div>
        <div class="sr-prod-qty-counter-row-v2" onclick="if(event.target.tagName !== 'BUTTON') document.getElementById('qtyPieces').focus()" style="cursor:text;">
          <button class="sr-qty-counter-btn-v2" onclick="changeQty('pieces',-1)">−</button>
          <input type="number" id="qtyPieces" value="0" min="0" oninput="calcTotal()" class="sr-qty-counter-input-v2">
          <button class="sr-qty-counter-btn-v2" onclick="changeQty('pieces',1)">+</button>
        </div>
      </div>
    </div>
    
    <input type="hidden" id="baseUnitPrice" value="0">
    <input type="hidden" id="unitPrice" value="0">
    <div id="unitPriceDisplay" style="display:none;">৳ 0.00</div>
 
    <!-- Bottom blue Add to Cart CTA -->
    <button class="sr-prod-sheet-add-btn-v2" id="addToCartBtn" onclick="addToCart()">
      <span id="addToCartBtnText">৳ 0 • কার্টে যোগ করুন</span> <i class="fa-solid fa-cart-shopping" style="margin-left: 4px;"></i>
    </button>
  </div>
</div>



<!-- ══════════════════════════════════════════════════════════════
     RETAILER CART SHEET
══════════════════════════════════════════════════════════════ -->
<div class="sr-sheet-overlay" id="retCartOverlay"></div>
<div class="sr-bottom-sheet-v2" id="retCartSheet">
  <div class="sr-sheet-handle-v2"></div>
  <div class="sr-sheet-header-v2" style="border-bottom: 1px solid #f1f5f9;">
    <span class="sr-sheet-title-v2" id="retCartTitle">অর্ডার তালিকা দেখুন</span>
    <button class="sr-sheet-close-v2" onclick="closeSheet('retCartSheet','retCartOverlay')"><i class="fa-solid fa-xmark"></i></button>
  </div>
  <div class="sr-sheet-body-v2" style="padding: 16px;">
    <!-- Items scroll list -->
    <div id="retCartItemsList" style="margin-bottom:14px; max-height: 48vh; overflow-y: auto; display: flex; flex-direction: column; gap: 12px;">
      <!-- Populated by JS -->
    </div>
    
    <!-- Red bordered summary total container -->
    <div class="sr-cart-summary-box-v2">
      <div class="sr-cart-summary-left-v2">
        <div class="sr-cart-summary-oc-v2" id="retCartOcVal" style="display:none;">O/C 0</div>
        <div class="sr-cart-summary-subtotal-v2">Subtotal: <strong id="retCartGrandTotal" style="color:#0f172a;">Tk 0</strong></div>
      </div>
      <button class="sr-cart-summary-confirm-btn-v2" id="retCartConfirmBtn" onclick="confirmRetailerCart()">
        অর্ডার কনফার্ম করুন
      </button>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     CHECKOUT SUCCESS FULLSCREEN OVERLAY
══════════════════════════════════════════════════════════════ -->
<div class="sr-success-overlay-v2" id="successOverlay">
  <div class="sr-success-container-v2" style="font-family: 'Hind Siliguri', sans-serif;">
    <!-- Large success badge -->
    <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-500 border border-emerald-100 flex items-center justify-center text-3xl shadow-sm mb-4">
      <i class="fa-solid fa-circle-check"></i>
    </div>
    
    <h2 class="text-xl font-bold text-slate-900 leading-tight mb-1 text-center font-siliguri">অভিনন্দন!</h2>
    <p class="text-xs text-slate-500 text-center mb-6 leading-tight font-siliguri">আপনার অর্ডার সফলভাবে সম্পন্ন হয়েছে!</p>
    
    <!-- Unified Excel Receipt Card -->
    <div class="w-full bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
      
      <!-- Receipt Header Banner -->
      <div class="bg-slate-50 px-4 py-3 border-b border-slate-200 flex justify-between items-center text-xs">
        <span class="font-bold text-slate-700">অর্ডার ভাউচার</span>
        <span class="font-mono text-slate-400" id="successDateStr"><?= date('d M Y') ?></span>
      </div>

      <!-- Customer Detail Row -->
      <div class="p-3.5 border-b border-slate-200 bg-slate-50/50 flex flex-col gap-1.5 text-xs text-left">
        <div class="flex items-start gap-2">
          <span class="font-bold text-slate-400 min-w-[80px]">গ্রাহক:</span>
          <span class="font-bold text-slate-900 text-sm" id="successCustName">—</span>
        </div>
        <div class="flex items-start gap-2">
          <span class="font-bold text-slate-400 min-w-[80px]">ঠিকানা:</span>
          <span class="text-slate-600 font-medium" id="successAddress">—</span>
        </div>
      </div>

      <!-- Itemized Products List Table -->
      <table class="w-full text-left border-collapse text-xs">
        <thead>
          <tr class="bg-slate-100/80 border-b border-slate-200 text-slate-500 font-bold text-[10px] uppercase select-none">
            <th class="p-2.5 border-r border-slate-200/60 w-[10%] text-center">#</th>
            <th class="p-2.5 border-r border-slate-200/60 w-[60%]">প্রোডাক্ট বিবরণ</th>
            <th class="p-2.5 w-[30%] text-center">পরিমাণ</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700 font-sans" id="successProductList">
          <!-- JS filled -->
        </tbody>
        <tfoot>
          <!-- O/C Row -->
          <tr class="bg-slate-50 border-t border-slate-200" id="successOcRow" style="display:none;">
            <td colspan="2" class="p-2.5 text-right font-bold text-slate-500 border-r border-slate-200">O/C:</td>
            <td class="p-2.5 text-center font-black text-rose-600 font-mono" id="successOcAmount">0</td>
          </tr>
          <!-- Total Row -->
          <tr class="bg-slate-50 border-t border-slate-200 font-bold">
            <td colspan="2" class="p-2.5 text-right text-slate-700 border-r border-slate-200">সর্বমোট (Total):</td>
            <td class="p-2.5 text-center font-black text-slate-950 font-mono text-[13px]" id="successSubtotalVal">Tk 0</td>
          </tr>
        </tfoot>
      </table>
    </div>
    
    <!-- Actions -->
    <div class="w-full flex gap-3">
      <button class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200/60 font-bold text-xs rounded-xl transition active:scale-95 shadow-3xs" id="successHomeBtn">হোমে ফিরে যাই</button>
      <button class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl transition active:scale-95 shadow-sm" id="successStoreBtn">দোকানে ফিরে যাই</button>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     SR PRODUCT FREE ITEM BOTTOM SHEET MODAL
══════════════════════════════════════════════════════════════ -->
<div class="sr-sheet-overlay" id="srFreeItemOverlay" style="z-index: 1049;" onclick="closeSheet('srFreeItemSheet','srFreeItemOverlay')"></div>
<div class="sr-bottom-sheet-v2" id="srFreeItemSheet" style="z-index: 1050; max-height: 85vh; display: flex; flex-direction: column;">
  <div class="sr-sheet-handle-v2"></div>
  
  <!-- Header with Save button at top right -->
  <div class="sr-sheet-header-v2 border-b border-slate-100 pb-3 flex items-center justify-between px-4">
    <div class="min-w-0 flex-1 pr-2">
      <div class="flex items-center gap-1.5 text-amber-700 font-bold text-xs uppercase tracking-wide">
        <i class="fa-solid fa-gift text-amber-500"></i>
        <span>ফ্রি আইটেম নির্বাচন</span>
      </div>
      <div class="text-xs text-slate-500 truncate mt-0.5" id="srFreeItemSubtitle">—</div>
    </div>
    <div class="flex items-center gap-2 shrink-0">
      <button type="button" onclick="saveSrFreeItemsModal()" id="srFreeItemSaveBtn" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-3.5 py-1.5 rounded-xl shadow-xs flex items-center gap-1.5 transition active:scale-95">
        <i class="fa-solid fa-check text-xs"></i>
        <span>সংরক্ষণ</span>
      </button>
      <button type="button" class="sr-sheet-close-v2 w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition" onclick="closeSheet('srFreeItemSheet','srFreeItemOverlay')">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
  </div>

  <!-- Body -->
  <div class="sr-sheet-body-v2 space-y-3 p-4 overflow-y-auto flex-1">
    <!-- Search Box -->
    <div class="relative">
      <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
      <input type="text" id="srFreeItemSearch" placeholder="ফ্রি প্রোডাক্ট খুঁজুন (নাম বা SKU)..." oninput="filterSrFreeItemProducts()" class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 font-medium focus:bg-white focus:border-amber-400 focus:outline-none transition">
    </div>

    <!-- Product list -->
    <div id="srFreeItemsContainer" class="space-y-2 max-h-[50vh] overflow-y-auto pr-0.5">
      <!-- Injected via JS -->
    </div>
  </div>
</div>

<script>
function showConfirmModal(text, onYes) {
  document.getElementById('confirmModalBody').innerText = text;
  const overlay = document.getElementById('confirmModalOverlay');
  overlay.classList.add('open');
  
  const yesBtn = document.getElementById('confirmModalYesBtn');
  const noBtn = document.getElementById('confirmModalNoBtn');
  
  const newYesBtn = yesBtn.cloneNode(true);
  const newNoBtn = noBtn.cloneNode(true);
  
  yesBtn.parentNode.replaceChild(newYesBtn, yesBtn);
  noBtn.parentNode.replaceChild(newNoBtn, noBtn);
  
  newYesBtn.addEventListener('click', () => {
    overlay.classList.remove('open');
    onYes();
  });
  
  newNoBtn.addEventListener('click', () => {
    overlay.classList.remove('open');
  });
}

function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// ══════════════════════════════════════════════════════════════
// FREE ITEMS STATE & MODAL LOGIC (Retailer + Product Linked)
// ══════════════════════════════════════════════════════════════
let currentProductFreeItems = {}; // { [freeProductId]: quantity }
let retailerFreeItemsCache = {}; // { [retailerId]: { [productId]: { [freeProductId]: qty } } }

function updateSrFreeItemBadge() {
  const badge = document.getElementById('srProductFreeItemBadge');
  const btn   = document.getElementById('srProductFreeItemBtn');
  if (!badge) return;
  const count = Object.values(currentProductFreeItems).filter(q => q > 0).length;
  if (count > 0) {
    badge.textContent = count;
    badge.classList.remove('hidden');
    if (btn) {
      btn.classList.add('bg-amber-100', 'border-amber-400', 'text-amber-900');
      btn.classList.remove('bg-amber-50', 'border-amber-300', 'text-amber-800');
    }
  } else {
    badge.textContent = '0';
    badge.classList.add('hidden');
    if (btn) {
      btn.classList.remove('bg-amber-100', 'border-amber-400', 'text-amber-900');
      btn.classList.add('bg-amber-50', 'border-amber-300', 'text-amber-800');
    }
  }
}

function initProductFreeItems(p) {
  currentProductFreeItems = {};
  if (!currentRetailer || !p) {
    updateSrFreeItemBadge();
    return;
  }

  // 1. Check if the cart already has freeItems for this product
  const cart = cartsByRetailer[currentRetailer.id] || [];
  const existing = cart.find(c => c.id === p.id);
  if (existing && existing.freeItems && Object.keys(existing.freeItems).length > 0) {
    currentProductFreeItems = { ...existing.freeItems };
    updateSrFreeItemBadge();
    return;
  }

  // 2. Check local memory cache for this retailer + product
  if (retailerFreeItemsCache[currentRetailer.id] && retailerFreeItemsCache[currentRetailer.id][p.id]) {
    currentProductFreeItems = { ...retailerFreeItemsCache[currentRetailer.id][p.id] };
    updateSrFreeItemBadge();
    return;
  }

  // 3. Otherwise, fetch configured free items from server
  updateSrFreeItemBadge();
  fetch(`${BASE_URL}/sr/api/free-items?retailer_id=${currentRetailer.id}&product_id=${p.id}`)
    .then(r => r.json())
    .then(d => {
      if (d.success && d.free_items && d.free_items.length > 0) {
        if (!retailerFreeItemsCache[currentRetailer.id]) retailerFreeItemsCache[currentRetailer.id] = {};
        if (!retailerFreeItemsCache[currentRetailer.id][p.id]) retailerFreeItemsCache[currentRetailer.id][p.id] = {};
        
        d.free_items.forEach(fi => {
          const fPid = parseInt(fi.free_product_id);
          const fQty = parseInt(fi.quantity) || 0;
          if (fQty > 0) {
            retailerFreeItemsCache[currentRetailer.id][p.id][fPid] = fQty;
          }
        });

        if (currentProduct && currentProduct.id === p.id) {
          currentProductFreeItems = { ...(retailerFreeItemsCache[currentRetailer.id][p.id] || {}) };
          updateSrFreeItemBadge();
        }
      }
    })
    .catch(err => console.error('Error fetching free items:', err));
}

function openSrFreeItemModal() {
  if (!currentRetailer || !currentProduct) {
    showMiniToast('দোকান বা প্রোডাক্ট নির্বাচন করা হয়নি', true);
    return;
  }

  const sub = document.getElementById('srFreeItemSubtitle');
  if (sub) {
    sub.textContent = `${currentRetailer.name} • ${currentProduct.name}`;
  }

  const searchInput = document.getElementById('srFreeItemSearch');
  if (searchInput) searchInput.value = '';

  renderSrFreeItemsList();
  openSheet('srFreeItemSheet', 'srFreeItemOverlay');
}

function renderSrFreeItemsList() {
  const container = document.getElementById('srFreeItemsContainer');
  if (!container) return;
  container.innerHTML = '';

  if (!ALL_PRODUCTS || ALL_PRODUCTS.length === 0) {
    container.innerHTML = `<div class="py-8 text-center text-slate-400 text-xs">কোনো প্রোডাক্ট পাওয়া যায়নি।</div>`;
    return;
  }

  // Sort products: same company products first, then others alphabetically
  const currentCompId = currentProduct ? currentProduct.company_id : null;
  const sorted = [...ALL_PRODUCTS].sort((a, b) => {
    if (a.company_id === currentCompId && b.company_id !== currentCompId) return -1;
    if (a.company_id !== currentCompId && b.company_id === currentCompId) return 1;
    return a.name.localeCompare(b.name);
  });

  sorted.forEach(p => {
    const qty = currentProductFreeItems[p.id] || 0;
    const row = document.createElement('div');
    row.className = "sr-free-item-row flex items-center justify-between p-2.5 bg-white border border-slate-200 rounded-xl hover:border-amber-400 transition shadow-3xs gap-2.5";
    row.dataset.id = p.id;
    row.dataset.name = (p.name || '').toLowerCase();
    row.dataset.sku  = (p.sku  || '').toLowerCase();

    let imgHtml = '';
    if (p.image) {
      imgHtml = `<img src="${BASE_URL}/${escHtml(p.image)}" class="w-10 h-10 object-contain rounded-lg bg-slate-50 border border-slate-200 shrink-0" alt="" onerror="this.outerHTML='<div class=&quot;w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center shrink-0 border border-slate-200 text-slate-400 text-xs&quot;><i class=&quot;fa-solid fa-box&quot;></i></div>'">`;
    } else {
      imgHtml = `<div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center shrink-0 border border-slate-200 text-slate-400 text-xs"><i class="fa-solid fa-box"></i></div>`;
    }

    const ppb = p.pieces_per_box || p.pieces_per_carton || 1;
    const boxLabel = p.box_type || 'বক্স';

    row.innerHTML = `
      <div class="flex items-center gap-2.5 min-w-0 flex-1">
        ${imgHtml}
        <div class="min-w-0 flex-1">
          <div class="font-bold text-xs text-slate-900 truncate" title="${escHtml(p.name)}">${escHtml(p.name)}</div>
          <div class="text-[10px] text-slate-500 font-sans">${escHtml(ppb)} Pcs / ${escHtml(boxLabel)}</div>
          ${p.company_name ? `<span class="inline-block text-[9px] text-amber-800 bg-amber-50 px-1.5 py-0.2 rounded border border-amber-200 font-semibold">${escHtml(p.company_name)}</span>` : ''}
        </div>
      </div>
      <div class="flex items-center gap-1 shrink-0 select-none">
        <button type="button" onclick="changeSrFreeItemQty(${p.id}, -1)" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold flex items-center justify-center transition border border-slate-200 active:scale-95">
          <i class="fa-solid fa-minus text-[10px]"></i>
        </button>
        <input type="number" min="0" value="${qty}" id="sr-free-qty-${p.id}" onchange="setSrFreeItemQty(${p.id}, this.value)" class="w-12 text-center font-mono font-bold text-xs py-1 border border-slate-200 rounded-lg focus:border-amber-500 focus:outline-none bg-slate-50">
        <button type="button" onclick="changeSrFreeItemQty(${p.id}, 1)" class="w-7 h-7 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-bold flex items-center justify-center transition active:scale-95 shadow-3xs">
          <i class="fa-solid fa-plus text-[10px]"></i>
        </button>
      </div>
    `;
    container.appendChild(row);
  });
}

function filterSrFreeItemProducts() {
  const query = (document.getElementById('srFreeItemSearch').value || '').toLowerCase().trim();
  const rows = document.querySelectorAll('.sr-free-item-row');
  rows.forEach(row => {
    const name = row.dataset.name || '';
    const sku  = row.dataset.sku  || '';
    if (!query || name.includes(query) || sku.includes(query)) {
      row.style.display = 'flex';
    } else {
      row.style.display = 'none';
    }
  });
}

function changeSrFreeItemQty(productId, delta) {
  const cur  = parseInt(currentProductFreeItems[productId]) || 0;
  const next = Math.max(0, cur + delta);
  if (next === 0) {
    delete currentProductFreeItems[productId];
  } else {
    currentProductFreeItems[productId] = next;
  }
  const input = document.getElementById(`sr-free-qty-${productId}`);
  if (input) input.value = next;
}

function setSrFreeItemQty(productId, val) {
  const next = Math.max(0, parseInt(val) || 0);
  if (next === 0) {
    delete currentProductFreeItems[productId];
  } else {
    currentProductFreeItems[productId] = next;
  }
  const input = document.getElementById(`sr-free-qty-${productId}`);
  if (input) input.value = next;
}

async function saveSrFreeItemsModal() {
  if (!currentRetailer || !currentProduct) {
    showMiniToast('দোকান বা প্রোডাক্ট নির্বাচন করা হয়নি', true);
    return;
  }

  const items = [];
  Object.keys(currentProductFreeItems).forEach(pid => {
    const q = parseInt(currentProductFreeItems[pid]) || 0;
    if (q > 0) {
      items.push({ free_product_id: parseInt(pid), quantity: q });
    }
  });

  const btn = document.getElementById('srFreeItemSaveBtn');
  const originalHtml = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i><span>সংরক্ষণ...</span>';

  try {
    const res = await fetch(`${BASE_URL}/sr/api/free-items/save`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        retailer_id: currentRetailer.id,
        product_id: currentProduct.id,
        items: items
      })
    });
    const data = await res.json();
    if (data.success) {
      if (!retailerFreeItemsCache[currentRetailer.id]) retailerFreeItemsCache[currentRetailer.id] = {};
      retailerFreeItemsCache[currentRetailer.id][currentProduct.id] = { ...currentProductFreeItems };

      // Also update in cart if product is already in cart
      const cart = cartsByRetailer[currentRetailer.id] || [];
      const existing = cart.find(c => c.id === currentProduct.id);
      if (existing) {
        existing.freeItems = { ...currentProductFreeItems };
        renderRetailerCart();
      }

      updateSrFreeItemBadge();
      closeSheet('srFreeItemSheet', 'srFreeItemOverlay');
      showMiniToast('✓ ফ্রি আইটেম সংরক্ষিত হয়েছে');
    } else {
      alert(data.message || 'ফ্রি আইটেম সংরক্ষণ ব্যর্থ হয়েছে');
    }
  } catch (err) {
    alert('Request failed: ' + (err.message || err));
  } finally {
    btn.disabled = false;
    btn.innerHTML = originalHtml;
  }
}

// ══════════════════════════════════════════════════════════════
// RETAILER CART SHEET & PRODUCT LIST
// ══════════════════════════════════════════════════════════════

function openRetailerCartSheet(ret) {
  currentRetailer = ret;
  if (!cartsByRetailer[ret.id]) {
    cartsByRetailer[ret.id] = [];
  }
  
  document.getElementById('retCartTitle').innerHTML = `<i class="fa-solid fa-cart-shopping" style="color:var(--sr-primary);margin-right:8px;"></i>${escHtml(ret.name)}`;
  
  renderRetailerCart();
  openSheet('retCartSheet', 'retCartOverlay');
}

function renderRetailerCart() {
  const cart = cartsByRetailer[currentRetailer.id] || [];
  const list = document.getElementById('retCartItemsList');
  
  if (cart.length === 0) {
    list.innerHTML = `<div style="text-align:center;padding:24px;color:#94a3b8;">Cart is empty. Select products from the shop to start.</div>`;
    document.getElementById('retCartGrandTotal').textContent = 'Tk 0';
    document.getElementById('retCartOcVal').style.display = 'none';
    return;
  }
  
  let totalVal = 0;
  
  let tableHtml = `
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
      <table class="w-full text-left border-collapse font-sans text-xs">
        <thead>
          <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold text-[10px] uppercase tracking-wider select-none">
            <th class="p-2.5 border-r border-slate-200">প্রোডাক্ট</th>
            <th class="p-2.5 text-center border-r border-slate-200">পরিমাণ</th>
            <th class="p-2.5 text-right border-r border-slate-200">মূল্য</th>
            <th class="p-2.5 text-center">মুছুন</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 text-slate-700">
  `;
  
  cart.forEach((c, i) => {
    totalVal += c.total;
    const pcsPerCarton = c.pcsPerCarton || 12;
    const boxes = Math.floor(c.qty / pcsPerCarton);
    const pcs = c.qty % pcsPerCarton;
    
    const prod = ALL_PRODUCTS.find(p => p.id === c.id);
    const imgHtml = (prod && prod.image)
      ? `<img src="${BASE_URL}/${escHtml(prod.image)}" class="w-7 h-7 rounded-lg object-contain bg-slate-50 border border-slate-200 shrink-0" alt="" loading="lazy" onerror="handleProductImageError(this)">`
      : `<div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 shrink-0 border border-slate-200"><i class="fa-regular fa-image text-[9px]"></i></div>`;
      
    const roundedOc = Math.round(c.oc || 0);
    const ocHtml = Math.abs(roundedOc) >= 1 
      ? `<span class="inline-block text-[9px] font-extrabold px-1 py-0.2 rounded ${roundedOc < 0 ? 'bg-rose-50 text-rose-700 border border-rose-100' : 'bg-emerald-50 text-emerald-700 border border-emerald-100'}">${roundedOc > 0 ? '+' : ''}${roundedOc} O/C</span>` 
      : '';
        
    let freeItemsHtml = '';
    if (c.freeItems) {
      const freeKeys = Object.keys(c.freeItems).filter(k => c.freeItems[k] > 0);
      if (freeKeys.length > 0) {
        freeItemsHtml = '<div class="flex flex-wrap gap-1 mt-0.5">' + freeKeys.map(k => {
          const fp = ALL_PRODUCTS.find(x => x.id == k);
          const fpName = fp ? fp.name : `Product #${k}`;
          return `<span class="inline-flex items-center gap-1 text-[9px] font-bold text-amber-800 bg-amber-50 border border-amber-200 px-1.5 py-0.2 rounded-md"><i class="fa-solid fa-gift text-amber-600 text-[8px]"></i> ফ্রি: ${escHtml(fpName)} (${c.freeItems[k]} P)</span>`;
        }).join('') + '</div>';
      }
    }

    tableHtml += `
      <tr class="hover:bg-slate-50/50 transition">
        <td class="p-2 border-r border-slate-200 font-semibold text-slate-800 flex items-center gap-2">
          ${imgHtml}
          <div class="flex flex-col gap-0.5 min-w-0">
            <span class="truncate">${escHtml(c.name)}</span>
            ${freeItemsHtml}
            ${ocHtml}
          </div>
        </td>
        <td class="p-2 text-center border-r border-slate-200 font-mono text-[10px] text-slate-600">
          <div class="flex justify-center items-center">
            <span class="bg-slate-100 px-2 py-0.5 rounded border border-slate-200/50 font-bold text-xs">${c.qty.toString().padStart(2, '0')} P</span>
          </div>
        </td>
        <td class="p-2 text-right font-bold border-r border-slate-200 text-slate-950 font-mono text-[11px]">
          Tk ${Math.round(c.total)}
        </td>
        <td class="p-2 text-center">
          <button class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-100 flex items-center justify-center transition active:scale-95 mx-auto" onclick="removeCartItem(${i})" title="Remove item">
            <i class="fa-solid fa-trash-can text-xs"></i>
          </button>
        </td>
      </tr>
    `;
  });
  
  tableHtml += `
        </tbody>
      </table>
    </div>
  `;
  
  list.innerHTML = tableHtml;
  
  document.getElementById('retCartGrandTotal').textContent = 'Tk ' + Math.round(totalVal);
  
  const totalOc = cart.reduce((sum, item) => sum + (item.oc || 0), 0);
  const roundedTotalOc = Math.round(totalOc);
  const retCartOcVal = document.getElementById('retCartOcVal');
  if (Math.abs(roundedTotalOc) >= 1) {
    retCartOcVal.style.display = 'block';
    retCartOcVal.textContent = `O/C ${roundedTotalOc > 0 ? '+' : ''}${roundedTotalOc}`;
    retCartOcVal.className = `sr-cart-summary-oc-v2 ${roundedTotalOc < 0 ? 'neg' : 'pos'}`;
  } else {
    retCartOcVal.style.display = 'none';
  }
}

function updateCartItem(index, type, value) {
  const cart = cartsByRetailer[currentRetailer.id];
  const item = cart[index];
  const val = parseFloat(value) || 0;
  
  const pcsPerCarton = item.pcsPerCarton || 12;
  let currentBoxes = Math.floor(item.qty / pcsPerCarton);
  let currentPcs = item.qty % pcsPerCarton;
  
  if (type === 'box') {
    currentBoxes = Math.max(0, parseInt(val));
    item.qty = currentBoxes * pcsPerCarton + currentPcs;
    item.total = item.qty * item.price;
  } else if (type === 'pc') {
    currentPcs = Math.max(0, parseInt(val));
    item.qty = currentBoxes * pcsPerCarton + currentPcs;
    item.total = item.qty * item.price;
  } else if (type === 'total') {
    item.total = Math.max(0, val);
    if (item.qty > 0) item.price = item.total / item.qty;
  }
  
  renderRetailerCart();
  updatePopupCartInfo();
  renderProductsGrid();
}

function removeCartItem(index) {
  cartsByRetailer[currentRetailer.id].splice(index, 1);
  renderRetailerCart();
  updateAllPins();
  updatePopupCartInfo();
  renderProductsGrid();
}

function openProductsForRetailer() {
  closeSheet('retCartSheet', 'retCartOverlay');
  
  const headerTitleEl = document.getElementById('retPopupHeaderTitle');
  if (headerTitleEl) {
    headerTitleEl.textContent = currentRetailer.name;
  }
  // Avatar removed


  renderProductsGrid();
  updatePopupCartInfo();
  document.getElementById('retailerPopup').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function updatePopupCartInfo() {
  const cart = cartsByRetailer[currentRetailer.id] || [];
  const totalQty = cart.reduce((sum, item) => sum + item.qty, 0);
  
  // Update floating cart badge and count
  const badgeCount = document.getElementById('cartCountBadge');
  if (badgeCount) {
    badgeCount.textContent = totalQty;
    badgeCount.style.display = totalQty > 0 ? 'flex' : 'none';
  }

  // Render product thumbnails in floating bar
  const thumbsContainer = document.getElementById('cartItemThumbs');
  if (thumbsContainer) {
    if (cart.length === 0) {
      thumbsContainer.innerHTML = '';
    } else {
      const maxThumbs = 3;
      let thumbsHtml = '';
      cart.slice(0, maxThumbs).forEach(item => {
        const prod = ALL_PRODUCTS.find(p => p.id === item.id);
        if (prod && prod.image) {
          thumbsHtml += `<div class="sr-cart-thumb-img-v2"><img src="${BASE_URL}/${escHtml(prod.image)}" alt="" loading="lazy"></div>`;
        } else {
          thumbsHtml += `<div class="sr-cart-thumb-img-placeholder-v2">📦</div>`;
        }
      });
      
      if (cart.length > maxThumbs) {
        thumbsHtml += `<div class="sr-cart-thumb-more-v2">+${cart.length - maxThumbs}</div>`;
      }
      thumbsContainer.innerHTML = thumbsHtml;
    }
  }
}

function handleProductImageError(img) {
  img.outerHTML = `<i class="fa-regular fa-image text-slate-300 text-xs"></i>`;
}

let productSearchQuery = '';

function toggleSearchInput() {
  const container = document.getElementById('retPopupSearchContainer');
  const title = document.getElementById('retPopupHeaderTitle');
  const btn = document.getElementById('retPopupSearchBtn');
  const input = document.getElementById('retPopupSearchInput');
  
  if (container.classList.contains('hidden')) {
    container.classList.remove('hidden');
    container.classList.add('flex');
    title.classList.add('hidden');
    btn.innerHTML = '<i class="fa-solid fa-xmark text-slate-500"></i>';
    input.focus();
  } else {
    container.classList.add('hidden');
    container.classList.remove('flex');
    title.classList.remove('hidden');
    btn.innerHTML = '<i class="fa-solid fa-magnifying-glass"></i>';
    input.value = '';
    productSearchQuery = '';
    renderProductsGrid();
  }
}

let filterProductsTimeout = null;
function filterProductsTable() {
  clearTimeout(filterProductsTimeout);
  filterProductsTimeout = setTimeout(() => {
    productSearchQuery = document.getElementById('retPopupSearchInput').value.trim().toLowerCase();
    renderProductsGrid();
  }, 150);
}

let shopViewMode = localStorage.getItem('sr_shop_view_mode') || 'list';

function setShopViewMode(mode) {
  shopViewMode = mode;
  try { localStorage.setItem('sr_shop_view_mode', mode); } catch (e) {}
  updateShopViewButtons();
  renderProductsGrid();
}

function updateShopViewButtons() {
  const listBtn = document.getElementById('srViewListBtn');
  const gridBtn = document.getElementById('srViewGridBtn');
  if (!listBtn || !gridBtn) return;

  if (shopViewMode === 'grid') {
    gridBtn.className = "flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-lg transition-all shadow-xs bg-white text-blue-600 cursor-pointer";
    listBtn.className = "flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-lg transition-all text-slate-600 hover:text-slate-900 cursor-pointer";
  } else {
    listBtn.className = "flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-lg transition-all shadow-xs bg-white text-blue-600 cursor-pointer";
    gridBtn.className = "flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-lg transition-all text-slate-600 hover:text-slate-900 cursor-pointer";
  }
}

function renderProductsGrid() {
  const grid = document.getElementById('productsGrid');
  updateShopViewButtons();

  if (!ALL_PRODUCTS || !ALL_PRODUCTS.length) {
    if (shopViewMode === 'grid') {
      grid.innerHTML = `
        <div class="grid grid-cols-2 gap-2.5">
          ${[1,2,3,4].map(() => `
            <div class="bg-white rounded-xl border border-slate-200 p-3 animate-pulse space-y-3">
              <div class="h-4 bg-slate-100 rounded w-1/2"></div>
              <div class="h-16 bg-slate-100 rounded"></div>
              <div class="h-4 bg-slate-100 rounded w-3/4"></div>
              <div class="h-7 bg-slate-100 rounded"></div>
            </div>
          `).join('')}
        </div>
      `;
    } else {
      grid.innerHTML = `
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
          <table class="w-full text-left border-collapse font-sans text-xs">
            <tbody class="divide-y divide-slate-100">
              ${[1,2,3,4,5].map(() => `
                <tr class="sr-skeleton-table-row">
                  <td class="p-3 flex items-center gap-2.5">
                    <div class="sr-skeleton-circle" style="width:32px; height:32px; flex-shrink:0;"></div>
                    <div class="sr-skeleton-line" style="width:65%; height:12px;"></div>
                  </td>
                  <td class="p-3 text-center"><div class="sr-skeleton-line" style="width:35px; height:16px; margin:0 auto; border-radius:4px;"></div></td>
                  <td class="p-3 text-center"><div class="sr-skeleton-line" style="width:50px; height:22px; margin:0 auto; border-radius:6px;"></div></td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        </div>
      `;
    }
    return;
  }

  const filteredProducts = ALL_PRODUCTS.filter(p => {
    if (!productSearchQuery) return true;
    return (p.name || '').toLowerCase().includes(productSearchQuery);
  });

  const countBadge = document.getElementById('prodCountBadge');
  if (countBadge) {
    countBadge.textContent = `${filteredProducts.length} টি প্রোডাক্ট`;
  }

  if (!filteredProducts.length) {
    grid.innerHTML = `<div style="text-align:center;padding:28px;color:#94a3b8;background:#fff;border-radius:12px;border:1px solid #e2e8f0;">কোনো প্রোডাক্ট পাওয়া যায়নি।</div>`;
    return;
  }
  
  const cart = cartsByRetailer[currentRetailer?.id] || [];

  // ── 2-COLUMN GRID VIEW ──────────────────────────────────────
  if (shopViewMode === 'grid') {
    let gridHtml = `<div class="grid grid-cols-2 gap-2.5">`;
    filteredProducts.forEach((p) => {
      const origIdx = ALL_PRODUCTS.findIndex(prod => prod.id === p.id);
      const isInCart = cart.some(item => item.id === p.id);
      const stockQty = parseInt(p.stock || 0);
      const stockHtml = stockQty > 0 
        ? `<span class="inline-block text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200/80">${stockQty} টি</span>`
        : `<span class="inline-block text-[10px] font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded border border-rose-200/80">স্টক নেই</span>`;

      const gridImgHtml = (p && p.image)
        ? `<img src="${BASE_URL}/${escHtml(p.image)}" class="w-full h-full max-h-28 object-contain mx-auto transition-transform group-hover:scale-105" alt="${escHtml(p.name)}" loading="lazy" onerror="this.outerHTML='<div class=\\'w-full h-full flex items-center justify-center text-slate-300\\'><i class=\\'fa-regular fa-image text-xl\\'></i></div>'">`
        : `<div class="w-full h-full flex items-center justify-center text-slate-300"><i class="fa-regular fa-image text-xl"></i></div>`;

      const btnHtml = isInCart 
        ? `<button class="w-full py-1.5 px-2 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-300 rounded-lg hover:bg-emerald-100 active:scale-95 transition flex items-center justify-center gap-1" onclick="event.stopPropagation(); openProductSheet(${origIdx})"><i class="fa-solid fa-circle-check text-[10px]"></i> যোগ হয়েছে</button>`
        : `<button class="w-full py-1.5 px-2 text-[11px] font-bold text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 active:scale-95 transition flex items-center justify-center gap-1" onclick="event.stopPropagation(); openProductSheet(${origIdx})"><i class="fa-solid fa-plus text-[10px]"></i> যোগ করুন</button>`;

      gridHtml += `
        <div class="group bg-white rounded-xl border border-slate-200 p-2.5 shadow-2xs flex flex-col justify-between hover:border-blue-400 hover:shadow-sm active:scale-[0.98] transition cursor-pointer relative" onclick="openProductSheet(${origIdx})">
          <div class="flex items-center justify-between gap-1 mb-1.5">
            ${stockHtml}
            ${isInCart ? '<span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-2xs shrink-0" title="কার্টে যোগ করা হয়েছে"></span>' : ''}
          </div>
          
          <div class="h-28 w-full bg-slate-50/70 rounded-lg p-1.5 flex items-center justify-center mb-2 overflow-hidden border border-slate-100">
            ${gridImgHtml}
          </div>
          
          <div class="mt-auto space-y-2">
            <div class="text-xs font-bold text-slate-800 sr-line-clamp-2 leading-tight min-h-[28px]" title="${escHtml(p.name)}">
              ${escHtml(p.name)}
            </div>
            <div onclick="event.stopPropagation();">
              ${btnHtml}
            </div>
          </div>
        </div>
      `;
    });
    gridHtml += `</div>`;
    grid.innerHTML = gridHtml;
    return;
  }

  // ── CURRENT LOOK (TABLE LIST VIEW) ──────────────────────────
  let tableHtml = `
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xs">
      <table class="w-full text-left border-collapse font-sans text-xs">
        <thead>
          <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold text-[10px] uppercase tracking-wider select-none">
            <th class="p-3 border-r border-slate-200">প্রোডাক্ট নাম</th>
            <th class="p-3 text-center border-r border-slate-200">স্টক</th>
            <th class="p-3 text-center">অ্যাকশন</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 text-slate-700">
  `;
  
  filteredProducts.forEach((p) => {
    const origIdx = ALL_PRODUCTS.findIndex(prod => prod.id === p.id);
    const isInCart = cart.some(item => item.id === p.id);
    const btnHtml = isInCart 
      ? `<button class="px-2.5 py-1 text-[10px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-200 rounded-md hover:bg-emerald-100 transition" onclick="event.stopPropagation(); openProductSheet(${origIdx})">যোগ হয়েছে</button>`
      : `<button class="px-2.5 py-1 text-[10px] font-bold text-blue-600 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100 transition" onclick="event.stopPropagation(); openProductSheet(${origIdx})">যোগ করুন</button>`;
      
    const stockQty = parseInt(p.stock || 0);
    const stockHtml = stockQty > 0 
      ? `<span class="inline-block text-[9px] font-extrabold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-100">${stockQty} টি</span>`
      : `<span class="inline-block text-[9px] font-extrabold text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded border border-rose-100">স্টক নেই</span>`;

    const imgHtml = (p && p.image)
      ? `<div class="w-11 h-11 rounded-lg bg-slate-50 border border-slate-200/80 shrink-0 flex items-center justify-center p-0.5"><img src="${BASE_URL}/${escHtml(p.image)}" class="max-w-full max-h-full object-contain mx-auto" alt="${escHtml(p.name)}" loading="lazy" onerror="handleProductImageError(this)"></div>`
      : `<div class="w-11 h-11 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 shrink-0 border border-slate-200"><i class="fa-regular fa-image text-xs"></i></div>`;

    tableHtml += `
        <tr class="hover:bg-slate-50/50 transition cursor-pointer" onclick="openProductSheet(${origIdx})">
          <td class="p-3 border-r border-slate-200 font-semibold text-slate-800 flex items-center gap-2.5">
            ${imgHtml}
            <span>${escHtml(p.name)}</span>
          </td>
          <td class="p-3 text-center border-r border-slate-200">
            ${stockHtml}
          </td>
          <td class="p-3 text-center" onclick="event.stopPropagation();">
            ${btnHtml}
          </td>
        </tr>
    `;
  });
  
  tableHtml += `
        </tbody>
      </table>
    </div>
  `;
  
  grid.innerHTML = tableHtml;
}

// ══════════════════════════════════════════════════════════════
// PRODUCT BOTTOM SHEET
// ══════════════════════════════════════════════════════════════
function isPcsProduct(p) {
  if (!p) return false;
  const ppb = parseInt(p.pieces_per_carton || p.pieces_per_box || 12);
  const boxTypeStr = (p.box_type || '').toString().trim().toLowerCase();
  const pcsKeywords = ['pcs', 'pc', 'piece', 'pieces', 'পিস', 'পিছ'];

  return pcsKeywords.includes(boxTypeStr) || (ppb <= 1);
}

function openProductSheet(idx) {
  currentProduct = ALL_PRODUCTS[idx];
  const p = currentProduct;
  initProductFreeItems(p);
  const grad  = gradients[idx % gradients.length];
  const emoji = emojis[idx % emojis.length];

  const ppb = parseInt(p.pieces_per_carton || p.pieces_per_box || 12);
  const isPcs = isPcsProduct(p);

  document.getElementById('productSheetName').textContent = p.name;
  
  const pkgInner = document.getElementById('productSheetPackageInner');
  const boxCounter = document.getElementById('boxCounterGroup');
  const grid = document.getElementById('qtyCountersGrid');

  if (isPcs) {
    if (pkgInner) pkgInner.textContent = 'পিস';
    if (boxCounter) boxCounter.style.display = 'none';
    if (grid) grid.style.gridTemplateColumns = '1fr';
  } else {
    const boxLabel = p.box_type || 'বক্স';
    if (pkgInner) pkgInner.innerHTML = `${escHtml(boxLabel)} ( <span id="productSheetPcsPerBox">${ppb}</span> পিস )`;
    if (boxCounter) boxCounter.style.display = 'flex';
    if (grid) grid.style.gridTemplateColumns = '1fr 1fr';
  }
  
  // Calculate standard selling price per piece based on buying_price & dealer_percentage
  const buyingPrice = parseFloat(p.buying_price || 0);
  const dealerPct = parseFloat(p.dealer_percentage || 0);
  
  let baseProductPrice = parseFloat(p.selling_price || p.price || 0);
  if (buyingPrice > 0) {
    // selling price per piece = (buying_price_per_box * (1 + dealer_percentage/100)) / ppb
    baseProductPrice = (buyingPrice * (1 + dealerPct / 100)) / (isPcs ? 1 : ppb);
  }
  
  // রাউন্ডিং এর কারণে O/C যেন না বাড়ে, তাই Base Price-কে ২ দশমিক পর্যন্ত রাউন্ড করে নিচ্ছি
  baseProductPrice = Math.round(baseProductPrice * 100) / 100;
  
  document.getElementById('baseUnitPrice').value = baseProductPrice;

  // Display initial per piece price in info table
  const perPieceValEl = document.getElementById('productSheetPerPiecePriceVal');
  const perPieceFormulaEl = document.getElementById('productSheetPerPiecePriceFormula');
  if (perPieceValEl) perPieceValEl.textContent = baseProductPrice.toFixed(2);
  if (perPieceFormulaEl && buyingPrice > 0) {
    perPieceFormulaEl.textContent = `(Buy: ৳${buyingPrice.toFixed(2)} + ${dealerPct}%)`;
  } else if (perPieceFormulaEl) {
    perPieceFormulaEl.textContent = '';
  }

  // Carton base price estimation (or piece price if isPcs)
  const defaultDisplayPrice = isPcs ? baseProductPrice : (baseProductPrice * ppb);
  const basePriceInput = document.getElementById('productSheetBasePriceInput');
  if (basePriceInput) basePriceInput.value = '0.00';
  
  // Set the big input default to the display price
  document.getElementById('totalDisplayInput').value = defaultDisplayPrice.toFixed(2);

  const imgWrap = document.getElementById('productSheetImgWrap');
  if (p && p.image) {
    imgWrap.innerHTML = `<img src="${BASE_URL}/${escHtml(p.image)}" class="sr-product-sheet-img-v2" alt="${escHtml(p.name)}" loading="lazy" onerror="this.onerror=null; this.parentNode.innerHTML='<div class=\\'sr-no-img-box-v2 sheet-placeholder\\'><i class=\\'fa-regular fa-image\\'></i><span>No Product Image</span></div>';">`;
  } else {
    imgWrap.innerHTML = `<div class="sr-no-img-box-v2 sheet-placeholder"><i class="fa-regular fa-image"></i><span>No Product Image</span></div>`;
  }
  
  let cartons = 0;
  let pieces = 0;
  let currentPiecePrice = baseProductPrice;
  
  // Pre-fill quantities from cart if item already exists
  const cart = cartsByRetailer[currentRetailer.id] || [];
  const existing = cart.find(c => c.id === p.id);
  
  if (existing) {
    if (isPcs) {
      cartons = 0;
      pieces = existing.qty;
    } else {
      cartons = Math.floor(existing.qty / ppb);
      pieces = existing.qty % ppb;
    }
    currentPiecePrice = existing.price;
    document.getElementById('totalDisplayInput').value = (currentPiecePrice * (isPcs ? 1 : ppb)).toFixed(2);
  }

  document.getElementById('qtyCartons').value = cartons;
  document.getElementById('qtyPieces').value  = pieces;
  document.getElementById('unitPrice').value = currentPiecePrice;
  
  calcTotal();

  openSheet('productSheet','productSheetOverlay');
}

function changeQty(type, delta) {
  const el = document.getElementById(type === 'cartons' ? 'qtyCartons' : 'qtyPieces');
  el.value = Math.max(0, parseInt(el.value || 0) + delta);
  calcTotal();
}

function changeTotalAmount(amount) {
  const input = document.getElementById('totalDisplayInput');
  let currentBoxPrice = parseFloat(input.value) || 0;
  input.value = Math.max(0, currentBoxPrice + amount).toFixed(2);
  calcTotal();
}

function updateOcDisplay(totalPcs, actualTotal) {
  const basePrice = parseFloat(document.getElementById('baseUnitPrice').value) || 0;
  const expectedTotal = totalPcs * basePrice;
  const oc = actualTotal - expectedTotal;
  
  const badge = document.getElementById('productSheetOcBadge');
  if (Math.abs(oc) < 0.001 || totalPcs === 0) {
    badge.style.display = 'none';
  } else {
    badge.style.display = 'inline-block';
    badge.textContent = `Tk ${oc > 0 ? '+' : ''}${oc.toFixed(2)}`;
    badge.className = `sr-prod-override-badge-v2 ${oc < 0 ? 'neg' : 'pos'}`;
  }
}

function calcTotal() {
  const p = currentProduct;
  const ppb = parseInt(p?.pieces_per_carton || p?.pieces_per_box || 12);
  const isPcs = isPcsProduct(p);

  const cartons = isPcs ? 0 : (parseInt(document.getElementById('qtyCartons').value) || 0);
  const pieces  = parseInt(document.getElementById('qtyPieces').value)  || 0;
  
  const currentBoxPrice = parseFloat(document.getElementById('totalDisplayInput').value) || 0;
  const pcsPerCarton = isPcs ? 1 : (ppb > 0 ? ppb : 1);
  const currentPiecePrice = isPcs ? currentBoxPrice : (pcsPerCarton > 0 ? (currentBoxPrice / pcsPerCarton) : currentBoxPrice);
  
  const totalPcs = cartons * pcsPerCarton + pieces;
  const actualTotal = totalPcs * currentPiecePrice;
  
  document.getElementById('unitPrice').value = currentPiecePrice;
  
  // Update Per Piece Price value dynamically in table
  const perPieceValEl = document.getElementById('productSheetPerPiecePriceVal');
  if (perPieceValEl) {
    perPieceValEl.textContent = currentPiecePrice.toFixed(2);
  }
  
  // update মোট মূল্য
  const basePriceInput = document.getElementById('productSheetBasePriceInput');
  if (basePriceInput && document.activeElement !== basePriceInput) {
    basePriceInput.value = actualTotal.toFixed(2);
  }
  
  const btnText = document.getElementById('addToCartBtnText');
  if (btnText) {
    btnText.textContent = `Tk ${Math.round(actualTotal)} • কার্টে যোগ করুন`;
  }
  updateOcDisplay(totalPcs, actualTotal);
}

function calcFromTotal() {
  const p = currentProduct;
  const ppb = parseInt(p?.pieces_per_carton || p?.pieces_per_box || 12);
  const isPcs = isPcsProduct(p);

  const cartons = isPcs ? 0 : (parseInt(document.getElementById('qtyCartons').value) || 0);
  const pieces  = parseInt(document.getElementById('qtyPieces').value)  || 0;
  
  const pcsPerCarton = isPcs ? 1 : (ppb > 0 ? ppb : 1);
  const totalPcs = cartons * pcsPerCarton + pieces;

  if (totalPcs > 0) {
    const newTotal = parseFloat(document.getElementById('productSheetBasePriceInput').value) || 0;
    const currentPiecePrice = newTotal / totalPcs;
    const currentBoxPrice = isPcs ? currentPiecePrice : (currentPiecePrice * pcsPerCarton);
    
    document.getElementById('totalDisplayInput').value = currentBoxPrice.toFixed(2);
    document.getElementById('unitPrice').value = currentPiecePrice;
    
    // Update Per Piece Price value dynamically in table (including decimal places)
    const perPieceValEl = document.getElementById('productSheetPerPiecePriceVal');
    if (perPieceValEl) {
      perPieceValEl.textContent = currentPiecePrice.toFixed(2);
    }
    
    const btnText = document.getElementById('addToCartBtnText');
    if (btnText) {
      btnText.textContent = `Tk ${Math.round(newTotal)} • কার্টে যোগ করুন`;
    }
    updateOcDisplay(totalPcs, newTotal);
  }
}

function addToCart() {
  const p = currentProduct;
  const ppb = parseInt(p?.pieces_per_carton || p?.pieces_per_box || 12);
  const isPcs = isPcsProduct(p);

  const cartons = isPcs ? 0 : (parseInt(document.getElementById('qtyCartons').value) || 0);
  const pieces  = parseInt(document.getElementById('qtyPieces').value)  || 0;
  
  const currentBoxPrice = parseFloat(document.getElementById('totalDisplayInput').value) || 0;
  const pcsPerCarton = isPcs ? 1 : (ppb > 0 ? ppb : 1);
  const currentPiecePrice = isPcs ? currentBoxPrice : (pcsPerCarton > 0 ? (currentBoxPrice / pcsPerCarton) : currentBoxPrice);
  
  const totalPcs = cartons * pcsPerCarton + pieces;

  if (totalPcs <= 0) { shakeElement('addToCartBtn'); return; }

  const actualTotal = totalPcs * currentPiecePrice;
  const basePrice = parseFloat(document.getElementById('baseUnitPrice').value) || 0;
  const oc = actualTotal - (totalPcs * basePrice);
  
  const cart = cartsByRetailer[currentRetailer.id];
  
  const existing = cart.find(c => c.id === p.id);
  if (existing) {
    existing.qty   = totalPcs;
    existing.total = actualTotal;
    existing.price = currentPiecePrice;
    existing.oc    = oc;
    existing.pcsPerCarton = pcsPerCarton;
    existing.freeItems = { ...currentProductFreeItems };
  } else {
    cart.push({ id: p.id, name: p.name, qty: totalPcs, price: currentPiecePrice, total: actualTotal, pcsPerCarton, oc, freeItems: { ...currentProductFreeItems } });
  }

  closeSheet('productSheet','productSheetOverlay');
  
  updateAllPins(); // Ensure pin turns yellow
  updatePopupCartInfo(); // Update the bottom bar in the popup
  renderProductsGrid(); // Update button states in the products grid
  
  showMiniToast(`✓ ${p.name} added to cart`);
}

// ── Checkout / Confirm Order ──────────────────────────────────
function confirmRetailerCart() {
  const cart = cartsByRetailer[currentRetailer.id] || [];
  if (!cart.length) {
    shakeElement('retCartConfirmBtn');
    showMiniToast('Cart is empty!');
    return; 
  }
  
  const notes = '';

  const form = document.createElement('form');
  form.method = 'POST';
  form.action = `${BASE_URL}/sr/orders/store`;

  const csrf = document.querySelector('meta[name="csrf"]');
  if (csrf) addInput(form, '_csrf', csrf.content);

  addInput(form, 'retailer_id', currentRetailer.id);
  addInput(form, 'notes', notes);
  addInput(form, 'ajax', '1');
  
  const allFreeItemsPayload = [];
  cart.forEach((c, i) => {
    addInput(form, `product_id[${i}]`, c.id);
    addInput(form, `quantity[${i}]`, c.qty);
    addInput(form, `unit_price[${i}]`, c.price);

    if (c.freeItems && typeof c.freeItems === 'object') {
      Object.keys(c.freeItems).forEach(freePid => {
        const fQty = parseInt(c.freeItems[freePid]) || 0;
        if (fQty > 0) {
          allFreeItemsPayload.push({
            product_id: c.id,
            free_product_id: parseInt(freePid),
            quantity: fQty
          });
        }
      });
    }
  });

  if (allFreeItemsPayload.length > 0) {
    addInput(form, 'free_items', JSON.stringify(allFreeItemsPayload));
  }

  isSubmitting = true;
  const confirmBtn = document.getElementById('retCartConfirmBtn');
  const originalBtnHtml = confirmBtn.innerHTML;
  confirmBtn.disabled = true;
  confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Placing Order...';

  // Show frosted glass loading overlay
  SRLoader.showOverlay('আপনার অর্ডার সম্পন্ন করা হচ্ছে...', 'অনুগ্রহ করে অপেক্ষা করুন...');

  const formData = new FormData(form);

  fetch(`${BASE_URL}/sr/orders/store`, {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(d => {
    isSubmitting = false;
    confirmBtn.disabled = false;
    confirmBtn.innerHTML = originalBtnHtml;
    SRLoader.hideOverlay();

    if (d.success) {
      // 1. Populating the success screen before clearing the cart
      document.getElementById('successCustName').textContent = currentRetailer.name;
      const cleanAddress = (currentRetailer.address && !currentRetailer.address.toLowerCase().includes('imported dummy')) ? currentRetailer.address.trim() : '';
      document.getElementById('successAddress').textContent = cleanAddress || 'ঠিকানা দেওয়া নেই';
      document.getElementById('successDateStr').textContent = new Date().toLocaleDateString('bn-BD', {day: 'numeric', month: 'short', year: 'numeric'});
      
      const prodList = document.getElementById('successProductList');
      let grandTotal = 0;
      
      prodList.innerHTML = cart.map((item, idx) => {
        grandTotal += item.total;
        const pcsPerCarton = item.pcsPerCarton || 12;
        const boxes = Math.floor(item.qty / pcsPerCarton);
        const pcs = item.qty % pcsPerCarton;
        
        let freeHtml = '';
        if (item.freeItems && typeof item.freeItems === 'object') {
          const fList = Object.keys(item.freeItems).filter(k => (parseInt(item.freeItems[k]) || 0) > 0);
          if (fList.length > 0) {
            const tags = fList.map(k => {
              const fp = allProductsList.find(x => String(x.id) === String(k));
              const fName = fp ? fp.name : `আইটেম #${k}`;
              return `<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-200 text-[10px] font-semibold"><i class="fa-solid fa-gift text-amber-600 text-[9px]"></i> ${escHtml(fName)}: <b>${item.freeItems[k]}</b>টি</span>`;
            }).join(' ');
            freeHtml = `<div class="mt-1 flex flex-wrap gap-1">${tags}</div>`;
          }
        }

        return `
        <tr class="hover:bg-slate-50/50 transition">
          <td class="p-2.5 border-r border-slate-200 text-center font-mono font-bold text-slate-500">${idx + 1}</td>
          <td class="p-2.5 border-r border-slate-200 font-semibold text-slate-800">
            <div>${escHtml(item.name)}</div>
            ${freeHtml}
          </td>
          <td class="p-2.5 text-center font-mono text-[10px] text-slate-600">
            <div class="flex justify-center items-center">
              <span class="bg-slate-100 px-2 py-0.5 rounded border border-slate-200/50 font-bold text-xs">${item.qty.toString().padStart(2, '0')} P</span>
            </div>
          </td>
        </tr>`;
      }).join('');
      
      // Total O/C computation
      const totalOc = cart.reduce((sum, item) => sum + (item.oc || 0), 0);
      const successOcRow = document.getElementById('successOcRow');
      if (totalOc !== 0) {
        successOcRow.style.display = 'table-row';
        document.getElementById('successOcAmount').textContent = `${totalOc > 0 ? '+' : ''}${Math.round(totalOc)}`;
      } else {
        successOcRow.style.display = 'none';
      }
      
      document.getElementById('successSubtotalVal').textContent = `Tk ${Math.round(grandTotal)}`;

      // Clear cart for this retailer
      cartsByRetailer[currentRetailer.id] = [];
      
      // Close sheets and popups
      closeSheet('retCartSheet', 'retCartOverlay');
      
      // Open Success Screen overlay
      document.getElementById('successOverlay').classList.add('open');
      triggerDualCannonShower();

      // Play success notification sound
      try {
        const audio = new Audio(`${BASE_URL}/public/assets/dragon-studio-notification-sound-effect-372475.mp3.mpeg`);
        audio.play().catch(e => console.log('Audio playback blocked or failed:', e));
      } catch (err) {
        console.error('Audio error:', err);
      }
      
      if (currentRetailer) currentRetailer.has_order_today = true;
      // Update map pins (so yellow cart indicator is removed and it's marked as ordered)
      updateAllPins();
    } else {
      showMiniToast('❌ ' + (d.message || 'Failed to place order'), true);
    }
  })
  .catch(err => {
    isSubmitting = false;
    confirmBtn.disabled = false;
    confirmBtn.innerHTML = originalBtnHtml;
    SRLoader.hideOverlay();
    showMiniToast('❌ Network error', true);
    console.error(err);
  });
}

function addInput(form, name, value) {
  const el = document.createElement('input');
  el.type  = 'hidden';
  el.name  = name;
  el.value = value;
  form.appendChild(el);
}


// ══════════════════════════════════════════════════════════════
// SHEET HELPERS
// ══════════════════════════════════════════════════════════════
function openSheet(sheetId, overlayId) {
  document.getElementById(overlayId).classList.add('open');
  document.getElementById(sheetId).classList.add('open');
}
function closeSheet(sheetId, overlayId) {
  document.getElementById(overlayId).classList.remove('open');
  document.getElementById(sheetId).classList.remove('open');
}

function shakeElement(id) {
  const el = document.getElementById(id);
  el.style.animation = 'none';
  el.offsetHeight;
  el.style.animation = 'shake 0.3s ease';
  setTimeout(() => el.style.animation = '', 400);
}

function showMiniToast(msg, isError = false) {
  const t = document.createElement('div');
  t.className = 'sr-flash sr-flash-' + (isError ? 'error' : 'success');
  t.style.cssText = 'position:fixed;top:20px;left:50%;transform:translateX(-50%);z-index:2000;transition:opacity 0.4s;';
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(() => { t.style.opacity='0'; setTimeout(()=>t.remove(),400); }, 2500);
}
function closeRetailerPopup() {
  const popup = document.getElementById('retailerPopup');
  if (popup && popup.classList.contains('open')) {
    popup.classList.remove('open');
    document.body.style.overflow = '';
  }
}

// ══════════════════════════════════════════════════════════════
// BROWSER & HARDWARE BACK BUTTON (POPSTATE) INTEGRATION
// ══════════════════════════════════════════════════════════════
function closeTopModalOrSheetSilently() {
  // 1. Check Product Sheet
  const productSheet = document.getElementById('productSheet');
  if (productSheet && productSheet.classList.contains('open')) {
    closeSheet('productSheet', 'productSheetOverlay');
    return true;
  }
  // 2. Check Retailer Cart Sheet
  const retCartSheet = document.getElementById('retCartSheet');
  if (retCartSheet && retCartSheet.classList.contains('open')) {
    closeSheet('retCartSheet', 'retCartOverlay');
    return true;
  }
  // 3. Check Add Retailer Sheet
  const addRetSheet = document.getElementById('addRetSheet');
  if (addRetSheet && addRetSheet.classList.contains('open')) {
    closeSheet('addRetSheet', 'addRetOverlay');
    return true;
  }
  // 4. Check Retailer Popup
  const retailerPopup = document.getElementById('retailerPopup');
  if (retailerPopup && retailerPopup.classList.contains('open')) {
    closeRetailerPopup();
    return true;
  }
  return false;
}

window.addEventListener('popstate', (e) => {
  closeTopModalOrSheetSilently();
});

function pushModalState(modalName) {
  history.pushState({ modal: modalName }, '');
}

function dismissModalWithHistory() {
  if (history.state && history.state.modal) {
    history.back();
  } else {
    closeTopModalOrSheetSilently();
  }
}

// Wrap openSheet & openRetailerPopup to pushState
const originalOpenSheet = openSheet;
openSheet = function(sheetId, overlayId) {
  originalOpenSheet(sheetId, overlayId);
  pushModalState(sheetId);
};

const originalOpenProductsForRetailer = openProductsForRetailer;
openProductsForRetailer = function() {
  originalOpenProductsForRetailer();
  pushModalState('retailerPopup');
};

document.getElementById('retPopupBack').addEventListener('click', () => {
  dismissModalWithHistory();
});
document.getElementById('productSheetClose').addEventListener('click', () => dismissModalWithHistory());
document.getElementById('productSheetOverlay').addEventListener('click', () => dismissModalWithHistory());

document.getElementById('successHomeBtn').addEventListener('click', () => {
  location.reload();
});

document.getElementById('successStoreBtn').addEventListener('click', () => {
  document.getElementById('successOverlay').classList.remove('open');
  renderProductsGrid();
  updatePopupCartInfo();
  dismissModalWithHistory();
});
function triggerDualCannonShower() {
  if (typeof confetti === 'undefined') return;
  const end = Date.now() + (3.5 * 1000);
  
  (function frame() {
    confetti({
      particleCount: 3,
      angle: 60,
      spread: 55,
      origin: { x: 0, y: 0.8 },
      zIndex: 9999
    });
    confetti({
      particleCount: 3,
      angle: 120,
      spread: 55,
      origin: { x: 1, y: 0.8 },
      zIndex: 9999
    });

    if (Date.now() < end) {
      requestAnimationFrame(frame);
    }
  }());
}
</script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
