<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">AI & Smart Tools</h1>
        <p class="text-sm text-slate-500 mt-1">AI Business Analytics & Real-time English ⇄ Bangla Translation</p>
    </div>
    
    <!-- Tab navigation -->
    <div class="inline-flex p-1 bg-slate-200/70 rounded-xl">
        <button id="tab-btn-analytics" onclick="switchTab('analytics')" class="px-4 py-2 rounded-lg text-xs font-bold transition-all bg-white text-blue-700 shadow-sm flex items-center gap-2">
            <i class="fa-solid fa-chart-pie"></i> Business Insights
        </button>
        <button id="tab-btn-translator" onclick="switchTab('translator')" class="px-4 py-2 rounded-lg text-xs font-bold transition-all text-slate-600 hover:text-slate-900 flex items-center gap-2">
            <i class="fa-solid fa-language text-base"></i> Live Translator (EN ⇄ BN)
        </button>
    </div>
</div>

<!-- ========================================== -->
<!-- TAB 1: BUSINESS INSIGHTS                   -->
<!-- ========================================== -->
<div id="tab-content-analytics">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        
        <!-- Sales Analysis Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col hover:border-blue-300 transition-all">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-xl mb-4 shadow-inner">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-900 mb-2">Sales Insights</h2>
            <p class="text-sm text-slate-500 flex-1 mb-4">Analyze the last 7 days of sales data to spot trends and opportunities.</p>
            <button onclick="analyzeData('analyze_sales', 'sales-result')" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-xl transition-all shadow-md shadow-blue-500/20 flex justify-center items-center gap-2">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Analyze Sales
            </button>
        </div>

        <!-- Inventory Analysis Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col hover:border-amber-300 transition-all">
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center text-xl mb-4 shadow-inner">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-900 mb-2">Inventory Check</h2>
            <p class="text-sm text-slate-500 flex-1 mb-4">Identify low-stock items across warehouses and get restock recommendations.</p>
            <button onclick="analyzeData('analyze_inventory', 'inventory-result')" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-semibold py-2.5 px-4 rounded-xl transition-all shadow-md shadow-amber-500/20 flex justify-center items-center gap-2">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Check Inventory
            </button>
        </div>

        <!-- Dealer Performance Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col hover:border-emerald-300 transition-all">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center text-xl mb-4 shadow-inner">
                <i class="fa-solid fa-store"></i>
            </div>
            <h2 class="text-lg font-bold text-slate-900 mb-2">Dealer Performance</h2>
            <p class="text-sm text-slate-500 flex-1 mb-4">Find dormant dealers and get strategies to re-engage them.</p>
            <button onclick="analyzeData('dealer_performance', 'dealer-result')" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 px-4 rounded-xl transition-all shadow-md shadow-emerald-500/20 flex justify-center items-center gap-2">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Analyze Dealers
            </button>
        </div>

    </div>

    <!-- Analytics Results Area -->
    <div class="space-y-6">
        <div id="sales-result" class="hidden bg-blue-50 border-l-4 border-blue-500 p-6 rounded-r-2xl shadow-sm"></div>
        <div id="inventory-result" class="hidden bg-amber-50 border-l-4 border-amber-500 p-6 rounded-r-2xl shadow-sm"></div>
        <div id="dealer-result" class="hidden bg-emerald-50 border-l-4 border-emerald-500 p-6 rounded-r-2xl shadow-sm"></div>
    </div>
</div>

