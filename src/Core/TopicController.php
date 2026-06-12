<?php
// src/Core/TopicController.php

namespace App\Core;

use App\Topic\TopicRepository;
use App\SEO\MetaBuilder;

class TopicController
{
    private TopicRepository $repo;
    private MetaBuilder $seo;

    public function __construct()
    {
        $this->repo = new TopicRepository();
        $this->seo  = new MetaBuilder();
    }

    public function index(array $params = []): void
    {
        $filters = [
            'level' => $_GET['level'] ?? '',
            'tag'   => $_GET['tag']   ?? '',
            'q'     => $_GET['q']     ?? '',
        ];
        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $result  = $this->repo->all($filters, $page, config('pagination.per_page', 12));
        $tags    = array_slice($this->repo->allTags(), 0, 30, true);
        $meta    = $this->seo->forTopics($filters);

        render('pages/topics', array_merge($result, compact('filters', 'tags', 'meta')));
    }

    public function show(array $params): void
    {
        $topic = $this->repo->findBySlug($params['slug'] ?? '');
        if (!$topic) {
            http_response_code(404);
            render('pages/404');
            return;
        }

        $related = $this->repo->related($topic['slug']);
        $meta    = $this->seo->forTopic($topic);

        render('pages/topic', compact('topic', 'related', 'meta'));
    }

    public function random(array $params = []): void
    {
        $topic = $this->repo->random();
        if ($topic) redirect('/topic/' . $topic['slug']);
        else redirect('/topics');
    }

    public function search(array $params = []): void
    {
        $q       = trim($_GET['q'] ?? '');
        $results = $q ? $this->repo->search($q) : [];
        $meta    = $this->seo->forTopics(['q' => $q]);
        jsonResponse(['results' => $results, 'query' => $q, 'count' => count($results)]);
    }
}
