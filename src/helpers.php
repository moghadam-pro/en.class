<?php
// src/helpers.php

/**
 * Safely escape HTML output
 */
function e(mixed $val): string
{
    return htmlspecialchars((string) $val, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Slugify a string
 */
function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

/**
 * Read a JSON file safely
 */
function readJson(string $path): array
{
    if (!file_exists($path)) return [];
    $content = file_get_contents($path);
    if ($content === false) return [];
    $data = json_decode($content, true);
    return is_array($data) ? $data : [];
}

/**
 * Write a JSON file atomically
 */
function writeJson(string $path, array $data): bool
{
    $dir = dirname($path);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $tmp = $path . '.tmp.' . uniqid();
    $written = file_put_contents($tmp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    if ($written === false) return false;
    return rename($tmp, $path);
}

/**
 * Generate a URL-safe asset path with cache-busting
 */
function asset(string $path): string
{
    $full = ROOT_PATH . '/public' . $path;
    $version = file_exists($full) ? '?v=' . substr(md5_file($full), 0, 8) : '';
    return $path . $version;
}

/**
 * Format a date
 */
function formatDate(string $date, string $format = 'F j, Y'): string
{
    $ts = strtotime($date);
    return $ts ? date($format, $ts) : $date;
}

/**
 * Truncate text
 */
function truncate(string $text, int $length = 160): string
{
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length - 3) . '...';
}

/**
 * Level badge color mapping
 */
function levelColor(string $level): string
{
    return match (strtoupper($level)) {
        'A1', 'A2' => 'green',
        'B1', 'B2' => 'blue',
        'C1', 'C2' => 'red',
        default    => 'gray',
    };
}

/**
 * Simple template renderer
 */
function render(string $template, array $vars = []): void
{
    extract($vars);
    $file = ROOT_PATH . '/templates/' . $template . '.php';
    if (!file_exists($file)) {
        http_response_code(500);
        echo "Template not found: {$template}";
        return;
    }
    require $file;
}

/**
 * Sanitize a string (removes HTML, trims)
 */
function sanitize(string $input): string
{
    return trim(strip_tags($input));
}

/**
 * Generate "topic of the day" slug deterministically from date
 */
function topicOfTheDay(array $slugs): ?string
{
    if (empty($slugs)) return null;
    $dayIndex = (int) date('z') % count($slugs);
    return $slugs[$dayIndex];
}

/**
 * Redirect with optional status
 */
function redirect(string $url, int $code = 302): never
{
    header("Location: {$url}", true, $code);
    exit;
}

/**
 * JSON response for API endpoints
 */
function jsonResponse(array $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Read root config
 */
function config(string $key, mixed $default = null): mixed
{
    static $cfg = null;
    if ($cfg === null) $cfg = require ROOT_PATH . '/config/config.php';
    $parts = explode('.', $key);
    $val   = $cfg;
    foreach ($parts as $part) {
        if (!is_array($val) || !array_key_exists($part, $val)) return $default;
        $val = $val[$part];
    }
    return $val;
}
