<?php
// bootstrap.php

define('ROOT_PATH', __DIR__);
define('VERSION', '1.0.0');

// ── Autoloader ──────────────────────────────────────────────────────────────
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $base   = ROOT_PATH . '/src/';
    if (!str_starts_with($class, $prefix)) return;
    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file     = $base . $relative . '.php';
    if (file_exists($file)) require $file;
});

// ── Config ───────────────────────────────────────────────────────────────────
$config = require ROOT_PATH . '/config/config.php';

// ── Timezone ─────────────────────────────────────────────────────────────────
date_default_timezone_set($config['app']['timezone']);

// ── Error handling ───────────────────────────────────────────────────────────
if (getenv('APP_ENV') === 'production') {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', ROOT_PATH . '/logs/php_errors.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// ── Directories ───────────────────────────────────────────────────────────────
foreach (['data/topics', 'uploads', 'logs', 'cache'] as $dir) {
    $path = ROOT_PATH . '/' . $dir;
    if (!is_dir($path)) mkdir($path, 0755, true);
}

// ── Helpers ───────────────────────────────────────────────────────────────────
require ROOT_PATH . '/src/helpers.php';

return $config;
