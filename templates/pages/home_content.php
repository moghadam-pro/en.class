<?php // templates/pages/home_content.php ?>

<!-- Minimal header: logo + search only -->
<header class="flex items-center justify-between px-4 pt-5 pb-3">
  <div class="flex items-center gap-2">
    <!-- Replace with your own SVG logo -->
    <svg width="34" height="34" viewBox="0 0 34 34" fill="none">
      <rect width="34" height="34" rx="9" fill="#0267c1"/>
      <path d="M8 11h11a6 6 0 010 12H8V11z" fill="#dddddd"/>
      <circle cx="26" cy="23" r="3.5" fill="#a01332"/>
    </svg>
    <span class="font-display text-xl font-bold tracking-wide text-brand-text">FDC</span>
  </div>
  <button onclick="FDC.openSearch()" class="w-9 h-9 rounded-xl bg-brand-card flex items-center justify-center text-brand-dim border border-brand-muted/30">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35" stroke-linecap="round"/></svg>
  </button>
</header>

<!-- Hero -->
<section class="px-4 pt-2 pb-6">
  <h2 class="font-display text-4xl font-bold text-brand-text leading-tight mb-1">
    Speak<br><span class="text-brand-blue">Confidently.</span>
  </h2>
  <p class="text-brand-dim text-sm leading-relaxed mb-6 max-w-xs">
    Free Discussion Class gives ESL learners and teachers a library of rich discussion topics — with vocabulary tools, speaking games, and live events — all in one place.
  </p>

  <!-- Today's topic hero card -->
  <?php if (!empty($featured)): ?>
  <div class="relative cursor-pointer" onclick="FDC.openTopicBySlug('<?= e($featured['slug']) ?>')">
    <!-- Card stack effect -->
    <div class="absolute inset-0 rounded-2xl bg-brand-card rotate-[-2.5deg] opacity-40 translate-y-1.5"></div>
    <div class="absolute inset-0 rounded-2xl bg-brand-card rotate-[1deg] opacity-60 translate-y-0.5"></div>
    <!-- Main card -->
    <div class="relative bg-brand-card rounded-2xl p-5 border border-brand-muted/30 active:scale-[0.98] transition-transform">
      <div class="flex items-center justify-between mb-3">
        <span class="text-xs font-semibold text-brand-blue uppercase tracking-widest">Today's Topic</span>
        <span class="<?= levelBadgeClass($featured['level']) ?> text-[10px] font-bold px-2 py-0.5 rounded-full"><?= e($featured['level']) ?></span>
      </div>
      <h3 class="font-display text-2xl font-bold text-brand-text"><?= e($featured['title']) ?></h3>
      <p class="text-brand-dim text-xs mt-1 mb-4 line-clamp-2"><?= e($featured['summary']) ?></p>
      <div class="flex items-center justify-between">
        <div class="flex gap-3 text-xs text-brand-dim">
          <span>💬 <?= count($featured['questions']) ?> questions</span>
          <span>📚 <?= count($featured['vocabulary']) ?> words</span>
        </div>
        <span class="text-brand-blue text-xs font-semibold">Open →</span>
      </div>
    </div>
  </div>
  <?php endif; ?>
</section>

<!-- Latest Topics -->
<?php if (!empty($recent['items'])): ?>
<section class="mb-6">
  <div class="flex items-center justify-between px-4 mb-3">
    <h2 class="font-display text-lg font-bold text-brand-text">Latest Topics</h2>
    <button onclick="FDC.tab('topics')" class="text-brand-blue text-xs font-semibold">See all</button>
  </div>
  <div class="flex gap-3 px-4 overflow-x-auto no-scroll pb-1">
    <?php foreach ($recent['items'] as $t): ?>
    <div class="flex-shrink-0 w-52 bg-brand-card rounded-2xl p-4 border border-brand-muted/20 cursor-pointer active:scale-95 transition-transform"
         onclick="FDC.openTopicBySlug('<?= e($t['slug']) ?>')">
      <div class="flex items-center justify-between mb-2">
        <span class="<?= levelBadgeClass($t['level']) ?> text-[10px] font-bold px-2 py-0.5 rounded-full"><?= e($t['level']) ?></span>
        <span class="text-brand-dim text-[10px]"><?= $t['views'] ?? 0 ?> views</span>
      </div>
      <h3 class="font-display text-base font-bold text-brand-text mb-1"><?= e($t['title']) ?></h3>
      <p class="text-brand-dim text-xs line-clamp-2 mb-3"><?= e($t['summary']) ?></p>
      <div class="flex gap-1.5 flex-wrap">
        <?php foreach (array_slice($t['tags'] ?? [], 0, 2) as $tag): ?>
        <span class="text-[10px] bg-brand-muted/40 text-brand-dim px-2 py-0.5 rounded-full"><?= e($tag) ?></span>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- Events -->
<section class="px-4 mb-6">
  <div class="flex items-center justify-between mb-3">
    <h2 class="font-display text-lg font-bold text-brand-text">Events</h2>
    <span class="text-brand-dim text-xs">This week</span>
  </div>
  <div id="eventsList" class="space-y-2">
    <!-- Placeholder events — replace with PHP data when events system is built -->
    <?php
    $placeholderEvents = [
      ['title'=>'Online Speaking Club','type'=>'online','date'=>'Wed, Jun 18','time'=>'7:00 PM','teacher'=>'Sara M.','level'=>'B1–B2','spots'=>6],
      ['title'=>'Advanced Discussion Workshop','type'=>'in-person','date'=>'Sat, Jun 21','time'=>'10:00 AM','teacher'=>'David K.','level'=>'C1','spots'=>2],
    ];
    foreach ($placeholderEvents as $ev):
      $isOnline = $ev['type'] === 'online';
    ?>
    <div class="bg-brand-card rounded-2xl p-4 border border-brand-muted/20 flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl flex-shrink-0 flex items-center justify-center <?= $isOnline ? 'bg-brand-blue/20' : 'bg-brand-red/20' ?>">
        <?php if ($isOnline): ?>
        <svg class="w-5 h-5 text-brand-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
        <?php else: ?>
        <svg class="w-5 h-5 text-brand-red" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        <?php endif; ?>
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-brand-text font-semibold text-sm truncate"><?= e($ev['title']) ?></p>
        <p class="text-brand-dim text-xs"><?= e($ev['date']) ?> · <?= e($ev['time']) ?> · <?= e($ev['teacher']) ?></p>
      </div>
      <div class="text-right flex-shrink-0">
        <p class="text-brand-dim text-xs"><?= $ev['spots'] ?> spots</p>
        <button class="text-brand-blue text-xs font-semibold mt-0.5">Join</button>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<?php
// Helper for level badge class
function levelBadgeClass(string $level): string {
  $l = strtoupper($level);
  if (in_array($l, ['A1','A2'])) return 'badge-a';
  if (in_array($l, ['B1','B2'])) return 'badge-b';
  return 'badge-c';
}
?>
