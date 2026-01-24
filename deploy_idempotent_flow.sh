#!/bin/bash

# ========================================
# DEPLOYMENT SCRIPT: Idempotent Registration Flow
# ========================================
# Author: Senior Laravel Engineer
# Date: 23 January 2026
# Description: Deploy idempotent registration flow to production
# ========================================

set -e  # Exit on error

echo "=========================================="
echo "🚀 DEPLOYMENT: Idempotent Registration Flow"
echo "=========================================="
echo ""

# Configuration
SERVER="root@76.13.18.166"
APP_PATH="/var/www/lsp-ui.ibnuapps.cloud/current"
BACKUP_PATH="/var/www/lsp-ui.ibnuapps.cloud/backups/$(date +%Y%m%d_%H%M%S)"

echo "📋 Configuration:"
echo "   Server: $SERVER"
echo "   App Path: $APP_PATH"
echo "   Backup Path: $BACKUP_PATH"
echo ""

# Step 1: Backup existing files
echo "📦 Step 1: Creating backup..."
ssh $SERVER "mkdir -p $BACKUP_PATH"
ssh $SERVER "cp -r $APP_PATH/app/Http/Controllers/PraPendaftaranController.php $BACKUP_PATH/ 2>/dev/null || true"
ssh $SERVER "cp -r $APP_PATH/app/Mail/PraPendaftaran/PraPendaftaranDiterima.php $BACKUP_PATH/ 2>/dev/null || true"
ssh $SERVER "cp -r $APP_PATH/routes/web.php $BACKUP_PATH/ 2>/dev/null || true"
echo "   ✅ Backup created at $BACKUP_PATH"
echo ""

# Step 2: Upload new files
echo "📤 Step 2: Uploading new files..."

# Controller
echo "   Uploading ResumePendaftaranController.php..."
scp app/Http/Controllers/ResumePendaftaranController.php $SERVER:$APP_PATH/app/Http/Controllers/

# Updated PraPendaftaranController (with validation)
echo "   Uploading updated PraPendaftaranController.php..."
scp app/Http/Controllers/PraPendaftaranController.php $SERVER:$APP_PATH/app/Http/Controllers/

# Updated Mailable (with signed URL)
echo "   Uploading updated PraPendaftaranDiterima.php..."
scp app/Mail/PraPendaftaran/PraPendaftaranDiterima.php $SERVER:$APP_PATH/app/Mail/PraPendaftaran/

# Routes
echo "   Uploading updated web.php..."
scp routes/web.php $SERVER:$APP_PATH/routes/

# View
echo "   Uploading public-detail.blade.php..."
ssh $SERVER "mkdir -p $APP_PATH/resources/views/pendaftaran-sertifikasi"
scp resources/views/pendaftaran-sertifikasi/public-detail.blade.php $SERVER:$APP_PATH/resources/views/pendaftaran-sertifikasi/

echo "   ✅ All files uploaded"
echo ""

# Step 3: Set permissions
echo "🔐 Step 3: Setting permissions..."
ssh $SERVER "chown -R www-data:www-data $APP_PATH/app/Http/Controllers/"
ssh $SERVER "chown -R www-data:www-data $APP_PATH/app/Mail/"
ssh $SERVER "chown -R www-data:www-data $APP_PATH/routes/"
ssh $SERVER "chown -R www-data:www-data $APP_PATH/resources/views/"
echo "   ✅ Permissions set"
echo ""

# Step 4: Clear caches
echo "🗑️  Step 4: Clearing caches..."
ssh $SERVER "cd $APP_PATH && php artisan route:clear"
ssh $SERVER "cd $APP_PATH && php artisan config:clear"
ssh $SERVER "cd $APP_PATH && php artisan view:clear"
ssh $SERVER "cd $APP_PATH && php artisan cache:clear"
echo "   ✅ Caches cleared"
echo ""

# Step 5: Verify deployment
echo "✅ Step 5: Verifying deployment..."

# Check if routes exist
echo "   Checking routes..."
ssh $SERVER "cd $APP_PATH && php artisan route:list | grep 'pendaftaran.lanjut'" && echo "   ✅ Route 'pendaftaran.lanjut' found" || echo "   ❌ Route 'pendaftaran.lanjut' NOT found"
ssh $SERVER "cd $APP_PATH && php artisan route:list | grep 'pendaftaran-sertifikasi.show'" && echo "   ✅ Route 'pendaftaran-sertifikasi.show' found" || echo "   ❌ Route 'pendaftaran-sertifikasi.show' NOT found"

# Check if controller exists
echo "   Checking controller..."
ssh $SERVER "[ -f $APP_PATH/app/Http/Controllers/ResumePendaftaranController.php ]" && echo "   ✅ ResumePendaftaranController.php exists" || echo "   ❌ ResumePendaftaranController.php NOT found"

# Check if view exists
echo "   Checking view..."
ssh $SERVER "[ -f $APP_PATH/resources/views/pendaftaran-sertifikasi/public-detail.blade.php ]" && echo "   ✅ public-detail.blade.php exists" || echo "   ❌ public-detail.blade.php NOT found"

echo ""

# Step 6: Test (optional)
echo "🧪 Step 6: Testing (optional)..."
echo "   You can manually test by:"
echo "   1. Approve a pra-pendaftaran (admin panel)"
echo "   2. Check email for signed URL"
echo "   3. Click link and verify it works"
echo "   4. Click link AGAIN and verify no duplicate"
echo ""

# Summary
echo "=========================================="
echo "✅ DEPLOYMENT COMPLETED"
echo "=========================================="
echo ""
echo "📌 Next Steps:"
echo "   1. Test flow dari awal (submit → approve → email → click link)"
echo "   2. Test double click (idempotency)"
echo "   3. Monitor audit logs: cd $APP_PATH && tail -f storage/logs/laravel.log"
echo "   4. Check for duplicates:"
echo "      SELECT pra_pendaftaran_id, COUNT(*) FROM pendaftaran_sertifikasi GROUP BY pra_pendaftaran_id HAVING COUNT(*) > 1;"
echo ""
echo "🔙 Rollback (if needed):"
echo "   ssh $SERVER"
echo "   cp $BACKUP_PATH/PraPendaftaranController.php $APP_PATH/app/Http/Controllers/"
echo "   cp $BACKUP_PATH/PraPendaftaranDiterima.php $APP_PATH/app/Mail/PraPendaftaran/"
echo "   cp $BACKUP_PATH/web.php $APP_PATH/routes/"
echo "   cd $APP_PATH && php artisan route:clear && php artisan config:clear && php artisan view:clear"
echo ""
echo "📖 Documentation: IDEMPOTENT_REGISTRATION_FLOW.md"
echo ""
