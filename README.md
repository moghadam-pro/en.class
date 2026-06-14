# Free Discussion Class

An interactive, SEO-optimized platform for ESL discussion topics — built with PHP 8.3 and a mobile-first Tailwind CSS frontend. Telegram Bot upload workflow. No database required.

**Live:** [en.class.sayid.ir](https://en.class.sayid.ir)

---

## What it does

Free Discussion Class gives ESL learners and teachers a library of rich discussion topics — with vocabulary tools, speaking games, and live events — all in one place.

Weekly topics are uploaded via Telegram (DOCX or PDF). The bot parses, publishes, and replies with the live URL automatically.

---

## v2 UI — Mobile-First (current)

- **Bottom Navigation** — Home · Search · Topics · Tools
- **Home** — minimal header (logo only), hero card, latest topics horizontal scroll, events list
- **Search** — full-text across topics, vocabulary, events, and teachers
- **Topics** — filterable grid (level, newest, popular)
- **Tools** — Question Wheel, Speaking Timer (more coming)
- **Topic Detail** — slide-in view with tabbed content: Questions / Vocabulary / Phrases & Idioms / Games
- **Tailwind CSS** + Inter + Barlow Condensed
- **PWA** — Service Worker, offline support, installable

---

## Features

### Learning Tools

- Vocabulary with pronunciation, definition, and example sentences
- Question Wheel — random question picker for class
- Speaking Timer with presets and pause/resume
- Phrases, idioms, and collocation panels
- Classroom game suggestions per topic
- Roleplay scenario cards

### Platform

- Full-text search across topics, words, events, and teachers
- Filter by CEFR level (A1→C2), date, and popularity
- Topic of the Day (deterministic daily rotation)
- Favorite topics (localStorage)
- Offline support via Service Worker (PWA)
- Dark mode only (matches brand)

### SEO & Meta

- Clean URLs (`/topic/jealousy`)
- OpenGraph + Twitter Card tags
- JSON-LD: FAQ Schema + Breadcrumb Schema
- Auto-generated XML sitemap

### Telegram Bot

- Send DOCX or PDF → parsed, published, URL replied instantly
- Admin whitelist by Telegram user ID
- Commands: `/help` `/list` `/stats` `/rebuild` `/delete`

---

## Tech Stack

| Layer        | Technology                                        |
| ------------ | ------------------------------------------------- |
| Language     | PHP 8.3+                                          |
| Frontend     | Tailwind CSS, Alpine.js, Inter + Barlow Condensed |
| Storage      | JSON flat files (no database)                     |
| Server       | Nginx + PHP-FPM (CloudPanel)                      |
| PDF parsing  | `pdftotext` (poppler-utils)                       |
| DOCX parsing | Native ZIP/XML                                    |
| Bot          | Telegram Bot API (webhooks)                       |
| PWA          | Service Worker + Web App Manifest                 |

---

## Project Structure

```
free-discussion-class/
├── bootstrap.php               — autoloader + .env loader
├── composer.json
├── .env.example
├── config/
│   ├── config.php
│   └── nginx.conf
├── data/
│   ├── index.json              — auto-generated
│   └── topics/*.json
├── public/                     — Nginx document root
│   ├── index.php
│   ├── css/app.css
│   ├── js/app.js
│   ├── sw.js
│   ├── manifest.json
│   ├── icons/
│   └── images/
├── scripts/
│   ├── rebuild-index.php
│   └── setup-webhook.php
├── src/
│   ├── helpers.php
│   ├── Bot/TelegramBot.php
│   ├── Core/{Router,HomeController,TopicController}.php
│   ├── Parser/{DocxParser,PdfParser,ParsedContent}.php
│   ├── SEO/MetaBuilder.php
│   └── Topic/TopicRepository.php
├── templates/
│   ├── layouts/base.php
│   ├── pages/
│   └── partials/
├── INSTALL-CLOUDPANEL.md
└── DEPLOY.md
```

---

## Topic JSON Schema

```json
{
  "slug": "jealousy",
  "title": "Jealousy",
  "summary": "...",
  "level": "B2",
  "tags": ["emotions", "relationships"],
  "cover": "",
  "questions": [{ "text": "...", "level": "B1", "type": "personal" }],
  "vocabulary": [
    {
      "word": "...",
      "definition": "...",
      "pronunciation": "...",
      "examples": [],
      "collocations": []
    }
  ],
  "phrases": [],
  "idioms": [],
  "collocations": [],
  "quotes": [{ "text": "...", "author": "..." }],
  "teacher_notes": "",
  "games": [
    { "name": "...", "description": "...", "players": "4+", "time": "10 min" }
  ],
  "roleplay": [{ "scenario": "...", "roles": [] }],
  "created_at": "2026-06-11"
}
```

---

## Quick Start (Local)

```bash
git clone https://github.com/youruser/free-discussion-class.git
cd free-discussion-class
cp .env.example .env   # fill in your values
php scripts/rebuild-index.php
php -S localhost:8080 -t public/
```

---

## Server Deployment (CloudPanel)

See [INSTALL-CLOUDPANEL.md](INSTALL-CLOUDPANEL.md) — 10-step guide:

1. Add PHP 8.3 site in CloudPanel
2. Upload files
3. Set Document Root → `/public`
4. Configure `.env`
5. Set permissions
6. SSL (auto via CloudPanel)
7. `php scripts/rebuild-index.php`
8. Test in browser
9. `php scripts/setup-webhook.php`
10. Test Telegram bot

---

## Environment Variables

| Variable                  | Required | Description                  |
| ------------------------- | -------- | ---------------------------- |
| `APP_ENV`                 | yes      | `production` / `development` |
| `APP_URL`                 | yes      | `https://en.class.sayid.ir`  |
| `TELEGRAM_BOT_TOKEN`      | yes      | From @BotFather              |
| `TELEGRAM_ADMIN_IDS`      | yes      | Comma-separated user IDs     |
| `TELEGRAM_WEBHOOK_SECRET` | yes      | Random 32-char string        |
| `GA_ID`                   | no       | Google Analytics ID          |

---

## Changelog

### v2.0 (2026-06)

- Mobile-first redesign with Tailwind CSS
- Bottom Navigation (Home, Search, Topics, Tools)
- Unified Search across all content types
- Topic filter by level, date, popularity
- Topic detail as slide-in panel with tabbed content
- Tools tab with Question Wheel and Speaking Timer
- Events section on Home

### v1.0 (2026-06)

- Initial PHP flat-file platform
- Telegram Bot upload workflow
- DOCX/PDF parser
- PWA + Service Worker
- SEO: OG, Twitter Cards, JSON-LD, Sitemap

---

## License

MIT
