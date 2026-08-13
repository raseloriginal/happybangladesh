<?php 
$pageTitle = 'সমারি'; 
$dateFormatted = date('d / m / Y', strtotime($selectedDate));

// Consolidated DSR stock maps
$consolidated = [];
$initEntry = function($name, $ppb, $price) {
    return [
        'name'           => $name,
        'pcs_per_box'    => $ppb,
        'trade_price'    => $price,
        'outside_qty'    => 0,
        'outside_val'    => 0,
        'outside_oc'     => 0,
        'sale_qty'       => 0,
        'sale_val'       => 0,
        'sale_oc'        => 0,
        'ready_sale_qty' => 0,
        'ready_sale_val' => 0,
        'ready_sale_oc'  => 0,
        'inside_qty'     => 0,
        'inside_val'     => 0,
        'inside_oc'      => 0,
        'damage_qty'     => 0,
        'damage_val'     => 0,
        'damage_oc'      => 0
    ];
};

foreach (($products['outside'] ?? []) as $p) {
    $name = $p['name'];
    if (!isset($consolidated[$name])) {
        $consolidated[$name] = $initEntry($name, $p['pcs_per_box'], $p['trade_price']);
    }
    $consolidated[$name]['outside_qty'] += $p['qty'];
    $consolidated[$name]['outside_val'] += $p['value'];
    $consolidated[$name]['outside_oc']  += ($p['oc_value'] ?? 0);
}
foreach (($products['sale'] ?? []) as $p) {
    $name = $p['name'];
    if (!isset($consolidated[$name])) {
        $consolidated[$name] = $initEntry($name, $p['pcs_per_box'], $p['trade_price']);
    }
    $consolidated[$name]['sale_qty'] += $p['qty'];
    $consolidated[$name]['sale_val'] += $p['value'];
    $consolidated[$name]['sale_oc']  += ($p['oc_value'] ?? 0);
}
foreach (($products['ready_sale'] ?? []) as $p) {
    $name = $p['name'];
    if (!isset($consolidated[$name])) {
        $consolidated[$name] = $initEntry($name, $p['pcs_per_box'], $p['trade_price']);
    }
    $consolidated[$name]['ready_sale_qty'] += $p['qty'];
    $consolidated[$name]['ready_sale_val'] += $p['value'];
    $consolidated[$name]['ready_sale_oc']  += ($p['oc_value'] ?? 0);
}
foreach (($products['inside'] ?? []) as $p) {
    $name = $p['name'];
    if (!isset($consolidated[$name])) {
        $consolidated[$name] = $initEntry($name, $p['pcs_per_box'], $p['trade_price']);
    }
    $consolidated[$name]['inside_qty'] += $p['qty'];
    $consolidated[$name]['inside_val'] += $p['value'];
    $consolidated[$name]['inside_oc']  += ($p['oc_value'] ?? 0);
}
foreach (($products['damage'] ?? []) as $p) {
    $name = $p['name'];
    if (!isset($consolidated[$name])) {
        $consolidated[$name] = $initEntry($name, $p['pcs_per_box'], $p['trade_price']);
    }
    $consolidated[$name]['damage_qty'] += $p['qty'];
    $consolidated[$name]['damage_val'] += $p['value'];
    $consolidated[$name]['damage_oc']  += ($p['oc_value'] ?? 0);
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

<div class="p-3 sm:p-5 space-y-4 pb-28 max-w-6xl mx-auto font-siliguri text-slate-800 print:p-0 print:max-w-none">

  <!-- Header Section (Clean & Minimal) -->
  <div class="bg-white px-4 py-3 sm:px-6 sm:py-4 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center justify-between gap-3 print:shadow-none print:border-none print:p-0">
    <div class="flex items-center gap-3">
      <a href="<?= url('dsr/dashboard') ?>" class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition flex items-center justify-center shadow-2xs active:scale-95 print:hidden">
        <i class="fa-solid fa-arrow-left text-xs sm:text-sm"></i>
      </a>
      <div>
        <div class="flex items-center gap-2">
          <h1 class="text-xl sm:text-2xl font-black text-slate-900 leading-tight tracking-tight">
            স্টক ও বিক্রয় সমারি
          </h1>
        </div>
        <p class="text-xs text-slate-500 font-medium leading-tight mt-0.5">দৈনিক ভ্যান স্টক, ডেলিভারি ও রেডি সেলের হিসাব</p>
      </div>
    </div>
    
    <div class="flex items-center gap-2 print:hidden">
      <!-- Date Picker Form -->
      <form method="GET" action="<?= url('dsr/van-stock') ?>" id="dateForm" class="relative flex items-center">
        <button type="button" onclick="const inp=document.getElementById('dateInput'); if(inp.showPicker){inp.showPicker()}else{inp.click()}" class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition active:scale-95 shadow-2xs border border-slate-200" title="তারিখ নির্বাচন">
          <i class="fa-regular fa-calendar-days text-sm"></i>
        </button>
        <input type="date" id="dateInput" name="date" value="<?= h($selectedDate) ?>" onchange="document.getElementById('dateForm').submit()" class="absolute opacity-0 pointer-events-none inset-0 w-full h-full">
      </form>

      <button onclick="window.print()" class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-900 hover:bg-slate-800 text-white flex items-center justify-center transition active:scale-95 shadow-xs" title="প্রিন্ট করুন">
        <i class="fa-solid fa-print text-xs sm:text-sm"></i>
      </button>
    </div>
  </div>

  <!-- Minimal Clean Table Container -->
  <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden print:border-slate-300">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse min-w-[650px]" id="inventoryTable">
        <thead>
          <tr class="bg-slate-50 text-[11px] font-black text-slate-600 uppercase tracking-tight border-b border-slate-200 font-siliguri">
            <th class="p-3 border-r border-slate-100 w-[22%]">
              পণ্যের নাম
            </th>
            <th class="p-3 text-center border-r border-slate-100 text-blue-700 w-[16%]">
              লোড (Out)
            </th>
            <th class="p-3 text-center border-r border-slate-100 text-emerald-700 w-[16%]">
              বিক্রি (Sell)
            </th>
            <th class="p-3 text-center border-r border-slate-100 text-amber-700 w-[16%]">
              রেডি সেল (Ready)
            </th>
            <th class="p-3 text-center border-r border-slate-100 text-purple-700 w-[16%]">
              অবশিষ্ট (In)
            </th>
            <th class="p-3 text-center text-rose-700 w-[14%]">
              ক্ষতি (Damage)
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-xs" id="tableBody">
          <?php if (empty($consolidated)): ?>
            <tr id="emptyRow">
              <td colspan="6" class="p-12 text-center text-slate-400 bg-white">
                <div class="w-10 h-10 rounded-2xl bg-slate-50 text-slate-300 flex items-center justify-center text-lg mx-auto mb-2"><i class="fa-solid fa-box-open"></i></div>
                <span class="text-xs font-medium">কোনো চালানের তথ্য পাওয়া যায়নি।</span>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($consolidated as $name => $p): ?>
              <tr class="product-row hover:bg-slate-50/70 transition-colors" data-name="<?= h($name) ?>">
                
                <!-- Product Name Cell -->
                <td class="p-3 border-r border-slate-100 align-middle bg-white">
                  <div class="font-bold text-slate-800 text-xs sm:text-sm leading-snug">
                    <?= h($name) ?>
                  </div>
                  <div class="text-[10px] text-slate-400 font-medium mt-0.5">ট্রেড প্রাইস: ৳<?= number_format($p['trade_price'], 2) ?></div>
                </td>

                <!-- Load (Out) Cell -->
                <td class="p-3 text-center border-r border-slate-100 align-middle bg-slate-50/30">
                  <div class="font-bold text-slate-900 text-xs sm:text-sm">
                    <?= $formatQty($p['outside_qty'], $p['pcs_per_box']) ?>
                  </div>
                  <div class="text-[10.5px] text-blue-700 font-extrabold mt-0.5 font-mono">
                    ৳<?= number_format($p['outside_val'], 2) ?>
                  </div>
                </td>

                <!-- Regular Sell Cell -->
                <td class="p-3 text-center border-r border-slate-100 align-middle bg-slate-50/30">
                  <div class="font-bold text-slate-900 text-xs sm:text-sm">
                    <?= $formatQty($p['sale_qty'], $p['pcs_per_box']) ?>
                  </div>
                  <div class="text-[10.5px] text-emerald-700 font-extrabold mt-0.5 font-mono">
                    ৳<?= number_format($p['sale_val'], 2) ?>
                  </div>
                </td>

                <!-- Ready Sale Cell -->
                <td class="p-3 text-center border-r border-slate-100 align-middle bg-slate-50/30">
                  <div class="font-bold text-slate-900 text-xs sm:text-sm">
                    <?= $formatQty($p['ready_sale_qty'], $p['pcs_per_box']) ?>
                  </div>
                  <div class="text-[10.5px] text-amber-700 font-extrabold mt-0.5 font-mono">
                    ৳<?= number_format($p['ready_sale_val'], 2) ?>
                  </div>
                </td>

                <!-- Remaining (In) Cell -->
                <td class="p-3 text-center border-r border-slate-100 align-middle bg-slate-50/30">
                  <div class="font-bold text-slate-900 text-xs sm:text-sm">
                    <?= $formatQty($p['inside_qty'], $p['pcs_per_box']) ?>
                  </div>
                  <div class="text-[10.5px] text-purple-700 font-extrabold mt-0.5 font-mono">
                    ৳<?= number_format($p['inside_val'], 2) ?>
                  </div>
                </td>

                <!-- Damage Cell -->
                <td class="p-3 text-center align-middle bg-slate-50/30">
                  <div class="font-bold text-slate-900 text-xs sm:text-sm">
                    <?= $formatQty($p['damage_qty'], $p['pcs_per_box']) ?>
                  </div>
                  <div class="text-[10.5px] text-rose-600 font-extrabold mt-0.5 font-mono">
                    ৳<?= number_format($p['damage_val'], 2) ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>

        <!-- Footer Subtotal (Clean Slate Footer) -->
        <tfoot>
          <tr class="border-t border-slate-300 font-bold text-slate-900 text-xs bg-slate-100/90">
            <td class="p-3 text-center border-r border-slate-200 font-siliguri font-bold text-slate-600">
              সর্বমোট:
            </td>
            <td class="p-3 text-center border-r border-slate-200 text-blue-800 font-black font-mono">
              <div>৳<?= number_format($totals['outside'] ?? 0, 2) ?></div>
            </td>
            <td class="p-3 text-center border-r border-slate-200 text-emerald-800 font-black font-mono">
              <div>৳<?= number_format($totals['sale'] ?? 0, 2) ?></div>
            </td>
            <td class="p-3 text-center border-r border-slate-200 text-amber-800 font-black font-mono">
              <div>৳<?= number_format($totals['ready_sale'] ?? 0, 2) ?></div>
            </td>
            <td class="p-3 text-center border-r border-slate-200 text-purple-800 font-black font-mono">
              <div>৳<?= number_format($totals['inside'] ?? 0, 2) ?></div>
            </td>
            <td class="p-3 text-center text-rose-700 font-black font-mono">
              <div>৳<?= number_format($totals['damage'] ?? 0, 2) ?></div>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
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
