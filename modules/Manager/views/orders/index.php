<?php $pageTitle = 'Orders Summary'; ?>

<div class="page-header">
    <div><h1 class="page-title">Orders Summary</h1><div class="breadcrumb">Manager &rsaquo; Orders</div></div>
</div>

<div class="card mb-6">
    <div class="card-header">
        <h2 class="card-title">Filters</h2>
    </div>
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/manager/orders" class="flex gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">From Date</label>
                <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom ?? '') ?>" class="form-input text-sm w-40">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">To Date</label>
                <input type="date" name="date_to" value="<?= htmlspecialchars($dateTo ?? '') ?>" class="form-input text-sm w-40">
            </div>
            <div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter mr-2"></i>Filter</button>
                <a href="<?= BASE_URL ?>/manager/orders" class="btn btn-secondary ml-2">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Orders by Date</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table whitespace-nowrap w-full text-sm">
            <thead class="bg-slate-100 text-slate-600 font-semibold border-b border-slate-200">
                <tr>
                    <th class="py-3 px-4 text-left">Order Date</th>
                    <th class="py-3 px-4 text-right">Total Order Base Value</th>
                    <th class="py-3 px-4 text-right">Total SR Written Value</th>
                </tr>
            </thead>
            <tbody id="orders-tbody" class="divide-y divide-slate-200">
                <?php if (empty($orderDates)): ?>
                    <tr>
                        <td colspan="3" class="text-center py-6 text-slate-400">No orders found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orderDates as $od): ?>
                        <tr class="hover:bg-slate-50 cursor-pointer transition-colors" onclick="toggleDate('<?= $od['order_date'] ?>')">
                            <td class="py-3 px-4 font-bold text-blue-700">
                                <i id="icon-date-<?= $od['order_date'] ?>" class="fas fa-chevron-right mr-2 transition-transform duration-200 text-slate-400"></i>
                                <?= date('d M Y', strtotime($od['order_date'])) ?>
                            </td>
                            <td class="py-3 px-4 text-right font-mono">৳<?= number_format($od['total_base_value'], 2) ?></td>
                            <td class="py-3 px-4 text-right font-mono">৳<?= number_format($od['total_sr_value'], 2) ?></td>
                        </tr>
                        <tr id="child-date-<?= $od['order_date'] ?>" class="hidden bg-slate-50 border-b border-slate-200">
                            <td colspan="3" class="p-0">
                                <div class="pl-8 pr-4 py-4 border-l-4 border-blue-500" id="container-date-<?= $od['order_date'] ?>">
                                    <!-- Companies load here -->
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if ($totalPages > 1): ?>
        <div class="p-4 border-t border-slate-200 flex justify-center">
            <nav class="flex items-center gap-1">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?>&date_from=<?= urlencode($dateFrom) ?>&date_to=<?= urlencode($dateTo) ?>" 
                       class="px-3 py-1 rounded text-sm <?= $i === $page ? 'bg-blue-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>

<script>
async function toggleDate(dateStr) {
    const icon = document.getElementById('icon-date-' + dateStr);
    const childRow = document.getElementById('child-date-' + dateStr);
    const container = document.getElementById('container-date-' + dateStr);

    if (childRow.classList.contains('hidden')) {
        childRow.classList.remove('hidden');
        icon.classList.add('rotate-90');

        if (container.innerHTML.trim() === '<!-- Companies load here -->') {
            container.innerHTML = '<div class="text-slate-500 py-2"><i class="fas fa-spinner fa-spin mr-2"></i>Loading companies...</div>';
            try {
                const res = await fetch(`<?= BASE_URL ?>/manager/api/orders/companies?date=${dateStr}`);
                const data = await res.json();
                
                if (data.length === 0) {
                    container.innerHTML = '<div class="text-slate-400 py-2">No data found.</div>';
                    return;
                }

                let html = `
                    <table class="w-full text-sm bg-white shadow-sm rounded-lg overflow-hidden border border-slate-200 mb-2">
                        <thead class="bg-indigo-50 text-indigo-800">
                            <tr>
                                <th class="py-2 px-3 text-left font-semibold">Company</th>
                                <th class="py-2 px-3 text-right font-semibold">Total Base Value</th>
                                <th class="py-2 px-3 text-right font-semibold">Total SR Written Value</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                `;

                data.forEach(comp => {
                    const cId = comp.company_id || '0';
                    const uid = `comp-${dateStr}-${cId}`;
                    const cName = comp.company_name || 'Unknown Company';
                    html += `
                        <tr class="hover:bg-indigo-50/50 cursor-pointer transition-colors" onclick="toggleCompany('${dateStr}', '${cId}')">
                            <td class="py-2 px-3 font-medium text-indigo-700">
                                <i id="icon-${uid}" class="fas fa-chevron-right mr-2 transition-transform duration-200 text-indigo-300"></i>
                                ${cName}
                            </td>
                            <td class="py-2 px-3 text-right font-mono">৳${parseFloat(comp.total_base_value).toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                            <td class="py-2 px-3 text-right font-mono">৳${parseFloat(comp.total_sr_value).toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                        </tr>
                        <tr id="child-${uid}" class="hidden bg-slate-50/50">
                            <td colspan="3" class="p-0">
                                <div class="pl-8 pr-4 py-3 border-l-4 border-indigo-400" id="container-${uid}">
                                    <!-- SRs load here -->
                                </div>
                            </td>
                        </tr>
                    `;
                });

                html += `</tbody></table>`;
                container.innerHTML = html;
            } catch (e) {
                container.innerHTML = '<div class="text-red-500 py-2"><i class="fas fa-exclamation-triangle mr-1"></i>Error loading companies.</div>';
            }
        }
    } else {
        childRow.classList.add('hidden');
        icon.classList.remove('rotate-90');
    }
}

