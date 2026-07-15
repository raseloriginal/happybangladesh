<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — <?= APP_NAME ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
  <style>
    body { font-family: 'Inter', sans-serif; }
    .login-bg {
      background: linear-gradient(135deg, #1e3a5f 0%, #1d4ed8 50%, #0ea5e9 100%);
    }
    .login-card {
      backdrop-filter: blur(8px);
    }
  </style>
</head>
<body class="h-full">

<?php
// Flash from session
$flash = Auth::getFlash();
?>

<div class="min-h-screen login-bg flex items-center justify-center p-4">

  <!-- Decorative circles -->
  <div class="absolute top-0 left-0 w-96 h-96 bg-white/5 rounded-full -translate-x-1/2 -translate-y-1/2 blur-3xl"></div>
  <div class="absolute bottom-0 right-0 w-96 h-96 bg-white/5 rounded-full translate-x-1/2 translate-y-1/2 blur-3xl"></div>

  <div class="relative w-full max-w-md">

    <!-- Card -->
    <div class="login-card bg-white/10 border border-white/20 rounded-2xl p-8 shadow-2xl">

      <!-- Logo -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-white/20 mb-4">
          <i class="fa-solid fa-truck-fast text-white text-2xl"></i>
        </div>
        <h1 class="text-2xl font-bold text-white"><?= APP_NAME ?></h1>
        <p class="text-blue-200 text-sm mt-1">Distribution Management System</p>
      </div>

      <!-- Flash alert -->
      <?php if ($flash): ?>
        <div class="mb-5 px-4 py-3 rounded-lg text-sm font-medium flex items-center gap-2
          <?= $flash['type'] === 'error' ? 'bg-red-500/20 text-red-200 border border-red-400/30' : 'bg-green-500/20 text-green-200 border border-green-400/30' ?>">
          <i class="fa-solid <?= $flash['type'] === 'error' ? 'fa-circle-xmark' : 'fa-circle-check' ?>"></i>
          <?= h($flash['message']) ?>
        </div>
      <?php endif; ?>

      <!-- Login form -->
      <form method="POST" action="<?= url('login') ?>" data-validate class="space-y-5">
        <?= Helpers::csrfField() ?>

        <!-- Email or Phone -->
        <div>
          <label for="email" class="block text-sm font-medium text-blue-100 mb-1.5">Email or Phone Number</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <i class="fa-regular fa-user text-blue-300"></i>
            </div>
            <input type="text" id="email" name="email" required autocomplete="username"
                   value="<?= h($_POST['email'] ?? '') ?>"
                   class="w-full pl-10 pr-4 py-2.5 bg-white/10 border border-white/20 rounded-lg
                          text-white placeholder-blue-300 text-sm focus:outline-none
                          focus:ring-2 focus:ring-blue-400 focus:border-transparent transition"
                   placeholder="Email or Phone Number">
          </div>
          <p id="email-error" class="text-red-300 text-xs mt-1"></p>
        </div>

        <!-- Password -->
        <div>
          <label for="password" class="block text-sm font-medium text-blue-100 mb-1.5">Password</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <i class="fa-solid fa-lock text-blue-300"></i>
            </div>
            <input type="password" id="password" name="password" required autocomplete="current-password"
                   class="w-full pl-10 pr-10 py-2.5 bg-white/10 border border-white/20 rounded-lg
                          text-white placeholder-blue-300 text-sm focus:outline-none
                          focus:ring-2 focus:ring-blue-400 focus:border-transparent transition"
                   placeholder="••••••••">
            <button type="button"
                    onclick="const i=document.getElementById('password');i.type=i.type==='password'?'text':'password'"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-blue-300 hover:text-white">
              <i class="fa-solid fa-eye text-sm"></i>
            </button>
          </div>
          <p id="password-error" class="text-red-300 text-xs mt-1"></p>
        </div>

        <!-- Remember + Forgot -->
        <div class="flex items-center justify-between text-sm">
          <label class="flex items-center gap-2 text-blue-200 cursor-pointer">
            <input type="checkbox" name="remember" class="rounded border-white/30 bg-white/10 text-blue-500">
            Remember me
          </label>
          <a href="<?= url('forgot') ?>" class="text-blue-300 hover:text-white transition">Forgot password?</a>
        </div>

        <!-- Submit -->
        <button type="submit"
                class="w-full py-2.5 px-4 bg-blue-500 hover:bg-blue-400 text-white font-semibold rounded-lg
                       transition duration-150 flex items-center justify-center gap-2 shadow-lg shadow-blue-500/30">
          <i class="fa-solid fa-right-to-bracket"></i>
          Sign In
        </button>

      </form>

      <!-- Demo credentials -->
      <div class="mt-6 p-3 bg-white/10 rounded-lg border border-white/10">
        <p class="text-blue-200 text-xs font-semibold mb-2">Demo Credentials (password: <code class="text-yellow-300">password123</code>)</p>
        <div class="grid grid-cols-2 gap-1 text-xs text-blue-300">
          <span>Admin: <code class="text-white">admin@dms.com</code></span>
          <span>Manager: <code class="text-white">manager@dms.com</code></span>
          <span>SR: <code class="text-white">sr@dms.com</code></span>
          <span>DSR: <code class="text-white">dsr@dms.com</code></span>
        </div>
      </div>

    </div><!-- /card -->

    <p class="text-center text-blue-300/60 text-xs mt-4">&copy; <?= date('Y') ?> <?= APP_NAME ?></p>

  </div>

</div>

<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
