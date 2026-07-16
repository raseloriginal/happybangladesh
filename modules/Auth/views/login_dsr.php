<?php $pageTitle = 'DSR Login'; ?>

<div class="h-screen w-full bg-blue-600 flex flex-col relative overflow-hidden" style="max-width: 480px; margin: 0 auto;">
    
    <!-- Top Branding Area -->
    <div class="flex-1 flex flex-col items-center justify-center p-8 text-white relative z-10">
        <div class="w-20 h-20 bg-white/20 backdrop-blur-md rounded-3xl flex items-center justify-center mb-6 shadow-xl border border-white/30">
            <i class="fa-solid fa-truck-fast text-4xl text-white"></i>
        </div>
        <h1 class="text-3xl font-extrabold tracking-tight mb-2">Delivery App</h1>
        <p class="text-blue-100 text-center text-sm font-medium">Log in to manage your deliveries, collections, and van stock.</p>
    </div>

    <!-- Bottom Sheet Form Area -->
    <div class="bg-white rounded-t-[32px] px-6 pt-10 pb-8 shadow-[0_-10px_40px_rgba(0,0,0,0.2)] relative z-20 flex flex-col w-full h-[60%] justify-between">
        <div class="absolute top-4 left-1/2 -translate-x-1/2 w-12 h-1.5 bg-gray-200 rounded-full"></div>
        
        <div class="flex-1 overflow-y-auto no-scrollbar">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Welcome Back!</h2>
            
            <?php $flash = Auth::getFlash(); if ($flash): ?>
                <div class="rounded-2xl bg-red-50 p-4 mb-6 flex items-start border border-red-100">
                    <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5 mr-3"></i>
                    <p class="text-sm text-red-700 font-medium leading-tight"><?= h($flash['message']) ?></p>
                </div>
            <?php endif; ?>

            <form action="<?= url('dsr/login') ?>" method="POST" class="space-y-5">
                <?= Helpers::csrfField() ?>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Phone or Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-mobile-screen-button text-gray-400"></i>
                        </div>
                        <input type="text" name="email" required class="block w-full pl-11 pr-4 py-4 bg-gray-50 border border-gray-200 rounded-2xl text-gray-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-base" placeholder="Enter your ID">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" name="password" required class="block w-full pl-11 pr-4 py-4 bg-gray-50 border border-gray-200 rounded-2xl text-gray-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-base" placeholder="Enter password">
                    </div>
                </div>

                <div class="pt-4 pb-2">
                    <button type="submit" class="w-full py-4 bg-blue-600 text-white rounded-2xl font-bold text-lg shadow-lg shadow-blue-600/30 active:scale-[0.98] transition-transform">
                        Login Now
                    </button>
                </div>
            </form>
            
            <div class="mt-6 text-center">
                <a href="<?= url('login') ?>" class="text-sm font-bold text-gray-400 hover:text-gray-600">
                    Wrong app? Go to Portals
                </a>
            </div>
        </div>
    </div>
</div>
