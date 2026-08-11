<?php $pageTitle = 'DSR উপস্থিতি'; ?>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
  #qr-canvas-container canvas { display: block; margin: 0 auto; }
  @keyframes fadeIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
  .fade-in { animation: fadeIn .35s ease forwards; }
  @keyframes spin { to { transform: rotate(360deg); } }
  .spin { animation: spin .8s linear infinite; }
  @media print {
    .no-print { display: none !important; }
    .print-only { display: block !important; }
    body { background: white !important; }
    .print-area { box-shadow: none !important; border: none !important; }
  }
  .print-only { display: none; }
</style>

<div class="page-header no-print">
  <div>
    <h1 class="page-title">DSR উপস্থিতি</h1>
    <div class="breadcrumb">Manager › Attendance</div>
  </div>
  <form method="GET" class="flex items-center gap-2">
    <input type="date" name="date" value="<?= h($date) ?>" class="form-input text-sm py-1.5 w-36">
    <button type="submit" class="btn btn-secondary btn-sm"><i class="fa-solid fa-filter"></i></button>
  </form>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

  <!-- ══════════ LEFT: QR Code Generator Card ══════════ -->
  <div class="xl:col-span-1">
    <div class="card print-area" id="qr-card">
      <div class="card-header no-print">
        <h2 class="card-title"><i class="fa-solid fa-qrcode mr-2 text-purple-600"></i>Mark Attendance QR Code</h2>
      </div>
      <div class="card-body text-center space-y-5">

        <!-- QR display area -->
        <div id="qr-status" class="text-sm text-slate-500 font-medium">লোড হচ্ছে...</div>

        <div id="qr-canvas-container" class="flex items-center justify-center min-h-[220px]">
          <div id="qr-loading" class="text-slate-400">
            <div class="w-8 h-8 border-4 border-slate-200 border-t-purple-600 rounded-full spin mx-auto mb-2"></div>
            <span class="text-xs">QR কোড লোড হচ্ছে...</span>
          </div>
        </div>

        <!-- Code text (small) -->
        <div id="qr-code-text" class="hidden">
          <div class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-[10px] font-mono text-slate-500 break-all" id="qr-code-display"></div>
        </div>

        <!-- Time rule reminder -->
        <div class="grid grid-cols-3 gap-1.5 text-[10px] font-bold no-print">
          <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg py-1.5">
            <i class="fa-solid fa-circle-check block mb-0.5"></i>Present<br><span class="text-[8.5px] font-black">৮টার আগে</span>
          </div>
          <div class="bg-amber-50 border border-amber-200 text-amber-700 rounded-lg py-1.5">
            <i class="fa-solid fa-clock block mb-0.5"></i>Late<br><span class="text-[8.5px] font-black">৮–৯টা</span>
          </div>
          <div class="bg-rose-50 border border-rose-200 text-rose-700 rounded-lg py-1.5">
            <i class="fa-solid fa-circle-xmark block mb-0.5"></i>Absent<br><span class="text-[8.5px] font-black">৯টার পরে</span>
          </div>
        </div>

        <!-- Action buttons -->
        <div class="flex flex-col gap-2 no-print">
          <button id="btn-generate" onclick="confirmGenerate()"
            class="btn btn-primary w-full gap-2">
            <i class="fa-solid fa-rotate-right"></i> নতুন QR কোড তৈরি করুন
          </button>
          <button onclick="printQr()"
            class="btn btn-secondary w-full gap-2">
            <i class="fa-solid fa-print"></i> প্রিন্ট করুন
          </button>
        </div>

      </div>

      <!-- Print-only layout -->
      <div class="print-only p-8 text-center">
        <h2 class="text-2xl font-black text-slate-900 mb-1">উপস্থিতি QR কোড</h2>
        <p class="text-sm text-slate-500 mb-6">Happy Bangladesh — DSR Attendance</p>
        <div id="qr-print-canvas" class="flex justify-center mb-6"></div>
        <div class="border-t pt-4 text-xs text-slate-400">
          এই QR কোড স্ক্যান করে DSR উপস্থিতি দিন।<br>
          ৮টার আগে = উপস্থিত &nbsp;|&nbsp; ৮–৯টা = লেট &nbsp;|&nbsp; ৯টার পরে = অনুপস্থিত
        </div>
      </div>
    </div>
  </div>

  <!-- ══════════ RIGHT: Attendance Table ══════════ -->
  <div class="xl:col-span-2 space-y-4 no-print">

    <!-- Stats row -->
    <?php
      $presentCount = count(array_filter($items, fn($i) => $i['status'] === 'present'));
      $lateCount    = count(array_filter($items, fn($i) => $i['status'] === 'late'));
      $absentCount  = count(array_filter($items, fn($i) => $i['status'] === 'absent'));
      $markedIds    = array_column($items, 'dsr_id');
      $notMarked    = array_filter($dsrs, fn($d) => !in_array($d['id'], $markedIds));
    ?>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
      <div class="card p-4 text-center">
        <div class="text-2xl font-black text-emerald-600"><?= $presentCount ?></div>
        <div class="text-xs font-bold text-slate-500 mt-1">উপস্থিত</div>
      </div>
      <div class="card p-4 text-center">
        <div class="text-2xl font-black text-amber-600"><?= $lateCount ?></div>
        <div class="text-xs font-bold text-slate-500 mt-1">লেট</div>
      </div>
      <div class="card p-4 text-center">
        <div class="text-2xl font-black text-rose-600"><?= $absentCount ?></div>
        <div class="text-xs font-bold text-slate-500 mt-1">অনুপস্থিত (মার্ক)</div>
      </div>
      <div class="card p-4 text-center">
        <div class="text-2xl font-black text-slate-500"><?= count($notMarked) ?></div>
        <div class="text-xs font-bold text-slate-500 mt-1">স্ক্যান করেনি</div>
      </div>
    </div>

    <!-- Scanned attendance table -->
    <div class="card">
      <div class="card-header">
        <h2 class="card-title"><i class="fa-solid fa-list-check mr-2"></i>
          <?= date('d M Y', strtotime($date)) ?> — DSR Attendance
        </h2>
      </div>
      <div class="overflow-x-auto">
        <table class="data-table">
          <thead>
            <tr>
              <th>#</th>
              <th>নাম</th>
              <th>স্ক্যান সময়</th>
              <th>স্ট্যাটাস</th>
              <th>লোকেশন</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $i => $a): ?>
            <tr class="fade-in">
              <td class="text-slate-400 text-xs"><?= $i + 1 ?></td>
              <td class="font-semibold"><?= h($a['user_name']) ?><br>
                <span class="text-xs text-slate-400"><?= h($a['phone'] ?? '') ?></span>
              </td>
              <td class="font-mono text-sm"><?= date('h:i:s A', strtotime($a['scan_time'])) ?></td>
              <td>
                <?php if ($a['status'] === 'present'): ?>
                  <span class="badge badge-success">উপস্থিত</span>
                <?php elseif ($a['status'] === 'late'): ?>
                  <span class="badge badge-warning">লেট</span>
                <?php else: ?>
                  <span class="badge badge-danger">অনুপস্থিত</span>
                <?php endif; ?>
              </td>
              <td class="text-xs text-slate-500 max-w-[180px] truncate">
                <?php if ($a['latitude'] && $a['longitude']): ?>
                  <a href="https://www.google.com/maps?q=<?= $a['latitude'] ?>,<?= $a['longitude'] ?>"
                     target="_blank" class="text-blue-600 hover:underline flex items-center gap-1">
                    <i class="fa-solid fa-map-pin text-[10px]"></i>
                    <?= $a['address'] ? mb_substr(h($a['address']), 0, 40) . '…' : 'Maps' ?>
                  </a>
                <?php else: ?>
                  <span class="text-slate-300">—</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($items)): ?>
            <tr><td colspan="5" class="text-center py-8 text-slate-400">
              <i class="fa-solid fa-inbox text-2xl block mb-2"></i>
              আজ কেউ এখনো QR স্ক্যান করেনি
            </td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Not-yet-scanned DSRs -->
    <?php if (!empty($notMarked)): ?>
    <div class="card border-amber-200">
      <div class="card-header">
        <h2 class="card-title text-amber-700"><i class="fa-solid fa-triangle-exclamation mr-2"></i>স্ক্যান করেনি (<?= count($notMarked) ?> জন)</h2>
      </div>
      <div class="p-4 flex flex-wrap gap-2">
        <?php foreach ($notMarked as $d): ?>
          <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold rounded-full">
            <i class="fa-solid fa-user text-[10px]"></i><?= h($d['name']) ?>
          </span>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

  </div>
