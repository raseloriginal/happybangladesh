<?php $pageTitle = 'Active Sessions'; ?>

<!-- ── Session Stats Cards ──────────────────────────────────── -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
  <?php
  $statCards = [
    ['label' => 'Total Active',  'value' => $counts['total'],   'icon' => 'fa-shield-halved', 'color' => 'bg-blue-100 text-blue-700'],
    ['label' => 'Admin',         'value' => $counts['admin'],   'icon' => 'fa-user-shield',   'color' => 'bg-indigo-100 text-indigo-700'],
    ['label' => 'Manager',       'value' => $counts['manager'], 'icon' => 'fa-users-gear',    'color' => 'bg-violet-100 text-violet-700'],
    ['label' => 'Sales Reps',    'value' => $counts['sr'],      'icon' => 'fa-briefcase',     'color' => 'bg-emerald-100 text-emerald-700'],
    ['label' => 'DSRs',          'value' => $counts['dsr'],     'icon' => 'fa-truck-fast',    'color' => 'bg-amber-100 text-amber-700'],
  ];
  foreach ($statCards as $c):
  ?>
  <div class="stat-card flex items-center gap-3">
    <div class="w-10 h-10 rounded-xl <?= $c['color'] ?> flex items-center justify-center flex-shrink-0">
      <i class="fa-solid <?= $c['icon'] ?> text-base"></i>
    </div>
    <div>
      <div class="text-2xl font-bold text-gray-800"><?= $c['value'] ?></div>
      <div class="text-xs text-gray-500 font-medium"><?= $c['label'] ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ── Filter Tabs ──────────────────────────────────────────── -->
