<?php $isEdit = !empty($item); $pageTitle = $isEdit ? 'Edit Company' : 'Add Company'; ?>
<div class="page-header">
  <div><h1 class="page-title"><?= $pageTitle ?></h1><div class="breadcrumb"><a href="<?= url('admin/companies') ?>">Companies</a> &rsaquo; <?= $pageTitle ?></div></div>
  <a href="<?= url('admin/companies') ?>" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>

<div class="card max-w-xl">
  <div class="card-header"><h2 class="card-title"><?= $pageTitle ?></h2></div>
  <div class="card-body">
    <form method="POST" action="<?= $isEdit ? url('admin/companies/update/'.$item['id']) : url('admin/companies/store') ?>" data-validate>
      <?= Helpers::csrfField() ?>
      <div class="form-group">
        <label class="form-label" for="name">Company Name <span class="required">*</span></label>
        <input type="text" id="name" name="name" required class="form-input" value="<?= h($item['name'] ?? '') ?>">
        <p id="name-error" class="form-error"></p>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div class="form-group">
          <label class="form-label">Contact Person</label>
          <input type="text" name="contact" class="form-input" value="<?= h($item['contact'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Phone</label>
          <input type="text" name="phone" class="form-input" value="<?= h($item['phone'] ?? '') ?>">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Address</label>
        <textarea name="address" class="form-input" rows="3"><?= h($item['address'] ?? '') ?></textarea>
      </div>
      <?php if ($isEdit): ?>
      <div class="form-group">
        <label class="form-label">Status</label>
        <select name="status" class="form-input">
          <option value="1" <?= ($item['status']==1?'selected':'') ?>>Active</option>
          <option value="0" <?= ($item['status']==0?'selected':'') ?>>Inactive</option>
        </select>
      </div>
      <?php endif; ?>
      <div class="flex gap-3 pt-2">
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> <?= $isEdit ? 'Update' : 'Create' ?></button>
        <a href="<?= url('admin/companies') ?>" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
