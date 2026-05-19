# Eckmar (Eckmar's Marketplace Script)
[![GitHub stars](https://img.shields.io/github/stars/dclipca/eckmar)](https://github.com/dclipca/eckmar/stargazers)
[![GitHub forks](https://img.shields.io/github/forks/dclipca/eckmar)](https://github.com/dclipca/eckmar/network)
[![GitHub issues](https://img.shields.io/github/issues/dclipca/eckmar)](https://github.com/dclipca/eckmar/issues)
![](https://img.shields.io/github/license/eckmarcommunity/eckmar)

![](https://github.com/nomiac-mobile/peralta/blob/master/demo/eckmar-background.jpg)

Eckmar is an open-source, cryptocurrency-friendly marketplace built on Laravel.

![](https://github.com/nomiac-mobile/peralta/blob/master/demo/website.jpg)

## Features
### Vendor Accounts
Any user can register and buy products on the marketplace but in order to create a listing you need vendor account status.

### Autofill
Autofill supports bulk digital inventory where each line represents one deliverable item.

### Messaging System
Users can send private messages to each other.

### Mnemonic Password Reset
Users receive a unique mnemonic that can be used to reset the password.

### Wallet System
Users can generate deposit addresses and check balances for supported coins.

### Escrow
Purchases include configurable market fee, escrow flow, and dispute handling.

### Feedback
Includes both score and trust-style rating indicators.

### News
Frontpage news/blog support.

### Auction System
Products can be listed as auctions with automatic winner/refund flow.

### Admin Panel
Manage users, categories, disputes, tickets, products, and operations.

## System Requirements
- Ubuntu 20.04 LTS VPS
- 1GB RAM minimum recommended (2GB+ recommended when running pruned Monero node)
- PHP 7.4 + required extensions
- MySQL 8.0
- Elasticsearch 7.x
- Redis
- Node.js 16.x + npm
- Composer 2.x
- Nginx

## Quick Install (Automated)
Run the full unattended installer as root/sudo:

```bash
cd /var/www
git clone https://github.com/bitbybit91/eckmar.git
cd eckmar
sudo DOMAIN=market.example.com \
  DB_NAME=eckmar \
  DB_USER=eckmar \
  DB_PASSWORD='change_me' \
  ADMIN_XMR_WALLET='YOUR_XMR_WALLET' \
  bash scripts/install.sh
```

Then validate:

```bash
sudo APP_DIR=/var/www/eckmar bash scripts/debug.sh
```

## Manual Installation (Ubuntu 20.04)
1. Install packages:
   ```bash
   sudo apt update
   sudo apt install -y software-properties-common apt-transport-https ca-certificates curl gnupg unzip git supervisor redis-server cron nginx mysql-server openjdk-11-jre-headless
   ```
2. Install PHP 7.4 + extensions:
   ```bash
   sudo apt install -y php7.4-fpm php7.4-mysql php7.4-mbstring php7.4-xml php7.4-gmp php7.4-curl php7.4-gd php7.4-zip php7.4-bcmath php7.4-json php7.4-tokenizer php7.4-xmlrpc
   sudo sed -i 's#^;*cgi.fix_pathinfo=.*#cgi.fix_pathinfo=0#' /etc/php/7.4/fpm/php.ini
   sudo systemctl restart php7.4-fpm
   ```
3. Install Elasticsearch 7.x:
   ```bash
   curl -fsSL https://artifacts.elastic.co/GPG-KEY-elasticsearch | sudo gpg --dearmor -o /usr/share/keyrings/elastic-keyring.gpg
   echo "deb [signed-by=/usr/share/keyrings/elastic-keyring.gpg] https://artifacts.elastic.co/packages/7.x/apt stable main" | sudo tee /etc/apt/sources.list.d/elastic-7.x.list
   sudo apt update
   sudo apt install -y elasticsearch
   echo -e "-Xms512m\n-Xmx512m" | sudo tee /etc/elasticsearch/jvm.options.d/heap.options
   sudo systemctl enable --now elasticsearch
   ```
4. Install Node.js 16.x:
   ```bash
   curl -fsSL https://deb.nodesource.com/setup_16.x | sudo -E bash -
   sudo apt install -y nodejs
   ```
5. Install Composer 2.x:
   ```bash
   php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
   php composer-setup.php --2 --install-dir=/usr/local/bin --filename=composer
   rm composer-setup.php
   ```
6. Deploy app:
   ```bash
   sudo mkdir -p /var/www
   cd /var/www
   sudo git clone https://github.com/bitbybit91/eckmar.git
   cd eckmar
   cp .env.example .env
   composer install --no-dev --optimize-autoloader
   npm install
   npm run prod
   php artisan key:generate --force
   ```
7. Configure MySQL 8.0 and run migrations:
   ```bash
   mysql -u root -p
   CREATE DATABASE eckmar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'eckmar'@'localhost' IDENTIFIED BY 'change_me';
   GRANT ALL PRIVILEGES ON eckmar.* TO 'eckmar'@'localhost';
   FLUSH PRIVILEGES;
   exit
   php artisan migrate --force
   php artisan storage:link
   ```
8. Scheduler + queue worker:
   ```bash
   echo "* * * * * www-data php /var/www/eckmar/artisan schedule:run >> /dev/null 2>&1" | sudo tee /etc/cron.d/eckmar
   sudo systemctl restart cron
   ```
   Supervisor config:
   ```ini
   [program:eckmar-worker]
   command=php /var/www/eckmar/artisan queue:work --sleep=3 --tries=3 --timeout=90
   autostart=true
   autorestart=true
   user=www-data
   redirect_stderr=true
   stdout_logfile=/var/log/supervisor/eckmar-worker.log
   ```
9. Configure Nginx to serve `/var/www/eckmar/public` and use `fastcgi_pass unix:/run/php/php7.4-fpm.sock;`.

## Monero (XMR) Node Setup
Use pruned node setup script:

```bash
sudo MONERO_ADMIN_WALLET='YOUR_ADMIN_WALLET' bash scripts/setup-xmr.sh
```

It installs a pinned stable Monero CLI release, verifies SHA256, configures pruned node, and creates a `monerod` systemd service with config at `/etc/monero/monerod.conf`.

## Connecting Coins
Configure coin RPC values in `.env` using prefixes:
- `BITCOIND_*`
- `MONERO_*` / `MONERO_RPC_*`
- `PIVX_*`
- `LITECOIN_*`
- `DASH_*`
- `VERGE_*`
- `BITCOIN_CASH_*`

For Monero wallet RPC set:
- `MONERO_HOST`
- `MONERO_PORT`
- `MONERO_RPC_USER`
- `MONERO_RPC_PASSWORD`

## Admin Wallet Profit Forwarding
Marketplace includes scheduled XMR profit forwarding command:

```bash
php artisan marketplace:forward-xmr-profits
```

Related `.env` keys:
- `ADMIN_XMR_WALLET`
- `ADMIN_FEE_FORWARD_PERCENT` (0-100)
- `ADMIN_FORWARD_MIN_BALANCE` (atomic units)

A transfer log is written to `admin_profit_transfers`.

## Configuration Reference
| Variable | Purpose |
|---|---|
| `APP_URL` | Marketplace base URL |
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Database connection |
| `MARKET_FEE_PERCENT` | Marketplace fee percent |
| `VENDOR_FEE` | Vendor bond fee amount |
| `ADMIN_XMR_WALLET` | Admin payout wallet |
| `ADMIN_FEE_FORWARD_PERCENT` | Percent of unlocked balance considered for forwarding |
| `ADMIN_FORWARD_MIN_BALANCE` | Minimum/operational reserve threshold in atomic units |
| `MONERO_HOST`, `MONERO_PORT` | Monero daemon or wallet RPC endpoint |
| `MONERO_USERNAME`, `MONERO_PASSWORD` | Legacy Monero wallet RPC credentials |
| `MONERO_RPC_USER`, `MONERO_RPC_PASSWORD` | Monero wallet RPC credentials |
| `MONERO_ADMIN_WALLET` | Monero node setup/admin wallet helper variable |
| `REDIS_HOST`, `REDIS_PORT` | Redis connection |

## SEO Configuration
The main Blade layout now provides default SEO/meta tags (description, robots, Open Graph, Twitter, canonical). Key pages override SEO sections, and `/sitemap.xml` is provided for search engines.

## Debugging
Use:

```bash
sudo APP_DIR=/var/www/eckmar bash scripts/debug.sh
```

Checks include PHP extensions, DB connectivity, Elasticsearch, Redis, Nginx, PHP-FPM socket, `.env` key status, Laravel permissions, Artisan execution, and optional XMR RPC reachability.

## Security
- Keep wallet RPC services bound to localhost or private networks.
- Use firewall rules for 22/80/443 only unless explicitly required.
- Rotate DB/RPC credentials and use strong random secrets.
- Keep server and dependencies patched.

## License / Disclaimer
THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
