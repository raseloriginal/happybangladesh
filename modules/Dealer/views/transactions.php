<?php $pageTitle = 'সাম্প্রতিক বিল সমূহ'; ?>

<style>
/* Custom Scrollbar for Modal */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1; 
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #c1c1c1; 
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8; 
}
</style>

<div class="mb-4">
    <h2 class="text-xl font-bold text-gray-800">সাম্প্রতিক বিল সমূহ</h2>
    <p class="text-sm text-gray-500">ডেসপাস, ফেরত ও বিক্রির বিস্তারিত হিসাব</p>
</div>

<!-- Main Table & Mobile Cards -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <!-- Desktop Table (Hidden on Mobile) -->
    <table class="hidden md:table w-full text-left text-sm text-gray-700">
        <thead class="bg-gray-50 border-b border-gray-200 font-bold text-gray-800">
            <tr>
                <th class="px-5 py-4">তারিখ</th>
                <th class="px-5 py-4">বের হয়েছে (ডেসপাস)</th>
                <th class="px-5 py-4">ফেরত এসেছে</th>
                <th class="px-5 py-4">আসল বিক্রি</th>
                <th class="px-5 py-4">মোট বিক্রি</th>
                <th class="px-5 py-4">আসল লাভ</th>
                <th class="px-5 py-4 text-center">সফলতা</th>
                <th class="px-5 py-4 text-center">অ্যাকশন</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 font-bold">
            <?php if(empty($bills)): ?>
            <tr>
                <td colspan="8" class="px-6 py-8 text-center text-gray-500 font-normal">কোনো বিল পাওয়া যায়নি।</td>
            </tr>
            <?php else: ?>
                <?php foreach($bills as $bill): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-4 text-gray-900"><?= $bill['date'] ?></td>
                    <td class="px-5 py-4">
                        <span class="text-blue-700 font-bold"><?= number_format($bill['dispatch_qty'] ?? 0) ?> টি</span>
                        <span class="text-xs text-gray-400 font-medium block">৳<?= number_format($bill['dispatch_value'] ?? 0, 2) ?></span>
                    </td>
                    <td class="px-5 py-4">
                        <span class="text-red-600 font-bold"><?= number_format($bill['return_qty'] ?? 0) ?> টি</span>
                        <span class="text-xs text-gray-400 font-medium block">৳<?= number_format($bill['return_value'] ?? 0, 2) ?></span>
                    </td>
                    <td class="px-5 py-4 text-emerald-700">৳<?= number_format($bill['net_sale'], 2) ?></td>
                    <td class="px-5 py-4 text-gray-800">৳<?= number_format($bill['gross_sale'], 2) ?></td>
                    <td class="px-5 py-4 text-blue-700 font-extrabold">৳<?= number_format($bill['net_profit'], 2) ?></td>
                    <td class="px-5 py-4 text-center">
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">
                            <?= number_format($bill['success_rate'], 1) ?>%
                        </span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <button onclick="openModal('<?= $bill['date'] ?>', <?= (float)$bill['gross_sale'] ?>, <?= (float)$bill['net_sale'] ?>, <?= (float)$bill['gross_profit'] ?>, <?= (float)$bill['net_profit'] ?>, <?= (float)$bill['success_rate'] ?>, <?= (int)($bill['dispatch_qty'] ?? 0) ?>, <?= (float)($bill['dispatch_value'] ?? 0) ?>, <?= (int)($bill['return_qty'] ?? 0) ?>, <?= (float)($bill['return_value'] ?? 0) ?>)" class="px-4 py-1.5 bg-blue-50 text-blue-600 rounded-full text-xs hover:bg-blue-100 border border-blue-200 transition-colors">
                            <i class="fas fa-eye mr-1"></i> দেখুন
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Mobile Card View (Hidden on Desktop) -->
    <div class="block md:hidden divide-y divide-gray-100">
        <?php if(empty($bills)): ?>
            <div class="p-6 text-center text-gray-500">কোনো বিল পাওয়া যায়নি।</div>
        <?php else: ?>
            <?php foreach($bills as $bill): ?>
            <div class="p-4 flex flex-col gap-3 hover:bg-gray-50">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-bold text-gray-800"><i class="far fa-calendar-alt text-emerald-600 mr-1.5"></i><?= $bill['date'] ?></span>
                    <span class="px-2.5 py-0.5 bg-green-50 text-green-700 rounded-full text-xs font-bold border border-green-200">
                        সফলতা: <?= number_format($bill['success_rate'], 1) ?>%
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="bg-blue-50/50 p-2.5 rounded-xl border border-blue-100">
                        <span class="text-blue-600 block text-[9px] font-bold uppercase tracking-wide">স্টক থেকে বের হয়েছে</span>
                        <div class="font-bold text-gray-800 text-sm"><?= number_format($bill['dispatch_qty'] ?? 0) ?> টি</div>
                        <div class="text-[10px] text-gray-500">৳<?= number_format($bill['dispatch_value'] ?? 0, 2) ?></div>
                    </div>
                    <div class="bg-red-50/50 p-2.5 rounded-xl border border-red-100">
                        <span class="text-red-500 block text-[9px] font-bold uppercase tracking-wide">ফেরত এসেছে</span>
                        <div class="font-bold text-gray-800 text-sm"><?= number_format($bill['return_qty'] ?? 0) ?> টি</div>
                        <div class="text-[10px] text-gray-500">৳<?= number_format($bill['return_value'] ?? 0, 2) ?></div>
                    </div>
                    <div class="bg-emerald-50/50 p-2.5 rounded-xl border border-emerald-100">
                        <span class="text-emerald-700 block text-[9px] font-bold uppercase tracking-wide">Net Sale (আসল বিক্রি)</span>
                        <div class="font-bold text-emerald-800 text-sm">৳<?= number_format($bill['net_sale'], 2) ?></div>
                        <div class="text-[10px] text-gray-500">মোট: ৳<?= number_format($bill['gross_sale'], 2) ?></div>
                    </div>
                    <div class="bg-amber-50/50 p-2.5 rounded-xl border border-amber-100">
                        <span class="text-amber-700 block text-[9px] font-bold uppercase tracking-wide">Net Profit (আসল লাভ)</span>
                        <div class="font-bold text-amber-800 text-sm">৳<?= number_format($bill['net_profit'], 2) ?></div>
                        <div class="text-[10px] text-gray-500">মোট লাভ: ৳<?= number_format($bill['gross_profit'], 2) ?></div>
                    </div>
                </div>
                <div class="flex justify-end pt-1">
                    <button onclick="openModal('<?= $bill['date'] ?>', <?= (float)$bill['gross_sale'] ?>, <?= (float)$bill['net_sale'] ?>, <?= (float)$bill['gross_profit'] ?>, <?= (float)$bill['net_profit'] ?>, <?= (float)$bill['success_rate'] ?>, <?= (int)($bill['dispatch_qty'] ?? 0) ?>, <?= (float)($bill['dispatch_value'] ?? 0) ?>, <?= (int)($bill['return_qty'] ?? 0) ?>, <?= (float)($bill['return_value'] ?? 0) ?>)" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-colors flex items-center justify-center gap-1.5 shadow-sm">
                        <i class="fas fa-eye"></i> বিস্তারিত দেখুন
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <div class="px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row gap-3 justify-between items-center bg-gray-50">
        <div class="text-sm font-bold text-gray-700">মোট <?= count($bills ?? []) ?> টি বিল পাওয়া গেছে</div>
        <div class="flex gap-1">
            <button class="w-8 h-8 rounded border border-gray-300 bg-white text-gray-400 flex items-center justify-center hover:bg-gray-50"><i class="fas fa-chevron-left text-xs"></i></button>
            <button class="w-8 h-8 rounded border border-blue-600 bg-blue-600 text-white flex items-center justify-center font-bold">1</button>
            <button class="w-8 h-8 rounded border border-gray-300 bg-white text-gray-700 flex items-center justify-center hover:bg-gray-50 font-bold">2</button>
            <button class="w-8 h-8 rounded border border-gray-300 bg-white text-gray-700 flex items-center justify-center hover:bg-gray-50 font-bold">3</button>
            <button class="w-8 h-8 rounded border border-gray-300 bg-white text-gray-700 flex items-center justify-center hover:bg-gray-50"><i class="fas fa-chevron-right text-xs"></i></button>
        </div>
    </div>
