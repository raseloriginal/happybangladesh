<?php $pageTitle = 'My Orders'; ?>

<div class="p-4 sm:p-5 space-y-4 pb-28 max-w-md mx-auto">

  <!-- 1. Header Bar -->
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-3">
      <a href="<?= url('sr/dashboard') ?>" class="w-10 h-10 rounded-full bg-white shadow-sm border border-slate-200 flex items-center justify-center text-slate-700 hover:bg-slate-50 transition">
        <i class="fa-solid fa-arrow-left"></i>
      </a>
      <div>
        <h1 class="text-lg font-black text-slate-900 leading-tight">My Orders</h1>
        <p class="text-xs text-slate-400 font-medium"><?= count($items) ?> total order<?= count($items)!==1?'s':'' ?></p>
      </div>
    </div>

    <a href="<?= url('sr/sales') ?>" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-3.5 py-2 rounded-xl flex items-center gap-1.5 shadow-md shadow-blue-600/20 transition">
      <i class="fa-solid fa-plus"></i> New Sale
    </a>
  </div>

  <!-- 2. Main Tab Navigation Switcher (2 Tabs) -->
  <div class="bg-slate-200/80 p-1 rounded-2xl flex items-center gap-1">
    <button type="button" id="tabBtnSummary" onclick="switchMainTab('summary')"
      class="flex-1 py-2.5 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 bg-white text-blue-600 shadow-sm">
      <i class="fa-solid fa-chart-pie text-sm"></i>
      <span>Order Summary</span>
    </button>

    <button type="button" id="tabBtnDate" onclick="switchMainTab('date')"
      class="flex-1 py-2.5 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 text-slate-600 hover:text-slate-900">
      <i class="fa-solid fa-calendar-days text-sm"></i>
      <span>Orders by Date</span>
    </button>
  </div>

  <!-- ==================== TAB 1: ORDER SUMMARY ==================== -->
  <div id="tabContentSummary" class="space-y-4">
    
    <?php
      $totalVal = 0;
      $totalDeliveredVal = 0;
      $totalOverCommission = 0;
      $totalHappyCommission = 0;
      $totalOrderedPieces = 0;
      $totalDeliveredPieces = 0;

      $totalOrdersCount = count($items);
      $pendingCnt = 0;
      $confirmedCnt = 0;
      $deliveredCnt = 0;

      foreach ($items as $o) {
        $amt = (float)$o['total_amount'];
        $totalVal += $amt;

        if ($o['status'] === 'pending') {
          $pendingCnt++;
        } elseif ($o['status'] === 'confirmed') {
          $confirmedCnt++;
        } elseif ($o['status'] === 'delivered') {
          $deliveredCnt++;
          $totalDeliveredVal += $amt;
        }

        // Happy Commission
        $comm_pct = (float)($o['happy_commission'] ?? 0);
        $totalHappyCommission += $amt * ($comm_pct / 100);

        // O/C and Quantities
        if (!empty($o['products'])) {
          foreach ($o['products'] as $p) {
            $qty = (int)$p['quantity'];
            $base_price = (float)$p['base_price'];
            $unit_price = (float)$p['unit_price'];
            $item_oc = ($unit_price - $base_price) * $qty;
            
            $totalOverCommission += $item_oc;
            $totalOrderedPieces += $qty;
            
            if ($o['status'] === 'delivered' || $o['status'] === 'confirmed') {
              $totalDeliveredPieces += $qty;
            }
          }
        }
      }
    ?>

    <!-- 1. Key Summary Cards Grid -->
    <div class="grid grid-cols-2 gap-3">
      
      <!-- Card 1: Total Order Value -->
      <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between">
        <div class="flex items-center justify-between mb-2">
          <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm">
            <i class="fa-solid fa-bangladeshi-taka-sign"></i>
          </div>
          <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">Total Value</span>
        </div>
        <div>
          <div class="text-xl font-black text-slate-900">৳ <?= number_format($totalVal, 0) ?></div>
          <div class="text-[11px] font-medium text-slate-500 mt-0.5">Total Order Value</div>
        </div>
      </div>

      <!-- Card 2: Order Qty vs Delivered Qty -->
      <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between">
        <div class="flex items-center justify-between mb-2">
          <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center text-sm">
            <i class="fa-solid fa-truck-ramp-box"></i>
          </div>
          <span class="text-[10px] font-bold bg-teal-50 text-teal-700 px-2 py-0.5 rounded-full">Quantities</span>
        </div>
        <div>
          <div class="text-lg font-black text-slate-900 flex items-center gap-1">
            <span><?= $totalOrderedPieces ?></span>
            <span class="text-xs text-slate-400 font-bold">/ <?= $totalDeliveredPieces ?> Delv</span>
          </div>
          <div class="text-[11px] font-medium text-slate-500 mt-0.5">Order Qty / Delivered</div>
        </div>
      </div>

    </div>

    <!-- 2. Status Breakdown Strip -->
    <div class="bg-white rounded-2xl p-3.5 border border-slate-100 shadow-sm grid grid-cols-4 gap-2 text-center">
      <div>
        <div class="text-xs font-black text-slate-900"><?= $totalOrdersCount ?></div>
        <div class="text-[10px] font-medium text-slate-400">Total</div>
      </div>
      <div>
        <div class="text-xs font-black text-amber-600"><?= $pendingCnt ?></div>
        <div class="text-[10px] font-medium text-slate-400">Pending</div>
      </div>
      <div>
        <div class="text-xs font-black text-emerald-600"><?= $confirmedCnt ?></div>
        <div class="text-[10px] font-medium text-slate-400">Confirmed</div>
      </div>
      <div>
        <div class="text-xs font-black text-blue-600"><?= $deliveredCnt ?></div>
        <div class="text-[10px] font-medium text-slate-400">Delivered</div>
      </div>
    </div>

    <!-- 3. Product Wise Sales Breakdown -->
    <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm space-y-3">
      <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
        <div class="flex items-center gap-2">
          <i class="fa-solid fa-boxes-stacked text-blue-600 text-sm"></i>
          <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">Product Sales Summary</h3>
        </div>
        <span class="text-[11px] font-bold bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full">
          <?= count($productSummary) ?> Items
        </span>
      </div>

      <?php if (empty($productSummary)): ?>
        <div class="text-center py-6 text-slate-400 text-xs">No products ordered yet.</div>
      <?php else: ?>
        <div class="space-y-2.5 max-h-72 overflow-y-auto pr-1">
          <?php 
            $totQty = 0; 
            $totValue = 0;
            $totOverComm = 0;

            foreach ($productSummary as $ps): 
              $totQty += $ps['qty'];
              $totValue += $ps['total_val'];
              $itemOc = (float)($ps['total_oc'] ?? 0);
              $totOverComm += $itemOc;

              $b = floor($ps['qty'] / $ps['ppb']);
              $p = $ps['qty'] % $ps['ppb'];
              $qtyTag = ($b > 0 ? $b . ' B ' : '') . ($p > 0 || $b == 0 ? $p . ' P' : '');
          ?>
          <div class="p-2.5 bg-slate-50 rounded-xl space-y-1">
            <div class="flex items-center justify-between text-xs">
              <div class="font-bold text-slate-800 pr-2 flex-1"><?= h($ps['name']) ?></div>
              <div class="flex items-center gap-2 text-right">
                <span class="bg-white border border-slate-200 px-2 py-0.5 rounded-lg font-bold text-slate-700 text-[11px]">
                  <?= $qtyTag ?>
                </span>
                <span class="font-black text-slate-900 min-w-[65px] text-right">
                  ৳ <?= number_format($ps['total_val'], 0) ?>
                </span>
              </div>
            </div>

            <!-- Over-Commission display per product -->
            <?php if ($itemOc != 0): ?>
              <div class="flex items-center justify-between text-[11px] pt-1 border-t border-slate-200/50">
                <span class="text-slate-400 font-medium">Over-Commission (O/C)</span>
                <span class="font-bold px-1.5 py-0.5 rounded <?= $itemOc < 0 ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600' ?>">
                  O/C <?= $itemOc > 0 ? '+' : '' ?>৳ <?= number_format($itemOc, 0) ?>
                </span>
              </div>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs font-black">
          <div class="flex items-center gap-2">
            <span class="text-slate-600">Total Sales</span>
            <?php if ($totOverComm != 0): ?>
              <span class="text-[10px] font-bold px-2 py-0.5 rounded-full <?= $totOverComm < 0 ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600' ?>">
                Total O/C: <?= $totOverComm > 0 ? '+' : '' ?>৳ <?= number_format($totOverComm, 0) ?>
              </span>
            <?php endif; ?>
          </div>
          <span class="text-blue-600">৳ <?= number_format($totValue, 0) ?></span>
        </div>
      <?php endif; ?>
    </div>

    <!-- 4. Retailers Served Card -->
    <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
          <i class="fa-solid fa-store"></i>
        </div>
        <div>
          <h4 class="text-xs font-black text-slate-900">Total Retailers Served</h4>
          <p class="text-[11px] text-slate-400">Unique route shops ordered</p>
        </div>
      </div>
      <div class="text-lg font-black text-emerald-600 bg-emerald-50 px-3 py-1 rounded-xl border border-emerald-100">
        <?= $retailerCount ?>
      </div>
    </div>

  </div>

  <!-- ==================== TAB 2: ORDERS BY DATE ==================== -->
  <div id="tabContentDate" class="space-y-3 hidden">
    
    <!-- Search & Filter Controls -->
    <div class="space-y-2">
      <!-- Search Input -->
      <div class="relative">
        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        <input type="text" id="orderSearchInput" onkeyup="filterOrdersByText()" placeholder="Search by Dealer / Warehouse / Order ID..."
          class="w-full bg-white border border-slate-200 rounded-xl pl-9 pr-4 py-2 text-xs font-semibold placeholder:text-slate-400 focus:outline-none focus:border-blue-500 shadow-sm" />
      </div>

      <!-- Filter Pills -->
      <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none">
        <?php
          $statuses = ['all'=>'All', 'pending'=>'Pending', 'confirmed'=>'Confirmed', 'delivered'=>'Delivered', 'rejected'=>'Rejected'];
          $activeStatus = $_GET['status'] ?? 'all';
        ?>
        <?php foreach ($statuses as $k => $lbl): ?>
          <button type="button" onclick="filterOrdersByStatus('<?= $k ?>', this)"
            class="status-pill-btn flex-shrink-0 px-3 py-1.5 rounded-full text-[11px] font-bold transition
              <?= $activeStatus === $k ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' ?>">
            <?= $lbl ?>
          </button>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Orders List (Grouped by Date) -->
    <div id="ordersListContainer" class="space-y-3">
      <?php if (empty($items)): ?>
        <div class="bg-white rounded-2xl p-8 text-center text-slate-400 border border-slate-100">
          <i class="fa-solid fa-inbox text-4xl opacity-30 mb-2"></i>
          <p class="text-xs font-bold text-slate-600">No orders found</p>
          <p class="text-[11px] text-slate-400 mt-0.5">Start selling to see orders appear here.</p>
          <a href="<?= url('sr/sales') ?>" class="inline-flex items-center gap-1.5 bg-blue-600 text-white text-xs font-bold px-4 py-2 rounded-xl mt-3 shadow-md shadow-blue-600/20">
            <i class="fa-solid fa-cart-plus"></i> Go to Sales
          </a>
        </div>
      <?php endif; ?>

      <?php
        // Group items by Date
        $groupedOrders = [];
        foreach ($items as $o) {
          $dateKey = date('Y-m-d', strtotime($o['created_at']));
          $groupedOrders[$dateKey][] = $o;
        }
      ?>

      <?php foreach ($groupedOrders as $dateStr => $dateOrders): 
        $formattedDateLabel = (date('Y-m-d') === $dateStr) ? 'Today — ' . date('d M Y', strtotime($dateStr)) :
                             ((date('Y-m-d', strtotime('-1 day')) === $dateStr) ? 'Yesterday — ' . date('d M Y', strtotime($dateStr)) : date('d M Y (D)', strtotime($dateStr)));
      ?>
        <!-- Date Header Banner -->
        <div class="order-date-header flex items-center justify-between px-1 pt-2">
          <span class="text-xs font-black text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
            <i class="fa-regular fa-calendar-check text-blue-600"></i>
            <?= $formattedDateLabel ?>
          </span>
          <span class="text-[10px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">
            <?= count($dateOrders) ?> Order<?= count($dateOrders) !== 1 ? 's' : '' ?>
          </span>
        </div>

        <?php foreach ($dateOrders as $o):
          $statusColors = [
            'pending'   => ['bg'=>'bg-amber-50', 'text'=>'text-amber-700', 'border'=>'border-amber-200', 'icon'=>'fa-clock'],
            'confirmed' => ['bg'=>'bg-emerald-50', 'text'=>'text-emerald-700', 'border'=>'border-emerald-200', 'icon'=>'fa-circle-check'],
            'delivered' => ['bg'=>'bg-blue-50', 'text'=>'text-blue-700', 'border'=>'border-blue-200', 'icon'=>'fa-truck'],
            'rejected'  => ['bg'=>'bg-rose-50', 'text'=>'text-rose-700', 'border'=>'border-rose-200', 'icon'=>'fa-circle-xmark'],
          ];
          $sc = $statusColors[$o['status']] ?? ['bg'=>'bg-slate-100', 'text'=>'text-slate-600', 'border'=>'border-slate-200', 'icon'=>'fa-circle'];
        ?>
        <div class="order-card-item bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden" 
             data-status="<?= $o['status'] ?>" 
             data-search="<?= strtolower(h(($o['dealer_name'] ?? '') . ' ' . ($o['warehouse_name'] ?? '') . ' ' . $o['id'])) ?>">
          
          <!-- Order Item Top Bar -->
          <div onclick="toggleOrderDetail(<?= $o['id'] ?>)" class="p-3.5 flex items-center justify-between cursor-pointer hover:bg-slate-50/80 transition">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-sm font-bold">
                <i class="fa-solid fa-bag-shopping text-blue-600"></i>
              </div>
              <div>
                <div class="font-bold text-xs text-slate-900 flex items-center gap-1.5">
                  <?= h($o['dealer_name'] ?? 'Direct Sale') ?>
                  <span class="text-[10px] text-slate-400 font-medium">#<?= $o['id'] ?></span>
                </div>
                <div class="text-[11px] text-slate-400 flex items-center gap-2 mt-0.5">
                  <span><i class="fa-solid fa-warehouse text-[10px]"></i> <?= h($o['warehouse_name'] ?? 'Warehouse') ?></span>
                  <span>·</span>
                  <span><?= date('h:i A', strtotime($o['created_at'])) ?></span>
                </div>
              </div>
            </div>

            <div class="text-right">
              <div class="font-black text-xs text-slate-900">৳ <?= number_format($o['total_amount'], 0) ?></div>
              <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full capitalize mt-0.5 <?= $sc['bg'] ?> <?= $sc['text'] ?>">
                <i class="fa-solid <?= $sc['icon'] ?> text-[9px]"></i>
                <?= ucfirst($o['status']) ?>
              </span>
            </div>
          </div>

          <!-- Expandable Detail Section -->
          <div id="order-detail-<?= $o['id'] ?>" class="hidden bg-slate-50/70 p-3.5 border-t border-slate-100 space-y-3">
            
            <?php if (!empty($o['notes'])): ?>
              <div class="text-xs text-slate-600 bg-amber-50/80 border border-amber-200 p-2 rounded-xl flex items-center gap-2">
                <i class="fa-solid fa-note-sticky text-amber-600"></i>
                <span><?= h($o['notes']) ?></span>
              </div>
            <?php endif; ?>

            <!-- Products Breakdown -->
            <div class="space-y-1.5">
              <div class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Products Ordered</div>
              
              <div class="space-y-1 bg-white p-2.5 rounded-xl border border-slate-100">
                <?php 
                  $total_oc = 0;
                  foreach ($o['products'] as $p): 
                    $ppb = (int)($p['pieces_per_box'] ?: 1);
                    $qty = (int)$p['quantity'];
                    $boxes = floor($qty / $ppb);
                    $pcs = $qty % $ppb;
                    
                    $base_price = (float)$p['base_price'];
                    $unit_price = (float)$p['unit_price'];
                    $item_oc = ($unit_price - $base_price) * $qty;
                    $total_oc += $item_oc;
                ?>
                  <div class="flex items-center justify-between text-xs py-1 border-b border-slate-50 last:border-0">
                    <div class="font-bold text-slate-800 pr-2">
                      <?= h($p['product_name']) ?>
                      <?php if ($item_oc != 0): ?>
                        <span class="text-[9px] font-extrabold px-1.5 py-0.5 rounded <?= $item_oc < 0 ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600' ?>">
                          O/C <?= $item_oc > 0 ? '+' : '' ?><?= round($item_oc) ?>
                        </span>
                      <?php endif; ?>
                    </div>
                    <div class="flex items-center gap-2 text-right">
                      <span class="bg-slate-100 text-slate-700 text-[10px] font-bold px-1.5 py-0.5 rounded">
                        <?= $boxes ?> B / <?= $pcs ?> P
                      </span>
                      <span class="font-bold text-slate-900 min-w-[55px] text-right">
                        ৳ <?= number_format($p['total_price'], 0) ?>
                      </span>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- O/C and Commission Summary -->
            <?php 
              $comm_pct = (float)($o['happy_commission'] ?? 0);
              $commission = (float)$o['total_amount'] * ($comm_pct / 100);
            ?>
            <?php if ($total_oc != 0 || $comm_pct > 0): ?>
            <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-200/60 font-bold">
              <div>
                <?php if ($total_oc != 0): ?>
                  <span class="text-slate-500">Total O/C:</span>
                  <span class="<?= $total_oc < 0 ? 'text-rose-600' : 'text-emerald-600' ?>">
                    <?= $total_oc > 0 ? '+' : '' ?>৳ <?= number_format($total_oc, 0) ?>
                  </span>
                <?php endif; ?>
              </div>
              <div>
                <?php if ($comm_pct > 0): ?>
                  <span class="text-slate-500">Commission (<?= $comm_pct ?>%):</span>
                  <span class="text-blue-600">৳ <?= number_format($commission, 0) ?></span>
                <?php endif; ?>
              </div>
            </div>
            <?php endif; ?>

          </div>

        </div>
        <?php endforeach; ?>

      <?php endforeach; ?>
    </div>

  </div>