<!-- ========================================== -->
<!-- TAB 2: REAL-TIME TRANSLATOR                -->
<!-- ========================================== -->
<div id="tab-content-translator" class="hidden">
    
    <!-- Language Switcher Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm mb-6 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="px-4 py-2 bg-blue-50 text-blue-700 font-bold text-sm rounded-xl border border-blue-100 flex items-center gap-2" id="source-lang-badge">
                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                <span id="source-lang-label">English (ইংরেজি)</span>
            </div>
            
            <!-- Swap Button -->
            <button onclick="swapLanguages()" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-blue-50 text-slate-600 hover:text-blue-600 transition-all flex items-center justify-center text-sm shadow-sm" title="Swap languages">
                <i class="fa-solid fa-right-left"></i>
            </button>
            
            <div class="px-4 py-2 bg-emerald-50 text-emerald-700 font-bold text-sm rounded-xl border border-emerald-100 flex items-center gap-2" id="target-lang-badge">
                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                <span id="target-lang-label">Bangla (বাংলা)</span>
            </div>
        </div>

        <div class="flex items-center gap-2 text-xs font-semibold text-slate-400">
            <i class="fa-solid fa-bolt text-amber-500"></i>
            <span>Instant translation as you type</span>
        </div>
    </div>

    <!-- Dual Box Translation Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Source Input Box -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm flex flex-col focus-within:border-blue-400 focus-within:ring-2 focus-within:ring-blue-100 transition-all">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between text-xs font-semibold text-slate-500">
                <span id="source-box-title">Source Text</span>
                <button onclick="clearSourceText()" class="hover:text-rose-600 transition-colors flex items-center gap-1">
                    <i class="fa-solid fa-trash-can"></i> Clear
                </button>
            </div>
            <textarea id="source-input" 
                      rows="8" 
                      placeholder="Type or paste text here to translate in real-time..." 
                      class="w-full p-5 text-slate-800 text-base leading-relaxed resize-none focus:outline-none placeholder:text-slate-400"
                      oninput="onSourceInput()"></textarea>
            <div class="p-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
                <span id="char-count">0 characters</span>
                <button onclick="pasteFromClipboard()" class="hover:text-blue-600 transition-colors flex items-center gap-1 font-semibold">
                    <i class="fa-regular fa-clipboard"></i> Paste
                </button>
            </div>
        </div>

        <!-- Target Output Box -->
        <div class="bg-slate-50/70 rounded-2xl border border-slate-200 shadow-sm flex flex-col">
            <div class="p-4 border-b border-slate-200/70 flex items-center justify-between text-xs font-semibold text-slate-500">
                <span id="target-box-title">Translated Result</span>
                <span id="translate-status" class="text-xs text-slate-400 flex items-center gap-1">
                    <i class="fa-solid fa-circle text-[8px] text-emerald-500"></i> Ready
                </span>
            </div>
            <div id="target-output" 
                 class="w-full p-5 text-slate-800 text-base leading-relaxed min-h-[200px] flex-1 select-all font-medium whitespace-pre-wrap">
                <span class="text-slate-400 font-normal italic">অনুবাদ এখানে তাৎক্ষণিকভাবে প্রদর্শিত হবে...</span>
            </div>
            <div class="p-4 border-t border-slate-200/70 flex items-center justify-between">
                <div class="text-xs text-slate-400">
                    <span>100% Free • High Accuracy</span>
                </div>
                <button id="copy-btn" 
                        onclick="copyTranslation()" 
                        class="px-4 py-2 bg-white hover:bg-slate-100 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 transition-all shadow-sm flex items-center gap-1.5">
                    <i class="fa-regular fa-copy"></i>
                    <span id="copy-btn-text">Copy Translation</span>
                </button>
            </div>
        </div>

    </div>

</div>

