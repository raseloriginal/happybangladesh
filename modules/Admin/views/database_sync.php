<?php $pageTitle = 'Database Sync'; ?>
<div class="page-header">
  <div>
    <h1 class="page-title">Database Sync & Migration</h1>
    <div class="breadcrumb">Admin &rsaquo; Database Sync</div>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
  <!-- Sync Status and Details Card -->
  <div class="card lg:col-span-2">
    <div class="card-header bg-slate-50 border-b border-slate-100 flex items-center justify-between">
      <h2 class="card-title text-slate-800"><i class="fas fa-network-wired mr-2"></i> Schema Status Analysis</h2>
      <?php if (empty($missingTables) && empty($missingColumns)): ?>
        <span class="badge badge-success px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold"><i class="fas fa-check-circle mr-1"></i> Fully Synced</span>
      <?php else: ?>
        <span class="badge badge-warning px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-semibold"><i class="fas fa-exclamation-triangle mr-1"></i> Sync Required</span>
      <?php endif; ?>
    </div>
    <div class="card-body">
      <?php if (empty($missingTables) && empty($missingColumns)): ?>
        <div class="p-6 text-center">
          <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 text-green-600 mb-4">
            <i class="fas fa-check-double text-2xl"></i>
          </div>
          <h3 class="text-lg font-bold text-gray-800">Your Database is Up to Date!</h3>
          <p class="text-sm text-gray-500 mt-1">All tables and columns defined in the schema match your database structure.</p>
        </div>
      <?php else: ?>
        <div class="space-y-4">
          <?php if (!empty($missingTables)): ?>
            <div>
              <h4 class="text-sm font-bold text-red-600 mb-2"><i class="fas fa-table mr-1"></i> Missing Tables (<?= count($missingTables) ?>)</h4>
              <ul class="list-disc pl-5 text-sm text-gray-700 space-y-1">
                <?php foreach ($missingTables as $table): ?>
                  <li><code class="bg-red-50 text-red-700 px-1 py-0.5 rounded"><?= htmlspecialchars($table) ?></code></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <?php if (!empty($missingColumns)): ?>
            <div>
              <h4 class="text-sm font-bold text-amber-600 mb-2"><i class="fas fa-columns mr-1"></i> Missing Columns (<?= count($missingColumns, COUNT_RECURSIVE) - count($missingColumns) ?>)</h4>
              <div class="space-y-2">
                <?php foreach ($missingColumns as $table => $cols): ?>
                  <div class="text-sm">
                    <span class="font-semibold text-gray-800"><?= htmlspecialchars($table) ?></span>: 
                    <?php foreach ($cols as $col): ?>
                      <code class="bg-amber-50 text-amber-700 px-1 py-0.5 rounded mr-1"><?= htmlspecialchars($col) ?></code>
                    <?php endforeach; ?>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Quick Info Card -->
  <div class="card">
    <div class="card-header bg-slate-50 border-b border-slate-100">
      <h2 class="card-title text-slate-800"><i class="fas fa-info-circle mr-2"></i> Safety Guidelines</h2>
    </div>
    <div class="card-body text-sm text-gray-600 space-y-3">
      <p>
        <i class="fas fa-shield-alt text-green-600 mr-1"></i> <strong>Safe Migration:</strong> This process will only add new tables or columns. It will never delete, drop, or rename existing columns/tables, protecting your production data.
      </p>
      <p>
        <i class="fas fa-copy text-blue-600 mr-1"></i> <strong>Backup Recommended:</strong> While safe, it is always recommended to create a database backup before performing any migration.
      </p>
    </div>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  
  <!-- Run Schema Sync -->
  <div class="card">
    <div class="card-header bg-blue-50 border-b border-blue-100">
      <h2 class="card-title text-blue-800"><i class="fas fa-database mr-2"></i> Run Schema Sync</h2>
    </div>
    <div class="card-body flex flex-col justify-between h-full">
      <div>
        <p class="text-sm text-gray-600 mb-4">
          This runs only the missing updates compared against <code>schema.sql</code>.
        </p>
        
        <form method="POST" action="<?= url('admin/database-sync/run') ?>">
          <?= Helpers::csrfField() ?>
          <input type="hidden" name="sync_type" value="schema">
          <input type="hidden" name="proposed_sql" value="<?= htmlspecialchars($proposedSql) ?>">
          
          <div class="bg-gray-800 rounded-md p-3 mb-4 overflow-y-auto" style="max-height: 200px;">
            <?php if (!empty($proposedSql)): ?>
              <pre class="text-xs text-green-400 font-mono whitespace-pre-wrap"><?= htmlspecialchars($proposedSql) ?></pre>
            <?php else: ?>
              <p class="text-xs text-gray-400 italic">No updates pending. Database is fully synced.</p>
            <?php endif; ?>
          </div>
          
          <button type="submit" class="btn btn-primary bg-blue-600 hover:bg-blue-700 w-full" <?= empty($proposedSql) ? 'disabled' : '' ?> onclick="return confirm('Are you sure you want to run the schema sync?')">
            <i class="fas fa-play mr-2"></i> Execute Sync SQL
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Run Custom SQL -->
  <div class="card">
    <div class="card-header bg-purple-50 border-b border-purple-100">
      <h2 class="card-title text-purple-800"><i class="fas fa-terminal mr-2"></i> Run Custom Migration (SQL)</h2>
    </div>
    <div class="card-body flex flex-col justify-between h-full">
      <div>
        <p class="text-sm text-gray-600 mb-4">
          Paste your <code>ALTER TABLE</code> or any other SQL migration commands here to apply custom changes.
        </p>
        
        <form method="POST" action="<?= url('admin/database-sync/run') ?>">
          <?= Helpers::csrfField() ?>
          <input type="hidden" name="sync_type" value="custom">
          
          <div class="form-group mb-4">
            <textarea name="custom_sql" class="form-input font-mono text-sm w-full p-2 border rounded" rows="6" placeholder="ALTER TABLE products ADD COLUMN ... ;" required></textarea>
          </div>
          
          <button type="submit" class="btn btn-primary bg-purple-600 hover:bg-purple-700 w-full" onclick="return confirm('Are you sure you want to execute these custom SQL commands?')">
            <i class="fas fa-bolt mr-2"></i> Execute Custom SQL
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Clear Database -->
  <div class="card">
    <div class="card-header bg-red-50 border-b border-red-100">
      <h2 class="card-title text-red-800"><i class="fas fa-trash-alt mr-2"></i> Reset Database (Start Fresh)</h2>
    </div>
    <div class="card-body flex flex-col justify-between h-full">
      <div>
        <p class="text-sm text-gray-600 mb-4">
          Wipes all transactional data, customers, products, stock, and users.
        </p>
        <div class="bg-red-50 border-l-4 border-red-400 p-3 mb-4 rounded">
          <div class="flex">
            <div class="flex-shrink-0">
              <i class="fas fa-exclamation-triangle text-red-400"></i>
            </div>
            <div class="ml-3">
              <p class="text-xs text-red-700 font-bold">
                WARNING: This action is irreversible. Only your current Administrator account and system roles will remain.
              </p>
            </div>
          </div>
        </div>
        
        <form method="POST" action="<?= url('admin/database-sync/clear') ?>" onsubmit="return confirm('CRITICAL WARNING: Are you absolutely sure you want to clear all data? This will delete all users, orders, dispatches, products, dealers, and reset the database. This action CANNOT be undone!')">
          <?= Helpers::csrfField() ?>
          <button type="submit" class="btn btn-danger bg-red-600 hover:bg-red-700 text-white w-full py-2.5 font-bold rounded-lg shadow-md transition active:scale-[0.98]">
            <i class="fas fa-trash-arrow-up mr-2"></i> Clear All Data
          </button>
        </form>
      </div>
    </div>
  </div>

</div>

