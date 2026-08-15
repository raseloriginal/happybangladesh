<?php $pageTitle = 'মূল্য সংশোধন'; ?>
<script src="https://cdn.tailwindcss.com"></script>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap');
    
    .font-siliguri {
        font-family: 'Hind Siliguri', 'Inter', sans-serif;
    }
    
    .glass-effect {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(226, 232, 240, 0.8);
    }
    .loader {
        border-top-color: #3b82f6;
        -webkit-animation: spinner 1.5s linear infinite;
        animation: spinner 1.5s linear infinite;
    }
    @keyframes spinner {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .card-hover:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
        transform: translateY(-2px);
        border-color: #bfdbfe;
    }
</style>

<div class="p-3 sm:p-5 space-y-5 pb-28 max-w-5xl mx-auto font-siliguri text-slate-800">

    <!-- Header / Top Bar -->
    <div class="bg-white/95 backdrop-blur-md px-4 py-3 sm:px-5 sm:py-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 transition-all">
        <div class="flex items-center space-x-3">
            <div class="bg-blue-50 text-blue-600 p-2 rounded-lg">
                <i class="fa-solid fa-tags text-xl"></i>
            </div>
            <h1 class="text-xl font-bold tracking-tight text-slate-800">মূল্য সংশোধন</h1>
        </div>
        
        <div class="flex items-center space-x-2 w-full sm:w-auto justify-end">
            <!-- Refresh -->
            <button onclick="fetchProducts()" class="bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-full w-10 h-10 flex items-center justify-center font-medium transition-colors shadow-sm shrink-0">
                <i class="fa-solid fa-rotate-right" id="refresh-icon"></i>
            </button>
        </div>
    </div>

    <!-- Main Content Area -->
    <?php if (isset($canCorrectPrice) && !$canCorrectPrice): ?>
        <div class="flex flex-col items-center justify-center min-h-[400px] bg-white/90 backdrop-blur-sm rounded-2xl p-8 text-center border border-slate-200">
            <div class="bg-red-50 text-red-500 p-5 rounded-full mb-4">
                <i class="fa-solid fa-lock text-4xl"></i>
            </div>
            <h2 class="text-xl font-bold text-slate-800 mb-2">প্রবেশাধিকার সংরক্ষিত</h2>
            <p class="text-slate-500 max-w-md">
                আপনার মূল্য সংশোধনের সুবিধাটি বর্তমানে বন্ধ রয়েছে। প্রয়োজনে এডমিন এর সাথে যোগাযোগ করুন।
            </p>
        </div>
    <?php else: ?>
    <div class="relative min-h-[400px]">
        <div class="flex justify-between items-end mb-4">
            <p class="text-sm font-medium text-slate-500" id="showing-text">মোট ০ টি পণ্য দেখাচ্ছে</p>
        </div>

        <!-- Loading State -->
        <div id="loading-state" class="absolute inset-0 flex flex-col items-center justify-center z-10 bg-white/50 backdrop-blur-sm rounded-2xl">
            <div class="loader ease-linear rounded-full border-4 border-t-4 border-slate-200 h-10 w-10 mb-4"></div>
            <p class="text-slate-500 font-medium">পণ্য লোড হচ্ছে...</p>
        </div>

        <!-- Error State -->
        <div id="error-state" class="absolute inset-0 flex flex-col items-center justify-center z-10 bg-white/90 backdrop-blur-sm rounded-2xl hidden">
            <div class="bg-red-50 text-red-500 p-4 rounded-full mb-4">
                <i class="fa-solid fa-triangle-exclamation text-3xl"></i>
            </div>
            <p class="text-slate-800 font-medium text-lg">ডেটা লোড করতে ব্যর্থ</p>
            <p class="text-slate-500 text-sm mb-4" id="error-message">এপিআই-এর সাথে সংযোগ করা যাচ্ছে না।</p>
            <button onclick="fetchProducts()" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm">পুনরায় চেষ্টা করুন</button>
        </div>

        <!-- Products Grid -->
        <div id="products-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-4">
            <!-- Cards will be injected here via JS -->
        </div>
    </div>
    <?php endif; ?>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-24 right-5 transform translate-y-20 opacity-0 transition-all duration-300 z-[100] flex items-center p-4 mb-4 text-slate-600 bg-white rounded-xl shadow-xl border border-slate-100" role="alert">
        <div id="toast-icon" class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg mr-3">
            <i class="fa-solid"></i>
        </div>
        <div class="text-sm font-medium pr-2 font-siliguri" id="toast-message">Message here</div>
        <button type="button" class="ml-auto bg-white text-slate-400 hover:text-slate-700 rounded-lg p-1 hover:bg-slate-100 inline-flex transition-colors" onclick="hideToast()">
            <span class="sr-only">Close</span>
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

