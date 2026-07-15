<?php $pageTitle = 'Profile'; ?>
<div style="background:linear-gradient(135deg,#4f46e5,#06b6d4);padding:44px 20px 60px;position:relative;overflow:hidden;">
  <div style="position:absolute;width:180px;height:180px;border-radius:50%;background:rgba(255,255,255,.06);top:-50px;right:-50px;"></div>
  <div style="font-size:0.75rem;color:rgba(255,255,255,0.6);margin-bottom:4px;"><i class="fa-solid fa-house" style="margin-right:4px;"></i>Home › Profile</div>
  <div style="display:flex;align-items:center;gap:16px;margin-top:8px;position:relative;z-index:2;">
    <div style="width:72px;height:72px;border-radius:50%;background:rgba(255,255,255,0.2);border:3px solid rgba(255,255,255,0.4);display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:800;color:#fff;">
      <?= Helpers::initials(Auth::name()) ?>
    </div>
    <div>
      <div style="font-size:1.3rem;font-weight:800;color:#fff;"><?= h(Auth::name()) ?></div>
      <div style="font-size:0.8rem;color:rgba(255,255,255,0.7);"><?= h(Auth::email()) ?></div>
      <div style="display:inline-flex;align-items:center;gap:4px;background:rgba(255,255,255,0.15);color:#fff;padding:3px 10px;border-radius:999px;font-size:0.7rem;font-weight:600;margin-top:4px;">
        <i class="fa-solid fa-person-walking"></i> Sales Representative
      </div>
    </div>
  </div>
</div>

<div style="padding:16px;">
  <!-- Quick Info Cards -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px;">
    <div style="background:#fff;border-radius:14px;padding:16px;box-shadow:0 2px 12px rgba(0,0,0,.06);text-align:center;">
      <i class="fa-solid fa-file-invoice" style="font-size:1.5rem;color:#4f46e5;margin-bottom:6px;display:block;"></i>
      <div style="font-size:1.3rem;font-weight:800;color:#0f172a;">—</div>
      <div style="font-size:0.72rem;color:#64748b;">Orders Placed</div>
    </div>
    <div style="background:#fff;border-radius:14px;padding:16px;box-shadow:0 2px 12px rgba(0,0,0,.06);text-align:center;">
      <i class="fa-solid fa-store" style="font-size:1.5rem;color:#06b6d4;margin-bottom:6px;display:block;"></i>
      <div style="font-size:1.3rem;font-weight:800;color:#0f172a;">—</div>
      <div style="font-size:0.72rem;color:#64748b;">Retailers</div>
    </div>
  </div>

  <!-- Coming Soon Notice -->
  <div style="background:#fff;border-radius:14px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,.06);text-align:center;margin-bottom:16px;">
    <div style="font-size:3rem;margin-bottom:10px;">👤</div>
    <div style="font-size:1rem;font-weight:700;color:#0f172a;margin-bottom:6px;">Full Profile</div>
    <div style="font-size:0.85rem;color:#64748b;line-height:1.6;">Edit your details, change password, and manage your account settings — coming soon!</div>
    <div style="display:inline-flex;align-items:center;gap:6px;background:rgba(79,70,229,.1);color:#4f46e5;padding:6px 16px;border-radius:999px;font-size:0.8rem;font-weight:600;margin-top:14px;">
      <i class="fa-solid fa-rocket"></i> Coming Soon
    </div>
  </div>

  <!-- Logout -->
  <a href="<?= BASE_URL ?>/logout"
     style="display:flex;align-items:center;justify-content:center;gap:8px;background:#fff;border:1.5px solid #fee2e2;color:#ef4444;border-radius:14px;padding:14px;font-weight:700;font-size:0.9rem;text-decoration:none;">
    <i class="fa-solid fa-right-from-bracket"></i> Logout
  </a>
</div>
<div style="height:16px;"></div>
