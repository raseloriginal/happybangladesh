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

<!-- ========================================== -->
<!-- PRODUCT RETAILERS MODAL                    -->
<!-- ========================================== -->
<div id="productRetailersModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-3 sm:p-4 transition-all" onclick="if(event.target===this) closeProductRetailersModal()">
    <div class="bg-white rounded-2xl w-full max-w-4xl shadow-2xl overflow-hidden flex flex-col max-h-[92vh] border border-slate-200">
        <!-- Modal Header -->
        <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center bg-gradient-to-r from-blue-50 to-indigo-50/50">
            <div class="min-w-0 pr-3">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    <span id="prm-date-badge" class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-800">
                        <i class="fa-regular fa-calendar text-[10px]"></i> <span id="prm-date-text"></span>
                    </span>
                    <span id="prm-sr-badge" class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800">
                        <i class="fa-solid fa-user-tie text-[10px]"></i> <span id="prm-sr-text"></span>
                    </span>
                    <span id="prm-comp-badge" class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full bg-slate-200/80 text-slate-700">
                        <i class="fa-solid fa-building text-[10px]"></i> <span id="prm-comp-text"></span>
                    </span>
                </div>
                <h3 id="prm-product-name" class="font-bold text-slate-900 text-base sm:text-lg truncate flex items-center gap-2">
                    <i class="fa-solid fa-box-open text-blue-600"></i> <span>Product Name</span>
                </h3>
            </div>
            <button type="button" onclick="closeProductRetailersModal()" class="w-8 h-8 rounded-xl bg-white hover:bg-slate-100 text-slate-400 hover:text-slate-700 transition flex items-center justify-center border border-slate-200 shadow-2xs">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- KPI Summary Bar & Search Filter -->
        <div class="px-5 py-3 bg-slate-50 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-4 text-xs flex-wrap">
                <div>
                    <span class="text-slate-500 font-medium">Retailers:</span>
                    <span id="prm-kpi-retailers" class="font-bold text-slate-800 font-mono ml-1">0</span>
                </div>
                <div class="border-l border-slate-300 pl-4">
                    <span class="text-slate-500 font-medium">Total Qty:</span>
                    <span id="prm-kpi-qty" class="font-bold text-blue-700 font-mono ml-1">0</span>
                </div>
                <div class="border-l border-slate-300 pl-4">
                    <span class="text-slate-500 font-medium">SR Sales Value:</span>
                    <span id="prm-kpi-sr-val" class="font-bold text-emerald-700 font-mono ml-1">৳0.00</span>
                </div>
                <div class="border-l border-slate-300 pl-4">
                    <span class="text-slate-500 font-medium">Total O/C:</span>
                    <span id="prm-kpi-oc" class="font-bold font-mono ml-1">৳0.00</span>
                </div>
            </div>

            <!-- Instant Search Input -->
            <div class="relative w-full sm:w-64">
                <i class="fa-solid fa-search absolute left-3 top-2.5 text-xs text-slate-400 pointer-events-none"></i>
                <input type="text" id="prm-search-input" oninput="filterProductRetailers()" 
                       placeholder="Filter retailer or phone..." 
                       class="w-full pl-8 pr-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
            </div>
        </div>

        <!-- Modal Body (Retailers List & Accordions) -->
        <div id="prm-body" class="p-4 sm:p-5 overflow-y-auto space-y-3 flex-1">
            <!-- Dynamic Retailers Rendered Here -->
        </div>

        <!-- Modal Footer -->
        <div class="px-5 py-3 border-t border-slate-200 bg-slate-50/70 flex items-center justify-between text-xs text-slate-500">
            <div>
                <i class="fa-solid fa-circle-info text-blue-500 mr-1"></i>
                Click any retailer to view their complete orders from all companies on this date.
            </div>
            <button type="button" onclick="closeProductRetailersModal()" class="btn btn-secondary text-xs px-4 py-1.5">
                Close
            </button>
        </div>
    </div>
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
                                <th class="py-2 px-3 text-center font-semibold">Stock</th>
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
                    const stockPcs = parseInt(prod.stock_pieces) || 0;

                    const boxType = (prod.box_type || '').trim();
                    const boxTypeLower = boxType.toLowerCase();
                    
                    let orderQtyDisplay = '';
                    let stockQtyDisplay = '';

                    if (boxTypeLower === 'pcs') {
                        orderQtyDisplay = `${orderQty} পিস`;
                        stockQtyDisplay = `${stockPcs} পিস`;
                    } else if (boxType === 'পিস' || boxType === 'পলি' || boxType === 'জার') {
                        orderQtyDisplay = `${orderQty} ${boxType}`;
                        stockQtyDisplay = `${stockPcs} ${boxType}`;
                    } else {
                        const boxLabel = boxType ? boxType : 'Box';
                        const orderBoxes = Math.floor(orderQty / ppb);
                        const orderPieces = orderQty % ppb;
                        orderQtyDisplay = `${orderBoxes} ${boxLabel} - ${orderPieces} পিস`;

                        const stockBoxes = Math.floor(stockPcs / ppb);
                        const stockPieces = stockPcs % ppb;
                        stockQtyDisplay = `${stockBoxes} ${boxLabel} - ${stockPieces} পিস`;
                    }

                    const isShort = stockPcs < orderQty;
                    const stockColorClass = isShort ? 'text-red-600 font-bold bg-red-50' : 'text-slate-600';

                    const oc = parseFloat(prod.total_sr_value) - parseFloat(prod.total_base_value);
                    const ocClass = oc > 0 ? 'text-emerald-600' : (oc < 0 ? 'text-rose-600' : 'text-slate-500');
                    const ocSign = oc > 0 ? '+' : (oc < 0 ? '-' : '');
                    const unitPrice = parseFloat(prod.total_base_value) / Math.max(1, parseFloat(prod.total_qty));

                    const safeProdName = (prod.product_name || '').replace(/'/g, "\\'");

                    html += `
                        <tr class="hover:bg-amber-50/30 transition-colors">
                            <td class="py-2 px-3 font-medium">
                                <button type="button" 
                                        onclick="event.stopPropagation(); openProductRetailers('${dateStr}', '${prod.product_id}', '${safeProdName}', '${companyId}', '${srId}')" 
                                        class="text-left font-semibold text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1.5 group cursor-pointer"
                                        title="Click to view retailers who ordered this product">
                                    <span class="group-hover:text-blue-700">${prod.product_name}</span>
                                    <span class="text-xs text-slate-500 font-normal">(${unitPrice.toLocaleString('en-IN', {minimumFractionDigits: 2})} tk)</span>
                                    <i class="fa-solid fa-users text-[11px] text-blue-400 group-hover:text-blue-600 ml-0.5 opacity-80 group-hover:opacity-100 transition"></i>
                                </button>
                            </td>
                            <td class="py-2 px-3 text-center font-mono ${stockColorClass}">${stockQtyDisplay}</td>
                            <td class="py-2 px-3 text-center font-mono">${orderQtyDisplay}</td>
                            <td class="py-2 px-3 text-right font-mono">৳${parseFloat(prod.total_base_value).toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                            <td class="py-2 px-3 text-right font-mono">৳${parseFloat(prod.total_sr_value).toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                            <td class="py-2 px-3 text-right font-mono font-bold ${ocClass}">
                                ${ocSign}৳${Math.abs(oc).toLocaleString('en-IN', {minimumFractionDigits: 2})}
                            </td>
                        </tr>
                    `;

                    if (prod.free_items && prod.free_items.length > 0) {
                        prod.free_items.forEach(fi => {
                            const fPpb = parseInt(fi.pieces_per_box) || 1;
                            const fOrderQty = parseInt(fi.free_qty) || 0;
                            const fStockPcs = parseInt(fi.stock_pieces) || 0;

                            const fBoxType = (fi.box_type || '').trim();
                            const fBoxTypeLower = fBoxType.toLowerCase();

                            let fOrderQtyDisplay = '';
                            let fStockQtyDisplay = '';

                            if (fBoxTypeLower === 'pcs') {
                                fOrderQtyDisplay = `${fOrderQty} পিস`;
                                fStockQtyDisplay = `${fStockPcs} পিস`;
                            } else if (fBoxType === 'পিস' || fBoxType === 'পলি' || fBoxType === 'জার') {
                                fOrderQtyDisplay = `${fOrderQty} ${fBoxType}`;
                                fStockQtyDisplay = `${fStockPcs} ${fBoxType}`;
                            } else {
                                const fBoxLabel = fBoxType ? fBoxType : 'Box';
                                const fOrderBoxes = Math.floor(fOrderQty / fPpb);
                                const fOrderPieces = fOrderQty % fPpb;
                                fOrderQtyDisplay = `${fOrderBoxes} ${fBoxLabel} - ${fOrderPieces} পিস`;

                                const fStockBoxes = Math.floor(fStockPcs / fPpb);
                                const fStockPieces = fStockPcs % fPpb;
                                fStockQtyDisplay = `${fStockBoxes} ${fBoxLabel} - ${fStockPieces} পিস`;
                            }

                            const fIsShort = fStockPcs < fOrderQty;
                            const fStockColorClass = fIsShort ? 'text-red-600 font-bold bg-red-50' : 'text-slate-600';

                            html += `
                                <tr class="bg-emerald-50/20 hover:bg-emerald-50/40 transition-colors border-l-2 border-emerald-400">
                                    <td class="py-1.5 px-3 pl-6 font-medium text-slate-700">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-emerald-600 font-black text-sm">↳</span>
                                            <span class="inline-flex items-center gap-1 bg-emerald-100 text-emerald-800 text-[10px] font-extrabold px-1.5 py-0.5 rounded border border-emerald-300 shadow-2xs">
                                                <i class="fa-solid fa-gift text-emerald-600"></i> FREE
                                            </span>
                                            <span class="font-bold text-slate-800">${fi.free_product_name}</span>
                                        </div>
                                    </td>
                                    <td class="py-1.5 px-3 text-center font-mono text-xs ${fStockColorClass}">${fStockQtyDisplay}</td>
                                    <td class="py-1.5 px-3 text-center font-mono font-bold text-emerald-700 text-xs">${fOrderQtyDisplay}</td>
                                    <td class="py-1.5 px-3 text-right font-mono text-slate-400 text-xs">—</td>
                                    <td class="py-1.5 px-3 text-right font-mono text-slate-400 text-xs">—</td>
                                    <td class="py-1.5 px-3 text-right font-mono text-slate-400 text-xs">—</td>
                                </tr>
                            `;
                        });
                    }
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

// ============================================================================
// PRODUCT RETAILERS & FULL DAY ORDERS DRILLDOWN LOGIC
// ============================================================================
let currentProductRetailers = [];
let currentRetailerDrilldownDate = '';
const retailerDayOrdersCache = {};

async function openProductRetailers(dateStr, productId, productName, companyId, srId) {
    currentRetailerDrilldownDate = dateStr;
    const modal = document.getElementById('productRetailersModal');
    const body = document.getElementById('prm-body');
    const searchInput = document.getElementById('prm-search-input');
    searchInput.value = '';

    // Set Header Info
    const formattedDate = new Date(dateStr).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    document.getElementById('prm-date-text').textContent = formattedDate;
    document.getElementById('prm-product-name').querySelector('span').textContent = productName;

    // Reset KPIs
    document.getElementById('prm-kpi-retailers').textContent = '...';
    document.getElementById('prm-kpi-qty').textContent = '...';
    document.getElementById('prm-kpi-sr-val').textContent = '...';
    document.getElementById('prm-kpi-oc').textContent = '...';

    // Show Loading
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    body.innerHTML = `
        <div class="py-12 text-center text-slate-500">
            <i class="fas fa-spinner fa-spin text-2xl text-blue-600 mb-2"></i>
            <div class="text-xs font-semibold">Loading retailers who ordered this product...</div>
        </div>
    `;

    try {
        let url = `<?= BASE_URL ?>/manager/api/orders/product-retailers?date=${encodeURIComponent(dateStr)}&product_id=${encodeURIComponent(productId)}`;
        if (srId) url += `&sr_id=${encodeURIComponent(srId)}`;
        if (companyId !== null && companyId !== undefined && companyId !== '') url += `&company_id=${encodeURIComponent(companyId)}`;

        const res = await fetch(url);
        const data = await res.json();
        currentProductRetailers = Array.isArray(data) ? data : [];

        // Set Badges
        const firstRow = currentProductRetailers[0] || {};
        document.getElementById('prm-sr-text').textContent = firstRow.sr_name || 'All SRs';
        document.getElementById('prm-comp-text').textContent = firstRow.company_name || 'All Companies';

        renderProductRetailers(currentProductRetailers);
    } catch (e) {
        body.innerHTML = `
            <div class="py-8 text-center text-red-500 text-xs">
                <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                <div>Failed to load retailers. Please try again.</div>
            </div>
        `;
    }
}

function renderProductRetailers(retailers) {
    const body = document.getElementById('prm-body');

    if (retailers.length === 0) {
        document.getElementById('prm-kpi-retailers').textContent = '0';
        document.getElementById('prm-kpi-qty').textContent = '0';
        document.getElementById('prm-kpi-sr-val').textContent = '৳0.00';
        document.getElementById('prm-kpi-oc').textContent = '৳0.00';
        body.innerHTML = `
            <div class="py-12 text-center text-slate-400">
                <i class="fa-solid fa-store-slash text-3xl mb-2 text-slate-300"></i>
                <div class="text-xs font-medium">No retailers found for this product on this date.</div>
            </div>
        `;
        return;
    }

    // Calculate Summary KPIs
    let totalQty = 0;
    let totalSrVal = 0;
    let totalOc = 0;
    retailers.forEach(r => {
        totalQty += parseInt(r.quantity) || 0;
        totalSrVal += parseFloat(r.total_price) || 0;
        totalOc += parseFloat(r.total_oc) || 0;
    });

    const firstP = retailers[0] || {};
    const ppb = parseInt(firstP.pieces_per_box) || 1;
    const boxType = (firstP.box_type || '').trim();
    const boxTypeLower = boxType.toLowerCase();
    let totalQtyDisplay = '';
    if (boxTypeLower === 'pcs' || boxType === 'পিস' || boxType === 'পলি' || boxType === 'জার') {
        totalQtyDisplay = `${totalQty} ${boxType || 'পিস'}`;
    } else {
        const boxLabel = boxType ? boxType : 'Box';
        totalQtyDisplay = `${Math.floor(totalQty / ppb)} ${boxLabel} - ${totalQty % ppb} পিস`;
    }

    const ocSign = totalOc > 0 ? '+' : (totalOc < 0 ? '-' : '');
    const ocClass = totalOc > 0 ? 'text-emerald-700' : (totalOc < 0 ? 'text-rose-600' : 'text-slate-600');

    document.getElementById('prm-kpi-retailers').textContent = retailers.length;
    document.getElementById('prm-kpi-qty').textContent = totalQtyDisplay;
    document.getElementById('prm-kpi-sr-val').textContent = '৳' + totalSrVal.toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('prm-kpi-oc').className = 'font-bold font-mono ml-1 ' + ocClass;
    document.getElementById('prm-kpi-oc').textContent = `${ocSign}৳${Math.abs(totalOc).toLocaleString('en-IN', {minimumFractionDigits: 2})}`;

    let html = '';
    retailers.forEach((r, idx) => {
        const uid = `prm-ret-${idx}`;
        const retId = r.retailer_id || '';
        const orderId = r.order_id || '';
        const safeRetName = (r.retailer_name || '').replace(/'/g, "\\'");
        const retOc = parseFloat(r.total_oc) || 0;
        const rOcSign = retOc > 0 ? '+' : (retOc < 0 ? '-' : '');
        const rOcClass = retOc > 0 ? 'text-emerald-600' : (retOc < 0 ? 'text-rose-600' : 'text-slate-500');

        let statusBadge = '';
        const st = (r.order_status || '').toLowerCase();
        if (st === 'delivered') statusBadge = '<span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Delivered</span>';
        else if (st === 'dispatched') statusBadge = '<span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">Dispatched</span>';
        else if (st === 'confirmed') statusBadge = '<span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Confirmed</span>';
        else if (st === 'cancelled') statusBadge = '<span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">Cancelled</span>';
        else statusBadge = '<span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">Pending</span>';

        let freePill = '';
        if (r.free_items && r.free_items.length > 0) {
            freePill = r.free_items.map(fi => `
                <div class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-800 text-[10px] font-bold px-1.5 py-0.5 rounded border border-emerald-200 mt-1">
                    <i class="fa-solid fa-gift text-emerald-600"></i> ${fi.qty_display} ${fi.free_product_name}
                </div>
            `).join(' ');
        }

        html += `
            <div class="retailer-card bg-white rounded-xl border border-slate-200 shadow-2xs hover:border-blue-300 transition overflow-hidden" 
                 data-name="${(r.retailer_name || '').toLowerCase()}" 
                 data-phone="${(r.retailer_phone || '').toLowerCase()}">
                <div class="p-3 sm:p-4 flex flex-col md:flex-row md:items-center justify-between gap-3 cursor-pointer select-none bg-white hover:bg-slate-50/50 transition"
                     onclick="toggleRetailerDayOrders('${uid}', '${currentRetailerDrilldownDate}', '${retId}', '${orderId}', '${safeRetName}')">
                    
                    <!-- Retailer Info -->
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 mt-0.5">
                            <i class="fa-solid fa-shop text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-slate-800 text-sm hover:text-blue-600 transition">${r.retailer_name}</span>
                                ${statusBadge}
                            </div>
                            <div class="text-xs text-slate-500 flex items-center gap-3 mt-1 flex-wrap">
                                ${r.retailer_phone ? `<span><i class="fa-solid fa-phone text-[10px] text-slate-400 mr-1"></i>${r.retailer_phone}</span>` : ''}
                                ${r.retailer_address ? `<span><i class="fa-solid fa-location-dot text-[10px] text-slate-400 mr-1"></i>${r.retailer_address}</span>` : ''}
                                <span><i class="fa-solid fa-user text-[10px] text-slate-400 mr-1"></i>SR: <strong class="text-slate-700">${r.sr_name}</strong></span>
                            </div>
                        </div>
                    </div>

                    <!-- Quantity & Value & Action -->
                    <div class="flex items-center gap-4 justify-between md:justify-end shrink-0 border-t md:border-t-0 pt-2 md:pt-0 border-slate-100">
                        <div class="text-left md:text-right">
                            <div class="text-xs font-bold text-blue-700 font-mono">${r.qty_display}</div>
                            <div class="text-[11px] text-slate-500 font-mono">
                                ৳${parseFloat(r.total_price).toLocaleString('en-IN', {minimumFractionDigits: 2})} 
                                <span class="font-bold ${rOcClass}">(${rOcSign}৳${Math.abs(retOc).toLocaleString('en-IN', {minimumFractionDigits: 2})})</span>
                            </div>
                            ${freePill}
                        </div>
                        <button type="button" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-indigo-50 text-slate-700 hover:text-indigo-700 border border-slate-200 text-xs font-semibold flex items-center gap-1.5 transition">
                            <span class="hidden sm:inline">All Companies</span>
                            <i id="ret-chev-${uid}" class="fa-solid fa-chevron-down text-slate-400 transition-transform duration-200 text-xs"></i>
                        </button>
                    </div>
                </div>

                <!-- Accordion Container for Full Multi-Company Day Orders -->
                <div id="ret-accordion-${uid}" class="hidden border-t border-slate-200 bg-slate-50/70 p-3 sm:p-4">
                    <!-- Loaded dynamically on click -->
                </div>
            </div>
        `;
    });

    body.innerHTML = html;
}

function filterProductRetailers() {
    const q = (document.getElementById('prm-search-input').value || '').trim().toLowerCase();
    const cards = document.querySelectorAll('#prm-body .retailer-card');
    cards.forEach(card => {
        const name = card.getAttribute('data-name') || '';
        const phone = card.getAttribute('data-phone') || '';
        if (!q || name.includes(q) || phone.includes(q)) {
            card.classList.remove('hidden');
        } else {
            card.classList.add('hidden');
        }
    });
}

async function toggleRetailerDayOrders(uid, dateStr, retailerId, orderId, retailerName) {
    const accordion = document.getElementById('ret-accordion-' + uid);
    const chevron = document.getElementById('ret-chev-' + uid);

    if (!accordion.classList.contains('hidden')) {
        accordion.classList.add('hidden');
        chevron.classList.remove('rotate-180');
        return;
    }

    accordion.classList.remove('hidden');
    chevron.classList.add('rotate-180');

    const cacheKey = `${dateStr}_${retailerId || ''}_${orderId || ''}_${retailerName || ''}`;

    if (retailerDayOrdersCache[cacheKey]) {
        renderRetailerDayOrdersContent(accordion, retailerDayOrdersCache[cacheKey]);
        return;
    }

    accordion.innerHTML = `
        <div class="py-4 text-center text-slate-500 text-xs">
            <i class="fas fa-spinner fa-spin mr-2 text-indigo-600"></i>
            Loading all company orders for this date...
        </div>
    `;

    try {
        let url = `<?= BASE_URL ?>/manager/api/orders/retailer-day-details?date=${encodeURIComponent(dateStr)}`;
        if (retailerId) url += `&retailer_id=${encodeURIComponent(retailerId)}`;
        if (orderId) url += `&order_id=${encodeURIComponent(orderId)}`;
        if (retailerName) url += `&retailer_name=${encodeURIComponent(retailerName)}`;

        const res = await fetch(url);
        const data = await res.json();
        retailerDayOrdersCache[cacheKey] = data;
        renderRetailerDayOrdersContent(accordion, data);
    } catch (e) {
        accordion.innerHTML = `
            <div class="py-3 text-center text-red-500 text-xs">
                <i class="fas fa-exclamation-triangle mr-1"></i> Failed to load full day orders.
            </div>
        `;
    }
}

function renderRetailerDayOrdersContent(container, data) {
    if (!data || !data.companies || data.companies.length === 0) {
        container.innerHTML = `
            <div class="py-3 text-center text-slate-400 text-xs">
                No other orders found for this retailer on this date.
            </div>
        `;
        return;
    }

    const grandOc = parseFloat(data.grand_total.total_oc) || 0;
    const grandOcSign = grandOc > 0 ? '+' : (grandOc < 0 ? '-' : '');
    const grandOcClass = grandOc > 0 ? 'text-emerald-700' : (grandOc < 0 ? 'text-rose-600' : 'text-slate-600');

    let html = `
        <div class="bg-indigo-50/70 rounded-xl p-3 border border-indigo-100 mb-3 flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs shadow-2xs">
                    <i class="fa-solid fa-layer-group"></i>
                </span>
                <div>
                    <div class="text-xs font-bold text-indigo-950">Complete Day Orders (${data.date})</div>
                    <div class="text-[11px] text-indigo-700">All products ordered by <strong>${data.retailer.name}</strong> across all companies</div>
                </div>
            </div>
            <div class="flex items-center gap-3 text-xs font-mono">
                <span class="text-slate-600">Day Total: <strong class="text-indigo-900 font-bold">৳${parseFloat(data.grand_total.total_sr_value).toLocaleString('en-IN', {minimumFractionDigits: 2})}</strong></span>
                <span class="font-bold ${grandOcClass}">(${grandOcSign}৳${Math.abs(grandOc).toLocaleString('en-IN', {minimumFractionDigits: 2})})</span>
            </div>
        </div>
    `;

    data.companies.forEach(comp => {
        const compOc = parseFloat(comp.total_oc) || 0;
        const compOcSign = compOc > 0 ? '+' : (compOc < 0 ? '-' : '');
        const compOcClass = compOc > 0 ? 'text-emerald-600' : (compOc < 0 ? 'text-rose-600' : 'text-slate-500');

        let statusBadge = '';
        const st = (comp.order_status || '').toLowerCase();
        if (st === 'delivered') statusBadge = '<span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Delivered</span>';
        else if (st === 'dispatched') statusBadge = '<span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">Dispatched</span>';
        else if (st === 'confirmed') statusBadge = '<span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Confirmed</span>';
        else if (st === 'cancelled') statusBadge = '<span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">Cancelled</span>';
        else statusBadge = '<span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">Pending</span>';

        html += `
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden mb-3 shadow-2xs">
                <div class="px-3.5 py-2.5 bg-slate-100/90 border-b border-slate-200 flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
                            <i class="fa-solid fa-building text-indigo-600 text-xs"></i>
                            ${comp.company_name}
                        </span>
                        <span class="text-[11px] text-slate-600 bg-white px-2 py-0.5 rounded border border-slate-200 font-medium">
                            <i class="fa-solid fa-user-tie text-[10px] text-slate-400 mr-1"></i>SR: <strong>${comp.sr_name}</strong>
                        </span>
                        ${statusBadge}
                    </div>
                    <div class="text-xs font-mono text-slate-700">
                        Subtotal: <strong class="text-slate-900">৳${parseFloat(comp.total_sr_value).toLocaleString('en-IN', {minimumFractionDigits: 2})}</strong>
                    </div>
                </div>

                <table class="w-full text-xs">
                    <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 text-[11px] font-semibold">
                        <tr>
                            <th class="py-2 px-3 text-left">Product</th>
                            <th class="py-2 px-3 text-center">Order Qty</th>
                            <th class="py-2 px-3 text-right">SR Rate</th>
                            <th class="py-2 px-3 text-right">Total SR Price</th>
                            <th class="py-2 px-3 text-right">O/C</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
        `;

        comp.items.forEach(it => {
            const itOc = parseFloat(it.total_oc) || 0;
            const itOcSign = itOc > 0 ? '+' : (itOc < 0 ? '-' : '');
            const itOcClass = itOc > 0 ? 'text-emerald-600' : (itOc < 0 ? 'text-rose-600' : 'text-slate-500');

            html += `
                <tr class="hover:bg-slate-50/50 transition">
                    <td class="py-2 px-3 font-medium text-slate-800">
                        ${it.product_name}
                    </td>
                    <td class="py-2 px-3 text-center font-mono">${it.qty_display}</td>
                    <td class="py-2 px-3 text-right font-mono">৳${parseFloat(it.unit_price).toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                    <td class="py-2 px-3 text-right font-mono font-semibold text-slate-900">৳${parseFloat(it.total_price).toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                    <td class="py-2 px-3 text-right font-mono font-bold ${itOcClass}">${itOcSign}৳${Math.abs(itOc).toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                </tr>
            `;

            if (it.free_items && it.free_items.length > 0) {
                it.free_items.forEach(fi => {
                    html += `
                        <tr class="bg-emerald-50/20 border-l-2 border-emerald-400">
                            <td class="py-1 px-3 pl-6 font-medium text-slate-700">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-emerald-600 font-bold text-xs">↳</span>
                                    <span class="inline-flex items-center gap-1 bg-emerald-100 text-emerald-800 text-[9px] font-extrabold px-1.5 py-0.2 rounded border border-emerald-300">
                                        <i class="fa-solid fa-gift text-emerald-600"></i> FREE
                                    </span>
                                    <span class="font-semibold text-slate-800">${fi.free_product_name}</span>
                                </div>
                            </td>
                            <td class="py-1 px-3 text-center font-mono font-bold text-emerald-700 text-xs">${fi.qty_display}</td>
                            <td class="py-1 px-3 text-right font-mono text-slate-400 text-xs">—</td>
                            <td class="py-1 px-3 text-right font-mono text-slate-400 text-xs">—</td>
                            <td class="py-1 px-3 text-right font-mono text-slate-400 text-xs">—</td>
                        </tr>
                    `;
                });
            }
        });

        html += `
                    </tbody>
                    <tfoot class="bg-slate-50 font-mono text-xs border-t border-slate-200">
                        <tr>
                            <td colspan="3" class="py-2 px-3 text-right font-bold text-slate-600">Company Subtotal:</td>
                            <td class="py-2 px-3 text-right font-bold text-slate-900">৳${parseFloat(comp.total_sr_value).toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                            <td class="py-2 px-3 text-right font-bold ${compOcClass}">${compOcSign}৳${Math.abs(compOc).toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        `;
    });

    html += `
        <div class="bg-white rounded-xl border border-slate-200 p-3 flex items-center justify-between flex-wrap gap-2 text-xs">
            <span class="font-bold text-slate-700">Retailer Day Summary (Across All Companies):</span>
            <div class="flex items-center gap-4 font-mono">
                <span>Base Value: <strong class="text-slate-800">৳${parseFloat(data.grand_total.total_base_value).toLocaleString('en-IN', {minimumFractionDigits: 2})}</strong></span>
                <span>SR Sales Value: <strong class="text-emerald-700">৳${parseFloat(data.grand_total.total_sr_value).toLocaleString('en-IN', {minimumFractionDigits: 2})}</strong></span>
                <span>Total O/C: <strong class="${grandOcClass}">${grandOcSign}৳${Math.abs(grandOc).toLocaleString('en-IN', {minimumFractionDigits: 2})}</strong></span>
            </div>
        </div>
    `;

    container.innerHTML = html;
}

function closeProductRetailersModal() {
    const modal = document.getElementById('productRetailersModal');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
}

window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeProductRetailersModal();
    }
});
</script>