</div>

<!-- Confirm modal -->
<div id="confirm-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center hidden">
  <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-4 text-center fade-in">
    <div class="w-14 h-14 bg-amber-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
      <i class="fa-solid fa-triangle-exclamation text-amber-500 text-2xl"></i>
    </div>
    <h3 class="text-lg font-black text-slate-900">QR কোড পরিবর্তন করবেন?</h3>
    <p class="text-sm text-slate-500 mt-2">পুরানো QR কোড আর কাজ করবে না। নতুন কোড প্রিন্ট করে অফিসে লাগাতে হবে।</p>
    <div class="flex gap-3 mt-6">
      <button onclick="closeModal()" class="btn btn-secondary flex-1">বাতিল</button>
      <button onclick="doGenerate()" class="btn btn-primary flex-1 bg-amber-500 hover:bg-amber-600 border-amber-500">হ্যাঁ, পরিবর্তন করুন</button>
    </div>
  </div>
</div>

<!-- QRCode.js library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
let currentQrCode = null;
let qrInstance    = null;
let hasExistingQr = false;

// ── Load QR on page load ────────────────────────────────
async function loadQr() {
  try {
    const res  = await fetch('<?= url('manager/api/attendance/qr') ?>');
    const data = await res.json();

    if (data.qr && data.qr.qr_code) {
      hasExistingQr  = true;
      currentQrCode  = data.qr.qr_code;
      renderQr(data.qr.qr_code);
      const created = new Date(data.qr.created_at).toLocaleString('bn-BD');
      document.getElementById('qr-status').textContent = 'সক্রিয় QR কোড — তৈরি: ' + created;
      document.getElementById('btn-generate').innerHTML = '<i class="fa-solid fa-rotate-right"></i> QR কোড পরিবর্তন করুন';
    } else {
      document.getElementById('qr-loading').innerHTML = `
        <div class="text-center py-6 text-slate-400">
          <i class="fa-solid fa-qrcode text-5xl block mb-3 opacity-30"></i>
          <p class="text-sm font-medium">কোনো QR কোড নেই</p>
          <p class="text-xs mt-1">নিচের বাটনে ক্লিক করে তৈরি করুন</p>
        </div>`;
      document.getElementById('qr-status').textContent = 'কোনো সক্রিয় QR কোড নেই';
    }
  } catch(e) {
    document.getElementById('qr-status').textContent = 'লোড করতে সমস্যা হয়েছে';
  }
}

