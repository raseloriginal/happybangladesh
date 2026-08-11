<?php $pageTitle = 'Operations Dashboard'; ?>

<div class="p-4 sm:ml-64 bg-slate-50 min-h-screen">
    <!-- Premium Header -->
    <div class="rounded-2xl shadow-lg mb-6 overflow-hidden relative text-white" style="background: #0b1b3d !important;">
        <div class="absolute inset-0 bg-black/10 backdrop-blur-sm"></div>
        <div class="relative p-8 flex flex-col md:flex-row items-center justify-between z-10">
            <div class="text-white flex items-center gap-4">
                <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-md border border-white/10">
                    <i class="fa-solid fa-sliders text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-3xl font-bold tracking-tight">Operations Control Center</h2>
                    <p class="text-blue-100 mt-1 font-medium">Manage and correct operational discrepancies.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="bg-white shadow-xl shadow-slate-200/50 rounded-2xl border border-slate-100 p-6">
        
        <!-- Modern Segmented Tabs -->
        <div class="flex justify-center mb-8">
            <div class="bg-slate-100 p-1.5 rounded-xl inline-flex shadow-inner" id="operations-tab" role="tablist">
                <button class="px-6 py-2.5 rounded-lg text-sm font-bold transition-all duration-200 aria-selected:bg-white aria-selected:text-blue-600 aria-selected:shadow-sm aria-selected:ring-1 aria-selected:ring-slate-200 text-slate-500 hover:text-slate-700 flex items-center gap-2" id="orders-tab" data-tabs-target="#orders" type="button" role="tab" aria-controls="orders" aria-selected="true">
                    <i class="fa-solid fa-box"></i> Edit Orders
                </button>
                <button class="px-6 py-2.5 rounded-lg text-sm font-bold transition-all duration-200 aria-selected:bg-white aria-selected:text-emerald-600 aria-selected:shadow-sm aria-selected:ring-1 aria-selected:ring-slate-200 text-slate-500 hover:text-slate-700 flex items-center gap-2 ml-1" id="deliveries-tab" data-tabs-target="#deliveries" type="button" role="tab" aria-controls="deliveries" aria-selected="false">
                    <i class="fa-solid fa-truck-fast"></i> Edit Deliveries
                </button>
            </div>
        </div>

        <div id="operations-tab-content">
            <!-- Orders Tab -->
            <div class="hidden" id="orders" role="tabpanel" aria-labelledby="orders-tab">
                <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center mb-6 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                            <i class="fa-solid fa-calendar-day text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Recent Orders</h3>
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-wide">Last 48 Hours</p>
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" id="order-search" placeholder="Search ID, SR, Retailer..." class="pl-9 pr-4 py-2.5 bg-white border border-slate-200 text-sm font-medium rounded-lg focus:ring-2 focus:ring-blue-100 focus:border-blue-500 w-full sm:w-64 shadow-sm outline-none transition-all" oninput="renderOrders(windowOrdersData)">
                        </div>
                        <input type="date" id="order-date" class="px-4 py-2.5 bg-white border border-slate-200 text-sm font-medium rounded-lg focus:ring-2 focus:ring-blue-100 focus:border-blue-500 shadow-sm outline-none transition-all text-slate-600" onchange="renderOrders(windowOrdersData)">
                        <button class="bg-slate-800 hover:bg-slate-900 text-white px-5 py-2.5 text-sm font-bold rounded-lg shadow-md transition-all flex items-center gap-2" onclick="fetchOrders()">
                            <i class="fa-solid fa-rotate-right"></i> Refresh
                        </button>
                    </div>
                </div>

                <div class="border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-slate-600 whitespace-nowrap">
                            <thead class="text-xs text-slate-500 uppercase tracking-wider bg-slate-50 font-bold border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-4">Order ID</th>
                                    <th class="px-6 py-4">SR Name</th>
                                    <th class="px-6 py-4">Retailer</th>
                                    <th class="px-6 py-4">Total Amount</th>
                                    <th class="px-6 py-4">Date & Time</th>
                                    <th class="px-6 py-4 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="orders-list" class="divide-y divide-slate-100">
                                <tr><td colspan="6" class="text-center py-8 font-medium text-slate-500">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Deliveries Tab -->
            <div class="hidden" id="deliveries" role="tabpanel" aria-labelledby="deliveries-tab">
                <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center mb-6 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                            <i class="fa-solid fa-truck-fast text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Recent Deliveries</h3>
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-wide">Last 48 Hours</p>
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" id="delivery-search" placeholder="Search ID, DSR, Retailer..." class="pl-9 pr-4 py-2.5 bg-white border border-slate-200 text-sm font-medium rounded-lg focus:ring-2 focus:ring-emerald-100 focus:border-emerald-500 w-full sm:w-64 shadow-sm outline-none transition-all" oninput="renderDeliveries(windowDeliveriesData)">
                        </div>
                        <input type="date" id="delivery-date" class="px-4 py-2.5 bg-white border border-slate-200 text-sm font-medium rounded-lg focus:ring-2 focus:ring-emerald-100 focus:border-emerald-500 shadow-sm outline-none transition-all text-slate-600" onchange="renderDeliveries(windowDeliveriesData)">
                        <button class="bg-slate-800 hover:bg-slate-900 text-white px-5 py-2.5 text-sm font-bold rounded-lg shadow-md transition-all flex items-center gap-2" onclick="fetchDeliveries()">
                            <i class="fa-solid fa-rotate-right"></i> Refresh
                        </button>
                    </div>
                </div>

                <div class="border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-slate-600 whitespace-nowrap">
                            <thead class="text-xs text-slate-500 uppercase tracking-wider bg-slate-50 font-bold border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-4">Delivery ID</th>
                                    <th class="px-6 py-4">Invoice No</th>
                                    <th class="px-6 py-4">DSR Name</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4">Paid Amount</th>
                                    <th class="px-6 py-4">Due Amount</th>
                                    <th class="px-6 py-4 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="deliveries-list" class="divide-y divide-slate-100">
                                <tr><td colspan="7" class="text-center py-8 font-medium text-slate-500">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Order Modal -->