<script>
// ── TAB SWITCHING ──────────────────────────────────────────
function switchTab(tab) {
    const analyticsContent = document.getElementById('tab-content-analytics');
    const translatorContent = document.getElementById('tab-content-translator');
    const btnAnalytics = document.getElementById('tab-btn-analytics');
    const btnTranslator = document.getElementById('tab-btn-translator');

    if (tab === 'analytics') {
        analyticsContent.classList.remove('hidden');
        translatorContent.classList.add('hidden');
        btnAnalytics.className = 'px-4 py-2 rounded-lg text-xs font-bold transition-all bg-white text-blue-700 shadow-sm flex items-center gap-2';
        btnTranslator.className = 'px-4 py-2 rounded-lg text-xs font-bold transition-all text-slate-600 hover:text-slate-900 flex items-center gap-2';
    } else {
        analyticsContent.classList.add('hidden');
        translatorContent.classList.remove('hidden');
        btnTranslator.className = 'px-4 py-2 rounded-lg text-xs font-bold transition-all bg-white text-blue-700 shadow-sm flex items-center gap-2';
        btnAnalytics.className = 'px-4 py-2 rounded-lg text-xs font-bold transition-all text-slate-600 hover:text-slate-900 flex items-center gap-2';
        document.getElementById('source-input').focus();
    }
}

// ── REAL-TIME TRANSLATOR LOGIC ──────────────────────────────
let currentSource = 'en';
let currentTarget = 'bn';
let debounceTimer = null;

function onSourceInput() {
    const text = document.getElementById('source-input').value;
    document.getElementById('char-count').innerText = `${text.length} characters`;

    if (!text.trim()) {
        document.getElementById('target-output').innerHTML = '<span class="text-slate-400 font-normal italic">অনুবাদ এখানে তাৎক্ষণিকভাবে প্রদর্শিত হবে...</span>';
        document.getElementById('translate-status').innerHTML = '<i class="fa-solid fa-circle text-[8px] text-emerald-500"></i> Ready';
        return;
    }

    document.getElementById('translate-status').innerHTML = '<i class="fa-solid fa-circle-notch fa-spin text-blue-600 text-xs"></i> Translating...';

    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        performTranslation(text);
    }, 350);
}

async function performTranslation(text) {
    try {
        const response = await fetch('<?= BASE_URL ?>/admin/ai-assistant/translate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                text: text,
                source: currentSource,
                target: currentTarget
            })
        });

        const data = await response.json();
        if (data.success) {
            document.getElementById('target-output').innerText = data.translation;
            document.getElementById('translate-status').innerHTML = '<i class="fa-solid fa-check text-emerald-600 text-xs"></i> Translated';
        } else {
            document.getElementById('translate-status').innerHTML = '<span class="text-rose-500 text-xs">Error</span>';
        }
    } catch (e) {
        document.getElementById('translate-status').innerHTML = '<span class="text-rose-500 text-xs">Failed</span>';
    }
}

function swapLanguages() {
    // Swap source & target
    const temp = currentSource;
    currentSource = currentTarget;
    currentTarget = temp;

    // Update labels
    if (currentSource === 'en') {
        document.getElementById('source-lang-label').innerText = 'English (ইংরেজি)';
        document.getElementById('target-lang-label').innerText = 'Bangla (বাংলা)';
        document.getElementById('source-input').placeholder = 'Type or paste English text here to translate...';
    } else {
        document.getElementById('source-lang-label').innerText = 'Bangla (বাংলা)';
        document.getElementById('target-lang-label').innerText = 'English (ইংরেজি)';
        document.getElementById('source-input').placeholder = 'অনুবাদ করার জন্য এখানে বাংলা লিখুন বা পেস্ট করুন...';
    }

    // Swap text values
    const sourceText = document.getElementById('source-input').value;
    const targetText = document.getElementById('target-output').innerText;

    if (targetText && !targetText.includes('অনুবাদ এখানে')) {
        document.getElementById('source-input').value = targetText;
        onSourceInput();
    }
}

function clearSourceText() {
    document.getElementById('source-input').value = '';
    onSourceInput();
}

async function pasteFromClipboard() {
    try {
        const clipText = await navigator.clipboard.readText();
        document.getElementById('source-input').value = clipText;
        onSourceInput();
    } catch (err) {
        document.getElementById('source-input').focus();
    }
}

