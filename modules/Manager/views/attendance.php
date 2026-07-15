<?php $pageTitle = 'Attendance'; ?>
<div class="page-header">
  <div><h1 class="page-title">Attendance</h1><div class="breadcrumb">Manager &rsaquo; Attendance</div></div>
</div>

<!-- Date filter + Add form -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

  <div class="lg:col-span-2 card">
    <div class="card-header">
      <h2 class="card-title">Attendance for <?= Helpers::date($date) ?></h2>
      <form method="GET" class="flex items-center gap-2">
        <input type="date" name="date" value="<?= h($date) ?>" class="form-input text-sm py-1.5 w-36">
        <button type="submit" class="btn btn-secondary btn-sm"><i class="fa-solid fa-filter"></i></button>
      </form>
    </div>
    <div class="overflow-x-auto">
      <table class="data-table">
        <thead>
          <tr><th>Name</th><th>Role</th><th>Check In</th><th>Check Out</th><th>Status</th></tr>
        </thead>
        <tbody>
          <?php foreach ($items as $a): ?>
          <tr>
            <td class="font-medium"><?= h($a['user_name']) ?></td>
            <td><?= h($a['role_name']) ?></td>
            <td><?= h($a['check_in'] ?? '—') ?></td>
            <td><?= h($a['check_out'] ?? '—') ?></td>
            <td><?= Helpers::statusBadge($a['status']) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($items)): ?><tr><td colspan="5" class="text-center py-6 text-gray-400">No attendance recorded for this date.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Add/Update attendance -->
  <div class="card">
    <div class="card-header"><h2 class="card-title">Mark Attendance</h2></div>
    <div class="card-body">
      <form method="POST" action="<?= url('manager/attendance/store') ?>">
        <?= Helpers::csrfField() ?>
        <input type="hidden" name="date" value="<?= h($date) ?>">

        <div class="form-group">
          <label class="form-label">Employee</label>
          <select name="user_id" required class="form-input">
            <option value="">— Select —</option>
            <?php foreach ($users as $u): ?>
              <option value="<?= $u['id'] ?>"><?= h($u['name']) ?> (<?= h($u['role_slug']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Status</label>
          <select name="status" class="form-input">
            <option value="present">Present</option>
            <option value="absent">Absent</option>
            <option value="late">Late</option>
            <option value="half_day">Half Day</option>
            <option value="holiday">Holiday</option>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-2">
          <div class="form-group">
            <label class="form-label">Check In</label>
            <input type="time" name="check_in" class="form-input">
          </div>
          <div class="form-group">
            <label class="form-label">Check Out</label>
            <input type="time" name="check_out" class="form-input">
          </div>
        </div>

        <button type="submit" class="btn btn-primary w-full"><i class="fa-solid fa-floppy-disk"></i> Save</button>
      </form>
    </div>
  </div>

</div>
