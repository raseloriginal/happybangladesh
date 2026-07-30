<?php 
$pageTitle = 'দোকান তালিকা'; 

// Helper function to truncate retailer name to 2 words
$truncateName = function($name) {
    $words = preg_split('/\s+/', trim($name));
    if (count($words) > 2) {
        $truncated = implode(' ', array_slice($words, 0, 2)) . '..';
        return [
            'is_truncated' => true,
            'short' => $truncated,
            'full' => $name
        ];
    }
    return [
        'is_truncated' => false,
        'short' => $name,
        'full' => $name
    ];
};
?>

<style>
  .font-siliguri {
    font-family: 'Hind Siliguri', 'Inter', sans-serif;
  }
</style>

<div class="p-3 sm:p-5 space-y-4 pb-28 max-w-5xl mx-auto font-siliguri text-slate-800 print:p-0 print:max-w-none print:bg-white">

  <!-- Premium Minimal Header Card -->
  <div class="bg-white/95 backdrop-blur-md px-4 py-3 sm:px-6 sm:py-4 rounded-2xl border border-slate-200/60 shadow-2xs flex items-center justify-between gap-3 print:shadow-none print:border-none print:p-0">
    <div class="flex items-center gap-3">
      <a href="<?= url('sr/dashboard') ?>" class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-100 hover:bg-slate-900 hover:text-white transition-all duration-200 flex items-center justify-center text-slate-600 shadow-2xs active:scale-95 print:hidden">
        <i class="fa-solid fa-arrow-left text-xs sm:text-sm"></i>
      </a>
      <div>
        <div class="flex items-center gap-2">
          <h1 class="text-xl sm:text-2xl font-bold text-slate-900 leading-tight tracking-tight">
            দোকান তালিকা
          </h1>
          <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[9px] font-bold bg-slate-100 text-slate-600 border border-slate-200/50 print:hidden">
            <?= count($retailers) ?>টি দোকান
          </span>
        </div>
        <p class="text-xs text-slate-400 font-medium leading-tight mt-1">কাস্টমার শপের তালিকা</p>
      </div>
    </div>
    
    <div class="flex items-center gap-2 print:hidden">
      <a href="<?= url('sr/sales') ?>" class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-900 hover:bg-slate-800 text-white flex items-center justify-center transition active:scale-95 shadow-sm" title="ম্যাপ ভিউ">
        <i class="fa-solid fa-map-location-dot text-xs sm:text-sm"></i>
      </a>
    </div>
  </div>

  <!-- Search Box Card -->
  <div class="bg-white p-3 rounded-2xl border border-slate-200/50 shadow-3xs print:hidden">
    <form method="GET" action="<?= url('sr/retailers') ?>" class="flex gap-2">
      <div class="relative flex-1">
        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        <input type="text" name="search" value="<?= h($search) ?>" placeholder="দোকানের নাম, ঠিকানা বা মোবাইল নাম্বার..." 
          class="w-full bg-slate-50 border border-slate-200/60 rounded-xl pl-9 pr-8 py-2 text-xs font-bold text-slate-800 placeholder:text-slate-400 focus:outline-none focus:border-blue-500 transition" autocomplete="off">
        <?php if ($search !== ''): ?>
          <a href="<?= url('sr/retailers') ?>" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs">
            <i class="fa-solid fa-circle-xmark"></i>
          </a>
        <?php endif; ?>
      </div>
      <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs px-4 py-2 rounded-xl active:scale-95 transition">
        খুঁজুন
      </button>
    </form>
  </div>

  <!-- Minimal Table Container -->
  <div class="bg-white rounded-2xl border border-slate-200/80 shadow-3xs overflow-hidden print:border-slate-300">
    <table class="w-full text-left border-collapse table-fixed min-w-0" id="retailersTable">
      <thead>
        <tr class="border-b border-slate-200 text-xs text-slate-800 font-bold tracking-tight bg-slate-50">
          <th class="p-2.5 bg-slate-50/80 border-r border-slate-200/50 w-[58%]">
            দোকানের নাম
          </th>
          <th class="p-2.5 bg-slate-50/80 text-center border-r border-slate-200/50 w-[28%]">
            মোবাইল
          </th>
          <th class="p-2.5 bg-slate-50/80 text-center w-[14%]">
            অর্ডার
          </th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 font-sans" id="tableBody">
        <?php if (empty($retailers)): ?>
          <tr id="emptyRow">
            <td colspan="3" class="p-12 text-center text-slate-400 bg-white">
              <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-300 flex items-center justify-center text-xl mx-auto mb-2"><i class="fa-solid fa-store"></i></div>
              <span class="text-xs font-medium">কোনো দোকান পাওয়া যায়নি।</span>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($retailers as $r): ?>
            <tr class="retailer-row hover:bg-slate-50/40 transition-colors">
              
              <!-- Retailer Name Cell -->
              <td class="p-2 border-r border-slate-100 align-middle bg-white overflow-hidden">
                <div class="flex items-center gap-2 min-w-0">
                  <?php if ($r['has_order_today']): ?>
                    <div class="w-7 h-7 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-center font-bold text-emerald-600 text-xs shrink-0 select-none font-siliguri" title="অর্ডার সম্পন্ন">
                      <i class="fa-solid fa-check text-[10px]"></i>
                    </div>
                  <?php else: ?>
                    <div class="w-7 h-7 rounded-lg bg-blue-50 border border-blue-200 flex items-center justify-center font-bold text-blue-600 text-xs shrink-0 select-none font-siliguri" title="পেন্ডিং">
                      <?= mb_substr($r['name'], 0, 1, 'UTF-8') ?>
                    </div>
                  <?php endif; ?>
                  
                  <div class="min-w-0 flex-1">
                    <?php 
                      $nameInfo = $truncateName($r['name']); 
                      if ($nameInfo['is_truncated']):
                    ?>
                      <div class="font-bold text-slate-800 text-xs sm:text-sm leading-snug cursor-pointer select-none break-words"
                           onclick="toggleRetailerName(this, '<?= htmlspecialchars($nameInfo['full'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($nameInfo['short'], ENT_QUOTES, 'UTF-8') ?>')">
                        <?= h($nameInfo['short']) ?>
                      </div>
                    <?php else: ?>
                      <div class="font-bold text-slate-800 text-xs sm:text-sm leading-snug break-words">
                        <?= h($r['name']) ?>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </td>

              <!-- Phone Cell -->
              <td class="p-2 text-center border-r border-slate-100 align-middle bg-white text-slate-600 text-[10px] sm:text-xs font-mono truncate">
                <?= h($r['phone'] ?: 'N/A') ?>
              </td>

              <!-- Order Action Button -->
              <td class="p-2 text-center align-middle bg-white">
                <?php if ($r['has_order_today']): ?>
                  <button type="button" onclick="openShop(<?= $r['id'] ?>, '<?= h(addslashes($r['name'])) ?>', '<?= h(addslashes($r['address'] ?? '')) ?>', true)" 
                    class="w-8 h-8 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-600 hover:bg-emerald-100 transition-all flex items-center justify-center shadow-3xs active:scale-95 mx-auto" 
                    title="অর্ডার সম্পন্ন (সম্পাদনা করতে ক্লিক করুন)">
                    <i class="fa-solid fa-circle-check text-sm"></i>
                  </button>
                <?php else: ?>
                  <button type="button" onclick="openShop(<?= $r['id'] ?>, '<?= h(addslashes($r['name'])) ?>', '<?= h(addslashes($r['address'] ?? '')) ?>', false)" 
                    class="w-8 h-8 rounded-lg bg-blue-50 border border-blue-200 text-blue-600 hover:bg-blue-600 hover:text-white transition-all flex items-center justify-center shadow-3xs active:scale-95 mx-auto" 
                    title="অর্ডার শুরু">
                    <i class="fa-solid fa-cart-plus text-xs"></i>
                  </button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
      </table>
  </div>

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
    <div class="flex items-center justify-center gap-2 pt-2 print:hidden select-none">
      <?php if ($page > 1): ?>
        <a href="<?= url('sr/retailers') ?>?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" class="px-3.5 py-2 bg-white border border-slate-200/60 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-50 hover:border-slate-300 shadow-3xs active:scale-95 transition">
          <i class="fa-solid fa-angle-left"></i> আগে
        </a>
      <?php endif; ?>

      <span class="px-4 py-2 bg-slate-50 border border-slate-200/40 rounded-xl text-xs font-bold text-slate-500 font-mono">
        পেজ <?= $page ?> / <?= $totalPages ?>
      </span>

      <?php if ($page < $totalPages): ?>
        <a href="<?= url('sr/retailers') ?>?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" class="px-3.5 py-2 bg-white border border-slate-200/60 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-50 hover:border-slate-300 shadow-3xs active:scale-95 transition">
          পরে <i class="fa-solid fa-angle-right"></i>
        </a>
      <?php endif; ?>
    </div>
  <?php endif; ?>

