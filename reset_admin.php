<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

$user = User::where('role', 'admin')->first();
if ($user) {
    echo "Reseting permissions for: {$user->name}\n";
    $user->permissions = null;
    $user->save();
    echo "Done.\n";
} else {
    echo "User not found.\n";
}
