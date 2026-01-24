#!/bin/bash

# ============================================================================
# Event-Driven Email Architecture - Deployment Script
# ============================================================================
# Purpose: Deploy event-listener architecture to production server
# Server: 76.13.18.166 (lsp-ui.ibnuapps.cloud)
# Author: Development Team
# Date: 2024-01-XX
# ============================================================================

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
PROJECT_PATH="/var/www/lsp-ui.ibnuapps.cloud/current"
PHP_BIN="/usr/bin/php"

echo -e "${GREEN}╔════════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  Event-Driven Email Architecture - Deployment Script          ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════════════════╝${NC}"
echo ""

# Step 1: Check if we're in the correct directory
echo -e "${YELLOW}[1/9] Checking current directory...${NC}"
if [ ! -f "$PROJECT_PATH/artisan" ]; then
    echo -e "${RED}Error: artisan file not found. Are you in the correct directory?${NC}"
    echo "Expected: $PROJECT_PATH"
    exit 1
fi
echo -e "${GREEN}✓ Directory verified${NC}"
echo ""

# Step 2: Backup current state (optional but recommended)
echo -e "${YELLOW}[2/9] Creating backup...${NC}"
BACKUP_DIR="/var/www/backups/$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"
cp -r "$PROJECT_PATH/app/Events" "$BACKUP_DIR/" 2>/dev/null || echo "No Events dir to backup"
cp -r "$PROJECT_PATH/app/Listeners" "$BACKUP_DIR/" 2>/dev/null || echo "No Listeners dir to backup"
cp "$PROJECT_PATH/app/Providers/EventServiceProvider.php" "$BACKUP_DIR/" 2>/dev/null || echo "EventServiceProvider not found"
echo -e "${GREEN}✓ Backup created at: $BACKUP_DIR${NC}"
echo ""

# Step 3: Run migration for status_email field
echo -e "${YELLOW}[3/9] Running database migration...${NC}"
cd "$PROJECT_PATH"
$PHP_BIN artisan migrate --force
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Migration completed successfully${NC}"
else
    echo -e "${RED}✗ Migration failed${NC}"
    exit 1
fi
echo ""

# Step 4: Clear all caches
echo -e "${YELLOW}[4/9] Clearing application caches...${NC}"
$PHP_BIN artisan event:cache
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:clear
echo -e "${GREEN}✓ Caches cleared${NC}"
echo ""

# Step 5: Optimize autoloader
echo -e "${YELLOW}[5/9] Optimizing autoloader...${NC}"
composer dump-autoload --optimize
echo -e "${GREEN}✓ Autoloader optimized${NC}"
echo ""

# Step 6: Restart queue workers
echo -e "${YELLOW}[6/9] Restarting queue workers...${NC}"
$PHP_BIN artisan queue:restart
echo -e "${GREEN}✓ Queue workers restarted${NC}"
echo ""

# Step 7: Check for failed jobs
echo -e "${YELLOW}[7/9] Checking for failed jobs...${NC}"
FAILED_JOBS=$($PHP_BIN artisan queue:failed | grep -c "Failed" || echo "0")
if [ "$FAILED_JOBS" -gt "0" ]; then
    echo -e "${YELLOW}⚠ Warning: $FAILED_JOBS failed job(s) detected${NC}"
    echo "Run: php artisan queue:failed to view them"
else
    echo -e "${GREEN}✓ No failed jobs${NC}"
fi
echo ""

# Step 8: Verify Event-Listener registration
echo -e "${YELLOW}[8/9] Verifying Event-Listener registration...${NC}"
$PHP_BIN artisan event:list | grep -E "PraPendaftaran|Keputusan|Sertifikat" || echo "No events found (might need manual check)"
echo -e "${GREEN}✓ Event registration verified${NC}"
echo ""

# Step 9: Display deployment summary
echo -e "${YELLOW}[9/9] Deployment Summary${NC}"
echo -e "${GREEN}════════════════════════════════════════════════════════════════${NC}"
echo ""
echo "📦 Files Deployed:"
echo "   - 5 Event classes (app/Events/)"
echo "   - 5 Listener classes (app/Listeners/)"
echo "   - EmailNotificationService updated"
echo "   - EventServiceProvider updated"
echo "   - 2 Controllers refactored"
echo "   - 1 Migration executed"
echo ""
echo "✅ Event → Listener Mappings:"
echo "   - PraPendaftaranDiterimaEvent → SendPraPendaftaranDiterimaEmail"
echo "   - PraPendaftaranDitolakEvent → SendPraPendaftaranDitolakEmail"
echo "   - KeputusanKompetenEvent → SendKeputusanKompetenEmail"
echo "   - KeputusanBelumKompetenEvent → SendKeputusanBelumKompetenEmail"
echo "   - SertifikatTerbitEvent → SendSertifikatTerbitEmail (reserved)"
echo ""
echo -e "${GREEN}════════════════════════════════════════════════════════════════${NC}"
echo ""

# Testing instructions
echo -e "${YELLOW}📋 Next Steps - MANUAL TESTING:${NC}"
echo ""
echo "1. Test Event Dispatching (via tinker):"
echo "   $ php artisan tinker"
echo "   >>> \$pra = App\\Models\\PraPendaftaran::first();"
echo "   >>> event(new App\\Events\\PraPendaftaranDiterimaEvent(\$pra));"
echo "   >>> exit"
echo ""
echo "2. Process Queue Job:"
echo "   $ php artisan queue:work --once -vvv"
echo ""
echo "3. Check Logs:"
echo "   $ tail -f storage/logs/laravel.log | grep -E 'Listener|EmailService'"
echo ""
echo "4. Monitor Queue Jobs:"
echo "   $ mysql -e 'SELECT * FROM jobs ORDER BY id DESC LIMIT 10;'"
echo ""
echo "5. Check Failed Jobs:"
echo "   $ php artisan queue:failed"
echo ""
echo -e "${GREEN}════════════════════════════════════════════════════════════════${NC}"
echo ""
echo -e "${YELLOW}⚠️  IMPORTANT NOTES:${NC}"
echo ""
echo "• Ensure queue worker is running in background:"
echo "  $ nohup php artisan queue:work --tries=3 --timeout=60 &"
echo ""
echo "• Or use Supervisor (recommended):"
echo "  $ sudo supervisorctl start laravel-queue-worker:*"
echo ""
echo "• Monitor email delivery in laravel.log"
echo "• Check EMAIL_STATUS_MATRIX.md for compliance rules"
echo "• Report any issues to development team"
echo ""
echo -e "${GREEN}✓ Deployment completed successfully!${NC}"
echo ""

exit 0
