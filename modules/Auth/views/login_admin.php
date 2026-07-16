<?php $pageTitle = 'Admin Login'; ?>

<div class="min-h-screen bg-gray-900 flex">
    <!-- Left Panel - Branding -->
    <div class="hidden lg:flex lg:w-1/2 bg-gray-800 border-r border-gray-700 flex-col justify-center px-12 relative overflow-hidden">
        <!-- Abstract background pattern -->
        <div class="absolute inset-0 opacity-10">
            <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                        <path d="M 40 0 L 0 0 0 40" fill="none" stroke="currentColor" stroke-width="1"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#grid)" />
            </svg>
        </div>
        
        <div class="relative z-10 text-white">
            <div class="h-16 w-16 bg-blue-600 rounded-xl flex items-center justify-center mb-8 shadow-[0_0_30px_rgba(37,99,235,0.5)]">
                <i class="fa-solid fa-server text-3xl"></i>
            </div>
            <h1 class="text-4xl font-extrabold mb-4 tracking-tight">Admin<br>Control Center</h1>
            <p class="text-gray-400 text-lg leading-relaxed max-w-md">
                Secure access to master configuration, user management, and system-wide analytics for HappyBangladesh DMS.
            </p>
        </div>
    </div>

    <!-- Right Panel - Login Form -->
    <div class="flex-1 flex flex-col justify-center px-4 sm:px-6 lg:px-20 xl:px-32 relative">
        <a href="<?= url('login') ?>" class="absolute top-8 left-8 lg:left-12 text-gray-400 hover:text-white transition flex items-center gap-2 text-sm">
            <i class="fa-solid fa-arrow-left"></i> All Portals
        </a>

        <div class="mx-auto w-full max-w-sm">
            <div class="text-center lg:text-left mb-10">
                <h2 class="text-3xl font-bold text-white mb-2">Welcome Back</h2>
                <p class="text-gray-400 text-sm">Sign in to your administrator account</p>
            </div>

            <?php $flash = Auth::getFlash(); if ($flash): ?>
                <div class="rounded-lg bg-red-900/50 border border-red-500/50 p-4 mb-6">
                    <div class="flex items-center">
                        <i class="fa-solid fa-circle-exclamation text-red-400 mr-3"></i>
                        <p class="text-sm text-red-200"><?= h($flash['message']) ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <form action="<?= url('admin/login') ?>" method="POST" class="space-y-6">
                <?= Helpers::csrfField() ?>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-envelope text-gray-500"></i>
                        </div>
                        <input type="email" name="email" required class="block w-full pl-10 pr-3 py-3 border border-gray-700 rounded-xl leading-5 bg-gray-800 text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors sm:text-sm">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium text-gray-300">Password</label>
                        <a href="<?= url('forgot') ?>" class="text-xs font-medium text-blue-400 hover:text-blue-300">Forgot password?</a>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-lock text-gray-500"></i>
                        </div>
                        <input type="password" name="password" required class="block w-full pl-10 pr-3 py-3 border border-gray-700 rounded-xl leading-5 bg-gray-800 text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors sm:text-sm">
                    </div>
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-blue-600 hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-900 focus:ring-blue-500 transition-all">
                        Authenticate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