</div>

<?php include __DIR__ . '/partials/_shop_v2.php'; ?>

<script>
const BASE_URL = '<?= BASE_URL ?>';
const ALL_PRODUCTS_URL = `${BASE_URL}/sr/api/products`;
let ALL_PRODUCTS = [];

// Fetch products asynchronously
fetch(ALL_PRODUCTS_URL)
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      ALL_PRODUCTS = data.products || [];
    }
  })
  .catch(err => console.error('Failed to load products', err));

let cartsByRetailer = {};
let currentRetailer = null;
let currentProduct  = null;
let isSubmitting    = false;

// Colour palette for product cards (required by _shop_v2.php)
const gradients = [
  'linear-gradient(135deg,#2563eb,#3b82f6)',
  'linear-gradient(135deg,#06b6d4,#0891b2)',
  'linear-gradient(135deg,#10b981,#059669)',
  'linear-gradient(135deg,#f59e0b,#d97706)',
  'linear-gradient(135deg,#8b5cf6,#7c3aed)',
  'linear-gradient(135deg,#ef4444,#dc2626)',
];
const emojis = ['📦','🛒','🏪','🎁','🧴','🍬','🧃','🍪'];

// Stub: no map pins on the retailers list page
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

function toggleRetailerName(element, fullName, shortName) {
  if (element.innerText.trim().endsWith('..')) {
    element.innerText = fullName;
  } else {
    element.innerText = shortName;
  }
}
</script>
