// public/js/app.js

// ── CDN Imports ────────────────────────────────────────────────────────────
// Alpine.js loaded from CDN in HTML if not bundled
if (typeof window !== 'undefined' && !window.Alpine) {
  const script = document.createElement('script');
  script.src = 'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js';
  script.defer = true;
  document.head.appendChild(script);
}

// ── App Shell (Alpine component for <body>) ────────────────────────────────
function appShell() {
  return {
    navOpen:      false,
    searchOpen:   false,
    searchQuery:  '',
    searchResults:[],
    searchLoading:false,
    readingMode:  localStorage.getItem('reading-mode') === '1',
    toast:        { show: false, message: '' },
    vocabModal:   { open: false, word: '', definition: '', pronunciation: '', examples: [], collocations: [] },

    toggleReadingMode() {
      this.readingMode = !this.readingMode;
      localStorage.setItem('reading-mode', this.readingMode ? '1' : '0');
      this.showToast(this.readingMode ? '📖 Reading mode on' : '📖 Reading mode off');
    },

    openSearch() {
      this.searchOpen = true;
      document.body.style.overflow = 'hidden';
      this.$nextTick(() => this.$refs.searchInput?.focus());
    },

    closeSearch() {
      this.searchOpen = false;
      document.body.style.overflow = '';
      this.searchQuery  = '';
      this.searchResults = [];
    },

    async doSearch() {
      if (this.searchQuery.length < 2) { this.searchResults = []; return; }
      this.searchLoading = true;
      try {
        const res  = await fetch('/api/search?q=' + encodeURIComponent(this.searchQuery));
        const data = await res.json();
        this.searchResults = data.results || [];
      } catch (e) {
        this.searchResults = [];
      }
      this.searchLoading = false;
    },

    openVocab(vocab) {
      this.vocabModal = { open: true, ...vocab };
    },

    copyVocab() {
      const text = `${this.vocabModal.word}: ${this.vocabModal.definition}`;
      copyToClipboard(text);
      this.showToast('📋 Copied to clipboard!');
    },

    showToast(message, duration = 2500) {
      this.toast = { show: true, message };
      setTimeout(() => { this.toast.show = false; }, duration);
    },
  };
}

// ── Topic page component ────────────────────────────────────────────────────
function topicPage(questions, vocab) {
  return {
    activeTab:      'questions',
    vocabView:      'grid',
    currentCard:    0,
    cardFlipped:    false,
    wheelQuestion:  '',
    currentRandomQ: 'Click to get a random question.',
    timerSeconds:   60,
    timerRunning:   false,
    timerInterval:  null,
    questions:      questions || [],
    vocab:          vocab     || [],

    // ── Flashcards ─────────────────────────────────────────────────────────
    nextCard() {
      if (this.currentCard < this.vocab.length - 1) {
        this.currentCard++;
        this.cardFlipped = false;
      }
    },
    prevCard() {
      if (this.currentCard > 0) {
        this.currentCard--;
        this.cardFlipped = false;
      }
    },

    // ── Discussion wheel ───────────────────────────────────────────────────
    spinWheel() {
      if (!this.questions.length) return;
      const wheel = document.getElementById('discussion-wheel');
      const deg   = Math.random() * 360;
      wheel.style.setProperty('--spin-extra', deg + 'deg');
      wheel.classList.remove('spinning');
      void wheel.offsetWidth; // reflow
      wheel.classList.add('spinning');
      setTimeout(() => {
        const q = this.questions[Math.floor(Math.random() * this.questions.length)];
        this.wheelQuestion = typeof q === 'object' ? q.text : q;
        wheel.classList.remove('spinning');
      }, 1050);
    },

    // ── Random question ────────────────────────────────────────────────────
    randomQuestion() {
      if (!this.questions.length) return;
      const q = this.questions[Math.floor(Math.random() * this.questions.length)];
      this.currentRandomQ = typeof q === 'object' ? q.text : q;
    },

    // ── Speaking timer ────────────────────────────────────────────────────
    setTimer(seconds) {
      this.resetTimer();
      this.timerSeconds = seconds;
    },
    startTimer() {
      this.timerRunning = true;
      this.timerInterval = setInterval(() => {
        if (this.timerSeconds <= 0) {
          this.pauseTimer();
          this.timerSeconds = 0;
          this.playBeep();
          return;
        }
        this.timerSeconds--;
      }, 1000);
    },
    pauseTimer() {
      this.timerRunning = false;
      clearInterval(this.timerInterval);
    },
    resetTimer() {
      this.pauseTimer();
      this.timerSeconds = 60;
    },
    formatTime(s) {
      const m = Math.floor(s / 60);
      const sec = s % 60;
      return `${String(m).padStart(2,'0')}:${String(sec).padStart(2,'0')}`;
    },
    playBeep() {
      try {
        const ctx  = new (window.AudioContext || window.webkitAudioContext)();
        const osc  = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.frequency.value = 880;
        osc.type = 'sine';
        gain.gain.setValueAtTime(0.3, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.8);
        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + 0.8);
      } catch (e) { /* AudioContext may be blocked */ }
    },

    // ── Copy helpers ──────────────────────────────────────────────────────
    copyText(text) {
      copyToClipboard(text);
    },
    copyQuestions() {
      const texts = this.questions.map((q, i) => `${i+1}. ${typeof q === 'object' ? q.text : q}`).join('\n');
      copyToClipboard(texts);
      window.dispatchEvent(new CustomEvent('show-toast', { detail: '📋 Questions copied!' }));
    },
    copyAll() {
      const qs = this.questions.map((q,i) => `${i+1}. ${typeof q === 'object' ? q.text : q}`).join('\n');
      const vs = this.vocab.map(v => `${typeof v === 'object' ? v.word : v}${(typeof v === 'object' && v.definition) ? ': '+v.definition : ''}`).join('\n');
      copyToClipboard(`QUESTIONS:\n${qs}\n\nVOCABULARY:\n${vs}`);
      window.dispatchEvent(new CustomEvent('show-toast', { detail: '📋 All content copied!' }));
    },
    printWorksheet() { window.print(); },
  };
}

