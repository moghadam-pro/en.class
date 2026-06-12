# Free Discussion Class — Installation Guide
## Ubuntu 22.04 LTS / 24.04 LTS

---

## Prerequisites

- Ubuntu 22.04+ server (1 GB RAM minimum, 2 GB recommended)
- A domain pointed to your server IP (`en.class.sayid.ir → YOUR.IP`)
- Root or sudo access
- A Telegram bot token from [@BotFather](https://t.me/botfather)

---

## 1 — System Update

```bash
sudo apt update && sudo apt upgrade -y
```

---

## 2 — Install PHP 8.3

```bash
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y \
    php8.3-fpm \
    php8.3-cli \
    php8.3-mbstring \
    php8.3-xml \
    php8.3-zip \
    php8.3-curl \
    php8.3-json \
    php8.3-intl
```

Verify:
```bash
php8.3 --version
```

---

## 3 — Install Nginx

```bash
sudo apt install -y nginx
sudo systemctl enable nginx
sudo systemctl start nginx
```

---

## 4 — Install Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

---

## 5 — Install poppler-utils (PDF parsing)

```bash
sudo apt install -y poppler-utils
pdftotext -v
```

---

## 6 — Deploy the Application

```bash
# Create web root
sudo mkdir -p /var/www/free-discussion-class
sudo chown $USER:$USER /var/www/free-discussion-class

# Upload or clone your project
# Option A: rsync from local machine
rsync -avz --exclude='.env' ./free-discussion-class/ user@yourserver:/var/www/free-discussion-class/

# Option B: git clone (if in a repo)
git clone https://github.com/youruser/free-discussion-class.git /var/www/free-discussion-class

cd /var/www/free-discussion-class

# Install PHP dependencies
composer install --no-dev --optimize-autoloader
```

---

## 7 — Configure Environment

```bash
cp .env.example .env
nano .env
```

Fill in at minimum:
```
APP_ENV=production
APP_URL=https://en.class.sayid.ir
TELEGRAM_BOT_TOKEN=your_actual_bot_token
TELEGRAM_ADMIN_IDS=your_telegram_user_id
TELEGRAM_WEBHOOK_SECRET=a_random_32char_string
```

Generate a random secret:
```bash
openssl rand -hex 32
```

---

## 8 — Set File Permissions

```bash
cd /var/www/free-discussion-class

# Directories that must be writable by the web server
sudo mkdir -p data/topics logs cache uploads
sudo chown -R www-data:www-data data/ logs/ cache/ uploads/
sudo chmod -R 755 data/ logs/ cache/ uploads/

# Everything else readable but not writable by www-data
sudo chown -R $USER:www-data .
sudo find . -type f -name "*.php" -exec chmod 640 {} \;
sudo find . -type d -exec chmod 750 {} \;

# Public assets must be readable
sudo chmod -R 755 public/
```

---

## 9 — Nginx Configuration

```bash
sudo cp config/nginx.conf /etc/nginx/sites-available/en.class.sayid.ir
sudo ln -s /etc/nginx/sites-available/en.class.sayid.ir \
           /etc/nginx/sites-enabled/en.class.sayid.ir

# Remove default site if present
sudo rm -f /etc/nginx/sites-enabled/default

sudo nginx -t
sudo systemctl reload nginx
```

---

## 10 — SSL Certificate (Let's Encrypt)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d en.class.sayid.ir

# Test auto-renewal
sudo certbot renew --dry-run
```

Certbot will automatically update the Nginx config with SSL settings.

---

## 11 — Build Index

```bash
cd /var/www/free-discussion-class
php scripts/rebuild-index.php
```

---

## 12 — Register Telegram Webhook

The server must be live with a valid SSL certificate before this step.

```bash
cd /var/www/free-discussion-class
php scripts/setup-webhook.php
```

Expected output:
```
✅  Webhook registered successfully.
    Response: Webhook was set
```

---

## 13 — Cron Jobs (optional but recommended)

```bash
sudo crontab -e -u www-data
```

Add:
```cron
# Rebuild index nightly in case of manual edits
0 2 * * * cd /var/www/free-discussion-class && php scripts/rebuild-index.php >> logs/cron.log 2>&1

# Clear old upload temp files daily
0 3 * * * find /var/www/free-discussion-class/uploads -mtime +1 -delete
```

---

## 14 — Verify Installation

```bash
# Test PHP-FPM is running
sudo systemctl status php8.3-fpm

# Test Nginx config
sudo nginx -t

# Check the site
curl -I https://en.class.sayid.ir

# Check webhook info from Telegram
curl "https://api.telegram.org/botYOUR_TOKEN/getWebhookInfo"
```

---

## 15 — Firewall

```bash
sudo ufw allow 22/tcp      # SSH
sudo ufw allow 80/tcp      # HTTP
sudo ufw allow 443/tcp     # HTTPS
sudo ufw enable
sudo ufw status
```

---

## Troubleshooting

### 502 Bad Gateway
```bash
sudo systemctl status php8.3-fpm
sudo tail -n 50 /var/log/nginx/error.log
```

### Permission denied on data/
```bash
sudo chown -R www-data:www-data /var/www/free-discussion-class/data
sudo chmod -R 755 /var/www/free-discussion-class/data
```

### Telegram bot not receiving messages
```bash
# Check webhook is set
curl "https://api.telegram.org/botTOKEN/getWebhookInfo"

# Re-register if needed
cd /var/www/free-discussion-class
php scripts/setup-webhook.php
```

### PDF parsing not working
```bash
which pdftotext          # must return a path
pdftotext --version      # poppler-utils version
```

### View application logs
```bash
tail -f /var/www/free-discussion-class/logs/app.log
tail -f /var/log/nginx/en.class.sayid.ir-error.log
```
