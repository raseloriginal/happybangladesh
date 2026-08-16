<?php $pageTitle = 'ড্যাশবোর্ড'; ?>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap');
  .font-siliguri {
    font-family: 'Hind Siliguri', 'Inter', sans-serif;
  }
</style>

<div class="p-3 sm:p-5 space-y-5 pb-28 max-w-5xl mx-auto font-siliguri text-slate-800">

  <!-- 1. Top Header Profile Bar -->
  <div class="bg-white/95 backdrop-blur-md px-4 py-3 sm:px-5 sm:py-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between gap-3 transition-all">
    <div class="flex items-center gap-3">
      <div class="relative shrink-0 select-none">
        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-slate-50 border border-slate-200 flex items-center justify-center font-black text-slate-700 text-sm sm:text-base shadow-inner">
          <?= Helpers::initials(Auth::name()) ?>
        </div>
        <span class="w-3 h-3 bg-emerald-500 border-2 border-white rounded-full absolute bottom-0 right-0 shadow-xs"></span>
      </div>
      <div>
        <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-50 text-[9px] font-black text-emerald-700 uppercase tracking-wide border border-emerald-100">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
          ফিল্ড একটিভ
        </div>
        <h2 class="text-base sm:text-lg font-black text-slate-900 leading-tight mt-1"><?= h(Auth::name()) ?></h2>
      </div>
    </div>

    <!-- SR Logout Button -->
    <a href="<?= url('sr/logout') ?>" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 text-xs font-bold shadow-3xs hover:bg-rose-100 transition duration-150 active:scale-95" title="লগআউট করুন">
      <i class="fa-solid fa-right-from-bracket text-xs"></i>
      <span>লগআউট</span>
    </a>
  </div>

  <!-- 2. Target & Delivery Metrics Grid Cards (Excel Style) -->
  <?php
    $target = $stats['target_amount'] > 0 ? $stats['target_amount'] : 0;
    $sales = $stats['this_month_sales'];
    $delivery = $stats['this_month_delivery'] ?? 0;
    $targetPct = $target > 0 ? min(100, round(($sales / $target) * 100)) : 0;
    $deliveryPct = $target > 0 ? min(100, round(($delivery / $target) * 100)) : ($sales > 0 ? min(100, round(($delivery / $sales) * 100)) : 0);
  ?>
  <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm font-mono text-xs">
    <table class="w-full text-left border-collapse">
      <thead>
        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold text-[10px] uppercase tracking-wider select-none">
          <th class="p-3 border-r border-slate-200 font-sans">মেট্রিক বিবরণ</th>
          <th class="p-3 text-right border-r border-slate-200 font-sans">পরিমাণ</th>
          <th class="p-3 text-center font-sans">অগ্রগতি</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-200 text-slate-800">
        <tr class="hover:bg-slate-50/50 transition">
          <td class="p-3 font-sans font-semibold border-r border-slate-200 text-slate-700 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-blue-500 shrink-0 shadow-2xs"></span>
            মাসিক লক্ষ্যমাত্রা
          </td>
          <td class="p-3 text-right font-black border-r border-slate-200 text-slate-900 text-sm">
            ৳<?= number_format($target) ?>
          </td>
          <td class="p-3 text-center font-sans">
            <div class="flex flex-col items-center gap-1">
              <span class="inline-block text-[10px] px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-bold border border-blue-100">
                <?= $targetPct ?>% অর্জিত
              </span>
              <div class="w-16 h-1 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-blue-500 rounded-full" style="width: <?= $targetPct ?>%"></div>
              </div>
            </div>
          </td>
        </tr>
        <tr class="hover:bg-slate-50/50 transition">
          <td class="p-3 font-sans font-semibold border-r border-slate-200 text-slate-700 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0 shadow-2xs"></span>
            ডেলিভারি সম্পন্ন
          </td>
          <td class="p-3 text-right font-black border-r border-slate-200 text-slate-900 text-sm">
            ৳<?= number_format($delivery) ?>
          </td>
          <td class="p-3 text-center font-sans">
            <div class="flex flex-col items-center gap-1">
              <span class="inline-block text-[10px] px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 font-bold border border-emerald-100">
                <?= $deliveryPct ?>% ডেলিভার্ড
              </span>
              <div class="w-16 h-1 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-emerald-500 rounded-full" style="width: <?= $deliveryPct ?>%"></div>
              </div>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- 3. Sales Menu (Excel Style Table) -->
  <div class="space-y-2">
    <div class="text-[10px] font-extrabold text-slate-400 tracking-wider uppercase px-1">সেলস মেনু</div>
    
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm font-sans text-xs">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold text-[10px] uppercase tracking-wider select-none">
            <th class="p-3 border-r border-slate-200">মেনু আইটেম</th>
            <th class="p-3 text-center">অ্যাকশন</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 text-slate-700">
          <!-- Item 1: Shop List -->
          <tr class="hover:bg-slate-50/50 transition cursor-pointer group" onclick="SRLoader.start('দোকান তালিকা লোড হচ্ছে...', 'অনুগ্রহ করে অপেক্ষা করুন'); window.location.href='<?= url('sr/retailers') ?>'">
            <td class="p-3 border-r border-slate-200 font-bold text-slate-800 flex items-center gap-2.5">
              <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center text-xs shrink-0 group-hover:scale-105 transition">
                <i class="fa-solid fa-store"></i>
              </div>
              <span>দোকান তালিকা</span>
            </td>
            <td class="p-3 text-center">
              <span class="inline-block text-[10px] px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 font-bold border border-blue-100 group-hover:bg-blue-600 group-hover:text-white transition duration-200 font-sans">
                প্রবেশ করুন
              </span>
            </td>
          </tr>

          <!-- Item 2: Order History -->
          <tr class="hover:bg-slate-50/50 transition cursor-pointer group" onclick="SRLoader.start('অর্ডার ইতিহাস লোড হচ্ছে...', 'অনুগ্রহ করে অপেক্ষা করুন'); window.location.href='<?= url('sr/orders') ?>'">
            <td class="p-3 border-r border-slate-200 font-bold text-slate-800 flex items-center gap-2.5">
              <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center text-xs shrink-0 group-hover:scale-105 transition">
                <i class="fa-solid fa-file-invoice"></i>
              </div>
              <span>অর্ডার ইতিহাস</span>
            </td>
            <td class="p-3 text-center">
              <span class="inline-block text-[10px] px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 font-bold border border-amber-100 group-hover:bg-amber-500 group-hover:text-white transition duration-200 font-sans">
                প্রবেশ করুন
              </span>
            </td>
          </tr>

          <!-- Item 3: Transactions -->
          <tr class="hover:bg-slate-50/50 transition cursor-pointer group" onclick="SRLoader.start('সমারি লোড হচ্ছে...', 'অনুগ্রহ করে অপেক্ষা করুন'); window.location.href='<?= url('sr/transactions') ?>'">
            <td class="p-3 border-r border-slate-200 font-bold text-slate-800 flex items-center gap-2.5">
              <div class="w-7 h-7 rounded-lg bg-purple-50 text-purple-600 border border-purple-100 flex items-center justify-center text-xs shrink-0 group-hover:scale-105 transition">
                <i class="fa-solid fa-money-bill-transfer"></i>
              </div>
              <span>সমারি</span>
            </td>
            <td class="p-3 text-center">
              <span class="inline-block text-[10px] px-2.5 py-1 rounded-lg bg-purple-50 text-purple-700 font-bold border border-purple-100 group-hover:bg-purple-600 group-hover:text-white transition duration-200 font-sans">
                প্রবেশ করুন
              </span>
            </td>
          </tr>

          <!-- Item 4: Price Correction -->
          <tr class="hover:bg-slate-50/50 transition cursor-pointer group" onclick="SRLoader.start('মূল্য সংশোধন লোড হচ্ছে...', 'অনুগ্রহ করে অপেক্ষা করুন'); window.location.href='<?= url('sr/price-correction') ?>'">
            <td class="p-3 border-r border-slate-200 font-bold text-slate-800 flex items-center gap-2.5">
              <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center text-xs shrink-0 group-hover:scale-105 transition">
                <i class="fa-solid fa-tags"></i>
              </div>
              <span>মূল্য সংশোধন</span>
            </td>
            <td class="p-3 text-center">
              <span class="inline-block text-[10px] px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-bold border border-emerald-100 group-hover:bg-emerald-600 group-hover:text-white transition duration-200 font-sans">
                প্রবেশ করুন
              </span>
            </td>
          </tr>

        </tbody>
      </table>
    </div>
  </div>

  <!-- ══════════════════════════════════════════════════════
       4. ORDER CUTOFF CARD
  ═══════════════════════════════════════════════════════ -->
  <div class="space-y-2">
    <div class="text-[10px] font-extrabold text-slate-400 tracking-wider uppercase px-1">আজকের অর্ডার</div>

    <?php if ($orderCutoff): ?>
      <!-- LOCKED STATE -->
      <div class="rounded-2xl border border-gray-200 shadow-sm p-4" style="background-color: #f0fdf4; border-color: #bbf7d0;">
        <div class="flex items-center gap-3">
          <div class="w-11 h-11 rounded-xl flex items-center justify-center shadow-md shrink-0" style="background-color: #10b981;">
            <i class="fa-solid fa-lock text-white text-lg"></i>
          </div>
          <div class="flex-1 min-w-0">
            <div class="font-black text-sm" style="color: #065f46;">আজকের অর্ডার কাটা শেষ ✅</div>
            <div class="text-[11px] font-semibold mt-0.5" style="color: #047857;">
              <?php if (!empty($cutoffInfo['is_auto'])): ?>
                রাত ১২টায় স্বয়ংক্রিয়ভাবে বন্ধ হয়েছে
              <?php else: ?>
                <?= date('h:i A', strtotime($cutoffInfo['cutoff_at'] ?? 'now')) ?> তে শেষ করা হয়েছে
              <?php endif; ?>
            </div>
          </div>
          <div class="shrink-0">
            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-black rounded-lg" style="background-color: #d1fae5; color: #047857; border: 1px solid #a7f3d0;">
              <i class="fa-solid fa-shield-check text-[9px]"></i>
              লক
            </span>
          </div>
        </div>
        <div class="mt-3 pt-3 text-[11px] font-semibold flex items-center gap-1.5" style="border-top: 1px solid #bbf7d0; color: #047857;">
          <i class="fa-solid fa-circle-info" style="color: #10b981;"></i>
          রাত ১২টার পরে নতুন অর্ডার নেওয়া যাবে।
        </div>
      </div>
    <?php else: ?>
      <!-- ACTIVE STATE — can cut off -->
      <div class="rounded-2xl border shadow-sm p-4" style="background-color: #fffbeb; border-color: #fde68a;">
        <div class="flex items-center gap-3 mb-3">
          <div class="w-11 h-11 rounded-xl flex items-center justify-center shadow-md shrink-0" style="background-color: #f59e0b;">
            <i class="fa-solid fa-flag-checkered text-white text-lg"></i>
          </div>
          <div class="flex-1">
            <div class="font-black text-sm" style="color: #92400e;">অর্ডার কাটা চলছে</div>
            <div class="text-[11px] font-semibold mt-0.5" style="color: #b45309;">আজকের অর্ডার নেওয়া শেষ হলে নিচের বাটনটি চাপুন</div>
          </div>
        </div>
        <button id="cutoffBtn" onclick="openCutoffConfirmModal()"
          class="w-full py-3 rounded-xl text-white font-black text-sm shadow-md active:scale-[0.98] transition flex items-center justify-center gap-2"
          style="background-color: #f43f5e;">
          <i class="fa-solid fa-hand-fist"></i>
          আজকের মতো অর্ডার কাটা শেষ
        </button>
        <div class="mt-2 text-[10px] font-semibold text-center flex items-center justify-center gap-1" style="color: #b45309;">
          <i class="fa-solid fa-triangle-exclamation" style="color: #f59e0b;"></i>
          এই বাটন চাপলে আর নতুন অর্ডার নেওয়া যাবে না
        </div>
      </div>
    <?php endif; ?>
  </div>

