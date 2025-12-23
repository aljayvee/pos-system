<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

$user = User::where('email', 'manager@pos.com')->first();
if (!$user) {
    echo "User manager@pos.com NOT FOUND.\n";
    exit;
}

echo "User: {$user->name}\n";
echo "Role: '{$user->role}'\n";
echo "Store: {$user->store_id}\n";
echo "Active: " . ($user->is_active ? 'Yes' : 'No') . "\n";

$roleRaw = "manager";
if ($user->role === "manager") {
    echo "Role match strict: YES\n";
} else {
    echo "Role match strict: NO (Length: " . strlen($user->role) . " vs " . strlen("manager") . ")\n";
}
