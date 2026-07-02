#!/bin/bash

# ===========================================
# Vytvoří čistý upload repozitář vedle projektu
# bez vendor/, .git/, node_modules/ a runtime cache
# ===========================================
# Spustit lokálně: bash prepare-clean-repo.sh
# ===========================================

set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"
TARGET_DIR="${1:-$PROJECT_DIR/../ZasukejSi-clean-repo}"

if [ -e "$TARGET_DIR" ]; then
    echo "CHYBA: Cílová složka už existuje: $TARGET_DIR"
    echo "Smaž ji ručně nebo zadej jinou cestu jako první argument."
    exit 1
fi

mkdir -p "$TARGET_DIR"

echo "======================================"
echo " Vytvářím čistý repozitář"
echo " Zdroj: $PROJECT_DIR"
echo " Cíl:   $TARGET_DIR"
echo "======================================"

cd "$PROJECT_DIR"

tar --exclude='.git' \
    --exclude='./vendor' \
    --exclude='./node_modules' \
    --exclude='./.env' \
    --exclude='./storage/logs/*.log' \
    --exclude='./storage/framework/cache/*' \
    --exclude='./storage/framework/sessions/*' \
    --exclude='./storage/framework/views/*' \
    --exclude='./bootstrap/cache/*.php' \
    --exclude='./zasukejsi-deploy.tar.gz' \
    --exclude='./ZasukejSi-clean-repo' \
    -cf - . | (cd "$TARGET_DIR" && tar -xf -)

echo ""
echo "Hotovo. V čistém repu je připraveno:"
echo "- .env.production pro dev.stanektech.cz"
echo "- deploy.sh pro PHP 8.3 a Node 22"
echo "- bez vendor/, node_modules/ a .git/"