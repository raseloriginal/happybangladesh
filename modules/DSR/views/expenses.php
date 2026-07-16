<?php $pageTitle = 'My Expenses'; ?>
<div class="h-full flex flex-col bg-gray-50 pb-20 overflow-y-auto relative">
  
  <!-- Header -->
  <div class="bg-brand rounded-b-[40px] shadow-sm px-4 pt-10 pb-8 relative z-10 flex items-center justify-between">
    <div class="flex items-center gap-3 text-white">
      <a href="<?= url('dsr/profile') ?>" class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-md active:bg-white/30 transition">
        <i class="fa-solid fa-arrow-left"></i>
      </a>
      <h1 class="text-xl font-bold">My Expenses</h1>
    </div>
    <button onclick="document.getElementById('addExpenseSheet').classList.add('active'); document.getElementById('bottomSheetOverlay').classList.add('active');" class="w-10 h-10 bg-white text-brand rounded-full flex items-center justify-center shadow-md active:scale-95 transition">
      <i class="fa-solid fa-plus"></i>
    </button>
  </div>

  <div class="px-4 -mt-4 relative z-20">
    <?php if(empty($items)): ?>
      <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center">
        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center text-gray-400 mb-4">
          <i class="fa-solid fa-receipt text-3xl"></i>
        </div>
        <h2 class="text-lg font-bold text-gray-800 mb-1">No Expenses Yet</h2>
        <p class="text-sm text-gray-500">You haven't recorded any expenses. Tap the + button to add one.</p>
      </div>
    <?php else: ?>
      <div class="space-y-3">
        <?php foreach($items as $item): ?>
          <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-xl flex items-center justify-center flex-shrink-0">
              <i class="fa-solid fa-money-bill-wave"></i>
            </div>
            <div class="flex-1">
              <div class="font-bold text-sm text-gray-800 mb-0.5 capitalize"><?= h($item['category']) ?></div>
              <div class="text-[10px] text-gray-500 line-clamp-1"><?= h($item['description'] ?: 'No description') ?></div>
              <div class="text-[9px] text-gray-400 font-bold mt-1"><?= date('d M Y', strtotime($item['date'])) ?></div>
            </div>
            <div class="text-right">
              <div class="text-lg font-black text-gray-800">৳<?= number_format($item['amount'], 2) ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Bottom Sheet Overlay -->
  <div id="bottomSheetOverlay" class="bottom-sheet-overlay" onclick="closeSheet()"></div>

  <!-- Bottom Sheet: Add Expense -->
  <div id="addExpenseSheet" class="bottom-sheet pb-[env(safe-area-inset-bottom)]">
    <div class="bottom-sheet-handle"></div>
    <div class="bottom-sheet-content">
      <h3 class="font-bold text-lg text-gray-800 mb-4">Record New Expense</h3>
      
      <form action="<?= url('dsr/expenses') ?>" method="POST">
        <?= Helpers::csrfField() ?>
        
        <div class="space-y-4">
          <div>
            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wide mb-1 ml-1">Category</label>
            <select name="category" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-gray-800 font-semibold outline-none focus:ring-2 focus:ring-brand focus:bg-white transition">
              <option value="fuel">Fuel</option>
              <option value="food">Food</option>
              <option value="toll">Toll</option>
              <option value="repair">Repair</option>
              <option value="other">Other</option>
            </select>
          </div>

          <div>
            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wide mb-1 ml-1">Amount (৳)</label>
            <input type="number" name="amount" step="0.01" min="0.01" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-gray-800 font-semibold outline-none focus:ring-2 focus:ring-brand focus:bg-white transition" placeholder="0.00">
          </div>

          <div>
            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wide mb-1 ml-1">Date</label>
            <input type="date" name="date" required value="<?= date('Y-m-d') ?>" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-gray-800 font-semibold outline-none focus:ring-2 focus:ring-brand focus:bg-white transition">
          </div>

          <div>
            <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wide mb-1 ml-1">Description (Optional)</label>
            <textarea name="description" rows="2" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-gray-800 text-sm outline-none focus:ring-2 focus:ring-brand focus:bg-white transition" placeholder="Enter details..."></textarea>
          </div>
        </div>

        <div class="flex gap-3 mt-6">
          <button type="button" onclick="closeSheet()" class="flex-1 py-3.5 rounded-xl font-bold bg-gray-100 text-gray-600 active:bg-gray-200 transition">Cancel</button>
          <button type="submit" class="flex-1 py-3.5 rounded-xl font-bold bg-brand text-white active:scale-[0.98] transition shadow-lg shadow-blue-500/30">Save Expense</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function closeSheet() {
    document.getElementById('addExpenseSheet').classList.remove('active');
    document.getElementById('bottomSheetOverlay').classList.remove('active');
}
</script>
