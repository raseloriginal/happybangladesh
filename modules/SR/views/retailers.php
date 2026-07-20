<?php $pageTitle = 'Retailers'; ?>
<style>
/* Sheet Overlay */
.sr-sheet-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(4px);
  z-index: 1000;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
}
.sr-sheet-overlay.open {
  opacity: 1;
  visibility: visible;
}
/* Bottom Sheet */
.sr-sheet {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: #ffffff;
  border-radius: 24px 24px 0 0;
  z-index: 1010;
  transform: translateY(100%);
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  flex-direction: column;
  box-shadow: 0 -10px 40px rgba(0,0,0,0.1);
  max-height: 90vh;
}
.sr-sheet.open {
  transform: translateY(0);
}
.sr-sheet-header {
  padding: 20px;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.sr-sheet-title {
  font-size: 1.15rem;
  font-weight: 800;
  color: #0f172a;
  margin: 0;
}
.sr-sheet-close {
  background: #f1f5f9;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  color: #64748b;
  cursor: pointer;
}
.sr-sheet-body {
  overflow-y: auto;
}
.sr-sheet-footer {
  display: flex;
}
.sr-sheet-btn {
  border: none;
  border-radius: 12px;
  cursor: pointer;
  color: white;
}
</style>
<div style="background:#2563eb;padding:44px 20px 20px;display:flex;align-items:center;justify-content:space-between;color:#fff;">
  <div>
    <div style="font-size:0.75rem;color:rgba(255,255,255,0.6);margin-bottom:4px;"><i class="fa-solid fa-house" style="margin-right:4px;"></i>Home › Retailers</div>
    <div style="font-size:1.4rem;font-weight:800;">Retailers</div>
  </div>
  <a href="<?= url('sr/dashboard') ?>" style="color:#fff;background:rgba(255,255,255,0.15);padding:6px 12px;border-radius:10px;font-size:0.8rem;text-decoration:none;font-weight:600;"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>

<div style="padding:16px 16px 8px;">
  <form method="GET" action="<?= url('sr/retailers') ?>" style="display:flex;gap:8px;">
    <div style="position:relative;flex:1;">
      <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:0.9rem;"></i>
      <input type="text" name="search" value="<?= h($search) ?>" placeholder="Search name, phone, address..." style="width:100%;padding:10px 12px 10px 36px;border:1px solid #cbd5e1;border-radius:12px;font-size:0.875rem;outline:none;background:#fff;" autocomplete="off">
      <?php if ($search !== ''): ?>
        <a href="<?= url('sr/retailers') ?>" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:0.9rem;text-decoration:none;"><i class="fa-solid fa-circle-xmark"></i></a>
      <?php endif; ?>
    </div>
    <button type="submit" style="background:#2563eb;color:#fff;border:none;padding:10px 16px;border-radius:12px;font-weight:600;font-size:0.875rem;">Search</button>
  </form>
</div>

<?php if (empty($retailers)): ?>
<div style="text-align:center;padding:40px 20px;color:#94a3b8;">
  <div style="font-size:3rem;margin-bottom:12px;">🏪</div>
  <div style="font-weight:600;color:#64748b;">No retailers found</div>
  <div style="font-size:0.8rem;margin-top:4px;">Try searching for a different name or phone number.</div>
</div>
<?php else: ?>
<div style="padding:0 16px 16px;display:flex;flex-direction:column;gap:12px;">
  <?php foreach ($retailers as $r): ?>
    <div style="background:#fff;border-radius:14px;padding:16px;box-shadow:0 2px 12px rgba(0,0,0,.04);border:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;gap:12px;">
      <div style="flex:1;min-width:0;">
        <div style="display:flex;align-items:center;gap:8px;">
          <span style="font-size:1.1rem;">🏪</span>
          <h3 style="font-weight:700;color:#0f172a;font-size:0.95rem;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= h($r['name']) ?></h3>
          <?php if ($r['has_order_today']): ?>
            <span style="font-size:0.65rem;font-weight:700;color:#16a34a;background:#d1fae5;padding:1px 6px;border-radius:4px;white-space:nowrap;">Ordered</span>
          <?php endif; ?>
        </div>
        
        <div style="font-size:0.75rem;color:#64748b;margin-top:6px;display:flex;align-items:center;gap:4px;">
          <i class="fa-solid fa-phone" style="width:12px;opacity:0.6;"></i>
          <?= h($r['phone']) ?>
        </div>
        <?php if (!empty($r['address'])): ?>
          <div style="font-size:0.75rem;color:#94a3b8;margin-top:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:260px;">
            <i class="fa-solid fa-location-dot" style="width:12px;opacity:0.6;"></i>
            <?= h($r['address']) ?>
          </div>
        <?php endif; ?>
      </div>
      
      <div style="flex-shrink:0;">
        <button onclick="openShop(<?= $r['id'] ?>, '<?= h(addslashes($r['name'])) ?>', '<?= h(addslashes($r['address'] ?? '')) ?>', <?= $r['has_order_today'] ? 'true' : 'false' ?>)" style="display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;border-radius:50%;background:#eff6ff;color:#2563eb;border:none;font-size:1.1rem;box-shadow:0 2px 8px rgba(37,99,235,0.08);cursor:pointer;" title="Place Order">
          <i class="fa-solid fa-cart-plus"></i>
        </button>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php if ($totalPages > 1): ?>
  <div style="padding:0 16px 24px;display:flex;align-items:center;justify-content:center;gap:6px;">
    <?php if ($page > 1): ?>
      <a href="<?= url('sr/retailers') ?>?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" style="padding:8px 12px;border:1px solid #cbd5e1;border-radius:10px;color:#334155;text-decoration:none;font-size:0.8rem;font-weight:600;background:#fff;"><i class="fa-solid fa-angle-left"></i> Prev</a>
    <?php endif; ?>
    
    <span style="font-size:0.8rem;color:#64748b;font-weight:600;background:#f8fafc;border:1px solid #cbd5e1;padding:8px 14px;border-radius:10px;">Page <?= $page ?> of <?= $totalPages ?></span>
    
    <?php if ($page < $totalPages): ?>
      <a href="<?= url('sr/retailers') ?>?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" style="padding:8px 12px;border:1px solid #cbd5e1;border-radius:10px;color:#334155;text-decoration:none;font-size:0.8rem;font-weight:600;background:#fff;">Next <i class="fa-solid fa-angle-right"></i></a>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php endif; ?>



<?php include __DIR__ . '/partials/_shop_v2.php'; ?>


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

// We need to define some globals that were usually defined in sales.php map section
let cartsByRetailer = {};
let currentRetailer = null;

// Dummy updateAllPins to prevent errors since we don't have a map here
function updateAllPins() {
  // optionally update the UI if needed
}

function openShop(id, name, address, hasOrderToday = false) {
  const ret = { id: id, name: name, address: address, has_order_today: hasOrderToday };
  
  if (ret.has_order_today) {
    showConfirmModal(`An order has already been placed for "${ret.name}" today. Are you sure you want to modify this order?`, () => {
      fetch(`${BASE_URL}/sr/api/today-order?retailer_id=${ret.id}`)
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            cartsByRetailer[ret.id] = data.items;
            ret.has_order_today = false; // allow editing
            openRetailerCartSheet(ret);
          } else {
            showMiniToast('❌ ' + (data.message || 'Error fetching order details'), true);
          }
        })
        .catch(() => showMiniToast('❌ Network error', true));
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

// Modify the onclick handler for the button to pass has_order_today
</script>