</div>

<!-- ══════════════════════════════════════════════════════
     ORDER CUTOFF CONFIRM MODAL
═══════════════════════════════════════════════════════ -->
<div id="cutoffConfirmModal" class="fixed inset-0 z-[500] hidden items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
  <div id="cutoffConfirmContent" class="bg-white rounded-3xl p-6 w-full max-w-sm shadow-2xl transform scale-95 opacity-0 transition-all duration-200">
    <div class="text-center">
      <div class="w-20 h-20 bg-gradient-to-br from-rose-100 to-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i class="fa-solid fa-flag-checkered text-3xl text-rose-500"></i>
      </div>
      <h3 class="text-xl font-black text-slate-800 mb-1 font-siliguri">নিশ্চিত করুন</h3>
      <p class="text-sm text-slate-500 mb-1 font-siliguri">আজকের অর্ডার কাটা কি শেষ করতে চান?</p>
      <p class="text-xs text-rose-500 font-bold mb-6 font-siliguri">
        <i class="fa-solid fa-triangle-exclamation mr-1"></i>
        এরপর রাত ১২টার আগে আর নতুন অর্ডার নেওয়া যাবে না।
      </p>
      <!-- Buttons -->
      <div class="flex gap-3">
        <button onclick="closeCutoffConfirmModal()"
          class="flex-1 py-3 bg-slate-100 text-slate-600 font-bold rounded-xl active:bg-slate-200 transition font-siliguri">
          বাতিল
        </button>
        <button id="cutoffConfirmOkBtn" onclick="submitOrderCutoff()"
          class="flex-1 py-3 text-white font-black rounded-xl active:scale-[0.98] shadow-md transition font-siliguri"
          style="background-color: #f43f5e;">
          হ্যাঁ, শেষ করুন
        </button>
      </div>
    </div>
  </div>
