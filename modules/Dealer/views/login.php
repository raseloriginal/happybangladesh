<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dealer Login - HappyBangladesh DMS</title>
    <link rel="stylesheet" href="<?= asset('css/tailwind.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center bg-gradient-to-br from-emerald-500 to-emerald-800">

<div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Dealer Portal</h1>
        <p class="text-gray-500">Sign in to manage your stock & sales</p>
    </div>

    <?php 
        $flash = Auth::getFlash();
        if ($flash && $flash['type'] === 'error'): 
    ?>
        <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6 border border-red-200">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>/dealer/login" method="POST">
        <div class="mb-5">
            <label class="block text-sm font-medium text-gray-700 mb-2" for="username">Username</label>
            <input class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors" 
                   type="text" name="username" id="username" required placeholder="Enter your username">
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2" for="password">Password</label>
            <input class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors" 
                   type="password" name="password" id="password" required placeholder="••••••••">
        </div>

        <button type="submit" class="w-full bg-emerald-600 text-white font-semibold py-3 px-4 rounded-lg hover:bg-emerald-700 focus:ring-4 focus:ring-emerald-200 transition-all">
            Sign In
        </button>
    </form>
    
    <div class="mt-6 text-center text-sm text-gray-500">
        &copy; <?= date('Y') ?> HappyBangladesh
    </div>
</div>

</body>
</html>
