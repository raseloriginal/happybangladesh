<style>
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
    <div class="sr-popup-header-title-v2">Products</div>
    <button class="sr-popup-search-btn-v2">
      <i class="fa-solid fa-magnifying-glass"></i>
    </button>
  </div>

  <!-- Content Area -->
  <div class="sr-popup-content-v2">
    <!-- Retailer Profile Card -->
    <div class="sr-popup-profile-card-v2">
      <div class="sr-popup-profile-avatar-v2">
        <img id="retPopupAvatar" src="" alt="Avatar" onerror="this.src='https://i.pravatar.cc/100?img=12'">
      </div>
      <div class="sr-popup-profile-info-v2">
        <div class="sr-popup-profile-name-v2" id="retPopupName">—</div>
        <div class="sr-popup-profile-shop-v2">
          <i class="fa-solid fa-store" style="font-size:0.75rem; margin-right:4px; color:#64748b;"></i>
          <span id="retPopupShopName">—</span>
        </div>
      </div>
    </div>

    <!-- Category / Company Horizontal Scroll Bar -->
    <div class="sr-popup-categories-wrap-v2" id="popupCategoriesWrap">
      <!-- Populated dynamically based on companies -->
    </div>

    <!-- Section Title & Filters -->
    <div class="sr-popup-section-header-v2">
      <div class="sr-popup-section-title-v2">প্রোডাক্ট সমূহ</div>
    </div>

    <!-- Products Grid -->
    <div class="sr-products-grid-v2" id="productsGrid">
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
  <div class="sr-sheet-header-v2">
    <span class="sr-sheet-title-v2">Add Product</span>
    <button class="sr-sheet-close-v2" id="productSheetClose"><i class="fa-solid fa-xmark"></i></button>
  </div>
  <div class="sr-sheet-body-v2">
    <!-- Image Wrapper -->
    <div class="sr-prod-sheet-img-wrap-v2">
      <div id="productSheetImgWrap"></div>
    </div>
    
    <!-- Product Name & Package Type -->
    <div class="sr-prod-sheet-name-v2" id="productSheetName">—</div>
    <div class="sr-prod-sheet-package-v2">
      প্যাকেজ টাইপ: <span style="color:#2563eb; font-weight:700;">বক্স ( <span id="productSheetPcsPerBox">—</span> পিস )</span>
    </div>
    <div class="sr-prod-sheet-baseprice-v2">
      মোট মূল্য <span style="color:#f43f5e; font-weight:700;" id="productSheetBasePrice">—</span>
    </div>
    
    <!-- Price setting override controls -->
    <div class="sr-prod-sheet-override-header-v2" style="justify-content: flex-end; min-height: 24px;">
      <span class="sr-prod-override-badge-v2" id="productSheetOcBadge" style="display:none;">Tk 0</span>
    </div>
    
    <!-- Big Middle counter showing Total Value -->
    <div class="sr-prod-total-counter-box-v2">
      <button class="sr-prod-total-cnt-btn-v2" onclick="changeTotalAmount(-10)">−</button>
      <div class="sr-prod-total-cnt-value-v2">
        Tk <input type="number" id="totalDisplayInput" value="0" min="0" step="1" oninput="calcTotal()" style="border:none; background:none; font-weight:700; width:100px; text-align:center; color:#0f172a; outline:none;">
      </div>
      <button class="sr-prod-total-cnt-btn-v2" onclick="changeTotalAmount(10)">+</button>
    </div>
    
    <!-- Box & Piece counters -->
    <div class="sr-prod-qty-counters-grid-v2">
      <!-- Box counter -->
      <div class="sr-prod-qty-counter-v2">
        <div class="sr-prod-qty-counter-label-v2">বক্স</div>
        <div class="sr-prod-qty-counter-row-v2">
          <button class="sr-qty-counter-btn-v2" onclick="changeQty('cartons',-1)">−</button>
          <input type="number" id="qtyCartons" value="0" min="0" oninput="calcTotal()" class="sr-qty-counter-input-v2">
          <button class="sr-qty-counter-btn-v2" onclick="changeQty('cartons',1)">+</button>
        </div>
      </div>
      <!-- Piece counter -->
      <div class="sr-prod-qty-counter-v2">
        <div class="sr-prod-qty-counter-label-v2">পিস</div>
        <div class="sr-prod-qty-counter-row-v2">
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
      <span id="addToCartBtnText">Tk 0 • Add Now</span> <i class="fa-solid fa-cart-shopping" style="margin-left: 4px;"></i>
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
  <div class="sr-success-container-v2">
    <div class="sr-success-icon-box-v2">
      <i class="fa-solid fa-circle-check"></i>
    </div>
    
    <div class="sr-success-title-v2">অভিনন্দন!</div>
    <div class="sr-success-subtitle-v2">আপনার অর্ডার সফলভাবে সম্পন্ন হয়েছে!</div>
    
    <!-- Delivery Info -->
    <div class="sr-success-info-card-v2">
      <div class="sr-info-card-header-v2">ডেলিভারি তথ্য:</div>
      <div class="sr-info-row-v2">
        <span class="sr-info-label-v2">গ্রাহক:</span>
        <span class="sr-info-value-v2" id="successCustName">—</span>
      </div>
      <div class="sr-info-row-v2">
        <span class="sr-info-label-v2">ডেলিভারির ঠিকানা:</span>
        <span class="sr-info-value-v2" id="successAddress">—</span>
      </div>
    </div>
    
    <!-- Products List -->
    <div class="sr-success-products-card-v2">
      <div class="sr-success-card-header-v2">Products</div>
      <div class="sr-success-prod-list-v2" id="successProductList">
        <!-- JS filled -->
      </div>
      <div class="sr-success-subtotal-box-v2">
        <div class="sr-subtotal-oc-row-v2" id="successOcRow" style="display:none;">
          <span>O/C</span>
          <span id="successOcAmount">0</span>
        </div>
        <div class="sr-subtotal-row-v2">
          <span>Total:</span>
          <span id="successSubtotalVal">Tk 0</span>
        </div>
      </div>
    </div>
    
    <!-- Actions -->
    <div class="sr-success-actions-v2">
      <button class="sr-btn-home-back-v2" id="successHomeBtn">হোমে ফিরে যাই</button>
      <button class="sr-btn-store-back-v2" id="successStoreBtn">দোকানে ফিরে যাই</button>
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
  list.innerHTML = cart.map((c, i) => {
    totalVal += c.total;
    const pcsPerCarton = c.pcsPerCarton || 12;
    const boxes = Math.floor(c.qty / pcsPerCarton);
    const pcs = c.qty % pcsPerCarton;
    
    const prod = ALL_PRODUCTS.find(p => p.id === c.id);
    const imgHtml = prod && prod.image
      ? `<img src="${BASE_URL}/${escHtml(prod.image)}" class="sr-cart-item-image-v2" alt="">`
      : `<div class="sr-cart-item-image-placeholder-v2">📦</div>`;
      
      // O/C status
      const ocHtml = c.oc !== 0 && c.oc !== undefined ? `<span class="sr-cart-item-oc-badge-v2 ${c.oc < 0 ? 'neg' : 'pos'}">${c.oc > 0 ? '+' : ''}${Math.round(c.oc)} O/C</span>` : '';
        
      return `
      <div class="sr-cart-item-card-v2">
        <div class="sr-cart-item-image-wrap-v2">
          ${imgHtml}
        </div>
        <div class="sr-cart-item-info-v2">
          <div class="sr-cart-item-name-v2">${escHtml(c.name)}</div>
          <div class="sr-cart-item-tags-v2">
            <span class="sr-cart-item-tag-v2"><strong>${boxes.toString().padStart(2, '0')}</strong> Box</span>
            <span class="sr-cart-item-tag-v2"><strong>${pcs.toString().padStart(2, '0')}</strong> Pack</span>
          </div>
          <div class="sr-cart-item-price-row-v2">
            <span class="sr-cart-item-price-v2">Tk ${Math.round(c.total)}</span>
            ${ocHtml}
          </div>
        </div>
        <button class="sr-cart-item-delete-btn-v2" onclick="removeCartItem(${i})" title="Remove item">
          <i class="fa-solid fa-trash-can"></i>
        </button>
      </div>`;
    }).join('');
    
    document.getElementById('retCartGrandTotal').textContent = 'Tk ' + Math.round(totalVal);
    
    // Total O/C display
    const totalOc = cart.reduce((sum, item) => sum + (item.oc || 0), 0);
    const retCartOcVal = document.getElementById('retCartOcVal');
    if (totalOc !== 0) {
      retCartOcVal.style.display = 'block';
      retCartOcVal.textContent = `O/C ${totalOc > 0 ? '+' : ''}${Math.round(totalOc)}`;
      retCartOcVal.className = `sr-cart-summary-oc-v2 ${totalOc < 0 ? 'neg' : 'pos'}`;
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
  
  document.getElementById('retPopupName').textContent  = currentRetailer.name;
  document.getElementById('retPopupShopName').textContent = currentRetailer.name;
  
  // Set profile avatar with a stable index based on retailer ID
  const avatarId = (currentRetailer.id % 70) + 1;
  document.getElementById('retPopupAvatar').src = `https://i.pravatar.cc/100?img=${avatarId}`;

  // Dynamically render categories from ALL_PRODUCTS
  const categories = [...new Set(ALL_PRODUCTS.map(p => p.category_name).filter(Boolean))];
  const catWrap = document.getElementById('popupCategoriesWrap');
  if (catWrap) {
    if (categories.length > 0) {
      catWrap.innerHTML = categories.map((cName, idx) => `
        <div class="sr-popup-category-card-v2 ${idx === 0 ? 'active' : ''}">
          <div class="sr-cat-icon-box-v2">🏷️</div>
          <div class="sr-cat-label-v2">${escHtml(cName)}</div>
        </div>
      `).join('');
    } else {
      catWrap.innerHTML = '';
    }
  }

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
          thumbsHtml += `<div class="sr-cart-thumb-img-v2"><img src="${BASE_URL}/${escHtml(prod.image)}" alt=""></div>`;
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

function renderProductsGrid() {
  const grid = document.getElementById('productsGrid');
  if (!ALL_PRODUCTS.length) {
    grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:24px;color:#94a3b8;">No products available.</div>`;
    return;
  }
  
  const cart = cartsByRetailer[currentRetailer?.id] || [];
  
  grid.innerHTML = ALL_PRODUCTS.map((p, i) => {
    const grad  = gradients[i % gradients.length];
    const emoji = emojis[i % emojis.length];
    const imgHtml = p.image
      ? `<img src="${BASE_URL}/${escHtml(p.image)}" class="sr-product-card-image-v2" alt="${escHtml(p.name)}">`
      : `<div class="sr-product-card-image-placeholder-v2" style="background:${grad};">${emoji}</div>`;

    const isInCart = cart.some(item => item.id === p.id);
    const btnHtml = isInCart 
      ? `<button class="sr-prod-card-btn-v2 added" onclick="event.stopPropagation(); openProductSheet(${i})">যোগ হয়েছে</button>`
      : `<button class="sr-prod-card-btn-v2" onclick="event.stopPropagation(); openProductSheet(${i})">যোগ করুন <i class="fa-solid fa-plus" style="font-size: 0.65rem; margin-left: 2px;"></i></button>`;
      
    const stockQty = parseInt(p.stock || 0);
    const stockHtml = stockQty > 0 
      ? `<span style="font-size: 0.65rem; font-weight: 700; color: #16a34a; background: #dcfce7; padding: 2px 6px; border-radius: 4px;">Stock: ${stockQty}</span>`
      : `<span style="font-size: 0.65rem; font-weight: 700; color: #ef4444; background: #fee2e2; padding: 2px 6px; border-radius: 4px;">Out of Stock</span>`;

    return `
    <div class="sr-product-card-v2" onclick="openProductSheet(${i})">
      <div class="sr-product-card-image-wrap-v2">
        ${imgHtml}
      </div>
      <div class="sr-product-card-info-v2">
        <div class="sr-product-card-name-v2">${escHtml(p.name)}</div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
            <div class="sr-product-card-price-v2" style="margin-bottom:0;">Tk ${parseFloat(p.selling_price || p.price || 0)}</div>
            ${stockHtml}
        </div>
        <div class="sr-product-card-action-v2">
          ${btnHtml}
        </div>
      </div>
    </div>`;
  }).join('');
}

// ══════════════════════════════════════════════════════════════
// PRODUCT BOTTOM SHEET
// ══════════════════════════════════════════════════════════════
function openProductSheet(idx) {
  currentProduct = ALL_PRODUCTS[idx];
  const p = currentProduct;
  const grad  = gradients[idx % gradients.length];
  const emoji = emojis[idx % emojis.length];

  const ppb = parseInt(p.pieces_per_carton || p.pieces_per_box || 12);
  document.getElementById('productSheetName').textContent = p.name;
  document.getElementById('productSheetPcsPerBox').textContent = ppb;
  
  // Pre-fill quantities from cart if item already exists
  const cart = cartsByRetailer[currentRetailer.id] || [];
  const existing = cart.find(c => c.id === p.id);
  
  const baseProductPrice = parseFloat(p.selling_price || p.price || 0);
  document.getElementById('baseUnitPrice').value = baseProductPrice;

  // Carton base price estimation
  const cartonBasePrice = baseProductPrice * ppb;
  document.getElementById('productSheetBasePrice').textContent = `Tk 0`;
  
  // Set the big input default to the carton base price
  document.getElementById('totalDisplayInput').value = cartonBasePrice.toFixed(0);

  const imgWrap = document.getElementById('productSheetImgWrap');
  if (p.image) {
    imgWrap.innerHTML = `<img src="${BASE_URL}/${escHtml(p.image)}" class="sr-product-sheet-img-v2" alt="${escHtml(p.name)}">`;
  } else {
    imgWrap.innerHTML = `<div class="sr-product-sheet-placeholder-v2" style="background:${grad};">${emoji}</div>`;
  }
  
  let cartons = 0;
  let pieces = 0;
  let currentPiecePrice = baseProductPrice;
  
  if (existing) {
    cartons = Math.floor(existing.qty / ppb);
    pieces = existing.qty % ppb;
    currentPiecePrice = existing.price;
    document.getElementById('totalDisplayInput').value = (currentPiecePrice * ppb).toFixed(0);
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
  input.value = Math.max(0, currentBoxPrice + amount).toFixed(0);
  calcTotal();
}

function updateOcDisplay(totalPcs, actualTotal) {
  const basePrice = parseFloat(document.getElementById('baseUnitPrice').value) || 0;
  const expectedTotal = totalPcs * basePrice;
  const oc = actualTotal - expectedTotal;
  
  const badge = document.getElementById('productSheetOcBadge');
  if (Math.round(oc) === 0 || totalPcs === 0) {
    badge.style.display = 'none';
  } else {
    badge.style.display = 'inline-block';
    badge.textContent = `Tk ${oc > 0 ? '+' : ''}${Math.round(oc)}`;
    badge.className = `sr-prod-override-badge-v2 ${oc < 0 ? 'neg' : 'pos'}`;
  }
}

function calcTotal() {
  const cartons = parseInt(document.getElementById('qtyCartons').value) || 0;
  const pieces  = parseInt(document.getElementById('qtyPieces').value)  || 0;
  
  const currentBoxPrice = parseFloat(document.getElementById('totalDisplayInput').value) || 0;
  const pcsPerCarton = currentProduct?.pieces_per_carton || currentProduct?.pieces_per_box || 12;
  const currentPiecePrice = pcsPerCarton > 0 ? (currentBoxPrice / pcsPerCarton) : currentBoxPrice;
  
  const totalPcs = cartons * pcsPerCarton + pieces;
  const actualTotal = totalPcs * currentPiecePrice;
  
  document.getElementById('unitPrice').value = currentPiecePrice;
  
  // update মোট মূল্য
  document.getElementById('productSheetBasePrice').textContent = `Tk ${Math.round(actualTotal)}`;
  
  const btnText = document.getElementById('addToCartBtnText');
  if (btnText) {
    btnText.textContent = `Tk ${Math.round(actualTotal)} • Add Now`;
  }
  updateOcDisplay(totalPcs, actualTotal);
}

function addToCart() {
  const p = currentProduct;
  const cartons = parseInt(document.getElementById('qtyCartons').value) || 0;
  const pieces  = parseInt(document.getElementById('qtyPieces').value)  || 0;
  
  const currentBoxPrice = parseFloat(document.getElementById('totalDisplayInput').value) || 0;
  const pcsPerCarton = p?.pieces_per_carton || p?.pieces_per_box || 12;
  const currentPiecePrice = pcsPerCarton > 0 ? (currentBoxPrice / pcsPerCarton) : currentBoxPrice;
  
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
  } else {
    cart.push({ id: p.id, name: p.name, qty: totalPcs, price: currentPiecePrice, total: actualTotal, pcsPerCarton, oc });
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
  
  cart.forEach((c, i) => {
    addInput(form, `product_id[${i}]`, c.id);
    addInput(form, `quantity[${i}]`, c.qty);
    addInput(form, `unit_price[${i}]`, c.price);
  });

  isSubmitting = true;
  const confirmBtn = document.getElementById('retCartConfirmBtn');
  const originalBtnHtml = confirmBtn.innerHTML;
  confirmBtn.disabled = true;
  confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Placing Order...';

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

    if (d.success) {
      // 1. Populating the success screen before clearing the cart
      document.getElementById('successCustName').textContent = currentRetailer.name;
      document.getElementById('successAddress').textContent = currentRetailer.address || 'Detected Location';
      
      const prodList = document.getElementById('successProductList');
      let grandTotal = 0;
      
      prodList.innerHTML = cart.map((item, idx) => {
        grandTotal += item.total;
        const pcsPerCarton = item.pcsPerCarton || 12;
        const boxes = Math.floor(item.qty / pcsPerCarton);
        const pcs = item.qty % pcsPerCarton;
        
        return `
        <div class="sr-success-prod-item-v2">
          <div class="sr-success-prod-index-v2">${idx + 1}</div>
          <div class="sr-success-prod-details-v2">
            <div class="sr-success-prod-name-v2">${escHtml(item.name)}</div>
            <div class="sr-success-prod-tags-v2">
              <span class="sr-success-tag-v2"><strong>${boxes.toString().padStart(2, '0')}</strong> Box</span>
              <span class="sr-success-tag-v2"><strong>${pcs.toString().padStart(2, '0')}</strong> Pack</span>
            </div>
          </div>
        </div>`;
      }).join('');
      
      // Total O/C computation
      const totalOc = cart.reduce((sum, item) => sum + (item.oc || 0), 0);
      if (totalOc !== 0) {
        document.getElementById('successOcRow').style.display = 'flex';
        document.getElementById('successOcRow').className = `sr-subtotal-oc-row-v2 ${totalOc < 0 ? 'neg' : 'pos'}`;
        document.getElementById('successOcAmount').textContent = `${totalOc > 0 ? '+' : ''}${Math.round(totalOc)}`;
      } else {
        document.getElementById('successOcRow').style.display = 'none';
      }
      
      document.getElementById('successSubtotalVal').textContent = `Tk ${Math.round(grandTotal)}`;

      // Clear cart for this retailer
      cartsByRetailer[currentRetailer.id] = [];
      
      // Close sheets and popups
      closeSheet('retCartSheet', 'retCartOverlay');
      
      // Open Success Screen overlay
      document.getElementById('successOverlay').classList.add('open');

      // Play success notification sound
      try {
        const audio = new Audio(`${BASE_URL}/public/assets/dragon-studio-notification-sound-effect-372475.mp3.mpeg`);
        audio.play().catch(e => console.log('Audio playback blocked or failed:', e));
      } catch (err) {
        console.error('Audio error:', err);
      }
      
      // Update map pins (so yellow cart indicator is removed)
      updateAllPins();
    } else {
      showMiniToast('❌ ' + (d.message || 'Failed to place order'), true);
    }
  })
  .catch(err => {
    isSubmitting = false;
    confirmBtn.disabled = false;
    confirmBtn.innerHTML = originalBtnHtml;
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
  document.getElementById('retPopupBack').addEventListener('click', () => {
    document.getElementById('retailerPopup').classList.remove('open');
    document.body.style.overflow = '';
  });
  document.getElementById('productSheetClose').addEventListener('click', () => closeSheet('productSheet','productSheetOverlay'));
  document.getElementById('productSheetOverlay').addEventListener('click', () => closeSheet('productSheet','productSheetOverlay'));

  document.getElementById('successHomeBtn').addEventListener('click', () => {
    document.getElementById('successOverlay').classList.remove('open');
    document.getElementById('retailerPopup').classList.remove('open');
    document.body.style.overflow = '';
    // If on retailers page, might want to just reload or something, but closing is fine.
  });
  
  document.getElementById('successStoreBtn').addEventListener('click', () => {
    document.getElementById('successOverlay').classList.remove('open');
    renderProductsGrid();
    updatePopupCartInfo();
  });
</script>