</div>

<script>
let cutoffCountdownTimer = null;

function openCutoffConfirmModal() {
  const modal = document.getElementById('cutoffConfirmModal');
  const content = document.getElementById('cutoffConfirmContent');
  const confirmBtn = document.getElementById('cutoffConfirmOkBtn');
  
  modal.classList.remove('hidden');
  modal.classList.add('flex');
  setTimeout(() => {
    content.classList.remove('scale-95', 'opacity-0');
    content.classList.add('scale-100', 'opacity-100');
  }, 10);

  // Countdown initialization
  if (cutoffCountdownTimer) clearInterval(cutoffCountdownTimer);
  let seconds = 10;
  confirmBtn.disabled = true;
  confirmBtn.classList.add('opacity-50', 'cursor-not-allowed');
  confirmBtn.innerHTML = `হ্যাঁ, শেষ করুন (${seconds}s)`;

  cutoffCountdownTimer = setInterval(() => {
    seconds--;
    if (seconds > 0) {
      confirmBtn.innerHTML = `হ্যাঁ, শেষ করুন (${seconds}s)`;
    } else {
      clearInterval(cutoffCountdownTimer);
      cutoffCountdownTimer = null;
      confirmBtn.disabled = false;
      confirmBtn.classList.remove('opacity-50', 'cursor-not-allowed');
      confirmBtn.innerHTML = 'হ্যাঁ, শেষ করুন';
    }
  }, 1000);
}

