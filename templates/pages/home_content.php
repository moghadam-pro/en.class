<?php // templates/pages/home_content.php ?>

<!-- ── Hero ──────────────────────────────────────────────────────────────── -->
<section class="hero">
  <div class="hero-bg" aria-hidden="true">
    <div class="hero-blob hero-blob--1"></div>
    <div class="hero-blob hero-blob--2"></div>
  </div>
  <div class="container">
    <div class="hero-inner">
      <div class="hero-content">
        <div class="eyebrow">🗣 Weekly Discussion Topics</div>
        <h1 class="hero-title">
          Start conversations<br>
          <span class="text-gradient">that matter</span>
        </h1>
        <p class="hero-lead">
          Thought-provoking questions, rich vocabulary, and interactive activities
          for ESL classrooms of every level.
        </p>
        <div class="hero-actions">
          <a href="/topics" class="btn btn--primary btn--lg">Browse Topics</a>
          <a href="/random" class="btn btn--ghost btn--lg">🎲 Surprise Me</a>
        </div>

        <?php if (!empty($stats)): ?>
        <div class="hero-stats" aria-label="Platform statistics">
          <div class="stat-item">
            <span class="stat-number"><?= $stats['total_topics'] ?></span>
            <span class="stat-label">Topics</span>
          </div>
          <div class="stat-divider" aria-hidden="true"></div>
          <div class="stat-item">
            <span class="stat-number"><?= $stats['total_questions'] ?></span>
            <span class="stat-label">Questions</span>
          </div>
          <div class="stat-divider" aria-hidden="true"></div>
          <div class="stat-item">
            <span class="stat-number"><?= $stats['total_vocabulary'] ?></span>
            <span class="stat-label">Vocabulary items</span>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <?php if ($featured): ?>
      <div class="hero-card">
        <div class="today-badge">📅 Topic of the Day</div>
        <div class="featured-card glass-card">
          <div class="featured-level">
            <span class="level-badge level-<?= strtolower(e($featured['level'])) ?>"><?= e($featured['level']) ?></span>
            <?php foreach (array_slice($featured['tags'] ?? [], 0, 2) as $tag): ?>
              <span class="tag-chip"><?= e($tag) ?></span>
            <?php endforeach; ?>
          </div>
          <h2 class="featured-title"><?= e($featured['title']) ?></h2>
          <p class="featured-summary"><?= e(truncate($featured['summary'], 120)) ?></p>
          <div class="featured-meta">
            <span>❓ <?= count($featured['questions']) ?> questions</span>
            <span>📚 <?= count($featured['vocabulary']) ?> vocabulary</span>
          </div>
          <a href="/topic/<?= e($featured['slug']) ?>" class="btn btn--primary">Explore Topic →</a>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ── Tags Cloud ─────────────────────────────────────────────────────────── -->
<?php if (!empty($tags)): ?>
<section class="tags-section" aria-label="Browse by topic">
  <div class="container">
    <div class="section-header">
      <h2 class="section-title">Browse by Theme</h2>
    </div>
    <div class="tags-cloud" role="list">
      <?php foreach ($tags as $tag => $count): ?>
        <a href="/topics?tag=<?= urlencode($tag) ?>" class="tag-link" role="listitem">
          <?= e($tag) ?>
          <span class="tag-count"><?= $count ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ── Recent Topics ──────────────────────────────────────────────────────── -->
<?php if (!empty($recent['items'])): ?>
<section class="topics-section" aria-label="Recent topics">
  <div class="container">
    <div class="section-header">
      <h2 class="section-title">Recent Topics</h2>
      <a href="/topics" class="section-link">View all →</a>
    </div>
    <div class="topic-grid">
      <?php foreach ($recent['items'] as $topic): ?>
        <?php require ROOT_PATH . '/templates/partials/topic_card.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ── Features ───────────────────────────────────────────────────────────── -->
<section class="features-section" aria-label="Platform features">
  <div class="container">
    <div class="section-header center">
      <h2 class="section-title">Built for the Classroom</h2>
      <p class="section-lead">Every topic comes with a full set of interactive tools.</p>
    </div>
    <div class="features-grid">
      <?php
      $features = [
        ['icon' => '🃏', 'title' => 'Vocabulary Flashcards', 'desc' => 'Click any word for definitions, examples, and collocations.'],
        ['icon' => '⏱', 'title' => 'Speaking Timer', 'desc' => 'Built-in countdown for timed speaking activities.'],
        ['icon' => '🎡', 'title' => 'Discussion Wheel', 'desc' => 'Spin to randomly pick discussion questions.'],
        ['icon' => '🎮', 'title' => 'Classroom Games', 'desc' => 'Ready-to-use game suggestions for every topic.'],
        ['icon' => '🖨', 'title' => 'Printable Worksheets', 'desc' => 'One-click print-ready worksheets for class.'],
        ['icon' => '📴', 'title' => 'Works Offline', 'desc' => 'PWA support means topics load even without internet.'],
      ];
      foreach ($features as $f): ?>
        <div class="feature-card glass-card">
          <div class="feature-icon" aria-hidden="true"><?= $f['icon'] ?></div>
          <h3 class="feature-title"><?= $f['title'] ?></h3>
          <p><?= $f['desc'] ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