</div>

<!-- Bill Details Modal -->
<div id="billModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black bg-opacity-40 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-7xl max-h-[90vh] flex flex-col relative overflow-hidden transition-all transform">
        
        <!-- Modal Header -->
        <div class="px-6 py-4 flex justify-between items-start border-b border-gray-100">
            <div>
                <h3 class="text-xl font-bold text-gray-800">বিলের বিস্তারিত বিবরণ</h3>
                <p class="text-sm font-bold text-gray-500 mt-1" id="modal-date">2026-08-29</p>
            </div>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-700 p-2">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal Content (Scrollable) -->
        <div class="p-6 overflow-y-auto custom-scrollbar flex-1 bg-gray-50/50">
            
            <!-- Modal Summary Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-3 mb-6">
                <!-- Dispatched -->
                <div class="bg-white border border-blue-200 rounded-xl p-4 shadow-sm border-t-4 border-t-blue-500">
                    <div class="text-[10px] font-bold text-blue-600 uppercase tracking-wide mb-1">বের হয়েছে (ডেসপাস)</div>
                    <div class="text-lg font-bold text-gray-800"><span id="card-dispatch-qty">0</span> টি</div>
                    <div class="text-xs text-gray-500 font-semibold">৳<span id="card-dispatch-val">0.00</span></div>
                </div>
                <!-- Returned -->
                <div class="bg-white border border-red-200 rounded-xl p-4 shadow-sm border-t-4 border-t-red-400">
                    <div class="text-[10px] font-bold text-red-500 uppercase tracking-wide mb-1">ফেরত এসেছে</div>
                    <div class="text-lg font-bold text-gray-800"><span id="card-return-qty">0</span> টি</div>
                    <div class="text-xs text-gray-500 font-semibold">৳<span id="card-return-val">0.00</span></div>
                </div>
                <!-- Gross Sale -->
                <div class="bg-white border border-teal-200 rounded-xl p-4 shadow-sm border-t-4 border-t-teal-500">
                    <div class="text-[10px] font-bold text-teal-600 uppercase tracking-wide mb-1">Gross Sale | মোট বিক্রি</div>
                    <div class="text-lg font-bold text-gray-800">৳<span id="card-gross-sale">0.00</span></div>
                </div>
                <!-- Net Sale -->
                <div class="bg-white border border-emerald-200 rounded-xl p-4 shadow-sm border-t-4 border-t-emerald-500">
                    <div class="text-[10px] font-bold text-emerald-600 uppercase tracking-wide mb-1">Net Sale | আসল বিক্রি</div>
                    <div class="text-lg font-bold text-gray-800">৳<span id="card-net-sale">0.00</span></div>
                </div>
                <!-- Gross Profit -->
                <div class="bg-[#fffcf0] border border-[#fef08a] rounded-xl p-4 shadow-sm border-t-4 border-t-yellow-400">
                    <div class="text-[10px] font-bold text-yellow-700 uppercase tracking-wide mb-1">Gross Profit | মোট লাভ</div>
                    <div class="text-lg font-bold text-gray-800">৳<span id="card-gross-profit">0.00</span></div>
                </div>
                <!-- Net Profit -->
                <div class="bg-[#f0f7ff] border border-blue-200 rounded-xl p-4 shadow-sm border-t-4 border-t-blue-400">
                    <div class="text-[10px] font-bold text-blue-700 uppercase tracking-wide mb-1">Net Profit | আসল লাভ</div>
                    <div class="text-lg font-bold text-gray-800">৳<span id="card-net-profit">0.00</span></div>
                </div>
                <!-- Success Rate -->
                <div class="bg-[#f0fdf4] border border-green-200 rounded-xl p-4 shadow-sm border-t-4 border-t-green-500">
                    <div class="text-[10px] font-bold text-green-700 uppercase tracking-wide mb-1">Success Rate | ডেলিভারি</div>
                    <div class="text-lg font-bold text-gray-800"><span id="card-success-rate">0.00</span>%</div>
                </div>
            </div>

            <!-- Products Breakdown -->
            <h4 class="text-md font-bold text-gray-800 mb-3">কোন পণ্য কত বিক্রি হলো</h4>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <!-- Desktop Table View -->
                <table class="hidden md:table w-full text-center text-xs text-gray-700">
                    <thead class="bg-gray-50 border-b border-gray-200 font-bold text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left">পণ্যের নাম</th>
                            <th class="px-4 py-3">স্টক থেকে বের হয়েছে</th>
                            <th class="px-4 py-3">ফেরত এসেছে</th>
                            <th class="px-4 py-3">বিক্রি হয়েছে</th>
                            <th class="px-4 py-3">বের হওয়া দাম</th>
                            <th class="px-4 py-3">ফেরত আসা দাম</th>
                            <th class="px-4 py-3 text-green-600">বিক্রির টাকা (Net)</th>
                            <th class="px-4 py-3">মোট দাম (Gross)</th>
                            <th class="px-4 py-3 text-blue-600">লাভ</th>
                            <th class="px-4 py-3">সফলতার %</th>
                        </tr>
                    </thead>
                    <tbody id="modal-table-body" class="divide-y divide-gray-100 font-bold">
                        <!-- Loaded via JS -->
                    </tbody>
                </table>

                <!-- Mobile Card View -->
                <div id="modal-mobile-list" class="block md:hidden divide-y divide-gray-100 font-bold text-xs">
                    <!-- Loaded via JS -->
                </div>

                <div id="modal-loading" class="hidden p-10 text-center text-gray-400">
                    <i class="fas fa-circle-notch fa-spin text-3xl mb-2 text-blue-500"></i>
                    <p>লোড হচ্ছে...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openModal(date, grossSale, netSale, grossProfit, netProfit, successRate, dispatchQty = 0, dispatchVal = 0, returnQty = 0, returnVal = 0) {
    document.getElementById('billModal').classList.remove('hidden');
    document.getElementById('modal-date').innerText = date;
    
    document.getElementById('card-dispatch-qty').innerText = dispatchQty;
    document.getElementById('card-dispatch-val').innerText = dispatchVal.toFixed(2);
    document.getElementById('card-return-qty').innerText = returnQty;
    document.getElementById('card-return-val').innerText = returnVal.toFixed(2);
    document.getElementById('card-gross-sale').innerText = grossSale.toFixed(2);
    document.getElementById('card-net-sale').innerText = netSale.toFixed(2);
    document.getElementById('card-gross-profit').innerText = grossProfit.toFixed(2);
    document.getElementById('card-net-profit').innerText = netProfit.toFixed(2);
    document.getElementById('card-success-rate').innerText = successRate.toFixed(2);

    document.getElementById('modal-table-body').innerHTML = '';
    document.getElementById('modal-mobile-list').innerHTML = '';
    document.getElementById('modal-loading').classList.remove('hidden');

    fetch(`<?= BASE_URL ?>/dealer/transactions/bill?date=${date}`)
        .then(response => response.json())
        .then(res => {
            document.getElementById('modal-loading').classList.add('hidden');
            if (res.success) {
                if (res.summary) {
                    document.getElementById('card-dispatch-qty').innerText = res.summary.dispatch_qty || 0;
                    document.getElementById('card-dispatch-val').innerText = (res.summary.dispatch_value || 0).toFixed(2);
                    document.getElementById('card-return-qty').innerText = res.summary.return_qty || 0;
                    document.getElementById('card-return-val').innerText = (res.summary.return_value || 0).toFixed(2);
                    document.getElementById('card-gross-sale').innerText = (res.summary.gross_sale || 0).toFixed(2);
                    document.getElementById('card-net-sale').innerText = (res.summary.net_sale || 0).toFixed(2);
                    document.getElementById('card-gross-profit').innerText = (res.summary.gross_profit || 0).toFixed(2);
                    document.getElementById('card-net-profit').innerText = (res.summary.net_profit || 0).toFixed(2);
                    document.getElementById('card-success-rate').innerText = (res.summary.success_rate || 0).toFixed(2);
                }

                if (res.data) {
                    let html = '';
                    let mobileHtml = '';
                    res.data.forEach(item => {
                        html += `
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-left text-gray-800">${item.name}</td>
                                <td class="px-4 py-3 text-blue-700 font-bold">${item.out_qty}</td>
                                <td class="px-4 py-3 text-red-600 font-bold">${item.in_qty}</td>
                                <td class="px-4 py-3 text-emerald-700 font-bold">${item.sell_qty}</td>
                                <td class="px-4 py-3">৳${item.out_value.toFixed(2)}</td>
                                <td class="px-4 py-3">৳${item.in_value.toFixed(2)}</td>
                                <td class="px-4 py-3 text-emerald-700">৳${item.net_sale.toFixed(2)}</td>
                                <td class="px-4 py-3 text-gray-800">৳${item.total_sale.toFixed(2)}</td>
                                <td class="px-4 py-3 text-blue-600">৳${item.profit.toFixed(2)}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-[10px]">
                                        ${item.success_ratio.toFixed(1)}%
                                    </span>
                                </td>
                            </tr>
                        `;
                        mobileHtml += `
                            <div class="p-4 hover:bg-gray-50/50 flex flex-col gap-2.5">
                                <div class="text-gray-800 font-bold text-sm text-left">${item.name}</div>
                                <div class="grid grid-cols-3 gap-2 text-center text-[10px]">
                                    <div class="bg-blue-50/70 p-2 rounded-lg border border-blue-100">
                                        <span class="text-blue-600 block mb-0.5 font-bold">বের হয়েছে</span>
                                        <span class="text-gray-800 font-extrabold">${item.out_qty} টি</span>
                                        <span class="text-gray-400 block text-[9px]">৳${item.out_value.toFixed(2)}</span>
                                    </div>
                                    <div class="bg-red-50/70 p-2 rounded-lg border border-red-100">
                                        <span class="text-red-500 block mb-0.5 font-bold">ফেরত এসেছে</span>
                                        <span class="text-gray-800 font-extrabold">${item.in_qty} টি</span>
                                        <span class="text-gray-400 block text-[9px]">৳${item.in_value.toFixed(2)}</span>
                                    </div>
                                    <div class="bg-emerald-50/70 p-2 rounded-lg border border-emerald-100">
                                        <span class="text-emerald-700 block mb-0.5 font-bold">বিক্রি হয়েছে</span>
                                        <span class="text-emerald-800 font-extrabold">${item.sell_qty} টি</span>
                                        <span class="text-emerald-600 block text-[9px]">৳${item.net_sale.toFixed(2)}</span>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-x-2 gap-y-1 text-[11px] text-left pt-1">
                                    <div>
                                        <span class="text-gray-400">বের হওয়া দাম:</span>
                                        <span class="text-gray-700 font-bold">৳${item.out_value.toFixed(2)}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-400">ফেরত আসা দাম:</span>
                                        <span class="text-gray-700 font-bold">৳${item.in_value.toFixed(2)}</span>
                                    </div>
                                    <div>
                                        <span class="text-emerald-600">বিক্রির টাকা (Net):</span>
                                        <span class="text-emerald-700 font-bold">৳${item.net_sale.toFixed(2)}</span>
                                    </div>
                                    <div>
                                        <span class="text-blue-600">লাভ (Profit):</span>
                                        <span class="text-blue-700 font-bold">৳${item.profit.toFixed(2)}</span>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center text-[10px] bg-slate-50 p-2 rounded-lg border border-slate-100">
                                    <div>
                                        <span class="text-gray-400">মোট বিক্রি (Gross):</span>
                                        <span class="text-gray-800 font-bold">৳${item.total_sale.toFixed(2)}</span>
                                    </div>
                                    <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-bold">
                                        ডেলিভারি: ${item.success_ratio.toFixed(1)}%
                                    </span>
                                </div>
                            </div>
                        `;
                    });
                    document.getElementById('modal-table-body').innerHTML = html;
                    document.getElementById('modal-mobile-list').innerHTML = mobileHtml;
                }
            }
        });
}

function closeModal() {
    document.getElementById('billModal').classList.add('hidden');
}

// Close modal on escape key
document.addEventListener('keydown', function(event) {
    if (event.key === "Escape") closeModal();
});
</script>