function renderQr(code) {
  const container = document.getElementById('qr-canvas-container');
  container.innerHTML = '<div id="qr-inner"></div>';

  qrInstance = new QRCode(document.getElementById('qr-inner'), {
    text:          code,
    width:         240,
    height:        240,
    colorDark:     '#0f172a',
    colorLight:    '#ffffff',
    correctLevel:  QRCode.CorrectLevel.H
  });

  // Copy to print area
  setTimeout(() => {
    const printDiv  = document.getElementById('qr-print-canvas');
    printDiv.innerHTML = '';
    const newQr = new QRCode(printDiv, {
      text: code, width: 300, height: 300,
      colorDark: '#0f172a', colorLight: '#ffffff',
      correctLevel: QRCode.CorrectLevel.H
    });
    document.getElementById('qr-code-display').textContent = code;
    document.getElementById('qr-code-text').classList.remove('hidden');
  }, 300);
}

// ── Confirm before generating ───────────────────────────
function confirmGenerate() {
  if (hasExistingQr) {
    document.getElementById('confirm-modal').classList.remove('hidden');
  } else {
    doGenerate();
  }
}

function closeModal() {
  document.getElementById('confirm-modal').classList.add('hidden');
}

async function doGenerate() {
  closeModal();
  const btn = document.getElementById('btn-generate');
  btn.disabled = true;
  btn.innerHTML = '<span class="w-4 h-4 border-2 border-white border-t-transparent rounded-full spin inline-block mr-2"></span> তৈরি হচ্ছে...';

  try {
    const fd  = new FormData();
    const res = await fetch('<?= url('manager/api/attendance/qr/generate') ?>', { method: 'POST', body: fd });
    const data = await res.json();

    if (data.success) {
      hasExistingQr = true;
      currentQrCode = data.qr_code;
      renderQr(data.qr_code);
      document.getElementById('qr-status').innerHTML =
        '<span class="text-emerald-600 font-bold"><i class="fa-solid fa-circle-check mr-1"></i>নতুন QR কোড তৈরি হয়েছে!</span> এখনই প্রিন্ট করুন।';
      btn.innerHTML = '<i class="fa-solid fa-rotate-right"></i> QR কোড পরিবর্তন করুন';
    } else {
      alert('সমস্যা হয়েছে। আবার চেষ্টা করুন।');
      btn.innerHTML = '<i class="fa-solid fa-rotate-right"></i> নতুন QR কোড তৈরি করুন';
    }
  } catch(e) {
    alert('নেটওয়ার্ক সমস্যা।');
    btn.innerHTML = '<i class="fa-solid fa-rotate-right"></i> নতুন QR কোড তৈরি করুন';
  }
  btn.disabled = false;
}

function printQr() {
  if (!currentQrCode) { alert('আগে একটি QR কোড তৈরি করুন।'); return; }
  window.print();
}

// Close modal on backdrop click
document.getElementById('confirm-modal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});

// Init
loadQr();
</script>
