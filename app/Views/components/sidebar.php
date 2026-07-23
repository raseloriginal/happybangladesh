<?php
/**
 * Sidebar component — role-based navigation
 */
$role    = Auth::role();
$baseUrl = BASE_URL;

$menus = [
    'admin' => [
        ['section' => 'OVERVIEW'],
        ['icon' => 'fa-gauge',        'label' => 'Dashboard',   'url' => '/admin/dashboard'],
        ['section' => 'MANAGEMENT'],
        ['icon' => 'fa-warehouse',    'label' => 'Warehouses',  'url' => '/admin/warehouses'],
        ['icon' => 'fa-user-tie',     'label' => 'Managers',    'url' => '/admin/managers'],
        ['icon' => 'fa-person-walking','label'=> 'Sales Reps',  'url' => '/admin/srs'],
        ['icon' => 'fa-truck',        'label' => 'DSRs',        'url' => '/admin/dsrs'],
        ['section' => 'BUSINESS'],
        ['icon' => 'fa-file-invoice', 'label' => 'Orders',      'url' => '/admin/orders'],
        ['icon' => 'fa-building',     'label' => 'Companies',   'url' => '/admin/companies'],
        ['icon' => 'fa-store',        'label' => 'Dealers',     'url' => '/admin/dealers'],
        ['section' => 'SYSTEM'],
        ['icon' => 'fa-circle-check', 'label' => 'Approvals',   'url' => '/admin/approvals'],
        ['icon' => 'fa-chart-bar',    'label' => 'Reports',     'url' => '/admin/reports'],
        ['icon' => 'fa-file-import',  'label' => 'Import Retailers', 'url' => '/admin/retailers/import'],
        ['icon' => 'fa-database',     'label' => 'Database Sync', 'url' => '/admin/database-sync'],
        ['icon' => 'fa-shield-halved','label' => 'Sessions',      'url' => '/admin/sessions'],
    ],
    'manager' => [
        ['section' => 'OVERVIEW'],
        ['icon' => 'fa-gauge',        'label' => 'Dashboard',   'url' => '/manager/dashboard'],
        ['section' => 'PRODUCTS'],
        ['icon' => 'fa-boxes-stacked','label' => 'Products',    'url' => '/manager/products'],
        ['icon' => 'fa-tags',         'label' => 'Categories',  'url' => '/manager/categories'],
        ['icon' => 'fa-layer-group',  'label' => 'Lots',        'url' => '/manager/lots'],
        ['icon' => 'fa-cubes',        'label' => 'Inventory',   'url' => '/manager/inventory'],
        ['section' => 'OPERATIONS'],
        ['icon' => 'fa-truck-fast',   'label' => 'Dispatch',    'url' => '/manager/dispatch'],
        ['icon' => 'fa-file-invoice-dollar','label' => 'Settlements', 'url' => '/manager/settlements'],
        ['icon' => 'fa-calendar-check','label'=> 'Attendance',  'url' => '/manager/attendance'],
        ['icon' => 'fa-tags',         'label' => 'Ready Sale',  'url' => '/manager/readysale'],
    ],
    'sr' => [
        ['section' => 'OVERVIEW'],
        ['icon' => 'fa-gauge',        'label' => 'Dashboard',   'url' => '/sr/dashboard'],
        ['section' => 'ORDERS'],
        ['icon' => 'fa-file-invoice', 'label' => 'My Orders',   'url' => '/sr/orders'],
        ['icon' => 'fa-plus-circle',  'label' => 'Place Order', 'url' => '/sr/orders/place'],
    ],
    'dsr' => [
        ['section' => 'OVERVIEW'],
        ['icon' => 'fa-gauge',        'label' => 'Dashboard',   'url' => '/dsr/dashboard'],
        ['section' => 'DELIVERY'],
        ['icon' => 'fa-qrcode',       'label' => 'Scanner',     'url' => '/dsr/scanner'],
        ['icon' => 'fa-truck',        'label' => 'Van Stock',   'url' => '/dsr/van-stock'],
        ['icon' => 'fa-motorcycle',   'label' => 'Deliveries',  'url' => '/dsr/delivery'],
        ['section' => 'EXPENSES'],
        ['icon' => 'fa-receipt',      'label' => 'Expenses',    'url' => '/dsr/expenses'],
    ],
];

$items = $menus[$role] ?? [];
?>

<!-- ── Sidebar ─────────────────────────────────────────────── -->
<aside id="sidebar" class="sidebar bg-white border-r border-slate-200 flex flex-col shadow-sm">

  <!-- Brand -->
  <div class="flex items-center gap-3 px-5 py-5 border-b border-slate-100">
    <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center shadow-md shadow-blue-500/20">
      <i class="fa-solid fa-truck-fast text-white text-sm"></i>
    </div>
    <div>
      <div class="text-slate-900 font-black text-base leading-none">HappyBD</div>
      <div class="text-blue-600 font-bold text-[11px] mt-0.5 tracking-wider uppercase">DMS v<?= APP_VERSION ?></div>
    </div>
  </div>

  <!-- User card -->
  <div class="px-4 py-3.5 border-b border-slate-100 bg-slate-50/60">
    <div class="flex items-center gap-3">
      <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center text-white text-xs font-black shadow-sm flex-shrink-0">
        <?= Helpers::initials(Auth::name()) ?>
      </div>
      <div class="min-w-0">
        <div class="text-slate-900 text-xs font-bold truncate"><?= h(Auth::name()) ?></div>
        <div class="inline-block text-[10px] font-bold uppercase tracking-wider text-blue-700 bg-blue-100/80 px-2 py-0.5 rounded-full mt-0.5"><?= h(Auth::roleName()) ?></div>
      </div>
    </div>
  </div>

  <!-- Nav -->
  <nav class="flex-1 overflow-y-auto py-3 px-3 space-y-1">
    <?php foreach ($items as $item): ?>
      <?php if (isset($item['section'])): ?>
        <div class="sidebar-section"><?= $item['section'] ?></div>
      <?php else: ?>
        <a href="<?= $baseUrl . $item['url'] ?>"
           class="sidebar-link <?= (strpos($_SERVER['REQUEST_URI'], $item['url']) !== false) ? 'active' : '' ?>">
          <i class="fa-solid <?= $item['icon'] ?> w-4 text-center"></i>
          <span><?= $item['label'] ?></span>
        </a>
      <?php endif; ?>
    <?php endforeach; ?>
  </nav>

  <!-- Logout -->
  <div class="px-3 py-4 border-t border-slate-100 bg-slate-50/40">
    <a href="<?= url($role . '/logout') ?>"
       class="sidebar-link text-rose-600 hover:text-rose-700 hover:bg-rose-50">
      <i class="fa-solid fa-right-from-bracket w-4 text-center"></i>
      <span>Logout</span>
    </a>
  </div>

</aside>
<!-- ── /Sidebar ────────────────────────────────────────────── -->
