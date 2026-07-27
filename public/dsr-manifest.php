<?php
/**
 * DSR Panel — Dynamic Web App Manifest
 * Outputs correct absolute URLs for both localhost subfolder
 * and live server root environments.
 */
declare(strict_types=1);

// Bootstrap config to get BASE_URL
require_once dirname(__DIR__) . '/app/Config/config.php';

// Output headers
header('Content-Type: application/manifest+json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('X-Content-Type-Options: nosniff');

$base  = rtrim(BASE_URL, '/');
$icons = $base . '/assets/images/icons/dsr';

$manifest = [
    'name'             => 'HappyBangladesh DSR',
    'short_name'       => 'DSR App',
    'description'      => 'HappyBangladesh DMS — Delivery Sales Representative Panel',
    'start_url'        => $base . '/dsr/dashboard',
    'scope'            => $base . '/dsr/',
    'display'          => 'standalone',
    'orientation'      => 'portrait',
    'theme_color'      => '#1e40af',
    'background_color' => '#1e40af',
    'lang'             => 'bn',
    'dir'              => 'ltr',
    'categories'       => ['business', 'productivity'],
    'icons'            => [
        ['src' => $icons . '/icon-192.png',         'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
        ['src' => $icons . '/icon-512.png',         'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
        ['src' => $icons . '/icon-maskable-512.png','sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
        ['src' => $icons . '/apple-touch-icon.png', 'sizes' => '180x180', 'type' => 'image/png', 'purpose' => 'maskable'],
    ],
    'shortcuts' => [
        [
            'name'       => 'ডেলিভারি রুট',
            'short_name' => 'ডেলিভারি',
            'url'        => $base . '/dsr/delivery',
            'icons'      => [['src' => $icons . '/icon-192.png', 'sizes' => '192x192']],
        ],
        [
            'name'       => 'ড্যাশবোর্ড',
            'short_name' => 'হোম',
            'url'        => $base . '/dsr/dashboard',
            'icons'      => [['src' => $icons . '/icon-192.png', 'sizes' => '192x192']],
        ],
    ],
];

echo json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
