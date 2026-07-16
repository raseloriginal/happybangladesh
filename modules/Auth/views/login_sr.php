<?php $pageTitle = 'SR Login'; ?>

<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="flex justify-center mb-6">
            <div class="h-16 w-16 bg-amber-500 rounded-2xl shadow-lg flex items-center justify-center text-white">
                <i class="fa-solid fa-briefcase text-3xl"></i>
            </div>
        </div>
        <h2 class="text-center text-3xl font-extrabold text-gray-900">
            Sales Rep Portal
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            Sign in to start taking orders
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0">
        <div class="bg-white py-8 px-4 shadow-xl shadow-amber-900/5 sm:rounded-3xl sm:px-10 border border-amber-100">
            
            <?php $flash = Auth::getFlash(); if ($flash): ?>
                <div class="rounded-xl bg-red-50 p-4 mb-6 flex items-center">
                    <i class="fa-solid fa-triangle-exclamation text-red-500 mr-3"></i>
                    <p class="text-sm text-red-700 font-medium"><?= h($flash['message']) ?></p>
                </div>
            <?php endif; ?>

            <form action="<?= url('sr/login') ?>" method="POST" class="space-y-6">
                <?= Helpers::csrfField() ?>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Email or Phone</label>
                    <input type="text" name="email" required class="block w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:bg-white transition-all sm:text-sm">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-bold text-gray-700">Password</label>
                        <a href="<?= url('forgot') ?>" class="text-xs font-bold text-amber-600 hover:text-amber-500">Forgot password?</a>
                    </div>
                    <input type="password" name="password" required class="block w-full px-4 py-3 border border-gray-300 rounded-xl bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:bg-white transition-all sm:text-sm">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-md shadow-amber-500/30 text-sm font-bold text-white bg-amber-500 hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-all active:scale-[0.98]">
                        Login to Sales Portal
                    </button>
                </div>
            </form>
            
            <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                <a href="<?= url('login') ?>" class="text-sm font-semibold text-gray-500 hover:text-gray-700 inline-flex items-center">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Portals
                </a>
            </div>
        </div>
    </div>
</div>
