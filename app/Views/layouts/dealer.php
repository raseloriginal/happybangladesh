<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dealer Panel - HappyBangladesh</title>
    
    <link rel="stylesheet" href="<?= asset('css/tailwind.css') ?>">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Noto+Sans+Bengali:wght@300;400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        body { font-family: 'Inter', 'Hind Siliguri', 'Noto Sans Bengali', sans-serif; background-color: #f4f7fa; }
        /* Hide scrollbar for Chrome, Safari and Opera */
        .scrollbar-none::-webkit-scrollbar {
            display: none;
        }
        /* Hide scrollbar for IE, Edge and Firefox */
        .scrollbar-none {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
    </style>
</head>
<body class="flex flex-col h-screen overflow-hidden">

    <!-- Top Header -->
    <header class="h-20 bg-white shadow-sm flex items-center justify-between px-4 md:px-8 border-b border-gray-100 z-10 shrink-0">
        <div class="flex items-center gap-3 md:gap-4">
            <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-lg shadow-sm shrink-0">
                <?= substr($_SESSION['dealer_name'] ?? 'M', 0, 1) ?>
            </div>
            <div class="flex flex-col">
                <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wide">Dealer Panel</span>
                <span class="font-bold text-gray-800 text-xs md:text-sm truncate max-w-[150px] md:max-w-none">স্বাগতম, <?= htmlspecialchars($_SESSION['dealer_name'] ?? 'User') ?></span>
            </div>
        </div>
        
        <div class="flex items-center gap-2 md:gap-3">
            <button id="toggleFilterBtn" class="w-10 h-10 rounded-xl bg-white border border-emerald-100 text-emerald-600 flex items-center justify-center shadow-sm hover:bg-emerald-50 transition-colors relative">
                <i class="far fa-calendar-alt text-base md:text-lg"></i>
                <?php if (!empty($_GET['start_date']) && !empty($_GET['end_date'])): ?>
                    <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-emerald-600 rounded-full animate-pulse"></span>
                <?php endif; ?>
            </button>
            <a href="<?= BASE_URL ?>/dealer/logout" class="w-10 h-10 rounded-xl bg-white border border-red-100 text-red-500 flex items-center justify-center shadow-sm hover:bg-red-50 transition-colors">
                <i class="fas fa-sign-out-alt text-base md:text-lg"></i>
            </a>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 overflow-y-auto w-full">
        <div class="max-w-7xl mx-auto p-4 md:p-8">

            <!-- Collapsible Date Filter Panel -->
            <div id="filterPanel" class="hidden mb-6 bg-white rounded-2xl p-4 border-2 border-emerald-200 shadow-md transition duration-300">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                    <div class="flex items-center gap-2 text-slate-900 font-bold">
                        <i class="fas fa-sliders-h text-emerald-600"></i>
                        <span>তারিখ বাছাই করুন</span>
                    </div>
                    <button id="closeFilterBtn" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">শুরুর দিন</label>
                        <input type="date" id="startDate" class="w-full px-3 py-2 md:py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-sm focus:outline-none focus:border-emerald-600 focus:bg-white font-bold" value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">শেষের দিন</label>
                        <input type="date" id="endDate" class="w-full px-3 py-2 md:py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-slate-900 text-sm focus:outline-none focus:border-emerald-600 focus:bg-white font-bold" value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>" />
                    </div>
                    <div class="flex gap-2">
                        <button id="applyDateFilter" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm py-2.5 px-4 rounded-xl shadow-md transition duration-200 flex items-center justify-center gap-1.5">
                            <i class="fas fa-check text-xs"></i> খুঁজুন
                        </button>
                        <button id="resetDateFilter" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold text-sm py-2.5 px-4 rounded-xl transition duration-200 flex items-center justify-center gap-1.5">
                            <i class="fas fa-redo text-xs"></i> সব মুছুন
                        </button>
                    </div>
                </div>
            </div>
            
            <?php 
                $uri = $_SERVER['REQUEST_URI'];
                $isDashboard = str_contains($uri, 'dashboard');
                $isTransactions = str_contains($uri, 'transactions');
                $isProfit = str_contains($uri, 'profit-report');
                $isSrTracking = str_contains($uri, 'sr-tracking');
                $isDsrTracking = str_contains($uri, 'dsr-tracking');
            ?>

            <!-- Top Navigation Tabs -->
            <div class="flex gap-2 md:gap-3 mb-6 md:mb-8 overflow-x-auto pb-2 scrollbar-none whitespace-nowrap">
                <a href="<?= BASE_URL ?>/dealer/dashboard<?= !empty($_GET['start_date']) ? '?start_date='.$_GET['start_date'].'&end_date='.$_GET['end_date'] : '' ?>" class="px-4 md:px-6 py-2 md:py-2.5 font-bold text-xs md:text-sm rounded-lg shadow-sm flex items-center gap-2 transition-colors <?= $isDashboard ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 border border-gray-100 hover:bg-gray-50 hover:text-emerald-600' ?>">
                    <i class="fas fa-home"></i> ড্যাশবোর্ড
                </a>
                <a href="<?= BASE_URL ?>/dealer/transactions<?= !empty($_GET['start_date']) ? '?start_date='.$_GET['start_date'].'&end_date='.$_GET['end_date'] : '' ?>" class="px-4 md:px-6 py-2 md:py-2.5 font-bold text-xs md:text-sm rounded-lg shadow-sm flex items-center gap-2 transition-colors <?= $isTransactions ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 border border-gray-100 hover:bg-gray-50 hover:text-emerald-600' ?>">
                    <i class="fas fa-file-invoice"></i> বিল সমূহ
                </a>
                <a href="<?= BASE_URL ?>/dealer/profit-report<?= !empty($_GET['start_date']) ? '?start_date='.$_GET['start_date'].'&end_date='.$_GET['end_date'] : '' ?>" class="px-4 md:px-6 py-2 md:py-2.5 font-bold text-xs md:text-sm rounded-lg shadow-sm flex items-center gap-2 transition-colors <?= $isProfit ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 border border-gray-100 hover:bg-gray-50 hover:text-emerald-600' ?>">
                    <i class="fas fa-chart-pie"></i> লাভের গ্রাফ ও হিসাব
                </a>
                <a href="<?= BASE_URL ?>/dealer/sr-tracking" class="px-4 md:px-6 py-2 md:py-2.5 font-bold text-xs md:text-sm rounded-lg shadow-sm flex items-center gap-2 transition-colors <?= $isSrTracking ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 border border-gray-100 hover:bg-gray-50 hover:text-emerald-600' ?>">
                    <i class="fas fa-location-dot"></i> SR ট্র্যাকিং
                </a>
                <a href="<?= BASE_URL ?>/dealer/dsr-tracking" class="px-4 md:px-6 py-2 md:py-2.5 font-bold text-xs md:text-sm rounded-lg shadow-sm flex items-center gap-2 transition-colors <?= $isDsrTracking ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 border border-gray-100 hover:bg-gray-50 hover:text-emerald-600' ?>">
                    <i class="fas fa-truck-fast"></i> DSR ট্র্যাকিং
                </a>
            </div>

            <!-- Flash Messages -->
            <?php 
                $flash = Auth::getFlash();
                if ($flash && $flash['type'] === 'success'): 
            ?>
                <div class="mb-4 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700 flex items-center font-semibold">
                    <i class="fas fa-check-circle mr-2"></i> <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <?php if ($flash && $flash['type'] === 'error'): ?>
                <div class="mb-4 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 flex items-center font-semibold">
                    <i class="fas fa-exclamation-circle mr-2"></i> <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <!-- Page Content -->
            <?= $content ?>

        </div>
    </main>

    <!-- Filter Toggle Scripts -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('toggleFilterBtn');
        const closeBtn = document.getElementById('closeFilterBtn');
        const filterPanel = document.getElementById('filterPanel');
        const applyBtn = document.getElementById('applyDateFilter');
        const resetBtn = document.getElementById('resetDateFilter');
        const startDateInput = document.getElementById('startDate');
        const endDateInput = document.getElementById('endDate');

        toggleBtn.addEventListener('click', function() {
            filterPanel.classList.toggle('hidden');
        });

        closeBtn.addEventListener('click', function() {
            filterPanel.classList.add('hidden');
        });

        applyBtn.addEventListener('click', function() {
            const start = startDateInput.value;
            const end = endDateInput.value;
            if (start && end) {
                const url = new URL(window.location.href);
                url.searchParams.set('start_date', start);
                url.searchParams.set('end_date', end);
                window.location.href = url.toString();
            } else {
                alert('অনুগ্রহ করে শুরুর দিন এবং শেষের দিন নির্বাচন করুন');
            }
        });

        resetBtn.addEventListener('click', function() {
            const url = new URL(window.location.href);
            url.searchParams.delete('start_date');
            url.searchParams.delete('end_date');
            window.location.href = url.toString();
        });
    });
    </script>
    
</body>
</html>