<div id="editOrderModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeEditOrderModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-2xl text-left shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl w-full border border-slate-100 flex flex-col max-h-[90vh]">
            
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/80 flex justify-between items-center sticky top-0 z-10">
                <div>
                    <h3 class="text-xl font-bold text-slate-800 flex items-center gap-2" id="modal-title">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-file-pen"></i>
                        </div>
                        Edit Order <span id="edit-order-id-display" class="text-blue-600"></span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-1 ml-10">Make corrections to this order. All changes are securely logged.</p>
                </div>
                <button onclick="closeEditOrderModal()" class="w-8 h-8 flex items-center justify-center rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-200 transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto">
                <form id="editOrderForm" onsubmit="submitEditOrder(event)">
                    <input type="hidden" id="edit-order-id" name="order_id">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Reason for Edit <span class="text-red-500">*</span></label>
                            <input type="text" id="edit-order-reason" name="reason" required placeholder="e.g. Wrong quantity entered by SR" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-medium text-slate-900 outline-none focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition shadow-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Order Date <span class="text-red-500">*</span></label>
                            <input type="date" id="edit-order-date" name="order_date" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-900 outline-none focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition shadow-sm">
                        </div>
                    </div>
                    
                    <div class="border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-slate-600">
                                <thead class="text-xs text-slate-500 uppercase bg-slate-50 font-bold border-b border-slate-200">
                                    <tr>
                                        <th class="px-4 py-3">Product</th>
                                        <th class="px-4 py-3 w-32">Unit Price</th>
                                        <th class="px-4 py-3 w-32">Qty (Pcs)</th>
                                        <th class="px-4 py-3 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="edit-order-items" class="divide-y divide-slate-100">
                                    <!-- Items populated via JS -->
                                </tbody>
                                <tfoot class="bg-slate-50/80 border-t border-slate-200">
                                    <tr>
                                        <td colspan="3" class="text-right px-4 py-4 font-bold text-slate-700">Revised Order Total:</td>
                                        <td class="px-4 py-4 font-bold text-blue-600 text-right text-lg" id="edit-order-new-total">0.00</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 rounded-b-2xl">
                <button type="button" onclick="closeEditOrderModal()" class="px-5 py-2.5 rounded-xl border border-slate-300 bg-white text-sm font-bold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition shadow-sm">Cancel</button>
                <button type="button" onclick="submitEditOrder(event)" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold shadow-md shadow-blue-500/20 transition flex items-center gap-2">
                    <i class="fa-solid fa-check"></i> Save Order Changes
                </button>
            </div>
        </div>
    </div>