function copyTranslation() {
    const text = document.getElementById('target-output').innerText;
    if (!text || text.includes('অনুবাদ এখানে')) return;

    navigator.clipboard.writeText(text).then(() => {
        const btnText = document.getElementById('copy-btn-text');
        btnText.innerText = 'Copied!';
        setTimeout(() => {
            btnText.innerText = 'Copy Translation';
        }, 1500);
    });
}

// ── BUSINESS ANALYTICS AI SCRIPT ───────────────────────────
async function analyzeData(action, resultId) {
    const resultDiv = document.getElementById(resultId);
    
    document.getElementById('sales-result').classList.add('hidden');
    document.getElementById('inventory-result').classList.add('hidden');
    document.getElementById('dealer-result').classList.add('hidden');
    
    resultDiv.classList.remove('hidden');
    resultDiv.innerHTML = '<div class="flex items-center gap-3 text-slate-600"><i class="fa-solid fa-circle-notch fa-spin text-xl"></i> Gathering database data...</div>';
    
    try {
        const dataRes = await fetch('<?= BASE_URL ?>/admin/ai-assistant/api', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: action })
        });
        
        const rawText = await dataRes.text();
        let dataJson;
        try {
            dataJson = JSON.parse(rawText);
        } catch (err) {
            throw new Error('Server response error: ' + rawText.replace(/<[^>]*>?/gm, '').substring(0, 150));
        }
        
        if (!dataJson.success) {
            resultDiv.innerHTML = '<div class="text-rose-600 font-semibold"><i class="fa-solid fa-triangle-exclamation"></i> Error: ' + (dataJson.error || 'Failed to fetch data') + '</div>';
            return;
        }

        resultDiv.innerHTML = '<div class="flex items-center gap-3 text-slate-600"><i class="fa-solid fa-circle-notch fa-spin text-xl"></i> Connecting to AI Model (qwen3.5:0.8b)...</div>';

        const sessionRes = await fetch('https://ai.happybangladesh.com/api/conversations', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title: 'Admin Analysis' })
        });
        
        if (!sessionRes.ok) {
            throw new Error('Failed to create AI session (HTTP ' + sessionRes.status + ')');
        }
        
        const session = await sessionRes.json();
        const convId = session.conversation?.id;
        if (!convId) {
            throw new Error('Invalid AI session created');
        }

        resultDiv.innerHTML = `
            <div class="flex flex-wrap items-center justify-between gap-2 mb-3 pb-3 border-b border-slate-200/60">
                <h3 class="font-bold text-lg flex items-center gap-2 text-slate-800">
                    <i class="fa-solid fa-robot text-blue-600"></i> AI বিশ্লেষণ ও পরামর্শ
                    <span id="${resultId}-badge" class="text-xs font-normal text-slate-400 ml-1 animate-pulse">তথ্য বিশ্লেষণ হচ্ছে...</span>
                </h3>
                <div id="${resultId}-toggle" class="hidden items-center gap-1 bg-white p-1 rounded-xl border border-slate-200 shadow-sm text-xs font-bold">
                    <button onclick="toggleLang('${resultId}', 'bn')" id="${resultId}-btn-bn" class="px-3 py-1 rounded-lg bg-emerald-600 text-white transition-all shadow-xs">বাংলা</button>
                    <button onclick="toggleLang('${resultId}', 'en')" id="${resultId}-btn-en" class="px-3 py-1 rounded-lg text-slate-600 hover:text-slate-900 transition-all">English</button>
                </div>
            </div>
            <div id="${resultId}-content" class="text-slate-800 leading-relaxed space-y-2 font-medium"></div>
        `;

        const contentEl = document.getElementById(`${resultId}-content`);
        const badgeEl = document.getElementById(`${resultId}-badge`);

        const response = await fetch(`https://ai.happybangladesh.com/api/conversations/${convId}/messages`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                content: dataJson.prompt,
                webSearch: false
            })
        });

        if (!response.ok) {
            throw new Error('AI stream failed with status ' + response.status);
        }

        const reader = response.body.getReader();
        const decoder = new TextDecoder();
        let fullText = '';

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;

            const chunk = decoder.decode(value, { stream: true });
            const lines = chunk.split('\n');

            for (const line of lines) {
                if (line.startsWith('data: ')) {
                    try {
                        const parsed = JSON.parse(line.slice(6));
                        if (parsed.token) {
                            fullText += parsed.token;
                            contentEl.innerHTML = formatMarkdown(fullText);
                        }
                    } catch (e) {}
                }
            }
        }

        // Automatic Translation to Bangla
        if (fullText.trim()) {
            badgeEl.className = 'text-xs font-semibold text-emerald-600 ml-1 flex items-center gap-1';
            badgeEl.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> বাংলায় অনুবাদ করা হচ্ছে...';

            try {
                const transRes = await fetch('<?= BASE_URL ?>/admin/ai-assistant/translate', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        text: fullText,
                        source: 'en',
                        target: 'bn'
                    })
                });
                const transJson = await transRes.json();

                if (transJson.success && transJson.translation) {
                    // Store both languages
                    contentEl.dataset.en = fullText;
                    contentEl.dataset.bn = transJson.translation;

                    // Display Bengali by default
                    contentEl.innerHTML = formatMarkdown(transJson.translation);

                    badgeEl.className = 'text-xs font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full ml-1';
                    badgeEl.innerHTML = '<i class="fa-solid fa-check"></i> বাংলা ফলাফল';

                    // Show toggle
                    const toggleEl = document.getElementById(`${resultId}-toggle`);
                    if (toggleEl) {
                        toggleEl.classList.remove('hidden');
                        toggleEl.classList.add('inline-flex');
                    }
                } else {
                    badgeEl.innerHTML = '<span class="text-xs text-slate-400">সম্পূর্ণ</span>';
                }
            } catch (err) {
                badgeEl.innerHTML = '<span class="text-xs text-slate-400">সম্পূর্ণ</span>';
            }
        } else {
            badgeEl.remove();
        }

    } catch (e) {
        resultDiv.innerHTML = '<div class="text-rose-600 font-semibold"><i class="fa-solid fa-triangle-exclamation"></i> Error: ' + e.message + '</div>';
    }
}

