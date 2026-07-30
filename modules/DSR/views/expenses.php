<?php 
$pageTitle = 'দৈনিক খরচ'; 
$dateFormatted = date('d / m / Y', strtotime($selectedDate));
?>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap');
  .font-siliguri {
    font-family: 'Hind Siliguri', 'Inter', sans-serif;
  }
</style>

<div class="p-3 sm:p-5 space-y-4 pb-28 max-w-5xl mx-auto font-siliguri text-slate-800 print:p-0 print:max-w-none">

  <!-- 1. Premium Minimal Header Card -->
  <div class="bg-white/95 backdrop-blur-md px-4 py-3 sm:px-6 sm:py-4 rounded-2xl border border-slate-200/60 shadow-2xs flex items-center justify-between gap-3 print:shadow-none print:border-none print:p-0">
    <div class="flex items-center gap-3">
      <a href="<?= url('dsr/profile') ?>" class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 hover:bg-slate-900 hover:text-white transition-all duration-200 flex items-center justify-center text-slate-600 shadow-2xs active:scale-95 print:hidden">
        <i class="fa-solid fa-arrow-left text-xs sm:text-sm"></i>
      </a>
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 leading-tight tracking-tight">দৈনিক খরচ এন্ট্রি</h1>
        <p class="text-xs text-slate-400 font-medium leading-tight mt-1">ফুয়েল, টোল, নাস্তা ও অন্যান্য খরচ • <span class="font-mono text-slate-500 font-bold"><?= date('d M Y', strtotime($selectedDate)) ?></span></p>
      </div>
    </div>

    <div class="flex items-center gap-2 print:hidden">
      <!-- Date Picker Form (Icon Only) -->
      <form method="GET" action="<?= url('dsr/expenses') ?>" id="dateForm" class="relative flex items-center">
        <button type="button" onclick="const inp=document.getElementById('dateInput'); if(inp.showPicker){inp.showPicker()}else{inp.click()}" class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition active:scale-95 shadow-2xs border border-slate-200/60" title="তারিখ পরিবর্তন করুন">
          <i class="fa-regular fa-calendar-days text-sm"></i>
        </button>
        <input type="date" id="dateInput" name="date" value="<?= h($selectedDate) ?>" onchange="document.getElementById('dateForm').submit()" class="absolute opacity-0 pointer-events-none inset-0 w-full h-full">
      </form>

      <!-- Add Expense Button -->
      <button onclick="document.getElementById('addExpenseSheet').classList.add('active'); document.getElementById('bottomSheetOverlay').classList.add('active');" class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-blue-600 hover:bg-blue-700 text-white flex items-center justify-center transition active:scale-95 shadow-sm" title="নতুন খরচ যোগ করুন">
        <i class="fa-solid fa-plus text-sm"></i>
      </button>
    </div>
  </div>

  <!-- 2. Expenses List (Excel Style Table) -->
  <div class="bg-white rounded-2xl border border-slate-200/80 shadow-3xs overflow-hidden print:border-slate-300">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse min-w-[500px]">
        <thead>
          <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold tracking-tight text-slate-650">
            <th class="p-3 border-r border-slate-200/60 w-[25%]">খরচের খাত</th>
            <th class="p-3 border-r border-slate-200/60 w-[45%]">বিবরণ / মন্তব্য</th>
            <th class="p-3 border-r border-slate-200/60 w-[15%] text-center">তারিখ</th>
            <th class="p-3 text-right w-[15%]">টাকার পরিমাণ</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-xs font-sans">
          <?php if (empty($items)): ?>
            <tr>
              <td colspan="4" class="p-12 text-center text-slate-400 bg-white">
                <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-300 flex items-center justify-center text-xl mx-auto mb-2">
                  <i class="fa-solid fa-receipt"></i>
                </div>
                <span class="text-xs font-medium">এই তারিখের জন্য কোনো খরচ এন্ট্রি করা হয়নি।</span>
              </td>
            </tr>
          <?php else: ?>
            <?php 
              $totalAmount = 0;
              foreach ($items as $item): 
                $totalAmount += $item['amount'];
                
                // Friendly category labels
                $catName = 'অন্যান্য খরচ';
                $catIcon = 'fa-money-bill-wave';
                $catBg = 'bg-slate-50 text-slate-600 border-slate-200';
                
                switch($item['category']) {
                  case 'fuel':
                    $catName = 'ফুয়েল / তেল';
                    $catIcon = 'fa-gas-pump';
                    $catBg = 'bg-blue-50 text-blue-600 border-blue-100';
                    break;
                  case 'food':
                    $catName = 'নাস্তা / খাবার';
                    $catIcon = 'fa-utensils';
                    $catBg = 'bg-emerald-50 text-emerald-600 border-emerald-100';
                    break;
                  case 'toll':
                    $catName = 'টোল / পার্কিং';
                    $catIcon = 'fa-road';
                    $catBg = 'bg-amber-50 text-amber-600 border-amber-100';
                    break;
                  case 'repair':
                    $catName = 'গাড়ির মেরামত';
                    $catIcon = 'fa-wrench';
                    $catBg = 'bg-rose-50 text-rose-600 border-rose-100';
                    break;
                }
            ?>
              <tr class="hover:bg-slate-50/30 transition-colors">
                <!-- Category -->
                <td class="p-3 border-r border-slate-100 align-middle bg-white font-siliguri">
                  <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg <?= $catBg ?> border flex items-center justify-center text-xs shrink-0">
                      <i class="fa-solid <?= $catIcon ?>"></i>
                    </div>
                    <span class="font-bold text-slate-800 text-xs"><?= h($catName) ?></span>
                  </div>
                </td>

                <!-- Description -->
                <td class="p-3 border-r border-slate-100 align-middle bg-white text-slate-600 font-siliguri">
                  <?= h($item['description'] ?: '—') ?>
                </td>

                <!-- Date -->
                <td class="p-3 text-center border-r border-slate-100 align-middle bg-white text-slate-400 font-bold font-mono">
                  <?= date('d M Y', strtotime($item['date'])) ?>
                </td>

                <!-- Amount -->
                <td class="p-3 text-right align-middle bg-white font-mono font-black text-slate-900">
                  ৳<?= number_format($item['amount'], 2) ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
        
        <?php if (!empty($items)): ?>
          <!-- Subtotal footer -->
          <tfoot>
            <tr class="border-t border-slate-300 font-bold text-slate-900 text-xs">
              <td colspan="3" class="p-3 text-right border-r border-slate-200 bg-slate-50 text-slate-500 font-siliguri">
                সর্বমোট খরচ:
              </td>
              <td class="p-3 text-right bg-slate-50 text-slate-950 font-black font-mono text-[13px]">
                ৳<?= number_format($totalAmount, 2) ?>
              </td>
            </tr>
          </tfoot>
        <?php endif; ?>
      </table>
    </div>
  </div>

  <!-- Bottom Sheet Overlay -->
  <div id="bottomSheetOverlay" class="bottom-sheet-overlay" onclick="closeSheet()"></div>

  <!-- Bottom Sheet: Add Expense -->
  <div id="addExpenseSheet" class="bottom-sheet pb-[env(safe-area-inset-bottom)]">
    <div class="bottom-sheet-handle"></div>
    <div class="bottom-sheet-content p-4">
      <h3 class="font-bold text-base text-slate-900 mb-4 font-siliguri">নতুন খরচ এন্ট্রি করুন</h3>
      
      <form action="<?= url('dsr/expenses/store') ?>" method="POST" class="space-y-4">
        <?= Helpers::csrfField() ?>
        
        <div>
          <label class="block text-xs font-bold text-slate-600 mb-1 font-siliguri">খরচের খাত (Category)</label>
          <select name="category" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 font-bold outline-none focus:border-blue-500 transition font-siliguri">
            <option value="fuel">ফুয়েল / তেল</option>
            <option value="food">নাস্তা / খাবার</option>
            <option value="toll">টোল / পার্কিং</option>
            <option value="repair">গাড়ির মেরামত</option>
            <option value="other">অন্যান্য খরচ</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-600 mb-1 font-siliguri">টাকার পরিমাণ (৳)</label>
          <input type="number" name="amount" step="0.01" min="0.01" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-black text-slate-900 font-mono outline-none focus:border-blue-500 transition" placeholder="0.00">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-600 mb-1 font-siliguri">তারিখ</label>
          <input type="date" name="date" required value="<?= htmlspecialchars($selectedDate) ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-bold text-slate-800 outline-none focus:border-blue-500 transition font-siliguri">
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-600 mb-1 font-siliguri">বিবরণ / নোট (অপশনাল)</label>
          <textarea name="description" rows="2" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:border-blue-500 transition font-siliguri" placeholder="খরচের কোনো মন্তব্য থাকলে লিখুন..."></textarea>
        </div>

        <button type="submit" class="w-full py-3.5 rounded-xl font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition-all text-xs font-siliguri">
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
