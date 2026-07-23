<?php $pageTitle = 'Profile'; ?>
<div style="background:#2563eb;padding:44px 20px 60px;position:relative;overflow:hidden;">
  <div style="position:absolute;width:180px;height:180px;border-radius:50%;background:rgba(255,255,255,.06);top:-50px;right:-50px;"></div>
  <div style="font-size:0.75rem;color:rgba(255,255,255,0.7);margin-bottom:4px;"><i class="fa-solid fa-house" style="margin-right:4px;"></i>হোম › প্রোফাইল</div>
  <div style="display:flex;align-items:center;gap:16px;margin-top:8px;position:relative;z-index:2;">
    <div style="width:72px;height:72px;border-radius:50%;background:rgba(255,255,255,0.2);border:3px solid rgba(255,255,255,0.4);display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:900;color:#fff;">
      <?= Helpers::initials(Auth::name()) ?>
    </div>
    <div>
      <div style="font-size:1.3rem;font-weight:900;color:#fff;"><?= h(Auth::name()) ?></div>
      <div style="font-size:0.8rem;color:rgba(255,255,255,0.8);"><?= h(Auth::email()) ?></div>
      <div style="display:inline-flex;align-items:center;gap:4px;background:rgba(255,255,255,0.15);color:#fff;padding:3px 10px;border-radius:999px;font-size:0.7rem;font-weight:700;margin-top:4px;">
        <i class="fa-solid fa-user-tie"></i> সেলস রিপ্রেজেন্টেটিভ (SR)
      </div>
    </div>
  </div>
</div>

<div style="padding:16px;">
  <!-- Account Info Card -->
  <div style="background:#fff;border-radius:14px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,.06);text-align:center;margin-bottom:16px;border:1px solid #f1f5f9;">
    <div style="font-size:3rem;margin-bottom:10px;">👤</div>
    <div style="font-size:1rem;font-weight:800;color:#0f172a;margin-bottom:6px;">আমার প্রোফাইল ও একাউন্ট</div>
    <div style="font-size:0.85rem;color:#64748b;line-height:1.6;">পাসওয়ার্ড পরিবর্তন ও একাউন্ট সেটিংস আপডেট করুন।</div>
  </div>

  <a href="<?= url('sr/logout') ?>"
     style="display:flex;align-items:center;justify-content:center;gap:8px;background:#fff;border:1.5px solid #fee2e2;color:#ef4444;border-radius:14px;padding:14px;font-weight:800;font-size:0.9rem;text-decoration:none;box-shadow:0 1px 4px rgba(0,0,0,0.04);">
    <i class="fa-solid fa-right-from-bracket"></i> লগআউট করুন
  </a>
</div>
<div style="height:20px;"></div>
