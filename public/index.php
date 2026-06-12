<?php
// public/index.php

require dirname(__DIR__) . '/bootstrap.php';

use App\Core\Router;
use App\Core\HomeController;
use App\Core\TopicController;
use App\Bot\TelegramBot;
use App\SEO\MetaBuilder;
use App\Topic\TopicRepository;

$router = new Router();

// ── Web routes ────────────────────────────────────────────────────────────
$router->get('/',          [HomeController::class, 'index']);
$router->get('/topics',    [TopicController::class, 'index']);
$router->get('/topic/:slug', [TopicController::class, 'show']);
$router->get('/random',    [TopicController::class, 'random']);

// ── API routes ────────────────────────────────────────────────────────────
$router->get('/api/search', [TopicController::class, 'search']);
$router->get('/api/topics', function() {
    $repo = new \App\Topic\TopicRepository();
    $result = $repo->all(
        ['level' => $_GET['level'] ?? '', 'tag' => $_GET['tag'] ?? ''],
        (int)($_GET['page'] ?? 1),
        12
    );
    jsonResponse($result);
});

// ── Telegram bot webhook ──────────────────────────────────────────────────
$router->post('/bot/webhook', function() {
    $bot = new TelegramBot();
    $bot->handleWebhook();
    http_response_code(200);
    exit;
});

// ── Sitemap ───────────────────────────────────────────────────────────────
$router->get('/sitemap.xml', function() {
    $repo = new TopicRepository();
    $seo  = new MetaBuilder();
    $xml  = $seo->generateSitemap($repo->allSlugs());
    header('Content-Type: application/xml; charset=UTF-8');
    echo $xml;
    exit;
});

// ── Robots.txt ────────────────────────────────────────────────────────────
$router->get('/robots.txt', function() {
    $domain = config('app.url');
    header('Content-Type: text/plain');
    echo "User-agent: *\nAllow: /\nDisallow: /api/\nDisallow: /bot/\n\nSitemap: {$domain}/sitemap.xml\n";
    exit;
});

// ── Webhook setup (run once via CLI) ──────────────────────────────────────
$router->get('/bot/setup', function() {
    if ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1') {
        http_response_code(403); exit;
    }
    $bot = new \App\Bot\TelegramBot();
    $url = config('app.url') . '/bot/webhook';
    $result = $bot->setWebhook($url);
    jsonResponse($result);
});

$router->dispatch();