async function toggleCompany(dateStr, companyId) {
    const uid = `comp-${dateStr}-${companyId}`;
    const icon = document.getElementById('icon-' + uid);
    const childRow = document.getElementById('child-' + uid);
    const container = document.getElementById('container-' + uid);

    if (childRow.classList.contains('hidden')) {
        childRow.classList.remove('hidden');
        icon.classList.add('rotate-90');

        if (container.innerHTML.trim() === '<!-- SRs load here -->') {
            container.innerHTML = '<div class="text-slate-500 py-2"><i class="fas fa-spinner fa-spin mr-2"></i>Loading SRs...</div>';
            try {
                const res = await fetch(`<?= BASE_URL ?>/manager/api/orders/srs?date=${dateStr}&company_id=${companyId}`);
                const data = await res.json();
                
                if (data.length === 0) {
                    container.innerHTML = '<div class="text-slate-400 py-2">No SR data found.</div>';
                    return;
                }

                let html = `
                    <table class="w-full text-sm bg-white shadow-sm rounded-lg overflow-hidden border border-slate-200 mb-2">
                        <thead class="bg-teal-50 text-teal-800">
                            <tr>
                                <th class="py-2 px-3 text-left font-semibold">SR Name</th>
                                <th class="py-2 px-3 text-right font-semibold">Total Value (Base)</th>
                                <th class="py-2 px-3 text-right font-semibold">SR Sales Value</th>
                                <th class="py-2 px-3 text-right font-semibold">Total O/C</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                `;

                data.forEach(sr => {
                    const sId = sr.sr_id;
                    const suid = `sr-${dateStr}-${companyId}-${sId}`;
                    const oc = parseFloat(sr.total_oc);
                    const ocClass = oc > 0 ? 'text-emerald-600' : (oc < 0 ? 'text-rose-600' : 'text-slate-500');
                    const ocSign = oc > 0 ? '+' : (oc < 0 ? '-' : '');
                    
                    html += `
                        <tr class="hover:bg-teal-50/50 cursor-pointer transition-colors" onclick="toggleSr('${dateStr}', '${companyId}', '${sId}')">
                            <td class="py-2 px-3 font-medium text-teal-700">
                                <i id="icon-${suid}" class="fas fa-chevron-right mr-2 transition-transform duration-200 text-teal-300"></i>
                                ${sr.sr_name}
                            </td>
                            <td class="py-2 px-3 text-right font-mono">৳${parseFloat(sr.total_base_value).toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                            <td class="py-2 px-3 text-right font-mono">৳${parseFloat(sr.total_sr_value).toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                            <td class="py-2 px-3 text-right font-mono font-bold ${ocClass}">${ocSign}৳${Math.abs(oc).toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                        </tr>
                        <tr id="child-${suid}" class="hidden bg-slate-50/50">
                            <td colspan="4" class="p-0">
                                <div class="pl-8 pr-4 py-3 border-l-4 border-teal-400" id="container-${suid}">
                                    <!-- Products load here -->
                                </div>
                            </td>
                        </tr>
                    `;
                });

                html += `</tbody></table>`;
                container.innerHTML = html;
            } catch (e) {
                container.innerHTML = '<div class="text-red-500 py-2"><i class="fas fa-exclamation-triangle mr-1"></i>Error loading SRs.</div>';
            }
        }
    } else {
        childRow.classList.add('hidden');
        icon.classList.remove('rotate-90');
    }
}

