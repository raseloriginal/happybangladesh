<?php
/**
 * Flash alerts component
 */
$flash = Auth::getFlash();
if ($flash):
    $icons = [
        'success' => 'fa-circle-check',
        'error'   => 'fa-circle-xmark',
        'warning' => 'fa-triangle-exclamation',
        'info'    => 'fa-circle-info',
    ];
    $icon = $icons[$flash['type']] ?? 'fa-circle-info';
?>
<div class="alert alert-<?= h($flash['type']) ?>" data-auto-dismiss="4000" role="alert">
  <i class="fa-solid <?= $icon ?> mt-0.5 flex-shrink-0"></i>
  <span><?= h($flash['message']) ?></span>
  <button onclick="this.parentElement.remove()" class="ml-auto text-current opacity-60 hover:opacity-100">
    <i class="fa-solid fa-xmark"></i>
  </button>
</div>
<?php endif; ?>
