<?php 
$pageTitle = 'Van Inventory'; 
$dateFormatted = date('d / m / Y', strtotime($date));
?>

<div class="p-2.5 sm:p-4 space-y-3.5 pb-28 max-w-5xl mx-auto font-sans text-slate-800 print:p-0 print:max-w-none">

  <!-- Premium Header Card -->
  <div class="bg-white/95 backdrop-blur-md px-3.5 py-3 sm:px-5 sm:py-3.5 rounded-2xl border border-slate-200/90 shadow-xs flex items-center justify-between gap-3 print:shadow-none print:border-none print:p-0">
    <div class="flex items-center gap-3">
      <a href="<?= url('sr/dashboard') ?>" class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100/80 border border-slate-200 flex items-center justify-center text-slate-700 hover:bg-slate-900 hover:text-white transition-all duration-200 shadow-2xs active:scale-95 print:hidden">
        <i class="fa-solid fa-arrow-left text-xs sm:text-sm"></i>
      </a>
      <div>
        <div class="flex items-center gap-2">
          <h1 class="text-lg sm:text-xl font-black text-slate-900 leading-tight tracking-tight">
            Van Inventory
          </h1>
          <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200/80 print:hidden">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Live Sync
          </span>
        </div>
        <p class="text-xs text-slate-500 font-bold leading-tight mt-0.5">ভ্যান স্টক ও মালের চালান বিবরণ</p>
      </div>
    </div>
    
    <div class="flex items-center gap-2 print:hidden">
      <button onclick="window.print()" class="h-9 px-3 sm:px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-black flex items-center gap-2 transition active:scale-95 shadow-xs">
        <i class="fa-solid fa-print text-xs"></i>
        <span class="hidden sm:inline">প্রিন্ট রিপোর্ট</span>
      </button>
    </div>
  </div>

  <!-- Quick Metric Summary Cards -->
  <div class="grid grid-cols-3 gap-2 sm:gap-3 print:hidden">
    <!-- Load (Out) Card -->
    <div class="bg-gradient-to-br from-emerald-50/90 via-emerald-50/50 to-white p-2.5 sm:p-3 rounded-2xl border border-emerald-200/80 shadow-2xs relative overflow-hidden">
      <div class="flex items-center justify-between text-[11px] font-extrabold text-emerald-800 mb-1">
        <span class="truncate">লোড (Out)</span>
        <span class="text-emerald-600">🚚</span>
      </div>
      <div class="text-sm sm:text-lg font-black text-emerald-950 tracking-tight" id="cardLoad">
        ৳ <?= number_format($subtotal['dispatched_val'] ?? 0) ?>
      </div>
    </div>

    <!-- Sell Card -->
    <div class="bg-gradient-to-br from-blue-50/90 via-blue-50/50 to-white p-2.5 sm:p-3 rounded-2xl border border-blue-200/80 shadow-2xs relative overflow-hidden">
      <div class="flex items-center justify-between text-[11px] font-extrabold text-blue-800 mb-1">
        <span class="truncate">বিক্রি (Sell)</span>
        <span class="text-blue-600">🛍️</span>
      </div>
      <div class="text-sm sm:text-lg font-black text-blue-950 tracking-tight" id="cardSell">
        ৳ <?= number_format($subtotal['sell_val'] ?? 0) ?>
      </div>
    </div>

    <!-- Remaining (In) Card -->
    <div class="bg-gradient-to-br from-indigo-50/90 via-indigo-50/50 to-white p-2.5 sm:p-3 rounded-2xl border border-indigo-200/80 shadow-2xs relative overflow-hidden">
      <div class="flex items-center justify-between text-[11px] font-extrabold text-indigo-800 mb-1">
        <span class="truncate">অবশিষ্ট (In)</span>
        <span class="text-indigo-600">📦</span>
      </div>
      <div class="text-sm sm:text-lg font-black text-indigo-950 tracking-tight" id="cardReturn">
        ৳ <?= number_format($subtotal['return_val'] ?? 0) ?>
      </div>
    </div>
  </div>

  <!-- Filters Card (Date, Search, Company) -->
  <div class="bg-white p-3 rounded-2xl border border-slate-200/90 shadow-2xs space-y-2.5 print:hidden">
    <!-- Date Selector Bar -->
    <form method="GET" action="<?= url('sr/transactions') ?>" id="dateForm" class="bg-slate-50 border border-slate-200/80 rounded-xl p-2 flex items-center justify-between gap-2">
      <div class="flex items-center gap-2 text-slate-800 font-extrabold text-xs sm:text-sm pl-1">
        <i class="fa-solid fa-calendar-days text-slate-500"></i>
        <span>Select Date:</span>
      </div>
      
      <div class="relative flex items-center">
        <label for="dateInput" class="cursor-pointer font-black text-slate-900 text-xs sm:text-sm hover:text-blue-600 transition flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg border border-slate-200/90 shadow-2xs hover:border-slate-300">
          <span class="tracking-wide"><?= h($dateFormatted) ?></span>
          <i class="fa-regular fa-calendar text-slate-400 text-xs"></i>
        </label>
        <input type="date" id="dateInput" name="date" value="<?= h($date) ?>" onchange="document.getElementById('dateForm').submit()" class="absolute opacity-0 pointer-events-auto inset-0 w-full h-full cursor-pointer">
      </div>
    </form>

    <!-- Search & Company Dropdown Filter Grid -->
    <div class="grid grid-cols-2 gap-2">
      <!-- Live Search Input -->
      <div class="relative">
        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs">
          <i class="fa-solid fa-magnifying-glass"></i>
        </span>
        <input type="text" id="searchInput" placeholder="পণ্য খুঁজুন..." 
               class="w-full bg-slate-50/90 border border-slate-200/90 rounded-xl pl-9 pr-3 py-2 text-xs font-bold text-slate-900 placeholder-slate-400 outline-none focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 transition">
      </div>

      <!-- Company Dropdown Filter -->
      <div class="relative">
        <select id="companySelect" class="w-full bg-slate-50/90 border border-slate-200/90 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 outline-none focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/10 transition appearance-none cursor-pointer pr-8 truncate">
          <option value="ALL">সব কোম্পানি (All)</option>
          <?php if (!empty($companies)): ?>
            <?php foreach ($companies as $comp): ?>
              <option value="<?= h($comp['name']) ?>"><?= h($comp['name']) ?></option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
        <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400 text-xs">
          <i class="fa-solid fa-chevron-down"></i>
        </span>
      </div>
    </div>
  </div>

  <!-- Inventory Table Container -->
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden print:border-slate-400">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse min-w-[500px]" id="inventoryTable">
        <thead>
          <tr class="border-b border-slate-200 text-xs text-slate-900 font-black tracking-tight">
            <!-- Product Name Header -->
            <th class="p-2.5 sm:p-3 bg-slate-100/90 text-center border-r border-slate-200/90 w-[28%] font-black text-slate-800">
              পণ্যের নাম
            </th>
            
            <!-- Load (Out) Header -->
            <th class="p-2.5 sm:p-3 bg-[#e8f6ed] text-center border-r border-slate-200/90 w-[24%] font-black text-emerald-950">
              লোড (Out)<br>
              <span class="text-[10px] font-bold text-emerald-800/90">পরিমাণ / মূল্য</span>
            </th>
            
            <!-- Sell Header -->
            <th class="p-2.5 sm:p-3 bg-[#ebf3fe] text-center border-r border-slate-200/90 w-[24%] font-black text-blue-950">
              বিক্রি (Sell)<br>
              <span class="text-[10px] font-bold text-blue-800/90">পরিমাণ / মূল্য</span>
            </th>
            
            <!-- Remaining (In) Header -->
            <th class="p-2.5 sm:p-3 bg-[#eceffe] text-center w-[24%] font-black text-indigo-950">
              অবশিষ্ট (In)<br>
              <span class="text-[10px] font-bold text-indigo-800/90">পরিমাণ / মূল্য</span>
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 font-sans" id="tableBody">
          <?php if (empty($transactions)): ?>
            <tr id="emptyRow">
              <td colspan="4" class="p-8 text-center text-slate-500 font-bold bg-white">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-xl mx-auto mb-2"><i class="fa-solid fa-box-open"></i></div>
                <span class="text-xs font-bold text-slate-600">কোনো লেনদেনের তথ্য পাওয়া যায়নি।</span>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($transactions as $t): ?>
              <?php 
                $ppb = (int)($t['pieces_per_box'] ?: 1);

                // Format Quantity Function
                $formatQty = function($qty) use ($ppb) {
                  if ($qty <= 0) {
                    return $ppb > 1 ? '0 কার্টন' : '0 পিস';
                  }
                  if ($ppb > 1) {
                    $box = floor($qty / $ppb);
                    $rem = $qty % $ppb;
                    if ($box > 0 && $rem == 0) {
                      return sprintf('%02d কার্টন', $box);
                    } elseif ($box > 0 && $rem > 0) {
                      return sprintf('%d কা. %d পিস', $box, $rem);
                    } else {
                      return sprintf('%02d পিস', $rem);
                    }
                  }
                  return sprintf('%02d পিস', $qty);
                };
              ?>
              <tr class="product-row hover:bg-slate-50/90 transition-colors" 
                  data-name="<?= h(mb_strtolower($t['product_name'])) ?>" 
                  data-company="<?= h($t['company_name']) ?>"
                  data-load-val="<?= $t['dispatched_val'] ?>"
                  data-sell-val="<?= $t['sell_val'] ?>"
                  data-return-val="<?= $t['return_val'] ?>">
                
                <!-- Product Name Cell -->
                <td class="p-2.5 border-r border-slate-100 align-middle bg-white">
                  <div class="font-extrabold text-slate-900 text-xs sm:text-sm leading-snug">
                    <?= h($t['product_name']) ?>
                  </div>
                  <div class="inline-block mt-0.5">
                    <span class="text-[10px] font-extrabold text-indigo-700 bg-indigo-50/80 border border-indigo-100 px-2 py-0.5 rounded-md">
                      <?= h($t['company_name']) ?>
                    </span>
                  </div>
                </td>

                <!-- Load (Out) Cell -->
                <td class="p-2.5 text-center border-r border-slate-100 align-middle bg-[#f3fbf5]/80">
                  <div class="font-black text-slate-900 text-xs sm:text-sm">
                    <?= $formatQty($t['dispatched_qty']) ?>
                  </div>
                  <div class="text-[10px] font-bold text-slate-500 mt-0.5">
                    ৳ <?= number_format($t['dispatched_val']) ?>
                  </div>
                </td>

                <!-- Sell Cell -->
                <td class="p-2.5 text-center border-r border-slate-100 align-middle bg-[#f4f8ff]/80">
                  <div class="font-black text-slate-900 text-xs sm:text-sm">
                    <?= $formatQty($t['sell_qty']) ?>
                  </div>
                  <div class="text-[10px] font-bold text-slate-500 mt-0.5">
                    ৳ <?= number_format($t['sell_val']) ?>
                  </div>
                </td>

                <!-- Remaining (In) Cell -->
                <td class="p-2.5 text-center align-middle bg-[#f5f6ff]/80">
                  <div class="font-black text-slate-900 text-xs sm:text-sm">
                    <?= $formatQty($t['return_qty']) ?>
                  </div>
                  <div class="text-[10px] font-bold text-slate-500 mt-0.5">
                    ৳ <?= number_format($t['return_val']) ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>

        <!-- Footer Subtotal -->
        <tfoot>
          <tr class="border-t-2 border-slate-300 font-black text-slate-900 text-xs sm:text-sm">
            <td class="p-3 text-center border-r border-slate-200/90 bg-slate-100/90 font-black text-slate-900">
              সর্বমোট (Subtotal):
            </td>
            <td class="p-3 text-center border-r border-slate-200/90 bg-[#e8f6ed] font-black text-emerald-950" id="subtotalLoad">
              ৳ <?= number_format($subtotal['dispatched_val'] ?? 0) ?>
            </td>
            <td class="p-3 text-center border-r border-slate-200/90 bg-[#ebf3fe] font-black text-blue-950" id="subtotalSell">
              ৳ <?= number_format($subtotal['sell_val'] ?? 0) ?>
            </td>
            <td class="p-3 text-center bg-[#eceffe] font-black text-indigo-950" id="subtotalReturn">
              ৳ <?= number_format($subtotal['return_val'] ?? 0) ?>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

