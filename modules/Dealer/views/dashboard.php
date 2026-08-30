<?php $pageTitle = ''; ?>

<!-- Top Metrics Cards -->
<div class="flex flex-col lg:flex-row gap-4 mb-6">
    <!-- Emerald Card (Net Profit) -->
    <div class="flex-1 rounded-2xl p-6 text-white shadow-md relative overflow-hidden flex flex-col justify-between min-h-[160px] bg-emerald-800" style="background-color: #065f46;">
        <div class="absolute right-0 top-0 opacity-10 transform translate-x-4 -translate-y-4">
            <i class="fas fa-shield-alt text-9xl"></i>
        </div>
        
        <div>
            <div class="text-[10px] font-bold text-emerald-200 tracking-wider uppercase mb-1">NET PROFIT | আসল লাভ</div>
            <div class="text-3xl md:text-4xl font-bold mb-1">৳<?= number_format($stats['net_profit'] ?? 0, 2) ?></div>
            <div class="text-xs text-emerald-100">আপনার নির্ধারিত সময়ের মোট আসল লাভ</div>
        </div>

        <div class="flex flex-wrap mt-6 gap-4 sm:gap-8 relative z-10">
            <div>
                <div class="text-[10px] font-bold text-emerald-200 tracking-wider uppercase mb-1">NET SALE | আসল বিক্রি</div>
                <div class="text-sm md:text-lg font-bold">৳<?= number_format($stats['net_sale'] ?? 0, 2) ?></div>
            </div>
            <div>
                <div class="text-[10px] font-bold text-emerald-200 tracking-wider uppercase mb-1">SUCCESS RATE | সফল ডেলিভারি</div>
                <div class="text-sm md:text-lg font-bold text-emerald-300"><?= number_format($stats['success_rate'] ?? 0, 2) ?>%</div>
            </div>
        </div>
    </div>

    <!-- White Card (Total Revenue & Gross Profit) -->
    <div class="w-full lg:w-[320px] bg-white border border-gray-100 rounded-2xl p-6 shadow-sm flex flex-col justify-between min-h-[160px]">
        <div class="flex justify-between items-start mb-2">
            <div>
                <div class="text-[10px] font-bold text-gray-400 tracking-wider uppercase mb-1">TOTAL REVENUE | মোট বিক্রি</div>
                <div class="text-2xl md:text-3xl font-bold text-gray-900">৳<?= number_format($stats['gross_sale'] ?? 0, 2) ?></div>
                <div class="text-xs text-gray-500">সম্পূর্ণ বিক্রির টাকা</div>
            </div>
            <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600 shrink-0">
                <i class="fas fa-shopping-cart"></i>
            </div>
        </div>
        
        <div class="flex justify-between mt-4 pt-4 border-t border-gray-50">
            <div>
                <div class="text-[10px] font-bold text-gray-400 tracking-wider uppercase mb-1">GROSS PROFIT | মোট লাভ</div>
                <div class="text-base md:text-lg font-bold text-gray-800">৳<?= number_format($stats['gross_profit'] ?? 0, 2) ?></div>
            </div>
            <div class="text-right">
                <div class="text-[10px] font-bold text-red-400 tracking-wider uppercase mb-1">DAMAGE | পণ্যের ক্ষতি</div>
                <div class="text-base md:text-lg font-bold text-red-500">৳<?= number_format($stats['damage'] ?? 0, 2) ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions (জরুরি কাজ) -->
<div class="mb-6">
    <h3 class="text-sm font-bold text-gray-700 mb-3">জরুরি কাজ</h3>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <a href="<?= BASE_URL ?>/dealer/inventory" class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-center justify-center hover:shadow-md transition-shadow group text-center">
            <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600 text-xl mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="font-bold text-gray-800 text-sm md:text-base">স্টক ও পণ্য</div>
            <div class="text-xs text-gray-500">স্টক ও পণ্য দেখুন</div>
        </a>
        <a href="<?= BASE_URL ?>/dealer/profit-report" class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-center justify-center hover:shadow-md transition-shadow group text-center">
            <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600 text-xl mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="font-bold text-gray-800 text-sm md:text-base">বিক্রির রিপোর্ট</div>
            <div class="text-xs text-gray-500">লাভ-ক্ষতির হিসাব</div>
        </a>
        <a href="<?= BASE_URL ?>/dealer/logout" class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-center justify-center hover:shadow-md transition-shadow group text-center">
            <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center text-red-400 text-xl mb-3 group-hover:scale-110 transition-transform">
                <i class="fas fa-power-off"></i>
            </div>
            <div class="font-bold text-gray-800 text-sm md:text-base">লগ আউট</div>
            <div class="text-xs text-gray-500">আইডি থেকে বের হোন</div>
        </a>
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-center justify-center hover:shadow-md transition-shadow text-center">
            <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center text-green-500 text-xl mb-3">
                <i class="fas fa-percentage"></i>
            </div>
            <div class="font-bold text-gray-800 uppercase tracking-widest text-[10px] mb-1">Happy %</div>
            <div class="font-bold text-green-600 text-base md:text-lg">50.00%</div>
        </div>
    </div>
</div>

<!-- Profit Graph Preview -->
<div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
    <div class="flex justify-between items-end mb-4">
        <div>
            <h3 class="font-bold text-gray-800">লাভের গ্রাফ</h3>
            <p class="text-xs text-gray-500">গত ৩০ দিনের আসল লাভের হিসাব</p>
        </div>
        <a href="<?= BASE_URL ?>/dealer/profit-report<?= !empty($_GET['start_date']) ? '?start_date='.$_GET['start_date'].'&end_date='.$_GET['end_date'] : '' ?>" class="text-xs font-bold text-emerald-600 hover:text-emerald-700">বিস্তারিত দেখুন <i class="fas fa-chevron-right ml-1"></i></a>
    </div>
    <div class="h-64 relative w-full">
        <canvas id="miniNetProfitChart"></canvas>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('miniNetProfitChart').getContext('2d');
    const labels = <?= json_encode($labels ?? []) ?>;
    const data = <?= json_encode($chartData ?? []) ?>;
    
    let gradient = ctx.createLinearGradient(0, 0, 0, 250);
    gradient.addColorStop(0, 'rgba(16, 185, 129, 0.3)');
    gradient.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'নিট লাভ (৳)',
                data: data,
                borderColor: '#059669',
                backgroundColor: gradient,
                borderWidth: 2,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#059669',
                pointBorderWidth: 1.5,
                pointRadius: 3,
                pointHoverRadius: 5,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1f2937',
                    titleFont: { family: 'Inter', size: 12 },
                    bodyFont: { family: 'Inter', size: 13, weight: 'bold' },
                    padding: 8,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return '৳ ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: { font: { family: 'Inter', size: 9 }, color: '#9ca3af', autoSkip: true, maxTicksLimit: 6 }
                },
                y: {
                    grid: { color: '#f3f4f6', drawBorder: false },
                    ticks: {
                        font: { family: 'Inter', size: 9 },
                        color: '#9ca3af',
                        callback: function(value) { return '৳' + value; },
                        maxTicksLimit: 5
                    },
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
