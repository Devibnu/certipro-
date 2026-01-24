<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "📋 Checking table structures...\n\n";

$tables = ['pra_pendaftaran', 'pendaftaran_sertifikasi', 'asesmen', 'keputusan_sertifikasi', 'sertifikat'];

foreach ($tables as $table) {
    echo "=== {$table} ===\n";
    
    try {
        $columns = DB::select("SHOW COLUMNS FROM {$table}");
        foreach ($columns as $col) {
            echo "  - {$col->Field} ({$col->Type})\n";
        }
    } catch (\Exception $e) {
        echo "  ❌ Table not found or error: {$e->getMessage()}\n";
    }
    
    echo "\n";
}