</div>

<div id="editDeliveryModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeEditDeliveryModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-2xl text-left shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl w-full border border-slate-100 flex flex-col max-h-[90vh]">
            
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/80 flex justify-between items-center sticky top-0 z-10">
                <div>
                    <h3 class="text-xl font-bold text-slate-800 flex items-center gap-2" id="modal-title">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-truck-fast"></i>
                        </div>
                        Edit Delivery <span id="edit-delivery-id-display" class="text-emerald-600"></span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-1 ml-10">Make corrections to this delivery. All changes are securely logged.</p>
                </div>
                <button onclick="closeEditDeliveryModal()" class="w-8 h-8 flex items-center justify-center rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-200 transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto">
                <form id="editDeliveryForm" onsubmit="submitEditDelivery(event)">
                    <input type="hidden" id="edit-delivery-id" name="delivery_id">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Reason for Edit <span class="text-red-500">*</span></label>
                            <input type="text" id="edit-delivery-reason" name="reason" required placeholder="e.g. Delivered 3 pcs instead of 3 box" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-medium text-slate-900 outline-none focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 transition shadow-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Paid Amount (Tk) <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-slate-400 font-medium">Tk</span>
                                </div>
                                <input type="number" step="0.01" id="edit-delivery-paid" name="paid_amount" required class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-3 text-sm font-bold text-slate-900 outline-none focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 transition shadow-sm">
                            </div>
                        </div>
                    </div>
                    
                    <div class="border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-slate-600">
                                <thead class="text-xs text-slate-500 uppercase bg-slate-50 font-bold border-b border-slate-200">
                                    <tr>
                                        <th class="px-4 py-3">Product</th>
                                        <th class="px-4 py-3 w-32">Unit Price</th>
                                        <th class="px-4 py-3 w-32">Delivered Qty</th>
                                        <th class="px-4 py-3 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="edit-delivery-items" class="divide-y divide-slate-100">
                                    <!-- Items populated via JS -->
                                </tbody>
                                <tfoot class="bg-slate-50/80 border-t border-slate-200">
                                    <tr>
                                        <td colspan="3" class="text-right px-4 py-4 font-bold text-slate-700">Revised Delivery Total:</td>
                                        <td class="px-4 py-4 font-bold text-emerald-600 text-right text-lg" id="edit-delivery-new-total">0.00</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3 rounded-b-2xl">
                <button type="button" onclick="closeEditDeliveryModal()" class="px-5 py-2.5 rounded-xl border border-slate-300 bg-white text-sm font-bold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition shadow-sm">Cancel</button>
                <button type="button" onclick="submitEditDelivery(event)" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow-md shadow-emerald-500/20 transition flex items-center gap-2">
                    <i class="fa-solid fa-check"></i> Save Delivery Changes
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let windowOrdersData = [];
let windowDeliveriesData = [];
const basePath = '<?= BASE_URL ?>';

document.addEventListener('DOMContentLoaded', () => {
    // Tab logic
    const tabs = document.querySelectorAll('[role="tab"]');
    const tabContents = document.querySelectorAll('[role="tabpanel"]');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.setAttribute('aria-selected', 'false'));
            tab.setAttribute('aria-selected', 'true');
            tabContents.forEach(tc => tc.classList.add('hidden'));
            document.querySelector(tab.dataset.tabsTarget).classList.remove('hidden');
        });
    });

    // Default activate first tab
    tabs[0].click();
    fetchOrders();
    fetchDeliveries();
});

async function fetchOrders() {
    document.getElementById('orders-list').innerHTML = '<tr><td colspan="6" class="text-center py-4">Loading...</td></tr>';
    try {
        const res = await fetch(basePath + '/manager/api/operations/orders');
        const json = await res.json();
        if (json.success) {
            windowOrdersData = json.data;
            renderOrders(windowOrdersData);
        } else {
            document.getElementById('orders-list').innerHTML = `<tr><td colspan="6" class="text-center text-red-500 py-4">${json.message || 'Failed to fetch orders'}</td></tr>`;
        }
    } catch (e) {
        document.getElementById('orders-list').innerHTML = '<tr><td colspan="6" class="text-center text-red-500 py-4">Network error or invalid response.</td></tr>';
        console.error(e);
    }
}

