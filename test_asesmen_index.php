<?php

/*
 * Test script untuk debug error di /adminui/asesmen
 * Run: php test_asesmen_index.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing AsesmenController::index()...\n\n";

try {
    // Simulate request
    $request = Illuminate\Http\Request::create('/adminui/asesmen', 'GET');
    
    // Get controller instance
    $controller = new App\Http\Controllers\AdminUI\AsesmenController();
    
    // Call index method
    $response = $controller->index($request);
    
    echo "✅ SUCCESS: Controller method executed without errors\n";
    echo "Response type: " . get_class($response) . "\n";
    
    if ($response instanceof Illuminate\View\View) {
        echo "View name: " . $response->name() . "\n";
        echo "View data keys: " . implode(', ', array_keys($response->getData())) . "\n";
    }
    
} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
}
