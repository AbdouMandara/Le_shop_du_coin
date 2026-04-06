<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $p = \App\Models\Product::first();
    if($p) {
        $p->decrement('quantity', 1);
        echo "Decrement OK";
    } else {
        echo "No products";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
