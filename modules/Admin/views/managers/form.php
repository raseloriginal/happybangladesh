<?php
$isEdit    = !empty($item);
$roleSlug  = $roleSlug ?? 'manager';
$roleLabel = $roleLabel ?? 'Manager';
$pageTitle = ($isEdit ? 'Edit ' : 'Add ') . $roleLabel;
$formUrl   = $isEdit ? url("admin/{$roleSlug}s/update/".$item['id']) : url("admin/{$roleSlug}s/store");
?>
<div class="page-header">
  <div>
    <h1 class="page-title"><?= $pageTitle ?></h1>
    <div class="breadcrumb">
      <a href="<?= url("admin/{$roleSlug}s") ?>"><?= $roleLabel ?>s</a> &rsaquo; <?= $pageTitle ?>
    </div>
  </div>
  <a href="<?= url("admin/{$roleSlug}s") ?>" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>

<div class="card max-w-xl">
  <div class="card-header"><h2 class="card-title"><?= $pageTitle ?></h2></div>
  <div class="card-body">
    <form method="POST" action="<?= $formUrl ?>" data-validate>
      <?= Helpers::csrfField() ?>

      <div class="grid grid-cols-2 gap-4">
        <div class="form-group col-span-2">
          <label class="form-label" for="name">Full Name <span class="required">*</span></label>
          <input type="text" id="name" name="name" required class="form-input"
                 value="<?= h($item['name'] ?? '') ?>" placeholder="Full name">
          <p id="name-error" class="form-error"></p>
        </div>


        <div class="form-group">
          <label class="form-label" for="phone">Phone</label>
          <input type="text" id="phone" name="phone" class="form-input"
                 value="<?= h($item['phone'] ?? '') ?>" placeholder="01700-000000">
        </div>

        <div class="form-group">
          <label class="form-label" for="password">
            Password <?= $isEdit ? '' : '<span class="required">*</span>' ?>
          </label>
          <input type="password" id="password" name="password" <?= $isEdit ? '' : 'required' ?> class="form-input"
                 placeholder="<?= $isEdit ? 'Leave blank to keep current' : 'Min 8 chars' ?>">
          <p id="password-error" class="form-error"></p>
        </div>

        <?php if ($roleSlug !== 'sr'): ?>
        <div class="form-group">
          <label class="form-label" for="warehouse_id">Warehouse</label>
          <select id="warehouse_id" name="warehouse_id" class="form-input">
            <option value="">— No Warehouse —</option>
            <?php foreach ($warehouses as $wh): ?>
              <option value="<?= $wh['id'] ?>"
                <?= (($item['warehouse_id'] ?? '') == $wh['id'] ? 'selected' : '') ?>>
                <?= h($wh['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php else: ?>
        <div class="form-group">
          <label class="form-label" for="company_id">Company</label>
          <select id="company_id" name="company_id" class="form-input" required>
            <option value="">— Select Company —</option>
            <?php foreach ($companies as $comp): ?>
              <option value="<?= $comp['id'] ?>"
                <?= (($item['company_id'] ?? '') == $comp['id'] ? 'selected' : '') ?>>
                <?= h($comp['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>

        <?php if ($isEdit): ?>
        <div class="form-group">
          <label class="form-label" for="status">Status</label>
          <select id="status" name="status" class="form-input">
            <option value="1" <?= ($item['status'] == 1 ? 'selected' : '') ?>>Active</option>
            <option value="0" <?= ($item['status'] == 0 ? 'selected' : '') ?>>Inactive</option>
          </select>
        </div>
        <?php endif; ?>
      </div>

      <div class="flex gap-3 pt-2">
        <button type="submit" class="btn btn-primary">
          <i class="fa-solid <?= $isEdit ? 'fa-floppy-disk' : 'fa-plus' ?>"></i>
          <?= $isEdit ? "Update {$roleLabel}" : "Create {$roleLabel}" ?>
        </button>
        <a href="<?= url("admin/{$roleSlug}s") ?>" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
