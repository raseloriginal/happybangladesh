<?php $pageTitle = 'Select Portal'; ?>

<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8 bg-cover bg-center" style="background-image: url('<?= asset('img/portal-bg.jpg') ?>');">
    <!-- Dark overlay if we had an image, for now just a nice gradient -->
    <div class="absolute inset-0 bg-gradient-to-br from-blue-900 to-indigo-900 opacity-90"></div>
    
    <div class="max-w-4xl w-full space-y-8 relative z-10">
        <div>
            <div class="mx-auto h-20 w-20 bg-white rounded-2xl shadow-xl flex items-center justify-center">
                <span class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">HB</span>
            </div>
            <h2 class="mt-6 text-center text-4xl font-extrabold text-white">
                HappyBangladesh DMS
            </h2>
            <p class="mt-2 text-center text-sm text-blue-200">
                Please select your role portal to login
            </p>
        </div>

        <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            
            <!-- Admin Portal -->
            <a href="<?= url('admin/login') ?>" class="group bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-6 hover:bg-white/20 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl flex flex-col items-center text-center">
                <div class="w-16 h-16 rounded-full bg-blue-500/20 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-user-shield text-2xl text-blue-300 group-hover:text-white transition-colors"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Admin</h3>
                <p class="text-xs text-blue-200">System configuration and master controls</p>
            </a>

            <!-- Manager Portal -->
            <a href="<?= url('manager/login') ?>" class="group bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-6 hover:bg-white/20 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl flex flex-col items-center text-center">
                <div class="w-16 h-16 rounded-full bg-emerald-500/20 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-users-gear text-2xl text-emerald-300 group-hover:text-white transition-colors"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Manager</h3>
                <p class="text-xs text-emerald-100">Inventory, dispatch, and reports</p>
            </a>

            <!-- SR Portal -->
            <a href="<?= url('sr/login') ?>" class="group bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-6 hover:bg-white/20 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl flex flex-col items-center text-center">
                <div class="w-16 h-16 rounded-full bg-amber-500/20 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-briefcase text-2xl text-amber-300 group-hover:text-white transition-colors"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Sales Rep</h3>
                <p class="text-xs text-amber-100">Market orders and retailer tracking</p>
            </a>

            <!-- DSR Portal -->
            <a href="<?= url('dsr/login') ?>" class="group bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-6 hover:bg-white/20 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl flex flex-col items-center text-center">
                <div class="w-16 h-16 rounded-full bg-rose-500/20 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-truck-fast text-2xl text-rose-300 group-hover:text-white transition-colors"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Delivery Rep</h3>
                <p class="text-xs text-rose-100">Van stock, delivery, and settlement</p>
            </a>

        </div>

    </div>
</div>