function renderOrders(ordersData) {
    if (!ordersData) return;
    const search = document.getElementById('order-search').value.toLowerCase();
    const dateFilter = document.getElementById('order-date').value;
    
    let filtered = ordersData.filter(o => {
        const idMatch = o.id.toString().includes(search);
        const srMatch = (o.sr_name || '').toLowerCase().includes(search);
        const retMatch = (o.retailer_name || '').toLowerCase().includes(search);
        const dateMatch = dateFilter ? o.created_at.startsWith(dateFilter) : true;
        
        return (idMatch || srMatch || retMatch) && dateMatch;
    });

    let html = '';
    filtered.forEach(o => {
        html += `<tr class="hover:bg-slate-50/50 transition-colors group">
            <td class="px-6 py-4 font-bold text-slate-700">#${o.id}</td>
            <td class="px-6 py-4 font-medium text-slate-700">${o.sr_name || '-'}</td>
            <td class="px-6 py-4">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                    <i class="fa-solid fa-shop mr-1.5"></i> ${o.retailer_name || '-'}
                </span>
            </td>
            <td class="px-6 py-4 font-bold text-slate-800">Tk ${parseFloat(o.total_amount).toFixed(2)}</td>
            <td class="px-6 py-4 text-slate-500 text-xs font-medium"><i class="fa-regular fa-clock mr-1"></i> ${o.created_at}</td>
            <td class="px-6 py-4 text-center flex items-center justify-center gap-2">
                <button onclick="editOrder(${o.id})" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all shadow-sm" title="Edit">
                    <i class="fa-solid fa-pen"></i>
                </button>
                <button onclick="deleteOrder(${o.id})" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all shadow-sm" title="Delete">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </td>
        </tr>`;
    });
    
    if(filtered.length === 0) html = '<tr><td colspan="6" class="text-center py-8 font-medium text-slate-500">No matching orders found</td></tr>';
    document.getElementById('orders-list').innerHTML = html;
}

async function fetchDeliveries() {
    document.getElementById('deliveries-list').innerHTML = '<tr><td colspan="7" class="text-center py-4">Loading...</td></tr>';
    try {
        const res = await fetch(basePath + '/manager/api/operations/deliveries');
        const json = await res.json();
        if (json.success) {
            windowDeliveriesData = json.data;
            renderDeliveries(windowDeliveriesData);
        } else {
            document.getElementById('deliveries-list').innerHTML = `<tr><td colspan="7" class="text-center text-red-500 py-4">${json.message || 'Failed to fetch deliveries'}</td></tr>`;
        }
    } catch (e) {
        document.getElementById('deliveries-list').innerHTML = '<tr><td colspan="7" class="text-center text-red-500 py-4">Network error or invalid response.</td></tr>';
        console.error(e);
    }
}

function renderDeliveries(deliveriesData) {
    if (!deliveriesData) return;
    const search = document.getElementById('delivery-search').value.toLowerCase();
    const dateFilter = document.getElementById('delivery-date').value;
    
    let filtered = deliveriesData.filter(d => {
        const idMatch = d.id.toString().includes(search);
        const dsrMatch = (d.dsr_name || '').toLowerCase().includes(search);
        const retMatch = (d.retailer_name || '').toLowerCase().includes(search);
        const dateMatch = dateFilter ? d.created_at.startsWith(dateFilter) : true;
        
        return (idMatch || dsrMatch || retMatch) && dateMatch;
    });

    let html = '';
    filtered.forEach(d => {
        const paid = parseFloat(d.paid_amount) || 0;
        const due = parseFloat(d.due_amount) || 0;
        
        let statusBadge = '';
        if (d.status === 'Delivered') {
            statusBadge = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200"><i class="fa-solid fa-check mr-1"></i> Delivered</span>`;
        } else if (d.status === 'Partial') {
            statusBadge = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200"><i class="fa-solid fa-circle-half-stroke mr-1"></i> Partial</span>`;
        } else {
            statusBadge = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-800 border border-slate-200">${d.status}</span>`;
        }

        html += `<tr class="hover:bg-slate-50/50 transition-colors group">
            <td class="px-6 py-4 font-bold text-slate-700">#${d.id}</td>
            <td class="px-6 py-4 font-mono text-xs font-medium text-slate-500">${d.invoice_no || '-'}</td>
            <td class="px-6 py-4 font-medium text-slate-700">${d.dsr_name || '-'}</td>
            <td class="px-6 py-4">${statusBadge}</td>
            <td class="px-6 py-4 font-bold text-emerald-600">Tk ${paid.toFixed(2)}</td>
            <td class="px-6 py-4 font-bold text-red-500">Tk ${due.toFixed(2)}</td>
            <td class="px-6 py-4 text-center">
                <button onclick="editDelivery(${d.id})" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all shadow-sm">
                    <i class="fa-solid fa-pen"></i>
                </button>
            </td>
        </tr>`;
    });
    
    if(filtered.length === 0) html = '<tr><td colspan="7" class="text-center py-8 font-medium text-slate-500">No matching deliveries found</td></tr>';
    document.getElementById('deliveries-list').innerHTML = html;
}

