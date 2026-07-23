<?php $pageTitle = 'Orders History & Summary'; ?>

<div class="p-4 sm:p-5 space-y-4 pb-28 max-w-md mx-auto font-sans print:p-0 print:max-w-none print:bg-white">

  <?php
    // Pre-calculate Totals for Summary Banner
    $gTotalOrderedQty = 0;
    $gTotalDeliveredQty = 0;
    $gTotalVal = 0;
    $gDeliveredVal = 0;
    $gTotalOC = 0;
    $gTotalHappyComm = 0;
    $gTotalNetEarning = 0;

    foreach ($productSummary as $ps) {
      $gTotalOrderedQty += (int)$ps['qty'];
      $gTotalDeliveredQty += (int)$ps['delivered_qty'];
      $gTotalVal += (float)$ps['total_val'];
      $gDeliveredVal += (float)$ps['delivered_val'];
      $gTotalOC += (float)$ps['total_oc'];
      $gTotalHappyComm += (float)$ps['total_happy_comm'];
    }
    $gTotalNetEarning = $gTotalOC + $gTotalHappyComm;
  ?>

  <!-- 1. Top Header Bar -->
  <div class="flex items-center justify-between bg-white p-3.5 rounded-2xl border border-slate-200/90 shadow-2xs print:hidden">
    <div class="flex items-center gap-3">
      <a href="<?= url('sr/dashboard') ?>" class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-700 hover:bg-slate-200 transition">
        <i class="fa-solid fa-arrow-left text-sm"></i>
      </a>
      <div>
        <h1 class="text-base font-black text-slate-900 leading-tight">অর্ডার ইতিহাস ও সামারি</h1>
        <p class="text-[11px] text-slate-500 font-medium"><?= count($items) ?>টি অর্ডার · <?= count($productSummary) ?>টি পণ্য</p>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center gap-2">
      <button type="button" onclick="exportSummaryToExcel()" class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200/80 flex items-center justify-center text-sm shadow-2xs active:scale-95 transition" title="Excel ডাউনলোড">
        <i class="fa-solid fa-file-excel"></i>
      </button>
      <a href="<?= url('sr/retailers') ?>" class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 border border-blue-200/80 flex items-center justify-center text-sm shadow-2xs active:scale-95 transition" title="New Shop">
        <i class="fa-solid fa-store"></i>
      </a>
    </div>
  </div>

  <!-- 2. Date-wise Filter Strip -->
  <div class="space-y-2 print:hidden">
    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none">
      <?php
      $periodPills = [
        'all'       => 'সবগুলো',
        'today'     => 'আজকের',
        'yesterday' => 'গতকাল',
        'week'      => 'এই সপ্তাহের',
        'month'     => 'এই মাসের',
        'custom'    => 'কাস্টম তারিখ',
      ];
      $currPeriod = $period ?? 'all';
      foreach ($periodPills as $pKey => $pLabel):
        $activeCls = ($currPeriod === $pKey) 
          ? 'bg-blue-600 text-white font-black shadow-xs border-blue-600' 
          : 'bg-white text-slate-600 font-bold border-slate-200/90 hover:bg-slate-50';
      ?>
        <a href="<?= url('sr/orders') ?>?period=<?= $pKey ?>" 
           class="px-3.5 py-1.5 rounded-full text-[11px] border whitespace-nowrap transition-all duration-200 <?= $activeCls ?>">
          <?= $pLabel ?>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Custom Date Range Form -->
    <?php if ($currPeriod === 'custom'): ?>
      <form method="GET" action="<?= url('sr/orders') ?>" class="bg-white p-3 rounded-2xl border border-slate-200/90 shadow-2xs flex items-center gap-2">
        <input type="hidden" name="period" value="custom">
        <div class="flex-1 min-w-0">
          <label class="block text-[10px] font-bold text-slate-400 mb-0.5">হতে (From)</label>
          <input type="date" name="from" value="<?= h($from ?? '') ?>" required
                 class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1.5 text-xs font-bold text-slate-800 outline-none focus:border-blue-500">
        </div>
        <div class="flex-1 min-w-0">
          <label class="block text-[10px] font-bold text-slate-400 mb-0.5">পর্যন্ত (To)</label>
          <input type="date" name="to" value="<?= h($to ?? '') ?>" required
                 class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1.5 text-xs font-bold text-slate-800 outline-none focus:border-blue-500">
        </div>
        <button type="submit" class="self-end px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-2xs active:scale-95 transition">
          দেখুন
        </button>
      </form>
    <?php endif; ?>
  </div>

  <!-- 3. Dual Tab Navigation Bar -->
  <div class="bg-slate-100 p-1 rounded-2xl flex items-center border border-slate-200/80 print:hidden select-none">
    <button type="button" id="tabBtnSummary" onclick="switchTab('summary')"
            class="flex-1 py-2.5 rounded-xl text-xs font-black transition-all duration-200 flex items-center justify-center gap-2 bg-white text-blue-600 shadow-sm">
      <i class="fa-solid fa-boxes-stacked"></i>
      <span>প্রোডাক্ট সামারি</span>
    </button>
    <button type="button" id="tabBtnRetailers" onclick="switchTab('retailers')"
            class="flex-1 py-2.5 rounded-xl text-xs font-bold transition-all duration-200 flex items-center justify-center gap-2 text-slate-500 hover:text-slate-800">
      <i class="fa-solid fa-shop"></i>
      <span>দোকানভিত্তিক অর্ডার</span>
    </button>
  </div>

  <!-- ========================================================================= -->
  <!-- TAB 1: PRODUCT SUMMARY TAB CONTENT                                       -->
  <!-- ========================================================================= -->
  <div id="tabContentSummary" class="space-y-4 print:hidden">

    <!-- Unified Summary Hero Banner -->
    <div class="bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 text-white rounded-3xl p-5 shadow-xl shadow-blue-600/25 relative overflow-hidden space-y-3.5">
      
      <!-- Hero Main Value -->
      <div class="flex items-start justify-between relative z-10">
        <div>
          <div class="text-[11px] font-bold text-blue-100 tracking-wider uppercase flex items-center gap-1.5">
            <span>মোট অর্ডারের মূল্য</span>
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
          </div>
          <div class="text-3xl font-black tracking-tight mt-1 font-mono">
            ৳ <?= number_format($gTotalVal, 0) ?>
          </div>
          <div class="inline-flex items-center gap-1.5 text-[11px] font-extrabold text-amber-300 mt-1">
            <i class="fa-solid fa-sack-dollar"></i>
            <span>SR নিট লাভ: ৳ <?= number_format($gTotalNetEarning, 0) ?></span>
          </div>
        </div>

        <div class="w-11 h-11 rounded-2xl bg-white/15 backdrop-blur border border-white/20 flex items-center justify-center text-white text-lg shadow-2xs">
          <i class="fa-solid fa-file-invoice-dollar"></i>
        </div>
      </div>

      <!-- 3 Sub-Stats Strip inside Hero Card -->
      <div class="pt-3 border-t border-white/15 relative z-10 grid grid-cols-3 gap-2 text-center text-xs">
        <div class="bg-white/10 rounded-xl p-2 border border-white/10">
          <div class="text-[10px] text-blue-100 font-bold uppercase">অর্ডারকৃত পিস</div>
          <div class="font-black text-white font-mono mt-0.5"><?= number_format($gTotalOrderedQty) ?></div>
        </div>
        <div class="bg-white/10 rounded-xl p-2 border border-white/10">
          <div class="text-[10px] text-blue-100 font-bold uppercase">ডেলিভারিকৃত</div>
          <div class="font-black text-emerald-300 font-mono mt-0.5">৳ <?= number_format($gDeliveredVal, 0) ?></div>
        </div>
        <div class="bg-white/10 rounded-xl p-2 border border-white/10">
          <div class="text-[10px] text-blue-100 font-bold uppercase">দোকান সংখ্যা</div>
          <div class="font-black text-amber-300 font-mono mt-0.5"><?= $retailerCount ?>টি</div>
        </div>
      </div>

    </div>

    <!-- Search Bar for Products -->
    <div class="relative">
      <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
      <input type="text" id="gridSearchInput" onkeyup="filterExcelGrid()" placeholder="পণ্য বা কোম্পানির নাম লিখুন..."
        class="w-full bg-white border border-slate-200/90 rounded-2xl pl-9 pr-9 py-2.5 text-xs font-bold text-slate-800 placeholder:text-slate-400 focus:outline-none focus:border-blue-500 shadow-2xs transition" />
      <button type="button" onclick="clearSearch()" id="clearSearchBtn" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <!-- Product Summary List Cards -->
    <div class="space-y-2" id="productCardList">
      
      <?php if (empty($productSummary)): ?>
        <div class="bg-white rounded-2xl p-8 text-center border border-slate-200/90 shadow-2xs space-y-2">
          <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center mx-auto">
            <i class="fa-solid fa-boxes-stacked text-2xl"></i>
          </div>
          <h3 class="text-sm font-black text-slate-800">কোনো অর্ডারের তথ্য নেই</h3>
          <p class="text-xs text-slate-500">নির্বাচিত তারিখে কোনো অর্ডারের সামারি পাওয়া যায়নি।</p>
          <a href="<?= url('sr/sales') ?>" class="inline-block text-xs font-black text-blue-600 bg-blue-50 border border-blue-200/80 px-4 py-2 rounded-xl mt-2">অর্ডার শুরু করুন &rarr;</a>
        </div>
      <?php else: ?>

        <?php 
          $rowIdx = 0;
          foreach ($productSummary as $ps): 
            $rowIdx++;
            $qty = (int)$ps['qty'];
            $ppb = (int)$ps['ppb'] ?: 1;
            
            $ordBoxes = floor($qty / $ppb);
            $ordPcs = $qty % $ppb;
            $ordQtyStr = ($ordBoxes > 0 ? $ordBoxes . ' কার্টন ' : '') . ($ordPcs > 0 || $ordBoxes == 0 ? $ordPcs . ' পিস' : '');

            $totalVal = (float)$ps['total_val'];
            $totalOc = (float)$ps['total_oc'];
            $happyComm = (float)$ps['total_happy_comm'];
            $netEarning = $totalOc + $happyComm;
        ?>

        <!-- Product Card -->
        <div onclick="openProductDetailModal(<?= htmlspecialchars(json_encode($ps), ENT_QUOTES, 'UTF-8') ?>)" 
             class="bg-white p-3.5 rounded-2xl border border-slate-200/90 shadow-2xs flex items-center justify-between gap-3 hover:border-blue-400 cursor-pointer transition active:scale-[0.99] grid-row-item"
             data-name="<?= strtolower(h($ps['name'])) ?>"
             data-company="<?= strtolower(h($ps['company'])) ?>">
          
          <div class="flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 border border-blue-200/60 flex items-center justify-center font-black text-xs shrink-0 font-mono">
              #<?= $rowIdx ?>
            </div>
            <div class="min-w-0">
              <h3 class="text-xs font-extrabold text-slate-900 truncate leading-tight"><?= h($ps['name']) ?></h3>
              <div class="text-[11px] text-slate-500 font-medium mt-0.5 flex items-center gap-1.5">
                <span class="bg-slate-100 text-slate-700 px-1.5 py-0.2 rounded font-bold"><?= h($ps['company']) ?></span>
                <span>·</span>
                <span class="font-bold text-slate-700"><?= $ordQtyStr ?></span>
              </div>
            </div>
          </div>

          <div class="text-right shrink-0">
            <div class="text-xs font-black text-blue-700 font-mono">৳ <?= number_format($totalVal, 0) ?></div>
            <div class="text-[10px] font-extrabold text-emerald-600 mt-0.5">
              লাভ: ৳ <?= number_format($netEarning, 0) ?>
            </div>
          </div>

        </div>

        <?php endforeach; ?>

      <?php endif; ?>

    </div>

  </div>


  <!-- ========================================================================= -->
  <!-- TAB 2: RETAILER ORDERS TAB CONTENT                                       -->
  <!-- ========================================================================= -->
  <div id="tabContentRetailers" class="space-y-3 hidden print:hidden">
    
    <!-- Search Bar for Retailer Orders -->
    <div class="relative">
      <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
      <input type="text" id="retailerSearchInput" onkeyup="filterRetailerOrders()" placeholder="দোকানের নাম, ফোন নম্বর বা আইডি..."
        class="w-full bg-white border border-slate-200/90 rounded-2xl pl-9 pr-9 py-2.5 text-xs font-bold text-slate-800 placeholder:text-slate-400 focus:outline-none focus:border-blue-500 shadow-2xs transition" />
      <button type="button" onclick="clearRetailerSearch()" id="clearRetailerSearchBtn" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <!-- Retailer Orders List Cards -->
    <div class="space-y-3" id="retailerOrderCardList">
      
      <?php if (empty($items)): ?>
        <div class="bg-white rounded-2xl p-8 text-center border border-slate-200/90 shadow-2xs space-y-2">
          <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center mx-auto">
            <i class="fa-solid fa-shop text-2xl"></i>
          </div>
          <h3 class="text-sm font-black text-slate-800">কোনো দোকানভিত্তিক অর্ডার নেই</h3>
          <p class="text-xs text-slate-500">নির্বাচিত তারিখের মধ্যে কোনো অর্ডার পাওয়া যায়নি।</p>
        </div>
      <?php else: ?>

        <?php 
        $statusLabels = [
          'pending'    => ['label' => 'প্যান্ডিং', 'class' => 'bg-amber-50 text-amber-700 border-amber-200'],
          'confirmed'  => ['label' => 'কনফার্মড', 'class' => 'bg-blue-50 text-blue-700 border-blue-200'],
          'dispatched' => ['label' => 'ডিসপ্যাচড', 'class' => 'bg-indigo-50 text-indigo-700 border-indigo-200'],
          'delivered'  => ['label' => 'ডেলিভার্ড', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
          'cancelled'  => ['label' => 'বাতিল', 'class' => 'bg-rose-50 text-rose-700 border-rose-200'],
        ];

        foreach ($items as $ord): 
          $rName = !empty($ord['retailer_name']) ? $ord['retailer_name'] : (!empty($ord['dealer_name']) ? $ord['dealer_name'] : 'সাধারণ কাস্টমার');
          $rPhone = !empty($ord['retailer_phone']) ? $ord['retailer_phone'] : 'N/A';
          $rAddress = !empty($ord['retailer_address']) ? $ord['retailer_address'] : 'ঠিকানা দেওয়া নেই';
          $stInfo = $statusLabels[$ord['status']] ?? ['label' => ucfirst($ord['status']), 'class' => 'bg-slate-100 text-slate-700 border-slate-200'];

          // Total items & quantity
          $totalItemsCount = count($ord['products'] ?? []);
          $totalPcs = 0;
          foreach ($ord['products'] as $op) {
            $totalPcs += (int)$op['quantity'];
          }
        ?>

        <!-- Single Retailer Order Card -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4 space-y-3 hover:border-blue-300 transition retailer-order-card"
             data-rname="<?= strtolower(h($rName)) ?>"
             data-rphone="<?= strtolower(h($rPhone)) ?>"
             data-ordid="#ord-<?= $ord['id'] ?>">
          
          <!-- Top Row: Retailer Name & Status -->
          <div class="flex items-start justify-between gap-2 border-b border-slate-100 pb-2.5">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-50 to-slate-100 border border-slate-200/80 flex items-center justify-center text-blue-600 font-bold text-sm shrink-0">
                <i class="fa-solid fa-store"></i>
              </div>
              <div>
                <h3 class="text-xs font-black text-slate-900 leading-tight"><?= h($rName) ?></h3>
                <div class="text-[11px] text-slate-500 font-medium mt-0.5 flex items-center gap-2">
                  <span><i class="fa-solid fa-phone text-[10px] text-slate-400 mr-1"></i><?= h($rPhone) ?></span>
                </div>
              </div>
            </div>

            <!-- Status Pill -->
            <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold border <?= $stInfo['class'] ?>">
              <?= $stInfo['label'] ?>
            </span>
          </div>

          <!-- Middle Details: Order ID, Date & Item Summary -->
          <div class="flex items-center justify-between text-xs bg-slate-50/80 p-2.5 rounded-xl border border-slate-100 font-medium text-slate-700">
            <div>
              <div class="font-mono text-slate-900 font-extrabold">অর্ডার #ORD-<?= $ord['id'] ?></div>
              <div class="text-[10px] text-slate-500 font-bold mt-0.5">
                <?= date('d M Y, g:i A', strtotime($ord['created_at'])) ?>
              </div>
            </div>
            <div class="text-right">
              <div class="font-mono font-black text-blue-700">৳ <?= number_format((float)$ord['total_amount'], 2) ?></div>
              <div class="text-[10px] text-slate-500 font-bold mt-0.5"><?= $totalItemsCount ?>টি পণ্য (<?= $totalPcs ?> পিস)</div>
            </div>
          </div>

          <!-- Bottom Action: Open Beautiful Invoice -->
          <div class="flex items-center justify-end pt-1">
            <button type="button" 
                    onclick="openInvoiceModal(<?= htmlspecialchars(json_encode($ord), ENT_QUOTES, 'UTF-8') ?>)"
                    class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 active:scale-98 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-2 shadow-sm shadow-blue-600/20 transition">
              <i class="fa-solid fa-file-invoice"></i>
              <span>ইনভয়েস দেখুন (View Invoice)</span>
            </button>
          </div>

        </div>

        <?php endforeach; ?>

      <?php endif; ?>

    </div>

  </div>

</div>


<!-- ========================================================================= -->
<!-- 1. PRODUCT DETAIL MODAL (Tab 1)                                          -->
<!-- ========================================================================= -->
<div id="productDetailModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-[100] hidden opacity-0 transition-opacity duration-200 flex items-center justify-center p-4">
  <div id="productModalContent" class="bg-white w-full max-w-sm rounded-3xl p-5 shadow-2xl space-y-4 transform scale-95 transition-transform duration-200 border border-slate-200">
    
    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
      <div>
        <h3 id="modalProductName" class="text-sm font-black text-slate-900 leading-tight">পণ্যের বিবরণ</h3>
        <p id="modalProductCompany" class="text-[11px] text-slate-500 font-medium mt-0.5">কোম্পানির নাম</p>
      </div>
      <button onclick="closeProductDetailModal()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center hover:bg-slate-200 transition">
        <i class="fa-solid fa-xmark text-sm"></i>
      </button>
    </div>

    <div class="grid grid-cols-3 gap-2 bg-slate-50 p-2.5 rounded-2xl border border-slate-200/60 text-center">
      <div>
        <div class="text-[10px] text-slate-400 font-bold uppercase">মোট পিস</div>
        <div id="modalStatQty" class="text-xs font-black text-slate-900 font-mono mt-0.5">0</div>
      </div>
      <div>
        <div class="text-[10px] text-slate-400 font-bold uppercase">মোট বিক্রি</div>
        <div id="modalStatSales" class="text-xs font-black text-blue-700 font-mono mt-0.5">৳ 0</div>
      </div>
      <div>
        <div class="text-[10px] text-slate-400 font-bold uppercase">O/C কমিশন</div>
        <div id="modalStatOC" class="text-xs font-black text-emerald-700 font-mono mt-0.5">৳ 0</div>
      </div>
    </div>

    <div class="space-y-2">
      <div class="text-[11px] font-black text-slate-400 uppercase tracking-wider">দোকান ভিত্তিক বিস্তারিত</div>
      <div id="modalOrderLinesContainer" class="space-y-2 max-h-60 overflow-y-auto pr-1 scrollbar-none">
      </div>
    </div>

    <button onclick="closeProductDetailModal()" class="w-full py-2.5 bg-slate-900 text-white rounded-xl font-bold text-xs active:scale-95 transition">
      বন্ধ করুন
    </button>
  </div>
</div>


<!-- ========================================================================= -->
<!-- 2. BEAUTIFUL RETAILER INVOICE MODAL (Tab 2)                               -->
<!-- ========================================================================= -->
<div id="invoiceModal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-[110] hidden opacity-0 transition-opacity duration-200 flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
  
  <div id="invoiceModalContent" class="bg-white w-full max-w-lg rounded-3xl p-5 sm:p-6 shadow-2xl space-y-5 transform scale-95 transition-transform duration-200 border border-slate-200 my-auto text-slate-800">
    
    <!-- Printable Invoice Container -->
    <div id="printableInvoiceArea" class="space-y-5 bg-white">
      
      <!-- Invoice Header Banner -->
      <div class="flex items-start justify-between border-b-2 border-slate-900 pb-4">
        <div>
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center font-black text-sm">
              <i class="fa-solid fa-truck-fast"></i>
            </div>
            <span class="text-lg font-black text-slate-900 tracking-tight">HappyBD DMS</span>
          </div>
          <p class="text-[10px] text-slate-500 font-semibold mt-1">FMCG Distribution Management System</p>
        </div>

        <div class="text-right">
          <div class="inline-block px-3 py-1 bg-blue-600 text-white font-black text-xs uppercase tracking-wider rounded-lg shadow-sm">
            অর্ডার চালানি ইনভয়েস
          </div>
          <div class="text-xs font-extrabold font-mono text-slate-900 mt-1.5" id="invOrderId">#ORD-0000</div>
          <div class="text-[11px] text-slate-500 font-medium" id="invDate">00 Jan 2026, 1:50 PM</div>
        </div>
      </div>

      <!-- Retailer & SR Metadata Grid -->
      <div class="grid grid-cols-2 gap-3 bg-slate-50 p-3.5 rounded-2xl border border-slate-200/80 text-xs">
        <!-- Customer Info -->
        <div>
          <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">কাস্টমার / দোকান বিবরণ</div>
          <div class="font-black text-slate-900 text-sm leading-tight" id="invRetailerName">Hunaima Store</div>
          <div class="text-slate-600 font-bold mt-1" id="invRetailerPhone">01700000000</div>
          <div class="text-slate-500 text-[11px] mt-0.5 leading-snug" id="invRetailerAddress">ঠিকানা দেওয়া নেই</div>
        </div>

        <!-- Order & SR Info -->
        <div class="border-l border-slate-200/80 pl-3">
          <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">অর্ডার বিবরণ</div>
          <div class="flex items-center gap-1.5 mb-1">
            <span class="text-slate-500">স্ট্যাটাস:</span>
            <span id="invStatusBadge" class="font-extrabold px-2 py-0.5 rounded-md text-[10px] bg-emerald-100 text-emerald-800">
              ডেলিভার্ড
            </span>
          </div>
          <div class="text-[11px] text-slate-700 font-semibold" id="invDealerName">ডিলার: General Dealer</div>
          <div class="text-[11px] text-slate-700 font-semibold" id="invSRName">SR: <?= h(Auth::name()) ?></div>
        </div>
      </div>

      <!-- Itemized Products Table -->
      <div class="overflow-x-auto border border-slate-200 rounded-2xl">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-100 text-slate-700 font-extrabold border-b border-slate-200 text-[11px]">
              <th class="py-2.5 px-3">#</th>
              <th class="py-2.5 px-3">পণ্যের বিবরণ</th>
              <th class="py-2.5 px-3 text-center">প্যাকিং</th>
              <th class="py-2.5 px-3 text-center">মোট পিস</th>
              <th class="py-2.5 px-3 text-right">দর (৳)</th>
              <th class="py-2.5 px-3 text-right">মোট (৳)</th>
            </tr>
          </thead>
          <tbody id="invItemsTableBody" class="divide-y divide-slate-100 text-slate-800">
            <!-- JS will populate rows -->
          </tbody>
        </table>
      </div>

      <!-- Invoice Financial Summary Footer -->
      <div class="flex justify-end pt-1">
        <div class="w-full sm:w-64 bg-slate-50 p-3.5 rounded-2xl border border-slate-200/80 space-y-1.5 text-xs font-medium">
          <div class="flex justify-between text-slate-600">
            <span>মোট আইটেম সংখ্যা:</span>
            <span class="font-bold text-slate-900" id="invTotalItems">0টি</span>
          </div>
          <div class="flex justify-between text-slate-600">
            <span>মোট পিস:</span>
            <span class="font-bold text-slate-900" id="invTotalQtyPcs">0 পিস</span>
          </div>
          <div class="border-t border-slate-200 pt-1.5 flex justify-between items-center text-sm font-black text-slate-900">
            <span>সর্বমোট টাকা:</span>
            <span class="text-blue-700 font-mono text-base" id="invGrandTotal">৳ 0.00</span>
          </div>
        </div>
      </div>

      <!-- Auth Signatures / Footer text -->
      <div class="pt-6 border-t border-slate-200 flex justify-between items-end text-[10px] text-slate-400">
        <div>
          <div>ধন্যবাদ HappyBangladesh DMS-এর সাথে থাকার জন্য।</div>
          <div>Computer Generated Official Invoice.</div>
        </div>
        <div class="text-center">
          <div class="border-b border-slate-300 w-28 mb-1"></div>
          <div class="font-bold text-slate-600">প্রতিনিধির স্বাক্ষর</div>
        </div>
      </div>

    </div>

    <!-- Modal Footer Actions (Non-printable) -->
    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 print:hidden">
      <button type="button" onclick="window.print()" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-sm transition">
        <i class="fa-solid fa-print"></i>
        <span>প্রিন্ট করুন</span>
      </button>
      <button type="button" onclick="closeInvoiceModal()" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 active:scale-95 text-white rounded-xl text-xs font-bold transition">
        বন্ধ করুন
      </button>
    </div>

  </div>
</div>


<!-- Style for Print View -->
<style>
@media print {
  body * {
    visibility: hidden;
  }
  #invoiceModal, #invoiceModal * {
    visibility: visible;
  }
  #invoiceModal {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: auto;
    background: white !important;
    padding: 0 !important;
  }
  #invoiceModalContent {
    box-shadow: none !important;
    border: none !important;
    max-width: 100% !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 20px !important;
  }
  .print\:hidden {
    display: none !important;
  }
}
</style>

