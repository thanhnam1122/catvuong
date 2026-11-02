<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Create writable /tmp storage directories for Laravel on Vercel Serverless
$storageDirs = [
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
    '/tmp/storage/app/public',
    '/tmp/bootstrap/cache',
];

foreach ($storageDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

// Set environment variables for serverless runtime
$envDefaults = [
    'VERCEL' => '1',
    'APP_STORAGE' => '/tmp/storage',
    'VIEW_COMPILED_PATH' => '/tmp/storage/framework/views',
    'APP_SERVICES_CACHE' => '/tmp/bootstrap/cache/services.php',
    'APP_PACKAGES_CACHE' => '/tmp/bootstrap/cache/packages.php',
    'APP_CONFIG_CACHE' => '/tmp/bootstrap/cache/config.php',
    'APP_ROUTES_CACHE' => '/tmp/bootstrap/cache/routes-v7.php',
    'APP_EVENTS_CACHE' => '/tmp/bootstrap/cache/events.php',
    'SESSION_DRIVER' => 'cookie',
    'CACHE_STORE' => 'array',
    'LOG_CHANNEL' => 'stderr',
    'DB_CONNECTION' => 'pgsql',
    'DB_HOST' => 'aws-0-ap-southeast-1.pooler.supabase.com',
    'DB_PORT' => '5432',
    'DB_DATABASE' => 'postgres',
    'DB_USERNAME' => 'postgres.jguhzodajzapzqmkzljd',
    'DB_PASSWORD' => 'thanhnam1122004@',
    'APP_MAINTENANCE_DRIVER' => 'file',
    'APP_MAINTENANCE_STORE' => 'array',
    'APP_KEY' => 'base64:rvNN4ltrmVNsvfq4UyiuVq+I+eVkR0RWg8uJNaDgc/E=',
];

foreach ($envDefaults as $key => $value) {
    if (!getenv($key)) {
        putenv("{$key}={$value}");
    }
    if (!isset($_ENV[$key])) {
        $_ENV[$key] = $value;
    }
    if (!isset($_SERVER[$key])) {
        $_SERVER[$key] = $value;
    }
}

$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/../public/index.php';

// Bootstrap Laravel
define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

try {
    $app->useStoragePath('/tmp/storage');
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $request = \Illuminate\Http\Request::capture();
    $response = $kernel->handle($request);
    $response->send();
    
    try {
        $kernel->terminate($request, $response);
    } catch (\Throwable $termException) {
        // Safe ignore serverless termination cleanup
    }
} catch (\Throwable $e) {
    http_response_code(500);
    echo '<div style="font-family: sans-serif; padding: 2rem; background: #fff1f2; color: #9f1239; border: 1px solid #fecdd3; border-radius: 8px; margin: 2rem auto; max-width: 50rem;">';
    echo '<h2 style="margin-top: 0;">Lỗi Khởi Động Laravel trên Vercel</h2>';
    echo '<p><strong>Thông báo:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . ' (Dòng ' . $e->getLine() . ')</p>';
    echo '<pre style="background: #ffffff; padding: 1rem; border-radius: 4px; overflow-x: auto; font-size: 0.85rem;">' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    echo '</div>';
}


