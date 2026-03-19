<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

$email = 'livreur@livreur.com';
$password = 'password';

$user = User::where('email', $email)->first();

if (!$user) {
    echo "User not found\n";
    exit;
}

if (Auth::attempt(['email' => $email, 'password' => $password])) {
    echo "Login successful for $email\n";
} else {
    echo "Login failed for $email\n";
    // Check hash directly
    if (password_verify($password, $user->password)) {
        echo "Password verification passed directly, but Auth::attempt failed.\n";
    } else {
        echo "Password verification failed directly.\n";
    }
}
