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

putenv('VERCEL=1');
putenv('APP_STORAGE=/tmp/storage');
putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');
$_ENV['VERCEL'] = '1';
$_ENV['APP_STORAGE'] = '/tmp/storage';
$_ENV['VIEW_COMPILED_PATH'] = '/tmp/storage/framework/views';
$_SERVER['VERCEL'] = '1';
$_SERVER['APP_STORAGE'] = '/tmp/storage';
$_SERVER['VIEW_COMPILED_PATH'] = '/tmp/storage/framework/views';

// Fallback APP_KEY
if (!getenv('APP_KEY') && !isset($_ENV['APP_KEY']) && !isset($_SERVER['APP_KEY'])) {
    putenv('APP_KEY=base64:rvNN4ltrmVNsvfq4UyiuVq+I+eVkR0RWg8uJNaDgc/E=');
    $_ENV['APP_KEY'] = 'base64:rvNN4ltrmVNsvfq4UyiuVq+I+eVkR0RWg8uJNaDgc/E=';
    $_SERVER['APP_KEY'] = 'base64:rvNN4ltrmVNsvfq4UyiuVq+I+eVkR0RWg8uJNaDgc/E=';
}

putenv('SESSION_DRIVER=cookie');
putenv('CACHE_STORE=array');

$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/../public/index.php';

// Bootstrap Laravel
define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Explicitly set storage and view compiled path on the app instance before handling request
$app->useStoragePath('/tmp/storage');
$app['config']->set('view.compiled', '/tmp/storage/framework/views');
$app['config']->set('session.driver', 'cookie');
$app['config']->set('cache.default', 'array');

try {
    $app->handleRequest(\Illuminate\Http\Request::capture());
} catch (\Throwable $e) {
    http_response_code(500);
    echo '<div style="font-family: sans-serif; padding: 2rem; background: #fff1f2; color: #9f1239; border: 1px solid #fecdd3; border-radius: 8px; margin: 2rem auto; max-width: 50rem;">';
    echo '<h2 style="margin-top: 0;">Lỗi Khởi Động Laravel trên Vercel</h2>';
    echo '<p><strong>Thông báo:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . ' (Dòng ' . $e->getLine() . ')</p>';
    echo '<pre style="background: #ffffff; padding: 1rem; border-radius: 4px; overflow-x: auto; font-size: 0.85rem;">' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    echo '</div>';
}
