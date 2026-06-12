<?php // templates/pages/topic_content.php
$vocabJson     = json_encode($topic['vocabulary'] ?? [], JSON_UNESCAPED_UNICODE);
$questionsJson = json_encode($topic['questions']  ?? [], JSON_UNESCAPED_UNICODE);
$gamesJson     = json_encode($topic['games']      ?? [], JSON_UNESCAPED_UNICODE);
?>

<div x-data="topicPage(<?= htmlspecialchars($questionsJson, ENT_QUOTES) ?>, <?= htmlspecialchars($vocabJson, ENT_QUOTES) ?>)" class="topic-detail">

<!-- ── Hero / Header ──────────────────────────────────────────────────── -->
<div class="topic-hero">
  <div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="/">Home</a><span>›</span>
      <a href="/topics">Topics</a><span>›</span>
      <span aria-current="page"><?= e($topic['title']) ?></span>
    </nav>

    <div class="topic-hero-inner">
      <div class="topic-meta-row">
        <span class="level-badge level-<?= strtolower(e($topic['level'])) ?> level-badge--lg"><?= e($topic['level']) ?></span>
        <?php foreach (($topic['tags'] ?? []) as $tag): ?>
          <a href="/topics?tag=<?= urlencode($tag) ?>" class="tag-chip"><?= e($tag) ?></a>
        <?php endforeach; ?>
        <span class="topic-date"><?= formatDate($topic['created_at']) ?></span>
      </div>

      <h1 class="topic-title"><?= e($topic['title']) ?></h1>
      <?php if (!empty($topic['summary'])): ?>
        <p class="topic-summary"><?= e($topic['summary']) ?></p>
      <?php endif; ?>

      <!-- Quick action bar -->
      <div class="topic-actions" role="toolbar" aria-label="Topic actions">
        <button @click="printWorksheet()" class="btn btn--ghost btn--sm" title="Print worksheet">
          🖨 Print
        </button>
        <button onclick="toggleFavorite('<?= e($topic['slug']) ?>', this)" data-slug="<?= e($topic['slug']) ?>" class="btn btn--ghost btn--sm fav-btn">
          ♡ Save
        </button>
        <button @click="randomQuestion()" class="btn btn--ghost btn--sm">
          🎲 Random Q
        </button>
        <button @click="copyAll()" class="btn btn--ghost btn--sm">
          📋 Copy all
        </button>
        <div class="topic-stats-mini" aria-label="Topic statistics">
          <span>❓ <?= count($topic['questions']) ?></span>
          <span>📚 <?= count($topic['vocabulary']) ?></span>
          <?php if (!empty($topic['phrases'])): ?><span>💬 <?= count($topic['phrases']) ?></span><?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ── Sticky Tab Nav ─────────────────────────────────────────────────── -->
<div class="topic-tab-nav" role="tablist" aria-label="Topic sections">
  <div class="container">
    <div class="tab-scroller">
      <button @click="activeTab = 'questions'" :class="activeTab === 'questions' && 'active'" class="tab-btn" role="tab" :aria-selected="activeTab === 'questions'" aria-controls="tab-questions">Questions</button>
      <button @click="activeTab = 'vocabulary'" :class="activeTab === 'vocabulary' && 'active'" class="tab-btn" role="tab" :aria-selected="activeTab === 'vocabulary'" aria-controls="tab-vocabulary">Vocabulary</button>
      <?php if (!empty($topic['phrases'])): ?>
      <button @click="activeTab = 'phrases'" :class="activeTab === 'phrases' && 'active'" class="tab-btn" role="tab" :aria-selected="activeTab === 'phrases'" aria-controls="tab-phrases">Phrases</button>
      <?php endif; ?>
      <?php if (!empty($topic['idioms'])): ?>
      <button @click="activeTab = 'idioms'" :class="activeTab === 'idioms' && 'active'" class="tab-btn" role="tab" :aria-selected="activeTab === 'idioms'" aria-controls="tab-idioms">Idioms</button>
      <?php endif; ?>
      <button @click="activeTab = 'activities'" :class="activeTab === 'activities' && 'active'" class="tab-btn" role="tab" :aria-selected="activeTab === 'activities'" aria-controls="tab-activities">Activities</button>
      <button @click="activeTab = 'games'" :class="activeTab === 'games' && 'active'" class="tab-btn" role="tab" :aria-selected="activeTab === 'games'" aria-controls="tab-games">Games</button>
    </div>
  </div>
</div>

