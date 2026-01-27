<?php

/*
 * Test dengan authenticated user
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing with authenticated user...\n\n";

try {
    // Find any user for testing
    $user = App\Models\User::first();
    
    if (!$user) {
        echo "⚠️  No user found in database. Creating test user...\n";
        $user = App\Models\User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
        ]);
    }
    
    echo "Using user: {$user->name} ({$user->email})\n\n";
    
    // Set authenticated user
    auth()->login($user);
    
    // Create empty paginators
    $pendaftarans = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
    $asesmens = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1, [
        'path' => '/adminui/asesmen',
        'pageName' => 'asesmen_page',
    ]);
    
    // Try to render view
    $view = view('adminui.asesmen.index', compact('pendaftarans', 'asesmens'));
    $html = $view->render();
    
    echo "✅ View rendered successfully with authenticated user\n";
    echo "HTML length: " . strlen($html) . " bytes\n";
    
    // Logout
    auth()->logout();
    
} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
