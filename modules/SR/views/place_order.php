<?php $pageTitle = 'Place Order'; ?>
<div class="page-header">
  <div><h1 class="page-title">Place Order</h1><div class="breadcrumb"><a href="<?= url('sr/orders') ?>">Orders</a> &rsaquo; Place Order</div></div>
  <a href="<?= url('sr/orders') ?>" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>

<div class="card">
  <div class="card-header"><h2 class="card-title">New Retailer Order</h2></div>
  <div class="card-body">
    <form method="POST" action="<?= url('sr/orders/store') ?>" id="order-form" data-validate>
      <?= Helpers::csrfField() ?>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
        <div class="form-group">
          <label class="form-label">Company <span class="required">*</span></label>
          <select id="company-select" required class="form-input">
            <option value="">— Select Company —</option>
            <?php foreach ($companies as $c): ?>
              <option value="<?= $c['id'] ?>"><?= h($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Dealer / Retailer <span class="required">*</span></label>
          <select name="dealer_id" id="dealer-select" required class="form-input">
            <option value="">— Select Dealer —</option>
            <?php foreach ($dealers as $d): ?>
              <option value="<?= $d['id'] ?>"><?= h($d['name']) ?> (<?= h($d['phone'] ?? '') ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group md:col-span-2">
          <label class="form-label">Notes</label>
          <textarea name="notes" class="form-input" rows="2" placeholder="Delivery instructions, special remarks…"></textarea>
        </div>
      </div>

      <!-- Order Items -->
      <div class="mb-4">
        <div class="flex items-center justify-between mb-3">
          <h3 class="font-semibold text-gray-700">Order Items</h3>
          <button type="button" id="add-item-btn" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-plus"></i> Add Item
          </button>
        </div>

        <div id="order-items">
          <!-- Template row (shown by default for first item) -->
          <div class="order-item grid grid-cols-12 gap-2 items-end mb-3 p-3 bg-gray-50 rounded-lg border border-gray-100">
            <div class="col-span-5">
              <label class="form-label text-xs">Product</label>
              <select name="product_id[]" required class="form-input text-sm py-2">
                <option value="">— Select Product —</option>
                <?php foreach ($products as $p): ?>
                  <option value="<?= $p['id'] ?>" data-price="<?= $p['price'] ?>" data-company="<?= $p['company_id'] ?>">
                    <?= h($p['name']) ?> (<?= h($p['sku']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-span-2">
              <label class="form-label text-xs">Qty</label>
              <input type="number" name="quantity[]" min="1" required value="1" class="form-input text-sm py-2 item-qty">
            </div>
            <div class="col-span-3">
              <label class="form-label text-xs">Unit Price (৳)</label>
              <input type="number" name="unit_price[]" min="0" step="0.01" required value="0" class="form-input text-sm py-2 item-price">
            </div>
            <div class="col-span-2 pb-1 text-right">
              <label class="form-label text-xs invisible">Remove</label>
              <button type="button" class="btn btn-danger btn-sm remove-item-btn w-full"><i class="fa-solid fa-xmark"></i></button>
            </div>
          </div>
        </div>
      </div>

      <!-- Order total -->
      <div class="flex justify-end mb-5">
        <div class="bg-blue-50 border border-blue-100 rounded-lg px-5 py-3 text-right">
          <div class="text-xs text-gray-500 mb-1">Estimated Total</div>
          <div id="order-total" class="text-2xl font-bold text-blue-700">৳ 0.00</div>
        </div>
      </div>

      <div class="flex gap-3">
        <button type="submit" class="btn btn-primary">
          <i class="fa-solid fa-paper-plane"></i> Submit Order
        </button>
        <a href="<?= url('sr/orders') ?>" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

<template id="item-template">
  <div class="order-item grid grid-cols-12 gap-2 items-end mb-3 p-3 bg-gray-50 rounded-lg border border-gray-100">
    <div class="col-span-5">
      <label class="form-label text-xs">Product</label>
      <select name="product_id[]" required class="form-input text-sm py-2">
        <option value="">— Select Product —</option>
        <?php foreach ($products as $p): ?>
          <option value="<?= $p['id'] ?>" data-price="<?= $p['price'] ?>" data-company="<?= $p['company_id'] ?>">
            <?= h($p['name']) ?> (<?= h($p['sku']) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-span-2">
      <label class="form-label text-xs">Qty</label>
      <input type="number" name="quantity[]" min="1" required value="1" class="form-input text-sm py-2 item-qty">
    </div>
    <div class="col-span-3">
      <label class="form-label text-xs">Unit Price (৳)</label>
      <input type="number" name="unit_price[]" min="0" step="0.01" required value="0" class="form-input text-sm py-2 item-price">
    </div>
    <div class="col-span-2 pb-1 text-right">
      <label class="form-label text-xs invisible">Remove</label>
      <button type="button" class="btn btn-danger btn-sm remove-item-btn w-full"><i class="fa-solid fa-xmark"></i></button>
    </div>
  </div>
</template>

<?php $extraScripts = <<<'JS'
<script>
const addBtn   = document.getElementById('add-item-btn');
const itemsDiv = document.getElementById('order-items');
const template = document.getElementById('item-template');
const totalEl  = document.getElementById('order-total');
const companySelect = document.getElementById('company-select');
const dealerSelect = document.getElementById('dealer-select');
const companyDealerMap = <?= json_encode($companyDealerMap ?? []) ?>;

function filterOptions() {
  const companyId = companySelect.value;
  const allowedDealers = companyId && companyDealerMap[companyId] ? companyDealerMap[companyId].map(String) : [];

  // Filter Dealers
  Array.from(dealerSelect.options).forEach(opt => {
    if (opt.value === "") return;
    if (allowedDealers.includes(String(opt.value))) {
      opt.style.display = '';
    } else {
      opt.style.display = 'none';
      if (opt.selected) dealerSelect.value = "";
    }
  });

  // Filter Products
  document.querySelectorAll('select[name="product_id[]"]').forEach(select => {
    Array.from(select.options).forEach(opt => {
      if (opt.value === "") return;
      if (String(opt.dataset.company || "") === companyId) {
        opt.style.display = '';
      } else {
        opt.style.display = 'none';
        if (opt.selected) {
          opt.selected = false;
          select.value = "";
          const row = select.closest('.order-item');
          if (row) { row.querySelector('.item-price').value = '0'; calcTotal(); }
        }
      }
    });
  });
}

companySelect.addEventListener('change', filterOptions);

function calcTotal() {
  let total = 0;
  document.querySelectorAll('.order-item').forEach(row => {
    const qty   = parseFloat(row.querySelector('.item-qty')?.value || 0);
    const price = parseFloat(row.querySelector('.item-price')?.value || 0);
    total += qty * price;
  });
  totalEl.textContent = '৳ ' + total.toFixed(2);
}

addBtn.addEventListener('click', () => {
  const clone = template.content.cloneNode(true);
  itemsDiv.appendChild(clone);
  attachEvents(itemsDiv.lastElementChild);
  filterOptions();
  calcTotal();
});

function attachEvents(row) {
  row.querySelector('.remove-item-btn')?.addEventListener('click', () => {
    if (document.querySelectorAll('.order-item').length > 1) {
      row.remove(); calcTotal();
    }
  });
  row.querySelector('.item-qty')?.addEventListener('input', calcTotal);
  row.querySelector('.item-price')?.addEventListener('input', calcTotal);
  row.querySelector('select')?.addEventListener('change', function() {
    const price = this.options[this.selectedIndex]?.dataset?.price || 0;
    row.querySelector('.item-price').value = price;
    calcTotal();
  });
}

document.querySelectorAll('.order-item').forEach(attachEvents);
filterOptions(); // initial filter
</script>
JS; ?>
