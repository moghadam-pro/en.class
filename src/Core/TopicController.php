<?php
// src/Core/TopicController.php

namespace App\Core;

use App\Topic\TopicRepository;
use App\SEO\MetaBuilder;

class TopicController
{
    public function index(array $params = []): void
    {
        $activePage = 'topics';
        $meta       = (new MetaBuilder())->forTopics([]);
        require ROOT_PATH . '/templates/layouts/base.php';
    }

    public function show(array $params = []): void
    {
        $repo  = new TopicRepository();
        $slug  = $params['slug'] ?? '';
        $topic = $repo->findBySlug($slug);

        if (!$topic) {
            http_response_code(404);
            $activePage = 'topics';
            $meta = ['head' => '<title>Not Found — Free Discussion Class</title>'];
            require ROOT_PATH . '/templates/layouts/base.php';
            return;
        }

        $activePage = 'topics';
        $meta       = (new MetaBuilder())->forTopic($topic);
        require ROOT_PATH . '/templates/layouts/base.php';
        // After base loads, inject topic data for JS auto-open
    }

    public function random(array $params = []): void
    {
        $repo  = new TopicRepository();
        $topic = $repo->random();
        if ($topic) {
            redirect('/topic/' . $topic['slug']);
        } else {
            redirect('/topics');
        }
    }

    public function search(array $params = []): void
    {
        $query  = sanitize($_GET['q'] ?? '');
        $repo   = new TopicRepository();
        $results = $repo->search($query);
        jsonResponse(['results' => $results, 'query' => $query]);
    }
}