</div>

<!-- Floating Bottom Navigation Bar -->
<div class="fixed bottom-4 left-1/2 -translate-x-1/2 max-w-sm w-[90%] bg-white/95 backdrop-blur-md rounded-full shadow-2xl border border-slate-200/80 px-5 py-2.5 flex items-center justify-between z-50">
  
  <a href="<?= url('sr/dashboard') ?>" class="flex flex-col items-center text-slate-400 hover:text-slate-700 font-medium text-[10px]">
    <i class="fa-solid fa-house text-lg mb-0.5"></i>
    <span>Home</span>
  </a>

  <a href="<?= url('sr/retailers') ?>" class="flex flex-col items-center text-slate-400 hover:text-slate-700 font-medium text-[10px]">
    <i class="fa-solid fa-store text-lg mb-0.5"></i>
    <span>Shops</span>
  </a>

  <a href="<?= url('sr/sales') ?>" class="sr-float-loc-btn w-12 h-12 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white flex items-center justify-center shadow-lg shadow-blue-600/40 -mt-6 hover:scale-105 transition">
    <i class="fa-solid fa-location-dot text-xl"></i>
  </a>

  <a href="<?= url('sr/orders') ?>" class="flex flex-col items-center text-blue-600 font-bold text-[10px]">
    <i class="fa-solid fa-clock-rotate-left text-lg mb-0.5"></i>
    <span>History</span>
  </a>

  <a href="<?= url('sr/profile') ?>" class="flex flex-col items-center text-slate-400 hover:text-slate-700 font-medium text-[10px]">
    <i class="fa-solid fa-user text-lg mb-0.5"></i>
    <span>Profile</span>
  </a>

