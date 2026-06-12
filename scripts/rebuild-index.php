#!/usr/bin/env php
<?php
/**
 * Rebuild index.json from all topic JSON files.
 * Run from the project root:
 *   php scripts/rebuild-index.php
 */

define('PROJECT_ROOT', dirname(__DIR__));
define('DATA_DIR',     PROJECT_ROOT . '/data');
define('TOPICS_DIR',   DATA_DIR . '/topics');
define('INDEX_FILE',   DATA_DIR . '/index.json');

echo "🔄  Scanning " . TOPICS_DIR . " …\n";

$files  = glob(TOPICS_DIR . '/*.json');
$topics = [];
$errors = [];

if (empty($files)) {
    echo "⚠️  No topic files found.\n";
    exit(0);
}

foreach ($files as $file) {
    $raw = file_get_contents($file);
    $data = json_decode($raw, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $errors[] = "❌  Invalid JSON: " . basename($file);
        continue;
    }

    if (empty($data['slug'])) {
        $errors[] = "⚠️  Missing slug: " . basename($file);
        continue;
    }

    $topics[] = [
        'slug'           => $data['slug'],
        'title'          => $data['title']         ?? $data['slug'],
        'summary'        => $data['summary']        ?? '',
        'level'          => $data['level']          ?? 'B1',
        'tags'           => $data['tags']           ?? [],
        'cover'          => $data['cover']          ?? '',
        'created_at'     => $data['created_at']     ?? date('Y-m-d'),
        'question_count' => count($data['questions'] ?? []),
        'vocab_count'    => count($data['vocabulary'] ?? []),
    ];

    echo "   ✅  " . $data['title'] . " (" . $data['slug'] . ")\n";
}

// Sort newest first
usort($topics, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));

$index = [
    'generated_at' => gmdate('Y-m-d\TH:i:s\Z'),
    'total'        => count($topics),
    'topics'       => $topics,
];

$written = file_put_contents(
    INDEX_FILE,
    json_encode($index, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
);

echo "\n";
foreach ($errors as $e) {
    echo $e . "\n";
}

if ($written === false) {
    echo "❌  Failed to write " . INDEX_FILE . "\n";
    exit(1);
}

echo "✅  index.json written — {$index['total']} topics.\n";
exit(0);
