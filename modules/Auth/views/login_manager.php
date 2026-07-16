<?php $pageTitle = 'Manager Login'; ?>

<div class="min-h-screen bg-gray-50 flex">
    <!-- Left Panel - Login Form -->
    <div class="flex-1 flex flex-col justify-center px-4 sm:px-6 lg:px-20 xl:px-32 relative bg-white shadow-2xl z-10">
        <a href="<?= url('login') ?>" class="absolute top-8 left-8 lg:left-12 text-gray-400 hover:text-gray-600 transition flex items-center gap-2 text-sm font-medium">
            <i class="fa-solid fa-arrow-left"></i> Portals
        </a>

        <div class="mx-auto w-full max-w-sm mt-12 lg:mt-0">
            <div class="mb-8">
                <div class="h-12 w-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center mb-6">
                    <i class="fa-solid fa-users-gear text-2xl"></i>
                </div>
                <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight mb-2">Manager Portal</h2>
                <p class="text-gray-500 text-sm">Sign in to manage inventory, dispatches, and reports.</p>
            </div>

            <?php $flash = Auth::getFlash(); if ($flash): ?>
                <div class="rounded-xl bg-red-50 border border-red-100 p-4 mb-6 flex items-center">
                    <i class="fa-solid fa-circle-exclamation text-red-500 mr-3"></i>
                    <p class="text-sm text-red-700 font-medium"><?= h($flash['message']) ?></p>
                </div>
            <?php endif; ?>

            <form action="<?= url('manager/login') ?>" method="POST" class="space-y-5">
                <?= Helpers::csrfField() ?>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email / Phone</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <i class="fa-solid fa-user text-gray-400"></i>
                        </div>
                        <input type="text" name="email" required class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl leading-5 bg-gray-50 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:bg-white transition-all sm:text-sm shadow-sm">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-sm font-semibold text-gray-700">Password</label>
                        <a href="<?= url('forgot') ?>" class="text-xs font-semibold text-emerald-600 hover:text-emerald-500">Forgot password?</a>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <i class="fa-solid fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" name="password" required class="block w-full pl-10 pr-3 py-3 border border-gray-200 rounded-xl leading-5 bg-gray-50 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:bg-white transition-all sm:text-sm shadow-sm">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full flex justify-center items-center py-3.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all hover:shadow-lg">
                        Sign In <i class="fa-solid fa-arrow-right-to-bracket ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Panel - Branding/Image -->
    <div class="hidden lg:block lg:w-1/2 relative bg-emerald-900">
        <div class="absolute inset-0 bg-gradient-to-br from-emerald-800 to-teal-900 opacity-90 mix-blend-multiply"></div>
        <div class="absolute inset-0 flex flex-col items-center justify-center text-white px-12 text-center">
            <h2 class="text-4xl font-bold mb-4">Streamline Your Operations</h2>
            <p class="text-emerald-100 text-lg max-w-md">Efficiently manage warehouse inventory, assign dispatches, and track sales performance all in one place.</p>
        </div>
    </div>
</div>
