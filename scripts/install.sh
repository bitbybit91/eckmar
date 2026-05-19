#!/bin/bash
set -euo pipefail

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

banner() {
    echo -e "${GREEN}==> $1${NC}"
}

warn() {
    echo -e "${YELLOW}[WARN] $1${NC}"
}

error() {
    echo -e "${RED}[ERROR] $1${NC}"
}

require_cmd() {
    command -v "$1" >/dev/null 2>&1 || {
        error "Missing required command: $1"
        exit 1
    }
}

prompt_if_empty() {
    local var_name="$1"
    local prompt_text="$2"
    local default_value="$3"
    local secret="${4:-false}"
    local current="${!var_name:-}"

    if [ -z "$current" ]; then
        if [ "$secret" = "true" ]; then
            read -r -s -p "$prompt_text [$default_value]: " current
            echo
        else
            read -r -p "$prompt_text [$default_value]: " current
        fi
        current="${current:-$default_value}"
        export "$var_name=$current"
    fi
}

if [ "${EUID}" -ne 0 ]; then
    error "Please run as root (or via sudo)."
    exit 1
fi

DOMAIN="${DOMAIN:-localhost}"
APP_ENV="${APP_ENV:-production}"
APP_URL="${APP_URL:-https://${DOMAIN}}"
DB_NAME="${DB_NAME:-eckmar}"
DB_USER="${DB_USER:-eckmar}"
DB_PASSWORD="${DB_PASSWORD:-eckmar_pass}"
DB_ROOT_PASSWORD="${DB_ROOT_PASSWORD:-}"
ADMIN_XMR_WALLET="${ADMIN_XMR_WALLET:-}"
MONERO_HOST="${MONERO_HOST:-127.0.0.1}"
# MONERO_PORT here is the marketplace wallet-rpc port used by the application (setup-xmr.sh uses daemon RPC 18081).
MONERO_PORT="${MONERO_PORT:-28091}"
MONERO_RPC_USER="${MONERO_RPC_USER:-}"
MONERO_RPC_PASSWORD="${MONERO_RPC_PASSWORD:-}"
MONERO_ADMIN_WALLET="${MONERO_ADMIN_WALLET:-}"
REPO_URL="${REPO_URL:-https://github.com/bitbybit91/eckmar.git}"
APP_DIR="${APP_DIR:-/var/www/eckmar}"

prompt_if_empty DOMAIN "Domain name" "$DOMAIN"
APP_URL="${APP_URL:-https://${DOMAIN}}"
prompt_if_empty DB_NAME "MySQL database name" "$DB_NAME"
prompt_if_empty DB_USER "MySQL database user" "$DB_USER"
prompt_if_empty DB_PASSWORD "MySQL database password" "$DB_PASSWORD" true
prompt_if_empty ADMIN_XMR_WALLET "Admin XMR wallet address" "$ADMIN_XMR_WALLET"

banner "Installing base system packages"
export DEBIAN_FRONTEND=noninteractive
apt-get update -y
apt-get install -y software-properties-common apt-transport-https ca-certificates curl gnupg lsb-release unzip supervisor redis-server ufw cron git jq

banner "Installing Nginx"
apt-get install -y nginx

banner "Installing PHP 7.4 and required extensions"
apt-get install -y php7.4-fpm php7.4-mysql php7.4-mbstring php7.4-xml php7.4-gmp php7.4-curl php7.4-gd php7.4-zip php7.4-bcmath php7.4-json php7.4-tokenizer
if ! apt-get install -y php7.4-xmlrpc; then
    warn "php7.4-xmlrpc unavailable in current apt sources, adding ppa:ondrej/php"
    add-apt-repository -y ppa:ondrej/php
    apt-get update -y
    apt-get install -y php7.4-fpm php7.4-mysql php7.4-mbstring php7.4-xml php7.4-gmp php7.4-curl php7.4-gd php7.4-zip php7.4-bcmath php7.4-json php7.4-tokenizer php7.4-xmlrpc
fi

PHP_INI_FPM="/etc/php/7.4/fpm/php.ini"
if [ -f "$PHP_INI_FPM" ]; then
    sed -i 's#^;*cgi.fix_pathinfo=.*#cgi.fix_pathinfo=0#' "$PHP_INI_FPM"
fi
systemctl enable php7.4-fpm
systemctl restart php7.4-fpm

