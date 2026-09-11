<?php $pageTitle = 'উপস্থিতি স্ক্যানার'; ?>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap');
  .font-siliguri { font-family: 'Hind Siliguri', 'Inter', sans-serif; }

  #qr-reader { border: none !important; border-radius: 1rem; overflow: hidden; }
  #qr-reader video { border-radius: 1rem; }
  #qr-reader__scan_region { border-radius: 1rem; }
  #qr-reader__dashboard_section_swaplink,
  #qr-reader__dashboard_section_csr span { display: none; }
  #qr-reader__header_message { display: none !important; }
  #qr-reader__camera_permission_button {
    background: #7c3aed !important; color: white !important; border: none !important;
    padding: 10px 24px !important; border-radius: 8px !important;
    font-family: 'Hind Siliguri', sans-serif !important; cursor: pointer !important;
  }

  @keyframes scanLine {
    0%   { top: 0; }
    50%  { top: calc(100% - 3px); }
    100% { top: 0; }
  }
  .scan-line {
    position: absolute; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, transparent, #8b5cf6, transparent);
    animation: scanLine 2s ease-in-out infinite; border-radius: 2px;
  }

  @keyframes pulseRing {
    0%   { transform: scale(1); opacity: .6; }
    100% { transform: scale(1.7); opacity: 0; }
  }
  .pulse-ring { position: relative; display: inline-block; }
  .pulse-ring::after {
    content: ''; position: absolute; inset: -6px;
    border-radius: 50%; border: 2px solid currentColor;
    animation: pulseRing 1.5s ease-out infinite;
  }

  @keyframes slideUp {
    from { transform: translateY(20px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
  }
  .slide-up { animation: slideUp .4s ease forwards; }
</style>

<div class="p-3 sm:p-5 space-y-4 pb-28 max-w-xl mx-auto font-siliguri text-slate-900">

  <!-- Header -->
  <div class="bg-white px-4 py-3 rounded-2xl border border-slate-200 shadow-2xs flex items-center gap-3">
    <a href="<?= url('dsr/dashboard') ?>" class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition">
      <i class="fa-solid fa-arrow-left text-sm"></i>
    </a>
    <div>
      <div class="text-[10px] font-black text-purple-600 uppercase tracking-widest">QR স্ক্যানার</div>
      <h1 class="text-base font-black text-slate-900 leading-tight">উপস্থিতি রেজিস্ট্রেশন</h1>
    </div>
    <div class="ml-auto text-right">
      <div class="text-[10px] text-slate-400 font-medium"><?= date('d M Y') ?></div>
      <div id="live-clock" class="text-sm font-black text-slate-700 font-mono"></div>
    </div>
  </div>

  <!-- Time rule badges -->
  <div class="grid grid-cols-3 gap-2 text-center text-[10.5px] font-bold">
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl py-2.5 px-2">
      <i class="fa-solid fa-circle-check mb-1 block text-base"></i>
      উপস্থিত
      <span class="block font-black text-[9px] mt-0.5 text-emerald-600">৮টার আগে</span>
    </div>
    <div class="bg-amber-50 border border-amber-200 text-amber-700 rounded-xl py-2.5 px-2">
      <i class="fa-solid fa-clock mb-1 block text-base"></i>
      লেট
      <span class="block font-black text-[9px] mt-0.5 text-amber-600">৮টা – ৯টা</span>
    </div>
    <div class="bg-rose-50 border border-rose-200 text-rose-700 rounded-xl py-2.5 px-2">
      <i class="fa-solid fa-circle-xmark mb-1 block text-base"></i>
      অনুপস্থিত
      <span class="block font-black text-[9px] mt-0.5 text-rose-600">৯টার পরে</span>
    </div>
  </div>

  <?php if ($todayAttendance): ?>
    <!-- Already marked today — show result card -->
    <?php
      $st = $todayAttendance['status'];
      $cfgMap = [
        'present' => ['bg-emerald-50','border-emerald-300','text-emerald-700','text-emerald-600','fa-circle-check','উপস্থিত'],
        'late'    => ['bg-amber-50','border-amber-300','text-amber-700','text-amber-600','fa-clock','লেট'],
        'absent'  => ['bg-rose-50','border-rose-300','text-rose-700','text-rose-600','fa-circle-xmark','অনুপস্থিত'],
      ];
      [$cbg,$cborder,$ctext,$csub,$cicon,$clabel] = $cfgMap[$st] ?? $cfgMap['absent'];
      $scanTime = date('h:i:s A', strtotime($todayAttendance['scan_time']));
    ?>
    <div class="<?= $cbg ?> border <?= $cborder ?> rounded-2xl p-5 text-center slide-up">
      <div class="<?= $ctext ?> text-5xl mb-3 pulse-ring"><i class="fa-solid <?= $cicon ?>"></i></div>
      <h2 class="text-2xl font-black <?= $ctext ?> mt-4"><?= $clabel ?></h2>
      <p class="text-sm <?= $csub ?> mt-1 font-medium">আজকের উপস্থিতি নেওয়া হয়েছে</p>

      <div class="mt-4 pt-4 border-t <?= $cborder ?> grid grid-cols-2 gap-3 text-left text-xs">
        <div>
          <span class="text-slate-400 font-black block uppercase tracking-wide text-[9px]">স্ক্যান সময়</span>
          <span class="font-black text-slate-900 text-sm"><?= $scanTime ?></span>
        </div>
        <div>
          <span class="text-slate-400 font-black block uppercase tracking-wide text-[9px]">তারিখ</span>
          <span class="font-black text-slate-900 text-sm"><?= date('d/m/Y') ?></span>
        </div>
        <?php if ($todayAttendance['address']): ?>
        <div class="col-span-2">
          <span class="text-slate-400 font-black block uppercase tracking-wide text-[9px]">লোকেশন</span>
          <span class="font-medium text-slate-700 mt-0.5 block"><?= h($todayAttendance['address']) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($todayAttendance['latitude'] && $todayAttendance['longitude']): ?>
        <div class="col-span-2">
          <a href="https://www.google.com/maps?q=<?= $todayAttendance['latitude'] ?>,<?= $todayAttendance['longitude'] ?>"
             target="_blank" class="inline-flex items-center gap-1.5 font-bold text-blue-600 hover:underline">
            <i class="fa-solid fa-map-pin"></i> Google Maps-এ দেখুন
          </a>
        </div>
        <?php endif; ?>
      </div>

      <a href="<?= url('dsr/dashboard') ?>" class="mt-4 flex items-center justify-center gap-2 py-2.5 bg-white border <?= $cborder ?> rounded-xl text-sm font-black <?= $ctext ?> hover:<?= $cbg ?> transition active:scale-95">
        <i class="fa-solid fa-house"></i> ড্যাশবোর্ডে ফিরুন
      </a>
    </div>

  <?php else: ?>
    <!-- Scanner UI -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xs overflow-hidden">
      <!-- Camera viewport -->
      <div class="relative bg-slate-950 p-4">
        <div class="relative rounded-2xl overflow-hidden mx-auto" style="aspect-ratio:1/1; max-height:320px;">
          <div id="qr-reader" style="width:100%;height:100%;"></div>
          <!-- Corner brackets overlay -->
          <div class="absolute inset-0 pointer-events-none z-10">
            <div class="absolute top-3 left-3 w-7 h-7 border-t-[3px] border-l-[3px] border-purple-400 rounded-tl-lg"></div>
            <div class="absolute top-3 right-3 w-7 h-7 border-t-[3px] border-r-[3px] border-purple-400 rounded-tr-lg"></div>
            <div class="absolute bottom-3 left-3 w-7 h-7 border-b-[3px] border-l-[3px] border-purple-400 rounded-bl-lg"></div>
            <div class="absolute bottom-3 right-3 w-7 h-7 border-b-[3px] border-r-[3px] border-purple-400 rounded-br-lg"></div>
            <div class="scan-line"></div>
          </div>
        </div>
        <p class="text-center text-slate-400 text-[11px] font-medium mt-3">
          <i class="fa-solid fa-qrcode mr-1.5"></i>QR কোড ফ্রেমের ভেতরে ধরুন
        </p>
      </div>

      <!-- Result / status area -->
      <div id="scan-result" class="p-4 min-h-[80px] flex items-center justify-center border-t border-slate-100">
        <div class="text-center text-slate-400 text-xs font-medium">
          <i class="fa-solid fa-camera text-2xl mb-2 block text-slate-300"></i>
          ক্যামেরা চালু হলে QR কোড স্ক্যান করুন
        </div>
      </div>
    </div>

    <!-- Info note -->
    <div class="bg-purple-50 border border-purple-200 rounded-xl px-4 py-3 flex items-start gap-3">
      <i class="fa-solid fa-location-dot text-purple-500 mt-0.5 shrink-0"></i>
      <p class="text-xs text-purple-700 font-medium">
        স্ক্যান করার সময় আপনার <strong>লোকেশন</strong> ও <strong>সময়</strong> স্বয়ংক্রিয়ভাবে সেভ হবে। Location permission অনুমতি দিন।
      </p>
    </div>

  <?php endif; ?>

</div>

<!-- html5-qrcode CDN -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
// Live clock
(function() {
  const el = document.getElementById('live-clock');
  if (!el) return;
  function update() {
    const now = new Date();
    const h = String(now.getHours()).padStart(2,'0');
    const m = String(now.getMinutes()).padStart(2,'0');
    const s = String(now.getSeconds()).padStart(2,'0');
    el.textContent = h + ':' + m + ':' + s;
  }
  update();
  setInterval(update, 1000);
})();

<?php if (!$todayAttendance): ?>
let scanning  = true;
let submitted = false;

const html5QrCode = new Html5Qrcode("qr-reader");

html5QrCode.start(
  { facingMode: "environment" },
  { fps: 10, qrbox: { width: 200, height: 200 }, aspectRatio: 1.0 },
  onScanSuccess,
  () => {}
).catch(() => {
  document.getElementById('scan-result').innerHTML = `
    <div class="text-center text-rose-500 text-xs font-medium p-4">
      <i class="fa-solid fa-triangle-exclamation text-2xl mb-2 block"></i>
      ক্যামেরা চালু করতে পারিনি। ব্রাউজার পারমিশন দিন এবং রিফ্রেশ করুন।
    </div>`;
});

function onScanSuccess(decodedText) {
  if (!scanning || submitted) return;
  scanning  = false;
  submitted = true;

  html5QrCode.stop().catch(() => {});
  showProcessing();

  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      pos => submitAttendance(decodedText, pos.coords.latitude, pos.coords.longitude),
      ()  => submitAttendance(decodedText, null, null),
      { timeout: 8000, maximumAge: 0 }
    );
  } else {
    submitAttendance(decodedText, null, null);
  }
}

