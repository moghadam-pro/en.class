<?php // templates/pages/topics_content.php ?>

<header class="px-4 pt-5 pb-3 sticky top-0 bg-brand-dark/95 backdrop-blur z-10 border-b border-brand-card">
  <h1 class="font-display text-2xl font-bold text-brand-text mb-3">Topics</h1>
  <!-- Filter chips -->
  <div class="flex gap-2 overflow-x-auto no-scroll pb-1">
    <button class="chip active flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full border border-brand-blue text-white bg-brand-blue whitespace-nowrap transition-all" onclick="FDC.filter('all',this)">All</button>
    <button class="chip flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full border border-brand-muted text-brand-dim whitespace-nowrap transition-all" onclick="FDC.filter('A',this)">A1–A2</button>
    <button class="chip flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full border border-brand-muted text-brand-dim whitespace-nowrap transition-all" onclick="FDC.filter('B',this)">B1–B2</button>
    <button class="chip flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full border border-brand-muted text-brand-dim whitespace-nowrap transition-all" onclick="FDC.filter('C',this)">C1–C2</button>
    <button class="chip flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full border border-brand-muted text-brand-dim whitespace-nowrap transition-all" onclick="FDC.filter('newest',this)">Newest</button>
    <button class="chip flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full border border-brand-muted text-brand-dim whitespace-nowrap transition-all" onclick="FDC.filter('popular',this)">Popular</button>
  </div>
</header>

<!-- Topics grid — rendered by JS from FDC.allTopics -->
<div id="topicsGrid" class="p-4 space-y-3"></div>

<!-- Seed data for JS -->
<script>
// Expose PHP topic index to JS
window.FDC_TOPICS = <?php
  $repo = new App\Topic\TopicRepository();
  $all  = $repo->all();
  // Output minimal data needed for the topics list
  $out = array_map(fn($t) => [
    'slug'           => $t['slug'],
    'title'          => $t['title'],
    'summary'        => $t['summary'],
    'level'          => $t['level'],
    'tags'           => $t['tags'] ?? [],
    'created_at'     => $t['created_at'] ?? '',
    'views'          => $t['views'] ?? 0,
    'question_count' => count($t['questions'] ?? []),
    'vocab_count'    => count($t['vocabulary'] ?? []),
  ], $all);
  echo json_encode(array_values($out), JSON_UNESCAPED_UNICODE);
?>;
</script>