banner "Installing MySQL 8.0"
apt-get install -y mysql-server
systemctl enable mysql
systemctl restart mysql

MYSQL_DEFAULTS_FILE="/root/.my.cnf.eckmar"
if [ -n "$DB_ROOT_PASSWORD" ]; then
    cat > "$MYSQL_DEFAULTS_FILE" <<EOF
[client]
user=root
password=${DB_ROOT_PASSWORD}
EOF
else
    cat > "$MYSQL_DEFAULTS_FILE" <<EOF
[client]
user=root
EOF
fi
chmod 600 "$MYSQL_DEFAULTS_FILE"

mysql --defaults-file="$MYSQL_DEFAULTS_FILE" -e "CREATE DATABASE IF NOT EXISTS \\`${DB_NAME}\\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql --defaults-file="$MYSQL_DEFAULTS_FILE" -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';"
mysql --defaults-file="$MYSQL_DEFAULTS_FILE" -e "ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';"
mysql --defaults-file="$MYSQL_DEFAULTS_FILE" -e "GRANT ALL PRIVILEGES ON \\`${DB_NAME}\\`.* TO '${DB_USER}'@'localhost'; FLUSH PRIVILEGES;"

banner "Installing Elasticsearch 7.x"
if [ ! -f /usr/share/keyrings/elastic-keyring.gpg ]; then
    curl -fsSL https://artifacts.elastic.co/GPG-KEY-elasticsearch | gpg --dearmor -o /usr/share/keyrings/elastic-keyring.gpg
fi
cat > /etc/apt/sources.list.d/elastic-7.x.list <<'EOF'
deb [signed-by=/usr/share/keyrings/elastic-keyring.gpg] https://artifacts.elastic.co/packages/7.x/apt stable main
EOF
apt-get update -y
apt-get install -y openjdk-11-jre-headless elasticsearch
mkdir -p /etc/elasticsearch/jvm.options.d
cat > /etc/elasticsearch/jvm.options.d/heap.options <<'EOF'
-Xms512m
-Xmx512m
EOF
systemctl daemon-reload
systemctl enable elasticsearch
systemctl restart elasticsearch

banner "Configuring Redis"
sed -i 's/^supervised .*/supervised systemd/' /etc/redis/redis.conf
systemctl enable redis-server
systemctl restart redis-server

banner "Installing Node.js 16.x"
curl -fsSL https://deb.nodesource.com/setup_16.x | bash -
apt-get install -y nodejs

