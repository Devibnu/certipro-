<?php

/*
 * Test script untuk render blade view /adminui/asesmen
 * Run: php test_asesmen_view.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing view rendering for adminui.asesmen.index...\n\n";

try {
    // Create empty paginators untuk test
    $pendaftarans = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
    $asesmens = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1, [
        'path' => '/adminui/asesmen',
        'pageName' => 'asesmen_page',
    ]);
    
    // Try to render view
    $view = view('adminui.asesmen.index', compact('pendaftarans', 'asesmens'));
    
    echo "✅ View loaded successfully\n";
    echo "View path: " . $view->getPath() . "\n";
    
    // Try to render
    $html = $view->render();
    
    echo "✅ View rendered successfully\n";
    echo "HTML length: " . strlen($html) . " bytes\n";
    
    // Check for potential issues
    if (strpos($html, 'error') !== false || strpos($html, 'Error') !== false) {
        echo "⚠️  Warning: 'error' found in rendered HTML\n";
    }
    
} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
}
