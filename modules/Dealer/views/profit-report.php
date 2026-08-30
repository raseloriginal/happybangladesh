<?php $pageTitle = 'লাভের গ্রাফ ও হিসাব'; ?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">লাভের গ্রাফ ও হিসাব</h2>
    <p class="text-sm text-gray-500 mt-1">আপনার ব্যবসায়ের লাভজনক পণ্য ও বিক্রির তুলনা</p>
</div>

<div class="space-y-6">
    <!-- Net Profit Chart (Elevated Card) -->
    <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm">
        <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4">নিট লাভ</h4>
        <div class="h-[350px]">
            <canvas id="netProfitChart"></canvas>
        </div>
    </div>

    <!-- Top Products Chart (Togglable) -->
    <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
            <div>
                <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wider">সর্বাধিক বিক্রিত পণ্য</h4>
            </div>
            <div class="flex bg-gray-100 p-0.5 rounded-xl border border-gray-200 self-start">
                <button id="btnQty" class="px-4 py-1.5 text-xs font-bold rounded-lg transition duration-200 bg-white text-emerald-700 shadow-sm">পরিমাণ অনুসারে</button>
                <button id="btnVal" class="px-4 py-1.5 text-xs font-bold rounded-lg transition duration-200 text-gray-700 hover:text-gray-900">মূল্য অনুসারে</button>
            </div>
        </div>
        <div class="h-[350px]">
            <canvas id="topProductsChart"></canvas>
        </div>
    </div>

    <!-- Category-wise Sales & Order-to-Delivery Performance -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Category-wise Sales Chart -->
        <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex flex-col">
            <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4">ক্যাটেগরি অনুযায়ী বিক্রয়</h4>
            <div class="h-[300px] flex items-center justify-center flex-grow">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>

        <!-- Order-to-Delivery Performance Chart -->
        <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex flex-col">
            <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4">ডেলিভারি পারফরমেন্স</h4>
            <div class="h-[300px] flex items-center justify-center flex-grow">
                <canvas id="deliveryChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Profit Margin by Product Chart -->
    <div class="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm">
        <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4">পণ্যের লাভের হার (শীর্ষ ১০)</h4>
        <div class="h-[350px]">
            <canvas id="profitMarginChart"></canvas>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const isMobile = window.innerWidth < 768;

    // ----------------------------------------------------
    // 1. Net Profit Chart
    // ----------------------------------------------------
    const ctxNet = document.getElementById('netProfitChart').getContext('2d');
    const netLabels = <?= json_encode($labels ?? []) ?>;
    const netData = <?= json_encode($chartData ?? []) ?>;
    
    let gradNet = ctxNet.createLinearGradient(0, 0, 0, 350);
    gradNet.addColorStop(0, 'rgba(16, 185, 129, 0.4)');
    gradNet.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

    new Chart(ctxNet, {
        type: 'line',
        data: {
            labels: netLabels,
            datasets: [{
                label: 'নিট লাভ (৳)',
                data: netData,
                borderColor: '#059669',
                backgroundColor: gradNet,
                borderWidth: 2,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#059669',
                pointBorderWidth: 2,
                pointRadius: isMobile ? 3 : 4,
                pointHoverRadius: 5,
                fill: true,
                tension: 0.4
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
                    padding: 10,
                    displayColors: false,
                    callbacks: {
                        label: function(context) { return '৳ ' + context.parsed.y.toLocaleString(); }
                    }
                }
            },
            scales: {
                x: { 
                    grid: { display: false }, 
                    ticks: { 
                        font: { family: 'Inter', size: isMobile ? 9 : 11 }, 
                        color: '#6b7280',
                        autoSkip: true,
                        maxTicksLimit: isMobile ? 6 : 12
                    } 
                },
                y: {
                    grid: { color: '#f3f4f6', drawBorder: false },
                    ticks: { 
                        font: { family: 'Inter', size: isMobile ? 9 : 11 }, 
                        color: '#6b7280', 
                        callback: function(v) { return '৳ ' + v.toLocaleString(); },
                        maxTicksLimit: isMobile ? 5 : 8
                    },
                    beginAtZero: true
                }
            }
        }
    });

    // ----------------------------------------------------
    // 2. Top Products Chart (Togglable)
    // ----------------------------------------------------
    const ctxProd = document.getElementById('topProductsChart').getContext('2d');
    
    const qtyLabels = <?= json_encode(array_keys($topProductsQty ?? [])) ?>;
    const qtyValues = <?= json_encode(array_map(function($v) { return $v['qty']; }, $topProductsQty ?? [])) ?>;
    
    const valLabels = <?= json_encode(array_keys($topProductsVal ?? [])) ?>;
    const valValues = <?= json_encode(array_map(function($v) { return $v['value']; }, $topProductsVal ?? [])) ?>;

    let currentChart = new Chart(ctxProd, {
        type: 'bar',
        data: {
            labels: qtyLabels,
            datasets: [{
                label: 'বিক্রির পরিমাণ (টি)',
                data: qtyValues,
                backgroundColor: '#059669',
                borderRadius: 6,
                barThickness: isMobile ? 12 : 24
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { 
                    grid: { display: false },
                    ticks: { 
                        font: { family: 'Inter', size: isMobile ? 8 : 10 },
                        maxRotation: isMobile ? 45 : 0,
                        minRotation: isMobile ? 45 : 0
                    }
                },
                y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { family: 'Inter', size: isMobile ? 9 : 10 } } }
            }
        }
    });

    document.getElementById('btnQty').addEventListener('click', function() {
        this.classList.add('bg-white', 'text-emerald-700', 'shadow-sm');
        this.classList.remove('text-gray-700');
        document.getElementById('btnVal').classList.remove('bg-white', 'text-emerald-700', 'shadow-sm');
        document.getElementById('btnVal').classList.add('text-gray-700');

        currentChart.destroy();
        currentChart = new Chart(ctxProd, {
            type: 'bar',
            data: {
                labels: qtyLabels,
                datasets: [{
                    label: 'বিক্রির পরিমাণ (টি)',
                    data: qtyValues,
                    backgroundColor: '#059669',
                    borderRadius: 6,
                    barThickness: isMobile ? 12 : 24
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { 
                        grid: { display: false },
                        ticks: { 
                            font: { family: 'Inter', size: isMobile ? 8 : 10 },
                            maxRotation: isMobile ? 45 : 0,
                            minRotation: isMobile ? 45 : 0
                        }
                    },
                    y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { family: 'Inter', size: isMobile ? 9 : 10 } } }
                }
            }
        });
    });

    document.getElementById('btnVal').addEventListener('click', function() {
        this.classList.add('bg-white', 'text-emerald-700', 'shadow-sm');
        this.classList.remove('text-gray-700');
        document.getElementById('btnQty').classList.remove('bg-white', 'text-emerald-700', 'shadow-sm');
        document.getElementById('btnQty').classList.add('text-gray-700');

        currentChart.destroy();
        currentChart = new Chart(ctxProd, {
            type: 'bar',
            data: {
                labels: valLabels,
                datasets: [{
                    label: 'বিক্রির মূল্য (৳)',
                    data: valValues,
                    backgroundColor: '#10b981',
                    borderRadius: 6,
                    barThickness: isMobile ? 12 : 24
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { 
                        grid: { display: false },
                        ticks: { 
                            font: { family: 'Inter', size: isMobile ? 8 : 10 },
                            maxRotation: isMobile ? 45 : 0,
                            minRotation: isMobile ? 45 : 0
                        }
                    },
                    y: { 
                        beginAtZero: true, 
                        grid: { color: '#f3f4f6' }, 
                        ticks: { 
                            font: { family: 'Inter', size: isMobile ? 9 : 10 },
                            callback: function(v) { return '৳' + v.toLocaleString(); } 
                        } 
                    }
                }
            }
        });
    });

    // ----------------------------------------------------
    // 3. Category Chart (Pie/Doughnut)
    // ----------------------------------------------------
    const ctxCat = document.getElementById('categoryChart').getContext('2d');
    const catLabels = <?= json_encode(array_keys($categoryPerformance ?? [])) ?>;
    const catData = <?= json_encode(array_values($categoryPerformance ?? [])) ?>;

    new Chart(ctxCat, {
        type: 'doughnut',
        data: {
            labels: catLabels,
            datasets: [{
                data: catData,
                backgroundColor: ['#059669', '#10b981', '#34d399', '#6ee7b7', '#a7f3d0', '#047857'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    position: isMobile ? 'bottom' : 'right', 
                    labels: { boxWidth: 10, font: { family: 'Inter', size: 10 } } 
                }
            }
        }
    });

    // ----------------------------------------------------
    // 4. Delivery Performance (Pie/Doughnut)
    // ----------------------------------------------------
    const ctxDel = document.getElementById('deliveryChart').getContext('2d');
    const outTotal = <?= json_encode($totalOut ?? 0) ?>;
    const inTotal = <?= json_encode($totalIn ?? 0) ?>;
    const successTotal = Math.max(0, outTotal - inTotal);

    new Chart(ctxDel, {
        type: 'pie',
        data: {
            labels: ['ডেলিভারি সম্পন্ন', 'ফেরত আসা'],
            datasets: [{
                data: [successTotal, inTotal],
                backgroundColor: ['#10b981', '#f87171'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    position: isMobile ? 'bottom' : 'right', 
                    labels: { boxWidth: 10, font: { family: 'Inter', size: 10 } } 
                }
            }
        }
    });

    // ----------------------------------------------------
    // 5. Profit Margin Chart (Horizontal Bar)
    // ----------------------------------------------------
    const ctxMargin = document.getElementById('profitMarginChart').getContext('2d');
    const marginLabels = <?= json_encode(array_keys($topProductsMargin ?? [])) ?>;
    const marginData = <?= json_encode(array_map(function($v) { return $v['margin']; }, $topProductsMargin ?? [])) ?>;

    new Chart(ctxMargin, {
        type: 'bar',
        data: {
            labels: marginLabels,
            datasets: [{
                label: 'লাভের হার (%)',
                data: marginData,
                backgroundColor: '#059669',
                borderRadius: 4,
                barThickness: isMobile ? 10 : 16
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { max: 100, ticks: { font: { family: 'Inter', size: isMobile ? 9 : 10 }, callback: function(v) { return v + '%'; } } },
                y: { grid: { display: false }, ticks: { font: { family: 'Inter', size: isMobile ? 8 : 10 } } }
            }
        }
    });
});
</script>