</div>

<style>
@keyframes srSubtleFloat {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-4px); }
}
.sr-float-loc-btn {
  animation: srSubtleFloat 2.5s infinite ease-in-out;
}
.scrollbar-none::-webkit-scrollbar { display: none; }
.scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<script>
// Main Tab Switcher
function switchMainTab(tab) {
  const btnSummary = document.getElementById('tabBtnSummary');
  const btnDate = document.getElementById('tabBtnDate');
  const contentSummary = document.getElementById('tabContentSummary');
  const contentDate = document.getElementById('tabContentDate');

  if (tab === 'summary') {
    contentSummary.classList.remove('hidden');
    contentDate.classList.add('hidden');
    
    btnSummary.className = 'flex-1 py-2.5 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 bg-white text-blue-600 shadow-sm';
    btnDate.className = 'flex-1 py-2.5 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 text-slate-600 hover:text-slate-900';
  } else {
    contentDate.classList.remove('hidden');
    contentSummary.classList.add('hidden');

    btnDate.className = 'flex-1 py-2.5 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 bg-white text-blue-600 shadow-sm';
    btnSummary.className = 'flex-1 py-2.5 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 text-slate-600 hover:text-slate-900';
  }
}

// Toggle Order Detail Expandable
function toggleOrderDetail(id) {
  const el = document.getElementById('order-detail-' + id);
  if (el) el.classList.toggle('hidden');
}

