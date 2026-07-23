<?php $pageTitle = 'Retailers'; ?>

<div class="p-4 sm:p-5 space-y-4 pb-28 max-w-md mx-auto font-sans">

  <!-- 1. Top Header Bar -->
  <div class="flex items-center justify-between bg-white p-3.5 rounded-2xl border border-slate-200/90 shadow-2xs">
    <div class="flex items-center gap-3">
      <a href="<?= url('sr/dashboard') ?>" class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-700 hover:bg-slate-200 transition">
        <i class="fa-solid fa-arrow-left text-sm"></i>
      </a>
      <div>
        <h1 class="text-base font-black text-slate-900 leading-tight">Shops (দোকান তালিকা)</h1>
        <p class="text-[11px] text-slate-500 font-medium"><?= count($retailers) ?>টি কাস্টমার শপ উপলব্ধ</p>
      </div>
    </div>

    <!-- Quick Map Shortcut -->
    <a href="<?= url('sr/sales') ?>" class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200/80 flex items-center justify-center text-sm shadow-2xs active:scale-95 transition" title="ম্যাপ ভিউ">
      <i class="fa-solid fa-map-location-dot"></i>
    </a>
  </div>

  <!-- 2. Search Box Card (Matching Dashboard Minimal Style) -->
  <div class="bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 text-white rounded-3xl p-5 shadow-xl shadow-blue-600/25 space-y-3">
    <div class="flex items-center justify-between">
      <span class="text-[11px] font-bold text-blue-100 uppercase tracking-wider">কাস্টমার শপ নির্বাচন করুন</span>
      <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
    </div>

    <form method="GET" action="<?= url('sr/retailers') ?>" class="flex gap-2">
      <div class="relative flex-1">
        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        <input type="text" name="search" value="<?= h($search) ?>" placeholder="দোকানের নাম, ঠিকানা বা মোবাইল নাম্বার..." 
          class="w-full bg-white text-slate-900 font-bold placeholder:text-slate-400 border border-white/20 rounded-2xl pl-9 pr-8 py-2.5 text-xs outline-none focus:ring-2 focus:ring-amber-300 shadow-2xs transition" autocomplete="off">
        <?php if ($search !== ''): ?>
          <a href="<?= url('sr/retailers') ?>" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs">
            <i class="fa-solid fa-circle-xmark"></i>
          </a>
        <?php endif; ?>
      </div>
      <button type="submit" class="bg-amber-400 hover:bg-amber-500 text-slate-900 font-black text-xs px-4 py-2.5 rounded-2xl shadow-md active:scale-95 transition">
        খুঁজুন
      </button>
    </form>
  </div>

  <!-- 3. Retailers List Cards -->
  <div class="space-y-2.5">

    <?php if (empty($retailers)): ?>
      <div class="bg-white rounded-2xl p-8 text-center border border-slate-200/90 shadow-2xs space-y-2">
        <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center mx-auto text-3xl">
          🏪
        </div>
        <h3 class="text-sm font-black text-slate-800">কোনো দোকান পাওয়া যায়নি</h3>
        <p class="text-xs text-slate-500">অন্য কোনো নাম বা মোবাইল নাম্বার দিয়ে চেষ্টা করুন।</p>
      </div>
    <?php else: ?>

      <?php foreach ($retailers as $r): ?>
        <div class="bg-white p-3.5 rounded-2xl border border-slate-200/90 shadow-2xs flex items-center justify-between gap-3 hover:border-blue-400 transition">
          
          <!-- Shop Info -->
          <div class="flex items-center gap-3 min-w-0 flex-1">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 border border-blue-200/60 flex items-center justify-center text-lg shrink-0">
              <i class="fa-solid fa-store"></i>
            </div>
            
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2">
                <h3 class="text-xs font-black text-slate-900 truncate leading-tight"><?= h($r['name']) ?></h3>
                <?php if ($r['has_order_today']): ?>
                  <span class="text-[9px] font-black text-emerald-700 bg-emerald-50 border border-emerald-200/80 px-1.5 py-0.5 rounded-full shrink-0">
                    অর্ডার সম্পন্ন
                  </span>
                <?php endif; ?>
              </div>

              <!-- Phone -->
              <div class="text-[11px] text-slate-500 font-bold flex items-center gap-1 mt-1">
                <i class="fa-solid fa-phone text-[10px] text-slate-400"></i>
                <span class="font-mono"><?= h($r['phone']) ?></span>
              </div>

              <!-- Address -->
              <?php if (!empty($r['address'])): ?>
                <div class="text-[10px] text-slate-400 font-medium truncate mt-0.5">
                  <i class="fa-solid fa-location-dot text-[9px]"></i>
                  <?= h($r['address']) ?>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Order Action Button -->
          <button type="button" onclick="openShop(<?= $r['id'] ?>, '<?= h(addslashes($r['name'])) ?>', '<?= h(addslashes($r['address'] ?? '')) ?>', <?= $r['has_order_today'] ? 'true' : 'false' ?>)" 
            class="w-11 h-11 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white flex items-center justify-center text-base shadow-md shadow-blue-600/30 active:scale-95 transition shrink-0" 
            title="অর্ডার শুরু">
            <i class="fa-solid fa-cart-plus"></i>
          </button>

        </div>
      <?php endforeach; ?>

    <?php endif; ?>

  </div>

  <!-- 4. Pagination -->
  <?php if ($totalPages > 1): ?>
    <div class="flex items-center justify-center gap-2 pt-2">
      <?php if ($page > 1): ?>
        <a href="<?= url('sr/retailers') ?>?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" class="px-3.5 py-2 bg-white border border-slate-200/90 rounded-xl text-xs font-extrabold text-slate-700 hover:bg-slate-50 shadow-2xs transition">
          <i class="fa-solid fa-angle-left"></i> আগে
        </a>
      <?php endif; ?>

      <span class="px-4 py-2 bg-slate-100 border border-slate-200/80 rounded-xl text-xs font-bold text-slate-600 font-mono">
        পেজ <?= $page ?> / <?= $totalPages ?>
      </span>

      <?php if ($page < $totalPages): ?>
        <a href="<?= url('sr/retailers') ?>?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" class="px-3.5 py-2 bg-white border border-slate-200/90 rounded-xl text-xs font-extrabold text-slate-700 hover:bg-slate-50 shadow-2xs transition">
          পরে <i class="fa-solid fa-angle-right"></i>
        </a>
      <?php endif; ?>
    </div>
  <?php endif; ?>