<script>
// ── 12-Hour Time Format Helper (e.g. 23 Jul 2026, 1:50 PM) ───────────────────
function formatDateTime12Hr(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr.replace(/-/g, '/'));
  if (isNaN(d.getTime())) return dateStr;
  
  const day = d.getDate();
  const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  const month = monthNames[d.getMonth()];
  const year = d.getFullYear();
  
  let hours = d.getHours();
  const minutes = String(d.getMinutes()).padStart(2, '0');
  const ampm = hours >= 12 ? 'PM' : 'AM';
  hours = hours % 12;
  hours = hours ? hours : 12; // 0 becomes 12
  
  return `${day} ${month} ${year}, ${hours}:${minutes} ${ampm}`;
}

// ── Tab Switcher Logic ────────────────────────────────────────────────────────
function switchTab(tabName) {
  const summaryTab = document.getElementById('tabContentSummary');
  const retailersTab = document.getElementById('tabContentRetailers');
  const btnSummary = document.getElementById('tabBtnSummary');
  const btnRetailers = document.getElementById('tabBtnRetailers');

  if (tabName === 'summary') {
    summaryTab.classList.remove('hidden');
    retailersTab.classList.add('hidden');

    btnSummary.className = "flex-1 py-2.5 rounded-xl text-xs font-black transition-all duration-200 flex items-center justify-center gap-2 bg-white text-blue-600 shadow-sm";
    btnRetailers.className = "flex-1 py-2.5 rounded-xl text-xs font-bold transition-all duration-200 flex items-center justify-center gap-2 text-slate-500 hover:text-slate-800";
  } else {
    summaryTab.classList.add('hidden');
    retailersTab.classList.remove('hidden');

    btnRetailers.className = "flex-1 py-2.5 rounded-xl text-xs font-black transition-all duration-200 flex items-center justify-center gap-2 bg-white text-blue-600 shadow-sm";
    btnSummary.className = "flex-1 py-2.5 rounded-xl text-xs font-bold transition-all duration-200 flex items-center justify-center gap-2 text-slate-500 hover:text-slate-800";
  }
}

