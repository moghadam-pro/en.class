<?php
// src/Core/HomeController.php

namespace App\Core;

use App\Topic\TopicRepository;
use App\SEO\MetaBuilder;

class HomeController
{
    public function index(array $params = []): void
    {
        $repo  = new TopicRepository();
        $seo   = new MetaBuilder();

        $featured   = $repo->topicOfTheDay();
        $recent     = ['items' => $repo->all([], 1, 6)];
        $tags       = array_slice($repo->allTags(), 0, 20, true);
        $stats      = $repo->stats();
        $meta       = $seo->forHome();
        $activePage = 'home';

        require ROOT_PATH . '/templates/layouts/base.php';
    }
}