</div>



<?php include __DIR__ . '/partials/_shop_v2.php'; ?>

<style>
@keyframes srSubtleFloat {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-4px); }
}
.sr-float-loc-btn {
  animation: srSubtleFloat 2.5s infinite ease-in-out;
}
</style>

<script>
const BASE_URL = '<?= BASE_URL ?>';
const ALL_PRODUCTS = <?= json_encode($allProducts ?? [], JSON_UNESCAPED_UNICODE) ?>;
const gradients = [
  'linear-gradient(135deg,#2563eb,#3b82f6)',
  'linear-gradient(135deg,#06b6d4,#0891b2)',
  'linear-gradient(135deg,#10b981,#059669)',
  'linear-gradient(135deg,#f59e0b,#d97706)',
  'linear-gradient(135deg,#8b5cf6,#7c3aed)',
  'linear-gradient(135deg,#ef4444,#dc2626)',
];
const emojis = ['📦','🛒','🏪','🎁','🧴','🍬','🧃','🍪'];

let cartsByRetailer = {};
let currentRetailer = null;

function updateAllPins() {}

function openShop(id, name, address, hasOrderToday = false) {
  const ret = { id: id, name: name, address: address, has_order_today: hasOrderToday };
  
  if (ret.has_order_today) {
    showConfirmModal(`"${ret.name}" দোকানে আজ একটি অর্ডার দেওয়া হয়েছে। আপনি কি এই অর্ডার পরিবর্তন করতে চান?`, () => {
      fetch(`${BASE_URL}/sr/api/today-order?retailer_id=${ret.id}`)
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            cartsByRetailer[ret.id] = data.items;
            ret.has_order_today = false;
            openRetailerCartSheet(ret);
          } else {
            showMiniToast('❌ ' + (data.message || 'অর্ডার আনতে সমস্যা হয়েছে'), true);
          }
        })
        .catch(() => showMiniToast('❌ নেটওয়ার্ক ত্রুটি', true));
    });
    return;
  }

  if (cartsByRetailer[ret.id] && cartsByRetailer[ret.id].length > 0) {
    openRetailerCartSheet(ret);
  } else {
    currentRetailer = ret;
    if (!cartsByRetailer[ret.id]) cartsByRetailer[ret.id] = [];
    openProductsForRetailer();
  }
}
</script>
