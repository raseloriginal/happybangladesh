<?php $isEdit = !empty($item); $pageTitle = $isEdit ? 'Edit Dealer' : 'Add Dealer'; ?>
<div class="page-header">
  <div><h1 class="page-title"><?= $pageTitle ?></h1><div class="breadcrumb"><a href="<?= url('admin/dealers') ?>">Dealers</a> &rsaquo; <?= $pageTitle ?></div></div>
  <a href="<?= url('admin/dealers') ?>" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>

<div class="card max-w-3xl">
  <div class="card-header"><h2 class="card-title"><?= $pageTitle ?></h2></div>
  <div class="card-body">
    <form method="POST" action="<?= $isEdit ? url('admin/dealers/update/'.$item['id']) : url('admin/dealers/store') ?>" data-validate>
      <?= Helpers::csrfField() ?>
      <div class="grid grid-cols-2 gap-4">
        
        <div class="form-group col-span-2">
          <label class="form-label">Warehouse (Area) <span class="required">*</span></label>
          <select name="warehouse_id" required class="form-input">
            <option value="">— Select Warehouse —</option>
            <?php foreach ($warehouses as $wh): ?>
              <option value="<?= $wh['id'] ?>" <?= (($item['warehouse_id'] ?? '') == $wh['id'] ? 'selected' : '') ?>><?= h($wh['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group col-span-2">
          <label class="form-label">Dealer Name <span class="required">*</span></label>
          <input type="text" name="name" required class="form-input" value="<?= h($item['name'] ?? '') ?>">
        </div>
        <div class="form-group col-span-2">
          <label class="form-label">Business Name</label>
          <input type="text" name="business_name" class="form-input" value="<?= h($item['business_name'] ?? '') ?>">
        </div>
        <div class="form-group col-span-2">
          <label class="form-label">Phone</label>
          <input type="text" name="phone" class="form-input" value="<?= h($item['phone'] ?? '') ?>">
        </div>
        <div class="form-group col-span-2">
          <label class="form-label">Address</label>
          <textarea name="address" class="form-input" rows="2"><?= h($item['address'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Trade License</label>
          <input type="text" name="trade_license" class="form-input" value="<?= h($item['trade_license'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Happy Commission (%)</label>
          <input type="number" name="happy_commission" min="0" step="0.01" class="form-input" value="<?= $item['happy_commission'] ?? '0.00' ?>">
        </div>
        <?php if ($isEdit): ?>
        <div class="form-group col-span-2">
          <label class="form-label">Status</label>
          <select name="status" class="form-input">
            <option value="1" <?= ($item['status']==1?'selected':'') ?>>Active</option>
            <option value="0" <?= ($item['status']==0?'selected':'') ?>>Inactive</option>
          </select>
        </div>
        <?php endif; ?>
      </div>

      <!-- Companies & SRs Section -->
      <div class="mt-8 mb-6">
        <div class="flex items-center justify-between mb-3 border-b pb-2">
          <h3 class="text-lg font-semibold text-gray-800">Companies & SR Assignment</h3>
          <button type="button" id="add-company-btn" class="btn btn-secondary text-sm">
            <i class="fa-solid fa-plus"></i> Add Company
          </button>
        </div>
        
        <div id="company-rows-container" class="space-y-3">
          <?php 
          $dcs = $dealer_companies ?? [];
          if (empty($dcs)): 
          ?>
            <!-- Default empty row for new dealer -->
            <div class="company-row flex gap-3 items-end">
              <div class="flex-1">
                <label class="form-label text-xs">Company</label>
                <select name="company_id[]" class="form-input">
                  <option value="">— Select Company —</option>
                  <?php foreach ($companies as $co): ?>
                    <option value="<?= $co['id'] ?>"><?= h($co['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="flex-1">
                <label class="form-label text-xs">Assigned SR</label>
                <select name="sr_id[]" class="form-input">
                  <option value="">— Select SR —</option>
                  <?php foreach ($srs as $sr): ?>
                    <option value="<?= $sr['id'] ?>" data-company="<?= $sr['company_id'] ?? '' ?>"><?= h($sr['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div>
                <button type="button" class="btn btn-secondary text-red-500 remove-row-btn" title="Remove"><i class="fa-solid fa-trash"></i></button>
              </div>
            </div>
          <?php else: ?>
            <!-- Existing rows for edit -->
            <?php foreach ($dcs as $dc): ?>
            <div class="company-row flex gap-3 items-end">
              <div class="flex-1">
                <label class="form-label text-xs">Company</label>
                <select name="company_id[]" class="form-input">
                  <option value="">— Select Company —</option>
                  <?php foreach ($companies as $co): ?>
                    <option value="<?= $co['id'] ?>" <?= $dc['company_id']==$co['id'] ? 'selected' : '' ?>><?= h($co['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="flex-1">
                <label class="form-label text-xs">Assigned SR</label>
                <select name="sr_id[]" class="form-input">
                  <option value="">— Select SR —</option>
                  <?php foreach ($srs as $sr): ?>
                    <option value="<?= $sr['id'] ?>" data-company="<?= $sr['company_id'] ?? '' ?>" <?= $dc['sr_id']==$sr['id'] ? 'selected' : '' ?>><?= h($sr['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div>
                <button type="button" class="btn btn-secondary text-red-500 remove-row-btn" title="Remove"><i class="fa-solid fa-trash"></i></button>
              </div>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <div class="flex gap-3 pt-4 border-t">
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> <?= $isEdit ? 'Update' : 'Create' ?></button>
        <a href="<?= url('admin/dealers') ?>" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

<script>
(() => {
  const container = document.getElementById('company-rows-container');
  const addBtn = document.getElementById('add-company-btn');

  // Template for new row
  const rowHtml = `
    <div class="company-row flex gap-3 items-end mt-3">
      <div class="flex-1">
        <select name="company_id[]" class="form-input">
          <option value="">— Select Company —</option>
          <?php foreach ($companies as $co): ?>
            <option value="<?= $co['id'] ?>"><?= h($co['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="flex-1">
        <select name="sr_id[]" class="form-input">
          <option value="">— Select SR —</option>
          <?php foreach ($srs as $sr): ?>
            <option value="<?= $sr['id'] ?>" data-company="<?= $sr['company_id'] ?? '' ?>"><?= h($sr['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <button type="button" class="btn btn-secondary text-red-500 remove-row-btn" title="Remove"><i class="fa-solid fa-trash"></i></button>
      </div>
    </div>
  `;

  addBtn.addEventListener('click', () => {
    container.insertAdjacentHTML('beforeend', rowHtml);
    attachCompanyFilter(container.lastElementChild);
  });

  function attachCompanyFilter(row) {
    const companySelect = row.querySelector('select[name="company_id[]"]');
    const srSelect = row.querySelector('select[name="sr_id[]"]');
    if (!companySelect || !srSelect) return;

    function filterSRs() {
      const companyId = companySelect.value;
      let hasValidSelection = false;
      Array.from(srSelect.options).forEach(opt => {
        if (opt.value === "") return;
        if (!companyId || opt.dataset.company === companyId) {
          opt.style.display = '';
          if (opt.selected) hasValidSelection = true;
        } else {
          opt.style.display = 'none';
          if (opt.selected) opt.selected = false;
        }
      });
      if (!hasValidSelection && srSelect.value !== "") srSelect.value = "";
    }

    companySelect.addEventListener('change', filterSRs);
    filterSRs();
  }

  document.querySelectorAll('.company-row').forEach(attachCompanyFilter);

  container.addEventListener('click', (e) => {
    const btn = e.target.closest('.remove-row-btn');
    if (btn) {
      if (container.querySelectorAll('.company-row').length > 1) {
        btn.closest('.company-row').remove();
      } else {
        // Clear instead of remove if it's the last one
        const row = btn.closest('.company-row');
        row.querySelectorAll('select').forEach(s => s.value = '');
      }
    }
  });
})();
</script>
