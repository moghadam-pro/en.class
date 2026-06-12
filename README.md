# Free Discussion Class

A production-oriented PHP 8.3 platform for interactive, searchable English discussion lessons at `https://sayid.ir/en.class`.

## Included

- JSON topic repository with atomic writes and one file per topic
- Full-text topic and vocabulary search
- Daily and random topics, related topics, random prompts, discussion wheel, and speaking timer
- Vocabulary detail sheets, role-plays, classroom games, print worksheets, reading mode, favorites, and copy actions
- Responsive Tailwind UI, Alpine.js interactions, automatic dark mode, reduced-motion support, and WCAG-oriented markup
- PWA manifest, service worker, offline caching, SEO metadata, OpenGraph/Twitter cards, JSON-LD, FAQ/breadcrumb schema, sitemap, and robots output
- Admin-only Telegram DOCX/PDF publishing with webhook secret validation, MIME validation, safe parsing, index rebuild, stats, listing, and deletion

## Requirements

- Ubuntu 24.04 or later
- Nginx
- PHP 8.3 FPM with `curl`, `dom`, `fileinfo`, `json`, `mbstring`, and `zip`
- Poppler `pdftotext`
- Node.js 20+ and npm for the production frontend build
- Composer 2

## Install

```bash
sudo apt update
sudo apt install nginx php8.3-fpm php8.3-cli php8.3-curl php8.3-xml \
  php8.3-mbstring php8.3-zip poppler-utils composer nodejs npm certbot python3-certbot-nginx

sudo mkdir -p /var/www/en.class/releases
sudo cp -a free-discussion-class /var/www/en.class/releases/$(date +%Y%m%d%H%M%S)
cd /var/www/en.class/releases/$(ls -1 /var/www/en.class/releases | tail -1)

cp .env.example .env
composer install --no-dev --classmap-authoritative
npm install
npm run build

sudo chown -R root:www-data .
sudo find . -type d -exec chmod 0750 {} \;
sudo find . -type f -exec chmod 0640 {} \;
sudo chmod 0755 public public/assets bin
sudo chmod -R 0770 data storage

sudo ln -sfn "$PWD" /var/www/en.class/current
sudo cp deploy/nginx.conf /etc/nginx/sites-available/free-discussion-class
sudo ln -sfn /etc/nginx/sites-available/free-discussion-class /etc/nginx/sites-enabled/free-discussion-class
sudo nginx -t
sudo systemctl reload nginx
```

The repository includes a production asset build for immediate deployment. Run `npm run build` before each release to regenerate the minified Tailwind CSS and Alpine vendor file from the pinned dependencies.

## Configuration

Edit `.env`:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sayid.ir/en.class
APP_BASE_PATH=/en.class
APP_TIMEZONE=Asia/Tehran
TELEGRAM_BOT_TOKEN=123456:replace_me
TELEGRAM_WEBHOOK_SECRET=use_a_long_random_value
TELEGRAM_ADMINS=123456789,987654321
MAX_UPLOAD_MB=12
PDFTOTEXT_BIN=/usr/bin/pdftotext
```

Generate the webhook secret with `openssl rand -hex 32`. Never commit `.env`.

## TLS and Telegram

```bash
sudo certbot --nginx -d sayid.ir -d www.sayid.ir
cd /var/www/en.class/current
sudo -u www-data php bin/set-webhook.php
```

Telegram sends `X-Telegram-Bot-Api-Secret-Token`; the webhook rejects requests unless it exactly matches `TELEGRAM_WEBHOOK_SECRET`. Only IDs in `TELEGRAM_ADMINS` can execute commands or upload files.

Bot commands:

```text
/help
/list
/stats
/rebuild
/delete topic-slug
```

See [docs/AUTHORING.md](docs/AUTHORING.md) for the DOCX/PDF heading format.

## Cron and maintenance

The bot rebuilds `data/index.json` after every publish or delete. A nightly rebuild protects against drift after manual JSON changes:

```bash
sudo cp deploy/cron /etc/cron.d/free-discussion-class
sudo chmod 0644 /etc/cron.d/free-discussion-class
```

Temporary Telegram downloads are deleted immediately. Optionally install the tmpfiles rule to remove abandoned files after a crash:

```bash
sudo cp deploy/free-discussion-class-tmpfiles.conf /etc/tmpfiles.d/
sudo systemd-tmpfiles --create
```

## Release deployment

Build every release in a new timestamped directory, run the checks below, then atomically update `/var/www/en.class/current`. Keep the previous release for instant rollback.

```bash
find src public app bin -name '*.php' -print0 | xargs -0 -n1 php -l
php bin/rebuild-index.php
npm run build
sudo nginx -t
sudo ln -sfn /var/www/en.class/releases/NEW_RELEASE /var/www/en.class/current
sudo systemctl reload php8.3-fpm nginx
```

## Extending with AI

`DocumentParser` only extracts trusted text, `TopicFactory` maps text into the domain shape, and `TopicPublisher` validates/persists it. Add an AI enrichment implementation between factory and publisher, keep the same topic contract, and require human/admin review for generated claims.
