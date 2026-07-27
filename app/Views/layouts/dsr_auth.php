<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title><?= $pageTitle ?? 'DSR Login' ?> — <?= APP_NAME ?></title>

  <!-- Favicon & Apple Touch Icon (DSR-specific) -->
  <link rel="icon" type="image/png" href="<?= asset('images/icons/dsr/icon-192.png') ?>">
  <link rel="apple-touch-icon" href="<?= asset('images/icons/dsr/apple-touch-icon.png') ?>">
  <meta name="theme-color" content="#1e40af">

  <!-- PWA: Web App Manifest -->
  <link rel="manifest" href="<?= BASE_URL ?>/dsr-manifest.php">

  <!-- PWA: iOS Safari meta tags -->
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="apple-mobile-web-app-title" content="DSR App">

  <!-- PWA: Service Worker base URL -->
  <meta name="sw-base-url" content="<?= BASE_URL ?>/">

  <!-- Styles (same as auth layout) -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
  <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="h-full">
<?= $content ?>

<!-- PWA: Service Worker Registration -->
<script src="<?= BASE_URL ?>/dsr-sw-register.js" defer></script>
</body>
</html>
