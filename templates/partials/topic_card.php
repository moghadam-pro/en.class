<?php // templates/partials/topic_card.php ?>
<article class="topic-card" aria-label="<?= e($topic['title']) ?>">
  <a href="/topic/<?= e($topic['slug']) ?>" class="topic-card-link" tabindex="-1" aria-hidden="true"></a>

  <div class="topic-card-header">
    <span class="level-badge level-<?= strtolower(e($topic['level'])) ?>"><?= e($topic['level']) ?></span>
    <button
      class="fav-btn"
      onclick="toggleFavorite('<?= e($topic['slug']) ?>', this)"
      aria-label="Add to favorites"
      data-slug="<?= e($topic['slug']) ?>"
      title="Save to favorites"
    >♡</button>
  </div>

  <h3 class="topic-card-title">
    <a href="/topic/<?= e($topic['slug']) ?>"><?= e($topic['title']) ?></a>
  </h3>
  <p class="topic-card-summary"><?= e(truncate($topic['summary'], 110)) ?></p>

  <div class="topic-card-tags">
    <?php foreach (array_slice($topic['tags'] ?? [], 0, 3) as $tag): ?>
      <a href="/topics?tag=<?= urlencode($tag) ?>" class="tag-chip"><?= e($tag) ?></a>
    <?php endforeach; ?>
  </div>

  <div class="topic-card-footer">
    <span class="meta-stat" title="Discussion questions">❓ <?= (int)($topic['q_count'] ?? 0) ?></span>
    <span class="meta-stat" title="Vocabulary items">📚 <?= (int)($topic['v_count'] ?? 0) ?></span>
    <a href="/topic/<?= e($topic['slug']) ?>" class="topic-card-cta">Explore →</a>
  </div>
</article>
