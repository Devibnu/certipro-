<?php

/**
 * Quick fix for orphaned pendaftaran_sertifikasi records
 * Run: php fix_orphaned_data.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🔍 Checking for orphaned pendaftaran_sertifikasi records...\n\n";

// Find orphaned records
$orphaned = DB::table('pendaftaran_sertifikasi')
    ->whereNull('user_id')
    ->whereNotIn('status', ['draft', 'ditolak', 'dibatalkan'])
    ->get(['id', 'nomor_pendaftaran', 'status']);

if ($orphaned->isEmpty()) {
    echo "✅ No orphaned records found. Database is clean!\n";
    exit(0);
}

echo "Found " . $orphaned->count() . " orphaned records:\n";
foreach ($orphaned as $record) {
    echo "  - ID: {$record->id}, Nomor: {$record->nomor_pendaftaran}, Status: {$record->status}\n";
}

echo "\n📝 Setting status to 'draft'...\n";

$updated = DB::table('pendaftaran_sertifikasi')
    ->whereIn('id', $orphaned->pluck('id'))
    ->update([
        'status' => 'draft',
        'updated_at' => now(),
    ]);

echo "✅ Updated {$updated} records to DRAFT status\n";
echo "\n✅ Cleanup complete! You can now run: php artisan migrate\n";
