<?php
// templates/pages/topic.php
require ROOT_PATH . '/bootstrap.php';
use App\Topic\TopicRepository;
use App\SEO\MetaBuilder;

$repo  = new TopicRepository();
$topic = $repo->findBySlug($slug ?? '');
if (!$topic) { http_response_code(404); require ROOT_PATH . '/templates/pages/404.php'; exit; }

$meta       = MetaBuilder::forTopic($topic);
$activePage = 'topics';

require ROOT_PATH . '/templates/layouts/base.php';
