<?php 
$pageTitle = 'সমারি'; 
$dateFormatted = date('d / m / Y', strtotime($selectedDate));

// Consolidated DSR stock maps
$consolidated = [];
$initEntry = function($name, $ppb, $price) {
    return [
        'name' => $name,
        'pcs_per_box' => $ppb,
        'trade_price' => $price,
        'outside_qty' => 0,
        'outside_val' => 0,
        'sale_qty' => 0,
        'sale_val' => 0,
        'inside_qty' => 0,
        'inside_val' => 0,
        'damage_qty' => 0,
        'damage_val' => 0
    ];
};

foreach (($products['outside'] ?? []) as $p) {
    $name = $p['name'];
    if (!isset($consolidated[$name])) {
        $consolidated[$name] = $initEntry($name, $p['pcs_per_box'], $p['trade_price']);
    }
    $consolidated[$name]['outside_qty'] += $p['qty'];
    $consolidated[$name]['outside_val'] += $p['value'];
}
foreach (($products['sale'] ?? []) as $p) {
    $name = $p['name'];
    if (!isset($consolidated[$name])) {
        $consolidated[$name] = $initEntry($name, $p['pcs_per_box'], $p['trade_price']);
    }
    $consolidated[$name]['sale_qty'] += $p['qty'];
    $consolidated[$name]['sale_val'] += $p['value'];
}
foreach (($products['inside'] ?? []) as $p) {
    $name = $p['name'];
    if (!isset($consolidated[$name])) {
        $consolidated[$name] = $initEntry($name, $p['pcs_per_box'], $p['trade_price']);
    }
    $consolidated[$name]['inside_qty'] += $p['qty'];
    $consolidated[$name]['inside_val'] += $p['value'];
}
foreach (($products['damage'] ?? []) as $p) {
    $name = $p['name'];
    if (!isset($consolidated[$name])) {
        $consolidated[$name] = $initEntry($name, $p['pcs_per_box'], $p['trade_price']);
    }
    $consolidated[$name]['damage_qty'] += $p['qty'];
    $consolidated[$name]['damage_val'] += $p['value'];
}

// Format Quantity Helper
$formatQty = function($qty, $ppb) {
  if ($qty <= 0) {
    return $ppb > 1 ? '0 কা.' : '0 পিস';
  }
  if ($ppb > 1) {
    $box = floor($qty / $ppb);
    $rem = $qty % $ppb;
    if ($box > 0 && $rem == 0) {
      return sprintf('%d কা.', $box);
    } elseif ($box > 0 && $rem > 0) {
      return sprintf('%d কা. %d পিস', $box, $rem);
    } else {
      return sprintf('%d পিস', $rem);
    }
  }
  return sprintf('%d পিস', $qty);
};
?>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap');
  .font-siliguri {
    font-family: 'Hind Siliguri', 'Inter', sans-serif;
  }
</style>

