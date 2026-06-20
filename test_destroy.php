<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $user = \App\Models\User::where('role', 'admin')->first();
    \Auth::login($user);
    $controller = app(\App\Http\Controllers\ProductController::class);
    $response = $controller->destroy(3); // Test product ID 3
    echo "SUCCESS: " . get_class($response) . "\n";
    
    // session flash data
    $session = session()->all();
    if (isset($session['error'])) {
        echo "SESSION ERROR: " . $session['error'] . "\n";
    } elseif (isset($session['success'])) {
        echo "SESSION SUCCESS: " . $session['success'] . "\n";
    } else {
        print_r($session);
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
