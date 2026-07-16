<?php $pageTitle = 'Collection'; ?>
<div class="h-full flex flex-col bg-gray-50">
  
  <!-- Header -->
  <div class="bg-white rounded-b-3xl shadow-sm px-4 pt-6 pb-5 relative z-10">
    <div class="flex items-center gap-3 mb-4">
      <a href="<?= url('dsr/dashboard') ?>" class="w-8 h-8 flex items-center justify-center text-gray-500 active:text-brand transition">
        <i class="fa-solid fa-arrow-left"></i>
      </a>
      <h1 class="text-xl font-bold text-gray-800">Dispatch Collection</h1>
    </div>
    
    <div class="bg-blue-50 rounded-2xl p-4 border border-blue-100 flex items-center gap-4">
      <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center text-blue-600">
        <i class="fa-solid fa-truck-ramp-box text-xl"></i>
      </div>
      <div class="flex-1">
        <div class="text-xs text-blue-500 font-bold tracking-wide uppercase mb-1 flex items-center gap-2">
          Date: <input type="date" value="<?= htmlspecialchars($date) ?>" class="bg-transparent outline-none border-b border-blue-200 cursor-pointer text-blue-600" onchange="window.location.href='?date='+this.value">
        </div>
        <div class="text-gray-800 font-semibold text-sm">Collect from Warehouse</div>
      </div>
    </div>
  </div>

  <?php if(empty($items)): ?>
    <div class="flex-1 flex flex-col items-center justify-center p-6 text-center">
      <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center text-gray-400 mb-4">
        <i class="fa-solid fa-box-open text-3xl"></i>
      </div>
      <h2 class="text-lg font-bold text-gray-800 mb-1">No Collections</h2>
      <p class="text-sm text-gray-500">There are no pending dispatches assigned to you on this date.</p>
    </div>
  <?php elseif(!empty($isCompleted)): ?>
    <div class="flex-1 flex flex-col items-center justify-center p-6 text-center">
      <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center text-green-500 mb-4">
        <i class="fa-solid fa-check-circle text-4xl"></i>
      </div>
      <h2 class="text-lg font-bold text-gray-800 mb-1">Collection Completed</h2>
      <p class="text-sm text-gray-500 mb-6">You have already checked and collected the items for this date.</p>
      <a href="<?= url('dsr/delivery') ?>" class="px-6 py-3 bg-brand text-white font-bold rounded-xl shadow-lg shadow-blue-500/30">
        Go to Delivery Map
      </a>
    </div>
  <?php else: ?>
    
    <!-- Progress Indicator -->
    <div class="px-4 py-4">
      <div class="flex items-center justify-between mb-2">
        <span class="text-sm font-bold text-gray-700">Collection Progress</span>
        <span class="text-xs font-semibold text-brand bg-blue-50 px-2 py-1 rounded-lg" id="progressText">0 / <?= count($items) ?> Products</span>
      </div>
      <div class="h-2 w-full bg-gray-200 rounded-full overflow-hidden">
        <div id="progressBar" class="h-full bg-brand transition-all duration-300 w-0"></div>
      </div>
    </div>

    <!-- Product List -->
    <div class="flex-1 overflow-y-auto px-4 pb-28">
      <div class="space-y-3">
        <?php foreach($items as $idx => $item): ?>
          <label class="flex items-center gap-3 p-3 bg-white rounded-2xl shadow-sm border border-gray-100 transition hover:shadow-md cursor-pointer collection-item">
            <input type="checkbox" class="collection-checkbox w-6 h-6 text-brand bg-gray-100 border-gray-300 rounded-md focus:ring-brand focus:ring-2 ml-1" onchange="updateProgress()">
            
            <div class="w-14 h-14 bg-gray-50 rounded-xl overflow-hidden flex items-center justify-center flex-shrink-0">
              <?php if($item['image']): ?>
                <img src="<?= url($item['image']) ?>" class="w-full h-full object-cover">
              <?php else: ?>
                <i class="fa-solid fa-image text-gray-300 text-xl"></i>
              <?php endif; ?>
            </div>
            
            <div class="flex-1">
              <div class="font-semibold text-sm text-gray-800 line-clamp-2 leading-tight mb-1"><?= h($item['name']) ?></div>
              <div class="text-xs text-brand font-bold bg-blue-50 inline-block px-2 py-0.5 rounded"><?= $item['total_qty'] ?> Units</div>
            </div>
          </label>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Sticky Bottom Action -->
    <div class="fixed bottom-16 left-0 w-full p-4 bg-white border-t border-gray-100 z-30 pb-[calc(1rem+env(safe-area-inset-bottom))]">
      <button id="completeBtn" onclick="completeCollection()" disabled class="w-full py-4 rounded-2xl font-bold text-white shadow-lg transition-all duration-300 bg-gray-300 text-gray-500 cursor-not-allowed">
        Complete Collection
      </button>
    </div>
    
  <?php endif; ?>
</div>

<script>
const totalItems = <?= count($items ?? []) ?>;

function updateProgress() {
  if(totalItems === 0) return;
  const checkboxes = document.querySelectorAll('.collection-checkbox');
  const checked = document.querySelectorAll('.collection-checkbox:checked').length;
  
  const percent = Math.round((checked / totalItems) * 100);
  
  document.getElementById('progressBar').style.width = percent + '%';
  document.getElementById('progressText').innerText = `${checked} / ${totalItems} Products`;
  
  const btn = document.getElementById('completeBtn');
  if(checked === totalItems) {
    btn.disabled = false;
    btn.classList.remove('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
    btn.classList.add('bg-brand', 'shadow-[0_8px_20px_rgba(37,99,235,0.3)]', 'active:scale-[0.98]');
  } else {
    btn.disabled = true;
    btn.classList.add('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
    btn.classList.remove('bg-brand', 'shadow-[0_8px_20px_rgba(37,99,235,0.3)]', 'active:scale-[0.98]');
  }
}

function completeCollection() {
  fetch('<?= url("dsr/collection/complete") ?>', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'csrf_token=<?= Helpers::csrfToken() ?>&date=<?= $date ?>'
  })
  .then(res => res.json())
  .then(data => {
    if(data.success) {
      window.location.href = '<?= url("dsr/delivery") ?>?date=<?= $date ?>';
    }
  });
}
</script>
