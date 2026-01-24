<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n=== STATE MACHINE CONSTRAINTS VERIFICATION ===\n\n";

$constraints = [
    'pendaftaran_sertifikasi' => [
        'unique_pra_pendaftaran_id' => 'pra_pendaftaran_id',
        'idx_pendaftaran_status' => 'status',
        'idx_pendaftaran_locked' => 'is_locked',
    ],
    'asesmen' => [
        'unique_pendaftaran_asesmen' => 'pendaftaran_id',
        'idx_asesmen_status' => 'status',
        'idx_asesmen_locked' => 'is_locked',
    ],
    'keputusan_sertifikasi' => [
        'unique_asesmen_keputusan' => 'asesmen_id',
        'idx_keputusan_status' => 'status',
        'idx_keputusan_locked' => 'is_locked',
    ],
    'sertifikat' => [
        'unique_keputusan_sertifikat' => 'keputusan_id',
        'idx_sertifikat_status' => 'status',
        'idx_sertifikat_expiry' => 'tanggal_berlaku_sampai',
        'idx_sertifikat_nomor' => 'nomor_sertifikat',
    ],
];

$allPassed = true;

foreach ($constraints as $table => $indexes) {
    echo "Table: $table\n";
    
    foreach ($indexes as $indexName => $columnName) {
        $result = DB::select("SHOW INDEX FROM $table WHERE Key_name = ?", [$indexName]);
        
        if (count($result) > 0) {
            $isUnique = $result[0]->Non_unique == 0 ? 'UNIQUE' : 'INDEX';
            echo "  ✅ $indexName on column '$columnName' ($isUnique)\n";
        } else {
            echo "  ❌ $indexName NOT FOUND\n";
            $allPassed = false;
        }
    }
    echo "\n";
}

// Verify lock columns exist
echo "=== LOCK COLUMNS VERIFICATION ===\n\n";

$lockColumns = [
    'pra_pendaftaran' => ['is_processed', 'processed_at', 'catatan_admin'],
    'pendaftaran_sertifikasi' => ['is_locked', 'locked_at', 'locked_reason', 'status_updated_at', 'catatan_admin'],
    'asesmen' => ['is_locked', 'locked_at', 'started_at', 'completed_at'],
    'keputusan_sertifikasi' => ['status', 'is_locked', 'locked_at', 'decided_at', 'decided_by', 'catatan'],
    'sertifikat' => ['status', 'keputusan_id', 'revoked_at', 'revoked_by', 'revoked_reason'],
];

foreach ($lockColumns as $table => $columns) {
    echo "Table: $table\n";
    
    $tableColumns = DB::select("SHOW COLUMNS FROM $table");
    $existingColumns = array_column($tableColumns, 'Field');
    
    foreach ($columns as $column) {
        if (in_array($column, $existingColumns)) {
            echo "  ✅ $column\n";
        } else {
            echo "  ❌ $column MISSING\n";
            $allPassed = false;
        }
    }
    echo "\n";
}

if ($allPassed) {
    echo "\n✅ ALL CONSTRAINTS AND COLUMNS VERIFIED SUCCESSFULLY!\n";
    echo "State Machine is ready to use.\n\n";
} else {
    echo "\n❌ SOME CONSTRAINTS OR COLUMNS ARE MISSING\n";
    echo "Please review the migration.\n\n";
}