async function toggleSr(dateStr, companyId, srId) {
    const suid = `sr-${dateStr}-${companyId}-${srId}`;
    const icon = document.getElementById('icon-' + suid);
    const childRow = document.getElementById('child-' + suid);
    const container = document.getElementById('container-' + suid);

    if (childRow.classList.contains('hidden')) {
        childRow.classList.remove('hidden');
        icon.classList.add('rotate-90');

        if (container.innerHTML.trim() === '<!-- Products load here -->') {
            container.innerHTML = '<div class="text-slate-500 py-2"><i class="fas fa-spinner fa-spin mr-2"></i>Loading Products...</div>';
            try {
                const res = await fetch(`<?= BASE_URL ?>/manager/api/orders/products?date=${dateStr}&company_id=${companyId}&sr_id=${srId}`);
                const data = await res.json();
                
                if (data.length === 0) {
                    container.innerHTML = '<div class="text-slate-400 py-2">No Products found.</div>';
                    return;
                }

                let html = `
                    <table class="w-full text-xs bg-white shadow-sm rounded-lg overflow-hidden border border-slate-200">
                        <thead class="bg-amber-50 text-amber-900">
                            <tr>
                                <th class="py-2 px-3 text-left font-semibold">Product</th>
                                <th class="py-2 px-3 text-center font-semibold">Total Order Qty</th>
                                <th class="py-2 px-3 text-right font-semibold">Total Base Value</th>
                                <th class="py-2 px-3 text-right font-semibold">Total SR Sale Value</th>
                                <th class="py-2 px-3 text-right font-semibold">Total O/C</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                `;

                data.forEach(prod => {
                    const ppb = parseInt(prod.pieces_per_box) || 1;
                    
                    const orderQty = parseInt(prod.total_qty) || 0;

                    const boxType = (prod.box_type || '').trim();
                    const boxTypeLower = boxType.toLowerCase();
                    
                    let orderQtyDisplay = '';
                    if (boxTypeLower === 'pcs') {
                        orderQtyDisplay = `${orderQty} পিস`;
                    } else if (boxType === 'পিস' || boxType === 'পলি' || boxType === 'জার') {
                        orderQtyDisplay = `${orderQty} ${boxType}`;
                    } else {
                        const boxLabel = boxType ? boxType : 'Box';
                        const orderBoxes = Math.floor(orderQty / ppb);
                        const orderPieces = orderQty % ppb;
                        orderQtyDisplay = `${orderBoxes} ${boxLabel} - ${orderPieces} পিস`;
                    }

                    const oc = parseFloat(prod.total_base_value) - parseFloat(prod.total_sr_value);
                    const ocClass = oc > 0 ? 'text-emerald-600' : (oc < 0 ? 'text-rose-600' : 'text-slate-500');
                    const ocSign = oc > 0 ? '+' : (oc < 0 ? '-' : '');

                    html += `
                        <tr class="hover:bg-amber-50/30 transition-colors">
                            <td class="py-2 px-3 font-medium text-amber-800">${prod.product_name} (${parseFloat(prod.total_sr_value).toLocaleString('en-IN', {minimumFractionDigits: 2})} tk)</td>
                            <td class="py-2 px-3 text-center font-mono">${orderQtyDisplay}</td>
                            <td class="py-2 px-3 text-right font-mono">৳${parseFloat(prod.total_base_value).toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                            <td class="py-2 px-3 text-right font-mono">৳${parseFloat(prod.total_sr_value).toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                            <td class="py-2 px-3 text-right font-mono font-bold ${ocClass}">
                                ${ocSign}৳${Math.abs(oc).toLocaleString('en-IN', {minimumFractionDigits: 2})}
                            </td>
                        </tr>
                    `;
                });

                html += `</tbody></table>`;
                container.innerHTML = html;
            } catch (e) {
                container.innerHTML = '<div class="text-red-500 py-2"><i class="fas fa-exclamation-triangle mr-1"></i>Error loading Products.</div>';
            }
        }
    } else {
        childRow.classList.add('hidden');
        icon.classList.remove('rotate-90');
    }
}
</script>
