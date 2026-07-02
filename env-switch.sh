#!/bin/bash

# ===========================================
# Environment Switcher Script
# Switches between production and staging
# ===========================================

set -e

PRODUCTION_ENV=".env.production"
STAGING_ENV=".env.staging"
CURRENT_ENV=".env"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to display usage
usage() {
    echo "Usage: ./env-switch.sh [production|staging|status]"
    echo ""
    echo "Commands:"
    echo "  production  - Switch to production environment"
    echo "  staging     - Switch to staging environment"
    echo "  status      - Show current environment"
    exit 1
}

# Function to show current environment
show_status() {
    if [ ! -f "$CURRENT_ENV" ]; then
        echo -e "${RED}❌ No .env file found${NC}"
        exit 1
    fi
    
    echo "📋 Current Environment Status:"
    echo "=============================="
    
    ENV_TYPE=$(grep "^APP_ENV=" "$CURRENT_ENV" | cut -d '=' -f2)
    APP_DEBUG=$(grep "^APP_DEBUG=" "$CURRENT_ENV" | cut -d '=' -f2)
    APP_URL=$(grep "^APP_URL=" "$CURRENT_ENV" | cut -d '=' -f2)
    DB_DATABASE=$(grep "^DB_DATABASE=" "$CURRENT_ENV" | cut -d '=' -f2)
    
    echo "Environment: $ENV_TYPE"
    echo "Debug Mode:  $APP_DEBUG"
    echo "URL:         $APP_URL"
    echo "Database:    $DB_DATABASE"
}

# Function to backup current .env
backup_env() {
    if [ -f "$CURRENT_ENV" ]; then
        TIMESTAMP=$(date +%Y%m%d_%H%M%S)
        BACKUP_FILE=".env.backup.$TIMESTAMP"
        cp "$CURRENT_ENV" "$BACKUP_FILE"
        echo -e "${GREEN}✅ Backed up current .env to: $BACKUP_FILE${NC}"
    fi
}

# Function to switch environment
switch_env() {
    TARGET=$1
    
    if [ "$TARGET" == "production" ]; then
        SOURCE_FILE=$PRODUCTION_ENV
        ENV_NAME="PRODUCTION"
    elif [ "$TARGET" == "staging" ]; then
        SOURCE_FILE=$STAGING_ENV
        ENV_NAME="STAGING"
    else
        usage
    fi
    
    # Check if source env file exists
    if [ ! -f "$SOURCE_FILE" ]; then
        echo -e "${RED}❌ Error: $SOURCE_FILE not found!${NC}"
        echo ""
        echo "Please create $SOURCE_FILE first with your $ENV_NAME configuration."
        exit 1
    fi
    
    echo -e "${YELLOW}🔄 Switching to $ENV_NAME environment...${NC}"
    echo ""
    
    # Backup current .env
    backup_env
    
    # Copy target env file
    cp "$SOURCE_FILE" "$CURRENT_ENV"
    echo -e "${GREEN}✅ Switched to $ENV_NAME environment${NC}"
    
    # Clear caches
    echo ""
    echo "🧹 Clearing caches..."
    php artisan config:clear
    php artisan cache:clear
    php artisan route:clear
    php artisan view:clear
    
    echo ""
    echo -e "${GREEN}✅ Environment switch completed!${NC}"
    echo ""
    show_status
}

# Main script logic
if [ $# -eq 0 ]; then
    usage
fi

case "$1" in
    production)
        echo -e "${YELLOW}⚠️  WARNING: Switching to PRODUCTION environment!${NC}"
        echo "This will affect your live application."
        read -p "Are you sure? (yes/no): " CONFIRM
        if [ "$CONFIRM" != "yes" ]; then
            echo "Cancelled."
            exit 0
        fi
        switch_env "production"
        ;;
    staging)
        switch_env "staging"
        ;;
    status)
        show_status
        ;;
    *)
        usage
        ;;
esac