function showProcessing() {
  document.getElementById('scan-result').innerHTML = `
    <div class="text-center py-6">
      <div class="w-10 h-10 border-4 border-purple-200 border-t-purple-600 rounded-full animate-spin mx-auto mb-3"></div>
      <p class="text-sm font-bold text-slate-600">লোকেশন যাচাই করা হচ্ছে...</p>
    </div>`;
}

function submitAttendance(qrContent, lat, lng) {
  let addressPromise = Promise.resolve('');
  if (lat && lng) {
    addressPromise = fetch(
      `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`,
      { headers: { 'Accept-Language': 'bn' } }
    ).then(r => r.json()).then(d => d.display_name || '').catch(() => '');
  }

  addressPromise.then(address => {
    const fd = new FormData();
    fd.append('qr_content',  qrContent);
    fd.append('latitude',    lat  ?? '');
    fd.append('longitude',   lng  ?? '');
    fd.append('address',     address);
    fd.append('device_info', navigator.userAgent);

    fetch('<?= url('dsr/qr-code/mark') ?>', { method: 'POST', body: fd })
      .then(r => r.json())
      .then(data => showResult(data, address, lat, lng))
      .catch(() => showError('নেটওয়ার্ক সমস্যা। পেজ রিফ্রেশ করে আবার চেষ্টা করুন।'));
  });
}

