/**
 * Free Discussion Class — app.js  v2.0
 * Mobile-first JS for Tailwind-based UI
 */

// ═══════════════════════════════════════════════════════════
// DATA CACHE
// ═══════════════════════════════════════════════════════════
const FDC = (() => {

let _allTopics = [];          // populated from window.FDC_TOPICS or API
let _currentTopic = null;
let _timerInterval = null;
let _timerSeconds = 120;
let _timerRunning = false;

// ── Bootstrap ─────────────────────────────────────────────
async function init() {
  // Load topic index from embedded data or fetch from API
  if (window.FDC_TOPICS) {
    _allTopics = window.FDC_TOPICS;
  } else {
    try {
      const res = await fetch('/api/topics');
      const data = await res.json();
      _allTopics = data.topics || [];
    } catch(e) {}
  }

  // If a specific topic was embedded (direct URL), auto-open it
  if (window.FDC_CURRENT_TOPIC) {
    openDetail(window.FDC_CURRENT_TOPIC);
  }

  // Render topics grid if on topics page
  if (document.getElementById('topicsGrid')) renderTopicsGrid(_allTopics);

  // Init search defaults
  renderSearchDefaults();

  // Service Worker
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js').catch(() => {});
  }
}

// ═══════════════════════════════════════════════════════════
// TAB NAVIGATION
// ═══════════════════════════════════════════════════════════
function tab(name, btn) {
  document.querySelectorAll('.page').forEach(p => {
    p.classList.add('hidden');
    p.classList.remove('block');
  });
  const page = document.getElementById('page-' + name);
  if (page) { page.classList.remove('hidden'); page.classList.add('block'); }

  // Update nav buttons
  document.querySelectorAll('.nav-btn').forEach(b => {
    b.classList.remove('active');
    b.querySelectorAll('svg, span').forEach(el => {
      el.classList.remove('text-brand-blue');
      el.classList.add('text-brand-dim');
    });
  });
  const activeBtn = btn || document.querySelector(`.nav-btn[onclick*="'${name}'"]`);
  if (activeBtn) {
    activeBtn.classList.add('active');
    activeBtn.querySelectorAll('svg, span').forEach(el => {
      el.classList.add('text-brand-blue');
      el.classList.remove('text-brand-dim');
    });
  }

  // Render topics if switching to that tab
  if (name === 'topics') renderTopicsGrid(_allTopics);
}

// ═══════════════════════════════════════════════════════════
// TOPIC DETAIL
// ═══════════════════════════════════════════════════════════
function openTopicBySlug(slug) {
  // Try local cache first
  const cached = _allTopics.find(t => t.slug === slug);
  if (cached && cached.questions) { openDetail(cached); return; }

  // Fetch full topic data
  fetch('/api/topics/' + slug)
    .then(r => r.json())
    .then(data => openDetail(data))
    .catch(() => toast('Could not load topic'));
}

function openDetail(topic) {
  _currentTopic = topic;

  document.getElementById('detailTitle').textContent = topic.title;
  document.getElementById('detailMeta').innerHTML = `
    <span class="${levelBadgeClass(topic.level)} text-[10px] font-bold px-2 py-0.5 rounded-full">${topic.level}</span>
    ${(topic.tags||[]).slice(0,2).map(t=>`<span class="text-[10px] text-brand-dim">#${t}</span>`).join('')}
  `;

  // Reset to first tab
  document.querySelectorAll('#topicDetail .pill-tab').forEach((b,i) => {
    b.classList.toggle('active', i === 0);
  });
  detailTab('questions');

  document.getElementById('topicDetail').classList.add('open');
  document.body.style.overflow = 'hidden';

  // Update browser URL without page reload
  history.pushState({ topic: topic.slug }, '', '/topic/' + topic.slug);
}

function closeDetail() {
  document.getElementById('topicDetail').classList.remove('open');
  document.body.style.overflow = '';
  history.pushState({}, '', '/topics');
}

function detailTab(name, btn) {
  if (btn) {
    document.querySelectorAll('#topicDetail .pill-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
  }
  const t = _currentTopic;
  if (!t) return;
  const body = document.getElementById('detailBody');
  body.innerHTML = '';

  let html = '';
  if (name === 'questions') {
    html = (t.questions || []).map((q, i) => `
      <div class="bg-brand-card rounded-2xl p-4 border border-brand-muted/20 mb-3">
        <div class="flex items-start gap-3">
          <span class="text-brand-blue font-display font-bold text-lg flex-shrink-0 leading-none pt-0.5">${String(i+1).padStart(2,'0')}</span>
          <p class="text-brand-text text-sm leading-relaxed">${esc(q.text)}</p>
        </div>
        <div class="flex items-center gap-2 mt-3 ml-8">
          <span class="${levelBadgeClass(q.level||'B1')} text-[10px] font-bold px-2 py-0.5 rounded-full">${q.level||''}</span>
          <span class="text-brand-dim text-[10px] capitalize">${q.type||''}</span>
          <button onclick="copyText('${esc(q.text).replace(/'/g,"\\'")}',event)" class="ml-auto text-brand-dim text-[10px] flex items-center gap-1">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
            Copy
          </button>
        </div>
      </div>`).join('');
  } else if (name === 'vocab') {
    html = (t.vocabulary || []).map(v => `
      <div class="bg-brand-card rounded-2xl p-4 border border-brand-muted/20 mb-3">
        <div class="flex items-baseline justify-between mb-1">
          <h4 class="font-display text-lg font-bold text-brand-text">${esc(v.word)}</h4>
          <span class="text-brand-dim text-xs font-mono">${esc(v.pronunciation||'')}</span>
        </div>
        <p class="text-brand-dim text-xs mb-2">${esc(v.definition)}</p>
        ${(v.examples||[]).length ? `<p class="text-brand-text/70 text-xs italic border-l-2 border-brand-blue/40 pl-3">"${esc(v.examples[0])}"</p>` : ''}
        ${(v.collocations||[]).length ? `<div class="flex flex-wrap gap-1.5 mt-2">${v.collocations.map(c=>`<span class="text-[10px] bg-brand-muted/40 text-brand-dim px-2 py-0.5 rounded-full">${esc(c)}</span>`).join('')}</div>` : ''}
      </div>`).join('');
  } else if (name === 'phrases') {
    const phrases = (t.phrases || []).map(p => `<div class="bg-brand-card rounded-xl p-3 text-brand-text text-sm font-medium border border-brand-muted/20 mb-2">${esc(p)}</div>`).join('');
    const idioms  = (t.idioms  || []).map(p => `<div class="bg-brand-card rounded-xl p-3 text-brand-text text-sm font-medium border border-brand-red/20 mb-2">${esc(p)}</div>`).join('');
    html = `
      ${phrases.length ? `<p class="text-brand-dim text-xs uppercase tracking-widest font-semibold mb-2">Phrases</p>${phrases}` : ''}
      ${idioms.length  ? `<p class="text-brand-dim text-xs uppercase tracking-widest font-semibold mb-2 mt-4">Idioms</p>${idioms}` : ''}
      ${!phrases.length && !idioms.length ? '<p class="text-brand-dim text-sm text-center pt-8">No phrases added yet</p>' : ''}`;
  } else if (name === 'games') {
    html = (t.games || []).map(g => `
      <div class="bg-brand-card rounded-2xl p-4 border border-brand-muted/20 mb-3">
        <div class="flex items-center justify-between mb-2">
          <h4 class="font-semibold text-brand-text">${esc(g.name)}</h4>
          <span class="text-brand-dim text-xs">${esc(g.time||'')}</span>
        </div>
        <p class="text-brand-dim text-sm leading-relaxed">${esc(g.description)}</p>
        ${g.players ? `<p class="text-brand-dim text-xs mt-2">👥 ${esc(g.players)}</p>` : ''}
      </div>`).join('') || '<p class="text-brand-dim text-sm text-center pt-8">No games added yet</p>';
  }

  body.innerHTML = `<div class="fade-up">${html}</div>`;
}

function shareDetail() {
  if (!_currentTopic) return;
  const url = location.origin + '/topic/' + _currentTopic.slug;
  if (navigator.share) {
    navigator.share({ title: _currentTopic.title, url });
  } else {
    navigator.clipboard.writeText(url).then(() => toast('Link copied!'));
  }
}

// ═══════════════════════════════════════════════════════════
// SEARCH
// ═══════════════════════════════════════════════════════════
function openSearch() {
  document.getElementById('searchOverlay').classList.add('open');
  setTimeout(() => document.getElementById('searchInput')?.focus(), 300);
  renderSearchDefaults();
}

function closeSearch() {
  document.getElementById('searchOverlay').classList.remove('open');
}

let _searchTimer = null;
function search(query, target = 'overlay') {
  clearTimeout(_searchTimer);
  _searchTimer = setTimeout(() => _doSearch(query, target), 200);
}

function _doSearch(query, target) {
  const isOverlay = target === 'overlay';
  const container = isOverlay
    ? document.getElementById('searchResults')
    : document.getElementById('pageSearchResults');
  const label = document.getElementById('searchLabel');
  if (!container) return;

  if (!query.trim()) {
    if (isOverlay) { renderSearchDefaults(); return; }
    container.innerHTML = '<p class="text-brand-dim text-sm text-center pt-16 opacity-60">Start typing to search everything</p>';
    return;
  }

  const q = query.toLowerCase();
  const matchedTopics = _allTopics.filter(t =>
    t.title?.toLowerCase().includes(q) ||
    t.summary?.toLowerCase().includes(q) ||
    (t.tags||[]).some(tag => tag.includes(q)) ||
    (t.vocabulary||[]).some(v => v.word?.toLowerCase().includes(q))
  );

  if (label) label.textContent = matchedTopics.length + ' results';

  if (!matchedTopics.length) {
    container.innerHTML = '<p class="text-brand-dim text-sm text-center pt-8">No results for "' + esc(query) + '"</p>';
    return;
  }

  container.innerHTML = matchedTopics.map(t => `
    <div class="flex items-center gap-3 p-3 rounded-xl bg-brand-card mb-2 cursor-pointer active:opacity-70 transition-opacity"
         onclick="${isOverlay ? 'FDC.closeSearch();' : ''}FDC.openTopicBySlug('${t.slug}')">
      <div class="w-8 h-8 rounded-lg bg-brand-blue/20 flex items-center justify-center flex-shrink-0">
        <svg class="w-4 h-4 text-brand-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-brand-text text-sm font-semibold truncate">${esc(t.title)}</p>
        <p class="text-brand-dim text-xs">${t.level} · ${(t.tags||[]).slice(0,2).join(', ')}</p>
      </div>
    </div>`).join('');
}

function renderSearchDefaults() {
  const el = document.getElementById('searchResults');
  const label = document.getElementById('searchLabel');
  if (!el) return;
  if (label) label.textContent = 'All Topics';
  el.innerHTML = _allTopics.slice(0, 6).map(t => `
    <div class="flex items-center gap-3 p-3 rounded-xl bg-brand-card mb-2 cursor-pointer active:opacity-70 transition-opacity"
         onclick="FDC.closeSearch();FDC.openTopicBySlug('${t.slug}')">
      <div class="w-8 h-8 rounded-lg bg-brand-blue/20 flex items-center justify-center flex-shrink-0">
        <svg class="w-4 h-4 text-brand-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-brand-text text-sm font-semibold truncate">${esc(t.title)}</p>
        <p class="text-brand-dim text-xs">${t.level} · ${(t.tags||[]).slice(0,2).join(', ')}</p>
      </div>
    </div>`).join('');
}

// ═══════════════════════════════════════════════════════════
// TOPICS GRID
// ═══════════════════════════════════════════════════════════
let _currentFilter = 'all';

function filter(f, btn) {
  _currentFilter = f;
  document.querySelectorAll('.chip').forEach(c => {
    c.classList.remove('active','bg-brand-blue','text-white','border-brand-blue');
    c.classList.add('border-brand-muted','text-brand-dim');
  });
  if (btn) {
    btn.classList.add('active','bg-brand-blue','text-white','border-brand-blue');
    btn.classList.remove('border-brand-muted','text-brand-dim');
  }
  let list = [..._allTopics];
  if (f === 'newest')  list.sort((a,b) => (b.created_at||'').localeCompare(a.created_at||''));
  else if (f === 'popular') list.sort((a,b) => (b.views||0) - (a.views||0));
  else if (['A','B','C'].includes(f)) list = list.filter(t => t.level?.startsWith(f));
  renderTopicsGrid(list);
}

function renderTopicsGrid(list) {
  const grid = document.getElementById('topicsGrid');
  if (!grid) return;
  if (!list.length) {
    grid.innerHTML = '<p class="text-brand-dim text-sm text-center pt-12">No topics found</p>';
    return;
  }
  grid.innerHTML = list.map(t => `
    <div class="bg-brand-card rounded-2xl p-4 border border-brand-muted/20 cursor-pointer active:scale-[0.98] transition-transform fade-up"
         onclick="FDC.openTopicBySlug('${t.slug}')">
      <div class="flex items-start justify-between gap-2 mb-2">
        <h3 class="font-display text-lg font-bold text-brand-text">${esc(t.title)}</h3>
        <span class="${levelBadgeClass(t.level)} text-[10px] font-bold px-2 py-1 rounded-full flex-shrink-0">${t.level}</span>
      </div>
      <p class="text-brand-dim text-xs mb-3 line-clamp-2">${esc(t.summary||'')}</p>
      <div class="flex items-center justify-between">
        <div class="flex gap-1.5 flex-wrap">
          ${(t.tags||[]).slice(0,2).map(tag=>`<span class="text-[10px] bg-brand-muted/40 text-brand-dim px-2 py-0.5 rounded-full">${esc(tag)}</span>`).join('')}
        </div>
        <div class="flex gap-3 text-xs text-brand-dim flex-shrink-0">
          <span>💬 ${t.question_count||0}</span>
          <span>👁 ${t.views||0}</span>
        </div>
      </div>
    </div>`).join('');
}

// ═══════════════════════════════════════════════════════════
// TOOLS
// ═══════════════════════════════════════════════════════════
function openTool(tool) {
  const modal = document.getElementById('toolModal');
  const content = document.getElementById('toolContent');
  modal.classList.add('open');

  if (tool === 'wheel') {
    const qs = _currentTopic?.questions || (_allTopics[0]?.questions) || [];
    content.innerHTML = `
      <div class="flex items-center justify-between mb-5">
        <h3 class="font-display text-xl font-bold text-brand-text">Question Wheel</h3>
        <button onclick="FDC.closeTool()" class="text-brand-dim w-7 h-7 flex items-center justify-center">✕</button>
      </div>
      <div id="wheelResult" class="bg-brand-dark rounded-2xl p-5 mb-5 min-h-[5rem] flex items-center justify-center text-center">
        <p class="text-brand-dim text-sm">Tap Spin to get a random question</p>
      </div>
      <button onclick="FDC._spinWheel()" class="w-full bg-brand-blue text-white font-semibold py-3 rounded-2xl active:scale-95 transition-transform">
        🎯 Spin
      </button>`;
    window._wheelQs = qs;

  } else if (tool === 'timer') {
    content.innerHTML = `
      <div class="flex items-center justify-between mb-5">
        <h3 class="font-display text-xl font-bold text-brand-text">Speaking Timer</h3>
        <button onclick="FDC.closeTool()" class="text-brand-dim w-7 h-7 flex items-center justify-center">✕</button>
      </div>
      <div class="text-center mb-5">
        <div id="timerDisplay" class="font-display text-6xl font-bold text-brand-text">2:00</div>
      </div>
      <div class="flex gap-2 mb-4">
        ${[1,2,3,5].map(m=>`<button class="flex-1 bg-brand-dark text-brand-dim text-xs py-2 rounded-xl" onclick="FDC._setTimer(${m*60})">${m}m</button>`).join('')}
      </div>
      <div class="flex gap-3">
        <button id="timerBtn" onclick="FDC._toggleTimer()" class="flex-1 bg-brand-blue text-white font-semibold py-3 rounded-2xl active:scale-95 transition-transform">Start</button>
        <button onclick="FDC._resetTimer()" class="bg-brand-dark text-brand-dim font-semibold py-3 px-5 rounded-2xl">Reset</button>
      </div>`;
    _setTimer(120);
  }
}

function closeTool() {
  document.getElementById('toolModal').classList.remove('open');
  clearInterval(_timerInterval);
  _timerRunning = false;
}

function _spinWheel() {
  const qs = window._wheelQs || [];
  if (!qs.length) return;
  const q = qs[Math.floor(Math.random() * qs.length)];
  const el = document.getElementById('wheelResult');
  if (el) el.innerHTML = `<p class="text-brand-text text-sm leading-relaxed">${esc(q.text)}</p>`;
}

function _setTimer(secs) {
  clearInterval(_timerInterval);
  _timerRunning = false;
  _timerSeconds = secs;
  const btn = document.getElementById('timerBtn');
  if (btn) btn.textContent = 'Start';
  _updateTimerDisplay();
}

function _toggleTimer() {
  if (_timerRunning) {
    clearInterval(_timerInterval);
    _timerRunning = false;
    const btn = document.getElementById('timerBtn');
    if (btn) btn.textContent = 'Resume';
  } else {
    _timerRunning = true;
    const btn = document.getElementById('timerBtn');
    if (btn) btn.textContent = 'Pause';
    _timerInterval = setInterval(() => {
      _timerSeconds--;
      _updateTimerDisplay();
      if (_timerSeconds <= 0) {
        clearInterval(_timerInterval);
        _timerRunning = false;
        const el = document.getElementById('timerDisplay');
        if (el) { el.textContent = 'Time!'; el.classList.add('text-brand-red'); }
      }
    }, 1000);
  }
}

function _resetTimer() {
  _setTimer(120);
  const el = document.getElementById('timerDisplay');
  if (el) el.classList.remove('text-brand-red');
}

function _updateTimerDisplay() {
  const m = Math.floor(_timerSeconds / 60);
  const s = _timerSeconds % 60;
  const el = document.getElementById('timerDisplay');
  if (el) el.textContent = `${m}:${String(s).padStart(2,'0')}`;
}

// ═══════════════════════════════════════════════════════════
// UTILITIES
// ═══════════════════════════════════════════════════════════
function levelBadgeClass(level) {
  const l = (level||'').toUpperCase();
  if (l.startsWith('A')) return 'badge-a';
  if (l.startsWith('B')) return 'badge-b';
  return 'badge-c';
}

function esc(str) {
  if (!str) return '';
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function copyText(text, evt) {
  if (evt) evt.stopPropagation();
  navigator.clipboard.writeText(text).then(() => toast('Copied!')).catch(() => {});
}

function toast(msg, duration = 2000) {
  const el = document.getElementById('toast');
  if (!el) return;
  el.textContent = msg;
  el.style.opacity = '1';
  setTimeout(() => { el.style.opacity = '0'; }, duration);
}

// Handle browser back button closing detail panel
window.addEventListener('popstate', (e) => {
  const detail = document.getElementById('topicDetail');
  if (detail.classList.contains('open')) closeDetail();
});

// Public API
return { init, tab, openTopicBySlug, closeDetail, detailTab, shareDetail,
         openSearch, closeSearch, search,
         filter, renderTopicsGrid,
         openTool, closeTool,
         _spinWheel, _setTimer, _toggleTimer, _resetTimer,
         copyText, toast };

})();

document.addEventListener('DOMContentLoaded', () => FDC.init());