// ── Search Filters ─────────────────────────────────────────────────────────────
function filterExcelGrid() {
  const query = document.getElementById('gridSearchInput').value.toLowerCase().trim();
  const items = document.querySelectorAll('.grid-row-item');
  const clearBtn = document.getElementById('clearSearchBtn');
  
  if (query.length > 0) clearBtn.classList.remove('hidden');
  else clearBtn.classList.add('hidden');

  items.forEach(item => {
    const name = item.getAttribute('data-name') || '';
    const company = item.getAttribute('data-company') || '';
    if (name.includes(query) || company.includes(query)) {
      item.classList.remove('hidden');
    } else {
      item.classList.add('hidden');
    }
  });
}

function clearSearch() {
  document.getElementById('gridSearchInput').value = '';
  filterExcelGrid();
}

function filterRetailerOrders() {
  const query = document.getElementById('retailerSearchInput').value.toLowerCase().trim();
  const cards = document.querySelectorAll('.retailer-order-card');
  const clearBtn = document.getElementById('clearRetailerSearchBtn');

  if (query.length > 0) clearBtn.classList.remove('hidden');
  else clearBtn.classList.add('hidden');

  cards.forEach(card => {
    const name = card.getAttribute('data-rname') || '';
    const phone = card.getAttribute('data-rphone') || '';
    const ordId = card.getAttribute('data-ordid') || '';

    if (name.includes(query) || phone.includes(query) || ordId.includes(query)) {
      card.classList.remove('hidden');
    } else {
      card.classList.add('hidden');
    }
  });
}

