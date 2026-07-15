<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? h($pageTitle) . ' — ' : '' ?><?= APP_NAME ?></title>
  <meta name="description" content="<?= APP_NAME ?> — FMCG Distribution Management System">

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: { DEFAULT: '#2563eb', light: '#3b82f6', dark: '#1d4ed8' }
          }
        }
      }
    }
  </script>

  <!-- Font Awesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer">

  <!-- Custom styles -->
  <link rel="stylesheet" href="<?= asset('css/app.css') ?>">

  <!-- Inter font -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    body { font-family: 'Inter', sans-serif; background: #f8fafc; }
  </style>

  <?= $extraHead ?? '' ?>
</head>
<body class="h-full">

<!-- Mobile sidebar overlay -->
<div id="sidebar-overlay"></div>

<div class="flex h-full">

  <!-- Sidebar -->
  <?php include APP_PATH . '/Views/components/sidebar.php'; ?>

  <!-- Main content -->
  <div class="main-content flex-1 flex flex-col min-w-0">

    <!-- Top header -->
    <?php include APP_PATH . '/Views/components/header.php'; ?>

    <!-- Page content -->
    <main class="flex-1 p-5 lg:p-7 min-w-0">

      <!-- Flash alerts -->
      <?php include APP_PATH . '/Views/components/alerts.php'; ?>

      <!-- Page body (injected by render()) -->
      <?= $content ?>

    </main>

    <!-- Footer -->
    <?php include APP_PATH . '/Views/components/footer.php'; ?>
  </div><!-- /.main-content -->

</div><!-- /.flex -->

<!-- App JS -->
<script src="<?= asset('js/app.js') ?>"></script>
<?= $extraScripts ?? '' ?>

</body>
</html>
