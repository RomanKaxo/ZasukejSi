#!/bin/bash

# ===========================================
# Skript pro vytvoření deploy archivu
# bez vendor/, .git/, node_modules/
# ===========================================
# Spustit lokálně: bash make-archive.sh
# Vytvořený archiv: zasukejsi-deploy.tar.gz
# ===========================================

set -euo pipefail

ARCHIVE_NAME="zasukejsi-deploy.tar.gz"
PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"

echo "======================================"
echo " Vytváření deploy archivu..."
echo " Projekt: $PROJECT_DIR"
echo "======================================"

cd "$PROJECT_DIR"

echo ""
echo "Příprava buildu..."
npm run build
echo "  Build OK"

echo ""
echo "Vytváření archivu: $ARCHIVE_NAME"
echo "(bez vendor/, .git/, node_modules/, .env)"

tar --exclude='.git' \
    --exclude='./vendor' \
    --exclude='./node_modules' \
    --exclude='./.env' \
    --exclude='./storage/logs/*.log' \
    --exclude='./storage/framework/cache/*' \
    --exclude='./storage/framework/sessions/*' \
    --exclude='./storage/framework/views/*' \
    --exclude='./bootstrap/cache/*.php' \
    --exclude='./ZasukejSi-clean-repo' \
    --exclude="./$ARCHIVE_NAME" \
    -czf "$ARCHIVE_NAME" .

echo ""
echo "======================================"
echo " Archiv vytvořen: $ARCHIVE_NAME"
SIZE=$(du -sh "$ARCHIVE_NAME" | cut -f1)
echo " Velikost: $SIZE"
echo ""
echo " Nahrání na server:"
echo "   scp $ARCHIVE_NAME user@dev.stanektech.cz:/var/www/zasukejsi/"
echo ""
echo " Na serveru:"
echo "   cd /var/www/zasukejsi"
echo "   tar -xzf $ARCHIVE_NAME"
echo "   bash deploy.sh"
echo "======================================"
