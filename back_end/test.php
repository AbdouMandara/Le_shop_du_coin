<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $user = App\Models\User::first();
    $product = App\Models\Product::first();
    echo "User: {$user->id}, Product: {$product->id}\n";
    $order = App\Models\Order::create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'status' => 'pending'
    ]);
    echo "Success: " . $order->id;
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
