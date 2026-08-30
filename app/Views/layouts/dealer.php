<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dealer Panel - HappyBangladesh</title>
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/tailwind.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Noto+Sans+Bengali:wght@300;400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { font-family: 'Inter', 'Hind Siliguri', 'Noto Sans Bengali', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-emerald-800 text-white flex flex-col h-full shadow-xl">
        <div class="h-16 flex items-center justify-center border-b border-emerald-700 bg-emerald-900">
            <h1 class="text-xl font-bold tracking-wider">Happy<span class="text-emerald-300">Dealer</span></h1>
        </div>
        
        <nav class="flex-1 overflow-y-auto py-4">
            <ul class="space-y-1">
                <li>
                    <a href="<?= BASE_URL ?>/dealer/dashboard" class="flex items-center px-6 py-3 text-emerald-100 hover:bg-emerald-700 hover:text-white transition-colors <?= str_contains($_SERVER['REQUEST_URI'], 'dashboard') ? 'bg-emerald-700 text-white font-semibold' : '' ?>">
                        <i class="fas fa-home w-5 mr-3"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/dealer/inventory" class="flex items-center px-6 py-3 text-emerald-100 hover:bg-emerald-700 hover:text-white transition-colors <?= str_contains($_SERVER['REQUEST_URI'], 'inventory') ? 'bg-emerald-700 text-white font-semibold' : '' ?>">
                        <i class="fas fa-boxes w-5 mr-3"></i> Inventory
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/dealer/transactions" class="flex items-center px-6 py-3 text-emerald-100 hover:bg-emerald-700 hover:text-white transition-colors <?= str_contains($_SERVER['REQUEST_URI'], 'transactions') ? 'bg-emerald-700 text-white font-semibold' : '' ?>">
                        <i class="fas fa-exchange-alt w-5 mr-3"></i> Transactions
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/dealer/profit-report" class="flex items-center px-6 py-3 text-emerald-100 hover:bg-emerald-700 hover:text-white transition-colors <?= str_contains($_SERVER['REQUEST_URI'], 'profit-report') ? 'bg-emerald-700 text-white font-semibold' : '' ?>">
                        <i class="fas fa-chart-line w-5 mr-3"></i> Profit Report
                    </a>
                </li>
            </ul>
        </nav>
        
        <div class="p-4 border-t border-emerald-700">
            <div class="flex items-center text-sm mb-4">
                <div class="w-8 h-8 rounded-full bg-emerald-600 flex items-center justify-center font-bold mr-3">
                    <?= substr($_SESSION['dealer_name'] ?? 'D', 0, 1) ?>
                </div>
                <div class="truncate">
                    <p class="font-semibold text-emerald-50"><?= htmlspecialchars($_SESSION['dealer_name'] ?? 'Dealer') ?></p>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/dealer/logout" class="block w-full py-2 px-4 bg-emerald-700 hover:bg-red-600 text-center rounded text-sm transition-colors text-white font-medium">
                <i class="fas fa-sign-out-alt mr-2"></i>Logout
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <!-- Top Header -->
        <header class="h-16 bg-white shadow-sm flex items-center justify-between px-6 z-10">
            <h2 class="text-xl font-semibold text-gray-800">
                <!-- Title passed from view or dynamic -->
                <?= $pageTitle ?? 'Dealer Panel' ?>
            </h2>
            <div class="flex items-center text-gray-500">
                <span class="mr-2 text-sm">Welcome back,</span>
                <span class="font-semibold text-gray-700"><?= htmlspecialchars($_SESSION['dealer_name'] ?? '') ?></span>
            </div>
        </header>

        <!-- Main Body -->
        <div class="flex-1 overflow-y-auto p-6 bg-gray-50">
            <?php 
                $flash = Auth::getFlash();
                if ($flash && $flash['type'] === 'success'): 
            ?>
                <div class="mb-4 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700 flex items-center">
                    <i class="fas fa-check-circle mr-2"></i> <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <?php if ($flash && $flash['type'] === 'error'): ?>
                <div class="mb-4 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i> <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <?= $content ?>
        </div>
    </main>
    
</body>
</html>
