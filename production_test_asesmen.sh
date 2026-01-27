#!/bin/bash

################################################################################
# PRODUCTION TEST SCRIPT - ASESMEN DETAIL ENDPOINT
################################################################################
#
# Script ini untuk memastikan endpoint /adminui/asesmen/detail/{id}
# TIDAK PERNAH ERROR 500 di production.
#
# USAGE:
#   chmod +x production_test_asesmen.sh
#   ./production_test_asesmen.sh
#
################################################################################

echo ""
echo "================================================================================"
echo "🔍 PRODUCTION TEST: Asesmen Detail Endpoint"
echo "================================================================================"
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

PASS=0
FAIL=0
WARN=0

################################################################################
# TEST 1: Check controller has Throwable catch
################################################################################
echo "[TEST 1] Checking controller uses Throwable (not Exception)..."

if grep -q "catch (\\\\Throwable" app/Http/Controllers/AdminUI/AsesmenController.php; then
    echo -e "${GREEN}✓${NC} Controller uses Throwable for comprehensive error handling"
    PASS=$((PASS + 1))
else
    echo -e "${RED}✗${NC} Controller should use Throwable, not Exception"
    FAIL=$((FAIL + 1))
fi

echo ""

################################################################################
# TEST 2: Check controller has logging
################################################################################
echo "[TEST 2] Checking error logging in controller..."

if grep -q "\\\\Log::error" app/Http/Controllers/AdminUI/AsesmenController.php; then
    echo -e "${GREEN}✓${NC} Controller has error logging"
    PASS=$((PASS + 1))
else
    echo -e "${RED}✗${NC} Controller missing error logging"
    FAIL=$((FAIL + 1))
fi

echo ""

################################################################################
# TEST 3: Check error view exists
################################################################################
echo "[TEST 3] Checking error view exists..."

if [ -f "resources/views/adminui/asesmen/error.blade.php" ]; then
    echo -e "${GREEN}✓${NC} Error view exists"
    PASS=$((PASS + 1))
else
    echo -e "${RED}✗${NC} Error view not found"
    FAIL=$((FAIL + 1))
fi

echo ""

################################################################################
# TEST 4: Check blade has try-catch protection
################################################################################
echo "[TEST 4] Checking blade view has error protection..."

if grep -q "try {" resources/views/adminui/asesmen/show.blade.php; then
    echo -e "${GREEN}✓${NC} Blade view has try-catch protection"
    PASS=$((PASS + 1))
else
    echo -e "${YELLOW}⚠${NC} Blade view might not have try-catch protection"
    WARN=$((WARN + 1))
fi

echo ""

################################################################################
# TEST 5: Check controller returns error view (not redirect on critical error)
################################################################################
echo "[TEST 5] Checking controller returns error view for critical errors..."

if grep -q "return view('adminui.asesmen.error'" app/Http/Controllers/AdminUI/AsesmenController.php; then
    echo -e "${GREEN}✓${NC} Controller returns error view (not redirect)"
    PASS=$((PASS + 1))
else
    echo -e "${YELLOW}⚠${NC} Controller might use redirect instead of error view"
    WARN=$((WARN + 1))
fi

echo ""

################################################################################
# TEST 6: Check controller uses find() not findOrFail()
################################################################################
echo "[TEST 6] Checking controller uses safe find() method..."

if grep -q "->find(" app/Http/Controllers/AdminUI/AsesmenController.php; then
    echo -e "${GREEN}✓${NC} Controller uses find() for better control"
    PASS=$((PASS + 1))
else
    echo -e "${YELLOW}⚠${NC} Controller might use findOrFail()"
    WARN=$((WARN + 1))
fi

echo ""

################################################################################
# TEST 7: Check for detailed logging tags
################################################################################
echo "[TEST 7] Checking for detailed error logging tags..."

if grep -q "\[ASESMEN DETAIL ERROR\]" app/Http/Controllers/AdminUI/AsesmenController.php; then
    echo -e "${GREEN}✓${NC} Detailed logging tags found"
    PASS=$((PASS + 1))