</div>

<script>
    let productsData = [];
    const baseUrl = '<?= url('') ?>/';

    document.addEventListener('DOMContentLoaded', fetchProducts);

    async function fetchProducts() {
        const loadingState = document.getElementById('loading-state');
        const errorState = document.getElementById('error-state');
        const productsGrid = document.getElementById('products-grid');
        const refreshIcon = document.getElementById('refresh-icon');
        
        loadingState.classList.remove('hidden');
        errorState.classList.add('hidden');
        refreshIcon.classList.add('fa-spin');
        productsGrid.innerHTML = '';

        try {
            const response = await fetch('<?= url('sr/api/products') ?>');
            if (!response.ok) throw new Error('Network response was not ok');
            
            const result = await response.json();
            
            if (result.success) {
                productsData = result.products;
                renderCards(productsData);
                document.getElementById('showing-text').textContent = `মোট ${productsData.length} টি পণ্য দেখাচ্ছে`;
            } else {
                throw new Error(result.message || 'API returned an error');
            }
        } catch (error) {
            console.error('Error fetching products:', error);
            document.getElementById('error-message').textContent = error.message;
            errorState.classList.remove('hidden');
        } finally {
            loadingState.classList.add('hidden');
            setTimeout(() => refreshIcon.classList.remove('fa-spin'), 500);
        }
    }

    function renderCards(data) {
        const grid = document.getElementById('products-grid');
        grid.innerHTML = '';
        
        if(data.length === 0) {
            grid.innerHTML = `<div class="col-span-full py-12 text-center text-slate-500 bg-white rounded-xl border border-slate-200">কোনো পণ্য পাওয়া যায়নি।</div>`;
            return;
        }

        data.forEach((product) => {
            const bp = parseFloat(product.buying_price || 0);
            const dp = parseFloat(product.dealer_percentage || 0);
            const sp = parseFloat(product.price || 0);
            
            const imageUrl = product.image ? (baseUrl + product.image) : 'https://placehold.co/100x100?text=No+Image';
            
            let isPending = false;
            let pendingBp = bp;
            let pendingSp = sp;

            if (product.pending_approval_data) {
                isPending = true;
                try {
                    const parsed = JSON.parse(product.pending_approval_data);
                    if (parsed.buying_price) pendingBp = parseFloat(parsed.buying_price);
                    if (parsed.price) pendingSp = parseFloat(parsed.price);
                } catch(e) {}
            }

            const card = document.createElement('div');
            card.className = `glass-effect rounded-xl p-4 transition-all duration-200 card-hover product-card flex flex-col font-sans ${isPending ? 'ring-2 ring-amber-400 bg-amber-50/30' : ''}`;
            card.id = `card-${product.id}`;
            card.setAttribute('data-name', product.name.toLowerCase());
            card.setAttribute('data-id', product.id);
            
            card.innerHTML = `
                <!-- Top Area: Image & Info -->
                <div class="flex items-start space-x-3 mb-3">
                    <img src="${imageUrl}" alt="${product.name}" class="w-12 h-12 object-cover rounded-lg border border-slate-200 shrink-0">
                    <div class="flex-grow min-w-0">
                        <div class="flex justify-between items-start">
                            <h3 class="text-sm font-bold text-slate-800 leading-tight pr-2 line-clamp-2" title="${product.name}">${product.name}</h3>
                            <span class="bg-slate-100 text-slate-500 text-[10px] font-bold px-1.5 py-0.5 rounded-md shrink-0">#${product.id}</span>
                        </div>
                        
                        <!-- Current Price Summary (Compact) -->
                        <div class="mt-1.5 flex flex-wrap items-center text-[11px] text-slate-500 gap-x-1 gap-y-0.5 font-siliguri">
                            <span>বর্তমান:</span>
                            <span class="font-bold text-slate-700 bg-white px-1 rounded shadow-sm border border-slate-100" id="curr-bp-${product.id}">৳${bp.toFixed(2)}</span>
                            <span class="text-[9px] text-slate-400"><i class="fa-solid fa-plus"></i></span>
                            <span class="text-blue-600 font-semibold bg-blue-50 px-1 rounded" id="dp-${product.id}">${dp.toFixed(2)}%</span>
                            <span class="text-[9px] text-slate-400"><i class="fa-solid fa-equals"></i></span>
                            <span class="text-emerald-600 font-bold" id="curr-sp-${product.id}">৳${sp.toFixed(2)}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Modification Area: Vertical Stacked for Mobile Responsiveness -->
                <div class="mt-auto bg-slate-50/70 rounded-xl p-2.5 border border-slate-100 shadow-sm">
                    <div class="flex items-center justify-between mb-2 px-1">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider font-siliguri">নতুন মূল্য নির্ধারণ</span>
                    </div>
                    
                    <div class="flex flex-col space-y-2">
                        <!-- Row 1: Input BP -->
                        <div class="flex items-center justify-between bg-white border border-slate-200 rounded-lg p-1.5 focus-within:ring-2 focus-within:ring-blue-400 transition-shadow">
                            <span class="text-[11px] text-slate-600 font-semibold pl-1 shrink-0 font-siliguri">কেনা দাম:</span>
                            <div class="flex items-center relative w-28 shrink-0">
                                <span class="absolute left-2.5 text-slate-400 text-xs font-bold">৳</span>
                                <input type="number" step="0.01" id="input-${product.id}" class="w-full pl-6 pr-2 py-1 bg-transparent text-right font-bold outline-none text-[13px] ${isPending ? 'text-amber-700' : 'text-slate-800'}" value="${pendingBp.toFixed(2)}" oninput="calculateRealtime(${product.id}, ${dp})" ${isPending ? 'disabled' : ''}>
                            </div>
                        </div>
                        
                        <!-- Row 2: Realtime SP -->
                        <div class="flex items-center justify-between bg-emerald-50 border border-emerald-100 rounded-lg p-1.5">
                            <span class="text-[11px] text-emerald-700 font-semibold pl-1 shrink-0 font-siliguri">বিক্রয় মূল্য:</span>
                            <div class="flex items-center justify-end px-2 shrink-0">
                                <span class="font-bold text-[13px] ${isPending ? 'text-amber-700' : 'text-emerald-600'}" id="new-sp-${product.id}">৳${pendingSp.toFixed(2)}</span>
                            </div>
                        </div>
                        
                        <!-- Row 3: Button -->
                        <button onclick="updatePrice(${product.id})" id="btn-${product.id}" class="w-full mt-1 ${isPending ? 'bg-amber-500 opacity-50 pointer-events-none' : 'bg-blue-600 hover:bg-blue-700 active:bg-blue-800'} text-white rounded-lg py-2 text-xs font-bold transition-all flex items-center justify-center space-x-2 shadow-sm font-siliguri" ${isPending ? 'disabled' : ''}>
                            <i class="fa-solid ${isPending ? 'fa-clock' : 'fa-check-circle'} btn-icon"></i>
                            <span class="btn-text">${isPending ? 'অপেক্ষমান' : 'আপডেট করুন'}</span>
                            <i class="fa-solid fa-circle-notch fa-spin hidden btn-loader"></i>
                        </button>
                    </div>
                </div>
            `;
            grid.appendChild(card);
        });
    }

    function calculateRealtime(id, dp) {
        const inputVal = document.getElementById(`input-${id}`).value;
        const newSpEl = document.getElementById(`new-sp-${id}`);
        
        if(!inputVal || isNaN(inputVal) || inputVal < 0) {
            newSpEl.textContent = '৳0.00';
            newSpEl.classList.remove('text-emerald-600');
            newSpEl.classList.add('text-slate-400');
            return;
        }
        
        const bp = parseFloat(inputVal);
        const newSp = bp + (bp * (dp / 100));
        
        newSpEl.textContent = `৳${newSp.toFixed(2)}`;
        newSpEl.classList.add('text-emerald-600');
        newSpEl.classList.remove('text-slate-400');
    }

    async function updatePrice(id) {
        const inputEl = document.getElementById(`input-${id}`);
        const btnEl = document.getElementById(`btn-${id}`);
        const iconEl = btnEl.querySelector('.btn-icon');
        const loaderEl = btnEl.querySelector('.btn-loader');
        
        const newBp = parseFloat(inputEl.value);

        if (!newBp || isNaN(newBp) || newBp <= 0) {
            showToast('দয়া করে একটি সঠিক কেনা দাম লিখুন।', 'error');
            inputEl.focus();
            return;
        }

        btnEl.disabled = true;
        btnEl.classList.add('bg-blue-400', 'cursor-wait');
        btnEl.classList.remove('bg-blue-600', 'hover:bg-blue-700');
        iconEl.classList.add('hidden');
        loaderEl.classList.remove('hidden');

        try {
            const formData = new URLSearchParams();
            formData.append('id', id);
            formData.append('buying_price', newBp);

            const response = await fetch('<?= url('sr/api/price-correction/modify') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData.toString()
            });

            const result = await response.json();

            if (result.status === 'success') {
                showToast(`"${document.getElementById(`card-${id}`).getAttribute('data-name')}" এর মূল্য পরিবর্তনের অনুরোধ এডমিনের কাছে পাঠানো হয়েছে!`, 'success');
                
                const card = document.getElementById(`card-${id}`);
                card.classList.add('ring-2', 'ring-amber-400', 'bg-amber-50/30');
                
                // Keep the input fields as they are so SR sees what they requested, but maybe dim the update button.
                btnEl.classList.add('opacity-50', 'pointer-events-none');
                btnEl.querySelector('.btn-text').textContent = 'অনুরোধ পাঠানো হয়েছে';

            } else {
                throw new Error(result.message || 'আপডেট করতে ব্যর্থ হয়েছে');
            }
        } catch (error) {
            console.error('Update error:', error);
            showToast(error.message, 'error');
        } finally {
            btnEl.disabled = false;
            btnEl.classList.remove('bg-blue-400', 'cursor-wait');
            btnEl.classList.add('bg-blue-600', 'hover:bg-blue-700');
            loaderEl.classList.add('hidden');
            iconEl.classList.remove('hidden');
        }
    }



    let toastTimeout;
    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toast-message');
        const toastIcon = document.getElementById('toast-icon');
        const icon = toastIcon.querySelector('i');

        clearTimeout(toastTimeout);
        toastMessage.textContent = message;

        if (type === 'success') {
            toastIcon.className = 'inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 mr-3';
            icon.className = 'fa-solid fa-check text-sm';
        } else {
            toastIcon.className = 'inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-full bg-red-100 text-red-600 mr-3';
            icon.className = 'fa-solid fa-exclamation text-sm';
        }

        toast.classList.remove('translate-y-20', 'opacity-0');
        toast.classList.add('translate-y-0', 'opacity-100');
        
        toastTimeout = setTimeout(hideToast, 3000);
    }

    function hideToast() {
        const toast = document.getElementById('toast');
        toast.classList.remove('translate-y-0', 'opacity-100');
        toast.classList.add('translate-y-20', 'opacity-0');
    }
</script>
