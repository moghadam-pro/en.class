<?php // templates/pages/topics_content.php ?>

<div class="page-header">
  <div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="/">Home</a>
      <span aria-hidden="true">›</span>
      <span aria-current="page">Topics</span>
    </nav>
    <h1 class="page-title">
      <?php if (!empty($filters['q'])): ?>
        Search: "<?= e($filters['q']) ?>"
      <?php elseif (!empty($filters['tag'])): ?>
        Topics: <em><?= e($filters['tag']) ?></em>
      <?php elseif (!empty($filters['level'])): ?>
        <?= e($filters['level']) ?> Level Topics
      <?php else: ?>
        All Discussion Topics
      <?php endif; ?>
    </h1>
    <p class="page-lead"><?= $total ?> topic<?= $total !== 1 ? 's' : '' ?> found</p>
  </div>
</div>

<div class="container topics-layout">

  <!-- Filters sidebar -->
  <aside class="topics-sidebar" aria-label="Filter topics">
    <div class="filter-group">
      <h2 class="filter-title">Level</h2>
      <div class="filter-options" role="group" aria-label="Filter by level">
        <a href="/topics<?= !empty($filters['tag']) ? '?tag=' . urlencode($filters['tag']) : '' ?>" class="filter-opt <?= empty($filters['level']) ? 'active' : '' ?>">All levels</a>
        <?php foreach (['A1','A2','B1','B2','C1','C2'] as $lvl): ?>
          <a href="/topics?level=<?= $lvl ?><?= !empty($filters['tag']) ? '&tag=' . urlencode($filters['tag']) : '' ?>" class="filter-opt <?= ($filters['level'] === $lvl) ? 'active' : '' ?>">
            <span class="level-dot level-<?= strtolower($lvl) ?>"></span> <?= $lvl ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if (!empty($tags)): ?>
    <div class="filter-group">
      <h2 class="filter-title">Themes</h2>
      <div class="filter-tags">
        <?php foreach (array_slice($tags, 0, 20, true) as $tag => $count): ?>
          <a href="/topics?tag=<?= urlencode($tag) ?><?= !empty($filters['level']) ? '&level=' . $filters['level'] : '' ?>" class="filter-tag-chip <?= ($filters['tag'] === $tag) ? 'active' : '' ?>">
            <?= e($tag) ?> <span><?= $count ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($filters['level']) || !empty($filters['tag']) || !empty($filters['q'])): ?>
    <a href="/topics" class="btn btn--outline btn--sm">Clear filters ×</a>
    <?php endif; ?>
  </aside>

  <!-- Main content -->
  <main class="topics-main">
    <?php if (!empty($items)): ?>
      <div class="topic-grid">
        <?php foreach ($items as $topic): ?>
          <?php require ROOT_PATH . '/templates/partials/topic_card.php'; ?>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if ($last_page > 1): ?>
      <nav class="pagination" aria-label="Pagination">
        <?php if ($page > 1): ?>
          <a href="?<?= http_build_query(array_merge($filters, ['page' => $page - 1])) ?>" class="page-btn" aria-label="Previous page">← Prev</a>
        <?php endif; ?>

        <?php for ($p = max(1, $page - 2); $p <= min($last_page, $page + 2); $p++): ?>
          <a href="?<?= http_build_query(array_merge($filters, ['page' => $p])) ?>" class="page-btn <?= $p === $page ? 'active' : '' ?>" <?= $p === $page ? 'aria-current="page"' : '' ?>><?= $p ?></a>
        <?php endfor; ?>

        <?php if ($page < $last_page): ?>
          <a href="?<?= http_build_query(array_merge($filters, ['page' => $page + 1])) ?>" class="page-btn" aria-label="Next page">Next →</a>
        <?php endif; ?>
      </nav>
      <?php endif; ?>

    <?php else: ?>
    <div class="empty-state">
      <div class="empty-icon">🔍</div>
      <h2>No topics found</h2>
      <p>Try a different filter or <a href="/topics">browse all topics</a>.</p>
    </div>
    <?php endif; ?>
  </main>
</div>