function editOrder(id) {
    const order = windowOrdersData.find(o => parseInt(o.id) === id);
    if (!order) return;
    
    document.getElementById('edit-order-id-display').innerText = '#' + order.id;
    document.getElementById('edit-order-id').value = order.id;
    const orderDate = order.created_at ? order.created_at.split(' ')[0] : '';
    document.getElementById('edit-order-date').value = orderDate;
    
    let itemsHtml = '';
    let total = 0;
    
    order.items.forEach(item => {
        const qty = parseInt(item.quantity) || 0;
        const price = parseFloat(item.unit_price) || 0;
        const itemTotal = qty * price;
        total += itemTotal;
        
        itemsHtml += `
            <tr class="hover:bg-slate-50 transition-colors" data-item-id="${item.id}">
                <td class="px-4 py-3 font-medium text-slate-800">${item.product_name}</td>
                <td class="px-4 py-3">
                    <input type="number" step="0.01" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-sm font-medium outline-none focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 edit-price transition shadow-sm" value="${price}" onchange="recalcEditOrder()">
                </td>
                <td class="px-4 py-3">
                    <input type="number" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-sm font-medium outline-none focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 edit-qty transition shadow-sm" value="${qty}" onchange="recalcEditOrder()">
                </td>
                <td class="px-4 py-3 edit-item-total font-bold text-slate-700 text-right">Tk ${itemTotal.toFixed(2)}</td>
            </tr>
        `;
    });
    
    document.getElementById('edit-order-items').innerHTML = itemsHtml;
    document.getElementById('edit-order-new-total').innerText = 'Tk ' + total.toFixed(2);
    document.getElementById('editOrderModal').classList.remove('hidden');
}

function closeEditOrderModal() {
    document.getElementById('editOrderModal').classList.add('hidden');
    document.getElementById('editOrderForm').reset();
}

function recalcEditOrder() {
    let total = 0;
    document.querySelectorAll('#edit-order-items tr').forEach(row => {
        const price = parseFloat(row.querySelector('.edit-price').value) || 0;
        const qty = parseInt(row.querySelector('.edit-qty').value) || 0;
        const itemTotal = price * qty;
        total += itemTotal;
        row.querySelector('.edit-item-total').innerText = 'Tk ' + itemTotal.toFixed(2);
    });
    document.getElementById('edit-order-new-total').innerText = 'Tk ' + total.toFixed(2);
}

async function submitEditOrder(e) {
    e.preventDefault();
    const id = document.getElementById('edit-order-id').value;
    const reason = document.getElementById('edit-order-reason').value;
    const orderDate = document.getElementById('edit-order-date').value;
    
    const items = [];
    document.querySelectorAll('#edit-order-items tr').forEach(row => {
        items.push({
            id: row.dataset.itemId,
            price: row.querySelector('.edit-price').value,
            qty: row.querySelector('.edit-qty').value
        });
    });
    
    const formData = new FormData();
    formData.append('order_id', id);
    formData.append('reason', reason);
    formData.append('order_date', orderDate);
    formData.append('items', JSON.stringify(items));
    
    try {
        const res = await fetch(basePath + '/manager/api/operations/edit-order/' + id, {
            method: 'POST',
            body: formData
        });
        const json = await res.json();
        if (json.success) {
            alert('Order updated successfully!');
            closeEditOrderModal();
            fetchOrders();
        } else {
            alert('Error: ' + json.message);
        }
    } catch (e) {
        console.error(e);
        alert('An error occurred.');
    }
}