// ── Favorites ──────────────────────────────────────────────────────────────
function getFavorites() {
  try { return JSON.parse(localStorage.getItem('fav-topics') || '[]'); }
  catch (e) { return []; }
}
function saveFavorites(favs) {
  localStorage.setItem('fav-topics', JSON.stringify(favs));
}
function toggleFavorite(slug, btn) {
  let favs = getFavorites();
  const idx = favs.indexOf(slug);
  if (idx === -1) {
    favs.push(slug);
    btn && (btn.innerHTML = '♥');
    btn && btn.classList.add('is-fav');
    showToast('❤️ Saved to favorites!');
  } else {
    favs.splice(idx, 1);
    btn && (btn.innerHTML = '♡');
    btn && btn.classList.remove('is-fav');
    showToast('Removed from favorites');
  }
  saveFavorites(favs);
}

// Initialize fav button states on page load
document.addEventListener('DOMContentLoaded', () => {
  const favs = getFavorites();
  document.querySelectorAll('.fav-btn[data-slug]').forEach(btn => {
    const slug = btn.dataset.slug;
    if (favs.includes(slug)) {
      btn.innerHTML = '♥';
      btn.classList.add('is-fav');
    }
  });
});

// ── Clipboard ──────────────────────────────────────────────────────────────
async function copyToClipboard(text) {
  try {
    await navigator.clipboard.writeText(text);
    showToast('📋 Copied!');
  } catch (e) {
    // Fallback
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.style.cssText = 'position:fixed;opacity:0';
    document.body.appendChild(ta);
    ta.select();
    document.execCommand('copy');
    document.body.removeChild(ta);
    showToast('📋 Copied!');
  }
}

// ── Share ──────────────────────────────────────────────────────────────────
function shareUrl(url, title) {
  if (navigator.share) {
    navigator.share({ title, url }).catch(() => {});
  } else {
    copyToClipboard(url);
  }
}

// ── Toast helper ──────────────────────────────────────────────────────────
function showToast(message, duration = 2500) {
  let el = document.querySelector('.toast');
  if (!el) return;
  // Dispatch to Alpine
  el._x_dataStack?.[0] && (el._x_dataStack[0].toast = { show: true, message });
}
window.addEventListener('show-toast', (e) => showToast(e.detail));

// ── Service Worker ──────────────────────────────────────────────────────────
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js').catch(() => {});
  });
}

// ── Nav scroll effect ───────────────────────────────────────────────────────
window.addEventListener('scroll', () => {
  const nav = document.querySelector('.nav-bar');
  if (nav) nav.classList.toggle('scrolled', window.scrollY > 10);
}, { passive: true });

// ── Keyboard shortcut: / to open search ────────────────────────────────────
document.addEventListener('keydown', (e) => {
  if (e.key === '/' && !['INPUT','TEXTAREA','SELECT'].includes(document.activeElement?.tagName)) {
    e.preventDefault();
    // Find and trigger Alpine's openSearch
    document.querySelector('body')?._x_dataStack?.[0]?.openSearch?.();
  }
});
