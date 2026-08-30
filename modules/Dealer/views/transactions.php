<?php $pageTitle = 'Transactions'; ?>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800">Recent Transactions</h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 border-b border-gray-200 text-gray-700 uppercase text-xs font-semibold">
                <tr>
                    <th class="px-6 py-4">Date</th>
                    <th class="px-6 py-4">Order ID</th>
                    <th class="px-6 py-4">Type</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Handled By (SR)</th>
                    <th class="px-6 py-4">Retailer</th>
                    <th class="px-6 py-4 text-right">Amount (৳)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(empty($transactions)): ?>
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-receipt text-3xl mb-3 block text-gray-300"></i>
                        No recent transactions found.
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach($transactions as $txn): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?= date('d M Y, h:i A', strtotime($txn['created_at'])) ?>
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-800">#ORD-<?= str_pad($txn['id'], 5, '0', STR_PAD_LEFT) ?></td>
                        <td class="px-6 py-4">
                            <?php if($txn['is_ready_sale']): ?>
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Ready Sale</span>
                            <?php else: ?>
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Regular Order</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php 
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'confirmed' => 'bg-blue-100 text-blue-800',
                                    'dispatched' => 'bg-indigo-100 text-indigo-800',
                                    'delivered' => 'bg-emerald-100 text-emerald-800',
                                    'cancelled' => 'bg-red-100 text-red-800'
                                ];
                                $color = $statusColors[$txn['status']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium <?= $color ?>">
                                <?= ucfirst($txn['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4"><?= htmlspecialchars($txn['sr_name'] ?? 'N/A') ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($txn['retailer_name'] ?? 'N/A') ?></td>
                        <td class="px-6 py-4 text-right font-bold text-gray-800">৳<?= number_format($txn['total_amount'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
