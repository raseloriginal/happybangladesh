<?php $pageTitle = 'Database Sync'; ?>
<div class="page-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
  <div>
    <h1 class="page-title text-2xl font-bold text-slate-800">Database Sync</h1>
    <div class="breadcrumb text-sm text-slate-500 mt-1">Admin &rsaquo; Database Sync & Migration Updates</div>
  </div>
  
  <?php if ($pendingCount > 0): ?>
    <form method="POST" action="<?= url('admin/database-sync/run') ?>">
      <?= Helpers::csrfField() ?>
      <input type="hidden" name="file" value="all">
      <button type="submit" class="btn bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-lg shadow-sm transition flex items-center gap-2" onclick="return confirm('Are you sure you want to sync ALL pending database updates?')">
        <i class="fas fa-sync-alt"></i> Sync All Pending (<?= $pendingCount ?>)
      </button>
    </form>
  <?php endif; ?>
</div>

<!-- Stats Summary Grid -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
  <div class="card p-5 border-l-4 border-amber-500 bg-white shadow-sm rounded-lg flex items-center justify-between">
    <div>
      <div class="text-xs uppercase tracking-wider text-slate-500 font-semibold mb-1">Pending Updates</div>
      <div class="text-2xl font-bold text-slate-800"><?= $pendingCount ?></div>
    </div>
    <div class="w-12 h-12 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
      <i class="fas fa-clock"></i>
    </div>
  </div>

  <div class="card p-5 border-l-4 border-emerald-500 bg-white shadow-sm rounded-lg flex items-center justify-between">
    <div>
      <div class="text-xs uppercase tracking-wider text-slate-500 font-semibold mb-1">Synced Updates</div>
      <div class="text-2xl font-bold text-slate-800"><?= $syncedCount ?></div>
    </div>
    <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
      <i class="fas fa-check-circle"></i>
    </div>
  </div>

  <div class="card p-5 border-l-4 border-rose-500 bg-white shadow-sm rounded-lg flex items-center justify-between">
    <div>
      <div class="text-xs uppercase tracking-wider text-slate-500 font-semibold mb-1">Failed Updates</div>
      <div class="text-2xl font-bold text-slate-800"><?= $failedCount ?></div>
    </div>
    <div class="w-12 h-12 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center text-xl">
      <i class="fas fa-exclamation-triangle"></i>
    </div>
  </div>
</div>

<!-- SQL Updates Files Table -->
<div class="card bg-white shadow-sm rounded-lg overflow-hidden border border-slate-200">
  <div class="card-header bg-slate-50 border-b border-slate-200 px-6 py-4 flex items-center justify-between">
    <h2 class="card-title text-slate-800 font-bold flex items-center gap-2 text-base">
      <i class="fas fa-folder-open text-indigo-600"></i> Update SQL Files (<code>database/updates/</code>)
    </h2>
    <span class="text-xs font-semibold px-2.5 py-1 bg-slate-200 text-slate-700 rounded-full">Total: <?= count($filesList) ?></span>
  </div>

  <div class="overflow-x-auto">
    <table class="w-full text-left border-collapse text-sm">
      <thead>
        <tr class="bg-slate-100/70 border-b border-slate-200 text-slate-600 font-semibold uppercase text-xs">
          <th class="py-3 px-6">SQL File</th>
          <th class="py-3 px-6">Status</th>
          <th class="py-3 px-6">Executed At</th>
          <th class="py-3 px-6">Details / Errors</th>
          <th class="py-3 px-6 text-right">Action</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-200 text-slate-700">
        <?php if (empty($filesList)): ?>
          <tr>
            <td colspan="5" class="py-8 text-center text-slate-400">
              <i class="fas fa-file-code text-3xl mb-2 block text-slate-300"></i>
              No SQL update files found in <code>database/updates/</code> folder.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($filesList as $file): ?>
            <tr class="hover:bg-slate-50/80 transition-colors">
              <td class="py-4 px-6 font-mono font-medium text-slate-800">
                <i class="fas fa-file-alt text-slate-400 mr-2"></i>
                <?= htmlspecialchars($file['filename']) ?>
              </td>
              <td class="py-4 px-6">
                <?php if ($file['status'] === 'success'): ?>
                  <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                    <i class="fas fa-check text-xs"></i> Synced
                  </span>
                <?php elseif ($file['status'] === 'modified'): ?>
                  <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800" title="File content changed since last sync">
                    <i class="fas fa-exclamation-circle text-xs"></i> Modified
                  </span>
                <?php elseif ($file['status'] === 'failed'): ?>
                  <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                    <i class="fas fa-times text-xs"></i> Failed
                  </span>
                <?php else: ?>
                  <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                    <i class="fas fa-clock text-xs"></i> Pending
                  </span>
                <?php endif; ?>
              </td>
              <td class="py-4 px-6 text-slate-500 text-xs font-mono">
                <?= $file['executed_at'] ? date('Y-m-d H:i:s', strtotime($file['executed_at'])) : '<span class="text-slate-300">—</span>' ?>
              </td>
              <td class="py-4 px-6">
                <?php if ($file['error_message']): ?>
                  <div class="bg-rose-50 border border-rose-200 text-rose-700 text-xs p-2.5 rounded font-mono break-all max-w-md">
                    <strong>Error:</strong> <?= htmlspecialchars($file['error_message']) ?>
                  </div>
                <?php else: ?>
                  <span class="text-xs text-slate-400 font-mono"><?= round($file['size'] / 1024, 2) ?> KB</span>
                <?php endif; ?>
              </td>
              <td class="py-4 px-6 text-right flex items-center justify-end gap-2">
                <?php if ($file['status'] !== 'success'): ?>
                  <form method="POST" action="<?= url('admin/database-sync/run') ?>" class="inline-block">
                    <?= Helpers::csrfField() ?>
                    <input type="hidden" name="file" value="<?= htmlspecialchars($file['filename']) ?>">
                    <button type="submit" class="btn btn-sm bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-3 py-1.5 rounded text-xs transition inline-flex items-center gap-1.5" onclick="return confirm('Execute migration for <?= htmlspecialchars($file['filename']) ?>?')">
                      <i class="fas fa-play text-[10px]"></i> Sync
                    </button>
                  </form>
                <?php else: ?>
                  <form method="POST" action="<?= url('admin/database-sync/run') ?>" class="inline-block">
                    <?= Helpers::csrfField() ?>
                    <input type="hidden" name="file" value="force:<?= htmlspecialchars($file['filename']) ?>">
                    <button type="submit" class="btn btn-sm bg-slate-200 hover:bg-indigo-600 hover:text-white text-slate-700 font-medium px-3 py-1.5 rounded text-xs transition inline-flex items-center gap-1.5" onclick="return confirm('Re-execute migration for <?= htmlspecialchars($file['filename']) ?>?')">
                      <i class="fas fa-redo text-[10px]"></i> Re-Sync
                    </button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