// Filter by Status Pill
let currentSelectedStatus = 'all';
function filterOrdersByStatus(status, btn) {
  currentSelectedStatus = status;
  
  // Highlight active pill
  document.querySelectorAll('.status-pill-btn').forEach(b => {
    b.className = 'status-pill-btn flex-shrink-0 px-3 py-1.5 rounded-full text-[11px] font-bold transition bg-white text-slate-600 border border-slate-200 hover:bg-slate-50';
  });
  btn.className = 'status-pill-btn flex-shrink-0 px-3 py-1.5 rounded-full text-[11px] font-bold transition bg-blue-600 text-white shadow-sm';

  applyFilters();
}

// Text Search Filter
function filterOrdersByText() {
  applyFilters();
}

function applyFilters() {
  const query = (document.getElementById('orderSearchInput').value || '').toLowerCase().trim();
  const items = document.querySelectorAll('.order-card-item');

  items.forEach(item => {
    const itemStatus = item.getAttribute('data-status');
    const itemSearch = item.getAttribute('data-search') || '';

    const matchesStatus = (currentSelectedStatus === 'all' || itemStatus === currentSelectedStatus);
    const matchesText = (!query || itemSearch.includes(query));

    if (matchesStatus && matchesText) {
      item.style.display = 'block';
    } else {
      item.style.display = 'none';
    }
  });

  // Also hide empty date headers if all orders under that date are hidden
  document.querySelectorAll('.order-date-header').forEach(header => {
    let nextEl = header.nextElementSibling;
    let hasVisible = false;
    while (nextEl && nextEl.classList.contains('order-card-item')) {
      if (nextEl.style.display !== 'none') {
        hasVisible = true;
        break;
      }
      nextEl = nextEl.nextElementSibling;
    }
    header.style.display = hasVisible ? 'flex' : 'none';
  });
}
</script>