banner "Installing Composer 2.x"
if ! command -v composer >/dev/null 2>&1; then
    EXPECTED_SIGNATURE=$(curl -fsSL https://composer.github.io/installer.sig)
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    ACTUAL_SIGNATURE=$(php -r "echo hash_file('sha384', 'composer-setup.php');")
    if [ "$EXPECTED_SIGNATURE" != "$ACTUAL_SIGNATURE" ]; then
        rm -f composer-setup.php
        error "Invalid composer installer signature"
        exit 1
    fi
    php composer-setup.php --2 --install-dir=/usr/local/bin --filename=composer
    rm -f composer-setup.php
fi

banner "Cloning or updating repository"
mkdir -p "$(dirname "$APP_DIR")"
if [ -d "$APP_DIR/.git" ]; then
    git -C "$APP_DIR" fetch --all
    git -C "$APP_DIR" pull --ff-only || true
else
    git clone "$REPO_URL" "$APP_DIR"
fi

cd "$APP_DIR"

banner "Preparing environment file"
if [ ! -f .env ]; then
    cp .env.example .env
fi

sed -i "s#^APP_ENV=.*#APP_ENV=${APP_ENV}#" .env
sed -i "s#^APP_URL=.*#APP_URL=${APP_URL}#" .env
sed -i "s#^DB_DATABASE=.*#DB_DATABASE=${DB_NAME}#" .env
sed -i "s#^DB_USERNAME=.*#DB_USERNAME=${DB_USER}#" .env
sed -i "s#^DB_PASSWORD=.*#DB_PASSWORD=${DB_PASSWORD}#" .env

grep -q '^ADMIN_XMR_WALLET=' .env && sed -i "s#^ADMIN_XMR_WALLET=.*#ADMIN_XMR_WALLET=${ADMIN_XMR_WALLET}#" .env || echo "ADMIN_XMR_WALLET=${ADMIN_XMR_WALLET}" >> .env
grep -q '^MONERO_HOST=' .env && sed -i "s#^MONERO_HOST=.*#MONERO_HOST=${MONERO_HOST}#" .env || echo "MONERO_HOST=${MONERO_HOST}" >> .env
grep -q '^MONERO_PORT=' .env && sed -i "s#^MONERO_PORT=.*#MONERO_PORT=${MONERO_PORT}#" .env || echo "MONERO_PORT=${MONERO_PORT}" >> .env
grep -q '^MONERO_RPC_USER=' .env && sed -i "s#^MONERO_RPC_USER=.*#MONERO_RPC_USER=${MONERO_RPC_USER}#" .env || echo "MONERO_RPC_USER=${MONERO_RPC_USER}" >> .env
grep -q '^MONERO_RPC_PASSWORD=' .env && sed -i "s#^MONERO_RPC_PASSWORD=.*#MONERO_RPC_PASSWORD=${MONERO_RPC_PASSWORD}#" .env || echo "MONERO_RPC_PASSWORD=${MONERO_RPC_PASSWORD}" >> .env
grep -q '^MONERO_ADMIN_WALLET=' .env && sed -i "s#^MONERO_ADMIN_WALLET=.*#MONERO_ADMIN_WALLET=${MONERO_ADMIN_WALLET}#" .env || echo "MONERO_ADMIN_WALLET=${MONERO_ADMIN_WALLET}" >> .env

if [ -f public/robots.txt ]; then
    sed -i "s#^Sitemap: .*#Sitemap: ${APP_URL%/}/sitemap.xml#" public/robots.txt
fi

banner "Installing PHP dependencies"
COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-interaction

banner "Installing and building frontend assets"
npm install
npm run prod

banner "Running Laravel setup tasks"
php artisan key:generate --force
php artisan migrate --force
php artisan storage:link || true

banner "Setting file permissions"
chown -R www-data:www-data "$APP_DIR"
chmod -R ug+rwx "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

banner "Configuring Nginx"
cat > /etc/nginx/sites-available/eckmar <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN};
    root ${APP_DIR}/public;
    index index.php index.html;

    # HTTPS redirect stub (enable once SSL certificate is configured)
    # return 301 https://\$host\$request_uri;

    gzip on;
    gzip_types text/plain text/css application/json application/javascript application/xml+rss application/xml image/svg+xml;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php7.4-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOF

ln -sfn /etc/nginx/sites-available/eckmar /etc/nginx/sites-enabled/eckmar
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl enable nginx
systemctl restart nginx

banner "Configuring Laravel scheduler cron"
cat > /etc/cron.d/eckmar <<'EOF'
* * * * * www-data php /var/www/eckmar/artisan schedule:run >> /dev/null 2>&1
EOF
chmod 644 /etc/cron.d/eckmar
systemctl restart cron

banner "Configuring Supervisor queue worker"
cat > /etc/supervisor/conf.d/eckmar-worker.conf <<'EOF'
[program:eckmar-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/eckmar/artisan queue:work --sleep=3 --tries=3 --timeout=90
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/supervisor/eckmar-worker.log
stopwaitsecs=3600
EOF
supervisorctl reread
supervisorctl update
supervisorctl restart eckmar-worker:* || true
systemctl enable supervisor
systemctl restart supervisor

banner "Configuring swap (2GB if RAM < 3GB)"
TOTAL_RAM_MB=$(awk '/MemTotal/ {print int($2/1024)}' /proc/meminfo)
if [ "$TOTAL_RAM_MB" -lt 3072 ] && [ ! -f /swapfile ]; then
    fallocate -l 2G /swapfile || dd if=/dev/zero of=/swapfile bs=1M count=2048
    chmod 600 /swapfile
    mkswap /swapfile
    swapon /swapfile
    grep -q '^/swapfile' /etc/fstab || echo '/swapfile none swap sw 0 0' >> /etc/fstab
fi

banner "Configuring UFW"
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw --force enable

banner "Installation complete"
echo ""
echo "Site URL: ${APP_URL}"
echo "Admin wallet configured: ${ADMIN_XMR_WALLET:-not set}"
echo "Next steps:"
echo "  1) Run scripts/debug.sh to validate services"
echo "  2) Configure real SSL certificates (Let's Encrypt)"
echo "  3) Run scripts/setup-xmr.sh to configure monerod"
echo "  4) Configure monero-wallet-rpc for marketplace hot wallet operations"