function clearRetailerSearch() {
  document.getElementById('retailerSearchInput').value = '';
  filterRetailerOrders();
}

// ── Product Detail Modal (Tab 1) ──────────────────────────────────────────────
function openProductDetailModal(product) {
  const modal = document.getElementById('productDetailModal');
  const modalContent = document.getElementById('productModalContent');

  document.getElementById('modalProductName').innerText = product.name || 'পণ্যের বিবরণ';
  document.getElementById('modalProductCompany').innerText = (product.company || 'General') + ' · ১ কার্টন = ' + (product.ppb || 1) + ' পিস';
  document.getElementById('modalStatQty').innerText = (product.qty || 0).toLocaleString() + ' Pcs';
  document.getElementById('modalStatSales').innerText = '৳ ' + (product.total_val || 0).toLocaleString();
  document.getElementById('modalStatOC').innerText = '৳ ' + (product.total_oc || 0).toLocaleString();

  const linesContainer = document.getElementById('modalOrderLinesContainer');
  linesContainer.innerHTML = '';

  if (product.order_lines && product.order_lines.length > 0) {
    product.order_lines.forEach(line => {
      const ocText = line.item_oc != 0 
        ? `<span class="${line.item_oc < 0 ? 'text-rose-600 bg-rose-50' : 'text-emerald-600 bg-emerald-50'} text-[10px] font-extrabold px-1.5 py-0.5 rounded">O/C ${line.item_oc > 0 ? '+' : ''}${Math.round(line.item_oc)}</span>` 
        : '';

      const div = document.createElement('div');
      div.className = 'bg-slate-50 p-2.5 rounded-xl border border-slate-200/80 flex items-center justify-between text-xs';
      div.innerHTML = `
        <div>
          <div class="font-extrabold text-slate-900 flex items-center gap-1.5">
            <span>${line.dealer_name}</span>
            <span class="text-[10px] text-slate-400 font-mono">#${line.order_id}</span>
          </div>
          <div class="text-[10px] text-slate-500 font-medium mt-0.5">
            ${formatDateTime12Hr(line.created_at)}
          </div>
        </div>
        <div class="text-right font-mono">
          <div class="font-black text-slate-900">${line.qty} Pcs @ ৳ ${line.unit_price}</div>
          <div class="text-[11px] text-blue-600 font-bold">৳ ${Number(line.item_val).toLocaleString()} ${ocText}</div>
        </div>
      `;
      linesContainer.appendChild(div);
    });
  } else {
    linesContainer.innerHTML = '<div class="text-center py-6 text-slate-400 text-xs font-bold">কোনো অর্ডার পাওয়া যায়নি।</div>';
  }

  modal.classList.remove('hidden');
  setTimeout(() => {
    modal.classList.remove('opacity-0');
    modalContent.classList.remove('scale-95');
    modalContent.classList.add('scale-100');
  }, 10);
}

