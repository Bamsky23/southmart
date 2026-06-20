<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$products = \App\Models\Product::where('name', 'like', "%\n%")
    ->orWhere('name', 'like', "%\r%")
    ->orWhere('name', 'like', "%'%")
    ->orWhere('name', 'like', '%"%')
    ->get();

if ($products->count() > 0) {
    echo "Found " . $products->count() . " products with problematic characters:\n";
    foreach ($products as $p) {
        echo "- ID " . $p->id . ": " . $p->name . "\n";
    }
} else {
    echo "No problematic characters found.\n";
}