<!-- ── Content ────────────────────────────────────────────────────────── -->
<div class="container topic-content-grid">

  <!-- ── Left: Main content ────────────────────────────────────────────── -->
  <div class="topic-main-col">

    <!-- QUESTIONS TAB -->
    <section id="tab-questions" x-show="activeTab === 'questions'" role="tabpanel" aria-labelledby="Questions">
      <div class="section-header">
        <h2 class="section-title">Discussion Questions</h2>
        <button @click="copyQuestions()" class="btn btn--ghost btn--sm">📋 Copy all</button>
      </div>

      <?php if (!empty($topic['questions'])): ?>
      <ol class="question-list" aria-label="Discussion questions">
        <?php foreach ($topic['questions'] as $i => $q):
          $qText = is_array($q) ? ($q['text'] ?? '') : $q;
          $qType = is_array($q) ? ($q['type'] ?? 'open') : 'open';
        ?>
        <li class="question-item" x-data="{copied: false}">
          <div class="question-number" aria-hidden="true"><?= $i + 1 ?></div>
          <div class="question-body">
            <p class="question-text"><?= e($qText) ?></p>
            <div class="question-actions">
              <?php if ($qType): ?>
                <span class="question-type-chip"><?= e($qType) ?></span>
              <?php endif; ?>
              <button @click="copyText('<?= e(addslashes($qText)) ?>'); copied = true; setTimeout(()=>copied=false, 1500)" :class="copied && 'text-green'" class="icon-btn-sm" :title="copied ? 'Copied!' : 'Copy question'" :aria-label="copied ? 'Copied!' : 'Copy question'">
                <span x-text="copied ? '✓' : '📋'"></span>
              </button>
            </div>
          </div>
        </li>
        <?php endforeach; ?>
      </ol>
      <?php endif; ?>
    </section>

    <!-- VOCABULARY TAB -->
    <section id="tab-vocabulary" x-show="activeTab === 'vocabulary'" role="tabpanel" aria-labelledby="Vocabulary">
      <div class="section-header">
        <h2 class="section-title">Vocabulary</h2>
        <div class="vocab-view-toggle" role="group" aria-label="Vocabulary view">
          <button @click="vocabView = 'grid'" :class="vocabView === 'grid' && 'active'" class="view-btn" aria-label="Grid view">⊞</button>
          <button @click="vocabView = 'flash'" :class="vocabView === 'flash' && 'active'" class="view-btn" aria-label="Flashcard view">🃏</button>
        </div>
      </div>

      <?php if (!empty($topic['vocabulary'])): ?>
      <!-- Grid view -->
      <div x-show="vocabView === 'grid'" class="vocab-grid" role="list" aria-label="Vocabulary list">
        <?php foreach ($topic['vocabulary'] as $v):
          $word  = is_array($v) ? ($v['word'] ?? '') : $v;
          $def   = is_array($v) ? ($v['definition'] ?? '') : '';
          $pron  = is_array($v) ? ($v['pronunciation'] ?? '') : '';
          $exs   = is_array($v) ? json_encode($v['examples'] ?? []) : '[]';
          $cols  = is_array($v) ? json_encode($v['collocations'] ?? []) : '[]';
        ?>
        <button class="vocab-card" @click="openVocab(<?= htmlspecialchars(json_encode(['word'=>$word,'definition'=>$def,'pronunciation'=>$pron,'examples'=>(is_array($v) ? $v['examples']??[] : []),'collocations'=>(is_array($v) ? $v['collocations']??[] : [])]), ENT_QUOTES) ?>)" role="listitem" aria-label="<?= e($word) ?> — tap for details">
          <span class="vocab-word-text"><?= e($word) ?></span>
          <?php if ($def): ?><span class="vocab-def-preview"><?= e(truncate($def, 50)) ?></span><?php endif; ?>
          <?php if ($pron): ?><span class="vocab-pron">/<?= e($pron) ?>/</span><?php endif; ?>
        </button>
        <?php endforeach; ?>
      </div>

      <!-- Flashcard view -->
      <div x-show="vocabView === 'flash'" class="flashcard-container">
        <div class="flashcard" :class="cardFlipped && 'flipped'" @click="cardFlipped = !cardFlipped" role="button" :aria-label="'Flashcard ' + (currentCard + 1) + ' of <?= count($topic['vocabulary']) ?>. Click to flip.'" tabindex="0" @keydown.space.prevent="cardFlipped = !cardFlipped" @keydown.enter.prevent="cardFlipped = !cardFlipped">
          <div class="flashcard-front">
            <span class="fc-word" x-text="vocab[currentCard] ? (typeof vocab[currentCard] === 'object' ? vocab[currentCard].word : vocab[currentCard]) : ''"></span>
            <span class="fc-hint">Tap to reveal</span>
          </div>
          <div class="flashcard-back">
            <span class="fc-def" x-text="vocab[currentCard] ? (typeof vocab[currentCard] === 'object' ? vocab[currentCard].definition : '') : ''"></span>
          </div>
        </div>
        <div class="flashcard-controls">
          <button @click="prevCard()" :disabled="currentCard === 0" class="btn btn--ghost btn--sm" aria-label="Previous card">← Prev</button>
          <span class="fc-counter" aria-live="polite"><span x-text="currentCard + 1"></span> / <?= count($topic['vocabulary']) ?></span>
          <button @click="nextCard()" :disabled="currentCard >= vocab.length - 1" class="btn btn--ghost btn--sm" aria-label="Next card">Next →</button>
        </div>
      </div>
      <?php endif; ?>
    </section>

    <!-- PHRASES TAB -->
    <?php if (!empty($topic['phrases'])): ?>
    <section id="tab-phrases" x-show="activeTab === 'phrases'" role="tabpanel">
      <h2 class="section-title">Useful Phrases</h2>
      <ul class="phrase-list">
        <?php foreach ($topic['phrases'] as $phrase): ?>
        <li class="phrase-item" x-data="{copied: false}">
          <span><?= e(is_array($phrase) ? ($phrase['text'] ?? $phrase) : $phrase) ?></span>
          <button @click="copyText('<?= e(addslashes(is_array($phrase) ? ($phrase['text'] ?? $phrase) : $phrase)) ?>'); copied = true; setTimeout(()=>copied=false, 1500)" class="icon-btn-sm" :title="copied ? 'Copied!' : 'Copy'">
            <span x-text="copied ? '✓' : '📋'"></span>
          </button>
        </li>
        <?php endforeach; ?>
      </ul>
    </section>
    <?php endif; ?>

    <!-- IDIOMS TAB -->
    <?php if (!empty($topic['idioms'])): ?>
    <section id="tab-idioms" x-show="activeTab === 'idioms'" role="tabpanel">
      <h2 class="section-title">Idioms &amp; Expressions</h2>
      <ul class="phrase-list">
        <?php foreach ($topic['idioms'] as $idiom): ?>
        <li class="phrase-item"><?= e(is_array($idiom) ? ($idiom['text'] ?? $idiom) : $idiom) ?></li>
        <?php endforeach; ?>
      </ul>
    </section>
    <?php endif; ?>

    <!-- ACTIVITIES TAB -->
    <section id="tab-activities" x-show="activeTab === 'activities'" role="tabpanel">
      <h2 class="section-title">Classroom Activities</h2>

      <!-- Discussion Wheel -->
      <div class="activity-card glass-card">
        <h3 class="activity-title">🎡 Discussion Wheel</h3>
        <p>Spin to randomly pick a question for your students.</p>
        <div class="wheel-container" aria-live="polite">
          <div class="wheel" id="discussion-wheel" aria-hidden="true">
            <div class="wheel-inner">💬</div>
          </div>
          <p class="wheel-result" x-show="wheelQuestion" x-text="wheelQuestion" role="status"></p>
          <button @click="spinWheel()" class="btn btn--primary">🎡 Spin</button>
        </div>
      </div>

      <!-- Speaking Timer -->
      <div class="activity-card glass-card">
        <h3 class="activity-title">⏱ Speaking Timer</h3>
        <p>Give students time-limited speaking practice.</p>
        <div class="timer-controls" role="group" aria-label="Speaking timer">
          <div class="timer-display" aria-live="polite" :class="timerRunning && 'timer--running'">
            <span x-text="formatTime(timerSeconds)" class="timer-digits"></span>
          </div>
          <div class="timer-presets" role="group" aria-label="Timer presets">
            <?php foreach ([30, 60, 90, 120, 180] as $sec): ?>
              <button @click="setTimer(<?= $sec ?>)" class="preset-btn" aria-label="<?= $sec ?> seconds"><?= $sec < 60 ? $sec . 's' : ($sec/60) . 'm' ?></button>
            <?php endforeach; ?>
          </div>
          <div class="timer-btns">
            <button @click="startTimer()" x-show="!timerRunning" class="btn btn--primary">▶ Start</button>
            <button @click="pauseTimer()" x-show="timerRunning" class="btn btn--outline">⏸ Pause</button>
            <button @click="resetTimer()" class="btn btn--ghost">↺ Reset</button>
          </div>
        </div>
      </div>

      <!-- Role-play -->
      <?php if (!empty($topic['roleplay'])): ?>
      <div class="activity-card glass-card">
        <h3 class="activity-title">🎭 Role-Play Activities</h3>
        <?php foreach ($topic['roleplay'] as $rp): ?>
          <div class="roleplay-item">
            <strong><?= e(is_array($rp) ? ($rp['scenario'] ?? '') : $rp) ?></strong>
            <?php if (is_array($rp) && !empty($rp['roles'])): ?>
              <div class="roles-list">
                <?php foreach ($rp['roles'] as $role): ?>
                  <span class="role-chip"><?= e($role) ?></span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <!-- Quotes -->
      <?php if (!empty($topic['quotes'])): ?>
      <div class="activity-card glass-card">
        <h3 class="activity-title">💡 Discussion Starters</h3>
        <?php foreach ($topic['quotes'] as $quote): ?>
          <blockquote class="quote-block"><?= e(is_array($quote) ? ($quote['text'] ?? $quote) : $quote) ?></blockquote>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </section>

    <!-- GAMES TAB -->
    <section id="tab-games" x-show="activeTab === 'games'" role="tabpanel">
      <h2 class="section-title">Classroom Games</h2>
      <?php if (!empty($topic['games'])): ?>
        <div class="games-grid">
          <?php foreach ($topic['games'] as $game): ?>
          <div class="game-card glass-card">
            <div class="game-header">
              <h3 class="game-name"><?= e(is_array($game) ? ($game['name'] ?? '') : $game) ?></h3>
              <?php if (is_array($game)): ?>
                <div class="game-meta">
                  <?php if (!empty($game['players'])): ?><span>👥 <?= e($game['players']) ?></span><?php endif; ?>
                  <?php if (!empty($game['time'])): ?><span>⏱ <?= e($game['time']) ?></span><?php endif; ?>
                </div>
              <?php endif; ?>
            </div>
            <?php if (is_array($game) && !empty($game['description'])): ?>
              <p><?= e($game['description']) ?></p>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

  </div><!-- /topic-main-col -->

  <!-- ── Right: Sidebar ────────────────────────────────────────────────── -->
  <aside class="topic-sidebar-col">

    <!-- Teacher Notes -->
    <?php if (!empty($topic['teacher_notes'])): ?>
    <div class="sidebar-card glass-card">
      <h3 class="sidebar-title">📝 Teacher Notes</h3>
      <p><?= e($topic['teacher_notes']) ?></p>
    </div>
    <?php endif; ?>

    <!-- Collocations -->
    <?php if (!empty($topic['collocations'])): ?>
    <div class="sidebar-card glass-card">
      <h3 class="sidebar-title">🔗 Key Collocations</h3>
      <ul class="collocation-list">
        <?php foreach ($topic['collocations'] as $col): ?>
          <li><?= e(is_array($col) ? ($col['text'] ?? $col) : $col) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>

    <!-- Random Question generator -->
    <div class="sidebar-card glass-card" x-data>
      <h3 class="sidebar-title">🎲 Random Question</h3>
      <p class="random-q-display" x-text="currentRandomQ" aria-live="polite">Click below to get a random question.</p>
      <button @click="randomQuestion()" class="btn btn--primary btn--sm w-full">Get Question</button>
    </div>

    <!-- Quick share -->
    <div class="sidebar-card glass-card">
      <h3 class="sidebar-title">📤 Share Topic</h3>
      <button onclick="shareUrl('<?= e(config('app.url') . '/topic/' . $topic['slug']) ?>', '<?= e(addslashes($topic['title'])) ?>')" class="btn btn--outline btn--sm w-full">Share this topic</button>
    </div>

  </aside>