</div>

<!-- Client-side Filtering & Subtotal Recalculation JS -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  const searchInput = document.getElementById('searchInput');
  const companySelect = document.getElementById('companySelect');
  const rows = document.querySelectorAll('.product-row');
  
  const cardLoadEl = document.getElementById('cardLoad');
  const cardSellEl = document.getElementById('cardSell');
  const cardReturnEl = document.getElementById('cardReturn');
  
  const subtotalLoadEl = document.getElementById('subtotalLoad');
  const subtotalSellEl = document.getElementById('subtotalSell');
  const subtotalReturnEl = document.getElementById('subtotalReturn');

  function filterTable() {
    const query = (searchInput ? searchInput.value : '').trim().toLowerCase();
    const selectedCompany = companySelect ? companySelect.value : 'ALL';

    let totalLoad = 0;
    let totalSell = 0;
    let totalReturn = 0;

    rows.forEach(row => {
      const name = row.getAttribute('data-name') || '';
      const company = row.getAttribute('data-company') || '';
      
      const loadVal = parseFloat(row.getAttribute('data-load-val') || 0);
      const sellVal = parseFloat(row.getAttribute('data-sell-val') || 0);
      const returnVal = parseFloat(row.getAttribute('data-return-val') || 0);

      const matchesSearch = !query || name.includes(query) || company.toLowerCase().includes(query);
      const matchesCompany = (selectedCompany === 'ALL') || (company === selectedCompany);

      if (matchesSearch && matchesCompany) {
        row.style.display = '';
        totalLoad += loadVal;
        totalSell += sellVal;
        totalReturn += returnVal;
      } else {
        row.style.display = 'none';
      }
    });

    const formattedLoad = '৳ ' + Math.round(totalLoad).toLocaleString('en-US');
    const formattedSell = '৳ ' + Math.round(totalSell).toLocaleString('en-US');
    const formattedReturn = '৳ ' + Math.round(totalReturn).toLocaleString('en-US');

    if (cardLoadEl) cardLoadEl.innerText = formattedLoad;
    if (cardSellEl) cardSellEl.innerText = formattedSell;
    if (cardReturnEl) cardReturnEl.innerText = formattedReturn;

    if (subtotalLoadEl) subtotalLoadEl.innerText = formattedLoad;
    if (subtotalSellEl) subtotalSellEl.innerText = formattedSell;
    if (subtotalReturnEl) subtotalReturnEl.innerText = formattedReturn;
  }

  if (searchInput) searchInput.addEventListener('input', filterTable);
  if (companySelect) companySelect.addEventListener('change', filterTable);
});
</script>
