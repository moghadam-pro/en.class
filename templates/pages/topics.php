<?php
// templates/pages/topics.php
require ROOT_PATH . '/bootstrap.php';
use App\Topic\TopicRepository;
use App\SEO\MetaBuilder;

$repo  = new TopicRepository();
$meta  = MetaBuilder::forTopics([]);
$activePage = 'topics';

require ROOT_PATH . '/templates/layouts/base.php';
