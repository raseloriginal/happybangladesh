<?php $pageTitle = 'Dashboard'; ?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-500 mb-1">Total Products</p>
            <p class="text-3xl font-bold text-gray-800"><?= number_format($stats['total_products']) ?></p>
        </div>
        <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xl">
            <i class="fas fa-boxes"></i>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-500 mb-1">Current Stock (Boxes)</p>
            <p class="text-3xl font-bold text-gray-800"><?= number_format($stats['total_inventory']) ?></p>
        </div>
        <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl">
            <i class="fas fa-cubes"></i>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-500 mb-1">Total Orders</p>
            <p class="text-3xl font-bold text-gray-800"><?= number_format($stats['recent_orders']) ?></p>
        </div>
        <div class="w-12 h-12 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-xl">
            <i class="fas fa-shopping-cart"></i>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Welcome to HappyDealer</h3>
    <p class="text-gray-600 leading-relaxed mb-4">
        This is your dedicated portal to manage your inventory, track transactions, and view your commission reports.
        Use the sidebar on the left to navigate through different sections.
    </p>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
        <a href="<?= BASE_URL ?>/dealer/inventory" class="block p-4 border rounded-lg hover:bg-gray-50 transition-colors">
            <div class="text-emerald-600 mb-2"><i class="fas fa-boxes text-2xl"></i></div>
            <h4 class="font-semibold text-gray-800">Manage Inventory</h4>
            <p class="text-sm text-gray-500">Check current stock limits</p>
        </a>
        <a href="<?= BASE_URL ?>/dealer/transactions" class="block p-4 border rounded-lg hover:bg-gray-50 transition-colors">
            <div class="text-blue-600 mb-2"><i class="fas fa-exchange-alt text-2xl"></i></div>
            <h4 class="font-semibold text-gray-800">Recent Transactions</h4>
            <p class="text-sm text-gray-500">Track orders and dispatches</p>
        </a>
        <a href="<?= BASE_URL ?>/dealer/profit-report" class="block p-4 border rounded-lg hover:bg-gray-50 transition-colors">
            <div class="text-purple-600 mb-2"><i class="fas fa-chart-line text-2xl"></i></div>
            <h4 class="font-semibold text-gray-800">Profit Report</h4>
            <p class="text-sm text-gray-500">View your commission earnings</p>
        </a>
    </div>
</div>
