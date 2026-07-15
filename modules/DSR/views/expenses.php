<?php $pageTitle = 'Expenses'; ?>
<div class="page-header">
  <div><h1 class="page-title">My Expenses</h1><div class="breadcrumb">DSR &rsaquo; Expenses</div></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

  <!-- Expenses list -->
  <div class="lg:col-span-2 card">
    <div class="card-header">
      <h2 class="card-title">Expense History (<?= count($items) ?>)</h2>
      <input type="text" placeholder="Search…" data-table-search="exp-table" class="form-input w-36 text-sm py-1.5">
    </div>
    <div class="overflow-x-auto">
      <table class="data-table" id="exp-table">
        <thead><tr><th>#</th><th>Date</th><th>Category</th><th>Amount</th><th>Description</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($items as $i => $e): ?>
          <tr>
            <td class="text-gray-400 text-xs"><?= $i+1 ?></td>
            <td><?= Helpers::date($e['date']) ?></td>
            <td>
              <span class="badge bg-gray-100 text-gray-700"><?= ucfirst($e['category']) ?></span>
            </td>
            <td class="font-semibold"><?= Helpers::money($e['amount']) ?></td>
            <td class="text-gray-500 text-sm max-w-40 truncate"><?= h($e['description'] ?? '—') ?></td>
            <td><?= Helpers::statusBadge($e['status']) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($items)): ?><tr><td colspan="6" class="text-center py-8 text-gray-400">No expenses recorded.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Log expense form -->
  <div class="card">
    <div class="card-header"><h2 class="card-title"><i class="fa-solid fa-receipt text-green-500 mr-2"></i>Log Expense</h2></div>
    <div class="card-body">
      <form method="POST" action="<?= url('dsr/expenses/store') ?>" data-validate>
        <?= Helpers::csrfField() ?>

        <div class="form-group">
          <label class="form-label">Date</label>
          <input type="date" name="date" required class="form-input" value="<?= date('Y-m-d') ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Category <span class="required">*</span></label>
          <select name="category" required class="form-input">
            <option value="fuel">Fuel</option>
            <option value="food">Food</option>
            <option value="toll">Toll</option>
            <option value="repair">Repair</option>
            <option value="other">Other</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Amount (৳) <span class="required">*</span></label>
          <input type="number" name="amount" min="0.01" step="0.01" required class="form-input" placeholder="0.00">
        </div>

        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-input" rows="3" placeholder="Details about this expense…"></textarea>
        </div>

        <button type="submit" class="btn btn-success w-full">
          <i class="fa-solid fa-floppy-disk"></i> Save Expense
        </button>
      </form>
    </div>
  </div>

</div>
