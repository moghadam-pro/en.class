<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<?= $meta['head'] ?? '' ?>

<!-- PWA -->
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#0267c1">
<link rel="apple-touch-icon" href="/icons/icon-192.png">

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Barlow+Condensed:wght@600;700&display=swap" rel="stylesheet">

<!-- Tailwind -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        brand: {
          blue:   '#0267c1',
          red:    '#a01332',
          dark:   '#1e212b',
          darker: '#19171e',
          card:   '#262a36',
          muted:  '#3a3f52',
          text:   '#dddddd',
          dim:    '#8b92a8',
        }
      },
      fontFamily: {
        sans:    ['Inter', 'sans-serif'],
        display: ['Barlow Condensed', 'sans-serif'],
      }
    }
  }
}
</script>
<style>
  * { -webkit-tap-highlight-color: transparent; }
  body { background: #1e212b; color: #dddddd; overscroll-behavior-y: none; }
  .safe-bottom { padding-bottom: env(safe-area-inset-bottom, 0px); }
  .pb-nav { padding-bottom: calc(4.5rem + env(safe-area-inset-bottom, 0px)); }
  .no-scroll::-webkit-scrollbar { display: none; }
  .no-scroll { -ms-overflow-style: none; scrollbar-width: none; }
  .badge-a { background:#065f46; color:#6ee7b7; }
  .badge-b { background:#1e3a8a; color:#93c5fd; }
  .badge-c { background:#7c2d12; color:#fdba74; }
  /* Topic detail slide-in */
  #topicDetail { position:fixed; inset:0; z-index:90; background:#1e212b; transform:translateX(100%); transition:transform .3s cubic-bezier(.4,0,.2,1); overflow-y:auto; max-width:448px; margin:0 auto; }
  #topicDetail.open { transform:translateX(0); }
  /* Search overlay */
  #searchOverlay { position:fixed; inset:0; z-index:100; background:#1e212b; transform:translateY(-100%); transition:transform .3s cubic-bezier(.4,0,.2,1); }
  #searchOverlay.open { transform:translateY(0); }
  /* Pill tab active */
  .pill-tab.active { background:#0267c1 !important; color:#fff !important; }
  /* filter chip active */
  .chip.active { background:#0267c1; color:#fff; border-color:#0267c1; }
  /* Nav active */
  .nav-btn.active svg, .nav-btn.active span { color:#0267c1; }
  @keyframes fadeUp { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:translateY(0)} }
  .fade-up { animation: fadeUp .2s ease; }
  /* Tool modal */
  #toolModal { position:fixed; inset:0; z-index:110; display:none; }
  #toolModal.open { display:block; }
</style>

<!-- Alpine.js -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="dark font-sans max-w-md mx-auto relative min-h-screen">

<?php
// Determine active page for nav highlighting
$activePage = $activePage ?? 'home';
?>

<!-- ══════════════════════════════════════════
     SEARCH OVERLAY
══════════════════════════════════════════ -->
<div id="searchOverlay" class="flex flex-col">
  <div class="flex items-center gap-3 px-4 py-3 border-b border-brand-card">
    <button onclick="FDC.closeSearch()" class="p-2 -ml-2 text-brand-dim">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </button>
    <div class="flex-1 flex items-center gap-2 bg-brand-card rounded-xl px-3 border border-brand-muted/30">
      <svg class="w-4 h-4 text-brand-dim flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35" stroke-linecap="round"/></svg>
      <input id="searchInput" type="search" placeholder="Topics, words, events, teachers…"
        class="flex-1 bg-transparent py-3 text-sm text-brand-text placeholder-brand-dim outline-none"
        oninput="FDC.search(this.value)">
    </div>
  </div>
  <div class="flex-1 overflow-y-auto no-scroll">
    <div class="p-4">
      <p id="searchLabel" class="text-brand-dim text-xs uppercase tracking-widest font-semibold mb-3">Recent</p>
      <div id="searchResults" class="space-y-2"></div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════
     TOPIC DETAIL
══════════════════════════════════════════ -->
<div id="topicDetail" class="pb-nav">
  <!-- Sticky header -->
  <div class="sticky top-0 z-10 bg-brand-dark/95 backdrop-blur border-b border-brand-card">
    <div class="flex items-center gap-3 px-4 py-3">
      <button onclick="FDC.closeDetail()" class="p-1 -ml-1 text-brand-dim">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
      </button>
      <div class="flex-1 min-w-0">
        <h1 id="detailTitle" class="font-display text-xl font-bold text-brand-text truncate"></h1>
        <div id="detailMeta" class="flex items-center gap-2 mt-0.5 flex-wrap"></div>
      </div>
      <button id="detailShareBtn" onclick="FDC.shareDetail()" class="p-1 text-brand-dim">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
      </button>
    </div>
    <!-- Pill tabs -->
    <div class="flex gap-2 px-4 pb-3 overflow-x-auto no-scroll">
      <button class="pill-tab active flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full bg-brand-card text-brand-dim transition-all" onclick="FDC.detailTab('questions',this)">Questions</button>
      <button class="pill-tab flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full bg-brand-card text-brand-dim transition-all" onclick="FDC.detailTab('vocab',this)">Vocabulary</button>
      <button class="pill-tab flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full bg-brand-card text-brand-dim transition-all" onclick="FDC.detailTab('phrases',this)">Phrases & Idioms</button>
      <button class="pill-tab flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full bg-brand-card text-brand-dim transition-all" onclick="FDC.detailTab('games',this)">Games</button>
    </div>
  </div>
  <div id="detailBody" class="p-4"></div>
</div>

<!-- ══════════════════════════════════════════
     TOOL MODAL
══════════════════════════════════════════ -->
<div id="toolModal">
  <div class="absolute inset-0 bg-black/60" onclick="FDC.closeTool()"></div>
  <div class="absolute bottom-0 left-0 right-0 max-w-md mx-auto bg-brand-card rounded-t-3xl p-6" style="padding-bottom:calc(1.5rem + env(safe-area-inset-bottom,0px))">
    <div id="toolContent"></div>
  </div>
</div>

<!-- ══════════════════════════════════════════
     MAIN PAGES
══════════════════════════════════════════ -->
<div id="mainShell" class="pb-nav">

  <!-- HOME -->
  <div id="page-home" class="page <?= $activePage==='home'?'block':'hidden' ?>">
    <?php require ROOT_PATH . '/templates/pages/home_content.php'; ?>
  </div>

  <!-- SEARCH -->
  <div id="page-search" class="page <?= $activePage==='search'?'block':'hidden' ?>">
    <?php require ROOT_PATH . '/templates/pages/search_content.php'; ?>
  </div>

  <!-- TOPICS -->
  <div id="page-topics" class="page <?= $activePage==='topics'?'block':'hidden' ?>">
    <?php require ROOT_PATH . '/templates/pages/topics_content.php'; ?>
  </div>

  <!-- TOOLS -->
  <div id="page-tools" class="page <?= $activePage==='tools'?'block':'hidden' ?>">
    <?php require ROOT_PATH . '/templates/pages/tools_content.php'; ?>
  </div>

</div>

<!-- ══════════════════════════════════════════
     BOTTOM NAVIGATION
══════════════════════════════════════════ -->
<nav class="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-brand-darker/95 backdrop-blur border-t border-brand-card z-50 safe-bottom">
  <div class="flex">
    <button onclick="FDC.tab('home',this)" class="nav-btn <?= $activePage==='home'?'active':'' ?> flex-1 flex flex-col items-center gap-0.5 py-3">
      <svg class="w-5 h-5 <?= $activePage==='home'?'text-brand-blue':'text-brand-dim' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
      <span class="text-[10px] font-semibold <?= $activePage==='home'?'text-brand-blue':'text-brand-dim' ?>">Home</span>
    </button>
    <button onclick="FDC.tab('search',this)" class="nav-btn <?= $activePage==='search'?'active':'' ?> flex-1 flex flex-col items-center gap-0.5 py-3">
      <svg class="w-5 h-5 <?= $activePage==='search'?'text-brand-blue':'text-brand-dim' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35" stroke-linecap="round"/></svg>
      <span class="text-[10px] font-semibold <?= $activePage==='search'?'text-brand-blue':'text-brand-dim' ?>">Search</span>
    </button>
    <button onclick="FDC.tab('topics',this)" class="nav-btn <?= $activePage==='topics'?'active':'' ?> flex-1 flex flex-col items-center gap-0.5 py-3">
      <svg class="w-5 h-5 <?= $activePage==='topics'?'text-brand-blue':'text-brand-dim' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
      <span class="text-[10px] font-semibold <?= $activePage==='topics'?'text-brand-blue':'text-brand-dim' ?>">Topics</span>
    </button>
    <button onclick="FDC.tab('tools',this)" class="nav-btn <?= $activePage==='tools'?'active':'' ?> flex-1 flex flex-col items-center gap-0.5 py-3">
      <svg class="w-5 h-5 <?= $activePage==='tools'?'text-brand-blue':'text-brand-dim' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
      <span class="text-[10px] font-semibold <?= $activePage==='tools'?'text-brand-blue':'text-brand-dim' ?>">Tools</span>
    </button>
  </div>
</nav>

<!-- Toast -->
<div id="toast" class="fixed top-4 left-1/2 -translate-x-1/2 z-[200] bg-brand-card text-brand-text text-sm font-medium px-4 py-2 rounded-xl shadow-lg opacity-0 transition-opacity pointer-events-none"></div>

<script src="/js/app.js<?= '?v=' . VERSION ?>"></script>
<?php if (config('seo.google_analytics')): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= e(config('seo.google_analytics')) ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= e(config('seo.google_analytics')) ?>');</script>
<?php endif; ?>
</body>
</html>