async function deleteOrder(id) {
    const reason = prompt(`WARNING: You are about to permanently delete Order #${id}.\n\nPlease provide a reason for deletion:`);
    if (reason === null) return; // User cancelled
    if (reason.trim() === '') {
        alert("A reason is required to delete an order.");
        return;
    }
    
    if (confirm(`Are you absolutely sure you want to delete Order #${id}? This cannot be undone.`)) {
        try {
            const formData = new FormData();
            formData.append('reason', reason);
            const res = await fetch(basePath + '/manager/api/operations/delete-order/' + id, {
                method: 'POST',
                body: formData
            });
            const json = await res.json();
            if (json.success) {
                alert('Order deleted successfully!');
                fetchOrders();
            } else {
                alert('Error: ' + json.message);
            }
        } catch (e) {
            console.error(e);
            alert('An error occurred.');
        }
    }
}

function editDelivery(id) {
    const delivery = windowDeliveriesData.find(d => parseInt(d.id) === id);
    if (!delivery) return;
    
    document.getElementById('edit-delivery-id-display').innerText = '#' + delivery.id;
    document.getElementById('edit-delivery-id').value = delivery.id;
    document.getElementById('edit-delivery-paid').value = delivery.paid_amount;
    
    let itemsHtml = '';
    let total = 0;
    
    delivery.items.forEach(item => {
        const qty = parseInt(item.delivered_quantity) || 0;
        const price = parseFloat(item.unit_price) || 0;
        const itemTotal = qty * price;
        total += itemTotal;
        
        itemsHtml += `
            <tr class="hover:bg-slate-50 transition-colors" data-item-id="${item.id}">
                <td class="px-4 py-3 font-medium text-slate-800">${item.product_name}</td>
                <td class="px-4 py-3">
                    <input type="number" step="0.01" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-sm font-medium outline-none focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 edit-price transition shadow-sm" value="${price}" onchange="recalcEditDelivery()">
                </td>
                <td class="px-4 py-3">
                    <input type="number" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-2 py-1.5 text-sm font-medium outline-none focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 edit-qty transition shadow-sm" value="${qty}" onchange="recalcEditDelivery()">
                </td>
                <td class="px-4 py-3 edit-item-total font-bold text-slate-700 text-right">Tk ${itemTotal.toFixed(2)}</td>
            </tr>
        `;
    });
    
    document.getElementById('edit-delivery-items').innerHTML = itemsHtml;
    document.getElementById('edit-delivery-new-total').innerText = 'Tk ' + total.toFixed(2);
    document.getElementById('editDeliveryModal').classList.remove('hidden');
}

function closeEditDeliveryModal() {
    document.getElementById('editDeliveryModal').classList.add('hidden');
    document.getElementById('editDeliveryForm').reset();
}

function recalcEditDelivery() {
    let total = 0;
    document.querySelectorAll('#edit-delivery-items tr').forEach(row => {
        const price = parseFloat(row.querySelector('.edit-price').value) || 0;
        const qty = parseInt(row.querySelector('.edit-qty').value) || 0;
        const itemTotal = price * qty;
        total += itemTotal;
        row.querySelector('.edit-item-total').innerText = 'Tk ' + itemTotal.toFixed(2);
    });
    document.getElementById('edit-delivery-new-total').innerText = 'Tk ' + total.toFixed(2);
}

async function submitEditDelivery(e) {
    e.preventDefault();
    if (!document.getElementById('editDeliveryForm').reportValidity()) return;
    
    const id = document.getElementById('edit-delivery-id').value;
    const reason = document.getElementById('edit-delivery-reason').value;
    const paidAmount = document.getElementById('edit-delivery-paid').value;
    
    const items = [];
    document.querySelectorAll('#edit-delivery-items tr').forEach(row => {
        const itemId = row.getAttribute('data-item-id');
        const qty = row.querySelector('.edit-qty').value;
        items.push({ id: itemId, qty: qty });
    });
    
    const formData = new FormData();
    formData.append('delivery_id', id);
    formData.append('reason', reason);
    formData.append('paid_amount', paidAmount);
    formData.append('items', JSON.stringify(items));
    
    try {
        const res = await fetch(basePath + '/manager/api/operations/edit-delivery/' + id, {
            method: 'POST',
            body: formData
        });
        const json = await res.json();
        if (json.success) {
            alert('Delivery updated successfully!');
            closeEditDeliveryModal();
            fetchDeliveries();
        } else {
            alert('Error: ' + json.message);
        }
    } catch (e) {
        console.error(e);
        alert('An error occurred.');
    }
}
</script>
