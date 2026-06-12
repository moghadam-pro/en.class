<?php
// src/Topic/TopicRepository.php

namespace App\Topic;

class TopicRepository
{
    private string $indexPath;
    private string $topicsDir;
    private array  $indexCache = [];

    public function __construct()
    {
        $this->indexPath = ROOT_PATH . '/data/index.json';
        $this->topicsDir = ROOT_PATH . '/data/topics';
    }

    // ── Index ────────────────────────────────────────────────────────────────

    public function getIndex(): array
    {
        if (!empty($this->indexCache)) return $this->indexCache;
        $this->indexCache = readJson($this->indexPath);
        return $this->indexCache;
    }

    public function rebuildIndex(): bool
    {
        $entries = [];
        foreach (glob($this->topicsDir . '/*.json') as $file) {
            $data = readJson($file);
            if (empty($data['slug'])) continue;
            $entries[] = [
                'slug'       => $data['slug'],
                'title'      => $data['title']      ?? '',
                'summary'    => $data['summary']     ?? '',
                'level'      => $data['level']       ?? '',
                'tags'       => $data['tags']        ?? [],
                'cover'      => $data['cover']       ?? '',
                'created_at' => $data['created_at']  ?? '',
                'q_count'    => count($data['questions'] ?? []),
                'v_count'    => count($data['vocabulary'] ?? []),
            ];
        }
        usort($entries, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));
        $this->indexCache = [];
        return writeJson($this->indexPath, ['topics' => $entries, 'updated_at' => date('c')]);
    }

    // ── Listing ──────────────────────────────────────────────────────────────

    public function all(array $filters = [], int $page = 1, int $perPage = 12): array
    {
        $index  = $this->getIndex();
        $topics = $index['topics'] ?? [];

        if (!empty($filters['level'])) {
            $topics = array_filter($topics, fn($t) => strtoupper($t['level']) === strtoupper($filters['level']));
        }
        if (!empty($filters['tag'])) {
            $topics = array_filter($topics, fn($t) => in_array($filters['tag'], $t['tags'] ?? []));
        }
        if (!empty($filters['q'])) {
            $q = strtolower($filters['q']);
            $topics = array_filter($topics, fn($t) =>
                str_contains(strtolower($t['title']), $q) ||
                str_contains(strtolower($t['summary']), $q)
            );
        }

        $topics = array_values($topics);
        $total  = count($topics);
        $offset = ($page - 1) * $perPage;
        $items  = array_slice($topics, $offset, $perPage);

        return [
            'items'      => $items,
            'total'      => $total,
            'page'       => $page,
            'per_page'   => $perPage,
            'last_page'  => (int) ceil($total / $perPage),
        ];
    }

    public function allSlugs(): array
    {
        $index = $this->getIndex();
        return array_column($index['topics'] ?? [], 'slug');
    }

    public function allTags(): array
    {
        $index = $this->getIndex();
        $tags  = [];
        foreach ($index['topics'] ?? [] as $t) {
            foreach ($t['tags'] ?? [] as $tag) {
                $tags[$tag] = ($tags[$tag] ?? 0) + 1;
            }
        }
        arsort($tags);
        return $tags;
    }

    // ── Single topic ─────────────────────────────────────────────────────────

    public function findBySlug(string $slug): ?array
    {
        $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
        $file = $this->topicsDir . '/' . $slug . '.json';
        if (!file_exists($file)) return null;
        $data = readJson($file);
        return empty($data) ? null : $data;
    }

    public function related(string $slug, int $limit = 4): array
    {
        $topic  = $this->findBySlug($slug);
        if (!$topic) return [];

        $tags   = $topic['tags'] ?? [];
        $level  = $topic['level'] ?? '';
        $index  = $this->getIndex();
        $scored = [];

        foreach ($index['topics'] ?? [] as $t) {
            if ($t['slug'] === $slug) continue;
            $score = count(array_intersect($tags, $t['tags'] ?? []));
            if ($t['level'] === $level) $score++;
            if ($score > 0) $scored[] = ['topic' => $t, 'score' => $score];
        }

        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);
        return array_column(array_slice($scored, 0, $limit), 'topic');
    }

    public function random(): ?array
    {
        $slugs = $this->allSlugs();
        if (empty($slugs)) return null;
        $slug = $slugs[array_rand($slugs)];
        return $this->findBySlug($slug);
    }

    public function topicOfTheDay(): ?array
    {
        $slugs = $this->allSlugs();
        $slug  = topicOfTheDay($slugs);
        return $slug ? $this->findBySlug($slug) : null;
    }

    // ── Write ────────────────────────────────────────────────────────────────

    public function save(array $topic): bool
    {
        if (empty($topic['slug'])) return false;
        $file = $this->topicsDir . '/' . $topic['slug'] . '.json';
        $ok   = writeJson($file, $topic);
        if ($ok) $this->rebuildIndex();
        return $ok;
    }

    public function delete(string $slug): bool
    {
        $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
        $file = $this->topicsDir . '/' . $slug . '.json';
        if (!file_exists($file)) return false;
        $ok = unlink($file);
        if ($ok) $this->rebuildIndex();
        return $ok;
    }

    // ── Stats ─────────────────────────────────────────────────────────────────

    public function stats(): array
    {
        $index  = $this->getIndex();
        $topics = $index['topics'] ?? [];
        return [
            'total_topics'    => count($topics),
            'total_questions' => array_sum(array_column($topics, 'q_count')),
            'total_vocabulary'=> array_sum(array_column($topics, 'v_count')),
            'levels'          => array_count_values(array_column($topics, 'level')),
            'updated_at'      => $index['updated_at'] ?? '',
        ];
    }

    // ── Full-text search ──────────────────────────────────────────────────────

    public function search(string $query): array
    {
        if (strlen(trim($query)) < 2) return [];
        $q      = strtolower(trim($query));
        $results = [];

        foreach ($this->allSlugs() as $slug) {
            $topic = $this->findBySlug($slug);
            if (!$topic) continue;

            $score = 0;
            $titleLow = strtolower($topic['title'] ?? '');
            if (str_contains($titleLow, $q)) $score += 10;
            if (str_contains(strtolower($topic['summary'] ?? ''), $q)) $score += 5;
            foreach ($topic['tags'] ?? [] as $tag) {
                if (str_contains(strtolower($tag), $q)) $score += 3;
            }
            foreach ($topic['questions'] ?? [] as $question) {
                $text = is_array($question) ? ($question['text'] ?? '') : $question;
                if (str_contains(strtolower($text), $q)) $score += 1;
            }
            foreach ($topic['vocabulary'] ?? [] as $vocab) {
                $word = is_array($vocab) ? ($vocab['word'] ?? '') : $vocab;
                if (str_contains(strtolower($word), $q)) $score += 2;
            }

            if ($score > 0) {
                $results[] = [
                    'slug'    => $topic['slug'],
                    'title'   => $topic['title'],
                    'summary' => $topic['summary'] ?? '',
                    'level'   => $topic['level'] ?? '',
                    'tags'    => $topic['tags'] ?? [],
                    'score'   => $score,
                ];
            }
        }

        usort($results, fn($a, $b) => $b['score'] <=> $a['score']);
        return $results;
    }
}