</div><!-- /container -->

<!-- ── Related Topics ─────────────────────────────────────────────────── -->
<?php if (!empty($related)): ?>
<section class="related-section" aria-label="Related topics">
  <div class="container">
    <h2 class="section-title">Related Topics</h2>
    <div class="topic-grid topic-grid--4">
      <?php foreach ($related as $topic): ?>
        <?php require ROOT_PATH . '/templates/partials/topic_card.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

</div><!-- /topic-detail -->

<!-- Print-only content -->
<div class="print-worksheet" aria-hidden="true">
  <div class="print-header">
    <h1><?= e($topic['title']) ?> — Discussion Worksheet</h1>
    <p><?= e(config('app.url')) ?>/topic/<?= e($topic['slug']) ?> | <?= date('F j, Y') ?></p>
  </div>
  <section>
    <h2>Discussion Questions</h2>
    <ol>
      <?php foreach ($topic['questions'] ?? [] as $q): ?>
        <li><?= e(is_array($q) ? ($q['text'] ?? '') : $q) ?></li>
      <?php endforeach; ?>
    </ol>
  </section>
  <?php if (!empty($topic['vocabulary'])): ?>
  <section>
    <h2>Vocabulary</h2>
    <ul>
      <?php foreach ($topic['vocabulary'] as $v): ?>
        <li><?= e(is_array($v) ? ($v['word'] ?? '') : $v) ?><?= (is_array($v) && !empty($v['definition'])) ? ': ' . e($v['definition']) : '' ?></li>
      <?php endforeach; ?>
    </ul>
  </section>
  <?php endif; ?>
</div>
