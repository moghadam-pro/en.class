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
$router->get('/',            [HomeController::class, 'index']);
$router->get('/topics',      [TopicController::class, 'index']);
$router->get('/topic/:slug', [TopicController::class, 'show']);
$router->get('/search',      [TopicController::class, 'index']); // search tab
$router->get('/tools',       function() {
    $activePage = 'tools';
    $meta = ['head' => '<title>Tools — Free Discussion Class</title>'];
    require ROOT_PATH . '/templates/layouts/base.php';
});
$router->get('/random', [TopicController::class, 'random']);

// ── API: topic index (flat, for JS) ───────────────────────────────────────
$router->get('/api/topics', function() {
    $repo   = new TopicRepository();
    $all    = $repo->all();
    $topics = array_map(fn($t) => [
        'slug'           => $t['slug'],
        'title'          => $t['title'],
        'summary'        => $t['summary']    ?? '',
        'level'          => $t['level']      ?? 'B1',
        'tags'           => $t['tags']       ?? [],
        'created_at'     => $t['created_at'] ?? '',
        'views'          => $t['views']      ?? 0,
        'question_count' => count($t['questions']  ?? []),
        'vocab_count'    => count($t['vocabulary'] ?? []),
    ], $all);
    jsonResponse(['topics' => array_values($topics)]);
});

// ── API: full topic data for detail view ──────────────────────────────────
$router->get('/api/topics/:slug', function(string $slug) {
    $repo  = new TopicRepository();
    $topic = $repo->findBySlug($slug);
    if (!$topic) { http_response_code(404); jsonResponse(['error' => 'Not found']); return; }
    jsonResponse($topic);
});

// ── API: search ───────────────────────────────────────────────────────────
$router->get('/api/search', [TopicController::class, 'search']);

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

// ── Webhook setup ─────────────────────────────────────────────────────────
$router->get('/bot/setup', function() {
    if ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1') {
        http_response_code(403); exit('Forbidden');
    }
    $bot = new TelegramBot();
    $result = $bot->setWebhook();
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
});

$router->dispatch();
