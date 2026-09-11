<?php $isEdit = !empty($item); $pageTitle = $isEdit ? 'Edit Warehouse' : 'Add Warehouse'; ?>
<div class="page-header">
  <div>
    <h1 class="page-title"><?= $pageTitle ?></h1>
    <div class="breadcrumb"><a href="<?= url('admin/warehouses') ?>">Warehouses</a> &rsaquo; <?= $pageTitle ?></div>
  </div>
  <a href="<?= url('admin/warehouses') ?>" class="btn btn-secondary">
    <i class="fa-solid fa-arrow-left"></i> Back
  </a>
</div>

<div class="card max-w-xl">
  <div class="card-header"><h2 class="card-title"><?= $pageTitle ?></h2></div>
  <div class="card-body">
    <form method="POST" action="<?= $isEdit ? url('admin/warehouses/update/'.$item['id']) : url('admin/warehouses/store') ?>" data-validate>
      <?= Helpers::csrfField() ?>

      <div class="form-group">
        <label class="form-label" for="name">Name <span class="required">*</span></label>
        <input type="text" id="name" name="name" required class="form-input"
               value="<?= h($item['name'] ?? '') ?>" placeholder="Warehouse name">
        <p id="name-error" class="form-error"></p>
      </div>

      <div class="form-group">
        <label class="form-label" for="location">Location <span class="required">*</span></label>
        <input type="text" id="location" name="location" required class="form-input"
               value="<?= h($item['location'] ?? '') ?>" placeholder="City, District">
        <p id="location-error" class="form-error"></p>
      </div>

      <div class="form-group">
        <label class="form-label" for="phone">Phone</label>
        <input type="text" id="phone" name="phone" class="form-input"
               value="<?= h($item['phone'] ?? '') ?>" placeholder="01700-000000">
      </div>

      <?php if ($isEdit): ?>
      <div class="form-group">
        <label class="form-label" for="status">Status</label>
        <select id="status" name="status" class="form-input">
          <option value="1" <?= ($item['status'] == 1 ? 'selected' : '') ?>>Active</option>
          <option value="0" <?= ($item['status'] == 0 ? 'selected' : '') ?>>Inactive</option>
        </select>
      </div>
      <?php endif; ?>

      <div class="flex gap-3 pt-2">
        <button type="submit" class="btn btn-primary">
          <i class="fa-solid <?= $isEdit ? 'fa-floppy-disk' : 'fa-plus' ?>"></i>
          <?= $isEdit ? 'Update Warehouse' : 'Create Warehouse' ?>
        </button>
        <a href="<?= url('admin/warehouses') ?>" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
