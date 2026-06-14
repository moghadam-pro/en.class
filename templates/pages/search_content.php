<?php // templates/pages/search_content.php ?>

<header class="px-4 pt-5 pb-3">
  <h1 class="font-display text-2xl font-bold text-brand-text mb-3">Search</h1>
  <div class="flex items-center gap-2 bg-brand-card rounded-xl px-3 border border-brand-muted/30">
    <svg class="w-4 h-4 text-brand-dim flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35" stroke-linecap="round"/></svg>
    <input id="pageSearchInput" type="search" placeholder="Topics, words, events, teachers…"
      class="flex-1 bg-transparent py-3 text-sm text-brand-text placeholder-brand-dim outline-none"
      oninput="FDC.search(this.value,'page')">
  </div>
</header>

<div class="px-4 pb-4" id="pageSearchResults">
  <p class="text-brand-dim text-sm text-center pt-16 opacity-60">Start typing to search everything</p>
</div>