<div class="p-3 sm:p-5 space-y-4 pb-28 max-w-5xl mx-auto font-siliguri text-slate-800 print:p-0 print:max-w-none">

  <!-- Premium Minimal Header Card -->
  <div class="bg-white/95 backdrop-blur-md px-4 py-3 sm:px-6 sm:py-4 rounded-2xl border border-slate-200/60 shadow-2xs flex items-center justify-between gap-3 print:shadow-none print:border-none print:p-0">
    <div class="flex items-center gap-3">
      <a href="<?= url('dsr/dashboard') ?>" class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 hover:bg-slate-900 hover:text-white transition-all duration-200 flex items-center justify-center text-slate-600 shadow-2xs active:scale-95 print:hidden">
        <i class="fa-solid fa-arrow-left text-xs sm:text-sm"></i>
      </a>
      <div>
        <div class="flex items-center gap-2">
          <h1 class="text-xl sm:text-2xl font-bold text-slate-900 leading-tight tracking-tight">
            সমারি
          </h1>
          <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[9px] font-bold bg-slate-100 text-slate-600 border border-slate-200/50 print:hidden">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> ভ্যান স্টক
          </span>
        </div>
        <p class="text-xs text-slate-400 font-medium leading-tight mt-1">ভ্যান স্টক ও মালের চালান বিবরণ</p>
      </div>
    </div>
    
    <div class="flex items-center gap-2 print:hidden">
      <!-- Date Picker Form (Icon Only) -->
      <form method="GET" action="<?= url('dsr/van-stock') ?>" id="dateForm" class="relative flex items-center">
        <button type="button" onclick="const inp=document.getElementById('dateInput'); if(inp.showPicker){inp.showPicker()}else{inp.click()}" class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition active:scale-95 shadow-2xs border border-slate-200/60" title="তারিখ পরিবর্তন করুন">
          <i class="fa-regular fa-calendar-days text-sm"></i>
        </button>
        <input type="date" id="dateInput" name="date" value="<?= h($selectedDate) ?>" onchange="document.getElementById('dateForm').submit()" class="absolute opacity-0 pointer-events-none inset-0 w-full h-full">
      </form>

      <button onclick="window.print()" class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-900 hover:bg-slate-800 text-white flex items-center justify-center transition active:scale-95 shadow-sm" title="প্রিন্ট রিপোর্ট">
        <i class="fa-solid fa-print text-xs sm:text-sm"></i>
      </button>
    </div>
  </div>

  <!-- Search Input (Excel Style) -->
  <div class="bg-white p-3 rounded-2xl border border-slate-200/50 shadow-3xs print:hidden">
    <div class="relative flex-1">
      <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
      <input type="text" id="vanSearchInput" oninput="filterVanStock()" placeholder="পণ্যের নাম খুঁজুন..." 
        class="w-full bg-slate-50 border border-slate-200/60 rounded-xl pl-9 pr-3 py-2 text-xs font-bold text-slate-800 placeholder:text-slate-400 focus:outline-none focus:border-blue-500 transition" autocomplete="off">
    </div>
  </div>

  <!-- Minimal Table Container -->
  <div class="bg-white rounded-2xl border border-slate-200/80 shadow-3xs overflow-hidden print:border-slate-300">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse min-w-[550px]" id="inventoryTable">
        <thead>
          <tr class="border-b border-slate-200 text-xs font-bold tracking-tight font-siliguri">
            <th class="p-3 bg-slate-100/80 text-left border-r border-slate-200/60 text-slate-700 w-[24%]">
              পণ্যের নাম
            </th>
            <th class="p-3 bg-blue-50 text-center border-r border-slate-200/60 text-blue-700 w-[19%]">
              লোড (Out)
            </th>
            <th class="p-3 bg-emerald-50 text-center border-r border-slate-200/60 text-emerald-700 w-[19%]">
              বিক্রি (Sell)
            </th>
            <th class="p-3 bg-purple-50 text-center border-r border-slate-200/60 text-purple-700 w-[19%]">
              অবশিষ্ট (In)
            </th>
            <th class="p-3 bg-rose-50 text-center text-rose-700 w-[19%]">
              ক্ষতি (Damage)
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 font-sans" id="tableBody">
          <?php if (empty($consolidated)): ?>
            <tr id="emptyRow">
              <td colspan="5" class="p-12 text-center text-slate-400 bg-white">
                <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-300 flex items-center justify-center text-xl mx-auto mb-2"><i class="fa-solid fa-box-open"></i></div>
                <span class="text-xs font-medium">কোনো চালানের তথ্য পাওয়া যায়নি।</span>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($consolidated as $name => $p): ?>
              <tr class="product-row hover:bg-slate-50/40 transition-colors" data-name="<?= h($name) ?>">
                
                <!-- Product Name Cell -->
                <td class="p-3 border-r border-slate-100 align-middle bg-white">
                  <div class="font-bold text-slate-800 text-xs sm:text-sm leading-snug">
                    <?= h($name) ?>
                  </div>
                </td>

                <!-- Load (Out) Cell (Blue) -->
                <td class="p-3 text-center border-r border-slate-100 align-middle bg-blue-50/10 text-slate-800 font-siliguri">
                  <div class="font-bold text-slate-900 text-xs sm:text-sm">
                    <?= $formatQty($p['outside_qty'], $p['pcs_per_box']) ?>
                  </div>
                  <div class="text-[10px] text-blue-600 font-bold mt-0.5 font-mono">
                    ৳<?= number_format($p['outside_val']) ?>
                  </div>
                </td>

                <!-- Sell Cell (Emerald) -->
                <td class="p-3 text-center border-r border-slate-100 align-middle bg-emerald-50/10 text-slate-800 font-siliguri">
                  <div class="font-bold text-slate-900 text-xs sm:text-sm">
                    <?= $formatQty($p['sale_qty'], $p['pcs_per_box']) ?>
                  </div>
                  <div class="text-[10px] text-emerald-600 font-bold mt-0.5 font-mono">
                    ৳<?= number_format($p['sale_val']) ?>
                  </div>
                </td>

                <!-- Remaining (In) Cell (Purple) -->
                <td class="p-3 text-center border-r border-slate-100 align-middle bg-purple-50/10 text-slate-800 font-siliguri">
                  <div class="font-bold text-slate-900 text-xs sm:text-sm">
                    <?= $formatQty($p['inside_qty'], $p['pcs_per_box']) ?>
                  </div>
                  <div class="text-[10px] text-purple-600 font-bold mt-0.5 font-mono">
                    ৳<?= number_format($p['inside_val']) ?>
                  </div>
                </td>

                <!-- Damage Cell (Rose) -->
                <td class="p-3 text-center align-middle bg-rose-50/10 text-slate-800 font-siliguri">
                  <div class="font-bold text-slate-900 text-xs sm:text-sm">
                    <?= $formatQty($p['damage_qty'], $p['pcs_per_box']) ?>
                  </div>
                  <div class="text-[10px] text-rose-600 font-bold mt-0.5 font-mono">
                    ৳<?= number_format($p['damage_val']) ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>

        <!-- Footer Subtotal (Excel Style) -->
        <tfoot>
          <tr class="border-t border-slate-300 font-bold text-slate-900 text-xs">
            <td class="p-3 text-center border-r border-slate-200 bg-slate-100 font-siliguri font-bold text-slate-500">
              সর্বমোট:
            </td>
            <td class="p-3 text-center border-r border-slate-200 bg-blue-50/60 text-blue-700 font-black font-mono">
              ৳<?= number_format($totals['outside']) ?>
            </td>
            <td class="p-3 text-center border-r border-slate-200 bg-emerald-50/60 text-emerald-700 font-black font-mono">
              ৳<?= number_format($totals['sale']) ?>
            </td>
            <td class="p-3 text-center border-r border-slate-200 bg-purple-50/60 text-purple-700 font-black font-mono">
              ৳<?= number_format($totals['inside']) ?>
            </td>
            <td class="p-3 text-center bg-rose-50/60 text-rose-700 font-black font-mono">
              ৳<?= number_format($totals['damage']) ?>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

</div>

<script>
function filterVanStock() {
  const q = document.getElementById('vanSearchInput').value.toLowerCase().trim();
  const rows = document.querySelectorAll('.product-row');
  rows.forEach(row => {
    const name = row.getAttribute('data-name').toLowerCase();
    if (name.includes(q)) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  });
}
</script>
