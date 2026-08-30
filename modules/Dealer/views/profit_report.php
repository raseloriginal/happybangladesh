<?php $pageTitle = 'Profit Report'; ?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <div class="bg-gradient-to-br from-emerald-600 to-emerald-800 rounded-xl shadow-lg p-6 text-white relative overflow-hidden">
        <div class="relative z-10">
            <p class="text-emerald-100 font-medium mb-1">Your Commission Rate</p>
            <p class="text-4xl font-bold"><?= number_format($commissionRate, 2) ?>%</p>
            <p class="text-sm text-emerald-200 mt-2">Applied to all successfully delivered orders.</p>
        </div>
        <i class="fas fa-percent absolute -right-4 -bottom-4 text-emerald-500 opacity-20 text-8xl"></i>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800">Daily Earnings Overview</h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 border-b border-gray-200 text-gray-700 uppercase text-xs font-semibold">
                <tr>
                    <th class="px-6 py-4">Date</th>
                    <th class="px-6 py-4 text-center">Total Delivered Orders</th>
                    <th class="px-6 py-4 text-right">Total Sales Value (৳)</th>
                    <th class="px-6 py-4 text-right">Estimated Commission (৳)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(empty($reports)): ?>
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-chart-bar text-3xl mb-3 block text-gray-300"></i>
                        No delivered orders found yet.
                    </td>
                </tr>
                <?php else: ?>
                    <?php 
                    $totalSalesOverall = 0;
                    $totalCommissionOverall = 0;
                    foreach($reports as $row): 
                        $sales = $row['total_sales'];
                        $commission = ($sales * $commissionRate) / 100;
                        $totalSalesOverall += $sales;
                        $totalCommissionOverall += $commission;
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-800"><?= date('d M Y', strtotime($row['date'])) ?></td>
                        <td class="px-6 py-4 text-center"><?= number_format($row['total_orders']) ?></td>
                        <td class="px-6 py-4 text-right font-medium text-gray-700">৳<?= number_format($sales, 2) ?></td>
                        <td class="px-6 py-4 text-right font-bold text-emerald-600">৳<?= number_format($commission, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <?php if(!empty($reports)): ?>
            <tfoot class="bg-emerald-50 border-t border-emerald-100 font-bold">
                <tr>
                    <td colspan="2" class="px-6 py-4 text-right text-emerald-800 uppercase text-xs tracking-wider">Overall Total:</td>
                    <td class="px-6 py-4 text-right text-gray-800">৳<?= number_format($totalSalesOverall, 2) ?></td>
                    <td class="px-6 py-4 text-right text-emerald-700 text-lg">৳<?= number_format($totalCommissionOverall, 2) ?></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
