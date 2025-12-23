<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

$user = User::where('role', 'admin')->first();
echo "User: {$user->name} ({$user->role})\n";
echo "Permissions Column: " . json_encode($user->permissions) . "\n";

$roleConfig = Config::get('role_permission.admin');
echo "Role Config Count: " . count($roleConfig) . "\n";

$perms = $user->effective_permissions;
echo "Effective Permissions Count: " . count($perms) . "\n";
echo "Effective Dump: " . json_encode($perms) . "\n";
