<?php $pageTitle = 'Inventory'; ?>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800">Products Stock</h3>
        <div class="text-sm text-gray-500">
            Total Items: <span class="font-semibold text-gray-700"><?= count($inventory) ?></span>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 border-b border-gray-200 text-gray-700 uppercase text-xs font-semibold">
                <tr>
                    <th class="px-6 py-4">Product Name</th>
                    <th class="px-6 py-4">SKU</th>
                    <th class="px-6 py-4">Company</th>
                    <th class="px-6 py-4">Category</th>
                    <th class="px-6 py-4 text-right">Price</th>
                    <th class="px-6 py-4 text-right">Stock (Boxes)</th>
                    <th class="px-6 py-4 text-right">Value (৳)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(empty($inventory)): ?>
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-box-open text-3xl mb-3 block text-gray-300"></i>
                        No inventory found for your linked companies.
                    </td>
                </tr>
                <?php else: ?>
                    <?php 
                    $totalValue = 0;
                    foreach($inventory as $item): 
                        $qty = (int)($item['stock_qty'] ?? $item['qty_boxes'] ?? 0);
                        $value = $qty * (float)$item['price'];
                        $totalValue += $value;
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-800"><?= htmlspecialchars($item['name']) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($item['sku'] ?? '-') ?></td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <?= htmlspecialchars($item['company_name'] ?? 'N/A') ?>
                            </span>
                        </td>
                        <td class="px-6 py-4"><?= htmlspecialchars($item['category_name'] ?? 'N/A') ?></td>
                        <td class="px-6 py-4 text-right font-medium">৳<?= number_format($item['price'], 2) ?></td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-bold <?= $qty > 10 ? 'text-emerald-600' : ($qty > 0 ? 'text-yellow-600' : 'text-red-500') ?>">
                                <?= number_format($qty) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-gray-800 font-semibold">৳<?= number_format($value, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <?php if(!empty($inventory)): ?>
            <tfoot class="bg-gray-50 border-t border-gray-200 font-bold">
                <tr>
                    <td colspan="6" class="px-6 py-4 text-right text-gray-700">Total Value:</td>
                    <td class="px-6 py-4 text-right text-emerald-600 text-lg">৳<?= number_format($totalValue, 2) ?></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