function closeProductDetailModal() {
  const modal = document.getElementById('productDetailModal');
  const modalContent = document.getElementById('productModalContent');

  modalContent.classList.remove('scale-100');
  modalContent.classList.add('scale-95');
  modal.classList.add('opacity-0');
  setTimeout(() => {
    modal.classList.add('hidden');
  }, 200);
}

// ── Beautiful Retailer Invoice Modal (Tab 2) ──────────────────────────────────
function openInvoiceModal(order) {
  const modal = document.getElementById('invoiceModal');
  const modalContent = document.getElementById('invoiceModalContent');

  // Order & Retailer Header Data
  const retailerName = order.retailer_name || order.dealer_name || 'সাধারণ কাস্টমার';
  const retailerPhone = order.retailer_phone || 'N/A';
  const retailerAddress = order.retailer_address || 'ঠিকানা দেওয়া নেই';

  document.getElementById('invOrderId').innerText = '#ORD-' + order.id;
  document.getElementById('invDate').innerText = formatDateTime12Hr(order.created_at);
  document.getElementById('invRetailerName').innerText = retailerName;
  document.getElementById('invRetailerPhone').innerText = retailerPhone;
  document.getElementById('invRetailerAddress').innerText = retailerAddress;
  document.getElementById('invDealerName').innerText = 'ডিলার: ' + (order.dealer_name || 'Direct');

  // Status Badge
  const stBadge = document.getElementById('invStatusBadge');
  const statusMap = {
    'pending': { label: 'প্যান্ডিং', cls: 'bg-amber-100 text-amber-800' },
    'confirmed': { label: 'কনফার্মড', cls: 'bg-blue-100 text-blue-800' },
    'dispatched': { label: 'ডিসপ্যাচড', cls: 'bg-indigo-100 text-indigo-800' },
    'delivered': { label: 'ডেলিভার্ড', cls: 'bg-emerald-100 text-emerald-800' },
    'cancelled': { label: 'বাতিল', cls: 'bg-rose-100 text-rose-800' }
  };
  const stInfo = statusMap[order.status] || { label: order.status, cls: 'bg-slate-100 text-slate-800' };
  stBadge.innerText = stInfo.label;
  stBadge.className = 'font-extrabold px-2.5 py-0.5 rounded-md text-[10px] ' + stInfo.cls;

  // Populate Table Rows
  const tableBody = document.getElementById('invItemsTableBody');
  tableBody.innerHTML = '';

  let totalQtyPcs = 0;
  let totalItemsCount = 0;

  if (order.products && order.products.length > 0) {
    totalItemsCount = order.products.length;
    order.products.forEach((prod, index) => {
      const qty = parseInt(prod.quantity || 0);
      const ppb = parseInt(prod.pieces_per_box || 1) || 1;
      totalQtyPcs += qty;

      const boxes = Math.floor(qty / ppb);
      const pcs = qty % ppb;
      const packingStr = (boxes > 0 ? boxes + ' কার্টন ' : '') + (pcs > 0 || boxes === 0 ? pcs + ' পিস' : '');
      const itemTotal = parseFloat(prod.total_price || (qty * parseFloat(prod.unit_price || 0)));

      const tr = document.createElement('tr');
      tr.className = index % 2 === 0 ? 'bg-white' : 'bg-slate-50/50';
      tr.innerHTML = `
        <td class="py-2.5 px-3 font-mono font-bold text-slate-400">${index + 1}</td>
        <td class="py-2.5 px-3">
          <div class="font-extrabold text-slate-900">${prod.product_name || 'পণ্য'}</div>
          <div class="text-[10px] text-slate-400 font-semibold">${prod.company_name || 'General'}</div>
        </td>
        <td class="py-2.5 px-3 text-center font-bold text-slate-700">${packingStr}</td>
        <td class="py-2.5 px-3 text-center font-mono font-bold">${qty} পিস</td>
        <td class="py-2.5 px-3 text-right font-mono">৳ ${parseFloat(prod.unit_price || 0).toLocaleString()}</td>
        <td class="py-2.5 px-3 text-right font-mono font-extrabold text-slate-900">৳ ${itemTotal.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
      `;
      tableBody.appendChild(tr);
    });
  } else {
    tableBody.innerHTML = '<tr><td colspan="6" class="py-4 text-center text-slate-400 text-xs font-bold">কোনো আইটেম পাওয়া যায়নি।</td></tr>';
  }

  // Summary Totals
  document.getElementById('invTotalItems').innerText = totalItemsCount + 'টি';
  document.getElementById('invTotalQtyPcs').innerText = totalQtyPcs + ' পিস';
  document.getElementById('invGrandTotal').innerText = '৳ ' + parseFloat(order.total_amount || 0).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});

  modal.classList.remove('hidden');
  setTimeout(() => {
    modal.classList.remove('opacity-0');
    modalContent.classList.remove('scale-95');
    modalContent.classList.add('scale-100');
  }, 10);
}

