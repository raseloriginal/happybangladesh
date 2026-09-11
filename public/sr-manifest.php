<?php
/**
 * SR Panel — Dynamic Web App Manifest
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
$icons = $base . '/assets/images/icons/sr';

$manifest = [
    'name'             => 'HappyBangladesh SR',
    'short_name'       => 'SR App',
    'description'      => 'HappyBangladesh DMS — Sales Representative Panel',
    'start_url'        => $base . '/sr/dashboard',
    'scope'            => $base . '/sr/',
    'display'          => 'standalone',
    'orientation'      => 'portrait',
    'theme_color'      => '#2563eb',
    'background_color' => '#2563eb',
    'lang'             => 'bn',
    'dir'              => 'ltr',
    'categories'       => ['business', 'productivity'],
    'icons'            => [
        ['src' => $icons . '/icon-192.png',         'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
        ['src' => $icons . '/icon-512.png',         'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
        ['src' => $icons . '/icon-maskable-512.png','sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
        ['src' => $icons . '/apple-touch-icon.png', 'sizes' => '180x180', 'type' => 'image/png', 'purpose' => 'any'],
    ],
    'shortcuts' => [
        [
            'name'       => 'অর্ডার দিন',
            'short_name' => 'অর্ডার',
            'url'        => $base . '/sr/orders/place',
            'icons'      => [['src' => $icons . '/icon-192.png', 'sizes' => '192x192']],
        ],
        [
            'name'       => 'ড্যাশবোর্ড',
            'short_name' => 'হোম',
            'url'        => $base . '/sr/dashboard',
            'icons'      => [['src' => $icons . '/icon-192.png', 'sizes' => '192x192']],
        ],
    ],
];

echo json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
