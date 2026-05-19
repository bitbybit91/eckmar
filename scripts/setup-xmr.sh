#!/bin/bash
set -euo pipefail

# Auto-detect latest Monero release from GitHub API tag name.
# Monero does NOT publish binaries as GitHub release assets — hosted on downloads.getmonero.org.
_detect_latest_monero_version() {
    curl -fsSL https://api.github.com/repos/monero-project/monero/releases/latest \
        | grep '"tag_name"' | cut -d'"' -f4
}

MONERO_VERSION="${MONERO_VERSION:-}"
if [ -z "$MONERO_VERSION" ]; then
    echo "Detecting latest Monero release..."
    MONERO_VERSION="$(_detect_latest_monero_version)"
    if [ -z "$MONERO_VERSION" ]; then
        echo "ERROR: Could not detect latest Monero version. Set MONERO_VERSION manually."
        exit 1
    fi
    echo "Latest Monero release: $MONERO_VERSION"
fi

MONERO_ARCHIVE="monero-linux-x64-${MONERO_VERSION}.tar.bz2"
MONERO_DOWNLOAD_URL="https://downloads.getmonero.org/cli/${MONERO_ARCHIVE}"
MONERO_HASHES_URL="https://getmonero.org/downloads/hashes.txt"
INSTALL_DIR="/opt/monero"
CONFIG_DIR="/etc/monero"
DATA_DIR="/var/lib/monero"
LOG_DIR="/var/log/monero"
SERVICE_FILE="/etc/systemd/system/monerod.service"
ENV_FILE="${ENV_FILE:-/var/www/eckmar/.env}"
MONERO_HOST="${MONERO_HOST:-127.0.0.1}"
# This script configures monerod daemon RPC (default 18081), not wallet-rpc.
MONERO_PORT="${MONERO_PORT:-18081}"
MONERO_RPC_USER="${MONERO_RPC_USER:-}"
MONERO_RPC_PASSWORD="${MONERO_RPC_PASSWORD:-}"
MONERO_ADMIN_WALLET="${MONERO_ADMIN_WALLET:-}"

if [ "${EUID}" -ne 0 ]; then
    echo "Please run as root (or via sudo)."
    exit 1
fi

apt-get update -y
apt-get install -y curl bzip2 ca-certificates

if ! id monero >/dev/null 2>&1; then
    useradd --system --home /nonexistent --shell /usr/sbin/nologin monero
fi

mkdir -p "$INSTALL_DIR" "$CONFIG_DIR" "$DATA_DIR" "$LOG_DIR"
chown -R monero:monero "$DATA_DIR" "$LOG_DIR"

TMP_DIR="$(mktemp -d)"
trap 'rm -rf "$TMP_DIR"' EXIT

echo "Downloading $MONERO_DOWNLOAD_URL ..."
curl -fsSL "$MONERO_DOWNLOAD_URL" -o "$TMP_DIR/$MONERO_ARCHIVE"
curl -fsSL "$MONERO_HASHES_URL" -o "$TMP_DIR/hashes.txt"

EXPECTED_HASH="$(grep "${MONERO_ARCHIVE}" "$TMP_DIR/hashes.txt" | awk '{print $1}' | head -n1)"
ACTUAL_HASH="$(sha256sum "$TMP_DIR/$MONERO_ARCHIVE" | awk '{print $1}')"

if [ -z "$EXPECTED_HASH" ] || [ "$EXPECTED_HASH" != "$ACTUAL_HASH" ]; then
    echo "SHA256 verification failed for ${MONERO_ARCHIVE}"
    echo "Expected: $EXPECTED_HASH"
    echo "Actual:   $ACTUAL_HASH"
    exit 1
fi

tar -xjf "$TMP_DIR/$MONERO_ARCHIVE" -C "$TMP_DIR"
# The archive filename contains "x64", while the extracted directory name uses "x86_64-linux-gnu".
EXTRACTED_DIR="$(find "$TMP_DIR" -maxdepth 1 -type d -name "monero-x86_64-linux-gnu-*" | head -n1)"
if [ -z "$EXTRACTED_DIR" ]; then
    echo "Unable to locate extracted Monero directory."
    exit 1
fi
cp -f "$EXTRACTED_DIR/monerod" "$INSTALL_DIR/monerod"
chmod 755 "$INSTALL_DIR/monerod"

cat > "$CONFIG_DIR/monerod.conf" <<'EOF'
data-dir=/var/lib/monero
log-file=/var/log/monero/monerod.log
log-level=0
prune-blockchain=1
sync-pruned-blocks=1
db-sync-mode=fast
rpc-bind-ip=127.0.0.1
rpc-bind-port=18081
restricted-rpc=1
confirm-external-bind=0
no-igd=1
hide-my-port=0
out-peers=32
in-peers=64
limit-rate-up=2048
limit-rate-down=8192
EOF

cat > "$SERVICE_FILE" <<'EOF'
[Unit]
Description=Monero Daemon (pruned)
After=network-online.target
Wants=network-online.target

[Service]
Type=simple
User=monero
Group=monero
ExecStart=/opt/monero/monerod --config-file /etc/monero/monerod.conf
Restart=always
RestartSec=10
LimitNOFILE=65535

[Install]
WantedBy=multi-user.target
EOF

systemctl daemon-reload
systemctl enable monerod
systemctl restart monerod

if [ -f "$ENV_FILE" ]; then
    grep -q '^MONERO_HOST=' "$ENV_FILE" && sed -i "s#^MONERO_HOST=.*#MONERO_HOST=${MONERO_HOST}#" "$ENV_FILE" || echo "MONERO_HOST=${MONERO_HOST}" >> "$ENV_FILE"
    grep -q '^MONERO_PORT=' "$ENV_FILE" && sed -i "s#^MONERO_PORT=.*#MONERO_PORT=${MONERO_PORT}#" "$ENV_FILE" || echo "MONERO_PORT=${MONERO_PORT}" >> "$ENV_FILE"
    grep -q '^MONERO_RPC_USER=' "$ENV_FILE" && sed -i "s#^MONERO_RPC_USER=.*#MONERO_RPC_USER=${MONERO_RPC_USER}#" "$ENV_FILE" || echo "MONERO_RPC_USER=${MONERO_RPC_USER}" >> "$ENV_FILE"
    grep -q '^MONERO_RPC_PASSWORD=' "$ENV_FILE" && sed -i "s#^MONERO_RPC_PASSWORD=.*#MONERO_RPC_PASSWORD=${MONERO_RPC_PASSWORD}#" "$ENV_FILE" || echo "MONERO_RPC_PASSWORD=${MONERO_RPC_PASSWORD}" >> "$ENV_FILE"
    grep -q '^MONERO_ADMIN_WALLET=' "$ENV_FILE" && sed -i "s#^MONERO_ADMIN_WALLET=.*#MONERO_ADMIN_WALLET=${MONERO_ADMIN_WALLET}#" "$ENV_FILE" || echo "MONERO_ADMIN_WALLET=${MONERO_ADMIN_WALLET}" >> "$ENV_FILE"
fi

echo "Monero pruned node setup complete (${MONERO_VERSION})."
echo "Service: systemctl status monerod"
