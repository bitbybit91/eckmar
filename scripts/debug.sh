#!/bin/bash
set -euo pipefail

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

APP_DIR="${APP_DIR:-/var/www/eckmar}"
ENV_FILE="${APP_DIR}/.env"

pass() {
    echo -e "${GREEN}PASS${NC}: $1"
}

fail() {
    echo -e "${RED}FAIL${NC}: $1"
    echo -e "${YELLOW}Hint:${NC} $2"
}

env_get() {
    local key="$1"
    if [ ! -f "$ENV_FILE" ]; then
        echo ""
        return
    fi
    grep -E "^${key}=" "$ENV_FILE" | tail -n1 | cut -d '=' -f2- | sed 's/^"//;s/"$//'
}

check_php_extensions() {
    local extensions=(curl gmp json mbstring mysqlnd tokenizer xml xmlrpc zip bcmath gd)
    local missing=()

    for ext in "${extensions[@]}"; do
        if ! php -m | grep -iq "^${ext}$"; then
            missing+=("$ext")
        fi
    done

    if [ "${#missing[@]}" -eq 0 ]; then
        pass "All required PHP extensions are loaded"
    else
        fail "Missing PHP extensions: ${missing[*]}" "Install missing packages (e.g. apt install php7.4-<extension>) and restart php-fpm."
    fi
}

check_mysql() {
    local db_host db_port db_name db_user db_pass
    db_host="$(env_get DB_HOST)"
    db_port="$(env_get DB_PORT)"
    db_name="$(env_get DB_DATABASE)"
    db_user="$(env_get DB_USERNAME)"
    db_pass="$(env_get DB_PASSWORD)"

    local mysql_password_arg=()
    if [ -n "${db_pass:-}" ]; then
        mysql_password_arg=("-p${db_pass}")
    fi

    if mysql --protocol=tcp -h "${db_host:-127.0.0.1}" -P "${db_port:-3306}" -u "$db_user" "${mysql_password_arg[@]}" -e "USE \\`${db_name}\\`;" >/dev/null 2>&1; then
        pass "MySQL connection succeeded"
    else
        fail "MySQL connection failed" "Verify DB_* values in .env and MySQL user privileges."
    fi
}

check_elasticsearch() {
    if curl -fsS "http://127.0.0.1:9200" >/dev/null 2>&1; then
        pass "Elasticsearch is reachable on localhost:9200"
    else
        fail "Elasticsearch is unreachable" "Check elasticsearch service status and port bindings."
    fi
}

check_redis() {
    if redis-cli -h 127.0.0.1 -p 6379 ping | grep -q PONG; then
        pass "Redis is reachable on localhost:6379"
    else
        fail "Redis is unreachable" "Check redis-server service and redis.conf."
    fi
}

check_nginx() {
    if nginx -t >/dev/null 2>&1; then
        pass "Nginx configuration test passed"
    else
        fail "Nginx configuration test failed" "Run nginx -t to inspect syntax and fix invalid config."
    fi
}

check_php_fpm_socket() {
    if [ -S /run/php/php7.4-fpm.sock ]; then
        pass "PHP-FPM socket exists (/run/php/php7.4-fpm.sock)"
    else
        fail "PHP-FPM socket missing" "Ensure php7.4-fpm is installed and running."
    fi
}

check_app_key() {
    local app_key
    app_key="$(env_get APP_KEY)"
    if [ -n "$app_key" ]; then
        pass "APP_KEY is set"
    else
        fail "APP_KEY is empty" "Run: php artisan key:generate --force"
    fi
}

check_writable_paths() {
    local storage_path="${APP_DIR}/storage"
    local cache_path="${APP_DIR}/bootstrap/cache"

    if [ -w "$storage_path" ] && [ -w "$cache_path" ]; then
        pass "storage/ and bootstrap/cache are writable"
    else
        fail "storage/ or bootstrap/cache not writable" "Set ownership to www-data and adjust permissions."
    fi
}

check_artisan() {
    if [ -f "${APP_DIR}/artisan" ] && php "${APP_DIR}/artisan" --version >/dev/null 2>&1; then
        pass "Artisan executes correctly"
    else
        fail "Artisan execution failed" "Run composer install and verify PHP extensions/environment settings."
    fi
}

check_xmr_rpc() {
    local host port
    host="$(env_get MONERO_HOST)"
    port="$(env_get MONERO_PORT)"

    if [ -z "$host" ] || [ -z "$port" ]; then
        fail "XMR daemon RPC not configured" "Set MONERO_HOST and MONERO_PORT in .env to enable this check."
        return
    fi

    if curl -fsS "http://${host}:${port}/json_rpc" -H 'Content-Type: application/json' -d '{"jsonrpc":"2.0","id":"0","method":"get_info"}' >/dev/null 2>&1; then
        pass "XMR daemon RPC is reachable (${host}:${port})"
    else
        fail "XMR daemon RPC unreachable" "Ensure monerod is running and rpc-bind settings allow localhost access."
    fi
}

main() {
    echo "Running Eckmar diagnostics in ${APP_DIR}"
    check_php_extensions
    check_mysql
    check_elasticsearch
    check_redis
    check_nginx
    check_php_fpm_socket
    check_app_key
    check_writable_paths
    check_artisan
    check_xmr_rpc
}

main
