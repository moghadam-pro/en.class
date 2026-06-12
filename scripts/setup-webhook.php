#!/usr/bin/env php
<?php
/**
 * Register the Telegram webhook for this bot.
 * Run from the project root:
 *   php scripts/setup-webhook.php
 *
 * Requires environment variables (or .env loaded via bootstrap.php):
 *   TELEGRAM_BOT_TOKEN
 *   TELEGRAM_WEBHOOK_SECRET
 *   APP_URL  (e.g. https://en.class.sayid.ir)
 */

// ── Bootstrap (loads .env if present) ────────────────────────────────────────
$root = dirname(__DIR__);
$env  = $root . '/.env';
if (file_exists($env)) {
    foreach (file($env, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (!str_contains($line, '='))         continue;
        [$k, $v] = explode('=', $line, 2);
        $_ENV[trim($k)] = trim($v);
        putenv(trim($k) . '=' . trim($v));
    }
}

$token   = $_ENV['TELEGRAM_BOT_TOKEN']      ?? getenv('TELEGRAM_BOT_TOKEN')      ?? '';
$secret  = $_ENV['TELEGRAM_WEBHOOK_SECRET'] ?? getenv('TELEGRAM_WEBHOOK_SECRET') ?? '';
$appUrl  = rtrim($_ENV['APP_URL']           ?? getenv('APP_URL')                 ?? 'https://en.class.sayid.ir', '/');

if (empty($token)) {
    echo "❌  TELEGRAM_BOT_TOKEN is not set.\n";
    exit(1);
}

$webhookUrl = $appUrl . '/bot/webhook';

echo "🤖  Registering Telegram webhook…\n";
echo "    URL    : {$webhookUrl}\n";
echo "    Secret : " . (empty($secret) ? '(none — not recommended)' : str_repeat('*', strlen($secret))) . "\n\n";

// ── Build request ─────────────────────────────────────────────────────────────
$payload = ['url' => $webhookUrl, 'max_connections' => 40, 'allowed_updates' => ['message', 'callback_query']];
if (!empty($secret)) {
    $payload['secret_token'] = $secret;
}

$ch = curl_init("https://api.telegram.org/bot{$token}/setWebhook");
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 15,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($curlErr) {
    echo "❌  cURL error: {$curlErr}\n";
    exit(1);
}

$data = json_decode($response, true);

if ($data['ok'] ?? false) {
    echo "✅  Webhook registered successfully.\n";
    echo "    Response: " . ($data['description'] ?? 'OK') . "\n";
} else {
    echo "❌  Telegram API error:\n";
    echo "    " . ($data['description'] ?? $response) . "\n";
    exit(1);
}

echo "\n📋  Current webhook info:\n";
$ch2 = curl_init("https://api.telegram.org/bot{$token}/getWebhookInfo");
curl_setopt_array($ch2, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10]);
$info = json_decode(curl_exec($ch2), true);
curl_close($ch2);

if (!empty($info['result'])) {
    $r = $info['result'];
    echo "    URL              : " . ($r['url']                  ?? '-') . "\n";
    echo "    Has custom cert  : " . (($r['has_custom_certificate'] ?? false) ? 'yes' : 'no') . "\n";
    echo "    Pending updates  : " . ($r['pending_update_count']  ?? 0) . "\n";
    echo "    Last error       : " . ($r['last_error_message']    ?? 'none') . "\n";
}

exit(0);