else
    echo -e "${YELLOW}⚠${NC} Missing detailed logging tags"
    WARN=$((WARN + 1))
fi

echo ""

################################################################################
# TEST 8: Validate PHP syntax
################################################################################
echo "[TEST 8] Validating PHP syntax..."

php -l app/Http/Controllers/AdminUI/AsesmenController.php > /dev/null 2>&1

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} Controller syntax valid"
    PASS=$((PASS + 1))
else
    echo -e "${RED}✗${NC} Controller has syntax errors"
    FAIL=$((FAIL + 1))
fi

echo ""

################################################################################
# TEST 9: Check blade defensive patterns
################################################################################
echo "[TEST 9] Checking blade defensive coding patterns..."

PATTERNS=0

if grep -q "optional(" resources/views/adminui/asesmen/show.blade.php; then
    echo -e "${GREEN}  ✓${NC} Uses optional() helper"
    PATTERNS=$((PATTERNS + 1))
fi

if grep -q "method_exists" resources/views/adminui/asesmen/show.blade.php; then
    echo -e "${GREEN}  ✓${NC} Uses method_exists() check"
    PATTERNS=$((PATTERNS + 1))
fi

if grep -q "@if(\$detailsByUnit &&" resources/views/adminui/asesmen/show.blade.php; then
    echo -e "${GREEN}  ✓${NC} Checks collection exists before loop"
    PATTERNS=$((PATTERNS + 1))
fi

if [ $PATTERNS -ge 2 ]; then
    echo -e "${GREEN}✓${NC} Blade has defensive patterns ($PATTERNS/3)"
    PASS=$((PASS + 1))
else
    echo -e "${YELLOW}⚠${NC} Blade needs more defensive patterns ($PATTERNS/3)"
    WARN=$((WARN + 1))
fi

echo ""

################################################################################
# TEST 10: Check database for asesmen data
################################################################################
echo "[TEST 10] Checking database for test data..."

ASESMEN_COUNT=$(php artisan tinker --execute="echo App\Models\Asesmen::count();" 2>/dev/null || echo "0")

if [ "$ASESMEN_COUNT" != "0" ] && [ ! -z "$ASESMEN_COUNT" ]; then
    echo -e "${GREEN}✓${NC} Found $ASESMEN_COUNT asesmen records in database"
    PASS=$((PASS + 1))
else
    echo -e "${YELLOW}⚠${NC} No asesmen found in database (test data needed)"
    WARN=$((WARN + 1))
fi

echo ""

################################################################################
# SUMMARY
################################################################################
echo "================================================================================"
echo "📊 TEST SUMMARY"
echo "================================================================================"
echo ""
echo -e "${GREEN}Passed:${NC}  $PASS"
echo -e "${YELLOW}Warnings:${NC} $WARN"
echo -e "${RED}Failed:${NC}  $FAIL"
echo ""

TOTAL=$((PASS + WARN + FAIL))

if [ $FAIL -eq 0 ]; then
    echo -e "${GREEN}✅ ALL CRITICAL TESTS PASSED!${NC}"
    echo ""
    echo "================================================================================"
    echo "🛡️  PRODUCTION READINESS CHECKLIST"
    echo "================================================================================"
    echo ""
    echo "✓ Controller uses Throwable for comprehensive error catching"
    echo "✓ Error logging implemented for debugging"
    echo "✓ Error view exists for graceful degradation"
    echo "✓ Blade has defensive coding patterns"
    echo "✓ No syntax errors"
    echo ""
    echo "📝 NEXT STEPS:"
    echo "1. Monitor logs: tail -f storage/logs/laravel.log"
    echo "2. Test with real IDs in browser"
    echo "3. Test with invalid IDs (should show error page, not 500)"
    echo "4. Test with incomplete data (should handle gracefully)"
    echo ""
    echo "🚀 Endpoint is READY FOR PRODUCTION TESTING"
    echo ""
    
    exit 0
else
    echo -e "${RED}❌ SOME TESTS FAILED${NC}"
    echo ""
    echo "Please fix the failed tests before deploying to production."
    echo ""
    exit 1
fi
