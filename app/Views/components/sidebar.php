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
        ['icon' => 'fa-building',     'label' => 'Companies',   'url' => '/admin/companies'],
        ['icon' => 'fa-store',        'label' => 'Dealers',     'url' => '/admin/dealers'],
        ['section' => 'SYSTEM'],
        ['icon' => 'fa-circle-check', 'label' => 'Approvals',   'url' => '/admin/approvals'],
        ['icon' => 'fa-chart-bar',    'label' => 'Reports',     'url' => '/admin/reports'],
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
        ['icon' => 'fa-rotate-left',  'label' => 'Returns',     'url' => '/manager/returns'],
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
<aside id="sidebar" class="sidebar bg-gray-900 flex flex-col">

  <!-- Brand -->
  <div class="flex items-center gap-3 px-5 py-5 border-b border-gray-700/60">
    <div class="w-9 h-9 rounded-lg bg-blue-600 flex items-center justify-center">
      <i class="fa-solid fa-truck-fast text-white text-sm"></i>
    </div>
    <div>
      <div class="text-white font-bold text-sm leading-none">HappyBD</div>
      <div class="text-gray-400 text-xs mt-0.5">DMS v<?= APP_VERSION ?></div>
    </div>
  </div>

  <!-- User card -->
  <div class="px-4 py-4 border-b border-gray-700/60">
    <div class="flex items-center gap-3">
      <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
        <?= Helpers::initials(Auth::name()) ?>
      </div>
      <div class="min-w-0">
        <div class="text-white text-sm font-medium truncate"><?= h(Auth::name()) ?></div>
        <div class="text-gray-400 text-xs truncate"><?= h(Auth::roleName()) ?></div>
      </div>
    </div>
  </div>

  <!-- Nav -->
  <nav class="flex-1 overflow-y-auto py-3 px-3">
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
  <div class="px-3 py-4 border-t border-gray-700/60">
    <a href="<?= url($role . '/logout') ?>"
       class="sidebar-link text-red-400 hover:text-red-300 hover:bg-red-500/10">
      <i class="fa-solid fa-right-from-bracket w-4 text-center"></i>
      <span>Logout</span>
    </a>
  </div>

</aside>
<!-- ── /Sidebar ────────────────────────────────────────────── -->
