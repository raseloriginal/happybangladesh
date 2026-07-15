<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password — <?= APP_NAME ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
  <style>body { font-family: 'Inter', sans-serif; }
  .login-bg { background: linear-gradient(135deg, #1e3a5f 0%, #1d4ed8 50%, #0ea5e9 100%); }</style>
</head>
<body class="h-full">
<?php $flash = Auth::getFlash(); ?>
<div class="min-h-screen login-bg flex items-center justify-center p-4">
  <div class="w-full max-w-md">
    <div class="bg-white/10 border border-white/20 rounded-2xl p-8 shadow-2xl backdrop-blur">
      <div class="text-center mb-7">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-white/20 mb-3">
          <i class="fa-solid fa-key text-white text-xl"></i>
        </div>
        <h1 class="text-xl font-bold text-white">Forgot Password?</h1>
        <p class="text-blue-200 text-sm mt-1">Enter your email to receive reset instructions.</p>
      </div>

      <?php if ($flash): ?>
        <div class="mb-4 px-4 py-3 rounded-lg text-sm flex items-center gap-2
          <?= $flash['type'] === 'error' ? 'bg-red-500/20 text-red-200' : 'bg-blue-500/20 text-blue-200' ?>">
          <i class="fa-solid fa-circle-info"></i><?= h($flash['message']) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="<?= url('forgot') ?>" data-validate class="space-y-5">
        <?= Helpers::csrfField() ?>
        <div>
          <label class="block text-sm font-medium text-blue-100 mb-1.5">Email Address</label>
          <input type="email" name="email" required
                 class="w-full px-4 py-2.5 bg-white/10 border border-white/20 rounded-lg text-white placeholder-blue-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                 placeholder="your@email.com">
        </div>
        <button type="submit"
                class="w-full py-2.5 bg-blue-500 hover:bg-blue-400 text-white font-semibold rounded-lg transition flex items-center justify-center gap-2">
          <i class="fa-solid fa-paper-plane"></i> Send Reset Link
        </button>
        <div class="text-center mt-6">
          <a href="<?= url('login') ?>" class="text-blue-300 hover:text-white transition text-sm">
            <i class="fa-solid fa-arrow-left mr-1"></i> Back to login
          </a>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>
