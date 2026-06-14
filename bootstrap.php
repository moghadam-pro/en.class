<?php
// bootstrap.php

define('ROOT_PATH', __DIR__);
define('VERSION', '1.0.0');

// ── .env Loader ──────────────────────────────────────────────────────────────
// Looks for .env in ROOT_PATH first, then one directory up (CloudPanel style:
// /home/sayid-en-class/htdocs/.env)
(function () {
    $candidates = [
        ROOT_PATH . '/.env',
        dirname(ROOT_PATH) . '/.env',
    ];
    foreach ($candidates as $envFile) {
        if (!file_exists($envFile)) continue;
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;
            if (!str_contains($line, '=')) continue;
            [$key, $val] = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val);
            if (!getenv($key)) {          // don't override real server env vars
                putenv("$key=$val");
                $_ENV[$key] = $val;
            }
        }
        break;                            // stop after first .env found
    }
})();

// ── Autoloader ───────────────────────────────────────────────────────────────
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