function closeCutoffConfirmModal() {
  if (cutoffCountdownTimer) {
    clearInterval(cutoffCountdownTimer);
    cutoffCountdownTimer = null;
  }
  const modal = document.getElementById('cutoffConfirmModal');
  const content = document.getElementById('cutoffConfirmContent');
  content.classList.remove('scale-100', 'opacity-100');
  content.classList.add('scale-95', 'opacity-0');
  setTimeout(() => {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  }, 200);
}

async function submitOrderCutoff() {
  const btn = document.getElementById('cutoffConfirmOkBtn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> সেভ হচ্ছে...';

  try {
    const res = await fetch('<?= url('sr/api/order-cutoff') ?>', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: ''
    });
    const data = await res.json();

    if (data.success || data.already) {
      closeCutoffConfirmModal();
      setTimeout(() => window.location.reload(), 300);
    } else {
      alert(data.message || 'একটি ত্রুটি হয়েছে।');
      btn.disabled = false;
      btn.innerHTML = 'হ্যাঁ, শেষ করুন';
    }
  } catch (err) {
    alert('নেটওয়ার্ক ত্রুটি। আবার চেষ্টা করুন।');
    btn.disabled = false;
    btn.innerHTML = 'হ্যাঁ, শেষ করুন';
  }
}
</script>
