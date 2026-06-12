# Free Discussion Class

An interactive, SEO-optimized web platform for ESL discussion topics — built with PHP 8.3, Alpine.js, and a Telegram Bot upload workflow. No database required.

**Live demo:** [en.class.sayid.ir](https://en.class.sayid.ir)

---

## What it does

Weekly discussion topics are distributed as DOCX or PDF files. This platform transforms them into rich, interactive web pages that teachers and students can use directly in the browser — with vocabulary flashcards, speaking timers, discussion wheels, printable worksheets, and offline PWA support.

Admins upload files via Telegram. The bot parses the content, publishes it automatically, and replies with the live URL.

---

## Features

### Learning Tools

- Vocabulary flashcards with definitions, pronunciation, and collocations
- Discussion wheel — randomly selects a question for the class
- Speaking timer with configurable presets and audio cue
- Roleplay scenario cards
- Classroom game suggestions
- Printable worksheets (CSS print styles)
- Copy-to-clipboard for any question or phrase
- Reading mode

### Platform

- Full-text search with relevance scoring
- Filter by level (A1 → C2) and tags
- Topic of the Day (deterministic daily rotation)
- Random topic and random question generators
- Related topics sidebar
- Favorite topics (localStorage)
- Offline support via Service Worker (PWA)
- Auto dark mode (`prefers-color-scheme`)

### SEO & Meta

- Clean URLs (`/topic/jealousy`)
- Dynamic `<title>`, meta descriptions, canonical URLs
- OpenGraph + Twitter Card tags
- JSON-LD: FAQ Schema + Breadcrumb Schema
- Auto-generated XML sitemap at `/sitemap.xml`

### Telegram Bot

- Send a DOCX or PDF → bot parses, saves, and publishes in seconds
- Admin whitelist by Telegram user ID
- Commands: `/help` `/list` `/stats` `/rebuild` `/delete`
- Webhook secret validation

---

## Tech Stack

| Layer        | Technology                                |
| ------------ | ----------------------------------------- |
| Language     | PHP 8.3+                                  |
| Frontend     | Alpine.js 3, vanilla CSS (no framework)   |
| Storage      | JSON flat files (no database)             |
| Server       | Nginx + PHP-FPM                           |
| PDF parsing  | `pdftotext` (poppler-utils)               |
| DOCX parsing | Native ZIP/XML (no external lib required) |
| Bot          | Telegram Bot API (webhooks)               |
| PWA          | Service Worker + Web App Manifest         |

---

## Project Structure

```
free-discussion-class/
├── bootstrap.php               — autoloader, error handling
├── composer.json
├── .env.example                — environment variable template
│
├── config/
│   ├── config.php              — app configuration
│   └── nginx.conf              — production Nginx config
│
├── data/
│   ├── index.json              — auto-generated topic index
│   └── topics/
│       ├── jealousy.json
│       ├── friendship.json
│       └── success.json
│
├── public/                     — Nginx document root
│   ├── index.php               — front controller / router
│   ├── css/app.css
│   ├── js/app.js               — Alpine.js components
│   ├── sw.js                   — Service Worker
│   ├── manifest.json
│   ├── icons/
│   └── images/
│
├── scripts/
│   ├── rebuild-index.php       — CLI: rebuild data/index.json
│   └── setup-webhook.php       — CLI: register Telegram webhook
│
├── src/
│   ├── helpers.php
│   ├── Bot/TelegramBot.php
│   ├── Core/Router.php
│   ├── Core/HomeController.php
│   ├── Core/TopicController.php
│   ├── Parser/DocxParser.php
│   ├── Parser/PdfParser.php
│   ├── Parser/ParsedContent.php
│   ├── SEO/MetaBuilder.php
│   └── Topic/TopicRepository.php
│
├── templates/
│   ├── layouts/base.php
│   ├── pages/
│   └── partials/
│
├── INSTALL.md                  — Ubuntu server setup guide
└── DEPLOY.md                   — Deployment and update guide
```

---

## Topic JSON Format

Each topic is a single JSON file in `data/topics/`:

```json
{
  "slug": "jealousy",
  "title": "Jealousy",
  "summary": "Explore the psychology of jealousy...",
  "level": "B2",
  "tags": ["emotions", "relationships", "psychology"],
  "cover": "",
  "questions": [
    {
      "text": "Have you ever felt jealous of a friend's success?",
      "level": "B1",
      "type": "personal"
    }
  ],
  "vocabulary": [
    {
      "word": "envious",
      "definition": "feeling or showing envy",
      "pronunciation": "/ˈen.vi.əs/",
      "examples": ["She felt envious of her colleague's promotion."],
      "collocations": ["deeply envious", "feel envious of"]
    }
  ],
  "collocations": [],
  "phrases": [],
  "idioms": [],
  "quotes": [{ "text": "...", "author": "..." }],
  "teacher_notes": "",
  "games": [
    {
      "name": "Hot Seat",
      "description": "...",
      "players": "4+",
      "time": "10 min"
    }
  ],
  "roleplay": [{ "scenario": "...", "roles": ["Student A", "Student B"] }],
  "created_at": "2026-06-11"
}
```

---

## Quick Start (Local)

```bash
git clone https://github.com/youruser/free-discussion-class.git
cd free-discussion-class

cp .env.example .env
# Edit .env with your Telegram bot token and admin IDs

composer install

php scripts/rebuild-index.php

# Serve with PHP built-in server (development only)
php -S localhost:8080 -t public/
```

Open [http://localhost:8080](http://localhost:8080).

---

## Server Deployment

See [INSTALL.md](INSTALL.md) for the full step-by-step Ubuntu guide covering:

- PHP 8.3 + required extensions
- Nginx configuration + SSL via Certbot
- File permissions
- Telegram webhook registration
- Cron jobs

**One-line deploy sequence (after first install):**

```bash
composer install --no-dev --optimize-autoloader
php scripts/rebuild-index.php
sudo systemctl reload php8.3-fpm
```

---

## Environment Variables

Copy `.env.example` to `.env` and fill in:

| Variable                  | Required | Description                               |
| ------------------------- | -------- | ----------------------------------------- |
| `APP_ENV`                 | yes      | `production` or `development`             |
| `APP_URL`                 | yes      | Full URL e.g. `https://en.class.sayid.ir` |
| `TELEGRAM_BOT_TOKEN`      | yes      | Token from @BotFather                     |
| `TELEGRAM_ADMIN_IDS`      | yes      | Comma-separated Telegram user IDs         |
| `TELEGRAM_WEBHOOK_SECRET` | yes      | Random secret for webhook validation      |
| `GA_ID`                   | no       | Google Analytics measurement ID           |

---

## Telegram Bot Usage

1. Create a bot via [@BotFather](https://t.me/botfather) and copy the token.
2. Find your Telegram user ID via [@userinfobot](https://t.me/userinfobot).
3. Set `TELEGRAM_BOT_TOKEN` and `TELEGRAM_ADMIN_IDS` in `.env`.
4. Register the webhook:
   ```bash
   php scripts/setup-webhook.php
   ```
5. Send any DOCX or PDF file to your bot. It will reply:
   ```
   ✅ Topic Published
   Title: Jealousy
   Questions: 7
   Vocabulary: 15
   URL: https://en.class.sayid.ir/topic/jealousy
   ```

---

## Rebuilding the Index

`data/index.json` is a pre-built cache of all topics. Rebuild it after any manual edits:

```bash
php scripts/rebuild-index.php
```

The Telegram bot also rebuilds the index automatically after every upload.

---

## License

MIT — see [LICENSE](LICENSE) for details.
