<?php $pageTitle = 'Database Sync'; ?>
<div class="page-header">
  <div><h1 class="page-title">Database Sync & Migration</h1><div class="breadcrumb">Admin &rsaquo; Database Sync</div></div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
  
  <!-- Run Schema.sql -->
  <div class="card">
    <div class="card-header bg-blue-50 border-b border-blue-100">
      <h2 class="card-title text-blue-800"><i class="fas fa-database mr-2"></i> Run Schema Sync</h2>
    </div>
    <div class="card-body">
      <p class="text-sm text-gray-600 mb-4">
        This will execute the <code>database/migrations/schema.sql</code> file. It contains <code>CREATE TABLE IF NOT EXISTS</code> commands. Running this will safely create any missing tables without dropping existing data.
      </p>
      
      <form method="POST" action="<?= url('admin/database-sync/run') ?>">
        <?= Helpers::csrfField() ?>
        <input type="hidden" name="sync_type" value="schema">
        
        <div class="bg-gray-800 rounded-md p-3 mb-4 overflow-y-auto" style="max-height: 200px;">
          <pre class="text-xs text-green-400 font-mono whitespace-pre-wrap"><?= htmlspecialchars(substr($schemaContent, 0, 500)) ?>...</pre>
        </div>
        
        <button type="submit" class="btn btn-primary bg-blue-600 hover:bg-blue-700 w-full" onclick="return confirm('Are you sure you want to run the schema sync?')">
          <i class="fas fa-play mr-2"></i> Execute Schema.sql
        </button>
      </form>
    </div>
  </div>

  <!-- Run Custom SQL -->
  <div class="card">
    <div class="card-header bg-purple-50 border-b border-purple-100">
      <h2 class="card-title text-purple-800"><i class="fas fa-terminal mr-2"></i> Run Custom Migration (SQL)</h2>
    </div>
    <div class="card-body">
      <p class="text-sm text-gray-600 mb-4">
        Paste your <code>ALTER TABLE</code> or any other SQL migration commands here to apply custom changes to the database without losing data.
      </p>
      
      <form method="POST" action="<?= url('admin/database-sync/run') ?>">
        <?= Helpers::csrfField() ?>
        <input type="hidden" name="sync_type" value="custom">
        
        <div class="form-group">
          <textarea name="custom_sql" class="form-input font-mono text-sm" rows="8" placeholder="ALTER TABLE products ADD COLUMN ... ;" required></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary bg-purple-600 hover:bg-purple-700 w-full" onclick="return confirm('Are you sure you want to execute these custom SQL commands?')">
          <i class="fas fa-bolt mr-2"></i> Execute Custom SQL
        </button>
      </form>
    </div>
  </div>

</div>
