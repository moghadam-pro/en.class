<?php
// templates/pages/home.php
require ROOT_PATH . '/bootstrap.php';
use App\Topic\TopicRepository;
use App\SEO\MetaBuilder;

$repo     = new TopicRepository();
$featured = $repo->topicOfTheDay();
$recent   = $repo->all(['sort' => 'newest', 'limit' => 6]);
$stats    = $repo->stats();
$tags     = $repo->allTags();

$meta       = MetaBuilder::forHome();
$activePage = 'home';

require ROOT_PATH . '/templates/layouts/base.php';
