#!/usr/bin/env bash
# DriveNow ERP — generate a .env with cryptographically-random secrets.
# Usage:  ./scripts/generate-secrets.sh [--force]   (run from anywhere)
set -euo pipefail

root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
env_out="$root/.env"

if [ -f "$env_out" ] && [ "${1:-}" != "--force" ]; then
    echo ".env already exists. Re-run with --force to overwrite." >&2
    exit 1
fi

hex32()  { openssl rand -hex 32; }
appkey() { printf 'base64:%s' "$(openssl rand -base64 32)"; }

cat > "$env_out" <<EOF
JWT_SECRET=$(hex32)
SERVICE_TOKEN=$(hex32)
APPKEY_AUTH=$(appkey)
APPKEY_FLEET=$(appkey)
APPKEY_RENTAL=$(appkey)
APPKEY_CRM=$(appkey)
APPKEY_BILLING=$(appkey)
APPKEY_MAINTENANCE=$(appkey)
APPKEY_ANALYTICS=$(appkey)
APPKEY_CLIENT=$(appkey)
APP_DEBUG=false
APP_AUTO_SEED=false
EOF

echo "Wrote $env_out with fresh secrets."
echo "For the first launch: set APP_AUTO_SEED=true in .env, then: docker compose up -d --build"
