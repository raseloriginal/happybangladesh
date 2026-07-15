<?php
/**
 * Top header bar
 */
?>
<header class="bg-white border-b border-gray-200 px-4 lg:px-6 py-3 flex items-center justify-between sticky top-0 z-30 shadow-sm">

  <!-- Left: hamburger + breadcrumb -->
  <div class="flex items-center gap-3">
    <!-- Mobile hamburger -->
    <button id="menu-toggle" class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition">
      <i class="fa-solid fa-bars"></i>
    </button>

    <!-- Breadcrumb -->
    <div class="hidden md:block">
      <div class="text-sm font-semibold text-gray-800"><?= $pageTitle ?? APP_NAME ?></div>
      <div class="text-xs text-gray-400"><?= isset($breadcrumb) ? $breadcrumb : Helpers::datetime(date('Y-m-d H:i:s')) ?></div>
    </div>
  </div>

  <!-- Right: notifications + user dropdown -->
  <div class="flex items-center gap-2">

    <!-- Notification bell (decorative for starter) -->
    <button class="relative p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition">
      <i class="fa-regular fa-bell text-sm"></i>
      <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-red-500"></span>
    </button>

    <!-- User dropdown -->
    <div class="relative">
      <button data-dropdown="user-menu"
              class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-gray-100 transition">
        <div class="w-7 h-7 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs font-bold">
          <?= Helpers::initials(Auth::name()) ?>
        </div>
        <span class="hidden md:block text-sm font-medium text-gray-700"><?= h(Auth::name()) ?></span>
        <i class="fa-solid fa-chevron-down text-xs text-gray-400 hidden md:block"></i>
      </button>

      <!-- Dropdown menu -->
      <div id="user-menu" class="dropdown-menu hidden absolute right-0 top-full mt-1 w-52 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
        <div class="px-4 py-3 border-b border-gray-100">
          <div class="text-sm font-semibold text-gray-800"><?= h(Auth::name()) ?></div>
          <div class="text-xs text-gray-400"><?= h(Auth::email()) ?></div>
          <span class="inline-block mt-1 text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-medium">
            <?= h(Auth::roleName()) ?>
          </span>
        </div>
        <a href="<?= BASE_URL ?>/logout"
           class="flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition">
          <i class="fa-solid fa-right-from-bracket w-4"></i>
          Logout
        </a>
      </div>
    </div><!-- /user dropdown -->

  </div>
</header>