function closeInvoiceModal() {
  const modal = document.getElementById('invoiceModal');
  const modalContent = document.getElementById('invoiceModalContent');

  modalContent.classList.remove('scale-100');
  modalContent.classList.add('scale-95');
  modal.classList.add('opacity-0');
  setTimeout(() => {
    modal.classList.add('hidden');
  }, 200);
}

// ── Export CSV Summary ────────────────────────────────────────────────────────
function exportSummaryToExcel() {
  const productData = <?= json_encode($productSummary) ?>;
  if (!productData || productData.length === 0) {
    alert('ডাউনলোড করার মতো কোনো ডাটা নেই।');
    return;
  }

  let csvContent = "\uFEFF";
  csvContent += "Row #,Product Name,Company,Pieces Per Carton,Ordered Qty (Pcs),Delivered Qty (Pcs),Base Price (TK),Unit Selling Price (TK),Total Sales Value (TK),Over Commission OC (TK),Happy Commission (TK),Net SR Earning (TK),Delivery Completion (%)\n";

  let rowIdx = 0;
  let totQty = 0, totDelQty = 0, totSales = 0, totOC = 0, totHappy = 0, totNet = 0;

  productData.forEach(p => {
    rowIdx++;
    const qty = parseInt(p.qty || 0);
    const delQty = parseInt(p.delivered_qty || 0);
    const totalVal = parseFloat(p.total_val || 0);
    const totalOc = parseFloat(p.total_oc || 0);
    const happyComm = parseFloat(p.total_happy_comm || 0);
    const net = totalOc + happyComm;
    const delRate = qty > 0 ? ((delQty / qty) * 100).toFixed(1) : 0;

    totQty += qty;
    totDelQty += delQty;
    totSales += totalVal;
    totOC += totalOc;
    totHappy += happyComm;
    totNet += net;

    const name = `"${(p.name || '').replace(/"/g, '""')}"`;
    const company = `"${(p.company || '').replace(/"/g, '""')}"`;

    csvContent += `${rowIdx},${name},${company},${p.ppb || 1},${qty},${delQty},${p.base_price || 0},${p.unit_price || 0},${totalVal},${totalOc},${happyComm},${net},${delRate}%\n`;
  });

  const gDelRate = totQty > 0 ? ((totDelQty / totQty) * 100).toFixed(1) : 0;
  csvContent += `TOTAL,=SUM ALL PRODUCTS,,--,,${totQty},${totDelQty},--,--,${totSales},${totOC},${totHappy},${totNet},${gDelRate}%\n`;

  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  const dateStr = new Date().toISOString().slice(0, 10);
  link.setAttribute("href", url);
  link.setAttribute("download", `Product_Sales_Summary_${dateStr}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}
</script>
