<?php // templates/pages/tools_content.php ?>

<header class="px-4 pt-5 pb-4">
  <h1 class="font-display text-2xl font-bold text-brand-text">Tools</h1>
  <p class="text-brand-dim text-sm mt-1">Classroom games and activities</p>
</header>

<div class="px-4 grid grid-cols-2 gap-3 pb-4">

  <button onclick="FDC.openTool('wheel')"
    class="bg-brand-card rounded-2xl p-4 text-left border border-brand-muted/20 active:scale-95 transition-transform">
    <div class="w-10 h-10 rounded-xl bg-brand-blue/20 flex items-center justify-center mb-3">
      <svg class="w-5 h-5 text-brand-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 2v10l6 3" stroke-linecap="round"/></svg>
    </div>
    <p class="text-brand-text font-semibold text-sm">Question Wheel</p>
    <p class="text-brand-dim text-xs mt-0.5">Random discussion picker</p>
  </button>

  <button onclick="FDC.openTool('timer')"
    class="bg-brand-card rounded-2xl p-4 text-left border border-brand-muted/20 active:scale-95 transition-transform">
    <div class="w-10 h-10 rounded-xl bg-brand-red/20 flex items-center justify-center mb-3">
      <svg class="w-5 h-5 text-brand-red" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
    </div>
    <p class="text-brand-text font-semibold text-sm">Speaking Timer</p>
    <p class="text-brand-dim text-xs mt-0.5">Timed turn-taking</p>
  </button>

  <div class="bg-brand-card rounded-2xl p-4 border border-brand-muted/20 opacity-40">
    <div class="w-10 h-10 rounded-xl bg-brand-muted/30 flex items-center justify-center mb-3">
      <svg class="w-5 h-5 text-brand-dim" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
    </div>
    <p class="text-brand-text font-semibold text-sm">Flashcards</p>
    <p class="text-brand-dim text-xs mt-0.5">Coming soon</p>
  </div>

  <div class="bg-brand-card rounded-2xl p-4 border border-brand-muted/20 opacity-40">
    <div class="w-10 h-10 rounded-xl bg-brand-muted/30 flex items-center justify-center mb-3">
      <svg class="w-5 h-5 text-brand-dim" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
    </div>
    <p class="text-brand-text font-semibold text-sm">Hot Seat</p>
    <p class="text-brand-dim text-xs mt-0.5">Coming soon</p>
  </div>

</div>