function showResult(data, address, lat, lng) {
  const el = document.getElementById('scan-result');

  if (data.already_marked) {
    const cfg = statusCfg(data.status);
    el.innerHTML = `
      <div class="w-full text-center slide-up py-4">
        <div class="${cfg.text} text-4xl mb-2 pulse-ring"><i class="fa-solid ${cfg.icon}"></i></div>
        <h3 class="font-black text-xl ${cfg.text} mt-3">${cfg.label}</h3>
        <p class="text-xs text-slate-500 mt-1 font-medium">আজকে ইতোমধ্যে উপস্থিতি নেওয়া হয়েছে</p>
        <p class="text-xs font-black text-slate-700 mt-1.5">সময়: ${data.scan_time}</p>
        <a href="<?= url('dsr/dashboard') ?>" class="inline-flex items-center gap-2 mt-4 px-5 py-2 bg-slate-100 rounded-xl text-xs font-black text-slate-700 hover:bg-slate-200 transition">
          <i class="fa-solid fa-house"></i> ড্যাশবোর্ড
        </a>
      </div>`;
    return;
  }

  if (!data.success) {
    el.innerHTML = `
      <div class="w-full text-center slide-up py-4">
        <div class="text-rose-500 text-3xl mb-2"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <h3 class="font-black text-base text-rose-600">ব্যর্থ হয়েছে!</h3>
        <p class="text-xs text-slate-500 mt-1">${data.message || 'অজানা সমস্যা হয়েছে'}</p>
        <button onclick="location.reload()" class="mt-3 px-4 py-1.5 bg-slate-100 rounded-xl text-xs font-bold text-slate-700">আবার চেষ্টা করুন</button>
      </div>`;
    return;
  }

  const cfg = statusCfg(data.status);
  const [hh, mm] = data.scan_time.split(':');
  const timeStr = `${hh}:${mm}`;

  el.innerHTML = `
    <div class="w-full slide-up p-2">
      <div class="${cfg.bg} border ${cfg.border} rounded-xl p-5 text-center">
        <div class="${cfg.text} text-5xl mb-2 pulse-ring"><i class="fa-solid ${cfg.icon}"></i></div>
        <h3 class="font-black text-2xl ${cfg.text} mt-4">${cfg.label}!</h3>
        <p class="text-sm ${cfg.sub} font-medium mt-1">উপস্থিতি সফলভাবে সেভ হয়েছে</p>
        <div class="mt-4 pt-4 border-t ${cfg.border} grid grid-cols-2 gap-3 text-left text-xs">
          <div><span class="text-slate-400 font-black block text-[9px] uppercase tracking-wide">সময়</span>
               <span class="font-black text-slate-900 text-sm">${timeStr}</span></div>
          <div><span class="text-slate-400 font-black block text-[9px] uppercase tracking-wide">তারিখ</span>
               <span class="font-black text-slate-900 text-sm">${data.date}</span></div>
          ${address ? `<div class="col-span-2"><span class="text-slate-400 font-black block text-[9px] uppercase tracking-wide">লোকেশন</span>
               <span class="text-slate-700 font-medium mt-0.5 block">${address.substring(0,120)}</span></div>` : ''}
          ${lat ? `<div class="col-span-2"><a href="https://www.google.com/maps?q=${lat},${lng}" target="_blank" class="inline-flex items-center gap-1.5 font-bold text-blue-600 hover:underline text-xs"><i class="fa-solid fa-map-pin"></i> Google Maps-এ দেখুন</a></div>` : ''}
        </div>
        <a href="<?= url('dsr/dashboard') ?>" class="mt-4 flex items-center justify-center gap-2 py-2.5 ${cfg.btnBg} text-white rounded-xl text-sm font-black transition active:scale-95">
          <i class="fa-solid fa-house"></i> ড্যাশবোর্ডে ফিরুন
        </a>
      </div>
    </div>`;
}

