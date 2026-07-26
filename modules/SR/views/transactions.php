<?php $pageTitle = 'Transactions'; ?>

<div class="p-4 sm:p-5 space-y-4 pb-28 max-w-md mx-auto font-sans">

  <!-- Header -->
  <div class="flex items-center gap-3 bg-white p-3.5 rounded-2xl border border-slate-200/90 shadow-2xs">
    <a href="<?= url('sr/dashboard') ?>" class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-700 hover:bg-slate-200 transition">
      <i class="fa-solid fa-arrow-left text-sm"></i>
    </a>
    <div>
      <h1 class="text-base font-black text-slate-900 leading-tight">ট্রানজেকশন (Transactions)</h1>
      <p class="text-[11px] text-slate-500 font-medium">পণ্যের ডেলিভারি ও রিটার্ন সামারি</p>
    </div>
  </div>

  <!-- Date Filter Form -->
  <form method="GET" action="<?= url('sr/transactions') ?>" class="bg-white p-3 rounded-2xl border border-slate-200/90 shadow-2xs flex items-center gap-2">
    <div class="flex-1 min-w-0">
      <label class="block text-[10px] font-bold text-slate-400 mb-0.5">তারিখ নির্বাচন করুন</label>
      <input type="date" name="date" value="<?= h($date) ?>" required
             class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1.5 text-xs font-bold text-slate-800 outline-none focus:border-blue-500">
    </div>
    <button type="submit" class="self-end px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-2xs active:scale-95 transition">
      দেখুন
    </button>
  </form>

  <!-- Products List -->
  <div class="space-y-3">
    <?php if (empty($transactions)): ?>
      <div class="bg-white p-5 rounded-2xl border border-slate-200 text-center">
        <div class="text-slate-400 mb-2"><i class="fa-solid fa-box-open text-3xl"></i></div>
        <p class="text-sm font-bold text-slate-600">কোনো তথ্য পাওয়া যায়নি।</p>
      </div>
    <?php else: ?>
      <?php foreach ($transactions as $t): ?>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-3">
          <div class="flex justify-between items-start border-b border-slate-100 pb-2">
            <h3 class="text-sm font-black text-slate-800"><?= h($t['product_name']) ?></h3>
            <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-lg border border-blue-100">৳<?= number_format($t['ordered_price'], 2) ?> / পিস</span>
          </div>
          
          <div class="grid grid-cols-2 gap-2 text-xs">
            <!-- Ordered -->
            <div class="bg-slate-50 rounded-xl p-2 border border-slate-100 flex flex-col justify-between">
              <span class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">Ordered</span>
              <div class="flex justify-between items-end mt-1">
                <span class="font-bold text-slate-700"><?= $t['ordered_qty'] ?> pcs</span>
                <span class="font-black text-slate-800">৳<?= number_format($t['ordered_val'], 2) ?></span>
              </div>
            </div>
            
            <!-- Dispatched -->
            <div class="bg-amber-50 rounded-xl p-2 border border-amber-100 flex flex-col justify-between">
              <span class="text-[10px] font-extrabold text-amber-600 uppercase tracking-wider mb-1">Dispatched</span>
              <div class="flex justify-between items-end mt-1">
                <span class="font-bold text-amber-700"><?= $t['dispatched_qty'] ?> pcs</span>
                <span class="font-black text-amber-900">৳<?= number_format($t['dispatched_val'], 2) ?></span>
              </div>
            </div>

            <!-- Sell (Delivered) -->
            <div class="bg-emerald-50 rounded-xl p-2 border border-emerald-100 flex flex-col justify-between">
              <span class="text-[10px] font-extrabold text-emerald-600 uppercase tracking-wider mb-1">Sell Quantity</span>
              <div class="flex justify-between items-end mt-1">
                <span class="font-bold text-emerald-700"><?= $t['sell_qty'] ?> pcs</span>
                <span class="font-black text-emerald-900">৳<?= number_format($t['sell_val'], 2) ?></span>
              </div>
            </div>

            <!-- Return -->
            <div class="bg-rose-50 rounded-xl p-2 border border-rose-100 flex flex-col justify-between">
              <span class="text-[10px] font-extrabold text-rose-600 uppercase tracking-wider mb-1">Return</span>
              <div class="flex justify-between items-end mt-1">
                <span class="font-bold text-rose-700"><?= $t['return_qty'] ?> pcs</span>
                <span class="font-black text-rose-900">৳<?= number_format($t['return_val'], 2) ?></span>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</div>
