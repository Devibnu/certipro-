#!/bin/bash

# ============================================================================
# Script untuk DELETE user ibnuqosim022@gmail.com dari PRODUCTION
# ============================================================================
# USAGE:
# 1. chmod +x run-delete-user.sh
# 2. ./run-delete-user.sh
# ============================================================================

SERVER="76.13.18.166"
USER="root"
PROJECT_PATH="/var/www/lsp-ui.ibnuapps.cloud/current"

echo "════════════════════════════════════════════════════════════════"
echo "  DELETE USER FROM PRODUCTION SERVER"
echo "  Server: $SERVER"
echo "  Email: ibnuqosim022@gmail.com"
echo "════════════════════════════════════════════════════════════════"
echo ""

# Upload script ke server
echo "📤 Uploading deletion script to server..."
scp delete-user-production.php $USER@$SERVER:$PROJECT_PATH/

if [ $? -ne 0 ]; then
    echo "❌ Failed to upload script to server"
    exit 1
fi

echo "✅ Script uploaded successfully"
echo ""

# SSH dan execute
echo "🔐 Connecting to server and executing deletion..."
echo ""

ssh $USER@$SERVER << 'ENDSSH'
cd /var/www/lsp-ui.ibnuapps.cloud/current

echo "Current directory: $(pwd)"
echo ""

# Run deletion script
php delete-user-production.php

# Cleanup
echo ""
echo "🧹 Cleaning up..."
rm -f delete-user-production.php
echo "✅ Script removed from server"

ENDSSH

echo ""
echo "════════════════════════════════════════════════════════════════"
echo "  Done!"
echo "════════════════════════════════════════════════════════════════"