function showError(msg) {
  document.getElementById('scan-result').innerHTML = `
    <div class="text-center p-4">
      <div class="text-rose-400 text-2xl mb-2"><i class="fa-solid fa-wifi-slash"></i></div>
      <p class="text-xs font-bold text-rose-600">${msg}</p>
      <button onclick="location.reload()" class="mt-2 px-4 py-1.5 bg-slate-100 rounded-xl text-xs font-bold">রিফ্রেশ করুন</button>
    </div>`;
}

function statusCfg(status) {
  const m = {
    present: { bg:'bg-emerald-50', border:'border-emerald-300', text:'text-emerald-600', sub:'text-emerald-500', icon:'fa-circle-check', label:'উপস্থিত', btnBg:'bg-emerald-600 hover:bg-emerald-700' },
    late:    { bg:'bg-amber-50',   border:'border-amber-300',   text:'text-amber-600',   sub:'text-amber-500',   icon:'fa-clock',        label:'লেট',       btnBg:'bg-amber-600 hover:bg-amber-700' },
    absent:  { bg:'bg-rose-50',    border:'border-rose-300',    text:'text-rose-600',    sub:'text-rose-500',    icon:'fa-circle-xmark', label:'অনুপস্থিত', btnBg:'bg-rose-600 hover:bg-rose-700' },
  };
  return m[status] || m.absent;
}
<?php endif; ?>
</script>