<div class="excel-container">
  <div class="excel-ribbon">
    <div class="flex items-center gap-2">
      <i class="fa-solid fa-shield-halved text-blue-200"></i>
      <span class="font-bold text-sm">Active Login Sessions</span>
      <span class="text-blue-200 text-xs font-medium ml-1">(<?= count($sessions) ?> sessions)</span>
    </div>
    <div class="flex items-center gap-1">
      <a href="<?= url('admin/sessions') ?>"
         class="px-3 py-1.5 rounded-lg text-xs font-bold transition <?= !$filterRole ? 'bg-white text-blue-700 shadow-sm' : 'text-blue-200 hover:text-white hover:bg-white/10' ?>">
        All
      </a>
      <?php foreach (['admin' => 'Admin', 'manager' => 'Manager', 'sr' => 'SR', 'dsr' => 'DSR'] as $slug => $label): ?>
      <a href="<?= url('admin/sessions?role=' . $slug) ?>"
         class="px-3 py-1.5 rounded-lg text-xs font-bold transition <?= $filterRole === $slug ? 'bg-white text-blue-700 shadow-sm' : 'text-blue-200 hover:text-white hover:bg-white/10' ?>">
        <?= $label ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- ── Sessions Table ──────────────────────────────────────── -->
  <div class="overflow-x-auto">
    <table class="excel-table">
      <thead>
        <tr>
          <th class="excel-row-num">#</th>
          <th>User</th>
          <th>Role</th>
          <th>IP Address</th>
          <th>Device / Browser</th>
          <th>Logged In</th>
          <th>Last Active</th>
          <th>Expires</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($sessions as $i => $s): ?>
        <?php
          $isCurrentSession = ($s['token'] === $currentToken);
          $ua = $s['user_agent'] ?? '';
          
          // Parse user agent for friendly display
          $browser = 'Unknown';
          $device  = 'Desktop';
          if (stripos($ua, 'Chrome') !== false && stripos($ua, 'Edg') === false) $browser = 'Chrome';
          elseif (stripos($ua, 'Firefox') !== false) $browser = 'Firefox';
          elseif (stripos($ua, 'Safari') !== false && stripos($ua, 'Chrome') === false) $browser = 'Safari';
          elseif (stripos($ua, 'Edg') !== false) $browser = 'Edge';
          elseif (stripos($ua, 'Opera') !== false || stripos($ua, 'OPR') !== false) $browser = 'Opera';
          
          if (stripos($ua, 'Mobile') !== false || stripos($ua, 'Android') !== false) $device = 'Mobile';
          elseif (stripos($ua, 'Tablet') !== false || stripos($ua, 'iPad') !== false) $device = 'Tablet';
          
          $os = 'Unknown';
          if (stripos($ua, 'Windows') !== false) $os = 'Windows';
          elseif (stripos($ua, 'Mac') !== false) $os = 'macOS';
          elseif (stripos($ua, 'Linux') !== false) $os = 'Linux';
          elseif (stripos($ua, 'Android') !== false) $os = 'Android';
          elseif (stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false) $os = 'iOS';

          $roleBadgeColors = [
            'admin'   => 'bg-blue-100 text-blue-700',
            'manager' => 'bg-violet-100 text-violet-700',
            'sr'      => 'bg-emerald-100 text-emerald-700',
            'dsr'     => 'bg-amber-100 text-amber-700',
          ];
          $roleBadge = $roleBadgeColors[$s['role_slug']] ?? 'bg-gray-100 text-gray-600';
        ?>
        <tr class="<?= $isCurrentSession ? 'bg-blue-50/60' : '' ?>">
          <td class="excel-row-num"><?= $i + 1 ?></td>
          <td>
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white text-[10px] font-black flex-shrink-0">
                <?= Helpers::initials($s['user_name'] ?? 'U') ?>
              </div>
              <div class="min-w-0">
                <div class="text-sm font-bold text-gray-900 truncate">
                  <?= h($s['user_name'] ?? 'Unknown') ?>
                  <?php if ($isCurrentSession): ?>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700 ml-1">YOU</span>
                  <?php endif; ?>
                </div>
                <div class="text-[11px] text-gray-400 truncate"><?= h($s['user_email'] ?? '') ?></div>
              </div>
            </div>
          </td>
          <td>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold <?= $roleBadge ?>">
              <?= ucfirst($s['role_slug']) ?>
            </span>
          </td>
          <td class="excel-mono text-gray-600 text-xs"><?= h($s['ip_address'] ?? '—') ?></td>
          <td>
            <div class="flex items-center gap-1.5">
              <i class="fa-solid <?= $device === 'Mobile' ? 'fa-mobile-screen' : ($device === 'Tablet' ? 'fa-tablet-screen-button' : 'fa-desktop') ?> text-gray-400 text-xs"></i>
              <span class="text-xs text-gray-700 font-medium"><?= $browser ?></span>
              <span class="text-gray-300">·</span>
              <span class="text-xs text-gray-500"><?= $os ?></span>
            </div>
          </td>
          <td class="text-xs text-gray-600"><?= Helpers::datetime($s['logged_in_at'], 'd M, h:i A') ?></td>
          <td>
            <div class="text-xs text-gray-600"><?= Helpers::timeAgo($s['last_active_at']) ?></div>
          </td>
          <td class="text-xs text-gray-500"><?= Helpers::datetime($s['expires_at'], 'd M Y') ?></td>
          <td class="text-center">
            <?php if ($isCurrentSession): ?>
              <span class="text-xs text-gray-400 font-medium italic">Current</span>
            <?php else: ?>
              <form action="<?= url('admin/sessions/logout/' . $s['id']) ?>" method="POST" class="inline"
                    onsubmit="return confirm('Are you sure you want to terminate this session for <?= h($s['user_name'] ?? 'this user') ?>?');">
                <?= Helpers::csrfField() ?>
                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 hover:text-rose-700 transition border border-rose-100">
                  <i class="fa-solid fa-power-off text-[10px]"></i>
                  Terminate
                </button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>

        <?php if (empty($sessions)): ?>
        <tr>
          <td colspan="9" class="text-center text-gray-400 py-8">
            <div class="flex flex-col items-center gap-2">
              <i class="fa-solid fa-shield-halved text-3xl text-gray-200"></i>
              <span class="text-sm">No active sessions found<?= $filterRole ? ' for ' . ucfirst($filterRole) : '' ?></span>
            </div>
          </td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