function toggleLang(resultId, lang) {
    const contentEl = document.getElementById(`${resultId}-content`);
    const btnBn = document.getElementById(`${resultId}-btn-bn`);
    const btnEn = document.getElementById(`${resultId}-btn-en`);

    if (lang === 'bn' && contentEl.dataset.bn) {
        contentEl.innerHTML = formatMarkdown(contentEl.dataset.bn);
        btnBn.className = 'px-3 py-1 rounded-lg bg-emerald-600 text-white transition-all shadow-xs';
        btnEn.className = 'px-3 py-1 rounded-lg text-slate-600 hover:text-slate-900 transition-all';
    } else if (lang === 'en' && contentEl.dataset.en) {
        contentEl.innerHTML = formatMarkdown(contentEl.dataset.en);
        btnEn.className = 'px-3 py-1 rounded-lg bg-blue-600 text-white transition-all shadow-xs';
        btnBn.className = 'px-3 py-1 rounded-lg text-slate-600 hover:text-slate-900 transition-all';
    }
}

function formatMarkdown(text) {
    return text
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/\n- (.*?)/g, '<div class="flex items-start gap-2 mt-1"><span class="text-blue-500 font-bold">•</span><span>$1</span></div>')
        .replace(/\n\d+\. (.*?)/g, '<div class="flex items-start gap-2 mt-1"><span class="text-blue-500 font-bold">#</span><span>$1</span></div>')
        .replace(/\n\n/g, '<div class="my-2"></div>')
        .replace(/\n/g, '<br>');
}
</script>
