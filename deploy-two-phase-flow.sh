#!/bin/bash

# ====================================================================
# Two-Phase Registration Flow - Deployment Script
# ====================================================================
# This script deploys the fix for auto-create Pendaftaran issue
# ====================================================================

set -e  # Exit on error

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
SERVER="76.13.18.166"
USER="root"
REMOTE_PATH="/var/www/lsp-ui.ibnuapps.cloud/current"
LOCAL_PATH="/Users/ibnuqosim/Documents/devlopmentibnu/certipro"

echo -e "${GREEN}=====================================================================${NC}"
echo -e "${GREEN}Two-Phase Registration Flow Deployment${NC}"
echo -e "${GREEN}=====================================================================${NC}"
echo ""

# Confirmation
echo -e "${YELLOW}This will deploy the following changes:${NC}"
echo "  1. PraPendaftaranAdminController.php (removed auto-create logic)"
echo "  2. PendaftaranSertifikasiAdminController.php (added explicit creation)"
echo "  3. routes/web.php (added new route)"
echo "  4. pra-pendaftaran/index.blade.php (added modal for skema)"
echo ""
read -p "Continue with deployment? (y/n): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]
then
    echo -e "${RED}Deployment cancelled.${NC}"
    exit 1
fi

echo -e "${GREEN}Starting deployment...${NC}"
echo ""

# Step 1: Create backup on server
echo -e "${YELLOW}[1/6] Creating backup on server...${NC}"
ssh ${USER}@${SERVER} << 'ENDSSH'
    cd /var/www/lsp-ui.ibnuapps.cloud/current
    mkdir -p backup/two-phase-flow-fix-$(date +%Y%m%d-%H%M%S)
    BACKUP_DIR="backup/two-phase-flow-fix-$(date +%Y%m%d-%H%M%S)"
    
    cp app/Http/Controllers/AdminUI/PraPendaftaranAdminController.php $BACKUP_DIR/
    cp app/Http/Controllers/AdminUI/PendaftaranSertifikasiAdminController.php $BACKUP_DIR/
    cp routes/web.php $BACKUP_DIR/
    cp resources/views/adminui/pra-pendaftaran/index.blade.php $BACKUP_DIR/
    
    echo "Backup created at: $BACKUP_DIR"
ENDSSH

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Backup completed${NC}"
else
    echo -e "${RED}✗ Backup failed!${NC}"
    exit 1
fi

# Step 2: Upload modified files
echo -e "${YELLOW}[2/6] Uploading modified files...${NC}"

scp ${LOCAL_PATH}/app/Http/Controllers/AdminUI/PraPendaftaranAdminController.php \
    ${USER}@${SERVER}:${REMOTE_PATH}/app/Http/Controllers/AdminUI/
echo "  ✓ PraPendaftaranAdminController.php uploaded"

scp ${LOCAL_PATH}/app/Http/Controllers/AdminUI/PendaftaranSertifikasiAdminController.php \
    ${USER}@${SERVER}:${REMOTE_PATH}/app/Http/Controllers/AdminUI/
echo "  ✓ PendaftaranSertifikasiAdminController.php uploaded"

scp ${LOCAL_PATH}/routes/web.php \
    ${USER}@${SERVER}:${REMOTE_PATH}/routes/
echo "  ✓ web.php uploaded"

scp ${LOCAL_PATH}/resources/views/adminui/pra-pendaftaran/index.blade.php \
    ${USER}@${SERVER}:${REMOTE_PATH}/resources/views/adminui/pra-pendaftaran/
echo "  ✓ index.blade.php uploaded"

echo -e "${GREEN}✓ All files uploaded${NC}"

# Step 3: Set permissions
echo -e "${YELLOW}[3/6] Setting file permissions...${NC}"
ssh ${USER}@${SERVER} << 'ENDSSH'
    chown -R www-data:www-data /var/www/lsp-ui.ibnuapps.cloud/current/app/Http/Controllers/AdminUI/
    chown www-data:www-data /var/www/lsp-ui.ibnuapps.cloud/current/routes/web.php
    chown www-data:www-data /var/www/lsp-ui.ibnuapps.cloud/current/resources/views/adminui/pra-pendaftaran/index.blade.php
    chmod 644 /var/www/lsp-ui.ibnuapps.cloud/current/app/Http/Controllers/AdminUI/*.php
    chmod 644 /var/www/lsp-ui.ibnuapps.cloud/current/routes/web.php
    chmod 644 /var/www/lsp-ui.ibnuapps.cloud/current/resources/views/adminui/pra-pendaftaran/index.blade.php
ENDSSH
echo -e "${GREEN}✓ Permissions set${NC}"

# Step 4: Clear caches
echo -e "${YELLOW}[4/6] Clearing Laravel caches...${NC}"
ssh ${USER}@${SERVER} << 'ENDSSH'
    cd /var/www/lsp-ui.ibnuapps.cloud/current
    php artisan route:cache
    php artisan config:cache
    php artisan view:cache
    php artisan cache:clear
ENDSSH
echo -e "${GREEN}✓ Caches cleared${NC}"

# Step 5: Restart services
echo -e "${YELLOW}[5/6] Restarting services...${NC}"
ssh ${USER}@${SERVER} << 'ENDSSH'
    systemctl restart php8.3-fpm
    systemctl reload nginx
ENDSSH
echo -e "${GREEN}✓ Services restarted${NC}"

# Step 6: Verify deployment
echo -e "${YELLOW}[6/6] Verifying deployment...${NC}"
echo ""
echo "Checking route cache..."
ssh ${USER}@${SERVER} << 'ENDSSH'
    cd /var/www/lsp-ui.ibnuapps.cloud/current
    php artisan route:list | grep "create-from-pra"
ENDSSH

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ New route 'create-from-pra' found!${NC}"
else
    echo -e "${YELLOW}⚠ Warning: Route not found, but may still work${NC}"
fi

echo ""
echo -e "${GREEN}=====================================================================${NC}"
echo -e "${GREEN}Deployment completed successfully!${NC}"
echo -e "${GREEN}=====================================================================${NC}"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "1. Test the workflow:"
echo "   - Go to Pra-Pendaftaran index"
echo "   - Approve a Pra-Pendaftaran (status → DITERIMA)"
echo "   - Verify NO auto-create of Pendaftaran"
echo "   - Click green button ✅ to create Pendaftaran with skema"
echo ""
echo "2. Monitor logs:"
echo "   ssh ${USER}@${SERVER}"
echo "   tail -f ${REMOTE_PATH}/storage/logs/laravel.log"
echo ""
echo "3. Check deployment documentation:"
echo "   ${LOCAL_PATH}/docs/TWO_PHASE_REGISTRATION_FLOW_DEPLOYMENT.md"
echo ""
echo -e "${GREEN}Deployment finished at: $(date)${NC}"
