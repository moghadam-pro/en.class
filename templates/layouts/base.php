<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?= $meta['head'] ?? '' ?>

<!-- PWA -->
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#0267c1">
<link rel="apple-touch-icon" href="/icons/icon-192.png">

<!-- Preconnect -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">

<link rel="stylesheet" href="/css/app.css<?= '?v=' . VERSION ?>">

<!-- Alpine.js — must load before app.js components register -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body x-data="appShell()" :class="{'reading-mode': readingMode}" class="bg-surface text-body antialiased">

<!-- Skip to content -->
<a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:bg-blue focus:text-white focus:px-4 focus:py-2 focus:rounded">
  Skip to content
</a>

<!-- ── Navigation ──────────────────────────────────────────────────────── -->
<nav class="nav-bar" role="navigation" aria-label="Main navigation">
  <div class="nav-inner">

    <!-- Logo -->
    <a href="/" class="nav-logo" aria-label="Free Discussion Class — Home">
      <span class="logo-icon" aria-hidden="true">💬</span>
      <span class="logo-text">
        <strong>Free</strong> Discussion
        <small>Class</small>
      </span>
    </a>

    <!-- Desktop Links -->
    <ul class="nav-links" role="list">
      <li><a href="/" class="nav-link <?= ($activePage ?? '') === 'home' ? 'active' : '' ?>">Home</a></li>
      <li><a href="/topics" class="nav-link <?= ($activePage ?? '') === 'topics' ? 'active' : '' ?>">Topics</a></li>
      <li><a href="/random" class="nav-link nav-link--special">🎲 Random</a></li>
    </ul>

    <!-- Right actions -->
    <div class="nav-actions">
      <!-- Search trigger -->
      <button @click="openSearch()" class="icon-btn" aria-label="Search topics">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      </button>

      <!-- Reading mode -->
      <button @click="toggleReadingMode()" class="icon-btn" :aria-label="readingMode ? 'Exit reading mode' : 'Enter reading mode'" :title="readingMode ? 'Exit reading mode' : 'Reading mode'">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
      </button>

      <!-- Mobile hamburger -->
      <button @click="navOpen = !navOpen" class="icon-btn md:hidden" :aria-expanded="navOpen" aria-controls="mobile-menu" aria-label="Toggle menu">
        <svg x-show="!navOpen" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        <svg x-show="navOpen" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
  </div>

  <!-- Mobile menu -->
  <div id="mobile-menu" x-show="navOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="mobile-menu" @click.outside="navOpen = false">
    <a href="/" class="mobile-link" @click="navOpen = false">🏠 Home</a>
    <a href="/topics" class="mobile-link" @click="navOpen = false">📚 Topics</a>
    <a href="/random" class="mobile-link" @click="navOpen = false">🎲 Random Topic</a>
  </div>
</nav>

<!-- ── Global Search Overlay ───────────────────────────────────────────── -->
<div x-show="searchOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="search-overlay" @keydown.escape.window="closeSearch()" role="dialog" aria-modal="true" aria-label="Search">
  <div class="search-backdrop" @click="closeSearch()"></div>
  <div class="search-box">
    <div class="search-input-wrap">
      <svg class="search-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      <input type="search" x-ref="searchInput" x-model="searchQuery" @input.debounce.300="doSearch()" @keydown.escape="closeSearch()" placeholder="Search topics, vocabulary, questions…" class="search-input" autocomplete="off" spellcheck="false">
      <button @click="closeSearch()" class="search-close" aria-label="Close search">ESC</button>
    </div>
    <!-- Results -->
    <div x-show="searchResults.length > 0" class="search-results" role="listbox">
      <template x-for="r in searchResults" :key="r.slug">
        <a :href="'/topic/' + r.slug" class="search-result-item" role="option" @click="closeSearch()">
          <div class="result-meta">
            <span class="level-badge" :class="'level-' + r.level.toLowerCase()" x-text="r.level"></span>
            <span x-text="(r.tags || []).slice(0,2).join(' · ')"></span>
          </div>
          <div class="result-title" x-text="r.title"></div>
          <p class="result-summary" x-text="r.summary.slice(0, 100) + '...'"></p>
        </a>
      </template>
    </div>
    <div x-show="searchQuery.length > 1 && searchResults.length === 0 && !searchLoading" class="search-empty">
      No topics found for "<span x-text="searchQuery"></span>"
    </div>
    <div x-show="searchLoading" class="search-loading">Searching…</div>
  </div>
</div>

<!-- ── Main Content ────────────────────────────────────────────────────── -->
<main id="main" tabindex="-1">
  <?php require ROOT_PATH . '/templates/' . ($contentTemplate ?? 'pages/home') . '.php'; ?>
</main>

<!-- ── Footer ─────────────────────────────────────────────────────────── -->
<footer class="site-footer">
  <div class="footer-inner">
    <div class="footer-brand">
      <span class="logo-icon">💬</span>
      <div>
        <strong>Free Discussion Class</strong>
        <p>Interactive ESL discussion topics for classrooms worldwide.</p>
      </div>
    </div>
    <div class="footer-links">
      <a href="/topics">Browse Topics</a>
      <a href="/random">Random Topic</a>
      <a href="/sitemap.xml">Sitemap</a>
    </div>
    <p class="footer-copy">© <?= date('Y') ?> <?= e(config('app.domain')) ?></p>
  </div>
</footer>

<!-- ── Vocabulary Modal ────────────────────────────────────────────────── -->
<div x-show="vocabModal.open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="modal-overlay" @keydown.escape.window="vocabModal.open = false" role="dialog" aria-modal="true" :aria-label="'Vocabulary: ' + vocabModal.word">
  <div class="modal-backdrop" @click="vocabModal.open = false"></div>
  <div class="modal-card vocab-modal">
    <button @click="vocabModal.open = false" class="modal-close" aria-label="Close">
      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <div class="vocab-header">
      <h2 class="vocab-word" x-text="vocabModal.word"></h2>
      <span class="vocab-pronunciation" x-show="vocabModal.pronunciation" x-text="'/' + vocabModal.pronunciation + '/'"></span>
    </div>
    <p class="vocab-definition" x-text="vocabModal.definition"></p>
    <div x-show="vocabModal.examples && vocabModal.examples.length > 0" class="vocab-examples">
      <h3>Examples</h3>
      <ul>
        <template x-for="ex in vocabModal.examples"><li x-text="ex"></li></template>
      </ul>
    </div>
    <div x-show="vocabModal.collocations && vocabModal.collocations.length > 0" class="vocab-collocations">
      <h3>Collocations</h3>
      <div class="collocation-chips">
        <template x-for="col in vocabModal.collocations"><span class="chip" x-text="col"></span></template>
      </div>
    </div>
    <button @click="copyVocab()" class="btn btn--outline btn--sm mt-4">📋 Copy</button>
  </div>
</div>

<!-- ── Toast ───────────────────────────────────────────────────────────── -->
<div x-show="toast.show" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="toast" role="alert" aria-live="polite" x-text="toast.message"></div>

<script src="/js/app.js<?= '?v=' . VERSION ?>"></script>
<?php if (config('seo.google_analytics')): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= e(config('seo.google_analytics')) ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= e(config('seo.google_analytics')) ?>');</script>
<?php endif; ?>
</body>
</html>
