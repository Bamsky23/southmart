<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$p = \App\Models\Product::withTrashed()->where('sku', 'MKN-018')->first();
if ($p) {
    echo "ID: " . $p->id . "\n";
    echo "Deleted At: " . $p->deleted_at . "\n";
} else {
    echo "NOT FOUND\n";
}
