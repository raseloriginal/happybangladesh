<?php $pageTitle = 'My Expenses'; ?>
<div class="h-full flex flex-col bg-gray-50 pb-20 overflow-y-auto relative font-sans">
  
  <!-- Header -->
  <div class="bg-blue-600 rounded-b-[36px] shadow-sm px-4 pt-10 pb-8 relative z-10 flex items-center justify-between text-white">
    <div class="flex items-center gap-3">
      <a href="<?= url('dsr/profile') ?>" class="w-10 h-10 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-md active:bg-white/30 transition">
        <i class="fa-solid fa-arrow-left text-sm"></i>
      </a>
      <div>
        <h1 class="text-lg font-black leading-tight">সারাদিনের খরচ এন্ট্রি</h1>
        <p class="text-[11px] text-blue-100 font-medium">ফুয়েল, টোল, নাস্তা ও অন্যান্য খরচ</p>
      </div>
    </div>
    <button onclick="document.getElementById('addExpenseSheet').classList.add('active'); document.getElementById('bottomSheetOverlay').classList.add('active');" class="w-10 h-10 bg-white text-blue-600 rounded-2xl flex items-center justify-center shadow-md active:scale-95 transition" title="নতুন খরচ যোগ করুন">
      <i class="fa-solid fa-plus text-base"></i>
    </button>
  </div>

  <div class="px-4 -mt-4 relative z-20 space-y-4">
    
    <!-- Modern Date Selector -->
    <div class="mb-2">
      <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-none snap-x">
        <?php 
          for($i = 6; $i >= 0; $i--):
              $d = date('Y-m-d', strtotime("-$i days"));
              $dayName = date('D', strtotime($d));
              $dayNum = date('d', strtotime($d));
              $isSelected = ($d === $selectedDate);
              $bgClass = $isSelected ? 'bg-blue-600 text-white shadow-md' : 'bg-white border border-slate-200/80 shadow-2xs';
              $textClass = $isSelected ? 'text-white' : 'text-slate-800';
        ?>
        <a href="?date=<?= $d ?>" class="snap-start flex-shrink-0 w-14 h-16 flex flex-col items-center justify-center rounded-2xl transition active:scale-95 <?= $bgClass ?>">
          <span class="text-[10px] font-bold uppercase tracking-wider mb-0.5 <?= $isSelected ? 'text-blue-100' : 'text-slate-400' ?>"><?= $dayName ?></span>
          <span class="text-lg font-black font-mono <?= $textClass ?>"><?= $dayNum ?></span>
        </a>
        <?php endfor; ?>
      </div>
    </div>

    <?php if(empty($items)): ?>
      <div class="bg-white rounded-2xl p-8 shadow-xs border border-slate-200/90 flex flex-col items-center justify-center text-center">
        <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center mb-3">
          <i class="fa-solid fa-receipt text-2xl"></i>
        </div>
        <h2 class="text-base font-black text-slate-800 mb-1">কোনো খরচ যোগ করা হয়নি</h2>
        <p class="text-xs text-slate-500 max-w-xs">এই তারিখের জন্য কোনো খরচ এন্ট্রি করা হয়নি। উপরে প্লাস (+) বাটনে চেপে খরচ যুক্ত করুন।</p>
      </div>
    <?php else: ?>
      <div class="space-y-2.5">
        <?php foreach($items as $item): ?>
          <div class="bg-white p-3.5 rounded-2xl shadow-xs border border-slate-200/90 flex items-center gap-3.5">
            <div class="w-11 h-11 bg-blue-50 text-blue-600 border border-blue-200/60 rounded-xl flex items-center justify-center flex-shrink-0 text-lg">
              <i class="fa-solid fa-money-bill-wave"></i>
            </div>
            <div class="flex-1 min-width-0">
              <div class="font-black text-xs text-slate-900 capitalize"><?= h($item['category']) ?></div>
              <div class="text-[11px] text-slate-500 truncate mt-0.5"><?= h($item['description'] ?: 'কোনো বিবরণ দেওয়া হয়নি') ?></div>
              <div class="text-[10px] text-slate-400 font-bold font-mono mt-1"><?= date('d M Y', strtotime($item['date'])) ?></div>
            </div>
            <div class="text-right">
              <div class="text-base font-black text-slate-900 font-mono">৳<?= number_format($item['amount'], 2) ?></div>
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
    <div class="bottom-sheet-content p-4">
      <h3 class="font-black text-base text-slate-900 mb-4">নতুন খরচ এন্ট্রি করুন</h3>
      
      <form action="<?= url('dsr/expenses/store') ?>" method="POST" class="space-y-3.5">
        <?= Helpers::csrfField() ?>
        
        <div>
          <label class="block text-xs font-bold text-slate-600 mb-1">খরচের খাত (Category)</label>
          <select name="category" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 font-bold outline-none focus:border-blue-500">
            <option value="fuel">ফুয়েল / তেল</option>
            <option value="food">নাস্তা / খাবার</option>
            <option value="toll">টোল / পার্কিং</option>
            <option value="repair">গাড়ির মেরামত</option>
            <option value="other">অন্যান্য খরচ</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-600 mb-1">টাকার পরিমাণ (৳)</label>
          <input type="number" name="amount" step="0.01" min="0.01" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-black text-slate-900 font-mono outline-none focus:border-blue-500" placeholder="0.00">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-600 mb-1">তারিখ</label>
          <input type="date" name="date" required value="<?= htmlspecialchars($selectedDate) ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-bold text-slate-800 outline-none focus:border-blue-500">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-600 mb-1">বিবরণ / নোট (অপশনাল)</label>
          <textarea name="description" rows="2" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:border-blue-500" placeholder="খরচের কোনো মন্তব্য থাকলে লিখুন..."></textarea>
        </div>

        <button type="submit" class="w-full py-3.5 rounded-2xl font-black text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-600/20 active:scale-95 transition text-xs">
          খরচ সেভ করুন
        </button>
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
